**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Estrutura de Serviços  
**Objetivo:** definir a arquitetura, a função, a organização e a distribuição dos serviços do backend, estabelecendo quais services deverão existir, o que cada um deve fazer e como se relacionam com controladores, regras de negócio, persistência, segurança e rastreabilidade.

## 1. Finalidade do documento

Este documento estabelece a estrutura de serviços do SIGERD. Seu propósito é organizar formalmente a camada responsável por executar a lógica de negócio do sistema, preservando a separação entre:

- entrada de requisição;
- coordenação de fluxo;
- regra de negócio;
- persistência;
- infraestrutura técnica.

No contexto do SIGERD, essa definição é indispensável porque o sistema possui três áreas de produto, duas frentes operacionais independentes, controle contratual SaaS, escopo institucional, trilha de auditoria e módulos densos como PLANCON e SCI/SCO.

## 2. Papel dos serviços na arquitetura do SIGERD

Os serviços pertencem principalmente à camada de domínio, com interface direta com a camada de aplicação e dependência controlada da camada de persistência.

Seu papel é transformar intenção funcional em execução de regra institucional, contratual e operacional.

No SIGERD, os serviços devem ser responsáveis por:

- aplicar regras de negócio;
- validar coerência funcional;
- coordenar múltiplas entidades;
- controlar transições de estado;
- aplicar políticas contratuais;
- validar escopo institucional;
- acionar persistência;
- registrar auditoria quando necessário;
- orquestrar fluxos complexos entre módulos;
- impedir que controllers concentrem lógica crítica.

## 3. Princípios estruturais dos serviços

A arquitetura de serviços do SIGERD deverá obedecer aos seguintes princípios:

1. services orientados por domínio;
2. services com responsabilidade clara;
3. ausência de renderização ou lógica de interface dentro do service;
4. ausência de SQL bruto dentro do service, salvo exceções técnicas fortemente controladas;
5. validação de negócio centralizada;
6. rastreabilidade das operações sensíveis;
7. separação entre service funcional e service técnico;
8. reuso de regras comuns sem duplicação;
9. composição entre services quando necessário, sem acoplamento caótico;
10. aderência à arquitetura institucional e ao modelo SaaS do produto.

## 4. Classificação dos serviços no SIGERD

Os serviços do sistema deverão ser classificados em cinco grandes grupos:

1. serviços de autenticação e acesso;
2. serviços de domínio SaaS e institucional;
3. serviços de domínio operacional;
4. serviços de suporte funcional;
5. serviços técnicos transversais.

Essa classificação é superior a uma lista genérica porque permite distinguir regra de negócio principal de serviço auxiliar.

## 5. Estrutura macro recomendada da pasta de serviços

A estrutura já sugerida para o projeto permanece adequada:

app/Services/  
├── Auth/  
├── SaaS/  
├── Institutional/  
├── Plancon/  
├── Incident/  
├── Reports/  
├── Audit/  
├── Files/  
├── Export/  
└── Shared/

Essa organização é coerente com a separação de domínios do sistema e com a arquitetura em camadas previamente definida.

## 6. Serviços de autenticação e acesso

Esses serviços são responsáveis por identidade, sessão, credenciais e controle básico de acesso.

### Objetivos:

- autenticar usuário;
- validar credenciais;
- abrir sessão;
- encerrar sessão;
- redefinir senha;
- controlar 2FA;
- validar contexto de acesso.

### Serviços esperados:

- `AuthService`
- `LoginService`
- `PasswordResetService`
- `TwoFactorService`
- `SessionService`
- `AccessContextService`

### Responsabilidades detalhadas

**AuthService**  
Responsável por autenticação central, validação de senha, status do usuário, vínculo contratual mínimo e liberação inicial de acesso.

**LoginService**  
Responsável por fluxo de login, incluindo interpretação do contexto de entrada e encaminhamento do usuário à área correta.

**PasswordResetService**  
Responsável por geração, validação e consumo de tokens de redefinição de senha.

