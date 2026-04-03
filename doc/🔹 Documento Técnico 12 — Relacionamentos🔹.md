**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Relacionamentos  
**Objetivo:** definir os relacionamentos entre as tabelas do sistema, estabelecendo a lógica de vínculo entre os domínios comercial, institucional, operacional, documental e de auditoria.

## 1. Finalidade do documento

Este documento estabelece a malha relacional do banco de dados do SIGERD. Seu objetivo é descrever, de forma organizada, como as tabelas do sistema se conectam, quais entidades são centrais, quais são dependentes, quais são associativas e quais relações precisam ser preservadas para manter integridade funcional e rastreabilidade.

O foco aqui não é apenas dizer que uma tabela tem uma chave estrangeira para outra. O foco é explicar a lógica do relacionamento e a razão estrutural pela qual ele existe.

Este documento serve de base direta para:

- chaves primárias e estrangeiras;
- dicionário de dados;
- índices e constraints;
- modelagem física final.

## 2. Princípios relacionais do SIGERD

Os relacionamentos do SIGERD deverão obedecer aos seguintes princípios:

1. a conta contratante é a raiz do domínio comercial;
2. o órgão é a raiz do domínio institucional operacional;
3. usuários existem dentro de contexto institucional definido;
4. o plano de contingência é independente do incidente, embora possa se relacionar a ele;
5. o incidente é a raiz do domínio de resposta operacional;
6. estruturas operacionais devem manter encadeamento relacional entre estrutura, equipe, instalação, período e registro;
7. auditoria, anexos e conformidade devem atravessar múltiplos domínios sem perder referência de origem;
8. relações N:N devem ser resolvidas por tabelas associativas explícitas;
9. vínculos institucionais devem ser preservados em tabelas operacionais críticas;
10. relatórios e rastreabilidade dependem de relacionamentos claros e consistentes.

## 3. Visão macro dos relacionamentos

A malha relacional do SIGERD pode ser compreendida em cinco eixos principais:

- eixo comercial: conta → assinatura → fatura / módulos;
- eixo institucional: conta → órgão → unidade → usuário;
- eixo de gestão do risco: órgão/unidade → plancon → blocos do plano;
- eixo de gerenciamento do desastre: órgão/unidade → incidente → blocos do incidente;
- eixo transversal: anexos, auditoria, conformidade, exportações e documentos vinculados.

## 4. Tipos de relacionamento adotados

No SIGERD, os relacionamentos predominantes são:

- **1:1**, quando uma entidade possui complemento estrutural exclusivo;
- **1:N**, quando uma entidade principal possui várias dependências;
- **N:N**, quando há associação múltipla entre entidades e é necessária tabela intermediária.

### Diretriz geral

A maior parte da modelagem do SIGERD é naturalmente **1:N**. Relações **N:N** devem ser usadas apenas quando realmente representarem múltiplas associações legítimas, como usuários e perfis, ou assinaturas e módulos.

---

# 5. Relacionamentos do domínio comercial / SaaS

## 5.1. `contas` → `orgaos`

**Tipo:** 1:N

### Lógica:

uma conta contratante pode possuir um ou vários órgãos/instituições vinculados, conforme o plano contratado. Um órgão pertence a uma única conta contratante.

### Justificativa:

isso preserva a separação entre entidade comercial e entidade operacional.

## 5.2. `contas` → `assinaturas`

**Tipo:** 1:N, com regra operacional típica de 1:1 ativa por vez

### Lógica:

uma conta pode ter histórico de várias assinaturas ao longo do tempo, mas normalmente apenas uma assinatura ativa principal por ciclo contratual.

### Justificativa:

essa modelagem favorece histórico contratual sem perder a noção de assinatura vigente.

## 5.3. `planos_assinatura` → `assinaturas`

**Tipo:** 1:N

### Lógica:

um plano pode estar vinculado a muitas assinaturas; cada assinatura se refere a um único plano comercial base.

## 5.4. `assinaturas` → `faturas`

**Tipo:** 1:N

### Lógica:

uma assinatura pode gerar várias faturas ao longo de sua vigência. Cada fatura pertence a uma única assinatura.

