**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Implementações Técnicas  
**Objetivo:** definir as implementações técnicas recomendadas para o desenvolvimento do sistema, especificando como os módulos, fluxos, mecanismos e recursos deverão ser materializados no backend, frontend, banco, segurança, documentos e integrações.

## 1. Finalidade do documento

Este documento estabelece as implementações técnicas do SIGERD em nível aplicado. Seu propósito é converter a arquitetura do projeto em diretrizes concretas de construção do sistema.

A intenção aqui não é apenas dizer que o sistema terá backend, banco e telas. O foco é especificar tecnicamente como cada bloco relevante deverá ser implementado, reduzindo ambiguidade entre documentação e desenvolvimento.

Este documento responde, em essência:

- como os módulos deverão ser implementados;
- como os fluxos deverão ser processados;
- como os dados deverão ser persistidos e validados;
- como os arquivos deverão ser armazenados;
- como o controle de acesso deverá ser aplicado;
- como relatórios, mapas e painéis deverão ser entregues;
- como o projeto deverá ser preparado para crescimento futuro.

## 2. Objetivos das implementações técnicas

As implementações técnicas do SIGERD deverão atender aos seguintes objetivos:

- transformar a arquitetura definida em execução prática;
- reduzir decisões improvisadas durante o desenvolvimento;
- padronizar a forma de construir módulos;
- garantir coerência entre backend, frontend e banco;
- proteger a rastreabilidade institucional e operacional;
- preparar o sistema para manutenção e expansão;
- evitar acoplamento excessivo e retrabalho.

## 3. Princípios gerais de implementação

As implementações técnicas do SIGERD deverão seguir os seguintes princípios:

1. modularização por domínio;
2. separação entre área pública, administrativa e operacional;
3. backend orientado por controllers, services e repositories;
4. banco relacional com integridade referencial explícita;
5. segurança aplicada no backend;
6. formulários densos segmentados por blocos;
7. armazenamento documental centralizado e controlado;
8. relatórios implementados sobre datasets consistentes;
9. rastreabilidade de eventos críticos;
10. evolução incremental do produto.

---

# 4. Implementações técnicas da base do sistema

## IT-001 — Implementação do bootstrap da aplicação

O sistema deverá possuir um ponto central de inicialização responsável por:

- carregar ambiente;
- iniciar sessão;
- configurar timezone;
- registrar autoload;
- carregar rotas;
- preparar tratamento global de erros;
- iniciar os recursos essenciais da aplicação.

### Resultado esperado

A aplicação deverá iniciar de maneira previsível e isolada de regra de negócio.

## IT-002 — Implementação do roteamento por contexto

As rotas deverão ser implementadas de forma separada para:

- área pública;
- autenticação;
- área administrativa;
- área operacional;
- API futura, quando aplicável.

### Resultado esperado

Separação clara de fluxo e menor risco de colisão entre módulos.

## IT-003 — Implementação de middlewares

O sistema deverá implementar middlewares para:

- autenticação;
- verificação de área;
- verificação de módulo contratado;
- verificação de status da assinatura;
- verificação de escopo institucional;
- CSRF, quando aplicável;
- logging de acesso sensível.

### Resultado esperado

Barreiras técnicas consistentes antes do processamento do caso de uso.

---

# 5. Implementações técnicas da área pública

## IT-004 — Implementação da landing page pública

A página pública deverá ser implementada com:

- hero principal;
- seções de problema, solução, funcionalidades e planos;
- CTA para demonstração, contratação e acesso;
- responsividade;
- blocos institucionais claros.

### Observação

A implementação da área pública deve ser leve, orientada à apresentação e sem dependência de lógica operacional complexa.

## IT-005 — Implementação da página de planos

A página de planos deverá ser implementada de forma dinâmica, lendo os planos ativos do catálogo comercial ou de configuração controlada.

### Resultado esperado

Possibilidade de alterar oferta sem reconstrução manual excessiva da página.

## IT-006 — Implementação de formulários públicos

