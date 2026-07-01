<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Customer as StripeCustomer;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;
use Stripe\Webhook;

class SubscriptionController extends Controller
{
    public function __construct(private EmailService $email) {}

    public function status(): JsonResponse
    {
        $user = auth()->user();
        $subscription = $user->subscription ?? $user->subscription()->create(['status' => 'trial']);

        $data = [
            'status'             => $subscription->status,
            'current_period_end' => $subscription->current_period_end,
        ];

        if ($subscription->isTrial()) {
            $userId = $user->id;
            $data['customers_count'] = Customer::where('user_id', $userId)->count();
            $data['vehicles_count']  = Vehicle::where('user_id', $userId)->count();
            $data['customers_limit'] = 3;
            $data['vehicles_limit']  = 3;
        }

        return response()->json($data);
    }

    public function checkout(Request $request): JsonResponse
    {
        $user         = auth()->user();
        $subscription = $user->subscription ?? $user->subscription()->create(['status' => 'trial']);

        Stripe::setApiKey(config('services.stripe.secret'));

        if (! $subscription->stripe_customer_id) {
            $stripeCustomer = StripeCustomer::create([
                'email' => $user->email,
                'name'  => $user->name,
            ]);
            $subscription->update(['stripe_customer_id' => $stripeCustomer->id]);
        }

        $session = CheckoutSession::create([
            'customer'             => $subscription->stripe_customer_id,
            'payment_method_types' => ['card'],
            'line_items'           => [[
                'price'    => config('services.stripe.price_id'),
                'quantity' => 1,
            ]],
            'mode'        => 'subscription',
            'success_url' => config('services.stripe.frontend_url') . '/assinatura/sucesso?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => config('services.stripe.frontend_url') . '/assinatura',
        ]);

        return response()->json(['url' => $session->url]);
    }

    public function verifySession(Request $request): JsonResponse
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return response()->json(['message' => 'Session ID obrigatório'], 422);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = CheckoutSession::retrieve([
            'id'     => $sessionId,
            'expand' => ['subscription'],
        ]);

        if (! in_array($session->payment_status, ['paid', 'no_payment_required'])) {
            return response()->json(['message' => 'Pagamento não confirmado'], 402);
        }

        $user         = auth()->user();
        $subscription = $user->subscription;

        if ($subscription && $session->customer === $subscription->stripe_customer_id) {
            $subscriptionId = is_string($session->subscription)
                ? $session->subscription
                : $session->subscription->id;

            $stripeSubscription = StripeSubscription::retrieve($subscriptionId);

            $periodEnd = $stripeSubscription->current_period_end
                ? Carbon::createFromTimestamp($stripeSubscription->current_period_end)
                : now()->addMonth();

            $subscription->update([
                'stripe_subscription_id' => $stripeSubscription->id,
                'status'                 => 'active',
                'current_period_end'     => $periodEnd,
            ]);

            rescue(fn () => $this->email->sendPaymentConfirmed($user->email, $user->name));
        }

        return response()->json(['status' => 'active']);
    }

    public function cancel(): JsonResponse
    {
        $user         = auth()->user();
        $subscription = $user->subscription;

        if (! $subscription || ! $subscription->isActive()) {
            return response()->json(['message' => 'Nenhuma assinatura ativa encontrada.'], 422);
        }

        $client = new \Stripe\StripeClient(config('services.stripe.secret'));

        $stripeSubscription = $client->subscriptions->cancel(
            $subscription->stripe_subscription_id
        );

        $subscription->update([
            'status'             => 'canceled',
            'current_period_end' => $stripeSubscription->current_period_end
                ? Carbon::createFromTimestamp($stripeSubscription->current_period_end)
                : null,
        ]);

        rescue(fn () => $this->email->sendSubscriptionCanceled($user->email, $user->name));

        return response()->json(['message' => 'Assinatura cancelada com sucesso.']);
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $event = $secret
                ? Webhook::constructEvent($payload, $signature, $secret)
                : json_decode($payload);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Webhook inválido'], 400);
        }

        match ($event->type) {
            'checkout.session.completed'    => $this->onCheckoutCompleted($event->data->object),
            'customer.subscription.updated' => $this->onSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted' => $this->onSubscriptionDeleted($event->data->object),
            'invoice.payment_failed'        => $this->onPaymentFailed($event->data->object),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    private function onCheckoutCompleted(object $session): void
    {
        $subscription = Subscription::where('stripe_customer_id', $session->customer)->first();
        if (! $subscription) return;

        $stripeSubscription = StripeSubscription::retrieve($session->subscription);

        $subscription->update([
            'stripe_subscription_id' => $session->subscription,
            'status'                 => 'active',
            'current_period_end'     => $stripeSubscription->current_period_end
                ? Carbon::createFromTimestamp($stripeSubscription->current_period_end)
                : null,
        ]);
    }

    private function onSubscriptionUpdated(object $stripeSubscription): void
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();
        if (! $subscription) return;

        $status = match ($stripeSubscription->status) {
            'active'   => 'active',
            'past_due' => 'past_due',
            'canceled' => 'canceled',
            default    => $subscription->status,
        };

        $subscription->update([
            'status'             => $status,
            'current_period_end' => $stripeSubscription->current_period_end
                ? Carbon::createFromTimestamp($stripeSubscription->current_period_end)
                : null,
        ]);
    }

    private function onSubscriptionDeleted(object $stripeSubscription): void
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();
        $subscription?->update(['status' => 'canceled', 'current_period_end' => null]);
    }

    private function onPaymentFailed(object $invoice): void
    {
        $subscription = Subscription::where('stripe_customer_id', $invoice->customer)->first();
        if (! $subscription) return;

        $subscription->update(['status' => 'past_due']);

        $user = User::find($subscription->user_id);
        if ($user) {
            rescue(fn () => $this->email->sendPaymentFailed($user->email, $user->name));
        }
    }
}
