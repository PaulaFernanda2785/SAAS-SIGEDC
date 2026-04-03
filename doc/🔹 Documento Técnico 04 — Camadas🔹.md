**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Arquitetura em Camadas  
**Objetivo:** definir a organização lógica do sistema em camadas técnicas, distribuindo corretamente responsabilidades entre interface, aplicação, domínio e infraestrutura.

## 1. Finalidade do documento

Este documento estabelece a arquitetura em camadas do SIGERD. Seu propósito é orientar o desenvolvimento de forma organizada, previsível e sustentável, definindo onde cada tipo de responsabilidade deverá estar no sistema.

A principal função deste documento é impedir que a implementação se torne uma mistura descontrolada de HTML, regras de negócio, consultas SQL, permissões, validações e lógica operacional dentro de um único arquivo ou controller.

No contexto do SIGERD, isso é ainda mais importante porque o sistema possui três áreas distintas de produto, dois núcleos operacionais independentes e uma forte necessidade de rastreabilidade institucional e operacional.

## 2. Objetivos da arquitetura em camadas

A arquitetura em camadas do SIGERD deverá atender aos seguintes objetivos:

- separar responsabilidades técnicas;
- reduzir acoplamento entre interface e regra de negócio;
- facilitar manutenção e evolução do código;
- tornar os fluxos mais claros para desenvolvimento e auditoria;
- permitir reaproveitamento de regras comuns;
- melhorar a segurança estrutural do backend;
- preparar o sistema para crescer sem colapsar em arquivos monolíticos.

## 3. Modelo de camadas adotado

Para o SIGERD, o modelo mais adequado é uma arquitetura em **quatro camadas principais**, com possibilidade de subdivisões internas por domínio:

1. **Camada de Apresentação**
2. **Camada de Aplicação**
3. **Camada de Domínio**
4. **Camada de Infraestrutura e Persistência**

Esse modelo é o mais coerente para o porte do sistema porque equilibra simplicidade e robustez. Ele é suficientemente claro para um projeto web em PHP, mas já oferece maturidade arquitetural para um SaaS institucional com múltiplos módulos.

## 4. Visão geral das camadas

### 4.1. Camada de Apresentação

Responsável pela interface com o usuário.

### 4.2. Camada de Aplicação

Responsável por orquestrar fluxos, receber requisições, validar contexto de uso e acionar serviços do domínio.

### 4.3. Camada de Domínio

Responsável pelas regras de negócio do sistema.

### 4.4. Camada de Infraestrutura e Persistência

Responsável pelo acesso a banco de dados, armazenamento de arquivos, autenticação técnica, logs, integrações e recursos externos.

## 5. Princípios de separação entre camadas

O SIGERD deverá obedecer às seguintes regras de separação:

1. a interface não deve conter regra de negócio crítica;
2. controllers não devem concentrar lógica de domínio extensa;
3. regras operacionais não devem depender da estrutura visual da página;
4. acesso ao banco não deve ficar espalhado pelas views;
5. validações de segurança e escopo não devem existir apenas no frontend;
6. integrações externas devem ser isoladas da lógica de negócio central;
7. auditoria e controle de acesso devem atravessar múltiplas camadas, mas com responsabilidade definida.

## 6. Camada de Apresentação

## 6.1. Finalidade

A camada de apresentação é responsável por tudo que o usuário vê, interage e aciona visualmente. Ela representa a face do sistema nas três áreas do produto:

- área pública comercial;
- área administrativa SaaS;
- área operacional institucional.

## 6.2. Responsabilidades da camada de apresentação

Esta camada deverá ser responsável por:

- renderização de páginas;
- layout institucional e operacional;
- formulários;
- tabelas;
- componentes visuais;
- cards, gráficos e dashboards;
- filtros visuais;
- mensagens ao usuário;
- navegação;
- menus;
- headers e footers;
- breadcrumbs;
- organização visual dos módulos.

## 6.3. O que não deve existir na camada de apresentação

A camada de apresentação **não deve**:

- decidir regra de negócio;
- aplicar limite de assinatura como única barreira;
- montar lógica complexa de incidente;
- calcular estados operacionais críticos;
- consultar diretamente o banco de forma dispersa;
- validar autorização apenas pelo que aparece ou deixa de aparecer no menu;
- concentrar regras de workflow.

