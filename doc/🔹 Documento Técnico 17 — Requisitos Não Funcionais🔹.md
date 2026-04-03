**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Requisitos Não Funcionais  
**Objetivo:** definir os requisitos de qualidade, restrições técnicas e critérios operacionais que o sistema deverá atender para funcionar de forma segura, estável, escalável, utilizável e sustentável.

## 1. Finalidade do documento

Este documento estabelece os requisitos não funcionais do SIGERD. Seu propósito é complementar os requisitos funcionais já definidos, determinando como o sistema deve se comportar do ponto de vista técnico e operacional.

Enquanto os requisitos funcionais descrevem **o que o sistema faz**, os requisitos não funcionais definem **como o sistema deve fazer**, com que nível de qualidade, confiabilidade, segurança e eficiência.

No contexto do SIGERD, isso é especialmente importante porque o sistema foi concebido para:

- operar como SaaS institucional;
- suportar múltiplos perfis e estruturas organizacionais;
- gerenciar planos de contingência e incidentes;
- lidar com dados operacionais e administrativos sensíveis;
- sustentar painéis, relatórios, rastreabilidade e governança institucional.

## 2. Objetivos dos requisitos não funcionais

Os requisitos não funcionais do SIGERD deverão atender aos seguintes objetivos:

- garantir segurança da informação;
- assegurar desempenho operacional adequado;
- sustentar disponibilidade compatível com uso institucional;
- permitir escalabilidade progressiva;
- facilitar manutenção e evolução;
- garantir usabilidade em contextos administrativos e operacionais;
- preservar integridade e rastreabilidade dos dados;
- permitir interoperabilidade futura;
- apoiar conformidade legal e institucional;
- reduzir risco de falha em cenários críticos de uso.

## 3. Classificação dos requisitos não funcionais

Os requisitos não funcionais do SIGERD estão organizados nos seguintes grupos:

1. requisitos de arquitetura e estrutura técnica;
2. requisitos de desempenho;
3. requisitos de disponibilidade e continuidade;
4. requisitos de segurança;
5. requisitos de controle de acesso e auditoria;
6. requisitos de usabilidade e experiência de uso;
7. requisitos de compatibilidade e responsividade;
8. requisitos de escalabilidade;
9. requisitos de manutenibilidade e evolução;
10. requisitos de integridade e qualidade de dados;
11. requisitos de interoperabilidade e integração;
12. requisitos de conformidade legal e institucional;
13. requisitos de observabilidade e suporte operacional.

---

# 4. Requisitos de arquitetura e estrutura técnica

## RNF-001

O sistema deverá ser desenvolvido com arquitetura modular, em camadas, separando apresentação, aplicação, domínio e infraestrutura.

## RNF-002

O sistema deverá manter separação estrutural entre:

- área pública comercial;
- área administrativa SaaS;
- área operacional do usuário.

## RNF-003

O backend deverá ser organizado por domínios funcionais, evitando concentração excessiva de lógica em arquivos únicos.

## RNF-004

A aplicação deverá permitir crescimento progressivo sem necessidade de reestruturação destrutiva da base do projeto.

## RNF-005

O sistema deverá possuir organização física coerente de pastas, arquivos, controladores, serviços, repositórios e recursos visuais.

## RNF-006

As regras de negócio críticas não deverão depender da camada de interface para serem aplicadas.

## RNF-007

A arquitetura deverá permitir a separação entre regras contratuais do SaaS e regras operacionais do cliente.

---

# 5. Requisitos de desempenho

## RNF-008

O sistema deverá apresentar tempo de resposta adequado às operações administrativas e operacionais mais frequentes.

## RNF-009

Consultas listadas em telas principais deverão ser paginadas ou filtradas de forma a evitar carregamento excessivo de registros em uma única requisição.

## RNF-010

Relatórios, filtros e dashboards deverão ser otimizados para consultas por:

- conta;
- órgão;
- unidade;
- período;
- status;
- tipo;
- módulo.

## RNF-011

O sistema deverá utilizar índices de banco compatíveis com as consultas mais recorrentes dos módulos administrativos, PLANCON, incidentes, relatórios e auditoria.

## RNF-012

Operações críticas de gravação deverão preservar consistência sem comprometer excessivamente a fluidez de uso.

## RNF-013

Arquivos, relatórios e exportações deverão ser processados de forma controlada para evitar degradação desnecessária do ambiente.

## RNF-014

A geração de relatórios e exportações deverá respeitar critérios de filtro e escopo, evitando extrações massivas indevidas por padrão.

---