Os formulários de demonstração e contato deverão ser implementados com:

- validação de entrada;
- sanitização;
- persistência em tabela própria ou serviço de lead;
- retorno de sucesso/erro ao usuário;
- proteção básica contra abuso.

---

# 6. Implementações técnicas da autenticação e segurança inicial

## IT-007 — Implementação do login

O login deverá ser implementado com:

- formulário próprio;
- validação de credenciais;
- verificação de status do usuário;
- verificação de contexto institucional;
- abertura segura de sessão;
- redirecionamento conforme área autorizada.

## IT-008 — Implementação da recuperação de senha

A recuperação de senha deverá incluir:

- solicitação por e-mail/login;
- geração de token temporário;
- verificação de validade;
- redefinição segura da senha;
- invalidação do token após uso.

## IT-009 — Implementação do 2FA

A autenticação em dois fatores deverá ser implementável como recurso opcional por usuário ou por política de conta.

### Observação

Pode iniciar em fase posterior, mas a arquitetura deve prever seu encaixe desde já.

## IT-010 — Implementação da gestão de sessão

A camada de sessão deverá suportar:

- abertura;
- expiração;
- encerramento por logout;
- invalidação por segurança;
- registro de último acesso.

---

# 7. Implementações técnicas do domínio comercial / SaaS

## IT-011 — Implementação do cadastro de contas

O backend deverá implementar CRUD controlado de contas contratantes com:

- validações documentais mínimas;
- status cadastral;
- responsável contratual;
- dados de contato;
- observações administrativas.

## IT-012 — Implementação do catálogo de planos

Os planos deverão ser implementados como entidade administrativa editável, com:

- nome;
- preço;
- limites;
- módulos;
- exibição pública;
- status comercial.

## IT-013 — Implementação das assinaturas

A assinatura deverá ser implementada com:

- vínculo entre conta e plano;
- controle de vigência;
- ciclo de cobrança;
- status;
- teste gratuito;
- aceite contratual;
- renovação automática, quando aplicável.

## IT-014 — Implementação da liberação de módulos

Os módulos contratados deverão ser implementados com tabela própria e validação no backend.

### Resultado esperado

Mesmo que o menu esconda o módulo, o backend deverá bloquear sua execução quando não liberado.

## IT-015 — Implementação do faturamento

O módulo financeiro inicial deverá implementar:

- cadastro/registro de faturas;
- vencimento;
- pagamento;
- comprovante;
- status financeiro;
- vínculo com assinatura.

---

# 8. Implementações técnicas do domínio institucional

## IT-016 — Implementação do cadastro de órgãos

O sistema deverá implementar o cadastro de órgãos com separação explícita da conta contratante.

### Resultado esperado

A prefeitura ou ente contratante pode existir como conta, enquanto a Defesa Civil municipal opera como órgão vinculado.

## IT-017 — Implementação das unidades

As unidades deverão ser implementadas como subestrutura do órgão, com possibilidade de:

- sedes;
- regionais;
- bases;
- COMPDECs;
- salas de situação;
- centros logísticos.

## IT-018 — Implementação dos usuários

Os usuários deverão ser implementados com:

- vínculo com conta;
- vínculo com órgão;
- vínculo opcional com unidade;
- credenciais;
- status;
- foto;
- preferências básicas;
- segurança da conta.

## IT-019 — Implementação de perfis e permissões

O módulo de perfis deverá ser implementado com:

- perfil estruturado;
- escopo institucional;
- permissões por ação;
- associação N:N entre usuários e perfis;
- resolução de autorização no backend.

---

# 9. Implementações técnicas do módulo PLANCON

## IT-020 — Implementação do ciclo principal do plano

O módulo PLANCON deverá ser implementado com operações de:

- listar;
- criar;
- visualizar;
- editar;
- revisar;
- versionar;
- controlar vigência;
- controlar status.

## IT-021 — Implementação do PLANCON em blocos

Os blocos do plano deverão ser implementados separadamente, em nível de backend e interface.

