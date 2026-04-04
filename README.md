# SIGERD - Fase 3 (Expansao do PLANCON e do gerenciamento de desastres)

Esta entrega adiciona a Fase 3 sobre a fundacao das fases anteriores:

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
```

5. Execute os seeds:

```sql
source database/seeds/001_phase0_seed.sql;
source database/seeds/002_phase1_seed.sql;
source database/seeds/003_phase2_seed.sql;
source database/seeds/004_phase3_seed.sql;
```

6. Opcional: gere autoload do Composer:

```bash
composer dump-autoload
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

## Observacoes da Fase 3

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