**TwoFactorService**  
Responsável por validação complementar de segurança quando 2FA estiver habilitado.

**SessionService**  
Responsável por abertura, renovação, encerramento e verificação de sessão.

**AccessContextService**  
Responsável por resolver o contexto institucional e funcional do usuário, inclusive conta, órgão, unidade, perfil e área de navegação permitida.

## 7. Serviços do domínio SaaS

Esse grupo sustenta a operação comercial e contratual do produto.

### Objetivos:

- gerenciar contas;
- gerenciar planos;
- gerenciar assinaturas;
- controlar limites contratuais;
- gerenciar faturas;
- liberar ou bloquear módulos;
- garantir conformidade contratual.

### Serviços esperados:

- `AccountService`
- `OrganizationService`
- `UnitService`
- `PlanService`
- `SubscriptionService`
- `InvoiceService`
- `ModuleAccessService`
- `ComplianceService`
- `LeadService`

### Responsabilidades detalhadas

**AccountService**  
Responsável por cadastro, atualização, ativação, suspensão e cancelamento da conta contratante.

**OrganizationService**  
Responsável por gestão do órgão/instituição vinculada à conta, preservando a separação entre contratante e operador.

**UnitService**  
Responsável por criação e manutenção de unidades, subunidades e hierarquias institucionais.

**PlanService**  
Responsável por cadastro e manutenção dos planos comerciais do produto, incluindo limites e módulos disponíveis.

**SubscriptionService**  
Responsável por criar, atualizar, suspender, cancelar e renovar assinaturas, aplicando regras contratuais, vigência, testes gratuitos e status da assinatura.

**InvoiceService**  
Responsável por gestão financeira da assinatura, incluindo emissão lógica, registro de pagamento, status da fatura e histórico.

**ModuleAccessService**  
Responsável por liberar, bloquear e validar módulos contratados, além de controlar limites operacionais vinculados ao plano.

**ComplianceService**  
Responsável por aceite de termos, aceite LGPD, versões legais, responsável pelo aceite e consistência mínima de conformidade.

**LeadService**  
Responsável por registro e triagem de leads originados da área pública, como demonstração e contato.

## 8. Serviços do domínio institucional

Esse grupo sustenta a estrutura interna do cliente dentro da plataforma.

### Objetivos:

- gerenciar usuários;
- gerenciar perfis;
- gerenciar permissões;
- aplicar escopo institucional;
- controlar status de acesso.

### Serviços esperados:

- `UserService`
- `ProfileService`
- `PermissionService`
- `InstitutionalScopeService`
- `AccountSecurityService`

### Responsabilidades detalhadas

**UserService**  
Responsável por cadastro, atualização, ativação, bloqueio, inativação e vínculo de usuários com conta, órgão e unidade.

**ProfileService**  
Responsável por criação, manutenção e associação de perfis de acesso.

**PermissionService**  
Responsável por resolver permissões por ação, módulo, escopo e contexto.

**InstitutionalScopeService**  
Responsável por validar se o usuário pode operar sobre determinado órgão, unidade, recurso, plano ou incidente.

**AccountSecurityService**  
Responsável por alteração de senha, política de troca obrigatória, preferências de segurança e elementos de proteção da conta do usuário.

## 9. Serviços do domínio PLANCON

Esse grupo é um dos mais importantes do SIGERD porque materializa a frente de gestão do risco. O documento-base organiza o PLANCON em múltiplos blocos e deixa claro que ele não deve ser tratado como um cadastro monolítico.

### Objetivos:

- criar e versionar planos;
- gerenciar blocos do plano;
- validar vigência e status;
- manter coerência entre seções;
- estruturar o núcleo CSI/SCO vinculado ao plano.

### Serviços esperados:

- `PlanconService`
- `PlanconVersionService`
- `PlanconBlockService`
- `RiskAnalysisService`
- `ScenarioService`
- `OperationalActivationService`
- `PlanconGovernanceService`
- `PlanconResourceService`
- `PlanconCommunicationService`
- `PlanconProcedureService`
- `ShelterRouteService`
- `PopulationAssistanceService`
- `TrainingExerciseService`
- `PlanconReviewService`
- `PlanconAttachmentService`
- `PlanconStructureService`
- `PlanconTeamService`
- `PlanconFacilityService`
- `PlanconOperationalPeriodService`
- `PlanconOperationalRecordService`

### Responsabilidades detalhadas

**PlanconService**  
Responsável pelo ciclo principal do plano: criar, consultar, atualizar metadados, controlar status e exibir visão consolidada.

**PlanconVersionService**  
Responsável por versionamento, revisão, histórico de alterações e vigência do plano.

**PlanconBlockService**  
Responsável por tratar blocos modulares do plano sem reduzir todo o módulo a um único formulário.

**RiskAnalysisService**  
Responsável por registrar ameaças, riscos, vulnerabilidades, probabilidade, impacto e nível de risco.

**ScenarioService**  
Responsável por cadastrar cenários de desastre e manter estimativas de danos, necessidades e prioridades.

**OperationalActivationService**  
Responsável por níveis operacionais, gatilhos, autoridades de acionamento e lógica de escalonamento.

**PlanconGovernanceService**  
Responsável por governança, competências institucionais, responsáveis e regime de plantão.

**PlanconResourceService**  
Responsável por meios humanos, materiais, logísticos e sua disponibilidade preventiva.

**PlanconCommunicationService**  
Responsável por monitoramento, validação de informação, alerta e canais de comunicação.

**PlanconProcedureService**  
Responsável por procedimentos operacionais de resposta e sua estrutura lógica.

**ShelterRouteService**  
Responsável por rotas de fuga, pontos de apoio, pontos de encontro e abrigos.

**PopulationAssistanceService**  
Responsável por fluxos de assistência à população, critérios de atendimento e recursos vinculados.

**TrainingExerciseService**  
Responsável por simulados, treinamentos, avaliação e não conformidades.

**PlanconReviewService**  
Responsável por revisão periódica, pendências, atualização cartográfica, atualização de contatos e próxima revisão.

**PlanconAttachmentService**  
Responsável por anexos do plano, classificação, vínculo com cenário e controle documental.

**PlanconStructureService**  
Responsável por criar e gerenciar a estrutura CSI/SCO vinculada ao plano.

**PlanconTeamService**  
Responsável por funções e equipes do CSI/SCO, incluindo titular, substituto, atribuições e status operacional.

**PlanconFacilityService**  
Responsável por instalações operacionais vinculadas à estrutura do plano.

**PlanconOperationalPeriodService**  
Responsável por ciclos formais de operação, objetivos, prioridades, estratégias e encerramento por período.

**PlanconOperationalRecordService**  
Responsável por registros do comando, diário operacional, decisões, acionamentos, ocorrências, encaminhamentos e evidências.

## 10. Serviços do domínio Incidentes / SCI-SCO

Esse grupo sustenta a frente de gerenciamento de desastres. Ele é ainda mais crítico do que o PLANCON porque opera o evento real, o comando, os recursos e a resposta em andamento.

### Objetivos:

- abrir e conduzir incidentes;
- manter briefing inicial;
- gerir comando;
- gerir objetivos e períodos;
- gerir recursos e instalações;
- consolidar situação;
- manter diário do incidente;
- preparar desmobilização.

### Serviços esperados:

- `IncidentService`
- `IncidentNumberService`
- `IncidentBriefingService`
- `IncidentCommandService`
- `CommandStaffService`
- `GeneralStaffService`
- `IncidentObjectiveService`
- `IncidentStrategyService`
- `FieldOperationsService`
- `IncidentPlanningService`
- `IncidentResourceService`
- `IncidentFacilityService`
- `IntegratedCommunicationService`
- `OperationalSafetyService`
- `PublicInformationService`
- `InteragencyLiaisonService`
- `IncidentFinanceService`
- `OperationalPeriodService`
- `OperationalRecordService`
- `DemobilizationService`
- `IncidentClosureService`

