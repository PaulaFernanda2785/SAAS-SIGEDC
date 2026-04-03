# SIGERD - Fase 0 (Fundacao Tecnica)

Esta entrega implementa a fundacao tecnica inicial do SIGERD com:

- bootstrap central da aplicacao;
- rotas separadas por contexto;
- autenticacao base (login/logout);
- gestao de sessao com persistencia;
- logs tecnicos e auditoria funcional minima;
- schema inicial de banco com tabelas centrais.

## Requisitos

- PHP 8.3+
- MySQL 8+
- Apache (WampServer)

## Setup rapido

1. Copie o arquivo de ambiente:

```bash
copy .env.example .env
```

2. Ajuste credenciais em `.env`.
3. Crie o banco `sigerd`.
4. Execute o schema:

```sql
source database/schema/001_phase0_foundation.sql;
```

5. Execute o seed:

```sql
source database/seeds/001_phase0_seed.sql;
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

- `http://localhost/SAAS-SIGEDC/public/`

## Rotas base

- `GET /`
- `GET /login`
- `POST /login`
- `POST /logout`
- `GET /admin`
- `GET /operational`

