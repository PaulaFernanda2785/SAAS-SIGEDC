**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Especificação de Requisitos Funcionais  
**Objetivo:** definir, organizar e formalizar as funções que o sistema deverá executar em suas áreas pública, administrativa e operacional.

## 1. Finalidade do documento

Este documento estabelece os requisitos funcionais do SIGERD, contemplando as funcionalidades essenciais para:

- gestão de risco por meio de planos de contingência;
- gerenciamento de acidentes e desastres com lógica compatível com SCI/SCO;
- cadastro e administração de contas, órgãos, unidades e usuários;
- comercialização do produto em modelo SaaS com planos, assinaturas, faturamento e controle de módulos;
- operação em três camadas distintas: área pública, área administrativa SaaS e área interna operacional.

## 2. Escopo funcional do sistema

O SIGERD possui duas frentes funcionais independentes, porém complementares:

**Frente 1 — Gestão do risco**  
Permite elaborar, revisar, consultar e operacionalizar planos de contingência digitais, incluindo caracterização territorial, análise de riscos, cenários de desastre, níveis de ativação, recursos, comunicação, procedimentos, rotas, assistência, simulados, revisão e núcleo operacional CSI/SCO vinculado ao plano.

**Frente 2 — Gerenciamento de desastres**  
Permite registrar ocorrências/incidentes, estruturar comando, staff, planejamento, operações, recursos, instalações, comunicações, segurança, informação pública, ligação interinstitucional, administração/finanças, períodos operacionais, registros e desmobilização.

Além disso, o sistema contempla:

- gestão institucional e de usuários;
- gestão comercial e contratual do SaaS;
- área pública de apresentação e conversão comercial.

## 3. Perfis funcionais envolvidos

Os requisitos funcionais consideram, no mínimo, os seguintes perfis de atuação no sistema:

- visitante da área pública;
- conta contratante;
- administrador SaaS;
- administrador de órgão;
- gestor;
- coordenador;
- analista;
- operador;
- leitor;
- financeiro;
- suporte;
- convidado.

## 4. Premissas funcionais

O sistema deverá obedecer às seguintes premissas:

1. a gestão de risco e o gerenciamento de desastre são módulos independentes;
2. o centro do modelo institucional não é apenas o usuário, mas a conta contratante e o órgão operador;
3. o acesso às funcionalidades deverá respeitar plano contratado, módulo liberado, perfil e escopo de acesso;
4. a navegação e o comportamento do sistema deverão variar conforme a natureza da área: pública, administrativa ou operacional;
5. os blocos operacionais do CSI/SCO e do SCI/SCO não deverão ser tratados como texto solto, mas como núcleo estruturado com rastreabilidade entre estrutura, pessoas, instalações, períodos e registros.

## 5. Organização macro dos requisitos funcionais

Os requisitos foram organizados pelos seguintes macrogrupos:

- RF-001 a RF-019 — Área pública comercial;
- RF-020 a RF-049 — Administração SaaS;
- RF-050 a RF-119 — Gestão institucional e acesso;
- RF-120 a RF-199 — Gestão do risco / Plano de contingência;
- RF-200 a RF-289 — Gerenciamento de desastres / SCI-SCO;
- RF-290 a RF-329 — Relatórios, filtros, exportações e painéis;
- RF-330 a RF-349 — Segurança operacional da conta e do usuário;
- RF-350 a RF-369 — Auditoria e conformidade;
- RF-370 a RF-389 — Navegação, estado operacional e usabilidade funcional.

## 6. Requisitos funcionais — Área pública comercial

