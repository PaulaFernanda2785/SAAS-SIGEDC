**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Estrutura de Tabelas  
**Objetivo:** inventariar, organizar e classificar as tabelas do banco de dados do sistema por domínio funcional, definindo sua finalidade estrutural dentro da arquitetura relacional do SIGERD.

## 1. Finalidade do documento

Este documento apresenta a estrutura de tabelas do SIGERD em nível arquitetural e organizacional. Seu objetivo é transformar a arquitetura de banco de dados em um inventário técnico claro das tabelas que comporão o sistema, agrupadas por domínio de negócio e vinculadas às funções do produto.

O foco aqui não é ainda detalhar todas as colunas individualmente. O foco é responder, de forma organizada:

- quais tabelas o sistema precisa;
- a que domínio cada tabela pertence;
- qual a finalidade de cada tabela;
- como essas tabelas se agrupam estruturalmente;
- quais tabelas são centrais, dependentes, auxiliares ou transversais.

Esse documento serve como base direta para os próximos artefatos de modelagem detalhada, especialmente:

- relacionamentos;
- chaves primárias e estrangeiras;
- dicionário de dados;
- índices e constraints.

## 2. Critérios de organização das tabelas

As tabelas do SIGERD foram organizadas pelos seguintes critérios:

1. domínio funcional do sistema;
2. responsabilidade estrutural da tabela;
3. separação entre núcleo contratual, institucional e operacional;
4. separação entre gestão do risco e gerenciamento do desastre;
5. presença de tabelas transversais para auditoria, documentos e conformidade.

## 3. Classificação estrutural das tabelas

As tabelas do sistema podem ser classificadas em quatro grupos principais:

### 3.1. Tabelas centrais

São as tabelas que representam entidades principais do sistema, como conta, órgão, usuário, plano, assinatura, PLANCON e incidente.

### 3.2. Tabelas dependentes

São as tabelas que existem em função de uma tabela central, como versões do plano, faturas, períodos operacionais, registros e anexos vinculados.

### 3.3. Tabelas associativas

São as tabelas que resolvem relacionamentos entre entidades, como usuários e perfis, ou assinatura e módulos.

### 3.4. Tabelas transversais

São as tabelas que atravessam vários domínios, como auditoria, conformidade, anexos e logs.

## 4. Macrodomínios de tabelas do SIGERD

O inventário de tabelas do SIGERD está organizado nos seguintes macrodomínios:

1. tabelas do domínio comercial/SaaS;
2. tabelas do domínio institucional;
3. tabelas do domínio de identidade e acesso;
4. tabelas do domínio de gestão do risco — PLANCON;
5. tabelas do núcleo operacional do PLANCON — CSI/SCO;
6. tabelas do domínio de gerenciamento do desastre — Incidentes / SCI-SCO;
7. tabelas do domínio de relatórios, arquivos e documentos;
8. tabelas do domínio de auditoria e conformidade.

---

# 5. Tabelas do domínio comercial / SaaS

Esse domínio sustenta a operação comercial da plataforma, os planos contratados, a cobrança e a habilitação dos módulos.

## 5.1. `contas`

Tabela central do domínio comercial.

### Finalidade:

registrar a conta contratante, ou seja, a entidade responsável comercialmente pela contratação do sistema.

### Papel estrutural:

é a raiz comercial do SaaS. Outras tabelas contratuais e institucionais se vinculam a ela.

## 5.2. `planos_assinatura`

Tabela central do catálogo comercial.

### Finalidade:

registrar os planos oferecidos pelo sistema, com seus limites, características e recursos liberados.

### Papel estrutural:

define a oferta comercial e as regras-base da monetização.

## 5.3. `assinaturas`

Tabela central do ciclo contratual.

### Finalidade:

vincular a conta contratante a um plano de assinatura, controlando vigência, ciclo de cobrança, status contratual e condições específicas do contrato.

### Papel estrutural:

é a tabela que representa o contrato ativo de uso da plataforma.

## 5.4. `faturas`

Tabela dependente da assinatura.

### Finalidade:

registrar cobrança, competência, vencimento, pagamento, comprovantes e situação financeira das assinaturas.

### Papel estrutural:

sustenta o controle financeiro do SaaS.

## 5.5. `modulos`

Tabela de catálogo funcional.

### Finalidade:

registrar os módulos disponíveis no sistema, como dashboard, PLANCON, SCI/SCO, relatórios, auditoria, integrações e outros.

### Papel estrutural:

funciona como referência padronizada para habilitação modular.

