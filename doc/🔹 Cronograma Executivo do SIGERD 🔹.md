**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Objetivo:** organizar a implantação do sistema em fases executáveis, com visão de prioridade, dependência e carga relativa de trabalho.

## 1. Finalidade do cronograma

Este cronograma executivo serve para:

- orientar a implantação real do sistema;
- definir o que entra primeiro e o que depende de quê;
- evitar paralelismo caótico entre módulos;
- facilitar acompanhamento gerencial e técnico;
- apoiar tomada de decisão sobre escopo da primeira entrega.

## 2. Escala de esforço relativo

Para este cronograma, o esforço relativo foi classificado em:

- **Baixo**: implementação curta, com baixa complexidade estrutural.
- **Médio**: implementação com dependências moderadas e integração entre componentes.
- **Alto**: implementação com forte dependência de backend, banco, controle de acesso e interface.
- **Muito alto**: implementação estruturalmente densa, com múltiplos submódulos e alto risco de retrabalho.

## 3. Escala de prioridade

A prioridade foi classificada em:

- **Crítica**: sem isso, o sistema não se sustenta.
- **Alta**: gera valor direto e deve entrar cedo.
- **Média**: importante, mas depende de base já estável.
- **Evolutiva**: recomendada para fases mais maduras do produto.

---

# 4. Cronograma executivo por fase

## Fase 0 — Preparação técnica e fundação do projeto

### Objetivo da fase

Construir a base arquitetural e operacional mínima do projeto antes da entrada dos módulos de negócio.

|Entrega|Dependências|Prioridade|Esforço|
|---|---|---|---|
|Estrutura inicial do repositório|nenhuma|Crítica|Baixo|
|Bootstrap da aplicação|estrutura inicial|Crítica|Médio|
|Configuração de ambiente|bootstrap|Crítica|Baixo|
|Roteamento por contexto|bootstrap|Crítica|Médio|
|Estrutura de pastas e arquivos-base|estrutura inicial|Crítica|Médio|
|Conexão com banco de dados|ambiente|Crítica|Baixo|
|Tratamento global de erros|bootstrap|Alta|Médio|
|Base de autenticação|banco + rotas|Crítica|Médio|
|Base de sessão|autenticação|Crítica|Baixo|
|Estrutura inicial de auditoria|banco + autenticação|Alta|Médio|
|Estrutura inicial de anexos|banco + storage|Média|Médio|
|Schema inicial de tabelas centrais|banco|Crítica|Alto|

### Marco de encerramento da fase

O projeto sobe, autentica, conecta ao banco, separa áreas e já possui fundação técnica confiável.

---

## Fase 1 — Núcleo SaaS e identidade institucional

### Objetivo da fase

Implantar a base comercial, contratual e institucional do sistema.

|Entrega|Dependências|Prioridade|Esforço|
|---|---|---|---|
|Landing page pública inicial|Fase 0|Alta|Médio|
|Página de planos|landing page|Alta|Baixo|
|Página de demonstração e contato|landing page|Média|Baixo|
|Login e recuperação de senha|autenticação base|Crítica|Médio|
|Cadastro de contas contratantes|banco base + auth|Crítica|Médio|
|Cadastro de órgãos|contas|Crítica|Médio|
|Cadastro de unidades|órgãos|Alta|Médio|
|Cadastro de usuários|contas + órgãos + unidades|Crítica|Alto|
|Cadastro de perfis|auth + banco base|Crítica|Médio|
|Vínculo usuário-perfil|usuários + perfis|Crítica|Médio|
|Catálogo de planos|banco comercial|Alta|Médio|
|Criação de assinaturas|contas + planos|Crítica|Alto|
|Liberação de módulos por assinatura|assinaturas + módulos|Crítica|Médio|
|Dashboard administrativo inicial|contas + assinaturas|Alta|Médio|
|Controle básico de status contratual|assinaturas + módulos|Alta|Médio|

### Marco de encerramento da fase

O sistema já consegue cadastrar cliente, estruturar órgão/unidade/usuário e controlar assinatura com módulos liberados.

---

## Fase 2 — Núcleo operacional mínimo viável

### Objetivo da fase

Entregar a primeira versão operacional realmente utilizável pelo cliente.

