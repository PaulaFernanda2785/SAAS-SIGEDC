**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Estrutura de Pastas  
**Objetivo:** definir a organização física do projeto em diretórios e arquivos, de forma modular, escalável, legível e coerente com a arquitetura do sistema.

## 1. Finalidade do documento

Este documento estabelece a estrutura de pastas recomendada para o SIGERD, com foco em:

- separação entre área pública, área administrativa e área operacional;
- organização por camadas e por domínio;
- desacoplamento entre backend, frontend e infraestrutura;
- previsibilidade para manutenção;
- facilidade de crescimento do projeto;
- redução de acoplamento entre módulos.

O objetivo não é apenas “organizar arquivos”, mas refletir fisicamente a arquitetura do sistema no repositório do projeto.

## 2. Princípios para definição da estrutura de pastas

A estrutura de pastas do SIGERD deverá obedecer aos seguintes princípios:

1. separação por responsabilidade;
2. separação entre arquivos públicos e arquivos internos;
3. separação entre backend e recursos visuais;
4. agrupamento por domínio de negócio;
5. agrupamento por camada técnica quando necessário;
6. reutilização de componentes compartilhados;
7. isolamento de arquivos sensíveis;
8. facilidade de navegação para desenvolvimento e manutenção;
9. compatibilidade com crescimento modular do sistema.

## 3. Diretriz estrutural geral

A estrutura recomendada para o SIGERD é baseada em um núcleo de aplicação organizado em módulos e camadas, exposto por uma pasta pública controlada. Em termos práticos, o projeto deve ter:

- uma pasta pública acessível pela web;
- uma pasta da aplicação com backend;
- uma pasta de views e componentes visuais;
- uma pasta de configuração;
- uma pasta de rotas;
- uma pasta de armazenamento;
- uma pasta de logs e arquivos gerados;
- uma pasta de testes, se adotada;
- uma pasta de banco de dados e scripts, se adotada.

## 4. Estrutura-mestra recomendada

A organização física de alto nível do projeto pode ser assim:

SIGERD/  
├── app/  
├── bootstrap/  
├── config/  
├── database/  
├── public/  
├── resources/  
├── routes/  
├── storage/  
├── tests/  
├── vendor/  
├── .env  
├── .env.example  
├── composer.json  
├── composer.lock  
├── README.md

Essa estrutura é tecnicamente adequada porque:

- `public/` concentra apenas o que pode ser exposto à web;
- `app/` concentra a aplicação;
- `resources/` concentra views e ativos-fonte;
- `storage/` concentra arquivos gerados e anexos;
- `config/` concentra configuração;
- `routes/` concentra definição de rotas;
- `database/` concentra scripts e estrutura de dados.

## 5. Descrição das pastas principais

## 5.1. Pasta `app/`

É a principal pasta do backend. Deve conter o núcleo da aplicação.

### Finalidade:

- controllers;
- services;
- repositories;
- policies;
- middlewares;
- entities/models;
- helpers internos;
- classes de domínio;
- componentes de infraestrutura interna.

### Estrutura sugerida:

app/  
├── Controllers/  
├── Services/  
├── Repositories/  
├── Policies/  
├── Middleware/  
├── Requests/  
├── Models/  
├── Domain/  
├── Support/  
├── Exceptions/  
├── Traits/  
└── Helpers/

## 5.2. Pasta `bootstrap/`

Responsável pela inicialização do sistema.

### Finalidade:

- boot da aplicação;
- carga de ambiente;
- autoload complementar;
- inicialização de sessão;
- configuração inicial de timezone;
- tratamento global de erros e exceções.

### Estrutura sugerida:

bootstrap/  
├── app.php  
├── autoload.php  
├── session.php  
├── errors.php  
└── environment.php

## 5.3. Pasta `config/`

Concentra arquivos de configuração.

### Finalidade:

- banco de dados;
- autenticação;
- sessão;
- e-mail;
- paths;
- armazenamento;
- integração futura;
- parâmetros globais do sistema.

### Estrutura sugerida:

config/  
├── app.php  
├── database.php  
├── auth.php  
├── session.php  
├── mail.php  
├── storage.php  
├── audit.php  
├── plans.php  
└── integrations.php

## 5.4. Pasta `database/`

Concentra artefatos de banco de dados.

### Finalidade:

- schema inicial;
- migrations, se adotadas;
- seeds, se adotados;
- scripts utilitários;
- dicionários auxiliares de implantação.

