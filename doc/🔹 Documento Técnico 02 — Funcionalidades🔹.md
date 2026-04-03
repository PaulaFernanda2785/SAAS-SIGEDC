**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Especificação de Funcionalidades  
**Objetivo:** descrever, organizar e formalizar as funcionalidades do sistema por área, módulo e finalidade operacional.

## 1. Finalidade do documento

Este documento apresenta as funcionalidades do SIGERD de forma estruturada, descrevendo o que o sistema oferece ao usuário, à administração SaaS e à operação institucional. O foco aqui não é apenas listar telas, mas consolidar a proposta funcional do produto em linguagem técnica, orientada à futura arquitetura, modelagem de dados, desenvolvimento backend, serviços e controle de acesso.

## 2. Visão geral funcional do produto

O SIGERD foi concebido como uma plataforma SaaS com três camadas principais de produto:

1. área pública comercial;
2. área administrativa do SaaS;
3. área interna operacional do usuário.

Dentro dessa estrutura, o sistema opera em duas frentes centrais:

- **gestão do risco**, por meio da elaboração e manutenção de planos de contingência;
- **gerenciamento de acidentes e desastres**, com base em lógica estruturada de SCI/SCO.

Além disso, o sistema incorpora gestão institucional, usuários, permissões, contratos, planos de assinatura, cobrança, auditoria e relatórios.

## 3. Macroestrutura funcional do SIGERD

As funcionalidades do sistema foram agrupadas nos seguintes macrodomínios:

- funcionalidades da área pública;
- funcionalidades da área administrativa SaaS;
- funcionalidades do núcleo institucional;
- funcionalidades do módulo de planos de contingência;
- funcionalidades do módulo de gerenciamento de desastres;
- funcionalidades de relatórios e painéis;
- funcionalidades de segurança, conta e auditoria.

---

# 4. Funcionalidades da Área Pública Comercial

## 4.1. Apresentação institucional do sistema

A área pública tem função comercial e estratégica. Sua finalidade é apresentar o produto, transmitir confiança institucional, explicar valor e converter interessados em demonstração ou contratação.

### Funcionalidades principais:

- exibição da identidade visual do sistema;
- navegação pública institucional;
- apresentação dos diferenciais da plataforma;
- exibição dos principais módulos;
- comunicação da proposta de valor;
- captação de leads;
- encaminhamento para demonstração, contratação ou acesso.

## 4.2. Página inicial comercial

### Funcionalidades:

- exibir hero principal com chamada de valor;
- apresentar botões de ação comercial;
- exibir dor do problema enfrentado por instituições;
- mostrar solução do sistema em blocos visuais;
- apresentar módulos de forma resumida;
- destacar benefícios institucionais;
- exibir planos e preços;
- apresentar prova de valor;
- finalizar com chamada para conversão.

## 4.3. Página de planos

### Funcionalidades:

- exibir planos Start, Essencial, Profissional, Institucional e Governo/Enterprise;
- apresentar comparativo entre limites e recursos;
- exibir preços mensais e anuais;
- indicar público-alvo de cada plano;
- permitir encaminhamento para contratação ou demonstração.

## 4.4. Página de demonstração

### Funcionalidades:

- receber solicitação de demonstração;
- coletar dados institucionais do interessado;
- registrar interesse comercial;
- permitir triagem comercial posterior.

## 4.5. Página de contato

### Funcionalidades:

- disponibilizar formulário de contato;
- apresentar canais de comunicação;
- centralizar demandas comerciais e institucionais.

## 4.6. Página de login/acesso

### Funcionalidades:

- autenticar usuário;
- recuperar senha;
- encaminhar usuário à área correspondente;
- separar acesso administrativo do acesso do cliente, quando aplicável.

---

# 5. Funcionalidades da Área Administrativa SaaS

## 5.1. Dashboard administrativo

A área administrativa do SaaS é o núcleo de governança comercial e operacional do negócio.

