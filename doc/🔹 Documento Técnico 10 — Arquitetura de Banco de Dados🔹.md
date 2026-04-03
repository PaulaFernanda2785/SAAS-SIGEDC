**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Arquitetura de Banco de Dados  
**Objetivo:** definir a arquitetura lógica do banco de dados do sistema, estabelecendo seus domínios, agrupamentos estruturais, entidades centrais, princípios relacionais e diretrizes técnicas de persistência.

## 1. Finalidade do documento

Este documento estabelece a arquitetura de banco de dados do SIGERD em nível estrutural. Seu papel é organizar como os dados do sistema deverão ser agrupados, persistidos e relacionados, garantindo coerência entre:

- o modelo SaaS do produto;
- a estrutura institucional dos clientes;
- a gestão do risco por planos de contingência;
- o gerenciamento de acidentes e desastres com lógica SCI/SCO;
- os módulos de relatórios, auditoria, conformidade e arquivos.

A intenção aqui não é ainda esgotar todas as colunas ou constraints detalhadas. O foco é a **arquitetura do banco**: seus blocos, domínios, estratégia relacional e princípios de organização.

## 2. Objetivos da arquitetura de banco de dados

A arquitetura de banco de dados do SIGERD deverá atender aos seguintes objetivos:

- refletir a estrutura real do produto;
- separar domínios comerciais, institucionais e operacionais;
- evitar modelagem centrada apenas no usuário;
- preservar rastreabilidade operacional;
- suportar multiunidade e multiórgão;
- permitir escalabilidade progressiva;
- sustentar relatórios e painéis;
- manter integridade entre plano, incidente, comando, períodos e registros;
- viabilizar controle por assinatura e módulo contratado;
- favorecer manutenção e evolução futura.

## 3. Princípios arquiteturais do banco

A arquitetura relacional do SIGERD deverá seguir os seguintes princípios:

1. centralidade da **conta contratante** como unidade comercial;
2. centralidade do **órgão/instituição** como unidade operacional;
3. separação entre **dados contratuais** e **dados operacionais**;
4. separação entre **gestão do risco** e **gerenciamento do desastre**;
5. modelagem modular por domínio;
6. rastreabilidade de eventos e auditoria;
7. integridade entre estruturas de comando, equipes, instalações, períodos e registros;
8. possibilidade de crescimento progressivo sem reestruturação destrutiva;
9. uso de chaves e vínculos explícitos;
10. organização orientada a relatórios e filtros institucionais.

## 4. Estratégia geral de modelagem

A arquitetura recomendada para o SIGERD é a de um **banco relacional modular**, com agrupamento por domínios de negócio e separação entre blocos funcionais.

O modelo não deve ser monolítico nem excessivamente genérico. Também não deve cair no erro oposto de criar dezenas de tabelas sem critério. O ponto correto é um arranjo relacional com coesão por domínio e relações explícitas entre os módulos centrais.

### Estratégia recomendada:

- base relacional principal;
- organização por macrodomínios;
- tabelas centrais de referência institucional;
- tabelas operacionais específicas para PLANCON e incidentes;
- tabelas transversais para auditoria, anexos, permissões e conformidade;
- suporte a expansão futura por integrações e analytics.

## 5. Macrodomínios do banco de dados

A arquitetura do banco do SIGERD deverá ser organizada em **sete macrodomínios** principais:

1. domínio comercial/SaaS;
2. domínio institucional;
3. domínio de identidade e acesso;
4. domínio de gestão do risco (PLANCON);
5. domínio de gerenciamento do desastre (Incidentes / SCI-SCO);
6. domínio de relatórios, documentos e arquivos;
7. domínio de auditoria e conformidade.

## 6. Domínio comercial / SaaS

Este domínio sustenta a monetização e a governança contratual da plataforma.

### Finalidade:

- registrar contas contratantes;
- registrar planos comerciais;
- registrar assinaturas ativas;
- registrar faturas e cobrança;
- registrar módulos liberados;
- controlar situação contratual;
- manter trilha mínima da relação comercial.