## 5.5. `assinaturas` → `assinatura_modulos`

**Tipo:** 1:N

### Lógica:

uma assinatura pode possuir múltiplos módulos liberados, limitados ou bloqueados. Cada registro de assinatura_módulo pertence a uma assinatura específica.

## 5.6. `modulos` → `assinatura_modulos`

**Tipo:** 1:N

### Lógica:

um módulo pode estar vinculado a várias assinaturas. A tabela associativa resolve o vínculo entre assinatura e módulo.

## 5.7. `contas` → `termos_aceites`

**Tipo:** 1:N

### Lógica:

uma conta pode registrar múltiplos aceites legais ao longo do tempo, seja por novas versões de termo, política ou LGPD.

## 5.8. `contas` → `leads_comerciais`

**Tipo:** 1:N opcional ou sem vínculo obrigatório

### Lógica:

quando o lead já estiver convertido ou vinculado à conta, pode existir relação. Em estágio pré-contratual, o lead pode existir sem conta formal.

---

# 6. Relacionamentos do domínio institucional

## 6.1. `orgaos` → `unidades`

**Tipo:** 1:N

### Lógica:

um órgão pode possuir várias unidades, regionais, bases ou subestruturas. Cada unidade pertence a um único órgão.

## 6.2. `unidades` → `unidades_hierarquia`

**Tipo:** 1:N ou autorrelacionamento estruturado

### Lógica:

uma unidade pode estar vinculada a múltiplas relações hierárquicas, dependendo do modelo adotado. Se a modelagem for simplificada, basta uma referência direta `unidade_superior_id` em `unidades`.

### Diretriz:

não usar tabela de hierarquia separada se a complexidade real não justificar.

## 6.3. `contas` → `usuarios`

**Tipo:** 1:N

### Lógica:

todos os usuários pertencem a uma conta contratante, ainda que operem em diferentes órgãos e unidades.

## 6.4. `orgaos` → `usuarios`

**Tipo:** 1:N

### Lógica:

um órgão pode possuir vários usuários. Cada usuário está vinculado a um único órgão principal no contexto-base de cadastro.

## 6.5. `unidades` → `usuarios`

**Tipo:** 1:N

### Lógica:

uma unidade pode possuir vários usuários vinculados. O vínculo de unidade pode ser obrigatório ou facultativo conforme a operação institucional adotada.

### Justificativa:

isso permite escopo operacional granular por sede, regional, COMPDEC ou base.

---

# 7. Relacionamentos do domínio de identidade e acesso

## 7.1. `usuarios` → `usuarios_perfis`

**Tipo:** 1:N

### Lógica:

um usuário pode possuir múltiplos vínculos de perfil.

## 7.2. `perfis` → `usuarios_perfis`

**Tipo:** 1:N

### Lógica:

um perfil pode estar associado a muitos usuários.

## 7.3. `usuarios` ↔ `perfis`

**Tipo efetivo:** N:N  
**Resolvido por:** `usuarios_perfis`

### Lógica:

um usuário pode ter vários perfis, e um perfil pode ser atribuído a vários usuários.

## 7.4. `perfis` → `perfis_permissoes`

**Tipo:** 1:N

### Lógica:

um perfil pode ter várias permissões associadas.

## 7.5. `permissoes` → `perfis_permissoes`

**Tipo:** 1:N

### Lógica:

uma permissão pode estar vinculada a vários perfis.

## 7.6. `perfis` ↔ `permissoes`

**Tipo efetivo:** N:N  
**Resolvido por:** `perfis_permissoes`

## 7.7. `usuarios` → `sessoes_usuario`

**Tipo:** 1:N

### Lógica:

um usuário pode possuir várias sessões ao longo do tempo ou simultaneamente, dependendo da política de sessão.

## 7.8. `usuarios` → `autenticacao_2fator`

**Tipo:** 1:1 ou 1:N histórico

### Lógica:

cada usuário pode possuir uma configuração ativa de 2FA. Se houver histórico de métodos ou reconfigurações, a relação pode ser tratada como 1:N.

---

# 8. Relacionamentos do domínio PLANCON

## 8.1. `contas` → `plancons`

