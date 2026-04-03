**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Chaves Primárias e Estrangeiras  
**Objetivo:** definir as chaves primárias e estrangeiras das tabelas do sistema, estabelecendo a identidade técnica dos registros e os vínculos formais entre os domínios do banco de dados.

## 1. Finalidade do documento

Este documento estabelece a estrutura de chaves primárias e estrangeiras do SIGERD, com o objetivo de garantir:

- identificação única de cada entidade do banco;
- integridade referencial entre tabelas;
- rastreabilidade dos vínculos institucionais e operacionais;
- consistência entre o domínio comercial, institucional e operacional;
- base técnica correta para constraints, índices e modelagem física final.

O foco aqui é formalizar como os relacionamentos definidos anteriormente serão sustentados no banco por meio de PKs e FKs explícitas.

## 2. Princípios para definição de PKs e FKs

As chaves do SIGERD deverão seguir os seguintes princípios:

1. toda tabela principal deve possuir chave primária simples e estável;
2. tabelas associativas devem possuir PK própria ou PK composta, conforme o caso;
3. FKs devem ter nomenclatura clara e sem ambiguidade;
4. entidades operacionais críticas devem manter vínculo institucional mínimo;
5. FKs opcionais devem ser usadas apenas quando o vínculo realmente puder ser ausente;
6. relações históricas ou de auditoria devem preservar a referência do contexto de origem;
7. o banco não deve depender de campos textuais como substitutos de relacionamentos formais.

## 3. Convenção recomendada de nomenclatura

### Chaves primárias

A recomendação mais consistente para o SIGERD é:

- usar `id` como PK técnica em cada tabela, com identidade própria da linha;
- usar nomes de FKs no formato `<entidade>_id`.

### Exemplos:

- `id`
- `conta_id`
- `orgao_id`
- `unidade_id`
- `usuario_id`
- `plancon_id`
- `incidente_id`
- `assinatura_id`

### Justificativa

Essa convenção reduz redundância, simplifica joins e mantém clareza sem gerar nomes inflados como `id_conta_contratante_sistema`.

## 4. Estratégia geral de chave primária

A arquitetura recomendada para o SIGERD é:

- PK simples do tipo inteiro auto incrementável ou bigint auto incrementável;
- uso de identificadores de negócio adicionais quando necessário, mas não como PK principal;
- tabelas associativas podendo usar:
    - PK própria (`id`) + unique composto; ou
    - PK composta, quando a relação for puramente associativa e sem histórico próprio.

### Recomendação prática

Para o SIGERD, a opção mais robusta é usar `id` em quase todas as tabelas, inclusive associativas com comportamento operacional, porque isso facilita auditoria, anexos, histórico e manutenção.

---

# 5. Chaves primárias e estrangeiras do domínio comercial / SaaS

## 5.1. `contas`

**PK:** `id`

### FKs:

não possui FK obrigatória estrutural para outra tabela principal.

### Observação:

é a raiz comercial do sistema.

## 5.2. `planos_assinatura`

**PK:** `id`

### FKs:

não possui FK obrigatória estrutural.

## 5.3. `assinaturas`

**PK:** `id`

### FKs:

- `conta_id` → `contas.id`
- `plano_id` → `planos_assinatura.id`

### Observação:

essas duas FKs são obrigatórias.

## 5.4. `faturas`

**PK:** `id`

### FKs:

- `assinatura_id` → `assinaturas.id`

### Observação:

FK obrigatória, pois não existe fatura sem assinatura.

## 5.5. `modulos`

**PK:** `id`

### FKs:

não possui FK obrigatória estrutural.

## 5.6. `assinatura_modulos`

**PK:** `id`  
ou, alternativamente, PK composta por (`assinatura_id`, `modulo_id`) se o projeto optar por associação pura.

### FKs:

- `assinatura_id` → `assinaturas.id`
- `modulo_id` → `modulos.id`

### Recomendação:

usar `id` como PK e unique composto em (`assinatura_id`, `modulo_id`) para maior flexibilidade.

## 5.7. `leads_comerciais`

**PK:** `id`

### FKs:

- `conta_id` → `contas.id` (opcional, quando já houver conversão ou vínculo)