## 6.4. Subdivisões recomendadas da camada de apresentação

A camada de apresentação pode ser subdividida em:

- apresentação pública;
- apresentação administrativa;
- apresentação operacional;
- componentes compartilhados;
- componentes de mapa;
- componentes de gráfico;
- componentes de formulário;
- layouts base.

## 6.5. Aplicação no SIGERD

Exemplos do que pertence a essa camada no SIGERD:

- landing page do sistema;
- tela de planos;
- login;
- dashboard administrativo;
- painel operacional;
- tela de ocorrências;
- tela de PLANCON;
- tela de relatórios;
- tela de cadastro institucional;
- tela de alteração de senha.

## 7. Camada de Aplicação

## 7.1. Finalidade

A camada de aplicação atua como coordenadora dos fluxos do sistema. Ela recebe a requisição, entende o contexto do usuário, chama a regra apropriada do domínio e devolve a resposta correta para a interface.

Ela não deve ser confundida com o domínio. Sua função não é “ser o cérebro do negócio”, mas sim “organizar a execução do caso de uso”.

## 7.2. Responsabilidades da camada de aplicação

Esta camada deverá ser responsável por:

- receber requisições HTTP;
- acionar controllers;
- identificar ação solicitada;
- validar contexto da requisição;
- verificar autenticação;
- aplicar autorização inicial;
- acionar serviços de domínio;
- processar filtros e paginações;
- coordenar uploads e downloads;
- montar respostas para views, JSON, PDF ou planilhas;
- orquestrar fluxos de criação, edição, consulta, ativação, encerramento e exportação.

## 7.3. Componentes típicos da camada de aplicação

No SIGERD, essa camada deverá conter principalmente:

- controllers;
- actions ou casos de uso, quando adotados;
- requests/validações de entrada;
- middlewares;
- handlers de exportação;
- coordenadores de fluxo por módulo.

## 7.4. O que não deve existir na camada de aplicação

A camada de aplicação **não deve**:

- conter SQL espalhado diretamente em cada controller;
- centralizar toda a lógica do PLANCON;
- decidir sozinha regras de negócio profundas;
- guardar regras de cobrança ou limite de assinatura em blocos improvisados;
- manter cálculos de estado operacional complexos sem delegar ao domínio.

## 7.5. Aplicação no SIGERD

Exemplos de responsabilidades dessa camada:

- controller de login valida a requisição e aciona o serviço de autenticação;
- controller de assinatura recebe dados do formulário e chama o serviço que valida o plano contratado;
- controller de incidente recebe abertura de ocorrência e aciona o serviço de criação do incidente;
- controller de relatório recebe filtros e aciona o serviço gerador do relatório;
- controller do PLANCON recebe a atualização de um bloco e chama a regra do domínio correspondente.

## 8. Camada de Domínio

## 8.1. Finalidade

A camada de domínio é a camada mais crítica do SIGERD. É nela que ficam as regras do negócio, os estados válidos, as validações institucionais, os limites operacionais, os fluxos lógicos dos módulos e as decisões centrais do sistema.

Se o projeto errar essa camada, o restante do sistema vira apenas interface conectada a banco de dados, sem consistência operacional.

## 8.2. Responsabilidades da camada de domínio

A camada de domínio deverá ser responsável por:

- regras de negócio;
- validações funcionais;
- políticas operacionais;
- regras de status;
- transições de estado;
- integridade lógica entre entidades;
- restrições funcionais por plano;
- regras de escopo por conta, órgão e unidade;
- regras do PLANCON;
- regras do SCI/SCO;
- regras de incidente;
- regras de relatórios operacionais;
- regras de conformidade e rastreabilidade.

## 8.3. Estruturas recomendadas dentro do domínio

A camada de domínio pode ser organizada em:

- entidades;
- serviços de domínio;
- políticas;
- validadores de negócio;
- regras de transição;
- value objects, quando fizer sentido;
- enums ou catálogos estruturados;
- especificações por módulo.

## 8.4. Domínios centrais do SIGERD

No SIGERD, a camada de domínio deve ser organizada pelos seguintes blocos:

- domínio comercial;
- domínio SaaS;
- domínio institucional;
- domínio de identidade e acesso;
- domínio PLANCON;
- domínio de incidentes;
- domínio de relatórios;
- domínio de auditoria;
- domínio de anexos e evidências;
- domínio de conformidade.

