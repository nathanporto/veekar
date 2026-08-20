<?php

namespace App\Http\Controllers;

use App\Models\QuizLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Customer as StripeCustomer;
use Stripe\Stripe;

class QuizController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'max:255'],
            'phone'            => ['required', 'string', 'max:20'],
            'business_type'    => ['nullable', 'string', 'max:100'],
            'cars_per_month'   => ['nullable', 'string', 'max:100'],
            'current_control'  => ['nullable', 'string', 'max:100'],
            'main_pain'        => ['nullable', 'string', 'max:100'],
            'accepted_terms'   => ['required', 'accepted'],
        ]);

        $lead = QuizLead::create([
            ...$validated,
            'accepted_terms_at' => now(),
        ]);

        return response()->json(['id' => $lead->id], 201);
    }

    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lead_id' => ['required', 'integer', 'exists:quiz_leads,id'],
        ]);

        $lead = QuizLead::findOrFail($validated['lead_id']);
        $lead->update(['chosen_path' => 'discount']);

        Stripe::setApiKey(config('services.stripe.secret'));

        $stripeCustomer = StripeCustomer::create([
            'email' => $lead->email,
            'name'  => $lead->name,
            'phone' => $lead->phone,
        ]);

        $session = CheckoutSession::create([
            'customer'             => $stripeCustomer->id,
            'payment_method_types' => ['card'],
            'line_items'           => [[
                'price'    => config('services.stripe.price_id'),
                'quantity' => 1,
            ]],
            'discounts'  => [[
                'coupon' => config('services.stripe.quiz_coupon_id'),
            ]],
            'mode'        => 'subscription',
            'metadata'    => ['quiz_lead_id' => (string) $lead->id],
            'success_url' => config('services.stripe.frontend_url') . '/quiz/sucesso',
            'cancel_url'  => config('services.stripe.frontend_url') . '/quiz/oferta-kit?ref=checkout&lead_id=' . $lead->id,
        ]);

        return response()->json(['url' => $session->url]);
    }
}