---

# 6. Chaves primárias e estrangeiras do domínio institucional

## 6.1. `orgaos`

**PK:** `id`

### FKs:

- `conta_id` → `contas.id`

### Observação:

FK obrigatória, pois todo órgão operador pertence a uma conta contratante.

## 6.2. `unidades`

**PK:** `id`

### FKs:

- `orgao_id` → `orgaos.id`
- `unidade_superior_id` → `unidades.id` (opcional, se for adotado autorrelacionamento simples)

## 6.3. `unidades_hierarquia`

**PK:** `id`

### FKs:

- `unidade_pai_id` → `unidades.id`
- `unidade_filha_id` → `unidades.id`

### Observação:

essa tabela só deve existir se a hierarquia institucional exigir modelagem separada.

---

# 7. Chaves primárias e estrangeiras do domínio de identidade e acesso

## 7.1. `usuarios`

**PK:** `id`

### FKs:

- `conta_id` → `contas.id`
- `orgao_id` → `orgaos.id`
- `unidade_id` → `unidades.id` (opcional, conforme contexto do usuário)

### Observação:

`conta_id` e `orgao_id` devem ser obrigatórios; `unidade_id` pode ser opcional em alguns perfis.

## 7.2. `perfis`

**PK:** `id`

### FKs:

não possui FK obrigatória estrutural.

## 7.3. `usuarios_perfis`

**PK:** `id`  
ou PK composta (`usuario_id`, `perfil_id`).

### FKs:

- `usuario_id` → `usuarios.id`
- `perfil_id` → `perfis.id`

### Recomendação:

usar `id` + unique (`usuario_id`, `perfil_id`) para permitir trilha temporal futura, se necessário.

## 7.4. `permissoes`

**PK:** `id`

### FKs:

não possui FK obrigatória estrutural.

## 7.5. `perfis_permissoes`

**PK:** `id`  
ou PK composta (`perfil_id`, `permissao_id`).

### FKs:

- `perfil_id` → `perfis.id`
- `permissao_id` → `permissoes.id`

## 7.6. `sessoes_usuario`

**PK:** `id`

### FKs:

- `usuario_id` → `usuarios.id`

## 7.7. `autenticacao_2fator`

**PK:** `id`

### FKs:

- `usuario_id` → `usuarios.id`

### Observação:

caso o modelo seja estritamente 1:1, recomenda-se unique em `usuario_id`.

---

# 8. Chaves primárias e estrangeiras do domínio PLANCON

## 8.1. `plancons`

**PK:** `id`

### FKs:

- `conta_id` → `contas.id`
- `orgao_id` → `orgaos.id`
- `unidade_id` → `unidades.id` (opcional)
- `usuario_responsavel_id` → `usuarios.id` (opcional, se houver responsável cadastrado)

## 8.2. `plancon_versoes`

**PK:** `id`

### FKs:

- `plancon_id` → `plancons.id`
- `versao_anterior_id` → `plancon_versoes.id` (opcional)
- `usuario_revisao_id` → `usuarios.id` (opcional)

## 8.3. `plancon_territorios`

**PK:** `id`

### FKs:

- `plancon_id` → `plancons.id`

## 8.4. `plancon_riscos`

**PK:** `id`

### FKs:

- `plancon_id` → `plancons.id`

## 8.5. `plancon_cenarios`

**PK:** `id`

### FKs:

- `plancon_id` → `plancons.id`

## 8.6. `plancon_niveis_ativacao`

**PK:** `id`

### FKs:

- `plancon_id` → `plancons.id`

## 8.7. `plancon_governanca`

**PK:** `id`

### FKs:

- `plancon_id` → `plancons.id`
- `orgao_referenciado_id` → `orgaos.id` (opcional)
- `unidade_referenciada_id` → `unidades.id` (opcional)
- `usuario_referenciado_id` → `usuarios.id` (opcional)

## 8.8. `plancon_recursos`

**PK:** `id`

### FKs:

- `plancon_id` → `plancons.id`
- `orgao_responsavel_id` → `orgaos.id` (opcional)
- `unidade_responsavel_id` → `unidades.id` (opcional)