## 8.5. Regras críticas que pertencem ao domínio

Exemplos de regras que devem estar nesta camada:

- verificar se a assinatura permite determinado módulo;
- verificar se o perfil pode operar em certo escopo;
- definir quando um incidente pode ser encerrado;
- definir quando uma estrutura CSI/SCO pode ser ativada;
- verificar consistência entre período operacional e incidente;
- validar transição de status da assinatura;
- validar limite de usuários, órgãos e unidades por plano;
- garantir vínculo entre estrutura, pessoas, instalações, períodos e registros;
- validar versão, vigência e revisão do plano de contingência.

## 8.6. O que não deve existir na camada de domínio

A camada de domínio **não deve**:

- renderizar HTML;
- depender diretamente da página que chamou a ação;
- conhecer detalhes visuais da interface;
- emitir SQL bruto como regra principal;
- depender de variáveis globais de sessão de maneira descontrolada.

## 9. Camada de Infraestrutura e Persistência

## 9.1. Finalidade

Essa camada dá suporte técnico à execução do sistema. Ela não define o negócio, mas torna o negócio possível.

É aqui que ficam os meios técnicos de persistência, armazenamento, autenticação técnica, logs, integração, emissão de arquivos e comunicação com recursos externos.

## 9.2. Responsabilidades da camada de infraestrutura

A camada de infraestrutura deverá ser responsável por:

- conexão com banco de dados;
- consultas e persistência;
- repositórios;
- armazenamento de anexos;
- upload e download de arquivos;
- geração de PDF e planilhas;
- envio de e-mail;
- logs técnicos;
- cache futuro;
- sessões;
- autenticação técnica;
- integrações com APIs externas;
- configuração do ambiente.

## 9.3. Componentes típicos

No SIGERD, essa camada pode conter:

- database connection;
- repositories;
- gateways de integração;
- storage service;
- mail service;
- pdf service;
- export service;
- session manager;
- config loader;
- logger;
- audit persister.

## 9.4. O que não deve existir na infraestrutura

A infraestrutura **não deve**:

- decidir regra de negócio de forma autônoma;
- determinar fluxo funcional do incidente;
- impor interpretação de permissões sem apoio da camada de domínio;
- controlar sozinha o que é válido no PLANCON.

## 10. Relação entre as camadas

O fluxo recomendado entre camadas no SIGERD deverá seguir esta lógica:

**Apresentação → Aplicação → Domínio → Infraestrutura**

E, no retorno:

**Infraestrutura → Domínio → Aplicação → Apresentação**

Isso significa que:

- a view chama o controller;
- o controller organiza o caso de uso;
- o caso de uso aciona serviço de domínio;
- o serviço usa persistência e infraestrutura quando necessário;
- o resultado sobe novamente até ser exibido ao usuário.

## 11. Fluxo prático por exemplo

## 11.1. Exemplo: abertura de incidente

**Apresentação**  
Usuário preenche formulário de abertura do incidente.

**Aplicação**  
Controller recebe requisição, valida entrada e aciona o caso de uso de abertura.

**Domínio**  
Serviço de incidente valida regras, escopo, integridade mínima e status inicial.

**Infraestrutura**  
Repositório grava incidente, anexo e log.

**Retorno**  
A aplicação monta resposta e a apresentação exibe confirmação e redirecionamento.

## 11.2. Exemplo: criação de assinatura

**Apresentação**  
Administrador preenche dados da assinatura.

**Aplicação**  
Controller interpreta a ação e envia os dados ao serviço de assinatura.

**Domínio**  
Serviço valida plano, limites, situação da conta e regras contratuais.

**Infraestrutura**  
Repositório salva assinatura e dados auxiliares.

**Retorno**  
Sistema exibe confirmação e atualiza dashboard administrativo.

## 12. Camadas aplicadas às três áreas do produto

A arquitetura em camadas deve existir nas três áreas, mas com intensidades diferentes.

### 12.1. Área pública

Predominância de apresentação e aplicação leve.  
Domínio mais simples, centrado em planos, demonstração, contato e autenticação.

### 12.2. Área administrativa SaaS

Uso equilibrado das quatro camadas.  
Forte domínio em contratos, módulos, limites, faturamento, contas e auditoria.

