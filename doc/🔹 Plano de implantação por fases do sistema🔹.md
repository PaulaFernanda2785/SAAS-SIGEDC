**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Objetivo:** definir a estratégia de implantação progressiva do sistema, priorizando entregas de maior valor operacional e estrutural, reduzindo risco técnico e preservando a coerência arquitetural do projeto.

## 1. Finalidade do plano

Este plano organiza a implantação do SIGERD em fases sucessivas, com foco em:

- reduzir risco de execução;
- permitir validação progressiva;
- evitar supercarga de desenvolvimento simultâneo;
- entregar valor real desde as primeiras versões;
- manter aderência à arquitetura completa do produto.

A lógica central é simples: o sistema deve nascer com base sólida e crescer por camadas, sem comprometer a visão final.

## 2. Princípios da implantação

A implantação deverá seguir estes princípios:

1. primeiro a base estrutural, depois o refinamento;
2. primeiro módulos de uso recorrente, depois módulos avançados;
3. primeiro segurança e integridade, depois sofisticação visual;
4. primeiro operação mínima viável, depois expansão analítica e institucional;
5. nenhuma fase deve quebrar a arquitetura prevista para a fase seguinte.

## 3. Estratégia geral

A implantação do SIGERD é recomendada em **6 fases**:

- Fase 0 — Preparação técnica e fundação do projeto
- Fase 1 — Núcleo SaaS e identidade institucional
- Fase 2 — Núcleo operacional mínimo viável
- Fase 3 — Expansão do PLANCON e do gerenciamento de desastres
- Fase 4 — Inteligência operacional, documentos e governança avançada
- Fase 5 — Escala institucional, integrações e recursos enterprise

Essa divisão é superior a uma implantação “módulo por módulo” isolada, porque respeita dependências reais entre autenticação, escopo, banco, operação e relatórios.

---

# Fase 0 — Preparação técnica e fundação do projeto

## Objetivo

Criar a base técnica do sistema antes da entrada dos módulos de negócio.

## Entregas principais

- estrutura inicial do repositório;
- bootstrap da aplicação;
- configuração de ambiente;
- roteamento por contexto;
- arquitetura de pastas;
- base do frontend interno e público;
- conexão com banco;
- tratamento de erros;
- serviço central de autenticação;
- estrutura de logs técnicos;
- base da auditoria funcional;
- modelo inicial de anexos;
- esquema inicial do banco com tabelas centrais.

## Módulos envolvidos

- arquitetura do projeto;
- camadas;
- estrutura de pastas;
- organização de arquivos;
- estrutura do backend;
- banco de dados base;
- controle de acesso base.

## Critérios de aceite

- aplicação sobe de forma estável;
- áreas pública, administrativa e operacional já estão separadas;
- autenticação básica está funcional;
- banco inicial criado com integridade mínima;
- logs e auditoria básica já existem;
- ambiente pronto para receber módulos.

## Risco principal

Entrar em desenvolvimento funcional antes de estabilizar a fundação técnica.

## Duração relativa

Curta a média.

---

# Fase 1 — Núcleo SaaS e identidade institucional

## Objetivo

Implantar a base comercial e institucional do sistema, sem a qual a operação multiusuário e multiórgão fica inconsistente.

## Entregas principais

- área pública inicial;
- landing page institucional;
- página de planos;
- página de demonstração;
- login e recuperação de senha;
- cadastro de contas contratantes;
- cadastro de órgãos;
- cadastro de unidades;
- cadastro de usuários;
- cadastro de perfis;
- vínculo usuário-perfil;
- catálogo de planos;
- criação de assinaturas;
- liberação de módulos por assinatura;
- controle básico de situação contratual;
- painel administrativo inicial.

## Módulos envolvidos

- área pública;
- autenticação;
- contas;
- órgãos;
- unidades;
- usuários;
- perfis;
- planos;
- assinaturas;
- módulos contratados.

## Critérios de aceite

- é possível criar uma conta e vinculá-la a um plano;
- é possível cadastrar órgão, unidade e usuários;
- é possível autenticar usuários por contexto;
- o sistema já aplica distinção entre administrativo e operacional;
- já existe bloqueio básico por status de usuário e status contratual;
- a área pública já apresenta o produto de forma utilizável.

## Valor entregue

Nesta fase, o SIGERD já deixa de ser apenas protótipo técnico e se torna um SaaS institucional utilizável em base controlada.

## Risco principal

Modelar usuários sem respeitar conta, órgão e unidade.

## Duração relativa

Média.

---

# Fase 2 — Núcleo operacional mínimo viável

## Objetivo