## 8.9. `plancon_monitoramento_comunicacao`

**PK:** `id`

### FKs:

- `plancon_id` → `plancons.id`
- `usuario_acompanhamento_id` → `usuarios.id` (opcional)
- `usuario_validacao_id` → `usuarios.id` (opcional)
- `usuario_emissor_alerta_id` → `usuarios.id` (opcional)

## 8.10. `plancon_procedimentos`

**PK:** `id`

### FKs:

- `plancon_id` → `plancons.id`
- `orgao_executor_id` → `orgaos.id` (opcional)

## 8.11. `plancon_rotas_abrigos`

**PK:** `id`

### FKs:

- `plancon_id` → `plancons.id`

## 8.12. `plancon_assistencia`

**PK:** `id`

### FKs:

- `plancon_id` → `plancons.id`
- `orgao_responsavel_id` → `orgaos.id` (opcional)

## 8.13. `plancon_simulados`

**PK:** `id`

### FKs:

- `plancon_id` → `plancons.id`
- `usuario_responsavel_id` → `usuarios.id` (opcional)

## 8.14. `plancon_revisoes`

**PK:** `id`

### FKs:

- `plancon_id` → `plancons.id`
- `versao_id` → `plancon_versoes.id` (opcional)
- `usuario_revisor_id` → `usuarios.id` (opcional)

## 8.15. `plancon_anexos`

**PK:** `id`

### FKs:

- `plancon_id` → `plancons.id`
- `anexo_id` → `anexos.id` (recomendado, se usar arquitetura documental central)

---

# 9. Chaves primárias e estrangeiras do núcleo CSI/SCO do PLANCON

## 9.1. `plancon_estruturas_operacionais`

**PK:** `id`

### FKs:

- `plancon_id` → `plancons.id`
- `cenario_id` → `plancon_cenarios.id` (opcional)
- `unidade_id` → `unidades.id` (opcional)
- `orgao_id` → `orgaos.id` (opcional)
- `comandante_usuario_id` → `usuarios.id` (opcional)

## 9.2. `plancon_funcoes_equipes`

**PK:** `id`

### FKs:

- `estrutura_operacional_id` → `plancon_estruturas_operacionais.id`
- `usuario_titular_id` → `usuarios.id` (opcional)
- `usuario_substituto_id` → `usuarios.id` (opcional)
- `orgao_id` → `orgaos.id` (opcional)
- `unidade_id` → `unidades.id` (opcional)

## 9.3. `plancon_instalacoes`

**PK:** `id`

### FKs:

- `estrutura_operacional_id` → `plancon_estruturas_operacionais.id`
- `orgao_gestor_id` → `orgaos.id` (opcional)
- `usuario_responsavel_id` → `usuarios.id` (opcional)

## 9.4. `plancon_periodos_operacionais`

**PK:** `id`

### FKs:

- `estrutura_operacional_id` → `plancon_estruturas_operacionais.id`
- `usuario_aprovador_id` → `usuarios.id` (opcional)

## 9.5. `plancon_registros_operacionais`

**PK:** `id`

### FKs:

- `estrutura_operacional_id` → `plancon_estruturas_operacionais.id`
- `periodo_operacional_id` → `plancon_periodos_operacionais.id` (opcional, mas fortemente recomendado)
- `usuario_responsavel_info_id` → `usuarios.id` (opcional)
- `usuario_lancamento_id` → `usuarios.id` (opcional)
- `anexo_id` → `anexos.id` (opcional, se houver um principal)

---

# 10. Chaves primárias e estrangeiras do domínio Incidentes / SCI-SCO

## 10.1. `incidentes`

**PK:** `id`

### FKs:

- `conta_id` → `contas.id`
- `orgao_id` → `orgaos.id`
- `unidade_id` → `unidades.id` (opcional)
- `plancon_id` → `plancons.id` (opcional)
- `cenario_id` → `plancon_cenarios.id` (opcional)
- `usuario_abertura_id` → `usuarios.id` (opcional)

## 10.2. `incidentes_briefing`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id`
- `usuario_responsavel_id` → `usuarios.id` (opcional)