**Tipo:** 1:N

### Lógica:

uma conta pode manter vários planos de contingência, especialmente em contextos com múltiplos órgãos ou unidades.

## 8.2. `orgaos` → `plancons`

**Tipo:** 1:N

### Lógica:

um órgão pode possuir múltiplos planos de contingência.

## 8.3. `unidades` → `plancons`

**Tipo:** 1:N opcional

### Lógica:

uma unidade específica pode possuir ou operar planos próprios, dependendo da estrutura institucional adotada.

## 8.4. `plancons` → `plancon_versoes`

**Tipo:** 1:N

### Lógica:

um plano pode ter várias versões ao longo do tempo.

## 8.5. `plancons` → `plancon_territorios`

**Tipo:** 1:N

### Lógica:

um plano pode abranger um ou mais territórios ou recortes territoriais.

## 8.6. `plancons` → `plancon_riscos`

**Tipo:** 1:N

### Lógica:

um plano pode registrar múltiplos riscos analisados.

## 8.7. `plancons` → `plancon_cenarios`

**Tipo:** 1:N

### Lógica:

um plano pode conter múltiplos cenários de desastre.

## 8.8. `plancons` → `plancon_niveis_ativacao`

**Tipo:** 1:N

### Lógica:

um plano possui vários níveis de ativação operacional.

## 8.9. `plancons` → `plancon_governanca`

**Tipo:** 1:N

### Lógica:

um plano pode registrar vários órgãos, responsáveis e competências relacionados à governança.

## 8.10. `plancons` → `plancon_recursos`

**Tipo:** 1:N

### Lógica:

um plano pode cadastrar diversos recursos disponíveis.

## 8.11. `plancons` → `plancon_monitoramento_comunicacao`

**Tipo:** 1:N

### Lógica:

um plano pode possuir diversas fontes, indicadores e fluxos de comunicação.

## 8.12. `plancons` → `plancon_procedimentos`

**Tipo:** 1:N

### Lógica:

um plano pode conter vários procedimentos operacionais.

## 8.13. `plancons` → `plancon_rotas_abrigos`

**Tipo:** 1:N

### Lógica:

um plano pode registrar múltiplas rotas, pontos de apoio e abrigos.

## 8.14. `plancons` → `plancon_assistencia`

**Tipo:** 1:N

### Lógica:

um plano pode conter múltiplos fluxos de assistência.

## 8.15. `plancons` → `plancon_simulados`

**Tipo:** 1:N

### Lógica:

um plano pode registrar vários simulados e capacitações.

## 8.16. `plancons` → `plancon_revisoes`

**Tipo:** 1:N

### Lógica:

um plano pode ter várias revisões complementares, ainda que exista também a lógica de versões.

## 8.17. `plancons` → `plancon_anexos`

**Tipo:** 1:N

### Lógica:

um plano pode possuir vários anexos operacionais.

---

# 9. Relacionamentos do núcleo operacional do PLANCON — CSI/SCO

O documento-base estabelece a lógica correta como: **estrutura → pessoas → instalações → períodos → registros**. A modelagem relacional deve refletir exatamente isso.

## 9.1. `plancons` → `plancon_estruturas_operacionais`

**Tipo:** 1:N

### Lógica:

um plano pode possuir zero, uma ou várias estruturas operacionais vinculadas, inclusive em revisões distintas ou contextos diversos.

## 9.2. `plancon_estruturas_operacionais` → `plancon_funcoes_equipes`

**Tipo:** 1:N

### Lógica:

uma estrutura operacional possui várias funções e equipes cadastradas.

## 9.3. `plancon_estruturas_operacionais` → `plancon_instalacoes`

**Tipo:** 1:N

### Lógica:

uma estrutura operacional pode operar em várias instalações.

## 9.4. `plancon_estruturas_operacionais` → `plancon_periodos_operacionais`

**Tipo:** 1:N

### Lógica:

uma estrutura operacional pode ser organizada em múltiplos períodos operacionais.

## 9.5. `plancon_periodos_operacionais` → `plancon_registros_operacionais`

**Tipo:** 1:N

### Lógica:

um período operacional pode possuir vários registros no diário do comando.

## 9.6. `plancon_estruturas_operacionais` → `plancon_registros_operacionais`

**Tipo:** 1:N

### Lógica:

mesmo com vínculo ao período, o registro também precisa manter vínculo direto com a estrutura, para rastreabilidade e consultas estruturais.

## 9.7. `plancon_funcoes_equipes` ↔ `usuarios`

**Tipo:** 1:N opcional por referência institucional

### Lógica:

uma função/equipe pode referenciar um usuário do sistema quando o responsável for operador cadastrado. Porém a modelagem deve permitir nomes externos ou institucionais, sem depender exclusivamente da tabela `usuarios`.

### Diretriz:

usar referência opcional a `usuario_id`, sem obrigar o vínculo em todos os casos.

---

# 10. Relacionamentos do domínio de gerenciamento do desastre — Incidentes / SCI-SCO

## 10.1. `contas` → `incidentes`

**Tipo:** 1:N

### Lógica:

uma conta pode operar múltiplos incidentes.

## 10.2. `orgaos` → `incidentes`

**Tipo:** 1:N

### Lógica:

um órgão pode abrir ou operar múltiplos incidentes.

## 10.3. `unidades` → `incidentes`

**Tipo:** 1:N opcional

### Lógica:

uma unidade pode ser responsável por múltiplos incidentes, quando o contexto operacional exigir granularidade por unidade.

## 10.4. `plancons` → `incidentes`

**Tipo:** 1:N opcional

### Lógica:

um incidente pode referenciar um plano de contingência vinculado, mas esse vínculo não é obrigatório para a existência do incidente.

## 10.5. `plancon_cenarios` → `incidentes`

**Tipo:** 1:N opcional

### Lógica:

um incidente pode estar relacionado a um cenário previamente definido no plano.

## 10.6. `incidentes` → `incidentes_briefing`

**Tipo:** 1:1 ou 1:N controlado por versão

### Lógica:

o incidente possui um briefing inicial principal. Se houver histórico de atualizações independentes, pode-se tratar como 1:N com um registro vigente.

## 10.7. `incidentes` → `incidentes_comando`

**Tipo:** 1:N histórico ou 1:1 vigente

### Lógica:

um incidente possui uma estrutura de comando vigente, mas pode ter histórico de mudanças ou transferências de comando.

## 10.8. `incidentes` → `incidentes_staff_comando`

**Tipo:** 1:N

### Lógica:

um incidente pode possuir vários membros do staff do comando.

## 10.9. `incidentes` → `incidentes_staff_geral`

**Tipo:** 1:N

### Lógica:

um incidente pode possuir várias seções funcionais e chefias associadas.

## 10.10. `incidentes` → `incidentes_periodos_operacionais`

**Tipo:** 1:N

### Lógica:

um incidente pode ser gerenciado em múltiplos períodos operacionais.

## 10.11. `incidentes_periodos_operacionais` → `incidentes_objetivos`

**Tipo:** 1:N

### Lógica:

um período operacional pode conter vários objetivos.

## 10.12. `incidentes_periodos_operacionais` → `incidentes_estrategias_pai`

**Tipo:** 1:N

### Lógica:

um período operacional pode ter uma ou mais versões de estratégia/PAI.

## 10.13. `incidentes_periodos_operacionais` → `incidentes_operacoes_campo`

**Tipo:** 1:N

### Lógica:

um período operacional pode conter diversas operações de campo.

## 10.14. `incidentes_periodos_operacionais` → `incidentes_planejamento_situacao`

**Tipo:** 1:N

### Lógica:

um período pode possuir registros consolidados de situação e prognóstico.

## 10.15. `incidentes` → `incidentes_recursos`

**Tipo:** 1:N

### Lógica:

um incidente pode mobilizar muitos recursos.

## 10.16. `incidentes` → `incidentes_instalacoes`

**Tipo:** 1:N

### Lógica:

um incidente pode possuir várias instalações ativadas.

## 10.17. `incidentes_periodos_operacionais` → `incidentes_comunicacoes`

**Tipo:** 1:N

### Lógica:

as comunicações integradas podem variar por período operacional.