## 5.6. `assinatura_modulos`

Tabela associativa entre assinatura e módulos.

### Finalidade:

indicar quais módulos estão liberados, bloqueados ou limitados para determinada assinatura.

### Papel estrutural:

permite granularidade contratual além do plano base.

## 5.7. `leads_comerciais`

Tabela opcional, mas recomendada.

### Finalidade:

registrar leads oriundos da área pública, como solicitações de demonstração ou contato comercial.

### Papel estrutural:

apoia o funil comercial da plataforma.

---

# 6. Tabelas do domínio institucional

Esse domínio representa a estrutura organizacional do cliente dentro do SIGERD. O documento-base diferencia explicitamente conta contratante de órgão operador, e isso precisa aparecer nas tabelas.

## 6.1. `orgaos`

Tabela central do domínio institucional.

### Finalidade:

registrar o órgão, instituição ou entidade que efetivamente operará o sistema.

### Papel estrutural:

representa a unidade institucional operacional principal.

## 6.2. `unidades`

Tabela dependente do órgão.

### Finalidade:

registrar unidades, subunidades, regionais, bases, coordenadorias, COMPDECs, salas de situação e estruturas equivalentes.

### Papel estrutural:

permite granularidade organizacional interna.

## 6.3. `unidades_hierarquia`

Tabela opcional, recomendada se a hierarquia interna for relevante e complexa.

### Finalidade:

registrar relações hierárquicas entre unidades quando a simples coluna de referência a unidade superior não for suficiente.

### Papel estrutural:

organiza cadeias internas de subordinação institucional.

---

# 7. Tabelas do domínio de identidade e acesso

Esse domínio sustenta login, perfis, permissões, escopo e segurança do usuário.

## 7.1. `usuarios`

Tabela central de identidade operacional.

### Finalidade:

registrar as pessoas que acessam o sistema, vinculando-as à conta, órgão e unidade.

### Papel estrutural:

é a entidade de autenticação humana e operação sistêmica.

## 7.2. `perfis`

Tabela central de autorização.

### Finalidade:

registrar perfis de acesso, como ADMIN_MASTER, ADMIN_ORGAO, GESTOR, COORDENADOR, ANALISTA, OPERADOR, LEITOR e equivalentes.

### Papel estrutural:

padroniza papéis funcionais no sistema.

## 7.3. `usuarios_perfis`

Tabela associativa entre usuários e perfis.

### Finalidade:

permitir que um usuário possua um ou mais perfis, conforme o modelo de autorização adotado.

### Papel estrutural:

resolve vínculo N:N entre usuário e perfil.

## 7.4. `permissoes`

Tabela opcional, recomendada se a autorização for granular.

### Finalidade:

registrar ações e permissões específicas do sistema, como criar, editar, excluir, aprovar, exportar e assinar.

### Papel estrutural:

permite autorização mais refinada do que apenas perfis genéricos.

## 7.5. `perfis_permissoes`

Tabela associativa entre perfis e permissões.

### Finalidade:

definir quais permissões cada perfil possui.

### Papel estrutural:

sustenta o modelo granular de acesso.

## 7.6. `sessoes_usuario`

Tabela opcional, se houver persistência de sessões.

### Finalidade:

registrar sessões ativas, dispositivos vinculados, datas de acesso e contexto da sessão.

### Papel estrutural:

apoia segurança e rastreabilidade de acesso.

## 7.7. `autenticacao_2fator`

Tabela opcional, se houver persistência própria do 2FA.

### Finalidade:

registrar estado, segredo, método e situação de autenticação em dois fatores.

### Papel estrutural:

apoia a camada reforçada de segurança de acesso.

---

# 8. Tabelas do domínio de gestão do risco — PLANCON

Esse domínio sustenta a construção digital do plano de contingência, que no documento-base foi organizado em blocos. O desenho relacional precisa respeitar essa modularidade.

## 8.1. `plancons`

Tabela central do domínio PLANCON.

### Finalidade:

registrar o plano de contingência como entidade principal, com dados institucionais, escopo, vigência, status e metadados centrais.

### Papel estrutural:

é a entidade-mãe do módulo de gestão do risco.

## 8.2. `plancon_versoes`

Tabela dependente do plano.

### Finalidade:

registrar versões, revisões, histórico de atualização, motivo de revisão e controle de vigência.

### Papel estrutural:

sustenta governança documental do plano.

## 8.3. `plancon_territorios`

Tabela dependente do plano.

### Finalidade:

registrar caracterização do território, localidade, população, infraestruturas críticas, vias de acesso, abrigos e dados espaciais.

### Papel estrutural:

materializa o bloco territorial do plano.

## 8.4. `plancon_riscos`

Tabela dependente do plano.

### Finalidade:

registrar ameaças, descrição de risco, histórico, frequência, sazonalidade, vulnerabilidades, probabilidade, impacto e nível de risco.

### Papel estrutural:

materializa o bloco de análise de riscos.

## 8.5. `plancon_cenarios`

Tabela dependente do plano.

### Finalidade:

registrar cenários de desastre, área afetada estimada, população afetada, danos esperados e prioridades operacionais.

### Papel estrutural:

materializa o bloco de cenários.

## 8.6. `plancon_niveis_ativacao`

Tabela dependente do plano.

### Finalidade:

registrar níveis operacionais, critérios, gatilhos, autoridades, ações automáticas e procedimentos de escalonamento.

### Papel estrutural:

materializa o bloco de ativação operacional.

## 8.7. `plancon_governanca`

Tabela dependente do plano.

### Finalidade:

registrar órgãos, responsáveis, competências, responsabilidades por fase, substitutos e contatos.

### Papel estrutural:

materializa o bloco de governança e responsabilidades.

## 8.8. `plancon_recursos`

Tabela dependente do plano.

### Finalidade:

registrar recursos humanos, logísticos, materiais, equipamentos, veículos, insumos e disponibilidade preventiva.

### Papel estrutural:

materializa o bloco de recursos disponíveis.

## 8.9. `plancon_monitoramento_comunicacao`

Tabela dependente do plano.

### Finalidade:

registrar fontes de monitoramento, indicadores, limites, responsáveis, canais internos e externos e mensagens padrão.

### Papel estrutural:

materializa o bloco de monitoramento, alerta e comunicação.

## 8.10. `plancon_procedimentos`

Tabela dependente do plano.

### Finalidade:

registrar procedimentos operacionais, situações de aplicação, executor principal, apoio, passo a passo e critérios de conclusão.

### Papel estrutural:

materializa o bloco de procedimentos operacionais de resposta.

## 8.11. `plancon_rotas_abrigos`

Tabela dependente do plano.

### Finalidade:

registrar rotas de fuga, pontos de encontro, pontos de apoio, abrigos temporários, unidades de triagem e centros de distribuição.

### Papel estrutural:

materializa o bloco de rotas, pontos de apoio e abrigos.

## 8.12. `plancon_assistencia`

Tabela dependente do plano.

### Finalidade:

registrar tipos de atendimento à população, critérios, público-alvo, fluxos, recursos necessários e periodicidade.

### Papel estrutural:

materializa o bloco de assistência à população afetada.

## 8.13. `plancon_simulados`

Tabela dependente do plano.

### Finalidade:

registrar simulados, treinamentos, capacitações, objetivos, cenários, avaliação e medidas corretivas.

### Papel estrutural:

materializa o bloco de simulados e capacitações.

## 8.14. `plancon_revisoes`

Tabela dependente do plano.

### Finalidade:

registrar revisão do plano, alterações, pendências, próxima revisão e aprovação institucional.

### Papel estrutural:

materializa o bloco de monitoramento, avaliação e revisão do plano.

## 8.15. `plancon_anexos`

Tabela dependente do plano.

### Finalidade:

registrar anexos operacionais do plano, como mapas, fluxogramas, listas, formulários e checklists.

### Papel estrutural:

materializa o bloco de anexos operacionais.

---

# 9. Tabelas do núcleo operacional do PLANCON — CSI/SCO vinculado ao plano

O documento-base deixa claro que esse núcleo deve existir de forma relacional própria, e não como texto livre.

## 9.1. `plancon_estruturas_operacionais`

Tabela central do subdomínio CSI/SCO do plano.

### Finalidade:

registrar a estrutura de comando e coordenação da operação vinculada ao plano.

### Papel estrutural:

é a entidade-mãe do núcleo operacional do plano.

## 9.2. `plancon_funcoes_equipes`

Tabela dependente da estrutura operacional.

### Finalidade:

registrar funções, categorias, responsáveis, substitutos, turnos, atribuições e status operacional.

### Papel estrutural:

materializa o elo “pessoas” na cadeia estrutural.

## 9.3. `plancon_instalacoes`

Tabela dependente da estrutura operacional.

### Finalidade:

registrar posto de comando, base avançada, centro logístico, abrigo temporário e outras instalações ativadas.

