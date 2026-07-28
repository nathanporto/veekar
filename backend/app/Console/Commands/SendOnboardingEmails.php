<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\EmailService;
use Illuminate\Console\Command;

class SendOnboardingEmails extends Command
{
    protected $signature = 'onboarding:send';
    protected $description = 'Envia o e-mail de onboarding para usuários cadastrados há ~1 dia';

    public function handle(EmailService $email): int
    {
        $users = User::whereNotNull('email_verified_at')
            ->whereNull('onboarding_email_sent_at')
            ->where('created_at', '<=', now()->subDay())
            ->get();

        foreach ($users as $user) {
            rescue(fn () => $email->sendOnboarding($user->email, $user->name));
            $user->update(['onboarding_email_sent_at' => now()]);
        }

        $this->info("Onboarding enviado para {$users->count()} usuário(s).");

        return 0;
    }
}
