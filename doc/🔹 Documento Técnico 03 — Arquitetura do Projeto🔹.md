**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Arquitetura do Projeto  
**Objetivo:** definir a arquitetura macro do sistema, a organização estrutural dos módulos, a separação entre camadas do produto e a lógica técnica recomendada para desenvolvimento, manutenção, escalabilidade e governança.

## 1. Finalidade do documento

Este documento estabelece a arquitetura do projeto SIGERD em nível estrutural. Seu propósito é organizar tecnicamente o sistema antes da definição detalhada de banco de dados, controladores, serviços, relacionamentos e estrutura de pastas.

A arquitetura do projeto precisa refletir corretamente a natureza do produto, que não é apenas um painel administrativo ou um sistema cadastral simples. O SIGERD é um SaaS institucional com:

- camada pública comercial;
- camada administrativa de negócio SaaS;
- camada operacional institucional;
- dois grandes núcleos funcionais independentes: gestão do risco e gerenciamento do desastre.

## 2. Princípios arquiteturais do projeto

A arquitetura do projeto deverá seguir os seguintes princípios:

1. separação clara entre contexto comercial, contexto administrativo e contexto operacional;
2. desacoplamento entre regras de negócio e interface;
3. modularização por domínio funcional;
4. centralidade da conta contratante e do órgão operador no modelo institucional;
5. rastreabilidade operacional dos módulos críticos;
6. controle de acesso por perfil, escopo, plano e módulo liberado;
7. escalabilidade para operação multiunidade e multiórgão;
8. manutenção facilitada, com responsabilidades bem distribuídas.

## 3. Visão macro da arquitetura do SIGERD

A arquitetura do projeto deve ser organizada em **três áreas principais de produto**, sustentadas por uma **base compartilhada de autenticação, controle institucional, permissões, auditoria e serviços comuns**.

### 3.1. Áreas principais do sistema

**Área 1 — Camada pública comercial**  
Responsável por apresentação institucional, posicionamento do produto, geração de confiança, captação de leads, planos e entrada para autenticação.

**Área 2 — Camada administrativa SaaS**  
Responsável pela governança comercial e contratual do produto: contas, órgãos, planos, assinaturas, módulos, faturamento, conformidade e administração global.

**Área 3 — Camada interna operacional**  
Responsável pela operação real do cliente dentro da plataforma: painéis, planos de contingência, gerenciamento do desastre, mapas, relatórios, recursos, comunicações, cadastro institucional e segurança do usuário.

## 4. Macrodomínios arquiteturais

O projeto deverá ser dividido em macrodomínios de negócio, e não apenas em páginas.

### 4.1. Domínio Comercial

Abrange:

- site público;
- planos;
- demonstração;
- contato;
- funil de conversão;
- entrada de acesso.

### 4.2. Domínio SaaS Administrativo

Abrange:

- contas contratantes;
- órgãos e instituições;
- unidades;
- usuários administrativos;
- planos de assinatura;
- assinaturas;
- faturas;
- módulos liberados;
- conformidade;
- auditoria administrativa.

### 4.3. Domínio Institucional

Abrange:

- órgãos operadores;
- unidades institucionais;
- usuários operacionais;
- perfis;
- permissões;
- escopos de acesso.

### 4.4. Domínio de Gestão do Risco

Abrange:

- planos de contingência;
- análise territorial;
- riscos;
- cenários;
- ativação operacional;
- governança;
- recursos;
- monitoramento;
- comunicação;
- procedimentos;
- abrigos;
- assistência;
- simulados;
- revisões;
- núcleo CSI/SCO vinculado ao plano.

### 4.5. Domínio de Gerenciamento do Desastre

Abrange:

- incidentes;
- briefing inicial;
- comando;
- staff do comando;
- staff geral;
- objetivos;
- estratégias, táticas e PAI;
- operações;
- planejamento e situação;
- recursos;
- instalações;
- comunicações;
- segurança;
- informação pública;
- ligação interinstitucional;
- administração e finanças;
- períodos operacionais;
- registros;
- desmobilização.

### 4.6. Domínio de Visualização e Inteligência Operacional

Abrange:

- painel do usuário;
- mapa operacional;
- gráficos;
- indicadores;
- relatórios operacionais;
- relatórios administrativos;
- filtros e exportações.

### 4.7. Domínio Transversal

Abrange:

- autenticação;
- autorização;
- auditoria;
- anexos;
- notificações;
- logs;
- configurações;
- segurança da conta;
- sessões e conformidade.