**RF-001.** O sistema deverá disponibilizar página pública institucional com apresentação do produto.  
**RF-002.** O cabeçalho público deverá exibir logomarca, menu principal e botões de conversão.  
**RF-003.** O menu público deverá conter, no mínimo: Início, Solução, Funcionalidades, Planos, Demonstração, Sobre o sistema, Contato e Acessar plataforma.  
**RF-004.** A página inicial deverá apresentar seção hero com proposta de valor, CTA principal, CTA secundário e imagem principal do sistema.  
**RF-005.** A área pública deverá apresentar seção de dores do problema atual, destacando gestão descentralizada, comunicação fragmentada, baixa rastreabilidade, dificuldade de acompanhamento, relatórios manuais e falta de padronização no SCI/SCO.  
**RF-006.** A área pública deverá apresentar seção de solução com cards dos principais módulos do sistema.  
**RF-007.** O sistema deverá apresentar demonstração visual resumida dos módulos.  
**RF-008.** O sistema deverá exibir benefícios institucionais da plataforma.  
**RF-009.** O sistema deverá exibir planos de assinatura com nome, público-alvo, preço mensal, preço anual, limites e módulos liberados.  
**RF-010.** O sistema deverá exibir preço anual com desconto comercial padrão.  
**RF-011.** O sistema deverá apresentar seção de prova de valor, incluindo comparativos, indicadores ou depoimentos.  
**RF-012.** A página pública deverá possuir chamada final para ação com rotas para demonstração, consultoria e acesso.  
**RF-013.** O rodapé público deverá exibir descrição institucional, links rápidos, política de privacidade, termos de uso e contato.  
**RF-014.** O sistema deverá possuir página específica de planos com comparativo completo entre ofertas.  
**RF-015.** O sistema deverá possuir página específica para solicitação de demonstração com formulário estruturado.  
**RF-016.** O sistema deverá possuir página de contato com formulário e canais comerciais.  
**RF-017.** O sistema deverá possuir página de login/acesso.  
**RF-018.** A página de login deverá permitir redirecionamento distinto para área administrativa e área do cliente, quando aplicável.  
**RF-019.** O sistema deverá registrar leads oriundos dos formulários públicos para tratamento comercial.

## 7. Requisitos funcionais — Administração SaaS

**RF-020.** O sistema deverá disponibilizar área administrativa do SaaS para gestão do negócio.  
**RF-021.** A área administrativa deverá possuir dashboard com visão geral das assinaturas.  
**RF-022.** O dashboard administrativo deverá exibir assinaturas ativas, testes, inadimplência, receita mensal, receita anual, cancelamentos e novos cadastros.  
**RF-023.** O dashboard administrativo deverá exibir gráficos de evolução de assinaturas, receita por mês, distribuição por plano, inadimplência e uso por módulo.  
**RF-024.** O sistema deverá permitir cadastrar, editar, ativar, suspender e cancelar contas contratantes.  
**RF-025.** O sistema deverá permitir cadastrar, editar e consultar órgãos/instituições vinculados a uma conta.  
**RF-026.** O sistema deverá permitir cadastrar, editar e consultar unidades e subunidades vinculadas aos órgãos.  
**RF-027.** O sistema deverá permitir cadastrar, editar e consultar planos de assinatura.  
**RF-028.** O sistema deverá permitir definir limites por plano, inclusive usuários, órgãos, unidades, ocorrências, plancons e armazenamento.  
**RF-029.** O sistema deverá permitir associar módulos liberados por plano.  
**RF-030.** O sistema deverá permitir criar e manter assinaturas ativas vinculando conta contratante ao plano.  
**RF-031.** O sistema deverá controlar ciclo de cobrança, data de início, data de fim, valor contratado, desconto e renovação automática da assinatura.  
**RF-032.** O sistema deverá suportar período de teste gratuito quando aplicável.  
**RF-033.** O sistema deverá registrar aceite de termos e aceite LGPD por assinatura.  
**RF-034.** O sistema deverá permitir gerar, registrar e acompanhar faturas.  
**RF-035.** O sistema deverá registrar situação de pagamento por fatura.  
**RF-036.** O sistema deverá permitir anexar comprovantes e documentos fiscais às faturas.  
**RF-037.** O sistema deverá permitir liberar ou bloquear módulos contratados individualmente na assinatura.  
**RF-038.** O sistema deverá permitir consultar histórico financeiro por conta.  
**RF-039.** O sistema deverá permitir filtrar assinaturas por plano, status, período, estado, órgão e inadimplência.  
**RF-040.** O sistema deverá permitir filtrar faturas por status, competência, forma de pagamento, conta e plano.  
**RF-041.** O sistema deverá permitir visualizar em abas os dados da conta, órgão, unidades, usuários, assinatura, histórico financeiro e logs.  
**RF-042.** O sistema deverá permitir bloquear automaticamente módulos ou acessos em condição de inadimplência, conforme regra contratual.  
**RF-043.** O sistema deverá permitir cadastrar e manter termos, políticas e versões legais.  
**RF-044.** O sistema deverá registrar IP, data e responsável pelo aceite legal.  
**RF-045.** O sistema deverá manter logs de administração e eventos críticos do SaaS.  
**RF-046.** O sistema deverá permitir gestão de usuários administrativos internos do SaaS.  
**RF-047.** O sistema deverá disponibilizar relatórios administrativos.  
**RF-048.** O sistema deverá possuir área de configurações gerais do negócio.  
**RF-049.** O sistema deverá permitir auditoria de ações administrativas.

