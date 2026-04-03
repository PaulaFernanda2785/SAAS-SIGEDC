**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Controle de Acesso  
**Objetivo:** definir a política de autenticação, autorização, escopo institucional, acesso por perfil, acesso por plano e proteção dos módulos do sistema.

## 1. Finalidade do documento

Este documento estabelece a política de controle de acesso do SIGERD. Seu propósito é garantir que cada usuário acesse apenas aquilo que deve acessar, dentro do escopo institucional, contratual e funcional permitido.

O objetivo não é apenas restringir menus. O foco é proteger o sistema de forma estrutural, assegurando que:

- a área pública permaneça separada das áreas internas;
- a área administrativa SaaS permaneça separada da área operacional do cliente;
- o acesso respeite a conta contratante;
- o acesso respeite órgão, unidade e perfil;
- os módulos respeitem o plano contratado;
- ações sensíveis tenham proteção reforçada;
- a trilha de auditoria registre eventos relevantes.

## 2. Objetivos do controle de acesso

O controle de acesso do SIGERD deverá atender aos seguintes objetivos:

- autenticar usuários com segurança;
- autorizar ações conforme perfil;
- restringir escopo por conta, órgão e unidade;
- respeitar os limites do plano contratado;
- bloquear módulos não liberados;
- diferenciar administração do negócio e operação do cliente;
- proteger dados institucionais sensíveis;
- sustentar rastreabilidade e responsabilização;
- preparar o sistema para operação multiunidade e multiórgão.

## 3. Princípios de segurança funcional

O SIGERD deverá seguir os seguintes princípios:

1. nenhum acesso interno sem autenticação válida;
2. nenhuma autorização baseada apenas em frontend;
3. separação rígida entre contexto comercial, administrativo e operacional;
4. toda ação sensível validada no backend;
5. escopo institucional obrigatório nas consultas e operações;
6. plano contratado interfere diretamente no que pode ser acessado;
7. perfil define capacidade funcional;
8. módulo liberado define disponibilidade contratual;
9. auditoria registra eventos críticos;
10. o usuário não é a unidade máxima do modelo: ele opera dentro de conta, órgão e unidade.

## 4. Camadas do controle de acesso

O controle de acesso do SIGERD deverá operar em cinco camadas complementares:

### 4.1. Camada de autenticação

Verifica identidade do usuário.

### 4.2. Camada de autorização funcional

Verifica se o perfil permite a ação.

### 4.3. Camada de escopo institucional

Verifica se o usuário pode operar naquele órgão, unidade ou registro.

### 4.4. Camada contratual

Verifica se o plano da conta libera aquele módulo, capacidade ou recurso.

### 4.5. Camada de auditoria

Registra e rastreia ações relevantes de acesso, alteração, exportação e administração.

Essas camadas não são alternativas entre si. Elas atuam de forma combinada.

## 5. Contextos de acesso do sistema

O SIGERD possui três contextos principais de uso, e o controle de acesso precisa refletir isso.

## 5.1. Área pública

Destinada a visitantes e potenciais clientes.

### Características:

- não exige autenticação para navegação institucional;
- permite acesso a páginas públicas, planos, demonstração, contato e login;
- não permite acesso a dados internos, contratos ou módulos operacionais.

## 5.2. Área administrativa SaaS

Destinada à gestão do negócio da plataforma.

### Características:

- exige autenticação;
- exige perfil administrativo compatível;
- permite gestão de contas, planos, assinaturas, faturas, módulos, usuários administrativos, relatórios administrativos e auditoria;
- não deve ser confundida com operação do cliente.

## 5.3. Área operacional

Destinada ao uso institucional pelo cliente.

### Características:

- exige autenticação;
- exige perfil operacional compatível;
- exige vínculo institucional válido;
- exige módulo liberado no plano;
- permite operar incidentes, PLANCON, mapas, relatórios, recursos, comunicações, cadastro institucional e segurança da conta.

## 6. Autenticação

A autenticação é a primeira barreira formal de acesso ao sistema.

## 6.1. Requisitos de autenticação

O sistema deverá:

- exigir credenciais válidas para toda área interna;
- validar status do usuário antes de abrir sessão;
- validar situação mínima da assinatura quando o acesso depender dela;
- registrar tentativa de login;
- registrar sucesso e falha de autenticação;
- permitir logout seguro;
- permitir recuperação de senha;
- permitir autenticação em dois fatores quando habilitada.

## 6.2. Critérios mínimos para login bem-sucedido

Um login bem-sucedido deve depender de:

- credencial válida;
- usuário com status apto;
- vínculo institucional consistente;
- conta não bloqueada de forma impeditiva;
- contexto permitido para o tipo de usuário.

## 6.3. Estados de usuário que devem bloquear acesso

O sistema deverá impedir acesso quando o usuário estiver, por exemplo:

- pendente, se o fluxo exigir ativação formal;
- bloqueado;
- inativo;
- excluído;
- sem vínculo institucional válido;
- com sessão barrada por regra de segurança.

## 7. Autorização por perfil

A autorização funcional deverá ser controlada por perfis estruturados, e não apenas por um campo textual solto. O documento-base já estabelece perfis e escopos como parte central do sistema.

## 7.1. Perfis principais previstos

Perfis funcionais esperados:

- ADMIN_MASTER
- ADMIN_ORGAO
- GESTOR
- COORDENADOR
- ANALISTA
- OPERADOR
- LEITOR
- FINANCEIRO
- SUPORTE
- CONVIDADO

## 7.2. Lógica de autorização por perfil

O perfil deverá controlar, no mínimo:

- acesso ao dashboard;
- acesso ao PLANCON;
- acesso a ocorrências/incidentes;
- acesso ao SCI/SCO;
- acesso a alertas, quando o módulo existir;
- acesso a relatórios;
- acesso a usuários;
- acesso a assinatura;
- acesso ao financeiro;
- acesso a configurações;
- permissões de criar, editar, excluir, aprovar, exportar e assinar.

## 7.3. Regra central

Um perfil define **o que o usuário pode fazer**.  
O escopo define **onde ele pode fazer**.  
O plano define **se o módulo existe para aquela conta**.

Sem essas três dimensões, o controle de acesso fica incompleto.

## 8. Escopo institucional de acesso

O SIGERD não pode autorizar usuários apenas por perfil genérico. O escopo institucional precisa ser aplicado nas consultas e operações.

## 8.1. Escopos previstos

Escopos funcionais esperados:

- PROPRIA_UNIDADE
- PROPRIO_ORGAO
- MUNICIPAL
- REGIONAL
- ESTADUAL
- MULTIINSTITUCIONAL
- GLOBAL

## 8.2. Regra de uso do escopo

O escopo deverá controlar:

- quais registros o usuário pode listar;
- quais registros ele pode visualizar;
- quais registros ele pode editar;
- quais relatórios pode exportar;
- quais incidentes ou planos pode aprovar;
- quais usuários, órgãos ou unidades pode administrar.

## 8.3. Exemplos práticos de escopo

Um usuário com escopo **PROPRIA_UNIDADE**:

- visualiza apenas dados da sua unidade;
- não deve consultar incidentes de outras unidades;
- não deve editar planos de outra unidade.

Um usuário com escopo **PROPRIO_ORGAO**:

- pode operar registros do seu órgão;
- não deve acessar registros de outro órgão da mesma conta, se houver segregação.

Um perfil **GLOBAL** ou **MULTIINSTITUCIONAL**:

- pode visualizar dados de múltiplos órgãos, conforme a política do plano e da conta.

## 9. Controle por plano de assinatura

O SIGERD é um SaaS, então controle de acesso não pode ignorar o contrato. O plano contratado deve interferir diretamente nos módulos e nos limites disponíveis.

## 9.1. Regra central

O plano não substitui o perfil, mas condiciona a existência do módulo e a capacidade operacional.

## 9.2. Itens controlados por plano

O sistema deverá controlar, no mínimo:

- quantidade máxima de usuários;
- quantidade máxima de órgãos;
- quantidade máxima de unidades;
- quantidade máxima de ocorrências, se houver esse limite;
- quantidade máxima de PLANCONs, se houver esse limite;
- armazenamento;
- módulos disponíveis;
- recursos avançados;
- auditoria;
- assinatura digital;
- integrações;
- API;
- dashboards avançados.

## 9.3. Regra prática

Mesmo que um perfil tenha permissão lógica para usar um módulo, o acesso deverá ser bloqueado se:

- o módulo não estiver contratado;
- a assinatura estiver suspensa;
- a conta estiver inadimplente e a política prever bloqueio;
- o limite operacional estiver excedido.

## 10. Controle por módulo

Cada módulo do sistema deverá possuir estado contratual e disponibilidade funcional próprios.

## 10.1. Módulos previstos

Exemplos de módulos previstos:

- DASHBOARD
- PLANCON
- SCI_SCO
- OCORRENCIAS
- ALERTAS
- RELATORIOS
- MAPAS
- RECURSOS
- ABRIGOS
- COMUNICACAO
- ASSINATURA_DIGITAL
- AUDITORIA
- API
- INTEGRACOES

## 10.2. Regra de liberação

O sistema deverá verificar, antes de liberar uma rota ou ação:

- se o módulo existe no plano;
- se está ativo na assinatura;
- se não está bloqueado administrativamente;
- se o usuário possui perfil compatível;
- se o escopo permite acesso àquele conteúdo.

## 10.3. Regra de bloqueio

O bloqueio de módulo deve ocorrer no backend, não apenas por ocultação de menu.

## 11. Controle de acesso por ação

Além de controlar o acesso ao módulo, o SIGERD deverá controlar a ação permitida dentro do módulo.

## 11.1. Ações mínimas a controlar

As ações mais sensíveis que precisam de autorização explícita são:

- criar;
- editar;
- excluir/inativar;
- aprovar;
- exportar;
- encerrar;
- cancelar;
- liberar;
- bloquear;
- assinar;
- administrar perfis;
- acessar auditoria.

## 11.2. Exemplos operacionais

Um usuário pode:

- visualizar um incidente, mas não encerrá-lo;
- editar um bloco do PLANCON, mas não aprová-lo;
- ver relatórios, mas não exportá-los;
- acessar recursos, mas não excluí-los.

Essa granularidade é necessária para instituições com cadeia de responsabilidade formal.

## 12. Controle de acesso por área

## 12.1. Área pública

Não exige autenticação para navegação institucional, mas exige proteção contra abuso em formulários públicos.

## 12.2. Área administrativa

Exige:

- autenticação;
- perfil administrativo;
- autorização por ação;
- módulo administrativo liberado;
- auditoria reforçada.

## 12.3. Área operacional

Exige:

- autenticação;
- vínculo com conta/órgão/unidade;
- escopo institucional;
- perfil operacional adequado;
- módulo liberado no plano;
- proteção das ações sensíveis por policy e auditoria.

## 13. Controle de acesso aos principais módulos

## 13.1. Painel operacional

Acesso permitido a perfis operacionais compatíveis e dentro do escopo institucional.

## 13.2. PLANCON

Acesso condicionado a:

- módulo contratado;
- perfil com acesso ao PLANCON;
- escopo compatível com órgão/unidade do plano;
- permissão específica para criar, editar, revisar ou aprovar.

## 13.3. Incidentes / SCI-SCO

Acesso condicionado a:

- módulo contratado;
- perfil com acesso a ocorrências/incidentes;
- escopo compatível;
- permissão específica para abrir, editar, conduzir, registrar ou encerrar incidente.

## 13.4. Relatórios

Acesso condicionado a:

- módulo contratado;
- perfil com permissão de consulta;
- escopo de dados;
- permissão adicional de exportação, quando aplicável.

## 13.5. Usuários / Cadastro institucional

Acesso condicionado a:

- perfil administrativo institucional ou superior;
- escopo de administração do órgão/unidade;
- limites do plano.

## 13.6. Assinatura / Financeiro

Na área administrativa SaaS, acesso deve ser restrito a perfis administrativos e financeiros autorizados.