## 5. Estilo arquitetural recomendado

Para o SIGERD, o estilo mais adequado é uma **arquitetura modular em camadas**, com inspiração em MVC expandido, separando:

- apresentação;
- aplicação;
- domínio;
- infraestrutura.

Isso evita dois erros comuns:

1. concentrar regras críticas dentro das views ou controllers;
2. construir um sistema procedural excessivamente acoplado, difícil de manter.

A recomendação técnica é usar uma arquitetura com as seguintes faixas:

- **camada de interface**: páginas, layouts, componentes visuais, formulários e tabelas;
- **camada de aplicação**: controllers e orquestração de casos de uso;
- **camada de domínio**: regras de negócio, validações, serviços e entidades;
- **camada de persistência/infraestrutura**: repositórios, banco de dados, arquivos, autenticação técnica, integrações e logs.

Essa abordagem é coerente com o porte e a criticidade do produto.

## 6. Estrutura arquitetural por áreas

## 6.1. Arquitetura da Área Pública

A área pública deve ser isolada da operação interna. Ela não deve depender diretamente da lógica pesada da camada operacional, exceto por serviços comuns controlados, como autenticação, formulários de contato e consulta de planos ativos.

### Componentes da área pública:

- páginas institucionais;
- componentes de marketing;
- componente de planos;
- formulário de demonstração;
- formulário de contato;
- autenticação inicial;
- páginas auxiliares legais.

### Objetivo arquitetural:

- carregamento leve;
- SEO e performance;
- baixa complexidade transacional;
- separação do restante do sistema.

## 6.2. Arquitetura da Área Administrativa SaaS

A área administrativa deve operar como um backend de negócio do produto, com proteção reforçada e foco em governança comercial.

### Componentes da área administrativa:

- dashboard administrativo;
- contas contratantes;
- órgãos/instituições;
- unidades;
- usuários administrativos;
- planos;
- assinaturas;
- faturamento;
- módulos contratados;
- configurações;
- conformidade;
- auditoria.

### Objetivo arquitetural:

- centralizar gestão do negócio;
- controlar monetização;
- impor limites de uso;
- manter governança institucional.

## 6.3. Arquitetura da Área Operacional

A área operacional é o núcleo mais complexo do sistema. Ela deve ser tratada como ambiente de execução institucional, com foco em clareza, velocidade, rastreabilidade e coerência operacional.

### Componentes da área operacional:

- painel do usuário;
- ocorrências/incidentes;
- gerenciamento do desastre;
- plano de contingência;
- mapa operacional;
- recursos;
- comunicações;
- relatórios;
- cadastro institucional;
- conta e segurança.

### Objetivo arquitetural:

- permitir operação contínua;
- garantir leitura situacional;
- suportar fluxo operacional;
- manter vínculo entre pessoas, estruturas, períodos e registros.

## 7. Núcleos centrais da arquitetura operacional

Do ponto de vista arquitetural, a camada operacional do SIGERD precisa ser dividida em dois núcleos independentes.

### 7.1. Núcleo 1 — Gestão do risco

Responsável por:

- construção e manutenção do plano de contingência;
- análise de risco e cenários;
- ativação pré-estruturada;
- governança preventiva;
- preparação institucional.

### 7.2. Núcleo 2 — Gerenciamento do desastre

Responsável por:

- abertura de incidente;
- coordenação da resposta;
- comando;
- controle de operações;
- gestão de recursos;
- registro da evolução do evento;
- encerramento e desmobilização.

### Regra arquitetural importante

Esses dois núcleos podem se relacionar, mas não devem ser fundidos numa única estrutura. O erro seria modelar o plano de contingência e o incidente como se fossem um só objeto. O correto é permitir vínculo entre eles, preservando a independência funcional de cada domínio.

## 8. Arquitetura lógica do projeto

A arquitetura lógica do projeto pode ser representada da seguinte forma:

**Camada de Apresentação**

- páginas públicas;
- páginas administrativas;
- páginas operacionais;
- layouts;
- componentes;
- formulários;
- tabelas;
- dashboards;
- gráficos;
- mapas.

**Camada de Aplicação**

- controladores por módulo;
- orquestração de fluxos;
- autenticação;
- autorização;
- despacho de ações;
- processamento de filtros;
- exportações;
- upload e download de anexos.

**Camada de Domínio**

- serviços do negócio;
- validações;
- políticas de acesso;
- regras de assinatura;
- regras de limite por plano;
- regras do PLANCON;
- regras do SCI/SCO;
- regras de auditoria;
- regras de status e transição.

