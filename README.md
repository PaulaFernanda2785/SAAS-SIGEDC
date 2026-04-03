# SIGERD - Fase 1 (Nucleo SaaS e Identidade Institucional)

Esta entrega evolui a fundacao tecnica da Fase 0 para uma base SaaS institucional funcional, com:

- area publica inicial (landing, planos e demonstracao);
- autenticacao com recuperacao de senha por token;
- cadastro institucional (contas, orgaos, unidades, usuarios e perfis);
- vinculo usuario-perfil;
- catalogo de planos;
- criacao de assinaturas;
- liberacao de modulos por assinatura;
- controle basico de status contratual no login;
- painel administrativo inicial com resumo SaaS.

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
```

5. Execute os seeds:

```sql
source database/seeds/001_phase0_seed.sql;
source database/seeds/002_phase1_seed.sql;
```

6. Opcional: gere autoload do Composer:

```bash
composer dump-autoload
```

## Usuario inicial

- Login: `admin@sigerd.local`
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

## Observacoes da Fase 1

- O login agora valida:
  - `status_usuario = ATIVO`
  - `status_cadastral da conta = ATIVA`
  - `status_orgao = ATIVO`
  - existencia de assinatura ativa/trial valida para a conta
  - modulo `AUTH` liberado na assinatura
- Em ambiente nao-producao, o fluxo de recuperacao de senha exibe token de teste via flash message.