### Papel estrutural:

materializa o elo “instalações”.

## 9.4. `plancon_periodos_operacionais`

Tabela dependente da estrutura operacional.

### Finalidade:

registrar períodos operacionais, objetivos, prioridades, estratégias, indicadores e situação ao encerramento.

### Papel estrutural:

materializa o elo “tempo” na cadeia de gestão do comando.

## 9.5. `plancon_registros_operacionais`

Tabela dependente da estrutura operacional e do período operacional.

### Finalidade:

registrar diário operacional do comando, decisões, ocorrências, acionamentos, mobilizações, evidências e encaminhamentos.

### Papel estrutural:

materializa o elo “registros” da cadeia operacional.

---

# 10. Tabelas do domínio de gerenciamento do desastre — Incidentes / SCI-SCO

Esse domínio representa a resposta operacional ao acidente ou desastre, e deve ser modelado separadamente do PLANCON.

## 10.1. `incidentes`

Tabela central do domínio de resposta.

### Finalidade:

registrar a ocorrência/incidente, sua abertura, localização, classificação inicial, danos iniciais, riscos imediatos, status e contexto institucional.

### Papel estrutural:

é a entidade-mãe do módulo de gerenciamento do desastre.

## 10.2. `incidentes_briefing`

Tabela dependente do incidente.

### Finalidade:

registrar briefing inicial do incidente, equivalente funcional ao SCI 201 descrito no documento-base.

### Papel estrutural:

materializa a consolidação inicial da situação.

## 10.3. `incidentes_comando`

Tabela dependente do incidente.

### Finalidade:

registrar tipo de comando, comandante, comando unificado, assunção, transferência, base legal e restrições operacionais.

### Papel estrutural:

materializa o núcleo de comando do incidente.

## 10.4. `incidentes_staff_comando`

Tabela dependente do incidente.

### Finalidade:

registrar staff do comando, como segurança, informação pública, ligação e assessorias.

### Papel estrutural:

materializa apoio direto ao comando.

## 10.5. `incidentes_staff_geral`

Tabela dependente do incidente.

### Finalidade:

registrar seções funcionais como operações, planejamento, logística, administração/finanças e inteligência.

### Papel estrutural:

materializa a estrutura funcional de execução da resposta.

## 10.6. `incidentes_objetivos`

Tabela dependente do incidente e, em geral, do período operacional.

### Finalidade:

registrar objetivos do incidente, prioridade, prazo, responsável, indicador e resultado.

### Papel estrutural:

materializa o gerenciamento por objetivos.

## 10.7. `incidentes_estrategias_pai`

Tabela dependente do incidente e do período.

### Finalidade:

registrar estratégia, táticas, atividades, responsáveis, recursos necessários, áreas prioritárias e versão do PAI.

### Papel estrutural:

materializa a direção estratégica e tática da resposta.

## 10.8. `incidentes_operacoes_campo`

Tabela dependente do incidente e do período.

### Finalidade:

registrar frentes operacionais, setores, supervisores, missão tática, recursos designados, situação atual e resultados parciais.

### Papel estrutural:

materializa a execução de campo.

## 10.9. `incidentes_planejamento_situacao`

Tabela dependente do incidente e do período.

### Finalidade:

registrar situação consolidada, prognóstico, cenário provável, pendências críticas e escalonamento.

### Papel estrutural:

materializa a leitura situacional e o planejamento.

## 10.10. `incidentes_recursos`

Tabela dependente do incidente.

### Finalidade:

registrar recursos mobilizados, tipo, categoria, quantidade, status, localização, supervisor e condição operacional.

### Papel estrutural:

materializa o gerenciamento de recursos do incidente.

## 10.11. `incidentes_instalacoes`

Tabela dependente do incidente.

### Finalidade:

registrar instalações ativadas no incidente, como posto de comando, base, heliponto, abrigo e centro de distribuição.

### Papel estrutural:

materializa a infraestrutura operacional do incidente.

## 10.12. `incidentes_comunicacoes`

Tabela dependente do incidente e do período.

### Finalidade:

registrar canais, frequências, finalidades, instituições vinculadas, usuários autorizados e falhas registradas.

### Papel estrutural:

materializa a comunicação integrada.

## 10.13. `incidentes_seguranca`

Tabela dependente do incidente e do período.

### Finalidade:

registrar riscos operacionais, equipes expostas, medidas de controle, EPIs, restrições e interdições.

### Papel estrutural:

materializa a segurança operacional.

## 10.14. `incidentes_informacao_publica`