**Camada de Persistência e Infraestrutura**

- banco de dados;
- repositórios;
- armazenamento de arquivos;
- logs;
- filas futuras, se adotadas;
- serviços de e-mail/notificação;
- integrações externas;
- sessões;
- cache futuro, se necessário.

## 9. Organização por módulos arquiteturais

A arquitetura do projeto deve ser orientada por módulos. Cada módulo precisa ter fronteira funcional clara.

### 9.1. Módulo Público

Responsabilidades:

- apresentação institucional;
- marketing e planos;
- formulários comerciais;
- acesso.

### 9.2. Módulo de Identidade e Acesso

Responsabilidades:

- login;
- logout;
- recuperação de senha;
- sessão;
- 2FA;
- controle de troca de senha;
- bloqueios de acesso.

### 9.3. Módulo SaaS

Responsabilidades:

- contas;
- assinaturas;
- planos;
- faturas;
- módulos contratados;
- termos e conformidade.

### 9.4. Módulo Institucional

Responsabilidades:

- órgãos;
- unidades;
- usuários;
- perfis;
- permissões;
- escopo de acesso.

### 9.5. Módulo PLANCON

Responsabilidades:

- dados gerais do plano;
- território;
- riscos;
- cenários;
- ativação;
- governança;
- recursos;
- alerta/comunicação;
- procedimentos;
- abrigos;
- assistência;
- simulados;
- revisão;
- anexos;
- CSI/SCO do plano.

### 9.6. Módulo Incidentes

Responsabilidades:

- cadastro da ocorrência;
- briefing;
- comando;
- staff;
- objetivos;
- táticas;
- operações;
- planejamento;
- recursos;
- instalações;
- comunicações;
- segurança;
- registros;
- desmobilização.

### 9.7. Módulo de Painel e Inteligência

Responsabilidades:

- dashboard operacional;
- gráficos;
- indicadores;
- leitura situacional;
- widgets.

### 9.8. Módulo de Mapas

Responsabilidades:

- georreferenciamento;
- exibição de incidentes;
- áreas afetadas;
- instalações;
- recursos;
- abrigos;
- camadas territoriais.

### 9.9. Módulo de Relatórios

Responsabilidades:

- filtros;
- visualização tabular;
- exportação PDF;
- exportação planilha;
- relatórios administrativos;
- relatórios operacionais.

### 9.10. Módulo de Auditoria e Logs

Responsabilidades:

- trilha de acesso;
- logs operacionais;
- logs administrativos;
- rastreabilidade institucional.

## 10. Relacionamento entre módulos

A arquitetura do projeto deve evitar acoplamento excessivo. Os módulos se relacionam por dependência lógica, mas com fronteiras claras.

### Relações principais:

- o módulo SaaS controla limites e liberação de módulos;
- o módulo institucional controla quem pode operar;
- o módulo de identidade controla autenticação e sessão;
- o módulo PLANCON usa base institucional e permissões;
- o módulo Incidentes usa base institucional, permissões e recursos;
- o módulo Painel consome dados de incidentes, recursos, registros e mapas;
- o módulo Relatórios consome dados de quase todos os módulos;
- o módulo Auditoria recebe eventos transversais de toda a plataforma.

## 11. Arquitetura de navegação do projeto

A navegação deve refletir a arquitetura, não apenas a interface.

### 11.1. Navegação pública

- Home
- Solução
- Funcionalidades
- Planos
- Demonstração
- Contato
- Login

### 11.2. Navegação administrativa

- Dashboard
- Contas
- Órgãos
- Assinaturas
- Planos
- Pagamentos
- Módulos
- Usuários
- Relatórios
- Configurações
- Logs

### 11.3. Navegação operacional

- Painel
- Ocorrências
- Gerenciamento do desastre
- Mapa
- Recursos
- Comunicações
- Relatórios
- Planos de contingência
- Cadastro institucional
- Conta e segurança

Essa separação é arquiteturalmente correta porque cada navegação corresponde a um contexto distinto de uso.

## 12. Arquitetura de responsabilidade por contexto

Uma decisão importante para o SIGERD é definir responsabilidade técnica por contexto.

### Contexto Comercial

Responsável por aquisição e entrada de clientes.

### Contexto Administrativo

Responsável por monetização, contrato, governança do negócio e administração do ambiente SaaS.

### Contexto Operacional

Responsável por execução do trabalho institucional do cliente.

### Contexto Transversal

Responsável por segurança, sessão, notificação, anexos, logs, conformidade e serviços compartilhados.