### Entidades centrais esperadas:

- contas;
- planos_assinatura;
- assinaturas;
- faturas;
- assinatura_modulos;
- termos_aceites;
- leads_comerciais, se adotado.

### Diretriz arquitetural:

este domínio não deve se confundir com a operação do cliente. Ele controla o **negócio SaaS**, não o incidente nem o plano de contingência em si.

## 7. Domínio institucional

Este domínio representa a estrutura organizacional do cliente dentro do sistema.

### Finalidade:

- registrar órgãos operadores;
- registrar unidades e subunidades;
- registrar vínculos institucionais;
- suportar operação local, regional, estadual ou multi-institucional.

### Entidades centrais esperadas:

- orgaos;
- unidades;
- estruturas_hierarquicas, se a hierarquia for destacada;
- contatos_institucionais, se houver normalização complementar.

### Diretriz arquitetural:

a modelagem deve preservar a diferença entre:

- quem **contrata** o sistema;
- quem **opera** o sistema.

Essa separação foi explicitamente prevista no escopo-base e é crítica para um SaaS institucional.

## 8. Domínio de identidade e acesso

Este domínio sustenta usuários, perfis, permissões e escopos.

### Finalidade:

- registrar usuários;
- registrar perfis;
- vincular usuários a perfis;
- controlar escopo por conta, órgão, unidade e módulo;
- registrar sessões, autenticação e preferências de segurança.

### Entidades centrais esperadas:

- usuarios;
- perfis;
- usuarios_perfis;
- permissoes, se o modelo for normalizado;
- perfis_permissoes, se houver granularidade por permissão;
- sessoes_usuario, se persistidas;
- autenticacao_2fator, se persistida separadamente.

### Diretriz arquitetural:

o banco não deve depender apenas de um campo de função textual dentro do usuário para resolver toda a autorização. O modelo precisa suportar perfis estruturados, escopo e expansão futura.

## 9. Domínio de gestão do risco — PLANCON

Este domínio sustenta a primeira frente funcional do SIGERD: elaboração e manutenção de planos de contingência. O material-base organiza esse módulo em vários blocos e deixa claro que ele deve ser estruturado de forma relacional, não como texto único.

### Finalidade:

- registrar planos;
- versionar e revisar planos;
- estruturar território, risco, cenários, ativação, governança, recursos, comunicação, procedimentos, abrigos, assistência, simulados, revisão e anexos;
- manter o núcleo CSI/SCO vinculado ao plano.

### Entidades centrais esperadas:

- plancons;
- plancon_versoes;
- plancon_territorios;
- plancon_riscos;
- plancon_cenarios;
- plancon_niveis_ativacao;
- plancon_governanca;
- plancon_recursos;
- plancon_monitoramento_comunicacao;
- plancon_procedimentos;
- plancon_rotas_abrigos;
- plancon_assistencia;
- plancon_simulados;
- plancon_revisoes;
- plancon_anexos.

### Diretriz crítica:

o PLANCON não deve ser reduzido a uma única tabela “plano_contingencia” com dezenas ou centenas de colunas. Isso dificultaria manutenção, revisão, relatórios e reaproveitamento modular.

## 10. Núcleo operacional do PLANCON — CSI/SCO vinculado ao plano

O documento-base determina que os blocos operacionais vinculados ao plano formem um núcleo próprio, com a seguinte lógica: **estrutura → pessoas → instalações → períodos → registros**. Isso exige modelagem relacional dedicada.

### Finalidade:

- registrar a estrutura ativada;
- registrar funções e equipes;
- registrar instalações operacionais;
- registrar períodos operacionais;
- registrar diário do comando e registros operacionais.

### Entidades centrais esperadas:

- plancon_estruturas_operacionais;
- plancon_funcoes_equipes;
- plancon_instalacoes;
- plancon_periodos_operacionais;
- plancon_registros_operacionais.

### Diretriz arquitetural:

essas tabelas não devem ser colapsadas em campos textuais anexados ao plano. Elas compõem um subdomínio relacional do PLANCON.