### Estrutura sugerida:

database/  
├── schema/  
├── migrations/  
├── seeds/  
└── scripts/

## 5.5. Pasta `public/`

É a única pasta que deve ficar exposta ao servidor web.

### Finalidade:

- index.php de entrada;
- assets compilados ou públicos;
- uploads públicos controlados, se houver;
- arquivos estáticos.

### Estrutura sugerida:

public/  
├── index.php  
├── assets/  
│   ├── css/  
│   ├── js/  
│   ├── images/  
│   ├── icons/  
│   └── fonts/  
├── uploads/  
└── favicon.ico

### Diretriz crítica:

nenhum arquivo sensível de configuração, regra de negócio ou SQL deve ficar dentro de `public/`.

## 5.6. Pasta `resources/`

Deve concentrar recursos visuais e arquivos de interface.

### Finalidade:

- views;
- layouts;
- componentes;
- templates de PDF;
- e-mails;
- arquivos-fonte de frontend.

### Estrutura sugerida:

resources/  
├── views/  
├── emails/  
├── pdf/  
└── frontend/

## 5.7. Pasta `routes/`

Concentra a definição de rotas do sistema.

### Finalidade:

- rotas públicas;
- rotas administrativas;
- rotas operacionais;
- rotas de autenticação;
- agrupamento por contexto.

### Estrutura sugerida:

routes/  
├── web.php  
├── auth.php  
├── public.php  
├── admin.php  
├── operational.php  
└── api.php

## 5.8. Pasta `storage/`

Concentra arquivos gerados ou armazenados pela aplicação.

### Finalidade:

- logs;
- anexos;
- exports;
- PDFs gerados;
- arquivos temporários;
- cache futuro;
- evidências operacionais.

### Estrutura sugerida:

storage/  
├── logs/  
├── cache/  
├── temp/  
├── exports/  
├── reports/  
├── attachments/  
│   ├── contracts/  
│   ├── invoices/  
│   ├── plancon/  
│   ├── incidents/  
│   ├── evidence/  
│   └── institutional/

### Diretriz crítica:

arquivos sensíveis devem ser servidos por mediação do backend, e não por acesso direto irrestrito.

## 5.9. Pasta `tests/`

Opcional, mas recomendada.

### Finalidade:

- testes unitários;
- testes funcionais;
- testes de serviços críticos;
- testes de autorização;
- testes de fluxo.

## 5.10. Pasta `vendor/`

Gerenciada pelo Composer.

### Finalidade:

- dependências externas da aplicação.

### Diretriz:

não editar manualmente bibliotecas dentro dessa pasta.

---

# 6. Estrutura interna recomendada da pasta `app/`

A pasta `app/` é o núcleo mais importante. Abaixo está a organização recomendada em mais detalhe.

## 6.1. `app/Controllers/`

Deve conter os controllers organizados por contexto.

app/Controllers/  
├── Public/  
├── Auth/  
├── Admin/  
└── Operational/

### Finalidade por subpasta:

- `Public/`: páginas públicas, demonstração, contato, planos;
- `Auth/`: login, logout, recuperação de senha;
- `Admin/`: contas, planos, assinaturas, faturas, módulos, usuários administrativos;
- `Operational/`: painel, incidentes, PLANCON, relatórios, mapas, recursos, comunicações.

## 6.2. `app/Services/`

Deve conter os services organizados por domínio.

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

### Observação:

essa pasta precisa ser forte e bem organizada, porque o SIGERD depende de services para preservar a integridade da regra de negócio.

## 6.3. `app/Repositories/`

Deve conter a persistência desacoplada dos controllers.

app/Repositories/  
├── SaaS/  
├── Institutional/  
├── Plancon/  
├── Incident/  
├── Reports/  
├── Audit/  
└── Shared/

## 6.4. `app/Policies/`

Deve conter regras de autorização por contexto.

app/Policies/  
├── AuthPolicy.php  
├── AccountPolicy.php  
├── OrgPolicy.php  
├── UserPolicy.php  
├── SubscriptionPolicy.php  
├── PlanconPolicy.php  
├── IncidentPolicy.php  
├── ReportPolicy.php  
└── AuditPolicy.php

## 6.5. `app/Middleware/`

Deve conter filtros técnicos e institucionais.