### Funcionalidades:

- exibir indicadores de assinaturas ativas;
- exibir clientes em teste;
- exibir inadimplência;
- exibir receita mensal e anual;
- exibir cancelamentos;
- exibir novos cadastros;
- exibir distribuição de clientes por plano;
- exibir uso por módulo;
- listar movimentações recentes relevantes.

## 5.2. Gestão de contas contratantes

### Funcionalidades:

- cadastrar conta contratante;
- editar dados da conta;
- ativar, suspender, cancelar ou inativar conta;
- consultar histórico da conta;
- relacionar responsável contratual;
- registrar situação cadastral e comercial.

## 5.3. Gestão de órgãos e instituições

### Funcionalidades:

- cadastrar órgão/instituição operacional;
- vincular órgão a uma conta contratante;
- classificar instituição por tipo, esfera e nível de atuação;
- manter dados institucionais atualizados;
- separar entidade contratante da entidade operadora.

## 5.4. Gestão de unidades e subunidades

### Funcionalidades:

- cadastrar unidades institucionais;
- vincular unidades a órgãos;
- permitir hierarquia entre unidades;
- registrar responsável e contatos da unidade;
- segmentar atuação por território ou estrutura interna.

## 5.5. Gestão de planos de assinatura

### Funcionalidades:

- cadastrar planos;
- definir limites operacionais;
- definir módulos incluídos;
- definir suporte e SLA;
- ativar ou desativar plano comercial;
- controlar ordem de exibição na página pública.

## 5.6. Gestão de assinaturas

### Funcionalidades:

- vincular conta a plano contratado;
- controlar ciclo de cobrança;
- registrar valores contratados;
- aplicar desconto;
- controlar período de teste;
- controlar renovação automática;
- registrar status da assinatura;
- anexar contrato e aceite.

## 5.7. Gestão de faturamento e cobrança

### Funcionalidades:

- emitir ou registrar faturas;
- controlar vencimento;
- registrar pagamento;
- armazenar comprovante;
- registrar meio de pagamento;
- classificar fatura por status;
- manter histórico financeiro.

## 5.8. Liberação de módulos contratados

### Funcionalidades:

- habilitar módulo por assinatura;
- bloquear módulo por status contratual;
- definir limites operacionais por módulo;
- controlar data de liberação e bloqueio;
- aplicar restrições conforme o plano contratado.

## 5.9. Gestão de usuários administrativos

### Funcionalidades:

- cadastrar administradores do SaaS;
- aplicar perfis internos;
- controlar permissões sensíveis;
- manter trilha de auditoria administrativa.

## 5.10. Configurações e conformidade

### Funcionalidades:

- gerenciar termos de uso;
- gerenciar política de privacidade;
- manter versões legais;
- registrar aceite LGPD;
- registrar responsável legal;
- manter configurações globais do produto.

---

# 6. Funcionalidades do Núcleo Institucional

## 6.1. Cadastro institucional da conta

### Funcionalidades:

- registrar dados jurídicos e comerciais da conta;
- registrar endereço e contatos;
- registrar responsável pelo contrato;
- manter status cadastral.

## 6.2. Cadastro do órgão operador

### Funcionalidades:

- registrar instituição que efetivamente usa o sistema;
- manter dados administrativos e operacionais do órgão;
- registrar autoridade máxima e coordenador responsável;
- vincular área e nível de atuação.

## 6.3. Cadastro de unidades

### Funcionalidades:

- estruturar internamente a instituição;
- permitir múltiplas unidades por órgão;
- vincular usuários à unidade correta;
- suportar COMPDEC, regional, base operacional, sala de situação e estruturas similares.

## 6.4. Cadastro de usuários

### Funcionalidades:

- cadastrar operador do sistema;
- vincular usuário à conta, órgão e unidade;
- armazenar dados funcionais;
- controlar status do usuário;
- manter login, senha e fatores adicionais de autenticação;
- registrar último acesso.