## 8. Requisitos funcionais — Gestão institucional, usuários e permissões

**RF-050.** O sistema deverá permitir cadastrar conta contratante com dados jurídicos, comerciais, endereço, contato e responsável contratual.  
**RF-051.** O sistema deverá permitir classificar a conta por tipo de pessoa ou natureza institucional.  
**RF-052.** O sistema deverá permitir cadastrar órgão ou instituição operacional distinta da conta contratante.  
**RF-053.** O sistema deverá permitir classificar órgão por tipo de instituição, esfera administrativa e nível de atuação.  
**RF-054.** O sistema deverá permitir cadastrar múltiplas unidades ou subunidades por órgão.  
**RF-055.** O sistema deverá permitir cadastrar hierarquia entre unidades por referência à unidade superior.  
**RF-056.** O sistema deverá permitir cadastrar usuários vinculando-os à conta, órgão e unidade.  
**RF-057.** O sistema deverá armazenar dados pessoais, institucionais e operacionais mínimos do usuário.  
**RF-058.** O sistema deverá permitir definir função sistêmica do usuário.  
**RF-059.** O sistema deverá controlar status do usuário.  
**RF-060.** O sistema deverá permitir política de troca obrigatória de senha.  
**RF-061.** O sistema deverá permitir habilitação de autenticação em dois fatores.  
**RF-062.** O sistema deverá permitir vincular assinatura digital e certificado, quando contratado.  
**RF-063.** O sistema deverá permitir cadastro e manutenção de perfis.  
**RF-064.** O sistema deverá permitir atribuir permissões por perfil.  
**RF-065.** O sistema deverá permitir associar múltiplos perfis a um usuário, quando necessário.  
**RF-066.** O sistema deverá controlar escopo de acesso por perfil.  
**RF-067.** O sistema deverá controlar acesso por módulo, operação e nível hierárquico.  
**RF-068.** O sistema deverá restringir funcionalidades conforme limites do plano contratado.  
**RF-069.** O sistema deverá impedir criação de usuários, unidades ou órgãos acima do limite do plano.  
**RF-070.** O sistema deverá permitir cadastro institucional em interface por abas.  
**RF-071.** O sistema deverá permitir visualização, edição e consulta dos vínculos institucionais do usuário.  
**RF-072.** O sistema deverá permitir visualizar assinatura vinculada à conta ou ao órgão.  
**RF-073.** O sistema deverá permitir consulta aos módulos liberados por assinatura.  
**RF-074.** O sistema deverá permitir diferenciar visualmente perfis administrativos e operacionais.  
**RF-075.** O sistema deverá possuir mecanismo de bloqueio de login para usuário inativo, bloqueado ou excluído.  
**RF-076.** O sistema deverá registrar último login do usuário.  
**RF-077.** O sistema deverá registrar eventos relevantes de acesso e administração do usuário.  
**RF-078.** O sistema deverá permitir atualização de dados cadastrais institucionais.  
**RF-079.** O sistema deverá permitir upload de foto de perfil, logotipos e brasões institucionais.  
**RF-080.** O sistema deverá permitir segregação entre conta contratante e órgão operador em todas as rotinas.

## 9. Requisitos funcionais — Gestão do risco / Plano de contingência

O módulo de plano de contingência foi definido com 19 blocos funcionais. Cada bloco abaixo constitui um requisito funcional estruturante do sistema.

### 9.1 Núcleo do plano