### 12.3. Área operacional

Predominância forte do domínio e da aplicação.  
É a parte com maior densidade de regra de negócio e rastreabilidade institucional.

## 13. Subcamadas recomendadas para o SIGERD

Dentro das quatro camadas principais, o SIGERD pode adotar subcamadas internas para manter organização.

### 13.1. Na apresentação

- layouts;
- páginas;
- partials;
- componentes;
- widgets;
- formulários;
- tabelas;
- componentes de mapa.

### 13.2. Na aplicação

- controllers;
- middlewares;
- requests;
- actions/use cases;
- dispatchers;
- exporters.

### 13.3. No domínio

- entities;
- services;
- policies;
- validators;
- rules;
- enums;
- orchestrators de módulos complexos.

### 13.4. Na infraestrutura

- repositories;
- db;
- storage;
- logging;
- sessions;
- integrations;
- pdf;
- mail;
- configuration.

## 14. Aplicação por domínio funcional

## 14.1. Domínio SaaS

A lógica de planos, assinaturas, faturamento, módulos liberados e limites por contrato deve ficar concentrada no domínio SaaS.

## 14.2. Domínio Institucional

A lógica de conta, órgão, unidade, usuários, perfis e escopo deve ficar concentrada no domínio institucional.

## 14.3. Domínio PLANCON

A lógica de blocos do plano, versões, vigência, cenários, ativação e núcleo CSI/SCO deve ficar no domínio PLANCON.

## 14.4. Domínio Incidentes

A lógica de abertura, comando, staff, operações, períodos, registros e encerramento deve ficar no domínio de incidentes.

## 14.5. Domínio Auditoria

A lógica de trilha, classificação do evento e persistência orientada a rastreabilidade deve ficar em domínio próprio, ainda que use infraestrutura de log.

## 15. Regras de dependência entre camadas

Para manter a integridade da arquitetura, recomenda-se as seguintes regras:

1. apresentação pode depender da aplicação, nunca do domínio puro de forma desordenada;
2. aplicação pode depender do domínio e da infraestrutura por abstração controlada;
3. domínio não deve depender da apresentação;
4. infraestrutura não deve comandar o fluxo do negócio;
5. repositórios não devem assumir papel de regra de negócio;
6. controllers não devem substituir serviços de domínio;
7. permissões não devem ser decididas apenas no frontend.

## 16. Benefícios esperados desta arquitetura em camadas

A adoção correta das camadas trará os seguintes ganhos:

- maior clareza de responsabilidade;
- menor risco de duplicação de regra;
- melhor organização do projeto;
- maior segurança estrutural;
- manutenção mais previsível;
- facilidade de teste e depuração;
- base mais sólida para evolução do produto;
- menor dependência de arquivos monolíticos.

## 17. Riscos se a arquitetura em camadas for ignorada

Os principais riscos são:

- controllers gigantes e difíceis de manter;
- regras de negócio duplicadas em telas diferentes;
- SQL espalhado por views e páginas;
- permissões inconsistentes;
- falhas de escopo institucional;
- dificuldade para evoluir o módulo de incidente;
- dificuldade para crescer o SaaS comercial;
- baixa rastreabilidade operacional.

## 18. Diretriz estratégica para implementação

A arquitetura em camadas do SIGERD deve ser aplicada com pragmatismo. O risco aqui não é “usar poucas camadas”, mas usar muitas subdivisões artificiais sem ganho real. O sistema precisa de organização forte, mas não de complexidade ornamental.

A recomendação mais consistente é:

- quatro camadas principais;
- modularização por domínio;
- serviços fortes no domínio;
- controllers enxutos;
- persistência isolada;
- interface limpa e desacoplada.

Esse arranjo atende bem à realidade de um SaaS institucional robusto em PHP sem tornar o projeto excessivamente burocrático.

## 19. Conclusão técnica

O SIGERD exige uma arquitetura em camadas porque sua natureza funcional é complexa: comercialização SaaS, administração institucional, plano de contingência, gerenciamento de incidentes, relatórios, painéis, auditoria e controle de acesso convivem no mesmo produto.

Sem camadas bem definidas, o sistema tende a se degenerar em páginas acopladas ao banco. Com camadas bem definidas, ele ganha base real para crescer com segurança, clareza e manutenção sustentável.