### Responsabilidades detalhadas

**IncidentService**  
Responsável pela abertura, atualização geral, consulta consolidada e visão macro do incidente.

**IncidentNumberService**  
Responsável por geração e validação do número operacional da ocorrência/incidente.

**IncidentBriefingService**  
Responsável pelo briefing inicial equivalente ao SCI 201 funcional, incluindo situação, objetivos iniciais, ações em curso, recursos e riscos críticos.

**IncidentCommandService**  
Responsável por comando do incidente, comando unificado, assunção e transferência de comando.

**CommandStaffService**  
Responsável pelo staff do comando, como segurança, informação pública e ligação.

**GeneralStaffService**  
Responsável pelas seções funcionais: operações, planejamento, logística, administração/finanças e inteligência, quando adotada.

**IncidentObjectiveService**  
Responsável por objetivos do incidente, prioridade, prazo, indicador de cumprimento e resultado.

**IncidentStrategyService**  
Responsável por estratégia, tática, atividades a executar, PAI, versão do plano de ação e aprovação.

**FieldOperationsService**  
Responsável pelas operações de campo, frentes operacionais, setores, supervisão e missão tática.

**IncidentPlanningService**  
Responsável por situação consolidada, prognóstico, cenário provável, pendências críticas e escalonamento.

**IncidentResourceService**  
Responsável por recursos do incidente, incluindo mobilização, status, localização, supervisor e condição operacional.

**IncidentFacilityService**  
Responsável por instalações do incidente, sua capacidade, infraestrutura, situação operacional e vigência de ativação.

**IntegratedCommunicationService**  
Responsável por canais, frequências, procedimentos de comunicação, usuários autorizados e falhas registradas.

**OperationalSafetyService**  
Responsável por riscos operacionais, equipes expostas, EPIs, medidas de controle e restrições.

**PublicInformationService**  
Responsável por comunicados oficiais, público-alvo, porta-voz, rumor monitorado e resposta institucional.

**InteragencyLiaisonService**  
Responsável por instituições participantes, representantes, funções, recursos ofertados e limitações institucionais.

**IncidentFinanceService**  
Responsável por despesas, fontes de recurso, contratações emergenciais e documentação financeira.

**OperationalPeriodService**  
Responsável pelos períodos operacionais do incidente, incluindo abertura, situação inicial, vinculação de objetivos, recursos, briefing e encerramento.

**OperationalRecordService**  
Responsável pelo diário do incidente, registros operacionais, decisões, ocorrências, encaminhamentos, evidências e status do registro.

**DemobilizationService**  
Responsável pelo plano de desmobilização, ordem de liberação de recursos, pendências e lições iniciais.

**IncidentClosureService**  
Responsável pelo encerramento formal do incidente, validação de pendências mínimas e situação final.

## 11. Serviços de relatórios e inteligência operacional

Esse grupo sustenta dashboards, consultas filtradas, gráficos e exportações lógicas.

### Objetivos:

- consolidar dados;
- aplicar filtros;
- respeitar escopo institucional;
- gerar visões executivas e operacionais.

### Serviços esperados:

- `OperationalDashboardService`
- `AdministrativeDashboardService`
- `OperationalReportService`
- `PlanconReportService`
- `AdministrativeReportService`
- `AnalyticsService`
- `MapDataService`

### Responsabilidades detalhadas

**OperationalDashboardService**  
Responsável por indicadores do painel operacional do usuário, situação do incidente, recursos mobilizados, alertas críticos e visão resumida do contexto atual.

**AdministrativeDashboardService**  
Responsável por métricas administrativas do SaaS, como assinaturas ativas, receita, inadimplência e distribuição por plano.

**OperationalReportService**  
Responsável por relatórios de ocorrências, incidentes e registros operacionais, com filtros, consolidações e exportações autorizadas.

