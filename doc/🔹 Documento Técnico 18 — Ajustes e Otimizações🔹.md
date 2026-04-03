**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Ajustes e Otimizações  
**Objetivo:** identificar, consolidar e formalizar melhorias técnicas, funcionais, estruturais e operacionais recomendadas para o projeto, visando maior consistência, escalabilidade, segurança, clareza de uso e viabilidade de manutenção.

## 1. Finalidade do documento

Este documento reúne os principais ajustes e otimizações recomendados para o SIGERD antes e durante sua implementação. Ele não substitui os documentos de arquitetura, banco, serviços, controladores ou requisitos. Sua função é revisar criticamente o projeto e apontar onde há risco de excesso, ambiguidade, acoplamento, sobrecarga operacional ou fragilidade estrutural.

Em termos práticos, este documento responde a quatro perguntas:

- o que precisa ser simplificado;
- o que precisa ser reforçado;
- o que precisa ser separado melhor;
- o que precisa ser priorizado para evitar retrabalho futuro.

## 2. Objetivos dos ajustes e otimizações

Os ajustes e otimizações propostos têm os seguintes objetivos:

- melhorar a clareza estrutural do projeto;
- reduzir complexidade desnecessária;
- preservar a ambição funcional sem comprometer a implementabilidade;
- evitar acoplamento entre módulos;
- fortalecer segurança e governança;
- melhorar usabilidade para o contexto institucional;
- preparar o sistema para evolução incremental;
- evitar que o projeto nasça superdocumentado, mas operacionalmente difícil.

## 3. Premissa crítica de revisão

O SIGERD tem escopo robusto e coerente com um SaaS institucional. O ponto forte do projeto é justamente sua abrangência: ele cobre área pública, administração SaaS, gestão institucional, planos de contingência, gerenciamento de desastres, relatórios, auditoria e controle contratual.

O risco, porém, está em outro ponto: tentar implementar tudo com o mesmo nível de profundidade na primeira fase. O ajuste estratégico central é este:

**o sistema deve manter visão completa, mas execução por camadas de prioridade.**

Sem isso, o projeto tende a sofrer com:

- excesso de frentes simultâneas;
- backend inchado prematuramente;
- banco de dados superfragmentado antes da maturidade real;
- retrabalho de interface;
- lentidão de entrega.

## 4. Ajustes estratégicos de escopo

## AJ-001 — Separar escopo total de escopo de implantação inicial

O projeto atual descreve muito bem o produto final, mas precisa distinguir com mais firmeza:

- escopo estrutural definitivo;
- escopo da primeira versão operacional.

### Recomendação

Formalizar três níveis:

- **núcleo MVP institucional**
- **núcleo operacional expandido**
- **camada avançada enterprise/governo**

### Benefício

Isso reduz ansiedade de implementação e evita que recursos avançados travem a entrega da base.

## AJ-002 — Priorizar módulos de maior tração operacional

Nem todos os módulos têm o mesmo peso de adoção inicial.

### Ordem recomendada de prioridade

1. autenticação e controle de acesso
2. cadastro institucional
3. assinaturas e planos básicos
4. painel operacional inicial
5. incidentes
6. relatórios operacionais básicos
7. PLANCON básico
8. módulos avançados de SCI/SCO e CSI/SCO
9. financeiro mais refinado
10. auditoria avançada, integrações, API e assinatura digital

### Benefício

Entrega valor real mais cedo, sem sacrificar o desenho do produto.

## 5. Ajustes de arquitetura do projeto

## AJ-003 — Reforçar separação entre as três áreas do produto

O projeto já prevê:

- área pública;
- área administrativa SaaS;
- área operacional.

### Otimização recomendada

Essa separação deve ser mantida não apenas em layout, mas também em:

- rotas;
- controladores;
- permissões;
- menus;
- serviços;
- assets visuais;
- templates.

### Risco evitado

Misturar visual e tecnicamente as áreas, produzindo um sistema confuso.

## AJ-004 — Evitar controllers “supermódulo”

Nos módulos densos, especialmente PLANCON e Incidentes, há risco de centralizar tudo em poucos controllers.

### Recomendação

Manter a segmentação por subdomínio:

- `PlanconController` para ciclo do plano;
- `PlanconBlockController` para blocos;
- `PlanconStructureController` para CSI/SCO do plano;
- `IncidentController` para ciclo-base;
- controladores específicos para briefing, comando, operações, planejamento, registros e desmobilização.

### Benefício

Reduz acoplamento e melhora manutenção.

## AJ-005 — Manter services como núcleo real da regra de negócio

O projeto já está corretamente orientado a services. A otimização aqui é de disciplina arquitetural.

### Recomendação