# 6. Requisitos de disponibilidade e continuidade

## RNF-015

O sistema deverá estar disponível para uso institucional em regime compatível com rotina administrativa e operacional.

## RNF-016

O sistema deverá possuir tratamento de falhas que evite indisponibilidade total por erro pontual de módulo.

## RNF-017

O ambiente deverá prever estratégia de backup periódico do banco de dados e dos arquivos essenciais.

## RNF-018

O sistema deverá permitir recuperação administrativa após falha, sem perda generalizada de integridade referencial.

## RNF-019

Registros críticos, como incidentes, períodos operacionais, auditoria, assinaturas e aceites legais, não deverão depender de armazenamento temporário não persistente.

## RNF-020

O sistema deverá reduzir dependência de operações destrutivas, privilegiando bloqueio, inativação ou encerramento lógico em registros sensíveis.

---

# 7. Requisitos de segurança

## RNF-021

Toda área interna do sistema deverá exigir autenticação válida.

## RNF-022

O sistema deverá proteger credenciais por meio de armazenamento seguro de senha em formato hash.

## RNF-023

O sistema deverá impedir acesso a módulos internos apenas por digitação direta de URL quando o usuário não estiver autorizado.

## RNF-024

As validações de segurança deverão ocorrer no backend, independentemente do comportamento da interface.

## RNF-025

O sistema deverá tratar uploads de arquivos com validação de tipo, tamanho e contexto de uso.

## RNF-026

Arquivos sensíveis não deverão ficar expostos de forma irrestrita em área pública do servidor.

## RNF-027

O sistema deverá registrar eventos de autenticação relevantes, inclusive tentativas mal-sucedidas quando aplicável.

## RNF-028

A aplicação deverá reduzir risco de acesso indevido entre contas, órgãos e unidades.

## RNF-029

O sistema deverá suportar autenticação em dois fatores, ao menos nos contextos em que a conta ou o usuário optarem por esse reforço.

## RNF-030

A sessão do usuário deverá ser invalidada de forma segura em logout, expiração ou revogação administrativa.

---

# 8. Requisitos de controle de acesso e auditoria

## RNF-031

O sistema deverá aplicar autorização com base em perfil, escopo institucional e módulo contratado.

## RNF-032

Usuários não deverão visualizar nem manipular registros fora do escopo institucional permitido.

## RNF-033

A liberação de módulo deverá depender do plano contratado e do estado da assinatura.

## RNF-034

Ações sensíveis deverão ser auditáveis.

## RNF-035

O sistema deverá manter trilha de auditoria para eventos administrativos, contratuais e operacionais relevantes.

## RNF-036

A auditoria deverá registrar, sempre que possível:

- usuário;
- conta;
- órgão;
- unidade;
- módulo;
- ação;
- data/hora;
- resultado;
- contexto mínimo do evento.

## RNF-037

Perfis administrativos do SaaS não deverão ser automaticamente tratados como perfis operacionais do cliente.

## RNF-038

A política de acesso deverá impedir superposição indevida entre privilégios comerciais, administrativos e operacionais.

---

# 9. Requisitos de usabilidade e experiência de uso

## RNF-039

O sistema deverá possuir navegação clara e distinta para as três áreas do produto.

## RNF-040

A área pública deverá priorizar clareza institucional, apresentação do valor do produto e fluxo de conversão.

## RNF-041

A área administrativa deverá priorizar governança, leitura gerencial, filtros e controle contratual.

## RNF-042

A área operacional deverá priorizar velocidade de leitura, clareza visual, redução de ruído e apoio à decisão.

## RNF-043

O sistema deverá utilizar terminologia coerente com o contexto institucional de Defesa Civil, gerenciamento de riscos e resposta a desastres.

## RNF-044

Telas com grande volume de informação deverão preferir organização em abas, blocos ou seções, evitando formulários monolíticos.

## RNF-045

PLANCON e SCI/SCO não deverão ser apresentados como telas únicas excessivamente longas quando a modularização melhorar a operação.

## RNF-046

Mensagens de erro, sucesso, bloqueio e validação deverão ser compreensíveis ao usuário final.

## RNF-047

O sistema deverá minimizar ambiguidade entre dados administrativos e dados operacionais.

---

# 10. Requisitos de compatibilidade e responsividade

## RNF-048

O sistema deverá ser compatível com uso em desktop, notebook, tablet e smartphone.

## RNF-049

A interface deverá ser responsiva para diferentes larguras de tela.

## RNF-050

Menus, filtros, tabelas e painéis deverão adaptar-se ao contexto mobile sem perda crítica de usabilidade.