**PlanconReportService**  
Responsável por relatórios de planos de contingência, vigência, status, município, órgão e estrutura associada.

**AdministrativeReportService**  
Responsável por relatórios administrativos, financeiros e contratuais da plataforma.

**AnalyticsService**  
Responsável por agregações e indicadores estratégicos utilizados em gráficos e visões consolidadas.

**MapDataService**  
Responsável por preparar dados georreferenciados para mapas operacionais, camadas, ocorrências, instalações, abrigos e recursos.

## 12. Serviços de auditoria, conformidade e rastreabilidade

Esse grupo sustenta a governança do sistema.

### Objetivos:

- registrar trilha de auditoria;
- registrar eventos sensíveis;
- apoiar conformidade institucional;
- diferenciar log técnico de auditoria funcional.

### Serviços esperados:

- `AuditService`
- `AuditTrailService`
- `LegalAcceptanceService`
- `SensitiveActionLogService`

### Responsabilidades detalhadas

**AuditService**  
Responsável por registro central de eventos auditáveis no sistema.

**AuditTrailService**  
Responsável por consolidação e consulta estruturada da trilha de auditoria por usuário, conta, órgão, módulo e ação.

**LegalAcceptanceService**  
Responsável por aceite de termos, políticas e LGPD, inclusive IP, data, versão e responsável.

**SensitiveActionLogService**  
Responsável por captura reforçada de ações críticas, como bloqueios, encerramentos, alterações contratuais, exportações sensíveis e operações com impacto institucional.

## 13. Serviços de arquivos, anexos e documentos

O SIGERD depende fortemente de anexos, evidências, relatórios e documentos operacionais.

### Objetivos:

- validar upload;
- classificar arquivo;
- armazenar com segurança;
- controlar acesso ao download;
- vincular arquivo ao domínio correto.

### Serviços esperados:

- `FileStorageService`
- `AttachmentService`
- `DocumentLinkService`
- `MediaValidationService`

### Responsabilidades detalhadas

**FileStorageService**  
Responsável por armazenamento físico, paths, nomenclatura interna, exclusão controlada e recuperação de arquivo.

**AttachmentService**  
Responsável por vínculo entre arquivo e entidade do sistema, como incidente, plano, fatura, contrato ou evidência.

**DocumentLinkService**  
Responsável por lógica de associação documental entre registros e artefatos.

**MediaValidationService**  
Responsável por validação de tipo, tamanho, extensão e regras mínimas de segurança para upload.

## 14. Serviços de exportação e saída documental

Esses serviços tratam geração de artefatos externos, não a regra do relatório em si.

### Objetivos:

- gerar PDF;
- gerar planilha;
- gerar CSV;
- controlar templates de saída.

### Serviços esperados:

- `ExportService`
- `PdfExportService`
- `SpreadsheetExportService`
- `CsvExportService`
- `PrintViewService`

### Responsabilidades detalhadas

**ExportService**  
Responsável por coordenar exportações conforme tipo de saída.

**PdfExportService**  
Responsável por geração de PDF a partir de datasets já consolidados.

**SpreadsheetExportService**  
Responsável por geração de planilhas estruturadas.

**CsvExportService**  
Responsável por saídas tabulares simples.

**PrintViewService**  
Responsável por preparação de visualização de impressão quando aplicável.

## 15. Serviços compartilhados e utilitários de domínio

Há regras e operações que não pertencem exclusivamente a um domínio, mas também não devem virar helpers dispersos.

### Serviços esperados:

- `DateTimeService`
- `StatusTransitionService`
- `NotificationService`
- `CodeGenerationService`
- `GeoReferenceService`
- `ValidationMessageService`

### Responsabilidades detalhadas

**DateTimeService**  
Responsável por tratamento coerente de datas, horas, vigência, início/fim de período e timezones.

**StatusTransitionService**  
Responsável por validação centralizada de mudanças de status quando houver regras comuns reutilizáveis.