## 10.3. `incidentes_comando`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id`
- `usuario_comandante_id` → `usuarios.id` (opcional)
- `comando_anterior_id` → `incidentes_comando.id` (opcional, para histórico)

## 10.4. `incidentes_staff_comando`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id`
- `usuario_id` → `usuarios.id` (opcional)
- `orgao_id` → `orgaos.id` (opcional)

## 10.5. `incidentes_staff_geral`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id`
- `usuario_chefe_id` → `usuarios.id` (opcional)
- `usuario_substituto_id` → `usuarios.id` (opcional)
- `orgao_id` → `orgaos.id` (opcional)

## 10.6. `incidentes_periodos_operacionais`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id`
- `usuario_aprovador_id` → `usuarios.id` (opcional)
- `pai_id` → `incidentes_estrategias_pai.id` (opcional, se houver vínculo direto principal)

## 10.7. `incidentes_objetivos`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id`
- `periodo_operacional_id` → `incidentes_periodos_operacionais.id`
- `usuario_responsavel_id` → `usuarios.id` (opcional)

## 10.8. `incidentes_estrategias_pai`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id`
- `periodo_operacional_id` → `incidentes_periodos_operacionais.id`
- `usuario_aprovador_id` → `usuarios.id` (opcional)

## 10.9. `incidentes_operacoes_campo`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id`
- `periodo_operacional_id` → `incidentes_periodos_operacionais.id`
- `usuario_supervisor_id` → `usuarios.id` (opcional)

## 10.10. `incidentes_planejamento_situacao`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id`
- `periodo_operacional_id` → `incidentes_periodos_operacionais.id`
- `usuario_planejamento_id` → `usuarios.id` (opcional)

## 10.11. `incidentes_recursos`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id`
- `recurso_origem_id` → `plancon_recursos.id` (opcional)
- `usuario_supervisor_id` → `usuarios.id` (opcional)
- `orgao_proprietario_id` → `orgaos.id` (opcional)

## 10.12. `incidentes_instalacoes`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id`
- `instalacao_origem_id` → `plancon_rotas_abrigos.id` (opcional)
- `usuario_responsavel_id` → `usuarios.id` (opcional)

## 10.13. `incidentes_comunicacoes`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id`
- `periodo_operacional_id` → `incidentes_periodos_operacionais.id`
- `usuario_responsavel_tecnico_id` → `usuarios.id` (opcional)

## 10.14. `incidentes_seguranca`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id`
- `periodo_operacional_id` → `incidentes_periodos_operacionais.id`
- `usuario_responsavel_seguranca_id` → `usuarios.id` (opcional)

## 10.15. `incidentes_informacao_publica`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id`
- `usuario_aprovador_id` → `usuarios.id` (opcional)
- `usuario_porta_voz_id` → `usuarios.id` (opcional)

## 10.16. `incidentes_ligacao_interinstitucional`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id`
- `orgao_participante_id` → `orgaos.id` (opcional)
- `usuario_representante_id` → `usuarios.id` (opcional)

## 10.17. `incidentes_financas`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id`
- `periodo_operacional_id` → `incidentes_periodos_operacionais.id` (opcional)
- `usuario_responsavel_id` → `usuarios.id` (opcional)

## 10.18. `incidentes_registros_operacionais`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id`
- `periodo_operacional_id` → `incidentes_periodos_operacionais.id` (opcional, mas recomendado)
- `usuario_responsavel_info_id` → `usuarios.id` (opcional)
- `usuario_lancamento_id` → `usuarios.id` (opcional)
- `anexo_id` → `anexos.id` (opcional, para evidência principal)

## 10.19. `incidentes_desmobilizacao`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id`
- `usuario_responsavel_id` → `usuarios.id` (opcional)

---

# 11. Chaves primárias e estrangeiras do domínio documental

## 11.1. `anexos`

**PK:** `id`

### FKs:

- `usuario_envio_id` → `usuarios.id` (opcional)
- `conta_id` → `contas.id` (opcional, mas recomendado)
- `orgao_id` → `orgaos.id` (opcional)

## 11.2. `documentos_vinculados`

**PK:** `id`

### FKs:

- `anexo_id` → `anexos.id`
- `usuario_vinculacao_id` → `usuarios.id` (opcional)