### Blocos principais

- identificação geral;
- território;
- riscos;
- cenários;
- níveis de ativação;
- governança;
- recursos;
- monitoramento/comunicação;
- procedimentos;
- rotas/abrigos;
- assistência;
- simulados;
- revisões;
- anexos.

### Resultado esperado

Evitar um formulário único excessivamente longo e facilitar gravação modular.

## IT-022 — Implementação de versionamento do plano

O versionamento deverá ser implementado com:

- número ou código de versão;
- vínculo com versão anterior;
- data de revisão;
- usuário responsável;
- motivo de revisão;
- estado vigente ou histórico.

## IT-023 — Implementação de anexos do PLANCON

O módulo deverá permitir anexar:

- mapas;
- listas;
- formulários;
- fluxogramas;
- croquis;
- checklists.

### Requisito técnico

O armazenamento deverá ocorrer por serviço documental central, com vínculo por entidade.

---

# 10. Implementações técnicas do núcleo CSI/SCO do PLANCON

## IT-024 — Implementação da estrutura operacional do plano

A estrutura operacional vinculada ao plano deverá ser implementada como entidade própria, não como texto livre no plano.

### Deve permitir

- ativação;
- desativação;
- status;
- vínculo com cenário;
- comandante;
- objetivo operacional.

## IT-025 — Implementação de funções e equipes

As funções e equipes do CSI/SCO deverão ser implementadas com:

- função;
- categoria;
- responsável;
- substituto;
- turno;
- atribuições;
- status.

## IT-026 — Implementação de instalações operacionais

As instalações vinculadas ao plano deverão ser implementadas com:

- nome;
- tipo;
- endereço;
- capacidade;
- infraestrutura;
- status;
- coordenadas;
- responsável.

## IT-027 — Implementação de períodos operacionais do plano

O sistema deverá implementar períodos operacionais para a estrutura do plano com:

- número do período;
- data/hora de início;
- data/hora de término;
- objetivos;
- prioridades;
- estratégias;
- indicadores;
- avaliação de encerramento.

## IT-028 — Implementação dos registros operacionais do comando

O diário operacional do plano deverá ser implementado com:

- tipo de registro;
- classificação;
- criticidade;
- data/hora;
- descrição;
- decisão;
- encaminhamento;
- status;
- evidências.

---

# 11. Implementações técnicas do módulo de incidentes / SCI-SCO

## IT-029 — Implementação da abertura do incidente

O incidente deverá ser implementado como entidade operacional central, com:

- número da ocorrência;
- tipo;
- classificação;
- data/hora de abertura;
- localização;
- coordenadas;
- danos iniciais;
- riscos imediatos;
- status.

## IT-030 — Implementação do briefing inicial

O briefing deverá ser implementado em estrutura própria, com:

- resumo da situação;
- croqui/mapa inicial;
- objetivos iniciais;
- ações em andamento;
- recursos alocados;
- recursos solicitados;
- restrições;
- necessidades imediatas.

## IT-031 — Implementação do comando do incidente

O comando deverá ser implementado com:

- tipo de comando;
- comandante;
- instituição;
- assunção;
- transferência;
- base legal;
- restrições operacionais;
- status.

## IT-032 — Implementação do staff do comando e staff geral

As estruturas de staff deverão ser implementadas separadamente para manter clareza de papéis.

### Staff do comando

- segurança;
- informação pública;
- ligação;
- assessorias.

### Staff geral

- operações;
- planejamento;
- logística;
- administração/finanças;
- inteligência, quando aplicável.

## IT-033 — Implementação dos objetivos do incidente

Os objetivos deverão ser implementados como registros estruturados vinculados ao período operacional.

## IT-034 — Implementação do PAI e estratégias

As estratégias, táticas e o PAI deverão ser implementados com:

- estratégia definida;
- táticas previstas;
- atividades;
- responsáveis;
- recursos;
- áreas prioritárias;
- medidas de segurança;
- aprovação;
- versão.