## 6.5. Perfis e permissões

### Funcionalidades:

- criar perfis de acesso;
- definir escopo do perfil;
- controlar acesso por módulo;
- controlar ações permitidas;
- associar perfil a usuário;
- limitar acesso por plano, órgão, unidade e função.

## 6.6. Gestão de segurança da conta

### Funcionalidades:

- alterar senha;
- exigir troca de senha;
- habilitar autenticação em dois fatores;
- registrar sessões e acessos;
- habilitar assinatura digital quando contratada.

---

# 7. Funcionalidades do Módulo de Plano de Contingência

O módulo de plano de contingência compõe a frente de **gestão do risco**. Sua função é permitir que o usuário institucional construa, mantenha e revise planos estruturados digitalmente. O sistema não deve tratar o PLANCON como documento solto, mas como entidade modular e rastreável.

## 7.1. Gestão geral do plano

### Funcionalidades:

- criar plano de contingência;
- editar plano;
- versionar plano;
- revisar plano;
- consultar histórico do plano;
- definir vigência;
- controlar status do plano.

## 7.2. Identificação geral do plano

### Funcionalidades:

- registrar dados institucionais do plano;
- registrar responsáveis técnicos;
- registrar base legal;
- registrar escopo e objetivos;
- identificar desastres principais e associados.

## 7.3. Caracterização do território

### Funcionalidades:

- cadastrar localidade e dados territoriais;
- registrar população total e população em risco;
- identificar comunidades vulneráveis;
- registrar infraestrutura crítica;
- registrar unidades de saúde, escolas e abrigos;
- anexar mapa e coordenadas.

## 7.4. Identificação e análise de riscos

### Funcionalidades:

- cadastrar tipo de ameaça;
- descrever riscos;
- registrar histórico de ocorrências;
- avaliar frequência, sazonalidade, probabilidade e impacto;
- calcular ou registrar nível de risco;
- registrar fatores agravantes e atenuantes;
- armazenar fontes de informação.

## 7.5. Cenários de desastre

### Funcionalidades:

- cadastrar cenários;
- estimar danos humanos, materiais, ambientais e sociais;
- registrar necessidades iniciais esperadas;
- definir prioridades operacionais do cenário;
- classificar cenários.

## 7.6. Níveis de ativação operacional

### Funcionalidades:

- cadastrar níveis;
- definir gatilhos técnicos e institucionais;
- vincular órgãos acionados;
- definir ações automáticas;
- definir forma de comunicação e desmobilização.

## 7.7. Governança e responsabilidades

### Funcionalidades:

- cadastrar órgãos participantes;
- registrar responsáveis por fase;
- registrar competências e substitutos;
- manter regime de plantão.

## 7.8. Recursos disponíveis

### Funcionalidades:

- cadastrar recursos humanos, veículos, máquinas, equipamentos, materiais e insumos;
- registrar quantidade, localização, disponibilidade e restrições;
- identificar órgão responsável e tempo de mobilização.

## 7.9. Monitoramento, alerta e comunicação

### Funcionalidades:

- cadastrar fontes de monitoramento;
- definir indicadores críticos;
- registrar limites de atenção, alerta e alarme;
- definir responsáveis por acompanhar, validar e emitir alertas;
- manter mensagens padrão.

## 7.10. Procedimentos operacionais de resposta

### Funcionalidades:

- cadastrar procedimentos;
- vincular procedimento ao tipo de desastre;
- descrever passo a passo;
- indicar executor principal e apoio;
- definir tempo máximo, riscos, medidas de segurança e critério de conclusão.

## 7.11. Rotas, pontos de apoio e abrigos

### Funcionalidades:

- cadastrar rotas de fuga;
- cadastrar pontos de encontro;
- cadastrar pontos de apoio;
- cadastrar abrigos temporários;
- registrar capacidade, estrutura, responsáveis e status de uso.