### Observação:

o vínculo com a entidade de negócio pode ser:

- por colunas explícitas de FK, se a tabela for especializada; ou
- por referência polimórfica controlada (`entidade_tipo`, `entidade_id`) se a arquitetura documental central for mais genérica.

### Recomendação:

para o SIGERD, a abordagem polimórfica controlada pode ser útil, mas exige regras fortes na aplicação.

## 11.3. `relatorios_gerados`

**PK:** `id`

### FKs:

- `usuario_id` → `usuarios.id`
- `conta_id` → `contas.id` (opcional)
- `orgao_id` → `orgaos.id` (opcional)
- `anexo_id` → `anexos.id` (opcional, se o relatório gerado for armazenado como arquivo)

## 11.4. `exportacoes`

**PK:** `id`

### FKs:

- `usuario_id` → `usuarios.id`
- `conta_id` → `contas.id` (opcional)
- `orgao_id` → `orgaos.id` (opcional)
- `anexo_id` → `anexos.id` (opcional)

## 11.5. `mapas_operacionais`

**PK:** `id`

### FKs:

- `incidente_id` → `incidentes.id` (opcional)
- `plancon_id` → `plancons.id` (opcional)
- `usuario_id` → `usuarios.id` (opcional)

---

# 12. Chaves primárias e estrangeiras do domínio de auditoria e conformidade

## 12.1. `logs_auditoria`

**PK:** `id`

### FKs:

- `conta_id` → `contas.id` (opcional, mas recomendado)
- `orgao_id` → `orgaos.id` (opcional)
- `unidade_id` → `unidades.id` (opcional)
- `usuario_id` → `usuarios.id` (opcional)

## 12.2. `logs_acesso`

**PK:** `id`

### FKs:

- `usuario_id` → `usuarios.id` (obrigatória na maioria dos casos; opcional em falhas anteriores à identificação completa)

## 12.3. `termos_aceites`

**PK:** `id`

### FKs:

- `conta_id` → `contas.id`
- `assinatura_id` → `assinaturas.id` (opcional)
- `usuario_responsavel_id` → `usuarios.id` (opcional)

## 12.4. `eventos_sensiveis`

**PK:** `id`

### FKs:

- `conta_id` → `contas.id` (opcional)
- `orgao_id` → `orgaos.id` (opcional)
- `usuario_id` → `usuarios.id` (opcional)
- `log_auditoria_id` → `logs_auditoria.id` (opcional, se quiser vincular ao evento-base)

---

# 13. Tabelas auxiliares e catálogos

Caso o sistema adote catálogos normalizados, todas essas tabelas terão:

**PK:** `id`

### FKs:

normalmente não possuem FKs obrigatórias externas, pois funcionam como tabelas de referência.

### Exemplos:

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

---

# 14. Matriz resumida de PKs e FKs centrais

|Tabela|PK|FKs principais|
|---|---|---|
|contas|id|—|
|orgaos|id|conta_id|
|unidades|id|orgao_id, unidade_superior_id|
|usuarios|id|conta_id, orgao_id, unidade_id|
|perfis|id|—|
|usuarios_perfis|id|usuario_id, perfil_id|
|planos_assinatura|id|—|
|assinaturas|id|conta_id, plano_id|
|faturas|id|assinatura_id|
|modulos|id|—|
|assinatura_modulos|id|assinatura_id, modulo_id|
|plancons|id|conta_id, orgao_id, unidade_id|
|plancon_versoes|id|plancon_id, versao_anterior_id|
|plancon_riscos|id|plancon_id|
|plancon_cenarios|id|plancon_id|
|plancon_estruturas_operacionais|id|plancon_id, cenario_id|
|plancon_funcoes_equipes|id|estrutura_operacional_id, usuario_titular_id|
|plancon_periodos_operacionais|id|estrutura_operacional_id|
|plancon_registros_operacionais|id|estrutura_operacional_id, periodo_operacional_id|
|incidentes|id|conta_id, orgao_id, unidade_id, plancon_id, cenario_id|
|incidentes_briefing|id|incidente_id|
|incidentes_comando|id|incidente_id, usuario_comandante_id|
|incidentes_periodos_operacionais|id|incidente_id|
|incidentes_objetivos|id|incidente_id, periodo_operacional_id|
|incidentes_estrategias_pai|id|incidente_id, periodo_operacional_id|
|incidentes_recursos|id|incidente_id, recurso_origem_id|
|incidentes_instalacoes|id|incidente_id, instalacao_origem_id|
|incidentes_registros_operacionais|id|incidente_id, periodo_operacional_id|
|incidentes_desmobilizacao|id|incidente_id|
|anexos|id|usuario_envio_id, conta_id, orgao_id|
|documentos_vinculados|id|anexo_id, usuario_vinculacao_id|
|logs_auditoria|id|conta_id, orgao_id, unidade_id, usuario_id|
|termos_aceites|id|conta_id, assinatura_id, usuario_responsavel_id|