Entregar a primeira versão operacional realmente útil para uso institucional.

## Entregas principais

- painel operacional inicial;
- cadastro e listagem de incidentes;
- abertura de incidente;
- briefing inicial;
- comando do incidente em versão inicial;
- períodos operacionais;
- objetivos do incidente;
- registros operacionais / diário;
- recursos mobilizados em versão inicial;
- relatórios operacionais básicos;
- filtros por órgão, unidade, período e status;
- exportação inicial em PDF/planilha;
- auditoria de ações críticas operacionais.

## Módulos envolvidos

- dashboard operacional;
- incidentes;
- briefing;
- comando;
- períodos operacionais;
- registros operacionais;
- relatórios;
- exportações;
- controle de acesso operacional.

## Critérios de aceite

- um usuário autorizado consegue abrir e acompanhar um incidente;
- o incidente possui contexto, status e histórico;
- é possível registrar evolução temporal por período e diário;
- relatórios básicos já funcionam;
- o escopo institucional já restringe visualização e operação;
- ações críticas já são auditadas.

## Valor entregue

Esta é a primeira fase em que o SIGERD entrega valor operacional direto ao cliente final.

## Risco principal

Querer sofisticar SCI/SCO completo antes de estabilizar ciclo básico do incidente.

## Duração relativa

Média a alta.

---

# Fase 3 — Expansão do PLANCON e do gerenciamento de desastres

## Objetivo

Ampliar a maturidade funcional dos dois grandes núcleos do sistema: gestão do risco e resposta operacional.

## Entregas principais

### PLANCON

- criação e edição de planos;
- identificação geral;
- território;
- riscos;
- cenários;
- níveis de ativação;
- recursos do plano;
- anexos básicos;
- revisão inicial;
- versionamento inicial.

### Incidentes / SCI-SCO

- staff do comando;
- staff geral;
- estratégias e PAI;
- operações de campo;
- planejamento e situação;
- instalações do incidente;
- comunicações integradas;
- segurança operacional;
- desmobilização básica.

## Módulos envolvidos

- PLANCON;
- bloco de riscos;
- bloco de cenários;
- ativação;
- recursos;
- revisão;
- SCI/SCO ampliado;
- instalações;
- operações;
- planejamento;
- comunicações;
- segurança.

## Critérios de aceite

- já existe PLANCON funcional em blocos;
- incidentes já têm condução operacional mais completa;
- a ligação entre plano e incidente pode existir, mas sem fusão estrutural;
- o backend sustenta módulos densos sem colapso em controllers e views.

## Valor entregue

Nesta fase, o SIGERD passa de sistema operacional básico para plataforma institucional robusta.

## Risco principal

Transformar PLANCON em formulário monolítico ou concentrar módulos densos em poucos arquivos.

## Duração relativa

Alta.

---

# Fase 4 — Inteligência operacional, documentos e governança avançada

## Objetivo

Refinar o sistema para leitura situacional, qualidade documental e controle institucional mais maduro.

## Entregas principais

- mapa operacional em destaque;
- camadas geográficas e operacionais;
- gráficos e indicadores avançados;
- relatórios administrativos e operacionais ampliados;
- serviço documental central com vínculos por entidade;
- gestão refinada de anexos;
- persistência de relatórios/exportações, quando necessário;
- auditoria reforçada;
- controle mais detalhado de conformidade;
- políticas de aceite legal;
- histórico contratual e financeiro mais maduro.

## Módulos envolvidos

- mapas;
- analytics;
- relatórios avançados;
- anexos;
- exportações;
- conformidade;
- auditoria;
- financeiro ampliado.

## Critérios de aceite

- o painel operacional oferece leitura situacional mais rica;
- relatórios já atendem uso institucional e gerencial;
- anexos e documentos possuem rastreabilidade adequada;
- eventos sensíveis estão claramente auditados;
- a navegação continua clara apesar do aumento funcional.

## Valor entregue

A plataforma ganha capacidade de gestão, evidência e governança, não apenas de operação.

## Risco principal

Excesso de visualização sem qualidade do dado subjacente.

## Duração relativa

Média a alta.

---

# Fase 5 — Escala institucional, integrações e recursos enterprise

## Objetivo

Preparar o SIGERD para ambientes mais exigentes, multiinstitucionais e com maior sofisticação técnica.

## Entregas principais

- suporte mais completo a multiunidade e multiórgão;
- recursos avançados por plano enterprise/governo;
- assinatura digital, se aprovada;
- API controlada;
- integrações externas;
- automações;
- analytics avançado;
- relatórios executivos consolidados;
- gestão mais refinada de SLA e suporte;
- recursos de governança ampliada para contas maiores.