Essa separação reduz risco de confusão entre regras comerciais e regras operacionais.

## 13. Arquitetura de segurança estrutural

O projeto precisa prever segurança desde a arquitetura, não apenas na fase de implementação.

### Diretrizes arquiteturais:

- toda área interna deve exigir autenticação;
- rotas administrativas e operacionais devem ser segregadas;
- permissões devem ser verificadas no backend;
- limites contratuais devem ser validados no backend;
- logs devem registrar ações sensíveis;
- módulos bloqueados por assinatura não podem ser acessados apenas por ocultação de menu;
- upload de arquivos e anexos deve ser tratado como infraestrutura controlada;
- perfis com escopo reduzido não podem consultar dados fora do seu órgão/unidade.

## 14. Arquitetura de dados em alto nível

Embora o detalhamento completo pertença ao documento de banco, a arquitetura do projeto já exige visão macro dos grandes grupos de dados.

### Grandes grupos:

- dados comerciais;
- dados institucionais;
- dados de identidade e acesso;
- dados de planos de contingência;
- dados de incidentes;
- dados operacionais de comando;
- dados de recursos e instalações;
- dados de comunicação;
- dados de relatórios e exportações;
- dados de auditoria e conformidade.

Esses grupos não devem ser embaralhados em um único bloco monolítico. A arquitetura precisa preservar coesão por domínio.

## 15. Arquitetura de evolução do projeto

O SIGERD deve nascer com estrutura que permita crescimento progressivo.

### Fase inicial recomendada:

- área pública;
- autenticação;
- núcleo institucional;
- dashboard básico;
- ocorrências/incidentes;
- relatórios iniciais;
- plano de contingência básico;
- assinaturas e planos.

### Fase intermediária:

- SCI/SCO completo;
- mapa operacional robusto;
- auditoria ampliada;
- faturamento mais completo;
- comunicação operacional;
- recursos e instalações.

### Fase avançada:

- integrações externas;
- API;
- assinatura digital avançada;
- automações;
- analytics mais sofisticado;
- trilhas de governança e BI operacional.

## 16. Riscos arquiteturais a evitar

Há alguns erros de projeto que precisam ser evitados desde já.

### 16.1. Misturar as três áreas do produto

Se a área pública, administrativa e operacional forem tratadas com a mesma lógica, o sistema perde clareza, escalabilidade e governança.

### 16.2. Centralizar tudo no usuário

Em SaaS institucional, o centro do modelo é a conta contratante e o órgão operador. O usuário é um agente dentro dessa estrutura, não o eixo absoluto do sistema.

### 16.3. Transformar SCI/SCO em formulário simples

Os blocos operacionais foram concebidos para formar uma cadeia rastreável. Se forem reduzidos a campos soltos, a arquitetura perde valor operacional.

### 16.4. Acoplamento excessivo entre módulos

Quando um controller ou tela concentra múltiplas regras de vários domínios, o sistema se torna frágil.

### 16.5. Tratar relatórios como parte final isolada

Relatórios devem nascer da arquitetura de dados e de domínio desde o início.

## 17. Representação textual da arquitetura do projeto

A arquitetura recomendada pode ser resumida da seguinte forma:

**Nível 1 — Produto**

- Área Pública
- Área Administrativa SaaS
- Área Operacional

**Nível 2 — Domínios**

- Comercial
- SaaS
- Institucional
- Gestão do Risco
- Gerenciamento do Desastre
- Painel e Inteligência
- Mapas
- Relatórios
- Segurança e Auditoria

**Nível 3 — Componentes**

- páginas
- controladores
- serviços
- entidades
- repositórios
- arquivos
- integrações
- logs

**Nível 4 — Infraestrutura**

- banco de dados
- autenticação
- armazenamento
- notificações
- sessões
- configurações
- monitoramento técnico

## 18. Conclusão técnica

A arquitetura do projeto SIGERD deve ser modular, em camadas e orientada por domínio. Essa é a única forma consistente de sustentar um produto que, ao mesmo tempo, precisa:

- vender-se como SaaS;
- administrar contratos e assinaturas;
- suportar operação institucional complexa;
- documentar planos de contingência;
- gerenciar incidentes com lógica de comando e coordenação;
- manter rastreabilidade, governança e escalabilidade.

Arquiteturalmente, o projeto já deixa claro que não se trata de um sistema simples de cadastro e relatórios. É uma plataforma institucional com múltiplos contextos de uso, e isso exige uma separação rigorosa entre