## 10.18. `incidentes_periodos_operacionais` → `incidentes_seguranca`

**Tipo:** 1:N

### Lógica:

os riscos e medidas de segurança podem ser definidos e revisados por período.

## 10.19. `incidentes` → `incidentes_informacao_publica`

**Tipo:** 1:N

### Lógica:

um incidente pode gerar vários comunicados ou mensagens oficiais.

## 10.20. `incidentes` → `incidentes_ligacao_interinstitucional`

**Tipo:** 1:N

### Lógica:

um incidente pode envolver múltiplas instituições participantes.

## 10.21. `incidentes_periodos_operacionais` → `incidentes_financas`

**Tipo:** 1:N

### Lógica:

despesas e registros financeiros podem ser vinculados a um período específico, quando necessário.

## 10.22. `incidentes` → `incidentes_registros_operacionais`

**Tipo:** 1:N

### Lógica:

o incidente mantém vários registros no diário operacional.

## 10.23. `incidentes_periodos_operacionais` → `incidentes_registros_operacionais`

**Tipo:** 1:N

### Lógica:

além do vínculo ao incidente, o registro deve apontar para o período operacional correspondente, quando houver.

## 10.24. `incidentes` → `incidentes_desmobilizacao`

**Tipo:** 1:N ou 1:1 progressivo

### Lógica:

normalmente o incidente terá um processo principal de desmobilização, mas pode haver histórico de etapas ou revisões.

---

# 11. Relacionamentos entre recursos institucionais e recursos do incidente

## 11.1. `plancon_recursos` → `incidentes_recursos`

**Tipo:** 1:N opcional por referência de origem

### Lógica:

um recurso previamente catalogado no PLANCON pode servir de origem para um ou vários recursos efetivamente mobilizados no incidente.

### Diretriz:

manter vínculo opcional de origem, sem obrigar que todo recurso mobilizado exista previamente no plano.

## 11.2. `plancon_rotas_abrigos` → `incidentes_instalacoes`

**Tipo:** 1:N opcional

### Lógica:

um abrigo ou ponto de apoio previsto no plano pode ser efetivamente ativado como instalação do incidente.

## 11.3. `plancon_procedimentos` → `incidentes_estrategias_pai` ou `incidentes_operacoes_campo`

**Tipo:** 1:N opcional de referência funcional

### Lógica:

procedimentos previstos no plano podem inspirar estratégias e ações do incidente, sem que isso elimine a autonomia do módulo de resposta.

---

# 12. Relacionamentos do domínio documental e de arquivos

## 12.1. `anexos` → `documentos_vinculados`

**Tipo:** 1:N

### Lógica:

um anexo pode ser vinculado a uma ou várias entidades, dependendo da política documental. Se o sistema exigir exclusividade, a regra pode ser restringida funcionalmente.

## 12.2. `documentos_vinculados` ↔ entidades de negócio

**Tipo:** polimórfico controlado ou múltiplos campos de referência

### Lógica:

o documento vinculado pode apontar para diferentes entidades, como:

- plancon;
- revisão do plano;
- incidente;
- período operacional;
- registro operacional;
- fatura;
- contrato;
- estrutura operacional.

### Diretriz:

para o SIGERD, a melhor opção tende a ser uma tabela de vínculo documental com identificação clara do tipo de entidade vinculada e do registro alvo, desde que isso seja bem controlado.

## 12.3. `usuarios` → `anexos`

**Tipo:** 1:N

### Lógica:

um usuário pode ser o responsável pelo envio ou criação lógica de vários anexos.

## 12.4. `relatorios_gerados` → `usuarios`

**Tipo:** N:1

### Lógica:

um usuário pode gerar vários relatórios.

## 12.5. `exportacoes` → `usuarios`

**Tipo:** N:1

### Lógica:

um usuário pode realizar múltiplas exportações.

## 12.6. `incidentes_registros_operacionais` → `anexos`

**Tipo:** 1:N via `documentos_vinculados`

### Lógica:

um registro operacional pode conter múltiplas evidências anexas.

## 12.7. `plancon_anexos` ↔ `anexos`