**RF-120.** O sistema deverá permitir criar, editar, versionar, revisar e consultar planos de contingência.  
**RF-121.** O sistema deverá permitir cadastrar identificação geral do plano.  
**RF-122.** O sistema deverá permitir cadastrar caracterização do território.  
**RF-123.** O sistema deverá permitir cadastrar identificação e análise de riscos.  
**RF-124.** O sistema deverá permitir cadastrar múltiplos cenários de desastre por plano.  
**RF-125.** O sistema deverá permitir cadastrar níveis de ativação operacional.  
**RF-126.** O sistema deverá permitir cadastrar estrutura de governança e responsabilidades.  
**RF-127.** O sistema deverá permitir cadastrar recursos disponíveis por categoria.  
**RF-128.** O sistema deverá permitir cadastrar monitoramento, alerta e comunicação.  
**RF-129.** O sistema deverá permitir cadastrar procedimentos operacionais de resposta.  
**RF-130.** O sistema deverá permitir cadastrar rotas de fuga, pontos de apoio e abrigos.  
**RF-131.** O sistema deverá permitir cadastrar assistência à população afetada.  
**RF-132.** O sistema deverá permitir cadastrar simulados, treinamentos e capacitações.  
**RF-133.** O sistema deverá permitir registrar monitoramento, avaliação e revisão do plano.  
**RF-134.** O sistema deverá permitir anexar documentos operacionais complementares.

### 9.2 Núcleo operacional vinculado ao plano

**RF-135.** O sistema deverá permitir criar estrutura de comando e coordenação da operação vinculada ao plano de contingência.  
**RF-136.** O sistema deverá permitir registrar dados de ativação, desativação, autoridade responsável, comandante e objetivo operacional da estrutura ativada.  
**RF-137.** O sistema deverá permitir cadastrar funções e equipes do CSI/SCO vinculadas à estrutura.  
**RF-138.** O sistema deverá permitir registrar função, titular, substituto, vínculo institucional, atribuições, competências e status operacional.  
**RF-139.** O sistema deverá permitir cadastrar instalações operacionais vinculadas ao CSI/SCO.  
**RF-140.** O sistema deverá permitir registrar capacidade, infraestrutura, meios de comunicação, acessibilidade, segurança física e condição de uso da instalação.  
**RF-141.** O sistema deverá permitir organizar a operação em períodos operacionais.  
**RF-142.** O sistema deverá permitir registrar objetivos, prioridades, estratégias, táticas, recursos críticos, riscos, indicadores e avaliação ao encerramento por período operacional.  
**RF-143.** O sistema deverá permitir manter registros operacionais do comando vinculados à estrutura e ao período operacional.  
**RF-144.** O sistema deverá permitir registrar decisões, acionamentos, ocorrências, mobilizações, comunicações, danos e outras atualizações com status e evidências anexas.  
**RF-145.** O sistema deverá manter rastreabilidade lógica entre estrutura ativada, funções, instalações, períodos e registros.

### 9.3 Regras funcionais específicas do PLANCON

**RF-146.** O sistema deverá permitir múltiplas versões do mesmo plano.  
**RF-147.** O sistema deverá manter histórico de revisões do plano.  
**RF-148.** O sistema deverá permitir status do plano, incluindo ativo, em revisão, vencido ou equivalente conforme regra do negócio.  
**RF-149.** O sistema deverá permitir consultar plano por município, órgão, tipo de desastre, vigência, versão e responsável técnico.  
**RF-150.** O sistema deverá permitir gerar relatórios de planos de contingência.  
**RF-151.** O sistema deverá permitir exportar fichas e anexos do plano.  
**RF-152.** O sistema deverá permitir relacionar plano com cenários, estruturas operacionais e anexos.

## 10. Requisitos funcionais — Gerenciamento de desastres / SCI-SCO

O módulo de gerenciamento de desastres foi organizado em 19 blocos funcionais, refletindo a lógica do SCI/SCO descrita no material-base.

### 10.1 Abertura e comando do incidente

