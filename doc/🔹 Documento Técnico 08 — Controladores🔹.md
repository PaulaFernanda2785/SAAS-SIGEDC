**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Estrutura de Controladores  
**Objetivo:** definir a função, a organização, as responsabilidades e a distribuição dos controladores do backend, garantindo coerência arquitetural, segurança de fluxo e manutenção previsível.

## 1. Finalidade do documento

Este documento estabelece a estrutura de controladores do SIGERD. Seu objetivo é padronizar como as requisições deverão entrar na aplicação, como serão encaminhadas para os serviços adequados e como cada módulo será representado na camada de aplicação.

No contexto do SIGERD, os controladores não devem concentrar a inteligência do sistema. Eles devem atuar como coordenadores de fluxo, recebendo a requisição, validando o contexto inicial, acionando services e retornando a resposta apropriada para a interface ou exportação.

## 2. Papel dos controladores na arquitetura do SIGERD

Os controladores pertencem à camada de aplicação. Seu papel é intermediar a comunicação entre:

- interface e rotas;
- validação de entrada;
- autenticação e autorização inicial;
- serviços de negócio;
- mecanismos de resposta.

No SIGERD, os controladores devem ser responsáveis por:

- receber a requisição;
- interpretar a ação desejada;
- validar estrutura básica da entrada;
- acionar a política ou middleware adequado;
- delegar a execução ao service correto;
- montar resposta HTML, redirecionamento, JSON, PDF ou exportação;
- registrar eventos auxiliares quando necessário.

Eles não devem ser responsáveis por decidir regras profundas do negócio, calcular estados complexos ou implementar lógica central do SCI/SCO ou do PLANCON.

## 3. Princípios de organização dos controladores

A estrutura de controladores do SIGERD deverá seguir os seguintes princípios:

1. separação por contexto do produto;
2. separação por domínio funcional;
3. controladores enxutos;
4. ausência de regra de negócio pesada dentro do controller;
5. ausência de SQL direto no controller;
6. clareza de nomenclatura;
7. previsibilidade de rota para ação;
8. aderência ao escopo institucional e ao plano contratado;
9. respostas compatíveis com o contexto da rota.

## 4. Macroestrutura de controladores

A organização macro recomendada é:

app/Controllers/  
├── Public/  
├── Auth/  
├── Admin/  
└── Operational/

Essa separação é a mais adequada porque acompanha diretamente as três áreas do produto e o núcleo transversal de autenticação.

## 5. Controladores por contexto

## 5.1. Controladores da área pública

Esses controladores são responsáveis pela camada comercial e institucional pública do sistema.

### Finalidade:

- renderizar páginas públicas;
- apresentar planos;
- tratar solicitação de demonstração;
- tratar formulários de contato;
- encaminhar acesso para autenticação.

### Características:

- baixa densidade de regra de negócio;
- foco em conteúdo, formulários e entrada comercial;
- forte atenção à sanitização e segurança de entrada.

### Controladores esperados:

- `HomeController`
- `SolutionController`
- `FeaturesController`
- `PlansController`
- `DemoController`
- `ContactController`
- `AboutController`

### Responsabilidades resumidas:

- exibir páginas públicas;
- consultar planos ativos para exibição;
- registrar leads;
- encaminhar respostas de contato.

## 5.2. Controladores de autenticação

Esses controladores tratam entrada e segurança básica de acesso.

### Finalidade:

- login;
- logout;
- recuperação de senha;
- redefinição de senha;
- autenticação em dois fatores, se habilitada;
- controle de sessão.

### Controladores esperados:

- `LoginController`
- `LogoutController`
- `ForgotPasswordController`
- `ResetPasswordController`
- `TwoFactorController`
- `SessionController`

### Responsabilidades resumidas:

- receber credenciais;
- validar entrada;
- acionar serviço de autenticação;
- abrir ou encerrar sessão;
- redirecionar usuário para a área correta;
- registrar eventos de autenticação relevantes.

## 5.3. Controladores da área administrativa SaaS