Definir como regra de equipe:

- controller não implementa regra de domínio;
- repository não decide regra funcional;
- view não condiciona segurança;
- service concentra transição, validação e coerência de negócio.

## 6. Ajustes de modelagem de banco de dados

## AJ-006 — Não tentar hiper-normalizar tudo na primeira fase

A arquitetura relacional proposta está correta, mas alguns blocos podem nascer com granularidade mais pragmática antes de desdobramentos mais finos.

### Recomendação

Manter a separação dos grandes domínios, mas permitir que algumas tabelas iniciem com estrutura mais enxuta, desde que sem comprometer o modelo final.

### Exemplos possíveis

- alguns catálogos podem começar como enums controlados na aplicação;
- certos complementos documentais podem iniciar centralizados em `anexos` + `documentos_vinculados`;
- tabelas muito especializadas de analytics podem ficar para fase posterior.

### Risco evitado

Criar microtabelas prematuras sem uso real.

## AJ-007 — Reforçar o eixo institucional em tabelas críticas

Uma das decisões mais corretas do projeto foi não centralizar tudo em usuário.

### Otimização recomendada

Garantir presença consistente de:

- `conta_id`
- `orgao_id`
- `unidade_id`

nas tabelas críticas de operação, relatório, exportação e auditoria.

### Benefício

Melhora:

- escopo de segurança;
- filtros;
- relatórios;
- rastreabilidade.

## AJ-008 — Tratar PLANCON e Incidente como domínios irmãos, não como variantes da mesma entidade

Essa separação já está conceitualmente correta e deve ser mantida com firmeza.

### Recomendação

Permitir vínculos entre:

- `plancon`
- `plancon_cenario`
- `incidente`

mas sem reutilizar a mesma tabela estrutural para os dois mundos.

### Benefício

Evita contaminação entre prevenção e resposta.

## 7. Ajustes de modelagem funcional

## AJ-009 — Modularizar formulários densos

PLANCON e Incidentes não devem ser telas únicas com excesso de campos.

### Recomendação

Usar:

- abas;
- etapas;
- seções;
- blocos colapsáveis;
- gravação parcial.

### Benefício

Melhora usabilidade, reduz erro de preenchimento e facilita manutenção.

## AJ-010 — Definir claramente campos estruturados versus campos narrativos

Muitos blocos do sistema têm tanto dados estruturados quanto texto operacional longo.

### Recomendação

Sempre distinguir:

- dado estruturado para filtro, relatório e vínculo;
- dado narrativo para contextualização.

### Exemplo

No incidente:

- `status_incidente`, `tipo_ocorrencia`, `municipio`, `periodo_operacional_id` são estruturados;
- descrição detalhada, observações e justificativas são narrativas.

### Benefício

Evita transformar o sistema em mero repositório textual sem capacidade analítica.

## AJ-011 — Reforçar o conceito de “registro vivo” na operação

O documento-base já aponta corretamente que o núcleo operacional precisa funcionar como sistema vivo de gestão.

### Otimização recomendada

Dar prioridade de implementação a:

- registros operacionais;
- períodos operacionais;
- objetivos;
- status;
- evidências;
- timeline.

### Motivo

Esses elementos geram valor operacional real mais rápido do que certos refinamentos documentais avançados.

## 8. Ajustes de usabilidade

## AJ-012 — Diferenciar visualmente os três contextos de uso

Cada área do produto tem objetivo distinto:

- pública vende confiança;
- administrativa vende controle;
- operacional vende clareza e comando.

### Recomendação

Aplicar identidade visual consistente, mas com diferenças claras de comportamento:

- área pública mais institucional/comercial;
- área administrativa mais analítica;
- área operacional mais enxuta, direta e situacional.

## AJ-013 — Reduzir densidade visual nas telas operacionais

O risco na área operacional é excesso de informação simultânea.

### Recomendação

Adotar hierarquia:

- topo com indicadores-chave;
- centro com leitura situacional;
- bloco dedicado a mapa;
- bloco dedicado a timeline/últimos registros;
- detalhes aprofundados em abas ou painéis laterais.

### Benefício

Melhora tomada de decisão.

## AJ-014 — Tornar filtros previsíveis e persistentes

Relatórios e listagens serão fortemente usados no SIGERD.

### Recomendação

Filtros devem:

- manter estado durante navegação;
- permitir limpeza rápida;
- mostrar claramente o recorte atual;
- ser consistentes entre módulos.

### Benefício

Reduz fricção operacional.

## 9. Ajustes de segurança

## AJ-015 — Tratar acesso por camadas, não só por perfil

O documento de controle de acesso já está correto ao combinar:

- autenticação;
- perfil;
- escopo institucional;
- plano/módulo;
- auditoria.

### Otimização recomendada

Instituir isso como regra rígida de implementação.

### Fórmula operacional recomendada

**acesso permitido = autenticação válida + perfil compatível + escopo suficiente + módulo liberado + situação contratual apta**

## AJ-016 — Reforçar proteção de exportações

Relatórios e exportações terão alto valor institucional.

### Recomendação

Toda exportação relevante deve:

- validar escopo;
- validar permissão específica;
- ser auditada;
- idealmente registrar tipo, usuário, data/hora e recorte.

## AJ-017 — Restringir acesso direto a anexos

Os anexos do sistema podem incluir:

- contratos;
- evidências;
- relatórios;
- mapas;
- documentos operacionais.

### Recomendação

Arquivos sensíveis não devem ser baixados diretamente por URL pública. O acesso deve ocorrer por mediação do backend, com verificação de permissão.

## 10. Ajustes de auditoria e governança

## AJ-018 — Diferenciar claramente log técnico de auditoria funcional

Muitos sistemas confundem erro técnico com rastreabilidade institucional.

### Recomendação

Manter dois eixos:

- **log técnico** para suporte e depuração;
- **auditoria funcional** para responsabilização e governança.

## AJ-019 — Auditar ações de alto impacto

Nem tudo precisa entrar em auditoria reforçada, mas certas ações sim.

### Prioridade de auditoria

- login/logout/falha relevante;
- alteração contratual;
- bloqueio/liberação de módulo;
- mudança de perfil/permissão;
- criação/encerramento de incidente;
- transferência de comando;
- exportações sensíveis;
- aceite legal;
- alterações estruturais no PLANCON.

## AJ-020 — Evitar exclusão física em entidades sensíveis

Em módulos com histórico importante, a exclusão física é contraproducente.

### Recomendação

Privilegiar:

- inativação;
- cancelamento;
- encerramento;
- marcação lógica de exclusão, quando necessário.

### Benefício

Preserva trilha histórica e reduz risco de perda institucional.

## 11. Ajustes do domínio comercial / SaaS

## AJ-021 — Formalizar claramente a política de bloqueio por inadimplência

O projeto menciona status como inadimplente, suspensa, cancelada e encerrada.

### Recomendação

Definir desde já:

- o que bloqueia totalmente;
- o que bloqueia parcialmente;
- quais áreas permanecem acessíveis;
- se haverá modo leitura em algumas situações.

### Benefício

Evita comportamento contraditório entre financeiro, suporte e operação.

## AJ-022 — Manter catálogo de planos simples no início

Os planos já estão bem definidos:

- Start
- Essencial
- Profissional
- Institucional
- Governo / Enterprise.

### Otimização recomendada

Na primeira fase, evitar excesso de variações comerciais ou combinações excepcionais fora do catálogo.

### Benefício

Reduz complexidade contratual e técnica.

## 12. Ajustes do módulo PLANCON

## AJ-023 — Implementar PLANCON em camadas de profundidade

O desenho do PLANCON é forte, mas muito amplo.

### Recomendação de implantação por níveis

**Nível 1**

- identificação geral
- território
- riscos
- cenários
- ativação
- recursos
- anexos básicos

**Nível 2**

- governança
- monitoramento/comunicação
- procedimentos
- rotas/abrigos
- assistência

**Nível 3**

- simulados
- revisão avançada
- núcleo CSI/SCO completo

### Benefício

Entrega valor sem travar implementação.

## AJ-024 — Tornar revisão e versão do plano processos explícitos

Muitos sistemas tratam revisão apenas como edição.

### Recomendação

Separar:

- edição de conteúdo;
- revisão formal;
- mudança de versão;
- aprovação institucional.

### Benefício

Melhora governança documental.

## 13. Ajustes do módulo Incidentes / SCI-SCO

## AJ-025 — Priorizar o ciclo operacional mínimo viável

O módulo de incidente é o mais crítico do sistema.

### Recomendação de núcleo inicial

- abertura do incidente;
- briefing inicial;
- comando;
- períodos operacionais;
- objetivos;
- registros operacionais;
- recursos;
- encerramento básico.

### Fase seguinte

- staff detalhado;
- estratégias/PAI refinadas;
- finanças;
- ligação interinstitucional avançada;
- segurança operacional aprofundada;
- desmobilização completa.

## AJ-026 — Não transformar SCI/SCO em mera coleção de formulários

O valor real do módulo está no encadeamento operacional.

### Recomendação

Priorizar vínculos entre:

- incidente;
- comando;
- período;
- objetivo;
- registro;
- recurso;
- instalação.

### Benefício

Garante operação real, e não só documentação.