**RF-200.** O sistema deverá permitir cadastrar ocorrência/incidente.  
**RF-201.** O sistema deverá gerar ou controlar número operacional da ocorrência.  
**RF-202.** O sistema deverá permitir registrar tipo, classificação inicial, data/hora, local, coordenadas, descrição inicial e danos iniciais.  
**RF-203.** O sistema deverá permitir registrar briefing inicial do incidente.  
**RF-204.** O sistema deverá permitir registrar mapa/croqui inicial, objetivos iniciais, ações em andamento, recursos alocados, riscos críticos e necessidades imediatas.  
**RF-205.** O sistema deverá permitir registrar comando do incidente ou comando unificado.  
**RF-206.** O sistema deverá permitir registrar transferência formal de comando.  
**RF-207.** O sistema deverá permitir registrar diretrizes institucionais e restrições jurídicas/operacionais.  
**RF-208.** O sistema deverá permitir cadastrar staff do comando.  
**RF-209.** O sistema deverá permitir cadastrar staff geral por seções funcionais.

### 10.2 Direção da resposta

**RF-210.** O sistema deverá permitir cadastrar objetivos do incidente por período operacional.  
**RF-211.** O sistema deverá permitir registrar prioridade, justificativa, responsável, prazo, indicador e resultado do objetivo.  
**RF-212.** O sistema deverá permitir cadastrar estratégias, táticas e PAI.  
**RF-213.** O sistema deverá permitir vincular atividades, responsáveis, recursos, áreas prioritárias, medidas de segurança, plano de comunicação e aprovação do PAI.  
**RF-214.** O sistema deverá permitir organizar períodos operacionais do incidente.  
**RF-215.** O sistema deverá permitir associar objetivos, PAI, briefing e situação ao período operacional.

### 10.3 Execução da resposta

**RF-216.** O sistema deverá permitir registrar operações de campo.  
**RF-217.** O sistema deverá permitir registrar frente operacional, divisão/grupo/setor, supervisor, missão tática, área de atuação, recursos designados e resultado parcial.  
**RF-218.** O sistema deverá permitir registrar gerenciamento de recursos.  
**RF-219.** O sistema deverá registrar mobilização, chegada, desmobilização, condição operacional, localização e status do recurso.  
**RF-220.** O sistema deverá permitir cadastrar instalações do incidente.  
**RF-221.** O sistema deverá permitir registrar comunicações integradas por período operacional.  
**RF-222.** O sistema deverá permitir registrar segurança operacional com riscos identificados, áreas afetadas, medidas de controle, EPIs, restrições e interdições.

### 10.4 Coordenação e suporte

**RF-223.** O sistema deverá permitir registrar planejamento e situação do incidente.  
**RF-224.** O sistema deverá permitir consolidar situação, prognóstico, cenário provável, mudanças relevantes, recursos e pendências críticas.  
**RF-225.** O sistema deverá permitir registrar informação pública e comunicação externa.  
**RF-226.** O sistema deverá permitir registrar comunicados, público-alvo, mensagem oficial, canal, aprovação, porta-voz e resposta a rumores.  
**RF-227.** O sistema deverá permitir registrar ligação interinstitucional.  
**RF-228.** O sistema deverá permitir registrar instituição participante, representante, função, contatos, recursos ofertados e limitações institucionais.  
**RF-229.** O sistema deverá permitir registrar administração e finanças do incidente.  
**RF-230.** O sistema deverá permitir registrar despesas, valores estimados e realizados, fonte de recurso, contratação emergencial e documento comprobatório.

### 10.5 Encerramento

**RF-231.** O sistema deverá permitir manter diário do incidente e registros operacionais.  
**RF-232.** O sistema deverá permitir registrar data/hora, tipo de registro, origem da informação, encaminhamento, status, evidência anexa e observações.  
**RF-233.** O sistema deverá permitir elaborar e acompanhar plano de desmobilização.  
**RF-234.** O sistema deverá permitir registrar critérios de desmobilização, recursos a liberar, destino, pendências e lições iniciais.  
**RF-235.** O sistema deverá permitir encerrar formalmente o incidente, mantendo histórico completo da operação.

### 10.6 Regras funcionais específicas do incidente