## 7.12. Assistência à população afetada

### Funcionalidades:

- cadastrar tipos de atendimento;
- definir público-alvo e critérios;
- estimar beneficiários;
- definir fluxo de atendimento;
- registrar recursos necessários;
- estruturar prioridades especiais.

## 7.13. Simulados, treinamentos e capacitações

### Funcionalidades:

- registrar atividades simuladas;
- definir cenário, participantes e órgãos envolvidos;
- registrar avaliação;
- registrar não conformidades;
- registrar medidas corretivas.

## 7.14. Monitoramento, avaliação e revisão do plano

### Funcionalidades:

- registrar revisões;
- registrar alterações realizadas;
- registrar setores consultados;
- identificar pendências;
- agendar próxima revisão;
- formalizar aprovação institucional.

## 7.15. Anexos operacionais

### Funcionalidades:

- anexar mapas, listas, checklists, fluxogramas e formulários;
- classificar anexos;
- relacionar anexo com cenário;
- registrar responsável pelo envio.

## 7.16. Núcleo operacional vinculado ao plano — CSI/SCO

Esse é um diferencial funcional importante do produto. O anexo deixa claro que esses blocos não devem ser tratados como texto solto, mas como núcleo operacional do sistema.

### Funcionalidades:

- ativar estrutura de comando e coordenação;
- definir nome e tipo da estrutura ativada;
- vincular evento, cenário, autoridade responsável e comandante;
- registrar funções e equipes;
- registrar instalações operacionais;
- organizar períodos operacionais;
- manter diário do comando;
- vincular pessoas, instalações, períodos e registros entre si.

---

# 8. Funcionalidades do Módulo de Gerenciamento de Desastres

Esse módulo compõe a frente de **gerenciamento do desastre**. O objetivo não é apenas cadastrar uma ocorrência, mas estruturar o comando, a resposta, os recursos e a rastreabilidade operacional do incidente.

## 8.1. Cadastro inicial da ocorrência/incidente

### Funcionalidades:

- abrir incidente;
- registrar dados iniciais;
- gerar ou controlar número operacional;
- registrar local, coordenadas, danos iniciais, riscos imediatos e órgão líder;
- definir status inicial do incidente.

## 8.2. Briefing inicial do incidente

### Funcionalidades:

- consolidar resumo inicial da situação;
- registrar mapa/croqui;
- registrar objetivos iniciais;
- registrar ações em andamento;
- registrar recursos alocados e solicitados;
- registrar riscos críticos e necessidades imediatas;
- apoiar transferência de comando.

## 8.3. Comando do incidente / Comando unificado

### Funcionalidades:

- registrar comandante;
- definir tipo de comando;
- registrar comando unificado quando aplicável;
- registrar assunção e transferência de comando;
- manter base legal e diretrizes.

## 8.4. Staff do comando

### Funcionalidades:

- cadastrar oficial de segurança;
- cadastrar oficial de informação pública;
- cadastrar oficial de ligação;
- cadastrar assessorias adicionais;
- controlar vigência, status e atribuições.

## 8.5. Staff geral / Seções funcionais

### Funcionalidades:

- ativar seções de operações, planejamento, logística, administração/finanças e inteligência;
- registrar chefias;
- registrar estrutura subordinada;
- manter status de ativação e missão da seção.

## 8.6. Objetivos do incidente

### Funcionalidades:

- cadastrar objetivos estratégicos;
- definir prioridade;
- justificar objetivo;
- vincular responsável e prazo;
- controlar status e resultado.

## 8.7. Estratégias, táticas e PAI

### Funcionalidades:

- registrar estratégia definida;
- registrar táticas previstas;
- vincular atividades e responsáveis;
- alocar recursos necessários;
- registrar áreas prioritárias;
- vincular medidas de segurança e comunicação;
- registrar aprovação e versão do PAI.