## 11. Domínio de gerenciamento do desastre — Incidentes / SCI-SCO

Este domínio sustenta a segunda frente funcional do sistema: o gerenciamento do desastre em si. O material-base distribui esse módulo em blocos equivalentes a abertura, comando, direção da resposta, execução, suporte e encerramento.

### Finalidade:

- abrir incidente;
- manter briefing inicial;
- registrar comando e staff;
- registrar objetivos, estratégias, operações e planejamento;
- registrar recursos, instalações, comunicações, segurança, informação pública e finanças;
- registrar períodos operacionais;
- registrar diário do incidente;
- processar desmobilização e encerramento.

### Entidades centrais esperadas:

- incidentes;
- incidentes_briefing;
- incidentes_comando;
- incidentes_staff_comando;
- incidentes_staff_geral;
- incidentes_objetivos;
- incidentes_estrategias_pai;
- incidentes_operacoes_campo;
- incidentes_planejamento_situacao;
- incidentes_recursos;
- incidentes_instalacoes;
- incidentes_comunicacoes;
- incidentes_seguranca;
- incidentes_informacao_publica;
- incidentes_ligacao_interinstitucional;
- incidentes_financas;
- incidentes_periodos_operacionais;
- incidentes_registros_operacionais;
- incidentes_desmobilizacao.

### Diretriz crítica:

o módulo de incidente também não deve ser tratado como uma tabela única. O banco precisa refletir a complexidade do ciclo operacional real.

## 12. Relação entre PLANCON e Incidentes

A arquitetura do banco deve permitir relação entre plano de contingência e incidente, mas sem fundi-los.

### Regra arquitetural:

- o PLANCON é um objeto de gestão do risco e preparação;
- o incidente é um objeto de gestão da resposta operacional.

### Relações possíveis:

- incidente pode referenciar plancon;
- incidente pode referenciar cenário do plancon;
- incidente pode herdar ou consultar estruturas, recursos, procedimentos ou instalações previamente cadastradas;
- mas o incidente deve manter sua própria trilha operacional independente.

Essa separação é decisiva para não misturar prevenção com resposta de forma estruturalmente incorreta.

## 13. Domínio de relatórios, mapas, documentos e arquivos

Esse domínio suporta documentos operacionais, exportações, evidências e artefatos vinculados.

### Finalidade:

- registrar anexos;
- vincular documentos a registros;
- apoiar geração de relatórios;
- armazenar evidências operacionais;
- controlar exportações e arquivos documentais.

### Entidades centrais esperadas:

- anexos;
- documentos_vinculados;
- exportacoes, se houver persistência;
- relatorios_gerados, se houver persistência;
- mapas_operacionais, se parte dos metadados do mapa for persistida.

### Diretriz arquitetural:

não é recomendável replicar colunas de arquivo em todas as tabelas sem critério. Uma arquitetura com entidade de anexo e entidade de vínculo documental tende a ser mais flexível para o porte do sistema.

## 14. Domínio de auditoria e conformidade

Este domínio sustenta governança, rastreabilidade e responsabilidade institucional.

### Finalidade:

- registrar logs de acesso;
- registrar ações administrativas;
- registrar ações operacionais relevantes;
- registrar aceite de termos e política;
- registrar origem e contexto de eventos sensíveis.

### Entidades centrais esperadas:

- logs_auditoria;
- logs_acesso;
- termos_aceites;
- eventos_sensiveis, se houver tabela dedicada;
- historicos_status, quando necessário em módulos sensíveis.

### Diretriz arquitetural:

auditoria não deve ser tratada apenas como log técnico de infraestrutura. No SIGERD, ela é um componente funcional do negócio.

## 15. Arquitetura relacional em camadas de dados

Em termos de organização lógica, o banco do SIGERD pode ser entendido em quatro faixas:

### 15.1. Faixa de referência e identidade

Inclui:

- contas;
- órgãos;
- unidades;
- usuários;
- perfis.

### 15.2. Faixa contratual e administrativa