**RF-236.** O sistema deverá permitir múltiplos incidentes ativos conforme escopo institucional.  
**RF-237.** O sistema deverá permitir classificar e filtrar incidentes por município, tipo, gravidade, status, órgão e comandante.  
**RF-238.** O sistema deverá permitir visualizar situação operacional atual do incidente em painel específico.  
**RF-239.** O sistema deverá permitir associar incidente a mapa operacional.  
**RF-240.** O sistema deverá permitir exportar relatórios, fichas e histórico do incidente.  
**RF-241.** O sistema deverá manter vínculo entre incidente, staff, objetivos, períodos, registros, recursos e instalações.  
**RF-242.** O sistema deverá suportar rastreabilidade completa da evolução da resposta.

## 11. Requisitos funcionais — Painel operacional do usuário

**RF-290.** O sistema deverá disponibilizar painel inicial operacional como tela principal do usuário.  
**RF-291.** O painel deverá exibir indicadores de incidentes ativos, incidentes encerrados, pessoas afetadas, recursos mobilizados, abrigos ativos e alertas críticos.  
**RF-292.** O painel deverá exibir mapa operacional em destaque.  
**RF-293.** O mapa operacional deverá permitir visualizar ocorrências georreferenciadas, áreas afetadas, abrigos, instalações e recursos em campo.  
**RF-294.** O painel deverá exibir situação do incidente atual, período operacional, comandante, objetivos ativos, riscos críticos e próximos passos.  
**RF-295.** O painel deverá exibir gráficos operacionais.  
**RF-296.** O painel deverá exibir registros recentes, novos acionamentos, comunicados e pendências.  
**RF-297.** O sistema deverá permitir seleção de órgão/unidade quando o perfil possuir escopo superior.  
**RF-298.** O sistema deverá refletir status operacional em tempo compatível com a dinâmica da operação.

## 12. Requisitos funcionais — Relatórios, filtros e exportações

**RF-299.** O sistema deverá permitir consulta e filtragem de ocorrências por período, tipo, classificação, município, status, gravidade, órgão, unidade, comandante e período operacional.  
**RF-300.** O sistema deverá exibir tabela principal das ocorrências.  
**RF-301.** O sistema deverá permitir visualizar ficha completa da ocorrência.  
**RF-302.** O sistema deverá permitir imprimir ocorrência.  
**RF-303.** O sistema deverá permitir exportar relatórios em PDF.  
**RF-304.** O sistema deverá permitir exportar relatórios em planilha.  
**RF-305.** O sistema deverá permitir abrir ocorrência no mapa.  
**RF-306.** O sistema deverá permitir exibir gráficos de ocorrências por período, tipologia, severidade, território e situação operacional.  
**RF-307.** O sistema deverá permitir consulta e filtragem de planos de contingência.  
**RF-308.** O sistema deverá exibir tabela de planos por título, órgão, município, tipo de desastre, versão, vigência e status.  
**RF-309.** O sistema deverá exibir indicadores ou gráficos de planos por município, tipo de desastre, situação e revisão.  
**RF-310.** O sistema deverá permitir exportar relatórios de PLANCON.  
**RF-311.** O sistema deverá permitir exportar registros operacionais do comando.  
**RF-312.** O sistema deverá permitir filtrar registros por data/hora, tipo, criticidade e status.  
**RF-313.** O sistema deverá permitir linha do tempo operacional.  
**RF-314.** O sistema deverá permitir anexar evidências a registros e relatórios.

## 13. Requisitos funcionais — Conta e segurança do usuário

**RF-330.** O sistema deverá permitir alteração de senha.  
**RF-331.** O sistema deverá exigir confirmação da nova senha.  
**RF-332.** O sistema deverá apresentar indicador de força da senha.  
**RF-333.** O sistema deverá permitir habilitar ou desabilitar autenticação em dois fatores, conforme política do perfil e do plano.  
**RF-334.** O sistema deverá permitir visualizar último acesso.  
**RF-335.** O sistema deverá permitir visualizar sessões ativas e dispositivos vinculados, quando disponível.  
**RF-336.** O sistema deverá permitir cancelamento ou revogação de sessão ativa.  
**RF-337.** O sistema deverá permitir recuperação de senha no login.  
**RF-338.** O sistema deverá bloquear acesso conforme status do usuário, da assinatura ou do módulo.