Tabela dependente do incidente.

### Finalidade:

registrar comunicados oficiais, público-alvo, mensagem, canal, aprovação, porta-voz e tratamento de rumores.

### Papel estrutural:

materializa a informação pública.

## 10.15. `incidentes_ligacao_interinstitucional`

Tabela dependente do incidente.

### Finalidade:

registrar instituições participantes, representantes, recursos ofertados, limitações e solicitações pendentes.

### Papel estrutural:

materializa a coordenação interinstitucional.

## 10.16. `incidentes_financas`

Tabela dependente do incidente e, em geral, do período.

### Finalidade:

registrar despesas, valores estimados e realizados, fonte de recurso, contratação emergencial e documentação comprobatória.

### Papel estrutural:

materializa administração e finanças do incidente.

## 10.17. `incidentes_periodos_operacionais`

Tabela dependente do incidente.

### Finalidade:

registrar períodos operacionais, objetivos do período, recursos principais, briefing, PAI e situação ao encerramento.

### Papel estrutural:

materializa a organização temporal da operação.

## 10.18. `incidentes_registros_operacionais`

Tabela dependente do incidente e do período.

### Finalidade:

registrar diário do incidente, decisões, atualizações, evidências e encaminhamentos.

### Papel estrutural:

materializa a rastreabilidade operacional do evento.

## 10.19. `incidentes_desmobilizacao`

Tabela dependente do incidente.

### Finalidade:

registrar critérios de desmobilização, recursos liberados, pendências, lições iniciais e situação final.

### Papel estrutural:

materializa o encerramento progressivo da resposta.

---

# 11. Tabelas de relatórios, documentos, mapas e arquivos

Essas tabelas apoiam anexação, persistência documental e artefatos do sistema.

## 11.1. `anexos`

Tabela transversal central de arquivos.

### Finalidade:

registrar metadados de arquivos anexados ou gerados pelo sistema, independentemente do domínio.

### Papel estrutural:

centraliza o controle documental.

## 11.2. `documentos_vinculados`

Tabela associativa entre anexos e entidades de negócio.

### Finalidade:

permitir vincular anexos a plano, incidente, fatura, contrato, revisão, registro operacional ou outra entidade.

### Papel estrutural:

dá flexibilidade e evita duplicação de colunas de arquivo em várias tabelas.

## 11.3. `relatorios_gerados`

Tabela opcional.

### Finalidade:

registrar relatórios gerados, tipo, contexto, responsável, data e artefato associado.

### Papel estrutural:

apoia rastreabilidade documental e histórico de geração.

## 11.4. `exportacoes`

Tabela opcional.

### Finalidade:

registrar exportações PDF, CSV e planilhas realizadas no sistema, especialmente quando envolverem dados sensíveis.

### Papel estrutural:

apoia governança e rastreabilidade de saída de dados.

## 11.5. `mapas_operacionais`

Tabela opcional, recomendada se metadados do mapa forem persistidos.

### Finalidade:

registrar contextos cartográficos, camadas salvas, snapshots operacionais ou artefatos geográficos gerados.

### Papel estrutural:

apoia o módulo de mapa quando houver persistência específica.

---

# 12. Tabelas de auditoria e conformidade

Esse domínio é transversal e essencial para governança do produto.

## 12.1. `logs_auditoria`

Tabela central de auditoria funcional.

### Finalidade:

registrar ações auditáveis do sistema, como acesso, alteração, exclusão, exportação, encerramento e liberação de módulo.

### Papel estrutural:

é a principal tabela de rastreabilidade funcional.

## 12.2. `logs_acesso`

Tabela opcional, complementar à auditoria funcional.

### Finalidade:

registrar login, logout, falhas de autenticação, IP, navegador, dispositivo e contexto de sessão.

### Papel estrutural:

apoia segurança e observabilidade de acesso.

## 12.3. `termos_aceites`

Tabela de conformidade legal.

### Finalidade:

registrar aceite de termos de uso, política de privacidade e LGPD, inclusive versões, data, IP e responsável.

### Papel estrutural:

materializa conformidade jurídica da plataforma.

## 12.4. `eventos_sensiveis`

Tabela opcional, recomendada se houver trilha reforçada.

### Finalidade:

registrar ações de alto impacto institucional, contratual ou operacional com classificação especial.

### Papel estrutural:

apoia auditoria reforçada para eventos críticos.

---

# 13. Tabelas auxiliares e catálogos recomendados