## 14. Ajustes de relatórios e analytics

## AJ-027 — Implementar relatórios em duas camadas

### Camada 1

Relatórios operacionais e administrativos básicos, tabulares e exportáveis.

### Camada 2

Dashboards, gráficos, indicadores consolidados e analytics.

### Benefício

Evita travar a entrega por tentar construir BI sofisticado cedo demais.

## AJ-028 — Padronizar datasets de exportação

Cada exportação deve nascer de uma fonte de consulta bem definida.

### Recomendação

Não duplicar lógica de consulta dentro de múltiplos arquivos de exportação.

### Benefício

Reduz inconsistência entre tela, PDF e planilha.

## 15. Ajustes de performance

## AJ-029 — Indexar desde cedo os filtros dominantes

Os módulos do SIGERD terão filtros recorrentes por:

- conta;
- órgão;
- unidade;
- status;
- data;
- período operacional;
- município;
- tipo.

### Recomendação

Garantir os índices compostos mais críticos já na primeira modelagem física.

## AJ-030 — Paginador obrigatório em módulos volumosos

Listagens como:

- incidentes;
- registros operacionais;
- usuários;
- auditoria;
- faturas;
- relatórios

devem nascer com paginação, e não receber isso depois.

## 16. Ajustes de documentação e implantação

## AJ-031 — Manter rastreabilidade entre documento técnico e implementação

Agora que a documentação ficou extensa, surge um risco novo: divergência entre o que foi documentado e o que foi implementado.

### Recomendação

Manter uma matriz simples de rastreabilidade:

- módulo;
- documento de origem;
- artefato técnico correspondente;
- status de implementação.

## AJ-032 — Criar um mapa de priorização executiva

Além dos documentos técnicos completos, o projeto precisa de uma visão resumida de execução.

### Recomendação

Criar posteriormente um documento-síntese com:

- fases;
- dependências;
- módulos;
- prioridade;
- risco;
- esforço relativo.

### Benefício

Facilita implementação real.

## 17. Ajustes de simplificação recomendados

## AJ-033 — Simplificar onde a sofisticação ainda não gera valor imediato

Alguns pontos podem começar mais simples sem ferir a arquitetura:

- parte dos catálogos como enums;
- alguns relatórios sem persistência histórica de geração;
- alguns workflows de aprovação ainda sem múltiplas etapas;
- API e integrações como fase posterior.

## AJ-034 — Não simplificar o que é estrutural

Por outro lado, há elementos que **não** devem ser simplificados excessivamente:

- separação conta/órgão;
- separação PLANCON/incidente;
- controle de acesso por escopo;
- auditoria das ações críticas;
- modularização dos núcleos operacionais;
- vínculo institucional nas entidades sensíveis.

## 18. Matriz resumida de ajustes prioritários

|Código|Ajuste|Prioridade|
|---|---|---|
|AJ-001|Separar escopo final de escopo inicial|Alta|
|AJ-003|Reforçar separação das três áreas|Alta|
|AJ-005|Services como núcleo da regra de negócio|Alta|
|AJ-007|Reforçar eixo institucional no banco|Alta|
|AJ-009|Modularizar formulários densos|Alta|
|AJ-015|Controle de acesso em múltiplas camadas|Alta|
|AJ-021|Formalizar bloqueio por inadimplência|Alta|
|AJ-023|Implantar PLANCON por profundidade|Alta|
|AJ-025|Priorizar ciclo mínimo viável de incidentes|Alta|
|AJ-029|Indexar filtros dominantes desde cedo|Alta|
|AJ-031|Rastreabilidade entre documentação e implementação|Alta|
|AJ-033|Simplificar o que não gera valor imediato|Média/Alta|

## 19. Conclusão técnica

O SIGERD está conceitualmente forte. O projeto já possui amplitude, coerência institucional e base arquitetural suficiente para sustentar uma plataforma séria. O principal ponto agora não é inventar mais estrutura, e sim aplicar refinamento estratégico.

Os ajustes mais importantes podem ser resumidos assim:

- manter a visão completa do produto, mas implementar por fases;
- proteger a separação entre os grandes domínios;
- preservar segurança e escopo institucional como eixo central;
- reduzir complexidade prematura em pontos de menor valor imediato;
- priorizar o que gera uso real: autenticação, cadastro institucional, incidentes, relatórios básicos, controle contratual e PLANCON em núcleo progressivo.

Sem esses ajustes, o risco não é o projeto estar mal desenhado. O risco é ele estar bem desenhado demais para a primeira execução, e isso atrasar a entrega. Com esses ajustes, o SIGERD ganha algo mais importante do que complexidade: **viabilidade operacional de implementação**.