Esses controladores operam a camada de governança comercial do produto.

### Finalidade:

- administrar contas contratantes;
- administrar órgãos;
- administrar unidades;
- administrar planos;
- administrar assinaturas;
- administrar faturas;
- administrar módulos liberados;
- administrar usuários administrativos;
- exibir dashboard administrativo;
- consultar auditoria administrativa.

### Características:

- forte vínculo com regras contratuais;
- necessidade elevada de controle de permissão;
- impacto direto na monetização e governança do sistema.

### Controladores esperados:

- `AdminDashboardController`
- `AccountController`
- `OrganizationController`
- `UnitController`
- `AdminUserController`
- `PlanController`
- `SubscriptionController`
- `InvoiceController`
- `ModuleAccessController`
- `ComplianceController`
- `AdminReportController`
- `AuditController`
- `SettingsController`

## 5.4. Controladores da área operacional

Esses controladores representam a camada mais crítica do sistema.

### Finalidade:

- painel operacional;
- gestão institucional do cliente;
- plano de contingência;
- incidentes;
- mapa operacional;
- recursos;
- comunicações;
- relatórios operacionais;
- segurança da conta do usuário.

### Características:

- maior densidade funcional;
- maior volume de validação por escopo;
- forte dependência de services especializados;
- necessidade de respostas rápidas e consistentes.

### Controladores esperados:

- `OperationalDashboardController`
- `InstitutionalController`
- `OperationalUserController`
- `PlanconController`
- `PlanconBlockController`
- `PlanconStructureController`
- `IncidentController`
- `IncidentBriefingController`
- `IncidentCommandController`
- `IncidentOperationsController`
- `IncidentPlanningController`
- `IncidentResourceController`
- `IncidentCommunicationController`
- `IncidentSafetyController`
- `IncidentFinanceController`
- `OperationalPeriodController`
- `OperationalRecordController`
- `MapController`
- `ResourceController`
- `CommunicationController`
- `OperationalReportController`
- `AccountSecurityController`

---

# 6. Responsabilidades funcionais dos controladores

## 6.1. Responsabilidades permitidas

Os controladores do SIGERD podem e devem:

- interpretar a rota e o método HTTP;
- chamar validadores de entrada;
- verificar autenticação inicial;
- acionar policies ou middlewares;
- delegar a um service;
- montar objetos de resposta;
- escolher view ou redirecionamento;
- retornar dados para gráficos, filtros e exports;
- padronizar mensagens de sucesso e erro.

## 6.2. Responsabilidades proibidas ou inadequadas

Os controladores do SIGERD não devem:

- conter SQL bruto;
- decidir sozinho regra contratual;
- decidir transição crítica de status;
- implementar o ciclo completo de incidente;
- validar sozinho lógica do SCI/SCO;
- controlar a coerência do PLANCON inteiro;
- gerar PDF completo com regra de consulta embarcada;
- concentrar múltiplos domínios sem separação;
- virar arquivos monolíticos com dezenas de métodos heterogêneos.

---

# 7. Estrutura recomendada dos controladores por módulo

## 7.1. Controladores do módulo público

### `HomeController`

Responsável por:

- renderizar a página inicial;
- carregar elementos institucionais principais;
- exibir chamadas de valor e CTAs.

### `PlansController`

Responsável por:

- listar planos de assinatura;
- exibir comparativo;
- encaminhar ações de contratação ou demonstração.

### `DemoController`

Responsável por:

- exibir formulário de demonstração;
- receber envio;
- acionar serviço de lead/demo;
- registrar sucesso ou falha.

### `ContactController`

Responsável por:

- exibir formulário de contato;
- processar envio;
- registrar mensagem e retorno.

## 7.2. Controladores de autenticação

### `LoginController`

Responsável por:

- exibir tela de login;
- processar tentativa de autenticação;
- acionar `AuthService`;
- redirecionar conforme o perfil/contexto.

### `LogoutController`

Responsável por:

- encerrar sessão;
- limpar contexto;
- redirecionar ao login ou página pública.

### `ForgotPasswordController`

Responsável por:

- iniciar recuperação de senha;
- validar e-mail ou identificador;
- acionar serviço de recuperação.

### `ResetPasswordController`

Responsável por:

- processar redefinição;
- validar token e nova senha;
- concluir redefinição.

### `TwoFactorController`

Responsável por:

- validar segundo fator;
- concluir etapa complementar de login.

## 7.3. Controladores administrativos SaaS

### `AdminDashboardController`

Responsável por:

- exibir indicadores administrativos;
- montar visão consolidada de assinaturas, receita e inadimplência.

### `AccountController`

Responsável por:

- listar contas contratantes;
- abrir cadastro;
- editar conta;
- ativar, suspender ou cancelar conta;
- exibir visão detalhada da conta.

### `OrganizationController`

Responsável por:

- listar órgãos/instituições;
- criar e editar órgão;
- vincular órgão à conta;
- consultar estrutura institucional.

### `UnitController`

Responsável por:

- gerenciar unidades vinculadas ao órgão;
- criar, editar e consultar unidades.

### `AdminUserController`

Responsável por:

- administrar usuários da área administrativa;
- ativar, bloquear ou alterar perfil administrativo.

### `PlanController`

Responsável por:

- cadastrar e editar planos;
- ativar ou desativar oferta comercial;
- exibir limites e módulos dos planos.

### `SubscriptionController`

Responsável por:

- criar assinatura;
- editar dados da assinatura;
- consultar situação contratual;
- aplicar filtros por status, plano, período e inadimplência.

### `InvoiceController`

Responsável por:

- listar faturas;
- consultar situação financeira;
- registrar pagamento;
- anexar comprovante;
- exibir histórico financeiro.

### `ModuleAccessController`

Responsável por:

- liberar ou bloquear módulos por assinatura;
- consultar módulos ativos;
- ajustar limites operacionais por módulo.

### `ComplianceController`

Responsável por:

- exibir conformidade da conta/assinatura;
- registrar aceite de termos e LGPD;
- manter dados legais relevantes.

### `AdminReportController`

Responsável por:

- gerar relatórios administrativos;
- filtrar dados comerciais e financeiros;
- exportar resultados.

### `AuditController`

Responsável por:

- consultar trilha de auditoria administrativa;
- filtrar eventos por usuário, conta, ação e período.

### `SettingsController`

Responsável por:

- manter configurações administrativas do produto;
- exibir parâmetros gerais permitidos.

## 7.4. Controladores operacionais institucionais

### `OperationalDashboardController`

Responsável por:

- exibir painel principal do usuário;
- carregar indicadores operacionais;
- alimentar widgets e visão resumida do incidente.

### `InstitutionalController`

Responsável por:

- exibir dados institucionais do órgão/unidade;
- editar dados cadastrais institucionais;
- integrar informações de órgão, unidade e contexto operacional.

### `OperationalUserController`

Responsável por:

- gerenciar usuários operacionais do cliente;
- vincular perfis e escopos;
- controlar status de acesso.

---

# 8. Controladores do módulo PLANCON

O PLANCON, por sua complexidade, não deve ficar sob um único controller monolítico. O documento-base deixa claro que ele é composto por diversos blocos funcionais e por um núcleo operacional vinculado ao plano.

## 8.1. `PlanconController`

### Responsável por:

- listar planos;
- criar plano;
- editar metadados gerais;
- exibir plano;
- versionar;
- controlar vigência e status;
- iniciar processo de revisão.

### Ações típicas:

- `index()`
- `create()`
- `store()`
- `show()`
- `edit()`
- `update()`
- `review()`
- `changeStatus()`

## 8.2. `PlanconBlockController`

### Responsável por:

- tratar blocos específicos do plano;
- salvar e atualizar conteúdos por seção;
- segmentar o preenchimento modular.

### Escopo:

- identificação;
- território;
- riscos;
- cenários;
- ativação;
- governança;
- recursos;
- monitoramento;
- procedimentos;
- abrigos/rotas;
- assistência;
- simulados;
- revisão;
- anexos.

### Observação:

esse controller pode atuar com rotas segmentadas por bloco, evitando sobrecarregar o `PlanconController`.

## 8.3. `PlanconStructureController`

### Responsável por:

- tratar o núcleo CSI/SCO vinculado ao plano;
- criar estrutura ativada;
- gerenciar equipes, instalações, períodos e registros.

### Subresponsabilidades:

- estrutura de comando;
- funções e equipes;
- instalações operacionais;
- períodos operacionais;
- registros do comando.

### Observação crítica:

não misturar esse núcleo com o bloco narrativo do plano. Ele é operacional e relacional, não apenas textual.

---

# 9. Controladores do módulo Incidentes / SCI-SCO

O módulo de gerenciamento de desastres possui ainda mais densidade operacional. Seu desenho em controladores precisa refletir a divisão do fluxo entre abertura, direção da resposta, execução, suporte e encerramento.

## 9.1. `IncidentController`

### Responsável por:

- listar incidentes;
- abrir incidente;
- exibir ficha geral;
- editar dados iniciais;
- consultar status geral;
- encerrar incidente em nível macro, quando aplicável.

### Ações típicas:

- `index()`
- `create()`
- `store()`
- `show()`
- `edit()`
- `update()`
- `close()`

## 9.2. `IncidentBriefingController`

### Responsável por:

- registrar e atualizar briefing inicial;
- consolidar situação inicial;
- registrar objetivos iniciais, ações em curso, riscos e necessidades.

## 9.3. `IncidentCommandController`

### Responsável por:

- registrar comando do incidente;
- registrar comando unificado;
- tratar assunção e transferência de comando;
- gerenciar staff do comando e staff geral.

### Observação:

se o módulo crescer muito, o staff pode ser separado em controladores próprios.

## 9.4. `IncidentOperationsController`

### Responsável por:

- controlar objetivos do incidente;
- registrar estratégias, táticas e PAI;
- gerenciar operações de campo;
- acompanhar execução operacional.

## 9.5. `IncidentPlanningController`

### Responsável por:

- consolidar planejamento e situação;
- gerenciar cenários e prognósticos;
- abrir e fechar períodos operacionais;
- registrar pendências e transições entre períodos.

## 9.6. `IncidentResourceController`

### Responsável por:

- gerenciar recursos do incidente;
- registrar mobilização, chegada, desmobilização e condição operacional;
- gerenciar instalações do incidente.

## 9.7. `IncidentCommunicationController`

### Responsável por:

- gerenciar comunicações integradas;
- gerenciar informação pública;
- registrar comunicação externa;
- registrar ligação interinstitucional.

## 9.8. `IncidentSafetyController`

### Responsável por:

- registrar riscos à equipe e ao público;
- manter medidas de segurança;
- registrar interdições, EPIs e restrições operacionais.

## 9.9. `IncidentFinanceController`

### Responsável por:

- gerenciar despesas do incidente;
- registrar custos, fontes de recurso e documentação financeira;
- acompanhar pendências administrativas e financeiras.

## 9.10. `OperationalPeriodController`

### Responsável por:

- abrir período operacional;
- atualizar status do período;
- consolidar dados de objetivos, recursos e PAI vinculados.

## 9.11. `OperationalRecordController`

### Responsável por:

- manter diário do incidente;
- registrar ocorrências, decisões, encaminhamentos e evidências;
- aplicar filtros por data, tipo, criticidade e status.

### Observação crítica:

esse controller é central para rastreabilidade. Não deve ser simplificado como “observações do incidente”.

---

# 10. Controladores de suporte operacional

## 10.1. `MapController`

### Responsável por:

- carregar mapa operacional;
- devolver dados georreferenciados;
- filtrar camadas de incidente, instalações, abrigos, recursos e áreas afetadas.

## 10.2. `ResourceController`

### Responsável por:

- gerenciar recursos institucionais cadastrados fora do incidente;
- controlar disponibilidade geral, localização e condições de uso.

## 10.3. `CommunicationController`

### Responsável por:

- gerenciar fluxos gerais de comunicação institucional e operacional;
- manter modelos, canais ou registros complementares, conforme escopo adotado.

## 10.4. `OperationalReportController`

### Responsável por:

- consultar relatórios operacionais;
- aplicar filtros;
- exibir tabelas e gráficos;
- exportar PDF, CSV ou planilhas;
- abrir relatórios vinculados a incidente ou PLANCON.

## 10.5. `AccountSecurityController`

### Responsável por:

- alterar senha;
- habilitar ou desabilitar 2FA;
- exibir último acesso;
- listar sessões e dispositivos, se disponível;
- gerenciar preferências de segurança do usuário.

---

# 11. Padrão recomendado de ações dos controladores

Os controladores do SIGERD devem seguir uma convenção relativamente estável de ações. Não é obrigatório usar exatamente o mesmo conjunto em todos os módulos, mas a coerência ajuda manutenção.

## Ações mais comuns:

- `index()` — listar registros;
- `create()` — exibir formulário de criação;
- `store()` — persistir novo registro;
- `show()` — exibir detalhe;
- `edit()` — exibir formulário de edição;
- `update()` — persistir alteração;
- `delete()` ou `destroy()` — excluir/inativar;
- `export()` — exportar relatório/arquivo;
- `changeStatus()` — alterar status quando aplicável;
- `attach()` — anexar documento;
- `download()` — baixar arquivo;
- `audit()` — consultar trilha, quando aplicável.

## Diretriz:

métodos especiais devem refletir o caso de uso real, e não ser forçados dentro de um CRUD genérico quando o fluxo operacional for mais complexo.

---

# 12. Relação entre controladores e services

Cada controlador deve possuir relação clara com um ou mais services. A regra geral é:

- controller coordena;
- service decide e executa a regra de negócio;
- repository persiste ou recupera dados.

## Exemplo de mapeamento:

- `SubscriptionController` → `SubscriptionService`
- `PlanconController` → `PlanconService`
- `IncidentController` → `IncidentService`
- `OperationalRecordController` → `OperationalRecordService`
- `OperationalReportController` → `OperationalReportService` + `ExportService`

## Observação:

quando um controller depender de muitos services sem coerência, isso pode indicar que o módulo foi mal dividido.

---

# 13. Relação entre controladores e middlewares

Os controladores deverão ser protegidos por middlewares adequados ao contexto da rota.

## Exemplos:

- área pública: sanitização, anti-spam, CSRF;
- autenticação: rate limit, sessão;
- área administrativa: autenticação, perfil administrativo, módulo liberado;
- área operacional: autenticação, escopo institucional, status da assinatura, módulo operacional liberado.

## Regra:

o controller pode reforçar validações específicas, mas não deve depender apenas de validação interna solta se a proteção puder ser resolvida por middleware ou policy.

---

# 14. Relação entre controladores e policies

As policies devem atuar sobre ações sensíveis de recurso.

## Exemplos:

- criar incidente;
- editar plano de contingência;
- liberar módulo;
- exportar relatório sensível;
- consultar auditoria;
- alterar dados institucionais;
- encerrar incidente;
- transferir comando.

## Diretriz:

o controller invoca a policy ou utiliza verificação equivalente, mas a decisão não deve ficar codificada de forma improvisada em condicionais repetidas por todo o sistema.

---

# 15. Padrão de resposta dos controladores

Os controladores do SIGERD deverão responder de forma consistente com o tipo de rota e com o contexto funcional.

## Tipos de resposta esperados:

- renderização HTML;
- redirecionamento com mensagem;
- resposta JSON para componentes assíncronos;
- download de arquivo;
- exportação PDF;
- exportação CSV/XLSX;
- resposta de erro controlada.

## Diretriz:

não misturar retorno HTML e JSON sem critério no mesmo método, a menos que exista uma estratégia explícita de diferenciação.

---

# 16. Regras de qualidade para controladores

Os controladores do SIGERD devem observar as seguintes regras de qualidade:

1. tamanho controlado;
2. métodos curtos;
3. nome coerente com o domínio;
4. dependências explícitas;
5. ausência de duplicação de fluxo;
6. validação de entrada desacoplada;
7. autorização previsível;
8. mensagens padronizadas;
9. ausência de regra densa de negócio;
10. fácil leitura e manutenção.

---

# 17. Sinais de que um controlador está mal projetado

Alguns sinais claros de degradação arquitetural:

- controller com responsabilidades de vários módulos;
- controller com dezenas de métodos sem relação entre si;
- presença de SQL direto;
- muitas regras de status codificadas localmente;
- dependência excessiva de sessão global;
- mistura de lógica administrativa e operacional;
- exportação complexa montada integralmente no controller;
- duplicação de validações em múltiplos métodos;
- ifs extensos de permissão por todo o arquivo.

No SIGERD, esse risco é alto principalmente nos módulos de incidente, PLANCON e relatórios. Por isso a divisão proposta não é luxo; é necessidade.

---

# 18. Estrutura consolidada recomendada de controladores

app/Controllers/  
├── Public/  
│   ├── HomeController.php  
│   ├── SolutionController.php  
│   ├── FeaturesController.php  
│   ├── PlansController.php  
│   ├── DemoController.php  
│   ├── ContactController.php  
│   └── AboutController.php  
├── Auth/  
│   ├── LoginController.php  
│   ├── LogoutController.php  
│   ├── ForgotPasswordController.php  
│   ├── ResetPasswordController.php  
│   ├── TwoFactorController.php  
│   └── SessionController.php  
├── Admin/  
│   ├── AdminDashboardController.php  
│   ├── AccountController.php  
│   ├── OrganizationController.php  
│   ├── UnitController.php  
│   ├── AdminUserController.php  
│   ├── PlanController.php  
│   ├── SubscriptionController.php  
│   ├── InvoiceController.php  
│   ├── ModuleAccessController.php  
│   ├── ComplianceController.php  
│   ├── AdminReportController.php  
│   ├── AuditController.php  
│   └── SettingsController.php  
└── Operational/  
    ├── OperationalDashboardController.php  
    ├── InstitutionalController.php  
    ├── OperationalUserController.php  
    ├── PlanconController.php  
    ├── PlanconBlockController.php  
    ├── PlanconStructureController.php  
    ├── IncidentController.php  
    ├── IncidentBriefingController.php  
    ├── IncidentCommandController.php  
    ├── IncidentOperationsController.php  
    ├── IncidentPlanningController.php  
    ├── IncidentResourceController.php  
    ├── IncidentCommunicationController.php  
    ├── IncidentSafetyController.php  
    ├── IncidentFinanceController.php  
    ├── OperationalPeriodController.php  
    ├── OperationalRecordController.php  
    ├── MapController.php  
    ├── ResourceController.php  
    ├── CommunicationController.php  
    ├── OperationalReportController.php  
    └── AccountSecurityController.php

---

# 19. Conclusão técnica

A estrutura de controladores do SIGERD deve refletir a realidade do sistema: um SaaS institucional com três contextos de produto, governança contratual, gestão institucional, plano de contingência e gerenciamento de desastres baseado em lógica operacional estruturada.

A principal decisão correta aqui é não tratar controladores como arquivos genéricos de CRUD. Em especial:

- o módulo PLANCON precisa de segmentação por blocos;
- o módulo de incidentes precisa de segmentação por funções do fluxo operacional;
- a área administrativa precisa separar claramente monetização, contas e auditoria;
- a área pública precisa permanecer leve e isolada.

Se essa estrutura for respeitada, o backend fica muito mais previsível, seguro e escalável. Se for ignorada, o risco mais provável é o surgimento rápido de controladores gigantes, acoplados e difíceis de evoluir.