Dependendo do grau de normalização adotado, o sistema pode incluir tabelas auxiliares para padronização.

## Exemplos recomendados:

- `tipos_instituicao`
- `tipos_unidade`
- `tipos_recurso`
- `tipos_instalacao`
- `tipos_registro_operacional`
- `status_assinatura`
- `status_usuario`
- `status_incidente`
- `status_plancon`
- `tipos_despesa`
- `tipos_comunicado`

### Observação técnica

Essas tabelas são úteis quando o sistema demandar governança alta sobre catálogos. Em uma fase inicial, parte desses domínios pode ser resolvida por enums controlados em nível de aplicação, desde que sem perda de consistência.

---

# 14. Inventário consolidado das tabelas por domínio

## 14.1. Domínio comercial / SaaS

- `contas`
- `planos_assinatura`
- `assinaturas`
- `faturas`
- `modulos`
- `assinatura_modulos`
- `leads_comerciais`

## 14.2. Domínio institucional

- `orgaos`
- `unidades`
- `unidades_hierarquia`

## 14.3. Domínio de identidade e acesso

- `usuarios`
- `perfis`
- `usuarios_perfis`
- `permissoes`
- `perfis_permissoes`
- `sessoes_usuario`
- `autenticacao_2fator`

## 14.4. Domínio PLANCON

- `plancons`
- `plancon_versoes`
- `plancon_territorios`
- `plancon_riscos`
- `plancon_cenarios`
- `plancon_niveis_ativacao`
- `plancon_governanca`
- `plancon_recursos`
- `plancon_monitoramento_comunicacao`
- `plancon_procedimentos`
- `plancon_rotas_abrigos`
- `plancon_assistencia`
- `plancon_simulados`
- `plancon_revisoes`
- `plancon_anexos`

## 14.5. Núcleo CSI/SCO do PLANCON

- `plancon_estruturas_operacionais`
- `plancon_funcoes_equipes`
- `plancon_instalacoes`
- `plancon_periodos_operacionais`
- `plancon_registros_operacionais`

## 14.6. Domínio Incidentes / SCI-SCO

- `incidentes`
- `incidentes_briefing`
- `incidentes_comando`
- `incidentes_staff_comando`
- `incidentes_staff_geral`
- `incidentes_objetivos`
- `incidentes_estrategias_pai`
- `incidentes_operacoes_campo`
- `incidentes_planejamento_situacao`
- `incidentes_recursos`
- `incidentes_instalacoes`
- `incidentes_comunicacoes`
- `incidentes_seguranca`
- `incidentes_informacao_publica`
- `incidentes_ligacao_interinstitucional`
- `incidentes_financas`
- `incidentes_periodos_operacionais`
- `incidentes_registros_operacionais`
- `incidentes_desmobilizacao`

## 14.7. Relatórios, arquivos e documentos

- `anexos`
- `documentos_vinculados`
- `relatorios_gerados`
- `exportacoes`
- `mapas_operacionais`

## 14.8. Auditoria e conformidade

- `logs_auditoria`
- `logs_acesso`
- `termos_aceites`
- `eventos_sensiveis`

---

# 15. Observações estratégicas sobre o conjunto de tabelas

Há quatro decisões estruturais corretas no inventário acima.

A primeira é não centralizar tudo em usuário. O eixo comercial é a **conta**, e o eixo operacional é o **órgão**. Isso está alinhado ao modelo SaaS institucional do SIGERD.

A segunda é não comprimir o PLANCON em uma única tabela. Como o plano foi concebido em blocos, a estrutura relacional precisa refletir essa modularidade.

A terceira é não reduzir o incidente a um cadastro simples. O gerenciamento do desastre envolve comando, objetivos, períodos, registros, recursos e desmobilização, e isso exige múltiplas tabelas.

A quarta é prever um domínio documental e de auditoria transversal. Sem isso, o sistema perde rastreabilidade e governança.

---

# 16. Conclusão técnica

A estrutura de tabelas do SIGERD precisa refletir a complexidade real do produto sem cair em duas armadilhas: simplificação excessiva ou fragmentação artificial. O inventário apresentado aqui estabelece uma base sólida porque:

- separa claramente SaaS, instituição, PLANCON e incidente;
- preserva a diferença entre contratante e operador;
- estrutura o núcleo operacional do plano e do desastre;
- prevê auditoria, conformidade, anexos e exportações;
- prepara o terreno para modelagem relacional detalhada.

Com esse documento, o projeto já tem base suficiente para avançar com precisão para o detalhamento fino do banco.