## RNF-051

Páginas internas deverão preservar legibilidade em resoluções reduzidas.

## RNF-052

A experiência de navegação em dispositivos móveis deverá priorizar componentes recolhíveis, filtros compactos e hierarquia visual clara.

## RNF-053

Os componentes principais da plataforma deverão manter comportamento compatível com navegadores modernos de uso institucional.

---

# 11. Requisitos de escalabilidade

## RNF-054

O sistema deverá permitir operação por contas com diferentes portes, desde estruturas pequenas até contas multiunidade e multiórgão, conforme o plano contratado.

## RNF-055

A modelagem de banco deverá suportar múltiplos incidentes, múltiplos planos e múltiplas estruturas operacionais sem colapso estrutural.

## RNF-056

A arquitetura deverá permitir inclusão futura de novos módulos sem reescrita geral do sistema.

## RNF-057

O sistema deverá permitir crescimento progressivo da carga documental, desde que dentro dos limites contratuais e técnicos definidos.

## RNF-058

A estrutura de serviços e controladores deverá suportar desdobramento de módulos complexos em submódulos sem ruptura da arquitetura.

## RNF-059

A plataforma deverá permitir evolução futura para integrações, API, analytics e automações, mesmo que esses recursos não estejam completos na primeira fase.

---

# 12. Requisitos de manutenibilidade e evolução

## RNF-060

O código-fonte deverá ser organizado de forma previsível, modular e aderente à arquitetura definida.

## RNF-061

O sistema deverá reduzir duplicação de lógica de negócio entre controladores, serviços e views.

## RNF-062

As regras de domínio deverão ser concentradas em services ou estruturas equivalentes, evitando dispersão.

## RNF-063

O projeto deverá possuir nomenclatura consistente de arquivos, classes, tabelas e campos.

## RNF-064

A documentação técnica do sistema deverá poder ser atualizada de forma incremental ao longo da evolução do projeto.

## RNF-065

A estrutura do projeto deverá favorecer depuração, correção e expansão sem necessidade de alterar múltiplos arquivos sem contexto.

## RNF-066

O sistema deverá minimizar dependência de arquivos genéricos e monolíticos.

## RNF-067

O projeto deverá permitir manutenção por novos desenvolvedores sem exigir reconstrução mental completa do sistema a partir de arquivos dispersos.

---

# 13. Requisitos de integridade e qualidade de dados

## RNF-068

A base de dados deverá manter integridade referencial entre conta, órgão, unidade, usuário, assinatura, plano, PLANCON, incidente e tabelas dependentes.

## RNF-069

O sistema deverá evitar duplicidade lógica em vínculos críticos, como:

- usuário e perfil;
- perfil e permissão;
- assinatura e módulo;
- período operacional dentro do mesmo incidente ou estrutura.

## RNF-070

Campos essenciais de identificação, status, período e vínculo institucional deverão possuir restrições mínimas de consistência.

## RNF-071

O banco deverá ser orientado a histórico e rastreabilidade, evitando exclusões destrutivas em entidades sensíveis.

## RNF-072

Registros de incidentes, períodos operacionais, registros operacionais, assinaturas e aceites legais deverão preservar contexto histórico mínimo.

## RNF-073

O sistema deverá permitir coerência entre dados do PLANCON e dados do incidente sem fundir indevidamente seus domínios.

---

# 14. Requisitos de interoperabilidade e integração

## RNF-074

A arquitetura deverá permitir integração futura com APIs, serviços externos e mecanismos de intercâmbio de dados.

## RNF-075

A organização dos módulos não deverá impedir evolução futura para endpoints de API controlados.

## RNF-076

A estrutura documental e de anexos deverá permitir vinculação futura com fontes externas ou artefatos gerados por integração.

## RNF-077

O sistema deverá preservar consistência de identificadores e vínculos institucionais para facilitar integrações futuras.

## RNF-078

Relatórios e exportações deverão utilizar formatos passíveis de intercâmbio administrativo e técnico, como PDF, CSV e planilhas, quando aplicável.

---

# 15. Requisitos de conformidade legal e institucional

## RNF-079

O sistema deverá registrar aceite de termos de uso, política de privacidade e LGPD quando aplicável.

## RNF-080

A plataforma deverá manter trilha mínima de responsabilidade sobre ações administrativas e operacionais sensíveis.

## RNF-081

Os dados institucionais e operacionais deverão ser tratados com controle de acesso compatível com a sensibilidade do ambiente.

## RNF-082