**Tipo:** 1:1 lógico ou 1:N com metadados próprios

### Lógica:

`plancon_anexos` pode funcionar como especialização documental do plano, enquanto `anexos` guarda o artefato físico/metadados gerais.

---

# 13. Relacionamentos do domínio de auditoria e conformidade

## 13.1. `usuarios` → `logs_auditoria`

**Tipo:** 1:N

### Lógica:

um usuário pode gerar muitos eventos de auditoria.

## 13.2. `contas` → `logs_auditoria`

**Tipo:** 1:N

### Lógica:

vários eventos podem ser associados a uma conta contratante.

## 13.3. `orgaos` → `logs_auditoria`

**Tipo:** 1:N

### Lógica:

eventos auditáveis podem estar associados a um órgão específico.

## 13.4. `unidades` → `logs_auditoria`

**Tipo:** 1:N opcional

### Lógica:

quando aplicável, o evento auditado pode apontar para uma unidade específica.

## 13.5. `usuarios` → `logs_acesso`

**Tipo:** 1:N

### Lógica:

um usuário pode ter múltiplos eventos de login/logout/tentativa.

## 13.6. `contas` → `termos_aceites`

**Tipo:** 1:N

### Lógica:

uma conta pode aceitar sucessivas versões de termos, políticas e bases legais.

## 13.7. `usuarios` → `termos_aceites`

**Tipo:** 1:N opcional

### Lógica:

além do vínculo com a conta, o aceite pode apontar para o usuário responsável pelo ato.

## 13.8. `usuarios` → `eventos_sensiveis`

**Tipo:** 1:N

### Lógica:

um usuário pode gerar múltiplos eventos classificados como sensíveis.

---

# 14. Relacionamentos transversais por contexto institucional

Em tabelas operacionais críticas, é recomendável manter relacionamento direto também com os eixos institucionais principais:

- `conta_id`
- `orgao_id`
- `unidade_id`

### Aplicação recomendada

Esse vínculo deve existir em tabelas como:

- `plancons`
- `incidentes`
- `logs_auditoria`
- `relatorios_gerados`
- `exportacoes`
- `anexos`, quando fizer sentido
- `plancon_estruturas_operacionais`
- `incidentes_periodos_operacionais`

### Justificativa

Isso melhora:

- filtros;
- segurança por escopo;
- auditoria;
- relatórios multiunidade;
- manutenção da trilha institucional.

---

# 15. Relacionamentos hierárquicos e autorrelacionamentos

## 15.1. `unidades` → `unidades`

**Tipo:** autorrelacionamento 1:N, quando usado campo `unidade_superior_id`

### Lógica:

uma unidade pode ser superior a várias unidades subordinadas.

## 15.2. `plancon_versoes` → `plancon_versoes`

**Tipo:** autorrelacionamento opcional

### Lógica:

se o modelo exigir vínculo explícito entre versão derivada e versão anterior, pode haver campo de referência à versão predecessora.

## 15.3. `incidentes_comando` → `incidentes_comando`

**Tipo:** autorrelacionamento opcional histórico

### Lógica:

se houver necessidade de encadear transferências de comando de forma histórica, um registro pode apontar ao anterior.

---

# 16. Relações N:N relevantes do sistema

As principais relações N:N do SIGERD são:

### 16.1. `usuarios` ↔ `perfis`

Resolvida por `usuarios_perfis`.

### 16.2. `perfis` ↔ `permissoes`

Resolvida por `perfis_permissoes`.

### 16.3. `assinaturas` ↔ `modulos`

Resolvida por `assinatura_modulos`.

### 16.4. `incidentes` ↔ `orgaos` participantes

Pode ser resolvida parcialmente por `incidentes_ligacao_interinstitucional`, dependendo do grau de formalização institucional desejado.

### Observação crítica

Evitar criar relações N:N desnecessárias em módulos como PLANCON e Incidentes quando a relação real é claramente 1:N. Isso apenas complica a modelagem sem ganho funcional.

---

# 17. Relações obrigatórias e opcionais

## Relações tipicamente obrigatórias

