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
        <h1 class="text-3xl font-bold text-gray-900">Política de Privacidade</h1>
        <p class="text-gray-500 text-sm mt-2">Última atualização: 1º de julho de 2026</p>
        <p class="text-gray-500 text-sm mt-1">Em conformidade com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018 — LGPD)</p>
      </div>

      <div class="space-y-8 text-gray-700 text-sm leading-relaxed">

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">1. Quem Somos</h2>
          <p>
            O Veekar é um sistema de gestão automotiva operado por pessoa física/jurídica com
            contato disponível pelo e-mail
            <a href="mailto:veekarofc@gmail.com" class="text-blue-600 hover:underline">veekarofc@gmail.com</a>.
            Atuamos como <strong>controlador</strong> dos dados pessoais coletados em nossa plataforma,
            nos termos da LGPD.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">2. Dados que Coletamos</h2>
          <p class="mb-3">Coletamos dois tipos de dados:</p>

          <div class="bg-gray-50 rounded-xl p-4 mb-3">
            <p class="font-medium text-gray-800 mb-2">Dados dos usuários do sistema (donos de oficina):</p>
            <ul class="list-disc pl-5 space-y-1">
              <li>Nome completo</li>
              <li>Nome da empresa</li>
              <li>CPF ou CNPJ</li>
              <li>Endereço de e-mail</li>
              <li>Dados de pagamento (processados pela Stripe — não armazenamos dados de cartão)</li>
            </ul>
          </div>

          <div class="bg-gray-50 rounded-xl p-4">
            <p class="font-medium text-gray-800 mb-2">Dados inseridos pelos usuários sobre seus clientes:</p>
            <ul class="list-disc pl-5 space-y-1">
              <li>Nome do cliente da oficina</li>
              <li>CPF do cliente</li>
              <li>Telefone e e-mail</li>
              <li>Dados dos veículos (marca, modelo, ano, placa, quilometragem)</li>
              <li>Histórico de serviços realizados</li>
            </ul>
            <p class="mt-2 text-gray-500 text-xs">
              Esses dados pertencem ao usuário que os inseriu. O Veekar atua como <strong>operador</strong>
              em relação a eles, processando-os exclusivamente para prestação do serviço contratado.
            </p>
          </div>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">3. Como Usamos os Dados</h2>
          <p>Utilizamos os dados coletados para:</p>
          <ul class="list-disc pl-5 mt-2 space-y-1">
            <li>Criar e gerenciar sua conta no sistema;</li>
            <li>Processar pagamentos e gerenciar assinaturas;</li>
            <li>Prestar suporte técnico;</li>
            <li>Enviar comunicações relacionadas ao serviço (confirmações de pagamento, avisos de vencimento);</li>
            <li>Melhorar a plataforma com base no uso geral do sistema;</li>
            <li>Cumprir obrigações legais e regulatórias.</li>
          </ul>
          <p class="mt-3">
            <strong>Não utilizamos seus dados para venda a terceiros, publicidade direcionada ou qualquer
            finalidade não descrita nesta política.</strong>
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">4. Base Legal para Tratamento</h2>
          <p>O tratamento dos seus dados se baseia nas seguintes hipóteses previstas na LGPD:</p>
          <ul class="list-disc pl-5 mt-2 space-y-1">
            <li><strong>Consentimento</strong> — coletado no momento do cadastro;</li>
            <li><strong>Execução de contrato</strong> — para prestar o serviço contratado;</li>
            <li><strong>Cumprimento de obrigação legal</strong> — quando exigido por lei;</li>
            <li><strong>Legítimo interesse</strong> — para segurança e melhoria do serviço.</li>
          </ul>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">5. Compartilhamento de Dados</h2>
          <p>Compartilhamos dados apenas com os seguintes parceiros necessários para operação do serviço:</p>

          <div class="mt-3 space-y-3">
            <div class="border border-gray-200 rounded-xl p-4">
              <p class="font-medium text-gray-800">Stripe, Inc.</p>
              <p class="text-gray-500 text-xs mt-1">Processamento de pagamentos. Compartilhamos nome, e-mail e dados de faturamento. A Stripe possui sua própria política de privacidade: <a href="https://stripe.com/br/privacy" target="_blank" class="text-blue-600 hover:underline">stripe.com/br/privacy</a></p>
            </div>
            <div class="border border-gray-200 rounded-xl p-4">
              <p class="font-medium text-gray-800">Railway</p>
              <p class="text-gray-500 text-xs mt-1">Hospedagem do servidor e banco de dados. Os dados ficam armazenados em servidores seguros.</p>
            </div>
            <div class="border border-gray-200 rounded-xl p-4">
              <p class="font-medium text-gray-800">Vercel</p>
              <p class="text-gray-500 text-xs mt-1">Hospedagem da interface web (frontend). Não processa dados pessoais diretamente.</p>
            </div>
          </div>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">6. Armazenamento e Segurança</h2>
          <p>
            Seus dados são armazenados em servidores seguros com criptografia em trânsito (HTTPS/TLS).
            As senhas são armazenadas com hash criptográfico e nunca em texto puro. Adotamos medidas
            técnicas e organizacionais para proteger seus dados contra acessos não autorizados,
            perda ou destruição.
          </p>
          <p class="mt-2">
            Os dados são mantidos enquanto sua conta estiver ativa. Após o cancelamento, os dados
            são retidos por até 90 dias antes da exclusão definitiva, salvo obrigação legal em contrário.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">7. Seus Direitos (LGPD)</h2>
          <p>Como titular de dados, você tem direito a:</p>
          <ul class="list-disc pl-5 mt-2 space-y-1">
            <li><strong>Acesso</strong> — saber quais dados temos sobre você;</li>
            <li><strong>Correção</strong> — corrigir dados incompletos ou desatualizados;</li>
            <li><strong>Eliminação</strong> — solicitar a exclusão dos seus dados;</li>
            <li><strong>Portabilidade</strong> — receber seus dados em formato estruturado;</li>
            <li><strong>Revogação do consentimento</strong> — a qualquer momento;</li>
            <li><strong>Oposição</strong> — contestar o tratamento em caso de descumprimento da LGPD.</li>
          </ul>
          <p class="mt-3">
            Para exercer qualquer um desses direitos, entre em contato pelo e-mail
            <a href="mailto:veekarofc@gmail.com" class="text-blue-600 hover:underline">veekarofc@gmail.com</a>.
            Responderemos em até 15 dias úteis.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">8. Cookies</h2>
          <p>
            O Veekar utiliza apenas cookies estritamente necessários para funcionamento do sistema
            (autenticação e preferências de sessão). Não utilizamos cookies de rastreamento ou
            publicidade de terceiros.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">9. Menores de Idade</h2>
          <p>
            O Veekar não é destinado a menores de 18 anos. Não coletamos intencionalmente dados
            de menores. Caso identifiquemos tal situação, excluiremos os dados imediatamente.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">10. Alterações nesta Política</h2>
          <p>
            Esta Política de Privacidade pode ser atualizada periodicamente. Comunicaremos alterações
            relevantes por e-mail com antecedência mínima de 15 dias. Recomendamos que revise esta
            página periodicamente.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">11. Contato e Canal de Privacidade</h2>
          <p>
            Para dúvidas, solicitações ou reclamações relacionadas à privacidade e proteção de dados:
          </p>
          <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mt-3">
            <p class="font-medium text-gray-800">Veekar — Canal de Privacidade</p>
            <p class="text-gray-600 text-sm mt-1">
              E-mail: <a href="mailto:veekarofc@gmail.com" class="text-blue-600 hover:underline">veekarofc@gmail.com</a>
            </p>
            <p class="text-gray-500 text-xs mt-2">Prazo de resposta: até 15 dias úteis</p>
          </div>
        </section>

      </div>
    </main>

    <!-- Footer simples -->
    <footer class="border-t border-gray-100 py-6 mt-12">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-gray-400">
        <p>© {{ new Date().getFullYear() }} Veekar. Todos os direitos reservados.</p>
        <div class="flex gap-4">
          <NuxtLink to="/termos" class="hover:text-gray-600 transition-colors">Termos de Uso</NuxtLink>
          <NuxtLink to="/" class="hover:text-gray-600 transition-colors">Voltar ao site</NuxtLink>
        </div>
      </div>
    </footer>
  </div>
</template>