O sistema deverá permitir associação de documentos comprobatórios, evidências e anexos a registros formais.

## RNF-083

A estrutura do sistema deverá apoiar governança documental básica sobre contratos, comprovantes, anexos do plano, evidências do incidente e registros de conformidade.

## RNF-084

O modelo de auditoria deverá ser compatível com responsabilização institucional.

---

# 16. Requisitos de observabilidade e suporte operacional

## RNF-085

O sistema deverá registrar logs técnicos mínimos para apoio à manutenção.

## RNF-086

O sistema deverá distinguir logs técnicos de trilha funcional de auditoria.

## RNF-087

Falhas relevantes deverão poder ser identificadas sem depender exclusivamente de inspeção manual do banco.

## RNF-088

O sistema deverá permitir rastreamento de eventos críticos por usuário, módulo e período.

## RNF-089

A estrutura de logs deverá apoiar investigação de falhas de acesso, uso indevido, erros operacionais e inconsistências administrativas.

## RNF-090

A geração de relatórios, exportações e operações sensíveis deverá poder ser associada a usuário e contexto temporal.

---

# 17. Requisitos específicos por natureza do módulo

## 17.1. Área pública

## RNF-091

A área pública deverá priorizar leveza, clareza e carregamento adequado.

## RNF-092

Formulários públicos deverão possuir validação mínima contra entradas inválidas ou abusivas.

## 17.2. Área administrativa

## RNF-093

A área administrativa deverá privilegiar consistência de filtros, visões gerenciais e rastreabilidade de mudanças.

## RNF-094

Operações contratuais e financeiras deverão ter proteção reforçada de acesso e auditoria.

## 17.3. Área operacional

## RNF-095

A área operacional deverá suportar fluxo contínuo de trabalho institucional com prioridade para rapidez de navegação e leitura situacional.

## RNF-096

Os módulos de incidente e PLANCON deverão ser modularizados para reduzir risco de erro operacional por telas excessivamente concentradas.

## RNF-097

A operação sobre períodos, registros, funções, equipes e instalações deverá preservar rastreabilidade cronológica e institucional.

---

# 18. Restrições técnicas gerais

## RNF-098

O sistema deverá ser compatível com banco relacional aderente à modelagem definida para o projeto.

## RNF-099

O sistema deverá suportar armazenamento estruturado de anexos e metadados de arquivo.

## RNF-100

A aplicação deverá operar com separação entre arquivos públicos e arquivos internos sensíveis.

## RNF-101

O projeto deverá permitir deploy em ambiente web compatível com a arquitetura prevista para o produto.

## RNF-102

A configuração de ambiente deverá ser externalizada de forma controlada, evitando valores sensíveis embutidos diretamente no código.

---

# 19. Critérios de aceitação não funcional em alto nível

Para considerar o SIGERD aderente aos requisitos não funcionais, o sistema deverá demonstrar, no mínimo:

- organização modular coerente;
- autenticação e autorização efetivas;
- escopo institucional corretamente aplicado;
- separação entre contextos público, administrativo e operacional;
- consistência relacional do banco;
- trilha básica de auditoria;
- responsividade adequada;
- possibilidade de expansão controlada;
- estrutura de manutenção sustentável;
- tratamento razoável de documentos, relatórios e exportações.

---

# 20. Riscos que estes requisitos procuram evitar

Este conjunto de requisitos não funcionais procura evitar, principalmente:

1. sistema visualmente funcional, mas estruturalmente inseguro;
2. colapso do projeto por acoplamento entre módulos;
3. vazamento de escopo entre órgãos, unidades ou contas;
4. degradação de desempenho em relatórios e painéis;
5. perda de rastreabilidade em incidentes e aprovações;
6. dificuldade extrema de manutenção;
7. fragilidade contratual no SaaS;
8. desorganização documental;
9. baixa adaptação a dispositivos móveis;
10. incapacidade de crescimento do produto.

---

# 21. Conclusão técnica

Os requisitos não funcionais do SIGERD mostram que a qualidade do sistema não depende apenas de ele “ter funcionalidades”. O valor real da plataforma depende de ela ser:

- segura;
- estável;
- governável;
- auditável;
- modular;
- escalável;
- utilizável em contexto institucional real.

Esse ponto é especialmente importante porque o SIGERD não foi concebido como um sistema simples de cadastro. Ele combina dimensões comerciais, institucionais, preventivas, operacionais e documentais. Sem requisitos não funcionais claros, a implementação corre o risco de cumprir parte do escopo visível, mas falhar no que sustenta o produto em produção.