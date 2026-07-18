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
        <p class="text-gray-500 text-sm mt-2">Última atualização: 15 de julho de 2026</p>
      </div>

      <div class="prose prose-gray max-w-none space-y-8 text-gray-700 text-sm leading-relaxed">

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">1. Aceitação dos Termos</h2>
          <p>
            Ao acessar a plataforma e aceitar os presentes Termos de Uso, o usuário manifesta sua concordância
            expressa com as condições de utilização da plataforma e com o tratamento de seus dados pessoais,
            nos termos da Política de Privacidade. A ausência de concordância com qualquer das disposições
            destes Termos ou da Política de Privacidade impede a continuidade do acesso e da utilização das
            funcionalidades disponibilizadas pela plataforma.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">2. Descrição da Plataforma</h2>
          <p>
            A plataforma consiste em um sistema de gestão automotiva desenvolvido para atender oficinas
            mecânicas, centros automotivos, empresas de estética automotiva, funilarias e demais
            estabelecimentos do setor automotivo, disponibilizado na modalidade Software como Serviço (SaaS).
          </p>
          <p class="mt-2">O sistema oferece funcionalidades destinadas à gestão administrativa, operacional e financeira do estabelecimento, incluindo, entre outras:</p>
          <ul class="list-disc pl-5 mt-2 space-y-1">
            <li>cadastro e gerenciamento de clientes, veículos e fornecedores;</li>
            <li>registro e consulta do histórico de atendimentos, serviços executados e manutenções realizadas;</li>
            <li>emissão e gerenciamento de orçamentos digitais, ordens de serviço e demais documentos relacionados às atividades do usuário;</li>
            <li>controle financeiro, incluindo contas a pagar, contas a receber, fluxo de caixa e emissão de relatórios gerenciais;</li>
            <li>organização de informações necessárias à gestão do negócio e acompanhamento de indicadores operacionais.</li>
          </ul>
          <p class="mt-2">
            As funcionalidades disponibilizadas poderão ser alteradas, ampliadas, substituídas ou descontinuadas,
            total ou parcialmente, a qualquer tempo, visando à atualização tecnológica, ao aprimoramento da
            plataforma, à manutenção da segurança da informação ou ao cumprimento de obrigações legais e
            regulatórias, sem que tais alterações impliquem direito à indenização, observado o respeito aos
            contratos vigentes.
          </p>
          <p class="mt-2">
            O tratamento de dados pessoais realizado por meio da plataforma observará os princípios, direitos
            e obrigações previstos na Lei nº 13.709/2018 (LGPD), sendo efetuado exclusivamente para finalidades
            legítimas, específicas e compatíveis com a prestação dos serviços contratados, conforme detalhado
            na Política de Privacidade.
          </p>
          <p class="mt-2">
            Na utilização da plataforma, o usuário poderá atuar como <strong>Controlador</strong> dos dados
            pessoais inseridos no sistema, cabendo-lhe assegurar que possui base legal para a coleta, utilização,
            armazenamento e demais operações de tratamento dos dados de seus clientes, colaboradores,
            fornecedores e terceiros, nos termos da LGPD. A plataforma atuará, quando aplicável, na qualidade de
            <strong>Operadora</strong>, realizando o tratamento dos dados pessoais de acordo com as instruções
            lícitas do usuário e em conformidade com a legislação vigente.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">3. Cadastro e Conta</h2>
          <p>
            Ao criar uma conta na plataforma, o usuário declara que as informações fornecidas são verdadeiras,
            completas e atualizadas, comprometendo-se a mantê-las corretas durante todo o período de utilização
            do sistema. Declara, ainda, possuir capacidade civil para aceitar os presentes Termos de Uso e
            assumir as obrigações deles decorrentes ou, quando atuar em nome de pessoa jurídica, possuir
            poderes para representá-la.
          </p>
          <p class="mt-2">
            O usuário é o único responsável pela guarda, confidencialidade e utilização de suas credenciais de
            acesso, incluindo login e senha, comprometendo-se a adotar as medidas necessárias para impedir o
            acesso por terceiros não autorizados.
          </p>
          <p class="mt-2">
            Na hipótese de perda, extravio, suspeita de comprometimento das credenciais ou identificação de
            qualquer acesso ou utilização não autorizada da conta, o usuário deverá comunicar imediatamente a
            plataforma pelo e-mail <a href="mailto:veekarofc@gmail.com" class="text-blue-600 hover:underline">veekarofc@gmail.com</a>,
            para que sejam adotadas as medidas de segurança cabíveis.
          </p>
          <p class="mt-2">
            O usuário responderá pelas atividades realizadas por meio de sua conta até a efetiva comunicação do
            incidente à plataforma, ressalvadas as hipóteses em que fique comprovada falha de segurança
            atribuível exclusivamente à plataforma.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">4. Planos, Pagamento e Cancelamento</h2>
          <p>
            A plataforma poderá disponibilizar ao usuário um período de teste gratuito, com funcionalidades e
            recursos limitados, permitindo o cadastro de até três clientes e três veículos, ou outros limites
            que venham a ser definidos pela plataforma.
          </p>
          <p class="mt-2">
            Encerrado o período de teste, a continuidade da utilização da plataforma dependerá da contratação
            de um dos planos de assinatura disponíveis. A ausência de contratação poderá resultar na limitação
            ou suspensão do acesso às funcionalidades da plataforma.
          </p>
          <p class="mt-2">
            Os pagamentos das assinaturas são processados por intermediador de pagamento independente,
            atualmente a <strong>Stripe, Inc.</strong>, responsável integral pelo processamento das transações
            financeiras. A plataforma não coleta, processa, armazena ou possui acesso aos dados completos de
            cartões de crédito ou de outros meios de pagamento utilizados pelo usuário.
          </p>
          <p class="mt-2">
            As assinaturas possuem cobrança recorrente, realizada conforme a periodicidade e o plano escolhido
            pelo usuário no momento da contratação. Ao aderir a um plano, o usuário autoriza a realização das
            cobranças recorrentes até que a assinatura seja cancelada.
          </p>
          <p class="mt-2">
            O cancelamento da assinatura poderá ser realizado a qualquer momento pela área de gerenciamento de
            planos disponibilizada na plataforma. Após o cancelamento, não serão efetuadas novas cobranças,
            permanecendo o acesso às funcionalidades contratadas até o término do período já pago, sem direito
            a reembolso proporcional, salvo nas hipóteses previstas na legislação aplicável ou expressamente
            previstas nestes Termos de Uso.
          </p>
          <p class="mt-2 bg-gray-50 rounded-xl p-4">
            <strong>Direito de arrependimento:</strong> nos termos do art. 49 do Código de Defesa do Consumidor,
            o usuário poderá exercer o direito de arrependimento no prazo de até 7 (sete) dias, contados da data
            de contratação de um plano pago, mediante solicitação pelo e-mail
            <a href="mailto:veekarofc@gmail.com" class="text-blue-600 hover:underline">veekarofc@gmail.com</a>,
            hipótese em que fará jus ao reembolso integral dos valores eventualmente pagos nesse período.
          </p>
          <p class="mt-2">
            A plataforma poderá alterar os valores, condições comerciais, funcionalidades, limites e modalidades
            dos planos de assinatura, mediante comunicação prévia ao usuário, observando-se que as alterações
            produzirão efeitos apenas para os ciclos de cobrança subsequentes, ressalvadas as hipóteses previstas
            em lei.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">5. Uso Permitido e Vedações</h2>
          <p>
            O usuário compromete-se a utilizar a plataforma exclusivamente para fins lícitos relacionados à
            gestão de oficinas mecânicas, centros automotivos, funilarias, empresas de estética automotiva e
            demais atividades do setor, observando a legislação vigente, os presentes Termos de Uso e a
            Política de Privacidade.
          </p>
          <p class="mt-2">
            É vedada a utilização da plataforma para qualquer finalidade ilícita, fraudulenta ou que possa
            comprometer sua segurança, integridade, disponibilidade ou funcionamento. O usuário não poderá:
          </p>
          <ul class="list-disc pl-5 mt-2 space-y-1">
            <li>compartilhar suas credenciais de acesso com terceiros não autorizados;</li>
            <li>acessar ou tentar acessar contas, sistemas ou dados de outros usuários sem autorização;</li>
            <li>reproduzir, copiar, modificar, comercializar ou revender, total ou parcialmente, a plataforma ou qualquer de suas funcionalidades sem autorização prévia e expressa;</li>
            <li>inserir, armazenar ou compartilhar informações falsas, maliciosas, fraudulentas ou que violem direitos de terceiros, incluindo propriedade intelectual, privacidade e proteção de dados pessoais.</li>
          </ul>
          <p class="mt-2">
            O descumprimento das disposições desta cláusula poderá resultar na suspensão ou no encerramento da
            conta do usuário, sem prejuízo da adoção das medidas administrativas, civis e penais cabíveis e da
            obrigação de reparar eventuais danos causados à plataforma ou a terceiros.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">6. Titularidade e Tratamento de Dados</h2>
          <p>
            Os dados cadastrados e armazenados na plataforma, incluindo informações de clientes, veículos,
            histórico de serviços, ordens de serviço, orçamentos, registros financeiros e demais informações
            inseridas pelo usuário, permanecem de sua titularidade ou da pessoa por ele legitimamente
            representada.
          </p>
          <p class="mt-2">
            A plataforma realizará o tratamento desses dados exclusivamente para a prestação dos serviços
            contratados, não os utilizando para finalidades comerciais próprias nem os compartilhando com
            terceiros, exceto quando necessário à execução dos serviços (provedores de hospedagem,
            infraestrutura, armazenamento em nuvem, processamento de pagamentos e demais fornecedores
            essenciais) ou quando houver obrigação legal, regulatória ou determinação de autoridade competente.
          </p>
          <p class="mt-2">
            O tratamento dos dados pessoais será realizado em conformidade com a LGPD e com a Política de
            Privacidade da plataforma, observadas as medidas técnicas e administrativas aptas a proteger os
            dados contra acessos não autorizados, perda, destruição, alteração ou qualquer forma de tratamento
            ilícito.
          </p>
          <p class="mt-2">
            Em caso de cancelamento da conta, o usuário poderá solicitar a exportação ou a exclusão definitiva
            de seus dados pelo e-mail <a href="mailto:veekarofc@gmail.com" class="text-blue-600 hover:underline">veekarofc@gmail.com</a>,
            ressalvadas as hipóteses em que a manutenção dos dados seja necessária para o cumprimento de
            obrigação legal ou regulatória, exercício regular de direitos em processos judiciais, administrativos
            ou arbitrais, ou outras hipóteses autorizadas pela legislação aplicável.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">7. Disponibilidade do Serviço</h2>
          <p>
            A plataforma envidará esforços razoáveis para manter seus serviços disponíveis de forma contínua,
            segura e estável, adotando medidas técnicas compatíveis com as boas práticas de mercado.
          </p>
          <p class="mt-2">
            Todavia, a plataforma não garante disponibilidade ininterrupta ou isenta de falhas, podendo ocorrer
            interrupções temporárias em razão de manutenções, atualizações, falhas de infraestrutura,
            indisponibilidade de serviços de terceiros, caso fortuito, força maior ou outros eventos alheios ao
            seu controle. As eventuais indisponibilidades temporárias não gerarão direito a indenização,
            abatimento, ressarcimento ou qualquer outra compensação ao usuário, ressalvadas as hipóteses
            expressamente previstas na legislação aplicável.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">8. Limitação de Responsabilidade</h2>
          <p>
            A plataforma não será responsável por danos indiretos, incidentais, emergentes, lucros cessantes,
            perda de oportunidades de negócio, perda de receitas, perda de dados ou quaisquer prejuízos
            decorrentes da utilização, da impossibilidade de utilização ou da utilização inadequada da
            plataforma, quando tais eventos não forem decorrentes de dolo ou culpa da própria plataforma.
          </p>
          <p class="mt-2">
            O usuário reconhece que a plataforma consiste em uma ferramenta de apoio à gestão, sendo de sua
            exclusiva responsabilidade a conferência, atualização, guarda e utilização das informações
            inseridas no sistema, bem como a realização de cópias de segurança quando necessárias.
          </p>
          <p class="mt-2">
            Ressalvadas as hipóteses de dolo, culpa grave ou demais situações em que a limitação de
            responsabilidade seja vedada pela legislação aplicável, a responsabilidade civil total da
            plataforma ficará limitada ao valor efetivamente pago pelo usuário à plataforma nos 3 (três) meses
            imediatamente anteriores ao evento que deu origem à reclamação.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">9. Alterações nos Termos</h2>
          <p>
            A plataforma poderá alterar, atualizar ou revisar os presentes Termos de Uso a qualquer tempo, com
            a finalidade de adequá-los a alterações legislativas, regulatórias, tecnológicas, operacionais ou à
            evolução dos serviços disponibilizados.
          </p>
          <p class="mt-2">
            As alterações que impliquem modificações relevantes nos direitos e obrigações do usuário serão
            comunicadas por e-mail, com antecedência mínima de 15 (quinze) dias da data prevista para sua
            entrada em vigor. A continuidade da utilização da plataforma após o decurso do prazo informado
            constituirá manifestação de concordância com os Termos de Uso atualizados. Caso o usuário não
            concorde com as alterações, deverá interromper a utilização da plataforma e, se aplicável, solicitar
            o cancelamento de sua conta ou assinatura antes da entrada em vigor das novas disposições.
          </p>
        </section>

        <section>
          <h2 class="text-base font-semibold text-gray-900 mb-3">10. Lei Aplicável e Foro</h2>
          <p>
            Os presentes Termos de Uso serão regidos, interpretados e executados de acordo com as leis da
            República Federativa do Brasil, especialmente o Código Civil, o Código de Defesa do Consumidor
            (quando aplicável), o Marco Civil da Internet (Lei nº 12.965/2014) e a Lei Geral de Proteção de
            Dados Pessoais (Lei nº 13.709/2018).
          </p>
          <p class="mt-2">
            Fica eleito o foro da Comarca de São Paulo/SP para dirimir quaisquer controvérsias oriundas destes
            Termos de Uso ou da utilização da plataforma, com renúncia expressa a qualquer outro, por mais
            privilegiado que seja, ressalvadas as hipóteses em que a legislação aplicável assegure ao usuário
            foro diverso de caráter obrigatório.
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