Inclui:

- planos;
- assinaturas;
- faturas;
- módulos liberados;
- conformidade.

### 15.3. Faixa operacional preventiva

Inclui:

- plancons e blocos associados;
- estrutura CSI/SCO do plano.

### 15.4. Faixa operacional responsiva

Inclui:

- incidentes e blocos associados;
- estrutura SCI/SCO do incidente.

### 15.5. Faixa transversal

Inclui:

- anexos;
- relatórios;
- logs;
- auditoria;
- exportações;
- integrações futuras.

Essa leitura ajuda a manter clareza de dependência entre dados-base, dados contratuais e dados operacionais.

## 16. Estratégia de chaves e referência em alto nível

Embora o detalhamento formal pertença a outro documento, a arquitetura do banco já exige diretrizes de referência.

### Diretrizes:

- cada domínio central deve possuir chave primária própria;
- tabelas dependentes devem referenciar explicitamente a entidade-pai;
- relações institucionais devem preservar conta_id, orgao_id e unidade_id quando fizer sentido operacional;
- tabelas operacionais devem registrar vínculo institucional mínimo para facilitar escopo, filtro e auditoria;
- chaves externas devem ser claras e sem ambiguidades semânticas.

### Exemplo de lógica:

- assinatura referencia conta;
- órgão referencia conta;
- unidade referencia órgão;
- usuário referencia conta, órgão e, quando aplicável, unidade;
- plancon referencia conta, órgão e unidade ou contexto institucional equivalente;
- incidente referencia conta, órgão e unidade ou contexto equivalente;
- registros operacionais referenciam incidente ou estrutura correspondente.

## 17. Estratégia de normalização

A arquitetura recomendada é **normalização relacional controlada**, evitando dois extremos:

### Extremo incorreto 1:

desnormalização exagerada com tabelas gigantes, repetição de dados e baixa governança.

### Extremo incorreto 2:

normalização excessiva com microtabelas artificiais que só aumentam complexidade sem ganho funcional.

### Diretriz prática:

- normalizar entidades principais;
- separar submódulos operacionais relevantes;
- usar tabelas de relacionamento quando necessário;
- manter catálogos e enums onde fizer sentido;
- preservar desempenho e clareza.

## 18. Estratégia para status, ciclos e histórico

O SIGERD possui muitos objetos com ciclo de vida: conta, assinatura, usuário, plano, incidente, período operacional, registros etc.

### Diretriz arquitetural:

o banco deve permitir:

- status atual da entidade;
- data de criação;
- data de atualização;
- datas relevantes de início/fim;
- histórico de alteração quando o domínio exigir rastreabilidade reforçada.

### Casos mais sensíveis:

- assinaturas;
- incidentes;
- períodos operacionais;
- registros operacionais;
- revisões de PLANCON;
- aceite legal.

## 19. Estratégia para multiunidade e multiórgão

O sistema foi projetado para suportar desde estruturas pequenas até contextos multiórgão e multiunidade, inclusive planos mais avançados como institucional e governo/enterprise.

### Diretrizes:

- não centralizar tudo em um único órgão fixo;
- permitir múltiplas unidades por órgão;
- permitir múltiplos órgãos por conta contratante quando o plano suportar;
- registrar claramente a qual contexto institucional cada dado pertence;
- preservar possibilidade de escopo local, regional, estadual ou multi-institucional.

## 20. Estratégia para arquivos e anexos

Como o sistema lida com contratos, comprovantes, anexos do plano, evidências do incidente e documentos diversos, o banco deve ter arquitetura documental mínima.

### Recomendação:

- tabela central de anexos;
- tabela ou estratégia de vinculação documental por módulo;
- metadados mínimos de tipo, nome lógico, localização, responsável, data e entidade vinculada.

### Benefício:

isso evita espalhar colunas de arquivo por todo o banco de forma inconsistente.

## 21. Estratégia para relatórios e consultas

A arquitetura do banco deve ser desenhada já pensando em consultas operacionais e administrativas.

### Isso implica:

