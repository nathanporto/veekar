<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class EmailService
{
    private string $from;
    private string $fromName;

    public function __construct()
    {
        $this->from     = config('mail.from.address', 'veekarofc@gmail.com');
        $this->fromName = config('mail.from.name', 'Veekar');
    }

    public function sendWelcome(string $email, string $name): void
    {
        $this->send(
            to: $email,
            subject: 'Bem-vindo ao Veekar! 🚗',
            html: $this->template('Olá, ' . $this->firstName($name) . '!', [
                'Sua conta foi criada com sucesso.',
                'Você está no <strong>período gratuito</strong> — pode cadastrar até 3 clientes e 3 veículos para testar o sistema.',
                'Quando estiver pronto para usar sem limites, assine o Veekar Premium por <strong>R$ 49,90/mês</strong>.',
            ], 'Acessar o Veekar', config('services.stripe.frontend_url') . '/dashboard')
        );
    }

    public function sendPaymentConfirmed(string $email, string $name): void
    {
        $this->send(
            to: $email,
            subject: 'Pagamento confirmado — Veekar Premium ativo!',
            html: $this->template('Assinatura ativada!', [
                'Seu pagamento foi confirmado com sucesso.',
                'Sua conta agora tem acesso <strong>ilimitado</strong> a clientes, veículos e atendimentos.',
                'Obrigado por assinar o Veekar. Qualquer dúvida, responda este e-mail.',
            ], 'Ir para o Dashboard', config('services.stripe.frontend_url') . '/dashboard')
        );
    }

    public function sendSubscriptionCanceled(string $email, string $name): void
    {
        $this->send(
            to: $email,
            subject: 'Sua assinatura foi cancelada',
            html: $this->template('Assinatura cancelada', [
                'Recebemos sua solicitação de cancelamento.',
                'Seus dados continuam <strong>preservados</strong> — você pode reativar a assinatura a qualquer momento.',
                'Se foi um engano ou tiver alguma dúvida, entre em contato com o suporte.',
            ], 'Gerenciar Plano', config('services.stripe.frontend_url') . '/assinatura', '#dc2626')
        );
    }

    public function sendPaymentFailed(string $email, string $name): void
    {
        $this->send(
            to: $email,
            subject: 'Problema no pagamento da sua assinatura',
            html: $this->template('Falha no pagamento', [
                'Não conseguimos processar o pagamento da sua assinatura.',
                'Verifique se o cartão está válido e com limite disponível, e tente novamente.',
                'Se o problema persistir, entre em contato com o suporte.',
            ], 'Atualizar forma de pagamento', config('services.stripe.frontend_url') . '/assinatura', '#f59e0b')
        );
    }

    public function sendPasswordReset(string $email, string $name, string $token): void
    {
        $resetUrl = config('services.stripe.frontend_url') . '/redefinir-senha?token=' . $token . '&email=' . urlencode($email);

        $this->send(
            to: $email,
            subject: 'Redefinição de senha — Veekar',
            html: $this->template('Redefinir sua senha', [
                'Recebemos uma solicitação para redefinir a senha da sua conta Veekar.',
                'Clique no botão abaixo para criar uma nova senha. O link expira em <strong>60 minutos</strong>.',
                'Se você não solicitou a redefinição, ignore este e-mail. Sua senha permanece a mesma.',
            ], 'Redefinir senha', $resetUrl)
        );
    }

    public function sendEmailVerification(string $email, string $name, string $token): void
    {
        $verifyUrl = config('services.stripe.frontend_url') . '/verificar-email?token=' . $token . '&email=' . urlencode($email);

        $this->send(
            to: $email,
            subject: 'Confirme seu e-mail — Veekar',
            html: $this->template('Confirme seu e-mail', [
                'Olá, ' . $this->firstName($name) . '! Sua conta foi criada com sucesso.',
                'Clique no botão abaixo para confirmar seu e-mail e ativar sua conta.',
                'O link expira em <strong>24 horas</strong>. Se você não criou uma conta no Veekar, ignore este e-mail.',
            ], 'Confirmar e-mail', $verifyUrl)
        );
    }

    private function send(string $to, string $subject, string $html): void
    {
        Mail::html($html, function ($message) use ($to, $subject) {
            $message->to($to)
                    ->subject($subject)
                    ->from($this->from, $this->fromName);
        });
    }

    private function firstName(string $name): string
    {
        return explode(' ', trim($name))[0];
    }

    private function template(string $heading, array $paragraphs, string $btnLabel, string $btnUrl, string $btnColor = '#2563eb'): string
    {
        $ps   = implode('', array_map(fn ($p) => "<p style='margin:0 0 12px;color:#374151;font-size:15px;line-height:1.6;'>{$p}</p>", $paragraphs));
        $year = date('Y');

        return <<<HTML
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
        <body style="margin:0;padding:0;background:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:40px 16px;">
            <tr><td align="center">
              <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">

                <!-- Logo -->
                <tr><td align="center" style="padding-bottom:24px;">
                  <table cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="background:#2563eb;border-radius:10px;width:36px;height:36px;text-align:center;vertical-align:middle;">
                        <span style="color:#fff;font-size:20px;line-height:36px;">&#9675;</span>
                      </td>
                      <td style="padding-left:10px;font-size:20px;font-weight:700;color:#111827;vertical-align:middle;">Veekar</td>
                    </tr>
                  </table>
                </td></tr>

                <!-- Card -->
                <tr><td style="background:#fff;border-radius:16px;padding:36px 32px;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                  <h1 style="margin:0 0 20px;font-size:22px;font-weight:700;color:#111827;">{$heading}</h1>
                  {$ps}
                  <div style="margin-top:28px;text-align:center;">
                    <a href="{$btnUrl}" style="display:inline-block;background:{$btnColor};color:#fff;text-decoration:none;font-weight:600;font-size:15px;padding:13px 32px;border-radius:10px;">{$btnLabel}</a>
                  </div>
                </td></tr>

                <!-- Footer -->
                <tr><td align="center" style="padding-top:24px;">
                  <p style="margin:0;font-size:12px;color:#9ca3af;">© {$year} Veekar · <a href="mailto:veekarofc@gmail.com" style="color:#9ca3af;">veekarofc@gmail.com</a></p>
                </td></tr>

              </table>
            </td></tr>
          </table>
        </body>
        </html>
        HTML;
    }
}
