# SIGERD - Fase 5 (Escala institucional, integracoes e recursos enterprise)

Esta entrega adiciona a Fase 5 sobre a fundacao das fases anteriores:

- area publica inicial (landing, planos e demonstracao);
- autenticacao com recuperacao de senha por token;
- cadastro institucional (contas, orgaos, unidades, usuarios e perfis);
- catalogo de planos, assinaturas e modulos;
- controle contratual no login e trilha de auditoria;
- painel operacional com indicadores;
- abertura e listagem de incidentes;
- briefing inicial do incidente;
- comando inicial do incidente;
- periodos operacionais;
- registros operacionais (diario);
- relatorio operacional basico por escopo institucional.
- modulo PLANCON com blocos de risco, cenario, ativacao, recursos e revisao.
- modulo de expansao de desastres com PAI, operacoes, planejamento, seguranca e desmobilizacao.
- inteligencia operacional com hotspots, tendencia e pontos de mapa.
- inteligencia operacional com motor de alertas ativos por regra.
- documentos operacionais com upload auditado e vinculo por entidade.
- documentos operacionais com download seguro por escopo institucional.
- governanca operacional com aceite de termo, trilha de auditoria e conformidade.
- relatorio operacional avancado com registro de execucao e consolidacao analitica.
- area enterprise administrativa com API controlada, integracoes, automacoes, SLA/suporte e assinatura digital.
- endpoint API enterprise para resumo executivo com autenticacao por chave.
- relatorio executivo consolidado persistido para governanca de contas maiores.

## Requisitos

- PHP 8.3+
- MySQL 8+
- Apache (WampServer)

## Setup rapido

1. Copie o arquivo de ambiente:

```bash
copy .env.example .env
```

2. Ajuste credenciais em `.env` e garanta para uso com VirtualHost:
   - `APP_URL=http://sigerd.local`
   - `APP_BASE_PATH=`
3. Crie o banco `sigerd`.
4. Execute os schemas:

```sql
source database/schema/001_phase0_foundation.sql;
source database/schema/002_phase1_saas_core.sql;
source database/schema/003_phase2_operational_core.sql;
source database/schema/004_phase3_plancon_disaster_expansion.sql;
source database/schema/005_phase3_uf_territorios.sql;
source database/schema/006_phase4_intelligence_documents_governance.sql;
source database/schema/007_phase5_enterprise_scale_integrations.sql;
```

5. Execute os seeds:

```sql
source database/seeds/001_phase0_seed.sql;
source database/seeds/002_phase1_seed.sql;
source database/seeds/003_phase2_seed.sql;
source database/seeds/004_phase3_seed.sql;
source database/seeds/005_phase3_uf_seed.sql;
source database/seeds/006_phase4_seed.sql;
source database/seeds/007_phase5_seed.sql;
```

6. Opcional: gere autoload do Composer:

```bash
composer dump-autoload
```

7. Importe os municipios/UFs a partir dos CSVs da pasta `territorios`:

```bash
php scripts/import_territorios_csv.php
```

Importacao focada por UF (exemplo AM):

```bash
php scripts/import_territorios_csv.php --uf=AM
```

8. Execute os testes de integracao de contexto UF:

```bash
php tests/integration/uf_context_integration_test.php
```

## Usuario inicial

- Login: `admin@sigerd.local`
- Senha: `Admin@123`

## Usuario operacional de teste

- Login: `operador@sigerd.local`
- Senha: `Admin@123`

## Entrada web

Configure o Apache para apontar para `public/` e acesse:

- `http://sigerd.local`

## Rotas publicas

- `GET /`
- `GET /planos`
- `GET /demonstracao`
- `GET /login`
- `POST /login`
- `GET /forgot-password`
- `POST /forgot-password`
- `GET /reset-password`
- `POST /reset-password`

## Rotas administrativas (area.admin)

- `GET /admin`
- `GET /admin/institucional`
- `POST /admin/institucional/contas`
- `POST /admin/institucional/orgaos`
- `POST /admin/institucional/unidades`
- `POST /admin/institucional/usuarios`
- `POST /admin/institucional/perfis`
- `POST /admin/institucional/vinculos`
- `GET /admin/comercial`
- `POST /admin/comercial/planos`
- `POST /admin/comercial/assinaturas`
- `POST /admin/comercial/modulos`
- `GET /admin/enterprise`
- `POST /admin/enterprise/features`
- `POST /admin/enterprise/api-apps`
- `POST /admin/enterprise/integracoes`
- `POST /admin/enterprise/automacoes`
- `POST /admin/enterprise/sla`
- `POST /admin/enterprise/tickets`
- `POST /admin/enterprise/assinaturas-digitais`
- `POST /admin/enterprise/relatorios-executivos`

## Rotas operacionais