|Entrega|Dependências|Prioridade|Esforço|
|---|---|---|---|
|Painel operacional inicial|usuários + escopo + módulos|Alta|Médio|
|Cadastro e listagem de incidentes|núcleo institucional + auth|Crítica|Alto|
|Abertura de incidente|incidentes base|Crítica|Médio|
|Briefing inicial|incidente base|Alta|Médio|
|Comando inicial do incidente|incidente base|Alta|Médio|
|Períodos operacionais|incidente base|Crítica|Alto|
|Objetivos do incidente|períodos operacionais|Alta|Médio|
|Registros operacionais / diário|incidente + período|Crítica|Alto|
|Recursos mobilizados (versão inicial)|incidente base|Alta|Médio|
|Relatórios operacionais básicos|incidentes + registros|Alta|Médio|
|Exportação PDF/planilha básica|relatórios básicos|Média|Médio|
|Auditoria de ações operacionais críticas|incidente + auth + logs|Crítica|Médio|
|Aplicação completa do escopo institucional nas consultas|usuários + perfis + dados operacionais|Crítica|Alto|

### Marco de encerramento da fase

O cliente já consegue abrir, conduzir e acompanhar incidentes com rastreabilidade mínima confiável.

---

## Fase 3 — Expansão do PLANCON e do gerenciamento de desastres

### Objetivo da fase

Expandir os dois núcleos centrais do sistema: gestão do risco e resposta operacional.

|Entrega|Dependências|Prioridade|Esforço|
|---|---|---|---|
|Estrutura principal do PLANCON|fases 0, 1 e 2|Crítica|Alto|
|Bloco de identificação geral do plano|estrutura do PLANCON|Alta|Baixo|
|Bloco de território|PLANCON base|Alta|Médio|
|Bloco de riscos|PLANCON base|Crítica|Médio|
|Bloco de cenários|riscos|Alta|Médio|
|Bloco de níveis de ativação|cenários|Alta|Médio|
|Bloco de recursos do plano|PLANCON base|Alta|Médio|
|Bloco de monitoramento e comunicação|PLANCON base|Média|Médio|
|Bloco de procedimentos|PLANCON base|Média|Médio|
|Bloco de rotas, pontos de apoio e abrigos|PLANCON base|Média|Médio|
|Bloco de assistência|PLANCON base|Média|Médio|
|Bloco de simulados e capacitações|PLANCON base|Evolutiva|Médio|
|Revisão e versionamento do plano|PLANCON base|Alta|Alto|
|Anexos do PLANCON|storage documental|Alta|Médio|
|Staff do comando detalhado|incidente base|Alta|Médio|
|Staff geral / seções funcionais|comando inicial|Alta|Médio|
|Estratégias, táticas e PAI|objetivos + períodos|Alta|Alto|
|Operações de campo|PAI + recursos|Alta|Alto|
|Planejamento e situação|períodos + objetivos|Alta|Médio|
|Instalações do incidente|incidente base|Média|Médio|
|Comunicações integradas|incidente base|Média|Médio|
|Segurança operacional|incidente base|Média|Médio|
|Desmobilização básica|incidente + registros|Alta|Médio|

### Marco de encerramento da fase

O SIGERD passa a operar não só incidentes, mas também a gestão preventiva estruturada via PLANCON.

---

## Fase 4 — Inteligência operacional, documentos e governança avançada

### Objetivo da fase

Refinar leitura situacional, governança documental, conformidade e gestão analítica.

|Entrega|Dependências|Prioridade|Esforço|
|---|---|---|---|
|Mapa operacional em destaque|incidentes + geodados|Alta|Alto|
|Camadas geográficas operacionais|mapa base|Média|Alto|
|Gráficos operacionais avançados|relatórios básicos|Média|Médio|
|Dashboard administrativo avançado|assinaturas + financeiro|Média|Médio|
|Relatórios administrativos ampliados|financeiro + contas|Média|Médio|
|Serviço documental central completo|anexos base|Crítica|Alto|
|Vínculos documentais por entidade|serviço documental|Alta|Alto|
|Persistência de relatórios gerados|relatórios + anexos|Média|Médio|
|Persistência de exportações|exportações básicas|Média|Médio|
|Auditoria reforçada|logs + operações críticas|Alta|Médio|
|Conformidade legal ampliada|assinaturas + aceite|Média|Médio|
|Financeiro ampliado|faturas + status contratual|Média|Alto|

### Marco de encerramento da fase

O sistema passa a oferecer leitura gerencial, rastreabilidade documental e governança mais madura.

---

## Fase 5 — Escala institucional, integrações e recursos enterprise

### Objetivo da fase

Preparar o SIGERD para contas maiores, integrações e expansão de mercado.

