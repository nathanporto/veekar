<script setup lang="ts">
definePageMeta({ layout: 'landing' })
</script>

<template>
  <div class="min-h-screen bg-white">
    <!-- Header -->
    <header class="border-b border-gray-100 py-4">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 flex items-center gap-3">
        <NuxtLink to="/" class="flex items-center gap-2">
          <div class="w-7 h-7 bg-blue-600 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2.5-.001M13 16H9m4 0h3m3-10H6m0 0l2-3h7l2 3" />
            </svg>
          </div>
          <span class="font-bold text-gray-900">Veekar</span>
        </NuxtLink>
      </div>
    </header>

    <!-- Conteúdo -->
    <main class="max-w-3xl mx-auto px-4 sm:px-6 py-12">
      <div class="mb-10">
        <h1 class="text-3xl font-bold text-gray-900">Termos de Uso</h1>
        <p class="text-gray-500 text-sm mt-2">Última atualização: 1º de julho de 2025</p>
      </div>

      <div class="prose prose-gray max-w-none space-y-8 text-gray-700 text-sm leading-relaxed">

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">1. Aceitação dos Termos</h2>
          <p>
            Ao criar uma conta e utilizar o Veekar, você concorda integralmente com estes Termos de Uso.
            Caso não concorde com qualquer parte destes termos, não utilize o serviço.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">2. Descrição do Serviço</h2>
          <p>
            O Veekar é um sistema de gestão automotiva voltado para oficinas mecânicas, estéticas automotivas,
            funilarias e centros automotivos. O serviço permite o cadastro de clientes, veículos, histórico
            de serviços, geração de orçamentos digitais e emissão de relatórios financeiros.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">3. Cadastro e Conta</h2>
          <p>Ao criar uma conta no Veekar, você declara que:</p>
          <ul class="list-disc pl-5 mt-2 space-y-1">
            <li>As informações fornecidas no cadastro são verdadeiras e atualizadas;</li>
            <li>Você tem capacidade legal para aceitar estes termos;</li>
            <li>É responsável por manter a confidencialidade de sua senha;</li>
            <li>Notificará imediatamente o Veekar em caso de uso não autorizado de sua conta.</li>
          </ul>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">4. Planos e Pagamento</h2>
          <p>
            O Veekar oferece um período de testes gratuito com funcionalidades limitadas (até 3 clientes e
            3 veículos). Após o período de testes, é necessário contratar um plano pago para continuar
            utilizando o sistema sem limitações.
          </p>
          <p class="mt-2">
            Os pagamentos são processados pela Stripe, Inc., plataforma terceirizada de pagamentos.
            O Veekar não armazena dados de cartão de crédito. A cobrança é recorrente conforme o plano
            escolhido, e o cancelamento pode ser realizado a qualquer momento pela área de plano do sistema.
          </p>
          <p class="mt-2">
            Em caso de cancelamento, o acesso permanece ativo até o fim do período já pago.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">5. Uso Permitido</h2>
          <p>Você concorda em utilizar o Veekar apenas para fins legítimos de gestão do seu negócio automotivo.
          É expressamente proibido:</p>
          <ul class="list-disc pl-5 mt-2 space-y-1">
            <li>Utilizar o sistema para fins ilegais ou fraudulentos;</li>
            <li>Compartilhar credenciais de acesso com terceiros não autorizados;</li>
            <li>Tentar acessar sistemas, dados ou contas de outros usuários;</li>
            <li>Reproduzir, copiar ou revender qualquer parte do serviço sem autorização expressa;</li>
            <li>Inserir dados falsos, maliciosos ou que violem direitos de terceiros.</li>
          </ul>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">6. Propriedade dos Dados</h2>
          <p>
            Os dados inseridos por você no Veekar (clientes, veículos, histórico de serviços etc.)
            são de sua propriedade. O Veekar não utilizará esses dados para fins comerciais próprios,
            nem os compartilhará com terceiros, exceto quando exigido por lei ou para prestação do serviço
            (hospedagem em servidores, por exemplo).
          </p>
          <p class="mt-2">
            Ao cancelar a conta, você poderá solicitar a exportação ou exclusão definitiva dos dados
            mediante contato pelo e-mail <a href="mailto:veekarofc@gmail.com" class="text-blue-600 hover:underline">veekarofc@gmail.com</a>.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">7. Disponibilidade do Serviço</h2>
          <p>
            O Veekar envidaráesforços razoáveis para manter o sistema disponível de forma contínua.
            No entanto, não garante disponibilidade ininterrupta. Manutenções programadas ou
            incidentes técnicos podem ocasionar indisponibilidade temporária, sem direito a ressarcimento.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">8. Limitação de Responsabilidade</h2>
          <p>
            O Veekar não se responsabiliza por danos indiretos, incidentais ou consequentes decorrentes
            do uso ou impossibilidade de uso do serviço, incluindo perda de dados, lucros cessantes ou
            interrupção de negócios. A responsabilidade total do Veekar fica limitada ao valor pago
            pelo usuário nos últimos 3 meses de serviço.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">9. Alterações nos Termos</h2>
          <p>
            O Veekar pode atualizar estes Termos de Uso periodicamente. Alterações significativas
            serão comunicadas por e-mail com pelo menos 15 dias de antecedência. O uso continuado do
            serviço após esse prazo implica aceitação dos novos termos.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">10. Lei Aplicável e Foro</h2>
          <p>
            Estes termos são regidos pelas leis brasileiras. Eventuais disputas serão submetidas ao
            foro da comarca de São Paulo/SP, com renúncia expressa a qualquer outro, por mais
            privilegiado que seja.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">11. Contato</h2>
          <p>
            Em caso de dúvidas sobre estes Termos de Uso, entre em contato pelo e-mail
            <a href="mailto:veekarofc@gmail.com" class="text-blue-600 hover:underline">veekarofc@gmail.com</a>.
          </p>
        </section>

      </div>
    </main>

    <!-- Footer simples -->
    <footer class="border-t border-gray-100 py-6 mt-12">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-gray-400">
        <p>© {{ new Date().getFullYear() }} Veekar. Todos os direitos reservados.</p>
        <div class="flex gap-4">
          <NuxtLink to="/privacidade" class="hover:text-gray-600 transition-colors">Política de Privacidade</NuxtLink>
          <NuxtLink to="/" class="hover:text-gray-600 transition-colors">Voltar ao site</NuxtLink>
        </div>
      </div>
    </footer>
  </div>
</template>