app/Middleware/  
├── Authenticate.php  
├── CheckModuleAccess.php  
├── CheckSubscriptionStatus.php  
├── CheckOrgScope.php  
├── CheckAdminArea.php  
├── CheckOperationalArea.php  
├── VerifyCsrfToken.php  
└── LogSensitiveAccess.php

## 6.6. `app/Requests/`

Deve conter validadores de entrada.

app/Requests/  
├── Auth/  
├── SaaS/  
├── Institutional/  
├── Plancon/  
├── Incident/  
├── Reports/  
└── Shared/

## 6.7. `app/Models/`

Deve conter entidades ou modelos centrais.

app/Models/  
├── Account.php  
├── Organization.php  
├── Unit.php  
├── User.php  
├── Profile.php  
├── Plan.php  
├── Subscription.php  
├── Invoice.php  
├── Plancon.php  
├── Risk.php  
├── Scenario.php  
├── Incident.php  
├── OperationalPeriod.php  
├── OperationalRecord.php  
├── Attachment.php  
└── AuditLog.php

## 6.8. `app/Domain/`

Deve conter classes de domínio, enums, regras e estruturas mais conceituais.

app/Domain/  
├── Auth/  
├── SaaS/  
├── Institutional/  
├── Plancon/  
├── Incident/  
├── Audit/  
├── Shared/  
└── Enum/

### Observação importante:

se a equipe preferir um projeto mais pragmático, parte do domínio pode residir em `Services/`. Mas, se o sistema crescer como previsto, a pasta `Domain/` passa a ser bastante útil para preservar regras complexas.

## 6.9. `app/Support/`

Deve conter utilitários internos.

app/Support/  
├── Arr.php  
├── Str.php  
├── DateHelper.php  
├── Response.php  
├── Pagination.php  
└── FileHelper.php

## 6.10. `app/Exceptions/`

Deve conter exceções customizadas.

app/Exceptions/  
├── DomainException.php  
├── AuthorizationException.php  
├── ValidationException.php  
├── BusinessRuleException.php  
└── FileStorageException.php

---

# 7. Estrutura interna recomendada da pasta `resources/`

## 7.1. `resources/views/`

Deve refletir as três áreas do produto.

resources/views/  
├── layouts/  
├── components/  
├── public/  
├── auth/  
├── admin/  
└── operational/

## 7.2. `resources/views/layouts/`

resources/views/layouts/  
├── public.php  
├── admin.php  
└── operational.php

## 7.3. `resources/views/components/`

resources/views/components/  
├── header/  
├── sidebar/  
├── footer/  
├── cards/  
├── tables/  
├── filters/  
├── modals/  
├── charts/  
└── maps/

## 7.4. `resources/views/public/`

resources/views/public/  
├── home.php  
├── solution.php  
├── features.php  
├── plans.php  
├── demo.php  
├── contact.php  
└── about.php

## 7.5. `resources/views/auth/`

resources/views/auth/  
├── login.php  
├── forgot-password.php  
├── reset-password.php  
└── two-factor.php

## 7.6. `resources/views/admin/`

resources/views/admin/  
├── dashboard/  
├── accounts/  
├── organizations/  
├── subscriptions/  
├── plans/  
├── invoices/  
├── modules/  
├── users/  
├── reports/  
├── settings/  
└── audit/

## 7.7. `resources/views/operational/`

resources/views/operational/  
├── dashboard/  
├── incidents/  
├── plancon/  
├── maps/  
├── resources/  
├── communications/  
├── reports/  
├── institutional/  
└── account/

---

# 8. Estrutura específica dos módulos operacionais

Como o SIGERD possui dois núcleos operacionais densos, a pasta de views e parte do backend devem refletir isso.

## 8.1. Estrutura do módulo PLANCON

### Em views:

resources/views/operational/plancon/  
├── index.php  
├── create.php  
├── edit.php  
├── show.php  
├── blocks/  
│   ├── identification.php  
│   ├── territory.php  
│   ├── risks.php  
│   ├── scenarios.php  
│   ├── activation-levels.php  
│   ├── governance.php  
│   ├── resources.php  
│   ├── monitoring.php  
│   ├── procedures.php  
│   ├── shelters-routes.php  
│   ├── assistance.php  
│   ├── trainings.php  
│   ├── reviews.php  
│   ├── attachments.php  
│   └── csi-sco/  
│       ├── structure.php  
│       ├── teams.php  
│       ├── facilities.php  
│       ├── periods.php  
│       └── records.php