## IT-035 — Implementação das operações de campo

As operações deverão ser implementadas com:

- frente operacional;
- setor/divisão;
- supervisor;
- missão tática;
- recursos designados;
- situação atual;
- resultado parcial.

## IT-036 — Implementação do planejamento e situação

O módulo deverá permitir:

- consolidar situação;
- registrar prognóstico;
- registrar cenário provável;
- apontar pendências;
- sinalizar escalonamento.

## IT-037 — Implementação do gerenciamento de recursos

Os recursos do incidente deverão ser implementados com:

- tipo;
- identificação;
- origem;
- quantidade;
- status;
- localização;
- supervisor;
- mobilização;
- chegada;
- desmobilização.

## IT-038 — Implementação das instalações do incidente

As instalações deverão ser implementadas com:

- tipo;
- nome;
- localização;
- capacidade;
- infraestrutura;
- ativação;
- desativação;
- situação operacional.

## IT-039 — Implementação das comunicações integradas

O sistema deverá permitir registrar:

- canal;
- frequência;
- finalidade;
- instituições vinculadas;
- usuários autorizados;
- falhas;
- responsável técnico.

## IT-040 — Implementação da segurança operacional

A segurança deverá ser implementada com:

- risco identificado;
- área afetada;
- público/equipe exposta;
- medida de controle;
- EPI obrigatório;
- restrição operacional;
- responsável.

## IT-041 — Implementação da informação pública

O módulo deverá registrar:

- tipo de comunicado;
- público-alvo;
- mensagem oficial;
- canal de divulgação;
- porta-voz;
- aprovação;
- rumor monitorado;
- resposta associada.

## IT-042 — Implementação da ligação interinstitucional

O sistema deverá registrar:

- instituição participante;
- representante;
- função no incidente;
- contatos;
- recursos ofertados;
- limitações;
- solicitações pendentes.

## IT-043 — Implementação de administração e finanças do incidente

O módulo deverá registrar:

- tipo de despesa;
- valor estimado;
- valor realizado;
- fonte de recurso;
- contratação emergencial;
- documento comprobatório;
- status administrativo.

## IT-044 — Implementação dos períodos operacionais do incidente

Cada incidente deverá suportar múltiplos períodos operacionais com:

- número;
- início;
- fim;
- situação inicial;
- objetivos;
- recursos principais;
- briefing;
- PAI;
- encerramento;
- pendências.

## IT-045 — Implementação do diário operacional

O diário do incidente deverá ser implementado com:

- registro cronológico;
- tipo;
- título;
- descrição;
- origem da informação;
- encaminhamento;
- status;
- evidência anexa;
- observações.

## IT-046 — Implementação da desmobilização e encerramento

O encerramento deverá ser implementado com:

- critério de desmobilização;
- recursos liberados;
- pendências;
- lições iniciais;
- data/hora de encerramento;
- situação final.

---

# 12. Implementações técnicas do painel operacional

## IT-047 — Implementação do dashboard operacional

O painel do usuário deverá ser implementado com:

- indicadores rápidos;
- destaque do incidente ativo;
- leitura situacional;
- registros recentes;
- gráficos resumidos;
- mapa operacional.

## IT-048 — Implementação dos widgets

Os widgets do dashboard deverão ser construídos sobre serviços específicos de consolidação, e não sobre queries espalhadas na view.

## IT-049 — Implementação da timeline operacional

O sistema deverá implementar componente de linha do tempo com base nos registros operacionais do incidente ou da estrutura correspondente.

---

# 13. Implementações técnicas do mapa operacional

## IT-050 — Implementação do módulo de mapa

O mapa deverá ser implementado como componente próprio, com camada de dados preparada por serviço específico.

### Deve suportar

- ocorrências georreferenciadas;
- áreas afetadas;
- instalações;
- abrigos;
- recursos em campo;
- pontos de apoio;
- camadas de risco.

## IT-051 — Implementação dos dados geográficos