## 14. Modelo de decisão de acesso

Toda tentativa de acesso a um recurso interno do SIGERD deve seguir uma sequência lógica mínima:

1. o usuário está autenticado?
2. a sessão está válida?
3. o status do usuário permite acesso?
4. a conta/assinatura não está em situação impeditiva?
5. o módulo está liberado no plano?
6. o perfil permite essa ação?
7. o escopo institucional cobre esse registro?
8. há restrição adicional de segurança ou auditoria?

Se qualquer uma dessas respostas for negativa, o acesso deve ser negado.

## 15. Middlewares e policies

O controle de acesso do SIGERD deverá ser aplicado em múltiplos pontos da aplicação.

## 15.1. Middlewares

Responsáveis por barreiras gerais, como:

- autenticação;
- distinção de área administrativa e operacional;
- verificação de módulo contratado;
- verificação de status da assinatura;
- verificação de escopo base;
- proteção CSRF, quando aplicável;
- logging inicial de acessos sensíveis.

## 15.2. Policies

Responsáveis por decisões mais específicas, como:

- este usuário pode editar este incidente?
- este usuário pode exportar este relatório?
- este usuário pode aprovar este plano?
- este usuário pode gerenciar usuários deste órgão?
- este usuário pode encerrar esta ocorrência?

## 15.3. Regra prática

Middleware protege a entrada geral.  
Policy decide o acesso ao recurso ou à ação específica.

## 16. Controle de acesso a dados

No SIGERD, segurança não é apenas impedir rota. Também é restringir o conjunto de dados retornado.

## 16.1. Regra central

Toda consulta a registros sensíveis deverá ser filtrada por:

- conta_id;
- orgao_id;
- unidade_id;
- escopo do perfil;
- status contratual;
- módulo ativo, quando necessário.

## 16.2. Aplicação prática

Um usuário com escopo de unidade não pode receber, nem por falha de filtro, registros de outra unidade.

Um usuário da área administrativa SaaS pode ver contas, assinaturas e relatórios administrativos, mas não deve automaticamente visualizar a operação interna detalhada dos clientes sem política expressa.

## 17. Controle de ações sensíveis

Algumas ações precisam de proteção reforçada.

## 17.1. Ações críticas

Entre as mais sensíveis estão:

- bloqueio ou ativação de conta;
- alteração de plano;
- liberação ou bloqueio de módulo;
- alteração de perfis e permissões;
- exportação de dados operacionais sensíveis;
- encerramento de incidente;
- transferência de comando;
- exclusão ou inativação de registros estruturais;
- aceite legal e conformidade;
- operações financeiras.

## 17.2. Exigências recomendadas

Essas ações deverão exigir, conforme o caso:

- perfil específico;
- escopo adequado;
- módulo habilitado;
- auditoria obrigatória;
- validação adicional de contexto;
- eventual confirmação reforçada na interface.

## 18. Auditoria do acesso

Toda política séria de acesso no SIGERD precisa de trilha de auditoria.

## 18.1. Eventos a registrar

Devem ser auditados, no mínimo:

- login e logout;
- falhas de login relevantes;
- acessos à área administrativa;
- mudanças de perfil;
- alterações de módulo liberado;
- exportações;
- ações sobre incidentes;
- ações sobre planos;
- encerramentos, aprovações e cancelamentos;
- operações financeiras;
- aceite de termos e LGPD.

## 18.2. Dados mínimos da auditoria

O log deve registrar, sempre que possível:

- usuário;
- conta;
- órgão;
- unidade;
- módulo;
- ação;
- data/hora;
- IP;
- resultado;
- registro afetado;
- detalhes essenciais do evento.

## 19. Controle de acesso por situação contratual

A assinatura da conta precisa influenciar diretamente o acesso.

## 19.1. Situações contratuais típicas

Exemplos de status:

- TRIAL
- ATIVA
- PENDENTE
- INADIMPLENTE
- SUSPENSA
- CANCELADA
- ENCERRADA

## 19.2. Regras recomendadas