## 14. Requisitos funcionais — Auditoria e conformidade

**RF-350.** O sistema deverá registrar logs de acesso.  
**RF-351.** O sistema deverá registrar logs administrativos.  
**RF-352.** O sistema deverá registrar logs operacionais relevantes.  
**RF-353.** O sistema deverá armazenar conta, órgão, unidade, usuário, módulo, ação, data/hora, IP e resultado do evento auditado.  
**RF-354.** O sistema deverá registrar eventos de aceite legal e conformidade.  
**RF-355.** O sistema deverá manter trilha de auditoria para assinaturas, usuários e ações administrativas.  
**RF-356.** O sistema deverá permitir consulta aos logs conforme perfil autorizado.  
**RF-357.** O sistema deverá permitir segmentar auditoria por conta, órgão, unidade e usuário.

## 15. Requisitos funcionais — Navegação e comportamento das áreas

**RF-370.** O sistema deverá possuir navegação distinta para área pública, área administrativa e área operacional.  
**RF-371.** O menu lateral administrativo deverá conter dashboard, contas, órgãos, assinaturas, planos, pagamentos, módulos, usuários, relatórios, configurações e logs.  
**RF-372.** O menu lateral operacional deverá conter painel, ocorrências, gerenciamento do desastre, mapa operacional, recursos, comunicações, relatórios, planos de contingência, cadastro institucional, conta e segurança.  
**RF-373.** O cabeçalho administrativo deverá conter busca global, notificações, atalhos, perfil e saída.  
**RF-374.** O cabeçalho operacional deverá conter logo, nome do módulo atual, busca, notificações, status do incidente ativo, seletor de órgão/unidade e perfil.  
**RF-375.** O sistema deverá exibir breadcrumb, título da página e barra de ações rápidas nas páginas internas.  
**RF-376.** O sistema deverá permitir responsividade funcional para desktop, tablet e mobile.  
**RF-377.** O sistema deverá destacar item de menu ativo e permitir menu recolhível.  
**RF-378.** O rodapé interno deverá exibir versão do sistema, ambiente e referência de suporte.

## 16. Matriz resumida de módulos e objetivos funcionais

|Módulo|Finalidade funcional principal|
|---|---|
|Área pública|Apresentar, converter e gerar confiança comercial|
|Administração SaaS|Controlar clientes, assinaturas, planos, módulos e faturamento|
|Cadastro institucional|Organizar conta, órgão, unidade, usuários e perfis|
|PLANCON|Estruturar a gestão de risco e os planos de contingência|
|Gerenciamento de desastres|Operar incidentes com lógica compatível com SCI/SCO|
|Painel operacional|Consolidar leitura situacional em tempo operacional|
|Relatórios|Consultar, filtrar, exportar e documentar|
|Conta e segurança|Proteger acessos e credenciais|
|Auditoria e conformidade|Garantir rastreabilidade e governança|

## 17. Regras críticas de negócio derivadas dos requisitos funcionais

1. O plano contratado controla limites operacionais e acesso a módulos.
2. A conta contratante é a unidade comercial central do SaaS.
3. O órgão/instituição é a unidade operacional central do uso do sistema.
4. A estrutura do CSI/SCO vinculada ao plano deve manter encadeamento entre estrutura, pessoas, instalações, períodos e registros.
5. A estrutura do SCI/SCO do incidente deve manter encadeamento entre abertura, comando, direção da resposta, execução, suporte e encerramento.
6. O sistema não deve tratar módulos operacionais complexos apenas como texto livre, mas como entidades rastreáveis e vinculadas entre si.
7. O acesso deve ser condicionado simultaneamente por perfil, escopo, status da assinatura e módulos liberados.

## 18. Considerações técnicas sobre a qualidade deste documento

Este documento já estabelece uma base consistente para os próximos artefatos. O ponto forte dele é que traduz o conteúdo extenso do anexo em estrutura funcional formal. O principal cuidado daqui para frente é manter coerência entre estes requisitos e os próximos documentos, especialmente:

- arquitetura de banco de dados;
- modelagem;
- chaves primárias e estrangeiras;
- controladores;
- camadas;
- serviços;
- dicionário de dados.