|Entrega|Dependências|Prioridade|Esforço|
|---|---|---|---|
|Suporte multiunidade/multiórgão ampliado|base institucional estável|Alta|Alto|
|Recursos avançados por plano enterprise/governo|assinatura + módulos|Média|Médio|
|API controlada|backend estável + auth madura|Evolutiva|Alto|
|Integrações externas|API ou gateways internos|Evolutiva|Muito alto|
|Assinatura digital|segurança + documentos|Evolutiva|Alto|
|Automações operacionais|services estáveis|Evolutiva|Alto|
|Analytics avançado|dados consolidados|Evolutiva|Alto|
|Relatórios executivos consolidados|analytics + relatórios|Média|Médio|
|Governança avançada para grandes contas|multiunidade + auditoria + contratos|Alta|Alto|

### Marco de encerramento da fase

O SIGERD passa a operar em nível maduro para ambientes enterprise/governo.

---

# 5. Linha de dependência entre fases

|Fase|Depende de|
|---|---|
|Fase 0|nenhuma|
|Fase 1|Fase 0|
|Fase 2|Fase 0 + Fase 1|
|Fase 3|Fase 2 estabilizada|
|Fase 4|Fase 3 funcional|
|Fase 5|Fase 4 consolidada|

## Regra crítica

A Fase 3 não deve avançar de forma agressiva se a Fase 2 ainda estiver inconsistente em escopo, registros operacionais ou controle de acesso.

---

# 6. Priorização executiva geral

## Prioridade máxima do projeto

Os itens abaixo devem ser tratados como núcleo do núcleo:

- autenticação e sessão;
- contas, órgãos, unidades e usuários;
- perfis e escopo institucional;
- planos, assinaturas e módulos;
- incidente base;
- períodos operacionais;
- registros operacionais;
- auditoria mínima;
- relatórios básicos;
- fundação do banco.

## Prioridade alta

- PLANCON base;
- revisão e versionamento;
- briefing, comando e staff;
- recursos e instalações;
- exportação controlada;
- mapa operacional inicial.

## Prioridade média

- gráficos avançados;
- conformidade ampliada;
- financeiro refinado;
- persistência de relatórios gerados;
- dashboards executivos avançados.

## Prioridade evolutiva

- API;
- integrações;
- assinatura digital;
- automações;
- analytics avançado.

---

# 7. Esforço relativo por fase

|Fase|Esforço global|
|---|---|
|Fase 0|Médio|
|Fase 1|Alto|
|Fase 2|Alto|
|Fase 3|Muito alto|
|Fase 4|Alto|
|Fase 5|Muito alto|

## Leitura crítica

A fase mais densa do ponto de vista funcional tende a ser a **Fase 3**, porque ela expande simultaneamente PLANCON e SCI/SCO.  
A fase mais densa do ponto de vista técnico-evolutivo tende a ser a **Fase 5**, por causa de integrações, API e escala institucional.

---

# 8. Proposta de marcos executivos

## Marco 1 — Sistema fundado

Conclui Fase 0.

## Marco 2 — SaaS institucional utilizável

Conclui Fase 1.

## Marco 3 — Operação mínima viável entregue

Conclui Fase 2.

## Marco 4 — Plataforma institucional robusta

Conclui Fase 3.

## Marco 5 — Governança e inteligência ampliadas

Conclui Fase 4.

## Marco 6 — Produto maduro para escala

Conclui Fase 5.

---

# 9. Recomendação prática de execução

A melhor estratégia não é abrir muitas frentes em paralelo. O ideal é trabalhar com **frentes coordenadas por dependência**:

- **Frente A:** fundação técnica e banco
- **Frente B:** autenticação, conta, órgão, unidade, usuário
- **Frente C:** assinatura, planos e módulos
- **Frente D:** incidente e operação mínima
- **Frente E:** PLANCON
- **Frente F:** relatórios, mapa e documentos
- **Frente G:** enterprise, integrações e evolução

Mas essas frentes só devem correr em paralelo quando a dependência estrutural permitir. O erro seria abrir F, G e E antes de estabilizar B, C e D.

---

# 10. Conclusão executiva

O cronograma do SIGERD mostra que a ordem mais inteligente de implantação é:

1. fundação técnica;
2. identidade institucional e contrato SaaS;
3. operação mínima real;
4. expansão funcional robusta;
5. inteligência e governança;
6. escala e integrações.

Essa sequência maximiza três coisas ao mesmo tempo:

- viabilidade técnica;
- entrega de valor real;
- preservação da arquitetura.