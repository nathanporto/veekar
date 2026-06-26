<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Console\Command;

class ActivateSubscription extends Command
{
    protected $signature = 'subscription:activate {email}';
    protected $description = 'Ativa a assinatura de um usuário pelo e-mail';

    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("Usuário não encontrado: {$email}");
            return 1;
        }

        $subscription = Subscription::where('user_id', $user->id)->first();

        if (! $subscription) {
            Subscription::create([
                'user_id'    => $user->id,
                'status'     => 'active',
                'plan'       => 'premium',
                'started_at' => now(),
            ]);
            $this->info("Assinatura criada e ativada para {$email}");
            return 0;
        }

        $subscription->update(['status' => 'active']);
        $this->info("Assinatura ativada para {$email} (era: {$subscription->getOriginal('status')})");

        return 0;
    }
}