- presença consistente de datas;
- presença consistente de status;
- presença consistente de vínculo institucional;
- presença consistente de vínculo com incidente ou plano;
- campos que viabilizem filtros por período, município, órgão, unidade, tipo, gravidade, versão, vigência e situação.

### Ponto crítico:

relatórios no SIGERD não são periféricos; eles são parte da utilidade central do produto. Portanto, a arquitetura do banco precisa nascer orientada a consulta.

## 22. Estratégia para localização geográfica e mapas

O SIGERD possui mapa operacional, coordenadas, áreas afetadas, rotas, abrigos e instalações.

### Diretrizes arquiteturais:

- prever campos para coordenadas geográficas;
- prever campos para endereço/localidade;
- permitir referência a município e estado;
- admitir metadados geográficos em incidentes, instalações, abrigos, pontos de apoio e elementos territoriais do plano;
- considerar suporte futuro a geometrias mais avançadas, sem obrigar esse desenho em toda a base desde o início.

## 23. Estratégia para escalabilidade futura

A arquitetura do banco deve nascer preparada para expansão gradual.

### Fase inicial esperada:

- contas, órgãos, unidades, usuários;
- planos, assinaturas, módulos;
- PLANCON essencial;
- incidentes essenciais;
- relatórios básicos;
- auditoria mínima.

### Fase intermediária:

- expansão do núcleo CSI/SCO;
- expansão do SCI/SCO completo;
- documentos, evidências, relatórios avançados;
- maior refinamento de finanças e conformidade.

### Fase avançada:

- integrações;
- API;
- analytics mais sofisticado;
- histórico analítico;
- automações;
- governança mais avançada de documentos e eventos.

## 24. Riscos arquiteturais de banco que devem ser evitados

Os principais riscos a evitar são:

1. modelar tudo centrado apenas em usuário;
2. fundir conta contratante e órgão operador;
3. transformar PLANCON em tabela única;
4. transformar Incidente em tabela única;
5. misturar dados comerciais e operacionais sem fronteira;
6. não prever escopo institucional em tabelas sensíveis;
7. não prever rastreabilidade mínima;
8. não prever estrutura documental coerente;
9. criar excesso de campos livres em vez de entidades relacionais;
10. desenhar o banco sem considerar relatórios e filtros reais.

## 25. Representação textual da arquitetura do banco

A arquitetura de banco de dados do SIGERD pode ser resumida da seguinte forma:

**Nível 1 — Base institucional**

- contas
- órgãos
- unidades
- usuários
- perfis

**Nível 2 — Base contratual**

- planos
- assinaturas
- faturas
- módulos liberados
- conformidade

**Nível 3 — Base de gestão do risco**

- plancons
- blocos do plano
- núcleo CSI/SCO do plano

**Nível 4 — Base de gerenciamento do desastre**

- incidentes
- blocos do incidente
- núcleo SCI/SCO do incidente

**Nível 5 — Base transversal**

- anexos
- auditoria
- logs
- relatórios
- exportações
- integrações futuras

## 26. Conclusão técnica

A arquitetura de banco de dados do SIGERD precisa refletir a natureza real do produto: um SaaS institucional que combina comercialização, gestão organizacional, planejamento preventivo, coordenação operacional da resposta, documentação e rastreabilidade.

A decisão mais importante deste documento é esta: o banco não pode ser nem simplista nem genérico demais. Ele precisa ser modular, relacional e orientado por domínio. Em especial:

- **conta contratante** e **órgão operador** precisam ser distintos;
- **PLANCON** e **Incidente** precisam ser domínios separados;
- os núcleos operacionais de comando precisam existir como subestruturas relacionais;
- a base precisa nascer preparada para relatórios, filtros e auditoria.

Se essa arquitetura for respeitada, o SIGERD terá base sólida para o próximo passo: detalhar tabelas, relacionamentos, chaves e dicionário de dados. Se for ignorada, o risco mais provável será um banco confuso, acoplado e incapaz de sustentar a complexidade funcional prometida pelo sistema.