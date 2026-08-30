<?php

namespace App\Console\Commands;

use App\Models\QuizLead;
use App\Services\EmailService;
use Illuminate\Console\Command;

class SendQuizRecoveryEmails extends Command
{
    protected $signature = 'quiz:send-recovery-emails';
    protected $description = 'Envia e-mails de recuperação para leads do quiz que não viraram assinantes do Veekar';

    public function handle(EmailService $email): int
    {
        // 1º e-mail: ~1h depois de deixar o contato, ainda sem virar assinante
        $step1 = QuizLead::whereNull('user_id')
            ->where('recovery_step', 0)
            ->where('created_at', '<=', now()->subHour())
            ->get();

        foreach ($step1 as $lead) {
            rescue(fn () => $email->sendQuizRecoveryStep1($lead->email, $lead->name, $lead->id));
            $lead->update(['recovery_step' => 1, 'recovery_last_sent_at' => now()]);
        }

        // 2º e-mail: ~24h depois do 1º, ainda sem virar assinante
        $step2 = QuizLead::whereNull('user_id')
            ->where('recovery_step', 1)
            ->where('recovery_last_sent_at', '<=', now()->subDay())
            ->get();

        foreach ($step2 as $lead) {
            rescue(fn () => $email->sendQuizRecoveryStep2($lead->email, $lead->name, $lead->id));
            $lead->update(['recovery_step' => 2, 'recovery_last_sent_at' => now()]);
        }

        $this->info("Recuperação do quiz: {$step1->count()} 1º e-mail, {$step2->count()} 2º e-mail.");

        return 0;
    }
}