### Justificativa:

o PLANCON não deve ser um único formulário longo. A estrutura de pastas já precisa refletir sua modularidade.

## 8.2. Estrutura do módulo Incidentes

### Em views:

resources/views/operational/incidents/  
├── index.php  
├── create.php  
├── show.php  
├── edit.php  
├── briefing.php  
├── command.php  
├── command-staff.php  
├── general-staff.php  
├── objectives.php  
├── strategies.php  
├── field-operations.php  
├── planning.php  
├── resources.php  
├── facilities.php  
├── communications.php  
├── safety.php  
├── public-information.php  
├── liaison.php  
├── finance.php  
├── periods.php  
├── records.php  
└── demobilization.php

### Justificativa:

o módulo de incidentes é operacionalmente mais complexo que um CRUD comum. A estrutura de arquivos precisa sinalizar isso desde o início.

---

# 9. Estrutura recomendada da pasta `routes/`

A divisão de rotas não deve ser monolítica.

## Estrutura recomendada:

routes/  
├── public.php  
├── auth.php  
├── admin.php  
├── operational.php  
├── api.php  
└── console.php

### Explicação:

- `public.php`: páginas públicas e formulários comerciais;
- `auth.php`: login, logout, redefinição de senha;
- `admin.php`: rotas da administração SaaS;
- `operational.php`: rotas da operação do cliente;
- `api.php`: integrações futuras e endpoints internos;
- `console.php`: tarefas agendadas ou comandos internos, se adotados.

---

# 10. Estrutura recomendada da pasta `public/assets/`

Os assets públicos devem ser bem segmentados.

public/assets/  
├── css/  
│   ├── public/  
│   ├── admin/  
│   ├── operational/  
│   └── shared/  
├── js/  
│   ├── public/  
│   ├── admin/  
│   ├── operational/  
│   └── shared/  
├── images/  
│   ├── branding/  
│   ├── public/  
│   ├── admin/  
│   ├── operational/  
│   └── icons/  
├── maps/  
└── vendor/

### Diretriz:

não misturar CSS e JS da área pública com os da área operacional. Isso reduz conflito, facilita manutenção e preserva identidade de cada contexto.

---

# 11. Estrutura recomendada para armazenamento de anexos

Como o SIGERD lida com documentos institucionais, operacionais e financeiros, a estrutura de armazenamento precisa ser explícita.

## Estrutura sugerida:

storage/attachments/  
├── accounts/  
├── organizations/  
├── users/  
├── subscriptions/  
├── invoices/  
├── plancon/  
│   ├── annexes/  
│   ├── maps/  
│   ├── simulations/  
│   └── revisions/  
├── incidents/  
│   ├── evidence/  
│   ├── briefings/  
│   ├── periods/  
│   ├── records/  
│   └── demobilization/  
├── reports/  
└── temp/

### Diretriz crítica:

a pasta física precisa refletir o domínio do arquivo, para evitar bagunça operacional e risco de acesso indevido.

---

# 12. Estrutura recomendada para geração de relatórios e exports

## Estrutura sugerida:

storage/reports/  
├── administrative/  
├── operational/  
├── plancon/  
└── financial/  
  
storage/exports/  
├── pdf/  
├── xlsx/  
├── csv/  
└── temp/

### Justificativa:

relatórios administrativos, operacionais e financeiros não devem ser tratados como a mesma coisa nem misturados em um diretório genérico.

---

# 13. Estrutura recomendada para logs e auditoria

## Estrutura sugerida:

storage/logs/  
├── app/  
├── auth/  
├── admin/  
├── operational/  
├── audit/  
└── integrations/

### Diferença importante:

- logs técnicos ficam em arquivos e servem manutenção;
- auditoria é dado funcional e institucional, normalmente persistido também em banco.

Ainda assim, a separação física por tipo de log é recomendável.

---

# 14. Estrutura de pastas orientada por domínio versus orientada por camada

Para o SIGERD, a melhor solução não é ser 100% orientada por camada nem 100% orientada por domínio. O melhor arranjo é híbrido.

## Recomendação prática:

- em nível macro, organizar por camada principal;
- dentro de services, repositories, requests e views, organizar por domínio.

Isso oferece equilíbrio entre clareza arquitetural e praticidade de manutenção.

### Exemplo:

`app/Services/Plancon/PlanconService.php`  
`app/Services/Incident/IncidentService.php`  
`resources/views/operational/plancon/index.php`  
`resources/views/operational/incidents/show.php`

Esse padrão é superior a uma pasta única com dezenas de arquivos sem contexto.

---

# 15. Estrutura física consolidada recomendada

Abaixo, uma visão consolidada mais completa:

SIGERD/  
├── app/  
│   ├── Controllers/  
│   │   ├── Public/  
│   │   ├── Auth/  
│   │   ├── Admin/  
│   │   └── Operational/  
│   ├── Services/  
│   │   ├── Auth/  
│   │   ├── SaaS/  
│   │   ├── Institutional/  
│   │   ├── Plancon/  
│   │   ├── Incident/  
│   │   ├── Reports/  
│   │   ├── Audit/  
│   │   ├── Files/  
│   │   ├── Export/  
│   │   └── Shared/  
│   ├── Repositories/  
│   │   ├── SaaS/  
│   │   ├── Institutional/  
│   │   ├── Plancon/  
│   │   ├── Incident/  
│   │   ├── Reports/  
│   │   ├── Audit/  
│   │   └── Shared/  
│   ├── Policies/  
│   ├── Middleware/  
│   ├── Requests/  
│   ├── Models/  
│   ├── Domain/  
│   ├── Support/  
│   ├── Exceptions/  
│   ├── Traits/  
│   └── Helpers/  
├── bootstrap/  
├── config/  
├── database/  
├── public/  
│   ├── index.php  
│   ├── assets/  
│   └── uploads/  
├── resources/  
│   ├── views/  
│   │   ├── layouts/  
│   │   ├── components/  
│   │   ├── public/  
│   │   ├── auth/  
│   │   ├── admin/  
│   │   └── operational/  
│   ├── emails/  
│   ├── pdf/  
│   └── frontend/  
├── routes/  
├── storage/  
│   ├── logs/  
│   ├── cache/  
│   ├── temp/  
│   ├── exports/  
│   ├── reports/  
│   └── attachments/  
├── tests/  
├── vendor/  
├── .env  
├── .env.example  
├── composer.json  
└── README.md

---

# 16. Regras estruturais para nomes de arquivos e diretórios

Para manter consistência, recomenda-se:

1. nomes de classes em padrão PascalCase;
2. nomes de arquivos de classe iguais ao nome da classe;
3. nomes de views em kebab-case ou snake_case padronizado;
4. nomes de diretórios de domínio curtos e claros;
5. evitar abreviações obscuras;
6. evitar mistura de português e inglês sem critério.

## Recomendação prática

Como o projeto está tecnicamente orientado a desenvolvimento web padronizado, a melhor escolha é:

- classes e backend em inglês técnico;
- textos visíveis ao usuário em português;
- nomes institucionais e conceitos do negócio preservados quando necessário, como `Plancon`, `Incident`, `OperationalPeriod`.

Isso tende a reduzir confusão no código.

---

# 17. Erros de estrutura de pastas que devem ser evitados

Há alguns erros clássicos que precisam ser evitados desde o início:

1. colocar tudo dentro de uma pasta `pages/`;
2. misturar controllers com views;
3. armazenar arquivos sensíveis em `public/`;
4. centralizar todos os módulos operacionais em poucos arquivos genéricos;
5. criar estrutura profunda demais sem necessidade real;
6. usar nomenclatura inconsistente entre módulos;
7. tratar PLANCON e Incidentes como simples CRUDs sem subdivisão física;
8. misturar arquivos administrativos e operacionais nos mesmos diretórios;
9. usar uma pasta única de uploads sem classificação por domínio;
10. armazenar regras de negócio dentro de templates de PDF ou views.

---

# 18. Conclusão técnica

A estrutura de pastas do SIGERD não é um detalhe cosmético. Ela é parte da arquitetura. Se for mal definida, a implementação tende a se tornar confusa, acoplada e difícil de manter. Se for bem definida desde o início, ela ajuda a preservar:

- a separação entre áreas do produto;
- a modularização por domínio;
- a clareza das camadas;
- a segurança de arquivos e anexos;
- a escalabilidade do backend;
- a rastreabilidade operacional.

Para o SIGERD, a estrutura recomendada precisa refletir sua natureza real: um SaaS institucional com área pública, gestão comercial, operação técnica, PLANCON, SCI/SCO e forte governança de acesso.