- `GET /operational`
- `GET /operational/incidentes`
- `POST /operational/incidentes`
- `POST /operational/incidentes/briefing`
- `POST /operational/incidentes/comando`
- `POST /operational/incidentes/periodos`
- `POST /operational/incidentes/registros`
- `GET /operational/plancon`
- `POST /operational/plancon`
- `POST /operational/plancon/riscos`
- `POST /operational/plancon/cenarios`
- `POST /operational/plancon/ativacao`
- `POST /operational/plancon/recursos`
- `POST /operational/plancon/revisoes`
- `GET /operational/desastres`
- `POST /operational/desastres/pai`
- `POST /operational/desastres/operacoes`
- `POST /operational/desastres/planejamento`
- `POST /operational/desastres/seguranca`
- `POST /operational/desastres/desmobilizacao`
- `GET /operational/relatorios/basico`
- `GET /operational/relatorios/basico/export`
- `GET /operational/relatorios/avancado`
- `GET /operational/relatorios/avancado/export`
- `GET /operational/inteligencia`
- `GET /operational/documentos`
- `GET /operational/documentos/download`
- `POST /operational/documentos/upload`
- `GET /operational/governanca`
- `POST /operational/governanca/termo-aceite`

## Observacoes da Fase 5

- O login agora valida:
  - `status_usuario = ATIVO`
  - `status_cadastral da conta = ATIVA`
  - `status_orgao = ATIVO`
  - existencia de assinatura ativa/trial valida para a conta
  - modulo `AUTH` liberado na assinatura
- As rotas operacionais validam:
  - area operacional;
  - modulo contratado `OPERATIONAL`;
  - modulo contratado especifico para `PLANCON` e `DISASTER_EXPANSION`;
  - perfil operacional permitido;
  - escopo institucional (conta/orgao/unidade) em consultas sensiveis.
- O backend bloqueia duplo POST com token processado recentemente (5s) e frontend com `form-guard`.
- Em ambiente nao-producao, o fluxo de recuperacao de senha exibe token de teste via flash message.
- A camada administrativa agora padroniza UF de origem em:
  - `contas`, `orgaos`, `unidades`, `usuarios` e `assinaturas`.
- `ADMIN_MASTER` pode consultar todos os UFs; os demais perfis administrativos operam apenas no UF de contexto.
- A referencia territorial usa `territorios_ufs` e `territorios_municipios`, com carga executavel via CSV.
- Endpoint de autocomplete territorial:
  - `GET /api/territorios/ufs`
  - `GET /api/territorios/municipios?uf=TO&q=pal`
  - `GET /api/enterprise/executivo` (header `X-Api-Key` ou `Authorization: Bearer ...`)
  - protegido por autenticacao e escopo UF.
  - cache backend por `UF+prefixo` em `storage/cache/territory` para reduzir carga de consulta.
- As rotas de Fase 4 validam:
  - modulo contratado `INTELLIGENCE`, `DOCUMENTS`, `GOVERNANCE` e `ADV_REPORTS`;
  - perfil operacional permitido por politica de acesso;
  - escopo institucional (conta/orgao/unidade) em consultas e anexos sensiveis;
  - registro de auditoria para bloqueios de acesso e aceite de termo.
- Inteligencia operacional:
  - motor de alertas com persistencia em `inteligencia_alertas_operacionais`;
  - regras iniciais para concentracao de hotspots e atraso/ausencia de briefing;
  - visao de alertas ativos no painel de inteligencia e no relatorio avancado.
- Documentos operacionais:
  - upload com CSRF + protecao de duplo submit;
  - validacao de MIME/tamanho no backend;
  - persistencia em `storage/attachments/{conta}/{orgao}/...` com hash SHA-256;
  - vinculo obrigatorio por entidade operacional em escopo;
  - download seguro com verificacao de visibilidade/escopo e auditoria de acesso.
- Governanca operacional:
  - aceite do termo vigente configurado em `config/governance.php`;
  - trilha de logs e frequencia de acoes criticas;
  - historico de aceites por usuario/versao.
- Relatorio operacional avancado:
  - consolidacao de tendencia, hotspots, frequencia auditada e anexos por entidade;
  - exportacao inicial em planilha (CSV) e PDF;
  - persistencia de exportacoes com caminho de arquivo em `relatorios_avancados_execucoes`.
- Enterprise:
  - novo middleware `enterprise.access` para rotas administrativas de escala enterprise;
  - novo middleware `api.key` para API controlada por token hash (sem segredo em banco/log);
  - modulo contratado obrigatorio por capacidade (`ENTERPRISE_CORE`, `API_ENTERPRISE`, `INTEGRACOES_EXTERNAS`, `AUTOMACOES`, `ANALYTICS_EXECUTIVO`, `SLA_SUPORTE`, `ASSINATURA_DIGITAL`);
  - trilha de auditoria para operacoes criticas enterprise e recusas de acesso;
  - visao executiva consolidada por conta/orgao/unidade com persistencia em `relatorios_executivos_consolidados`.

## CI (GitHub Actions)

- Workflow: `.github/workflows/php.yml`
- Disparo automatico: a cada `push` e `pull_request`
- Pipeline:
  - sobe MySQL 8.4
  - executa matriz PHP 8.3 e 8.4
  - aplica `database/schema/*.sql` e `database/seeds/*.sql`
  - executa `php tests/integration/uf_context_integration_test.php`
  - executa `php tests/integration/phase4_operational_integration_test.php`
  - executa `php tests/integration/report_export_integration_test.php`
  - executa `php tests/integration/phase5_enterprise_integration_test.php`