## 8.8. Operações de campo

### Funcionalidades:

- registrar frente operacional;
- registrar divisão, grupo ou setor;
- vincular supervisor;
- registrar missão tática;
- registrar recursos designados;
- acompanhar situação atual e resultado parcial.

## 8.9. Planejamento e situação

### Funcionalidades:

- consolidar situação do incidente;
- produzir prognóstico;
- registrar cenário provável;
- indicar mudanças relevantes;
- controlar disponibilidade de recursos;
- indicar pendências críticas e necessidade de escalonamento.

## 8.10. Gerenciamento de recursos

### Funcionalidades:

- cadastrar recursos empregados;
- registrar mobilização, chegada e desmobilização;
- manter status do recurso;
- rastrear localização;
- identificar supervisor responsável;
- manter condição operacional atualizada.

## 8.11. Instalações do incidente

### Funcionalidades:

- cadastrar posto de comando, base, área de espera, abrigo, heliponto, centro de distribuição e outras instalações;
- registrar capacidade e infraestrutura;
- manter status operacional;
- controlar ativação e desativação.

## 8.12. Comunicações integradas

### Funcionalidades:

- cadastrar canais, frequências e procedimentos;
- definir usuários autorizados;
- registrar redundância;
- registrar falhas;
- vincular ao período operacional.

## 8.13. Segurança operacional

### Funcionalidades:

- registrar riscos operacionais;
- registrar público e equipes expostas;
- definir medidas de controle;
- indicar EPIs obrigatórios;
- registrar interdições e restrições.

## 8.14. Informação pública e comunicação externa

### Funcionalidades:

- elaborar comunicados;
- definir público-alvo;
- registrar mensagem oficial;
- controlar aprovação;
- registrar porta-voz;
- registrar rumor ou fake news monitorado e resposta associada.

## 8.15. Ligação interinstitucional

### Funcionalidades:

- registrar instituição participante;
- registrar representante;
- indicar função no incidente;
- registrar recursos ofertados;
- registrar limitações e solicitações pendentes.

## 8.16. Administração e finanças

### Funcionalidades:

- registrar despesas do incidente;
- controlar centro de custo;
- vincular fonte de recurso;
- registrar contratação emergencial;
- registrar documento comprobatório;
- manter status administrativo da despesa.

## 8.17. Períodos operacionais

### Funcionalidades:

- abrir período operacional;
- definir data/hora;
- registrar situação inicial;
- vincular objetivos;
- vincular recursos principais;
- vincular briefing e PAI;
- registrar encerramento e pendências.

## 8.18. Registros operacionais / Diário do incidente

### Funcionalidades:

- registrar fatos operacionais;
- registrar decisões;
- registrar encaminhamentos;
- anexar evidências;
- manter histórico cronológico;
- servir de base para relatórios e auditoria.

## 8.19. Desmobilização e encerramento

### Funcionalidades:

- planejar desmobilização;
- liberar recursos;
- registrar pendências logísticas e administrativas;
- registrar situação final do incidente;
- registrar lições iniciais;
- formalizar encerramento.

---

# 9. Funcionalidades do Painel Operacional

## 9.1. Painel inicial do usuário

### Funcionalidades:

- exibir indicadores rápidos;
- apresentar leitura situacional atual;
- destacar incidente ativo;
- orientar tomada de decisão imediata.

## 9.2. Mapa operacional

### Funcionalidades:

- exibir ocorrências georreferenciadas;
- exibir áreas afetadas;
- exibir instalações;
- exibir abrigos;
- exibir recursos em campo;
- exibir camadas de risco.

## 9.3. Gráficos operacionais

### Funcionalidades:

- exibir ocorrências por tipo;
- exibir ocorrências por território;
- exibir evolução temporal;
- exibir recursos por status;
- exibir distribuição de danos;
- exibir linha do tempo operacional.

## 9.4. Registros recentes

### Funcionalidades:

- listar últimas atualizações;
- listar acionamentos;
- listar comunicados;
- listar mudanças de status;
- listar pendências operacionais.

---

# 10. Funcionalidades de Relatórios

## 10.1. Relatórios de ocorrências e desastres

### Funcionalidades:

- filtrar ocorrências por múltiplos critérios;
- exibir tabela principal;
- abrir ficha completa;
- exportar PDF;
- exportar planilha;
- imprimir;
- abrir no mapa.

## 10.2. Relatórios de PLANCON

### Funcionalidades:

- filtrar planos por município, órgão, tipo de desastre, vigência, versão e status;
- exibir tabela consolidada;
- exibir indicadores de planos;
- exportar relatórios.

## 10.3. Relatórios administrativos

### Funcionalidades:

- consolidar assinaturas;
- consolidar faturamento;
- consolidar uso por módulo;
- consolidar clientes por plano;
- apoiar gestão comercial do SaaS.

## 10.4. Relatórios operacionais específicos

### Funcionalidades:

- exportar diário do incidente;
- exportar registros do comando;
- exportar períodos operacionais;
- exportar composição do CSI/SCO ou SCI/SCO;
- exportar relatórios gerenciais e institucionais.

---

# 11. Funcionalidades de Segurança, Auditoria e Conformidade

## 11.1. Segurança do acesso

### Funcionalidades:

- autenticar usuário;
- recuperar senha;
- exigir troca de senha;
- habilitar 2FA;
- controlar status de login.

## 11.2. Auditoria

### Funcionalidades:

- registrar acessos;
- registrar ações administrativas;
- registrar ações sensíveis;
- registrar contexto do evento;
- permitir rastreabilidade por conta, órgão, unidade e usuário.

## 11.3. Conformidade

### Funcionalidades:

- registrar aceite de termos;
- registrar aceite de política de privacidade;
- registrar aceite LGPD;
- armazenar versão do termo aceito;
- manter dados de responsável e IP de aceite.

---

# 12. Tabela consolidada de funcionalidades por camada

|Camada|Finalidade|Funcionalidades centrais|
|---|---|---|
|Área pública|Comercial e conversão|landing page, planos, demonstração, contato, login|
|Área administrativa SaaS|Gestão do negócio|contas, órgãos, planos, assinaturas, cobrança, módulos, relatórios|
|Área operacional|Gestão do risco e do desastre|PLANCON, incidente, SCI/SCO, mapa, relatórios, recursos, segurança|

---

# 13. Pontos críticos de arquitetura funcional

Do ponto de vista técnico, há cinco pontos críticos que precisam ser preservados nas próximas documentações:

1. **separação entre conta contratante e órgão operador**: isso é estrutural e evita modelagem errada centrada apenas no usuário;
2. **independência entre PLANCON e gerenciamento do desastre**: os dois módulos se relacionam, mas não podem ser confundidos;
3. **núcleo operacional rastreável**: no CSI/SCO do plano e no SCI/SCO do incidente, as entidades precisam existir de forma relacional, não como campos soltos;
4. **controle por plano e módulo**: o SaaS depende disso para monetização e governança;
5. **separação visual e funcional das três áreas do produto**: pública, administrativa e operacional têm objetivos diferentes e não devem compartilhar a mesma lógica de navegação.

# 14. Conclusão técnica

Este documento transforma o escopo descrito no anexo em uma visão organizada de funcionalidades do SIGERD. Ele já permite enxergar com clareza:

- o que o produto entrega comercialmente;
- o que a administração SaaS precisa controlar;
- o que a operação institucional realmente utiliza;
- quais módulos serão centrais no backend;
- quais áreas exigirão maior profundidade de modelagem relacional.

O principal risco, daqui para frente, seria produzir arquitetura de banco ou controladores sem respeitar esta divisão funcional. Isso geraria conflito entre o desenho do sistema e a experiência real de uso.