---

# 15. Diretrizes de integridade referencial

As FKs do SIGERD devem seguir as seguintes diretrizes práticas:

1. tabelas-filhas críticas não devem existir sem seu pai lógico;
2. exclusão física em tabelas centrais deve ser evitada quando houver histórico operacional;
3. preferir inativação lógica em vez de deleção cascata em entidades sensíveis;
4. `ON DELETE CASCADE` deve ser usado com extrema cautela;
5. em módulos como incidente, plancon, auditoria e assinatura, a preservação histórica é mais importante que a remoção automática;
6. FKs opcionais devem ser raras e justificadas funcionalmente.

## Recomendação prática de regra

- usar `RESTRICT` ou `NO ACTION` na maioria das relações sensíveis;
- usar `SET NULL` em FKs opcionais de referência secundária;
- reservar `CASCADE` para tabelas verdadeiramente subordinadas e sem relevância histórica independente.

---

# 16. Relações que exigem atenção especial

## 16.1. `incidentes` ↔ `plancons`

Esse vínculo deve ser opcional, porque nem todo incidente nasce de um plano formal vinculado.

## 16.2. `incidentes` ↔ `plancon_cenarios`

Também deve ser opcional, pois o incidente pode ocorrer fora de cenário previamente modelado.

## 16.3. `plancon_funcoes_equipes` ↔ `usuarios`

O vínculo com usuário deve ser opcional para permitir atores externos ou designações não cadastradas como login do sistema.

## 16.4. `documentos_vinculados`

Essa tabela exige cuidado porque pode apontar para múltiplos tipos de entidade. A integridade aqui precisa ser reforçada pela aplicação se o modelo polimórfico for adotado.

## 16.5. `logs_acesso`

Em falhas de login, pode não haver `usuario_id` resolvido. A modelagem deve aceitar isso quando o evento ocorrer antes da autenticação completa.

---

# 17. Erros de modelagem de chaves que devem ser evitados

Os principais erros a evitar são:

1. usar CPF, CNPJ ou código de negócio como PK principal;
2. deixar tabelas operacionais sem FK institucional mínima;
3. usar texto livre no lugar de FK quando a entidade já existe no banco;
4. criar FKs excessivamente rígidas em vínculos que precisam ser opcionais;
5. usar exclusão em cascata em tabelas históricas sensíveis;
6. depender apenas de nomes de pessoas em vez de `usuario_id` quando o usuário existe no sistema;
7. misturar identificadores de contrato com PK técnica;
8. não usar unique em tabelas associativas que exigem unicidade lógica.

---

# 18. Conclusão técnica

A estrutura de chaves primárias e estrangeiras do SIGERD confirma a coerência da modelagem já construída nos documentos anteriores. O ponto central deste documento é garantir que os vínculos técnicos do banco reflitam corretamente a lógica do sistema:

- a conta sustenta a dimensão comercial;
- o órgão e a unidade sustentam a dimensão institucional;
- o usuário opera dentro desse contexto;
- o plano de contingência é entidade própria;
- o incidente é entidade própria;
- os núcleos de comando do plano e do incidente possuem cadeia relacional própria;
- anexos, auditoria e conformidade atravessam toda a plataforma com vínculos formais.

Com isso, o projeto já está tecnicamente pronto para o detalhamento fino das colunas e significados de cada tabela.