**NotificationService**  
Responsável por notificações internas, avisos sistêmicos e alertas administrativos ou operacionais.

**CodeGenerationService**  
Responsável por geração de códigos, identificadores e sequências formais.

**GeoReferenceService**  
Responsável por apoio a coordenadas, áreas, pontos, camadas e lógica geográfica simplificada.

**ValidationMessageService**  
Responsável por padronização de mensagens de erro e sucesso em validações funcionais.

## 16. Relação entre services e controllers

A relação correta no SIGERD deve seguir esta lógica:

- controller recebe a ação;
- request valida a entrada;
- policy/middleware filtra acesso;
- service executa a regra de negócio;
- repository persiste e consulta;
- controller monta a resposta.

### Regra crítica

O controller não substitui o service. Se o controller começar a decidir regra contratual, escopo institucional ou fluxo operacional do incidente, a arquitetura estará comprometida.

## 17. Relação entre services e repositories

Os repositories devem servir aos services, e não competir com eles.

### Regra:

- repository lê e grava;
- service decide o que pode ou deve acontecer.

### Exemplo correto:

`IncidentService` valida se o incidente pode ser encerrado; `IncidentRepository` apenas persiste o status final.

### Exemplo incorreto:

`IncidentRepository` decidir sozinho se um incidente pode ser encerrado.

## 18. Relação entre services e policies

Policies devem tratar autorização. Services devem tratar regra de negócio. Em alguns casos a fronteira é próxima, mas precisa permanecer clara.

### Exemplo:

- policy decide se o usuário tem permissão para encerrar um incidente;
- service decide se o incidente, naquele estado operacional, pode ser encerrado.

Essa distinção é essencial para o SIGERD.

## 19. Padrões recomendados de composição entre services

Nem todo fluxo será resolvido por um único service isolado. Em módulos densos, a composição entre services é inevitável.

### Exemplo de composição:

`IncidentClosureService` pode depender de:

- `OperationalRecordService`
- `DemobilizationService`
- `AuditService`
- `IncidentService`

### Regra:

a composição deve ser explícita e controlada. O que não pode ocorrer é dependência circular desordenada entre diversos services.

## 20. Sinais de que um service está mal projetado

Alguns sinais claros de degradação:

- service com responsabilidades de múltiplos domínios sem coerência;
- service com centenas de linhas e múltiplos fluxos não relacionados;
- presença de HTML ou montagem de view;
- excesso de dependência de sessão global;
- SQL bruto espalhado;
- validação de entrada e regra de negócio misturadas;
- duplicação de regra existente em outros services;
- lógica financeira dentro de service operacional, ou vice-versa.

No SIGERD, os maiores riscos de degradação estão nos serviços de incidente, PLANCON e assinatura.

## 21. Estrutura consolidada recomendada dos serviços