A persistência geográfica inicial deverá suportar, no mínimo:

- coordenadas de ponto;
- endereço/localidade;
- referência territorial textual.

### Evolução futura

A arquitetura deve permitir adoção posterior de geometrias mais avançadas.

---

# 14. Implementações técnicas de relatórios e exportações

## IT-052 — Implementação do motor de relatórios

Os relatórios deverão ser implementados por datasets consolidados em services próprios.

### Isso vale para

- relatórios administrativos;
- relatórios de incidentes;
- relatórios do PLANCON;
- relatórios financeiros;
- relatórios operacionais.

## IT-053 — Implementação de filtros

Filtros deverão ser implementados com suporte a:

- período;
- status;
- conta;
- órgão;
- unidade;
- município;
- tipo;
- gravidade;
- responsável;
- período operacional.

## IT-054 — Implementação de exportação PDF

A exportação em PDF deverá ser implementada com:

- template específico;
- dataset consolidado;
- cabeçalho/rodapé padronizados;
- respeito ao escopo de acesso;
- auditoria quando o documento for sensível.

## IT-055 — Implementação de exportação em planilha/CSV

A exportação tabular deverá ser implementada com:

- colunas controladas;
- filtros replicados da consulta;
- escopo institucional aplicado;
- rastreabilidade do evento, quando necessário.

---

# 15. Implementações técnicas do armazenamento documental

## IT-056 — Implementação do serviço central de anexos

Todos os anexos do sistema deverão ser implementados por um serviço documental central com:

- validação de tipo;
- validação de tamanho;
- armazenamento organizado;
- metadados;
- vínculo com entidade de negócio;
- controle de acesso ao download.

## IT-057 — Implementação da tabela central de anexos

O sistema deverá implementar:

- tabela de metadados de arquivo;
- tabela de vínculo documental, quando necessário;
- referência por módulo de origem;
- referência de usuário responsável.

## IT-058 — Implementação de diretórios organizados

Os arquivos deverão ser armazenados em estrutura física organizada por domínio, como:

- contratos;
- faturas;
- plancon;
- incidentes;
- evidências;
- relatórios;
- institucionais.

---

# 16. Implementações técnicas do controle de acesso

## IT-059 — Implementação da política de acesso em múltiplas camadas

O acesso deverá ser implementado combinando:

- autenticação;
- perfil;
- escopo institucional;
- módulo contratado;
- estado da assinatura;
- auditoria.

## IT-060 — Implementação do escopo institucional nas consultas

Toda consulta sensível deverá ser implementada com filtro por:

- `conta_id`;
- `orgao_id`;
- `unidade_id`, quando aplicável;
- escopo do perfil.

## IT-061 — Implementação das policies

A camada de policy deverá ser implementada para ações como:

- editar plano;
- aprovar plano;
- abrir incidente;
- encerrar incidente;
- exportar relatório;
- administrar usuários;
- liberar módulos;
- consultar auditoria.

## IT-062 — Implementação de bloqueio contratual

Quando a política da plataforma exigir, o sistema deverá implementar bloqueio por:

- módulo não contratado;
- assinatura suspensa;
- conta inadimplente;
- limite operacional excedido.

---

# 17. Implementações técnicas da auditoria

## IT-063 — Implementação da trilha de auditoria funcional

A auditoria deverá ser implementada em tabela própria, distinta de log técnico.

### Deve registrar, quando aplicável

- usuário;
- conta;
- órgão;
- unidade;
- módulo;
- ação;
- data/hora;
- resultado;
- registro afetado;
- detalhes relevantes.

## IT-064 — Implementação de auditoria reforçada para ações sensíveis

Ações críticas deverão sempre gerar evento auditável, incluindo:

- login relevante;
- mudança de perfil;
- alteração contratual;
- bloqueio/liberação de módulo;
- exportação sensível;
- encerramento de incidente;
- aceite legal.

---

# 18. Implementações técnicas do banco de dados

## IT-065 — Implementação do schema relacional