- **ATIVA**: acesso normal conforme perfil e módulos.
- **TRIAL**: acesso conforme módulos do teste.
- **PENDENTE**: acesso pode ser restrito conforme política comercial.
- **INADIMPLENTE**: acesso parcial ou bloqueado conforme política.
- **SUSPENSA/CANCELADA/ENCERRADA**: bloqueio das áreas internas, salvo exceções administrativas controladas.

## 20. Controle de acesso à conta do usuário

O usuário deve poder acessar sua área de segurança, mas dentro de limites claros.

## 20.1. Funcionalidades permitidas ao próprio usuário

- alterar senha;
- habilitar/desabilitar 2FA, quando permitido;
- visualizar último acesso;
- visualizar sessões ativas, se disponível;
- encerrar sessões próprias, se suportado.

## 20.2. Restrições

O usuário não deve, por essa área, alterar:

- seu próprio escopo institucional;
- seu perfil funcional;
- os módulos contratados;
- sua liberação contratual;
- sua vinculação principal de órgão/unidade sem processo autorizado.

## 21. Regras específicas por perfil sugerido

## 21.1. ADMIN_MASTER

Acesso global à administração do SaaS.  
Não deve ser perfil operacional padrão do cliente.

## 21.2. ADMIN_ORGAO

Administra usuários, dados institucionais e operação dentro do próprio órgão, respeitando plano e escopo.

## 21.3. GESTOR

Pode acompanhar e conduzir fluxos operacionais com maior amplitude, inclusive relatórios e módulos estratégicos.

## 21.4. COORDENADOR

Atua na coordenação de módulos operacionais, com poder intermediário elevado.

## 21.5. ANALISTA

Opera, analisa, registra e acompanha, com restrições em aprovações e administração sensível.

## 21.6. OPERADOR

Realiza lançamentos, atualizações e operação cotidiana, com escopo mais restrito.

## 21.7. LEITOR

Consulta dados permitidos, sem alterar registros.

## 21.8. FINANCEIRO

Atua em rotinas financeiras e contratuais compatíveis com seu contexto.

## 21.9. SUPORTE

Perfil técnico/assistencial, com acesso controlado e auditado.

## 21.10. CONVIDADO

Acesso mínimo e altamente restrito.

## 22. Regras de negação de acesso

O sistema deverá negar acesso quando ocorrer qualquer uma das seguintes condições:

- usuário não autenticado;
- sessão inválida;
- usuário bloqueado/inativo/excluído;
- assinatura suspensa ou cancelada com bloqueio aplicável;
- módulo não contratado;
- perfil sem permissão;
- escopo institucional insuficiente;
- tentativa de acesso cruzado entre áreas;
- ação sensível sem autorização;
- recurso inexistente dentro do escopo permitido.

## 23. Erros de controle de acesso que devem ser evitados

Os principais erros a evitar no SIGERD são:

1. confiar apenas em menus ocultos;
2. deixar a aplicação decidir acesso só na view;
3. não filtrar dados por escopo institucional;
4. ignorar a influência do plano contratado;
5. tratar perfil como único mecanismo de autorização;
6. permitir que usuários de uma unidade vejam dados de outra por falha de consulta;
7. misturar privilégio administrativo SaaS com privilégio operacional do cliente;
8. deixar ações sensíveis sem auditoria;
9. superconceder permissões a perfis genéricos;
10. não diferenciar leitura, edição, aprovação e exportação.

## 24. Conclusão técnica

O controle de acesso do SIGERD precisa ser pensado como uma malha de proteção, e não como uma simples lista de perfis. A decisão correta aqui é combinar cinco dimensões:

- autenticação;
- perfil funcional;
- escopo institucional;
- plano/módulo contratado;
- auditoria.

Essa combinação é o que torna o sistema compatível com seu contexto real: um SaaS institucional com múltiplos órgãos, múltiplas unidades, operação sensível, contratos distintos e forte necessidade de responsabilização.

Sem isso, o sistema pode até funcionar visualmente, mas ficará vulnerável a vazamento de escopo, erros de governança e acesso indevido a módulos ou dados operacionais.