app/Services/  
├── Auth/  
│   ├── AuthService.php  
│   ├── LoginService.php  
│   ├── PasswordResetService.php  
│   ├── TwoFactorService.php  
│   ├── SessionService.php  
│   └── AccessContextService.php  
├── SaaS/  
│   ├── AccountService.php  
│   ├── OrganizationService.php  
│   ├── UnitService.php  
│   ├── PlanService.php  
│   ├── SubscriptionService.php  
│   ├── InvoiceService.php  
│   ├── ModuleAccessService.php  
│   ├── ComplianceService.php  
│   └── LeadService.php  
├── Institutional/  
│   ├── UserService.php  
│   ├── ProfileService.php  
│   ├── PermissionService.php  
│   ├── InstitutionalScopeService.php  
│   └── AccountSecurityService.php  
├── Plancon/  
│   ├── PlanconService.php  
│   ├── PlanconVersionService.php  
│   ├── PlanconBlockService.php  
│   ├── RiskAnalysisService.php  
│   ├── ScenarioService.php  
│   ├── OperationalActivationService.php  
│   ├── PlanconGovernanceService.php  
│   ├── PlanconResourceService.php  
│   ├── PlanconCommunicationService.php  
│   ├── PlanconProcedureService.php  
│   ├── ShelterRouteService.php  
│   ├── PopulationAssistanceService.php  
│   ├── TrainingExerciseService.php  
│   ├── PlanconReviewService.php  
│   ├── PlanconAttachmentService.php  
│   ├── PlanconStructureService.php  
│   ├── PlanconTeamService.php  
│   ├── PlanconFacilityService.php  
│   ├── PlanconOperationalPeriodService.php  
│   └── PlanconOperationalRecordService.php  
├── Incident/  
│   ├── IncidentService.php  
│   ├── IncidentNumberService.php  
│   ├── IncidentBriefingService.php  
│   ├── IncidentCommandService.php  
│   ├── CommandStaffService.php  
│   ├── GeneralStaffService.php  
│   ├── IncidentObjectiveService.php  
│   ├── IncidentStrategyService.php  
│   ├── FieldOperationsService.php  
│   ├── IncidentPlanningService.php  
│   ├── IncidentResourceService.php  
│   ├── IncidentFacilityService.php  
│   ├── IntegratedCommunicationService.php  
│   ├── OperationalSafetyService.php  
│   ├── PublicInformationService.php  
│   ├── InteragencyLiaisonService.php  
│   ├── IncidentFinanceService.php  
│   ├── OperationalPeriodService.php  
│   ├── OperationalRecordService.php  
│   ├── DemobilizationService.php  
│   └── IncidentClosureService.php  
├── Reports/  
│   ├── OperationalDashboardService.php  
│   ├── AdministrativeDashboardService.php  
│   ├── OperationalReportService.php  
│   ├── PlanconReportService.php  
│   ├── AdministrativeReportService.php  
│   ├── AnalyticsService.php  
│   └── MapDataService.php  
├── Audit/  
│   ├── AuditService.php  
│   ├── AuditTrailService.php  
│   ├── LegalAcceptanceService.php  
│   └── SensitiveActionLogService.php  
├── Files/  
│   ├── FileStorageService.php  
│   ├── AttachmentService.php  
│   ├── DocumentLinkService.php  
│   └── MediaValidationService.php  
├── Export/  
│   ├── ExportService.php  
│   ├── PdfExportService.php  
│   ├── SpreadsheetExportService.php  
│   ├── CsvExportService.php  
│   └── PrintViewService.php  
└── Shared/  
    ├── DateTimeService.php  
    ├── StatusTransitionService.php  
    ├── NotificationService.php  
    ├── CodeGenerationService.php  
    ├── GeoReferenceService.php  
    └── ValidationMessageService.php

## 22. Diretrizes de qualidade para implementação dos services

Os serviços do SIGERD devem observar, no mínimo, as seguintes diretrizes:

1. nome claro e aderente ao domínio;
2. responsabilidade coesa;
3. baixo acoplamento com interface;
4. composição controlada com outros services;
5. dependências explícitas;
6. regras de status centralizadas;
7. suporte à auditoria de operações sensíveis;
8. testes favorecidos por design;
9. ausência de lógica de apresentação;
10. previsibilidade de manutenção.

## 23. Conclusão técnica

Os serviços são a camada mais estratégica do backend do SIGERD. É nela que o sistema realmente deixa de ser um conjunto de cadastros e passa a operar como plataforma institucional, contratual e operacional.

Para este projeto, três decisões são decisivas:

- separar nitidamente serviços SaaS dos serviços operacionais;
- decompor PLANCON e Incidentes em múltiplos services coerentes;
- manter services como sede da regra de negócio, e não como utilitários secundários.

Se essa camada for bem estruturada, os controladores permanecerão limpos, os relatórios serão consistentes, o controle de acesso será mais confiável e o sistema poderá crescer com menor risco de colapso arquitetural. Se essa camada for negligenciada, o efeito mais provável será a dispersão da regra de negócio por controllers, views e consultas avulsas.