O banco deverá ser implementado com:

- tabelas organizadas por domínio;
- PKs simples;
- FKs explícitas;
- índices em filtros dominantes;
- constraints de integridade mínimas;
- proteção histórica por restrição de exclusão.

## IT-066 — Implementação de constraints

As tabelas deverão implementar, conforme o caso:

- `PRIMARY KEY`
- `FOREIGN KEY`
- `UNIQUE`
- `NOT NULL`
- `CHECK`
- `DEFAULT`

## IT-067 — Implementação de índices

Os índices deverão ser implementados desde a primeira modelagem física nas colunas de maior uso, especialmente:

- status;
- conta;
- órgão;
- unidade;
- data;
- período operacional;
- tipo;
- assinatura;
- módulo.

---

# 19. Implementações técnicas de observabilidade e suporte

## IT-068 — Implementação de logs técnicos

O sistema deverá implementar logs técnicos para:

- falhas;
- exceções;
- problemas de autenticação;
- problemas de integração futura;
- erros operacionais de backend.

## IT-069 — Implementação de tratamento de erros

O sistema deverá implementar tratamento de erro com:

- captura centralizada;
- resposta amigável ao usuário quando possível;
- registro técnico para suporte;
- não exposição desnecessária de detalhes sensíveis no frontend.

---

# 20. Implementações técnicas de evolução futura

## IT-070 — Implementação preparada para API

Mesmo que a API completa não exista na primeira fase, a arquitetura deverá permitir futura exposição controlada de endpoints.

## IT-071 — Implementação preparada para integrações

A camada de integração deverá ser isolada da regra de negócio central para permitir futuras conexões com serviços externos.

## IT-072 — Implementação preparada para analytics avançado

Os módulos de relatórios e dashboard deverão ser construídos de modo que analytics mais sofisticado possa ser incorporado sem reescrever o sistema inteiro.

---

# 21. Implementações técnicas por fase recomendada

## Fase 1 — Núcleo operacional mínimo viável

- bootstrap, rotas, autenticação;
- contas, órgãos, unidades, usuários;
- planos e assinaturas básicas;
- dashboard inicial;
- incidente núcleo;
- relatórios básicos;
- anexos básicos;
- controle de acesso;
- auditoria mínima.

## Fase 2 — Expansão institucional

- PLANCON modular;
- núcleo CSI/SCO do plano;
- períodos operacionais mais refinados;
- módulos de recursos, comunicações e mapa mais robustos;
- relatórios ampliados.

## Fase 3 — Expansão avançada

- finanças mais refinadas;
- integrações;
- API;
- assinatura digital;
- analytics avançado;
- automações;
- governança documental mais madura.

---

# 22. Riscos de implementação que devem ser evitados

Os principais riscos a evitar são:

1. tentar implementar todos os módulos em profundidade máxima desde o início;
2. concentrar regra de negócio em controllers;
3. concentrar SQL em views;
4. tratar PLANCON e incidente como formulários únicos;
5. não aplicar escopo institucional no backend;
6. não separar área administrativa da operacional;
7. deixar anexos sem governança;
8. construir relatórios a partir de consultas improvisadas em cada tela;
9. não auditar ações críticas;
10. ignorar a diferença entre contrato SaaS e operação institucional.

---

# 23. Conclusão técnica

As implementações técnicas do SIGERD precisam respeitar o que a documentação já revelou: o sistema não é apenas um painel web, nem apenas um repositório de formulários. Ele é uma plataforma institucional com três áreas de produto, dois grandes núcleos operacionais, modelo SaaS, trilha documental e forte necessidade de governança.

A principal diretriz deste documento pode ser resumida assim:

**implementar de forma modular, segura, progressiva e relacionalmente coerente.**

O projeto já tem maturidade conceitual. A implementação técnica correta agora depende de disciplina de execução: services fortes, controllers enxutos, banco consistente, controle de acesso real, formulários modulares, relatórios sobre datasets padronizados e documentação viva.