## Módulos envolvidos

- integrações;
- API;
- assinatura digital;
- analytics avançado;
- administração enterprise;
- suporte e governança avançada.

## Critérios de aceite

- arquitetura suporta crescimento sem refatoração estrutural profunda;
- integrações não quebram a regra central de domínio;
- enterprise/governo opera com segurança e separação adequada de escopo;
- novos módulos entram sem desorganizar a base.

## Valor entregue

O SIGERD se torna produto maduro para escalar comercial e institucionalmente.

## Risco principal

Adicionar integrações e automações antes de estabilizar o núcleo transacional.

## Duração relativa

Alta.

---

# 4. Mapa resumido por fase

|Fase|Nome|Foco principal|
|---|---|---|
|0|Preparação técnica|Fundação arquitetural e ambiente|
|1|Núcleo SaaS e institucional|Conta, órgãos, usuários, planos, assinaturas|
|2|Núcleo operacional mínimo viável|Incidentes, diário, períodos, relatórios básicos|
|3|Expansão operacional|PLANCON modular e SCI/SCO ampliado|
|4|Inteligência e governança|Mapas, analytics, documentos, auditoria avançada|
|5|Escala e enterprise|Integrações, API, automações, recursos avançados|

---

# 5. Dependências entre fases

## Dependências críticas

- A Fase 1 depende da Fase 0 completamente estabilizada.
- A Fase 2 depende da autenticação, escopo institucional e cadastro básico da Fase 1.
- A Fase 3 depende do núcleo operacional da Fase 2 já consistente.
- A Fase 4 depende de banco, relatórios e módulos operacionais com dados confiáveis.
- A Fase 5 depende da estabilização de regras internas, segurança e rastreabilidade.

## Regra importante

Não é recomendável adiantar recursos de Fase 4 ou 5 enquanto Fase 2 ainda estiver instável, porque isso tende a multiplicar retrabalho.

---

# 6. Critérios de priorização dentro de cada fase

Dentro de cada fase, os itens devem ser priorizados por este critério:

1. segurança e integridade;
2. fluxo-base do módulo;
3. persistência e vínculo institucional;
4. usabilidade mínima;
5. relatórios básicos;
6. refinamentos visuais;
7. automações e extras.

Isso evita um erro comum: investir cedo demais em estética e conveniência antes de estabilizar o fluxo principal.

---

# 7. Estratégia de validação por fase

Cada fase deve terminar com uma validação própria.

## Fase 0

Validação técnica interna.

## Fase 1

Validação administrativa e de cadastro institucional.

## Fase 2

Validação operacional com cenários reais de abertura, atualização e consulta de incidente.

## Fase 3

Validação funcional dos módulos PLANCON e SCI/SCO ampliado.

## Fase 4

Validação gerencial e documental.

## Fase 5

Validação de escala, integração e governança ampliada.

---

# 8. Riscos de implantação que este plano reduz

Este plano reduz principalmente os seguintes riscos:

- tentar construir o sistema inteiro de uma vez;
- colapsar o backend por excesso de módulos simultâneos;
- criar banco muito sofisticado antes do uso real;
- atrasar a entrega do valor operacional;
- concentrar esforço em recursos enterprise antes da base;
- perder coerência entre módulos ao crescer sem fases;
- comprometer segurança e escopo por pressa de entrega.

---

# 9. Recomendação executiva

Se o objetivo for maximizar viabilidade de implantação, a melhor decisão é tratar como **marco de primeira entrega real** a combinação das Fases 0, 1 e 2. Isso já coloca o SIGERD em condição de:

- apresentar-se como produto;
- cadastrar clientes e estruturas institucionais;
- operar incidentes de forma real;
- gerar relatórios básicos;
- aplicar segurança e escopo adequados.

A Fase 3 é o momento em que o sistema deixa de ser um núcleo operacional e passa a ser uma plataforma institucional mais completa.

---

# 10. Conclusão técnica

O SIGERD tem escopo suficiente para justificar implantação por fases. Isso não é sinal de fraqueza do projeto; é sinal de maturidade. A documentação já mostrou que o sistema possui alta densidade funcional, múltiplos domínios e forte necessidade de governança. A consequência lógica é esta: a implantação precisa ser progressiva, disciplinada e orientada por valor.

A ordem mais correta é:

- fundação técnica;
- núcleo SaaS e institucional;
- núcleo operacional mínimo viável;
- expansão do PLANCON e da resposta;
- inteligência, documentos e governança;
- escala e integrações.

Esse é o caminho com menor risco e maior chance de transformar a documentação do SIGERD em software efetivamente utilizável.