- órgão pertence a uma conta;
- unidade pertence a um órgão;
- usuário pertence a uma conta;
- assinatura pertence a uma conta e a um plano;
- fatura pertence a uma assinatura;
- plancon pertence a uma conta e a um órgão;
- incidente pertence a uma conta e a um órgão;
- período operacional pertence a uma estrutura ou incidente;
- registro operacional pertence a uma estrutura/período ou incidente/período.

## Relações tipicamente opcionais

- unidade vinculada ao usuário;
- vínculo entre incidente e plancon;
- vínculo entre incidente e cenário do plancon;
- vínculo entre recurso do incidente e recurso previamente cadastrado;
- vínculo entre registros e anexos;
- vínculo entre função operacional e usuário cadastrado;
- vínculo de 2FA separado por tabela;
- vínculo de mapas persistidos.

---

# 18. Matriz resumida dos relacionamentos centrais

|Entidade principal|Relaciona-se com|Tipo|
|---|---|---|
|contas|orgaos|1:N|
|contas|assinaturas|1:N|
|planos_assinatura|assinaturas|1:N|
|assinaturas|faturas|1:N|
|assinaturas|modulos|N:N|
|orgaos|unidades|1:N|
|contas|usuarios|1:N|
|orgaos|usuarios|1:N|
|usuarios|perfis|N:N|
|perfis|permissoes|N:N|
|plancons|plancon_versoes|1:N|
|plancons|plancon_riscos|1:N|
|plancons|plancon_cenarios|1:N|
|plancons|plancon_estruturas_operacionais|1:N|
|plancon_estruturas_operacionais|plancon_funcoes_equipes|1:N|
|plancon_estruturas_operacionais|plancon_instalacoes|1:N|
|plancon_estruturas_operacionais|plancon_periodos_operacionais|1:N|
|plancon_periodos_operacionais|plancon_registros_operacionais|1:N|
|incidentes|incidentes_briefing|1:1 ou 1:N|
|incidentes|incidentes_comando|1:1 ou 1:N|
|incidentes|incidentes_periodos_operacionais|1:N|
|incidentes_periodos_operacionais|incidentes_objetivos|1:N|
|incidentes_periodos_operacionais|incidentes_estrategias_pai|1:N|
|incidentes|incidentes_recursos|1:N|
|incidentes|incidentes_instalacoes|1:N|
|incidentes|incidentes_registros_operacionais|1:N|
|incidentes_periodos_operacionais|incidentes_registros_operacionais|1:N|
|usuarios|logs_auditoria|1:N|
|contas|termos_aceites|1:N|
|anexos|documentos_vinculados|1:N|

---

# 19. Riscos de relacionamento que devem ser evitados

Os principais erros de relacionamento a evitar no SIGERD são:

1. vincular tudo diretamente ao usuário e ignorar conta/órgão;
2. fundir plancon e incidente como se fossem a mesma entidade;
3. deixar registros operacionais sem vínculo com período ou estrutura;
4. perder o vínculo institucional em tabelas sensíveis;
5. criar N:N desnecessárias onde bastaria 1:N;
6. não prever vínculo documental estruturado;
7. deixar auditoria sem referência de conta, órgão ou usuário;
8. não diferenciar assinatura ativa de histórico contratual;
9. vincular funções operacionais exclusivamente a usuários do sistema, impedindo atores externos;
10. eliminar vínculos opcionais importantes por excesso de rigidez.

---

# 20. Conclusão técnica

A malha de relacionamentos do SIGERD confirma a natureza modular e institucional do sistema. Os vínculos definidos aqui mostram que o projeto não pode ser modelado como um conjunto de CRUDs independentes. Há uma lógica relacional clara e necessária:

- a **conta** sustenta a dimensão comercial;
- o **órgão** sustenta a dimensão institucional;
- o **plancon** sustenta a gestão do risco;
- o **incidente** sustenta a gestão da resposta;
- a **auditoria**, os **anexos** e a **conformidade** atravessam toda a plataforma;
- os núcleos de comando, tanto no plano quanto no incidente, precisam de cadeia relacional própria.

Esse desenho é coerente com o escopo descrito no material-base do SIGERD e prepara corretamente o próximo nível de detalhamento do banco.