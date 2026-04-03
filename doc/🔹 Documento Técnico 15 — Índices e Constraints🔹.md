**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Índices e Constraints  
**Objetivo:** definir os índices e constraints do banco de dados do SIGERD, garantindo integridade referencial, unicidade lógica, consistência de domínio e desempenho adequado de consulta.

## 1. Finalidade do documento

Este documento estabelece as diretrizes de índices e constraints do banco de dados do SIGERD. Seu propósito é assegurar que a modelagem relacional já definida funcione de forma segura, coerente e performática, evitando:

- duplicidade indevida de registros;
- inconsistência entre tabelas relacionadas;
- degradação de performance em consultas críticas;
- fragilidade em fluxos contratuais, institucionais e operacionais;
- ambiguidade em relações entre conta, órgão, unidade, plano, incidente e auditoria.

O foco aqui não é apenas técnico de banco. É também funcional. Em um sistema como o SIGERD, constraint mal definida afeta diretamente governança, rastreabilidade e segurança operacional.

## 2. Objetivos dos índices e constraints

A estratégia de índices e constraints do SIGERD deverá atender aos seguintes objetivos:

- garantir unicidade onde o negócio exigir;
- proteger integridade referencial entre entidades;
- reforçar regras mínimas de domínio;
- melhorar desempenho de filtros e relatórios;
- sustentar consultas por escopo institucional;
- sustentar consultas por vigência, status e período;
- evitar duplicidade em vínculos associativos;
- preservar histórico em vez de favorecer exclusão destrutiva.

## 3. Princípios gerais

Os índices e constraints do SIGERD deverão seguir os seguintes princípios:

1. toda PK deve ser indexada automaticamente;
2. toda FK relevante deve possuir índice correspondente;
3. unicidade deve refletir regra de negócio real, não conveniência técnica;
4. constraints de integridade devem ser preferidas a validações exclusivamente na aplicação, quando fizer sentido;
5. tabelas históricas e operacionais sensíveis devem evitar cascatas destrutivas;
6. índices devem ser criados para consultas reais esperadas, não por excesso;
7. combinações frequentes de filtro devem orientar índices compostos;
8. campos de status, período, órgão, unidade e conta são estruturalmente sensíveis no sistema.

## 4. Classificação dos constraints

No SIGERD, os principais tipos de constraints recomendados são:

- **PRIMARY KEY**
- **FOREIGN KEY**
- **UNIQUE**
- **NOT NULL**
- **CHECK**
- **DEFAULT**, quando necessário como regra de consistência operacional

### Observação crítica

Embora parte das validações de negócio mais densas pertença à aplicação, o banco deve assegurar as regras mínimas que não podem ser quebradas sem comprometer a integridade estrutural.

## 5. Classificação dos índices

Os principais tipos de índice recomendados são:

- índice primário;
- índice simples;
- índice composto;
- índice único;
- índice auxiliar para relatórios e filtros;
- índice em colunas de data/status/escopo institucional.

Não há necessidade, neste estágio, de prever indexação avançada especializada além do que o uso real do sistema justificar.

---

# 6. Constraints e índices do domínio comercial / SaaS

## 6.1. Tabela `contas`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `NOT NULL` em:
    - `nome_fantasia`
    - `tipo_pessoa`
    - `email_principal`
    - `status_cadastro`
    - `data_cadastro`

### Unique recomendados

- `UNIQUE (cpf_cnpj)` quando houver obrigatoriedade e padronização consistente
- `UNIQUE (email_principal)` se a conta for identificada comercialmente por e-mail principal

### Índices recomendados

- índice simples em `status_cadastro`
- índice simples em `email_principal`
- índice simples em `cpf_cnpj`

### Observação

Se houver contas em pré-cadastro sem documento definitivo, a constraint de unicidade em `cpf_cnpj` deve considerar possibilidade de valor nulo.

## 6.2. Tabela `planos_assinatura`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `NOT NULL` em:
    - `nome_plano`
    - `nome_comercial`
    - `tipo_plano`
    - `valor_mensal`
    - `status_plano`

### Unique recomendados

- `UNIQUE (nome_plano)`
- `UNIQUE (nome_comercial)` se a regra comercial impedir repetição

### Índices recomendados

- índice simples em `status_plano`
- índice simples em `ordem_exibicao`

## 6.3. Tabela `assinaturas`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (conta_id) REFERENCES contas(id)`
- `FOREIGN KEY (plano_id) REFERENCES planos_assinatura(id)`
- `NOT NULL` em:
    - `conta_id`
    - `plano_id`
    - `data_inicio`
    - `ciclo_cobranca`
    - `valor_contratado`
    - `status_assinatura`

### Unique recomendados

- `UNIQUE (codigo_assinatura)` se o código for gerado de forma oficial
- regra de unicidade lógica para impedir mais de uma assinatura ativa principal por conta, se essa for a política contratual

### Índices recomendados

- índice simples em `conta_id`
- índice simples em `plano_id`
- índice simples em `status_assinatura`
- índice composto em `(conta_id, status_assinatura)`
- índice composto em `(data_inicio, data_fim)`
- índice em `data_fim_teste` para controle de trial

### Check recomendados

- `valor_contratado >= 0`
- `desconto_aplicado >= 0`
- `data_fim >= data_inicio` quando `data_fim` não for nula
- `data_fim_teste >= data_inicio_teste` quando houver período de teste

## 6.4. Tabela `faturas`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (assinatura_id) REFERENCES assinaturas(id)`
- `NOT NULL` em:
    - `assinatura_id`
    - `competencia`
    - `data_emissao`
    - `data_vencimento`
    - `valor_bruto`
    - `valor_liquido`
    - `status_fatura`

### Unique recomendados

- `UNIQUE (assinatura_id, competencia)` se só puder haver uma fatura por competência e assinatura

### Índices recomendados

- índice simples em `assinatura_id`
- índice simples em `status_fatura`
- índice simples em `data_vencimento`
- índice composto em `(status_fatura, data_vencimento)`
- índice composto em `(assinatura_id, status_fatura)`

### Check recomendados

- `valor_bruto >= 0`
- `valor_desconto >= 0`
- `valor_liquido >= 0`
- `valor_pago >= 0`
- `data_pagamento >= data_emissao` quando preenchida

## 6.5. Tabela `modulos`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `NOT NULL` em:
    - `nome_modulo`
    - `codigo_modulo`, se adotado

### Unique recomendados

- `UNIQUE (nome_modulo)` ou preferencialmente `UNIQUE (codigo_modulo)`

## 6.6. Tabela `assinatura_modulos`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (assinatura_id) REFERENCES assinaturas(id)`
- `FOREIGN KEY (modulo_id) REFERENCES modulos(id)`
- `NOT NULL` em:
    - `assinatura_id`
    - `modulo_id`
    - `status_modulo`

### Unique recomendados

- `UNIQUE (assinatura_id, modulo_id)`

### Índices recomendados

- índice simples em `assinatura_id`
- índice simples em `modulo_id`
- índice composto em `(assinatura_id, status_modulo)`

---

# 7. Constraints e índices do domínio institucional

## 7.1. Tabela `orgaos`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (conta_id) REFERENCES contas(id)`
- `NOT NULL` em:
    - `conta_id`
    - `nome_oficial`
    - `tipo_instituicao`
    - `esfera_administrativa`
    - `status_orgao`

### Unique recomendados

- `UNIQUE (conta_id, nome_oficial)` quando a conta não puder repetir órgão com mesmo nome
- `UNIQUE (cnpj)` se houver exigência de unicidade institucional formal

### Índices recomendados

- índice simples em `conta_id`
- índice simples em `municipio_sede`
- índice simples em `estado_sede`
- índice simples em `status_orgao`
- índice composto em `(conta_id, status_orgao)`

## 7.2. Tabela `unidades`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (orgao_id) REFERENCES orgaos(id)`
- `FOREIGN KEY (unidade_superior_id) REFERENCES unidades(id)` quando adotado
- `NOT NULL` em:
    - `orgao_id`
    - `nome_unidade`
    - `tipo_unidade`

### Unique recomendados

- `UNIQUE (orgao_id, codigo_unidade)` quando o código existir
- `UNIQUE (orgao_id, nome_unidade)` quando a regra institucional impedir duplicidade interna

### Índices recomendados

- índice simples em `orgao_id`
- índice simples em `unidade_superior_id`
- índice simples em `municipio`
- índice simples em `estado`
- índice simples em `status_unidade`
- índice composto em `(orgao_id, status_unidade)`

### Check recomendados

- impedir autorreferência inválida simples, como `unidade_superior_id = id`, quando o banco suportar isso com segurança ou a aplicação reforçar

---

# 8. Constraints e índices do domínio de identidade e acesso

## 8.1. Tabela `usuarios`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (conta_id) REFERENCES contas(id)`
- `FOREIGN KEY (orgao_id) REFERENCES orgaos(id)`
- `FOREIGN KEY (unidade_id) REFERENCES unidades(id)`
- `NOT NULL` em:
    - `conta_id`
    - `orgao_id`
    - `nome_completo`
    - `email_login`
    - `senha_hash`
    - `status_usuario`
    - `data_cadastro`

### Unique recomendados

- `UNIQUE (email_login)`
- `UNIQUE (cpf)` quando o campo for obrigatório e normalizado
- `UNIQUE (orgao_id, matricula_funcional)` se matrícula for única por órgão

### Índices recomendados

- índice simples em `conta_id`
- índice simples em `orgao_id`
- índice simples em `unidade_id`
- índice simples em `status_usuario`
- índice simples em `ultimo_login`
- índice composto em `(orgao_id, status_usuario)`
- índice composto em `(conta_id, status_usuario)`

## 8.2. Tabela `perfis`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `NOT NULL` em:
    - `nome_perfil`
    - `status_perfil`

### Unique recomendados

- `UNIQUE (nome_perfil)`

## 8.3. Tabela `usuarios_perfis`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (usuario_id) REFERENCES usuarios(id)`
- `FOREIGN KEY (perfil_id) REFERENCES perfis(id)`
- `NOT NULL` em:
    - `usuario_id`
    - `perfil_id`

### Unique recomendados

- `UNIQUE (usuario_id, perfil_id)`

### Índices recomendados

- índice simples em `usuario_id`
- índice simples em `perfil_id`

## 8.4. Tabela `permissoes`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `NOT NULL` em `nome_permissao` ou `codigo_permissao`, conforme padrão adotado

### Unique recomendados

- `UNIQUE (codigo_permissao)` ou equivalente

## 8.5. Tabela `perfis_permissoes`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (perfil_id) REFERENCES perfis(id)`
- `FOREIGN KEY (permissao_id) REFERENCES permissoes(id)`
- `NOT NULL` em:
    - `perfil_id`
    - `permissao_id`

### Unique recomendados

- `UNIQUE (perfil_id, permissao_id)`

## 8.6. Tabela `sessoes_usuario`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (usuario_id) REFERENCES usuarios(id)`
- `NOT NULL` em:
    - `usuario_id`
    - `data_inicio_sessao`, se existir
    - `status_sessao`, se existir

### Índices recomendados

- índice simples em `usuario_id`
- índice composto em `(usuario_id, status_sessao)`

## 8.7. Tabela `autenticacao_2fator`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (usuario_id) REFERENCES usuarios(id)`
- `NOT NULL` em:
    - `usuario_id`
    - `status_2fator`, se existir

### Unique recomendados

- `UNIQUE (usuario_id)` se houver apenas uma configuração ativa por usuário

---

# 9. Constraints e índices do domínio PLANCON

## 9.1. Tabela `plancons`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (conta_id) REFERENCES contas(id)`
- `FOREIGN KEY (orgao_id) REFERENCES orgaos(id)`
- `FOREIGN KEY (unidade_id) REFERENCES unidades(id)`
- `NOT NULL` em:
    - `conta_id`
    - `orgao_id`
    - `titulo_plano`
    - `status_plancon`

### Unique recomendados

- `UNIQUE (orgao_id, titulo_plano, versao_documento)` quando houver controle formal por título e versão
- `UNIQUE (conta_id, orgao_id, titulo_plano)` se a versão estiver separada em outra tabela e a regra impedir duplicata lógica

### Índices recomendados

- índice simples em `conta_id`
- índice simples em `orgao_id`
- índice simples em `unidade_id`
- índice simples em `status_plancon`
- índice simples em `tipo_desastre_principal`
- índice composto em `(orgao_id, status_plancon)`
- índice composto em `(vigencia_inicio, vigencia_fim)`
- índice composto em `(municipio_estado, status_plancon)` ou desmembrado em município/estado se normalizado

## 9.2. Tabela `plancon_versoes`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (plancon_id) REFERENCES plancons(id)`
- `FOREIGN KEY (versao_anterior_id) REFERENCES plancon_versoes(id)`
- `NOT NULL` em:
    - `plancon_id`
    - `numero_versao` ou campo equivalente
    - `data_revisao`, se existir

### Unique recomendados

- `UNIQUE (plancon_id, numero_versao)`

### Índices recomendados

- índice simples em `plancon_id`
- índice simples em `data_revisao`

## 9.3. Tabelas dependentes do PLANCON

Aplicável a:

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

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (plancon_id) REFERENCES plancons(id)`
- `NOT NULL` em `plancon_id`

### Índices recomendados

- índice simples em `plancon_id`
- índices adicionais conforme o filtro mais provável:
    - `plancon_riscos`: índice em `tipo_ameaca`, `nivel_risco`
    - `plancon_cenarios`: índice em `tipo_desastre_associado`, `classificacao_cenario`
    - `plancon_niveis_ativacao`: índice em `nivel_operacional`
    - `plancon_recursos`: índice em `tipo_recurso`, `categoria_recurso`
    - `plancon_rotas_abrigos`: índice em `tipo_local`, `status_uso`
    - `plancon_simulados`: índice em `data_prevista`
    - `plancon_revisoes`: índice em `data_revisao`, `status_plano` quando existir

### Unique recomendados específicos

- `plancon_niveis_ativacao`: `UNIQUE (plancon_id, nivel_operacional)` ou `UNIQUE (plancon_id, nome_nivel)`
- `plancon_cenarios`: `UNIQUE (plancon_id, nome_cenario)` se a regra exigir nome único por plano
- `plancon_rotas_abrigos`: `UNIQUE (plancon_id, nome_local, tipo_local)` quando aplicável

---

# 10. Constraints e índices do núcleo CSI/SCO do PLANCON

## 10.1. Tabela `plancon_estruturas_operacionais`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (plancon_id) REFERENCES plancons(id)`
- `FOREIGN KEY (cenario_id) REFERENCES plancon_cenarios(id)`
- `NOT NULL` em:
    - `plancon_id`
    - `nome_estrutura`
    - `status_estrutura`

### Índices recomendados

- índice simples em `plancon_id`
- índice simples em `cenario_id`
- índice simples em `status_estrutura`
- índice composto em `(plancon_id, status_estrutura)`

## 10.2. Tabela `plancon_funcoes_equipes`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (estrutura_operacional_id) REFERENCES plancon_estruturas_operacionais(id)`
- `FOREIGN KEY (usuario_titular_id) REFERENCES usuarios(id)`
- `FOREIGN KEY (usuario_substituto_id) REFERENCES usuarios(id)`
- `NOT NULL` em:
    - `estrutura_operacional_id`
    - `nome_funcao`
    - `status_funcao`

### Índices recomendados

- índice simples em `estrutura_operacional_id`
- índice simples em `status_funcao`
- índice simples em `categoria_funcao`
- índice composto em `(estrutura_operacional_id, status_funcao)`

### Unique recomendados

- `UNIQUE (estrutura_operacional_id, codigo_funcao)` quando houver código formal da função
- `UNIQUE (estrutura_operacional_id, nome_funcao, periodo_turno_atuacao)` se o modelo exigir não repetição operacional equivalente

## 10.3. Tabela `plancon_instalacoes`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (estrutura_operacional_id) REFERENCES plancon_estruturas_operacionais(id)`
- `NOT NULL` em:
    - `estrutura_operacional_id`
    - `nome_instalacao`
    - `tipo_instalacao`
    - `status_instalacao`

### Índices recomendados

- índice simples em `estrutura_operacional_id`
- índice simples em `tipo_instalacao`
- índice simples em `status_instalacao`
- índice composto em `(estrutura_operacional_id, status_instalacao)`

## 10.4. Tabela `plancon_periodos_operacionais`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (estrutura_operacional_id) REFERENCES plancon_estruturas_operacionais(id)`
- `NOT NULL` em:
    - `estrutura_operacional_id`
    - `numero_periodo`
    - `data_hora_inicio`
    - `status_periodo`

### Unique recomendados

- `UNIQUE (estrutura_operacional_id, numero_periodo)`

### Índices recomendados

- índice simples em `estrutura_operacional_id`
- índice simples em `status_periodo`
- índice composto em `(estrutura_operacional_id, status_periodo)`
- índice composto em `(data_hora_inicio, data_hora_fim)`

## 10.5. Tabela `plancon_registros_operacionais`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (estrutura_operacional_id) REFERENCES plancon_estruturas_operacionais(id)`
- `FOREIGN KEY (periodo_operacional_id) REFERENCES plancon_periodos_operacionais(id)`
- `NOT NULL` em:
    - `estrutura_operacional_id`
    - `data_hora_registro`
    - `tipo_registro`
    - `status_registro`

### Índices recomendados

- índice simples em `estrutura_operacional_id`
- índice simples em `periodo_operacional_id`
- índice simples em `tipo_registro`
- índice simples em `status_registro`
- índice composto em `(estrutura_operacional_id, data_hora_registro)`
- índice composto em `(periodo_operacional_id, tipo_registro)`
- índice composto em `(status_registro, data_hora_registro)`

---

# 11. Constraints e índices do domínio Incidentes / SCI-SCO

## 11.1. Tabela `incidentes`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (conta_id) REFERENCES contas(id)`
- `FOREIGN KEY (orgao_id) REFERENCES orgaos(id)`
- `FOREIGN KEY (unidade_id) REFERENCES unidades(id)`
- `FOREIGN KEY (plancon_id) REFERENCES plancons(id)`
- `FOREIGN KEY (cenario_id) REFERENCES plancon_cenarios(id)`
- `NOT NULL` em:
    - `conta_id`
    - `orgao_id`
    - `numero_ocorrencia`
    - `nome_incidente`
    - `tipo_ocorrencia`
    - `data_hora_abertura`
    - `status_incidente`

### Unique recomendados

- `UNIQUE (numero_ocorrencia)` se o número for globalmente único
- ou `UNIQUE (orgao_id, numero_ocorrencia)` se a numeração for por órgão

### Índices recomendados

- índice simples em `conta_id`
- índice simples em `orgao_id`
- índice simples em `unidade_id`
- índice simples em `status_incidente`
- índice simples em `tipo_ocorrencia`
- índice simples em `municipio`
- índice simples em `data_hora_abertura`
- índice composto em `(orgao_id, status_incidente)`
- índice composto em `(municipio, status_incidente)`
- índice composto em `(data_hora_abertura, status_incidente)`

## 11.2. Tabela `incidentes_briefing`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (incidente_id) REFERENCES incidentes(id)`
- `NOT NULL` em:
    - `incidente_id`
    - `resumo_situacao`

### Unique recomendados

- `UNIQUE (incidente_id)` se houver um único briefing vigente
- se houver histórico, substituir por regra de versão ou vigência

### Índices recomendados

- índice simples em `incidente_id`
- índice simples em `data_hora_briefing`

## 11.3. Tabela `incidentes_comando`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (incidente_id) REFERENCES incidentes(id)`
- `FOREIGN KEY (comando_anterior_id) REFERENCES incidentes_comando(id)`
- `NOT NULL` em:
    - `incidente_id`
    - `tipo_comando`
    - `status_comando`

### Índices recomendados

- índice simples em `incidente_id`
- índice simples em `status_comando`
- índice simples em `data_hora_assuncao`

### Unique recomendados

- regra de unicidade lógica para um único comando vigente por incidente, se esse for o modelo adotado

## 11.4. Tabelas dependentes do incidente

Aplicável a:

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

### Constraints mínimas comuns

- `PRIMARY KEY (id)`
- `FOREIGN KEY (incidente_id) REFERENCES incidentes(id)`
- `NOT NULL` em `incidente_id`

## 11.5. Tabela `incidentes_periodos_operacionais`

### Constraints recomendados

- `NOT NULL` em:
    - `incidente_id`
    - `numero_periodo`
    - `data_hora_inicio`

### Unique recomendados

- `UNIQUE (incidente_id, numero_periodo)`

### Índices recomendados

- índice simples em `incidente_id`
- índice simples em `data_hora_inicio`
- índice composto em `(incidente_id, data_hora_inicio)`

## 11.6. Tabela `incidentes_objetivos`

### Constraints recomendados

- `FOREIGN KEY (periodo_operacional_id) REFERENCES incidentes_periodos_operacionais(id)`
- `NOT NULL` em:
    - `incidente_id`
    - `periodo_operacional_id`
    - `objetivo_estrategico`, se existir
    - `status_objetivo`, se existir

### Índices recomendados

- índice simples em `periodo_operacional_id`
- índice simples em `status_objetivo`
- índice composto em `(incidente_id, periodo_operacional_id)`

## 11.7. Tabela `incidentes_estrategias_pai`

### Constraints recomendados

- `FOREIGN KEY (periodo_operacional_id) REFERENCES incidentes_periodos_operacionais(id)`
- `NOT NULL` em:
    - `incidente_id`
    - `periodo_operacional_id`

### Índices recomendados

- índice simples em `periodo_operacional_id`
- índice composto em `(incidente_id, periodo_operacional_id)`

## 11.8. Tabela `incidentes_operacoes_campo`

### Constraints recomendados

- `FOREIGN KEY (periodo_operacional_id) REFERENCES incidentes_periodos_operacionais(id)`
- `NOT NULL` em:
    - `incidente_id`
    - `periodo_operacional_id`
    - `frente_operacional`, se existir

### Índices recomendados

- índice simples em `periodo_operacional_id`
- índice simples em `usuario_supervisor_id`, se existir
- índice composto em `(incidente_id, periodo_operacional_id)`

## 11.9. Tabela `incidentes_planejamento_situacao`

### Índices recomendados

- índice simples em `periodo_operacional_id`
- índice composto em `(incidente_id, periodo_operacional_id)`

## 11.10. Tabela `incidentes_recursos`

### Constraints recomendados

- `FOREIGN KEY (recurso_origem_id) REFERENCES plancon_recursos(id)`
- `NOT NULL` em:
    - `incidente_id`
    - `tipo_recurso`, se existir
    - `status_recurso`, se existir

### Índices recomendados

- índice simples em `incidente_id`
- índice simples em `status_recurso`
- índice simples em `tipo_recurso`
- índice simples em `recurso_origem_id`
- índice composto em `(incidente_id, status_recurso)`

## 11.11. Tabela `incidentes_instalacoes`

### Constraints recomendados

- `FOREIGN KEY (instalacao_origem_id) REFERENCES plancon_rotas_abrigos(id)`
- `NOT NULL` em:
    - `incidente_id`
    - `tipo_instalacao`, se existir
    - `status_instalacao`, se existir

### Índices recomendados

- índice simples em `incidente_id`
- índice simples em `tipo_instalacao`
- índice simples em `status_instalacao`
- índice composto em `(incidente_id, status_instalacao)`

## 11.12. Tabela `incidentes_comunicacoes`

### Índices recomendados

- índice simples em `incidente_id`
- índice simples em `periodo_operacional_id`
- índice composto em `(incidente_id, periodo_operacional_id)`

## 11.13. Tabela `incidentes_seguranca`

### Índices recomendados

- índice simples em `incidente_id`
- índice simples em `periodo_operacional_id`
- índice composto em `(incidente_id, periodo_operacional_id)`

## 11.14. Tabela `incidentes_informacao_publica`

### Índices recomendados

- índice simples em `incidente_id`
- índice simples em `data_hora_emissao`, se existir
- índice composto em `(incidente_id, data_hora_emissao)`

## 11.15. Tabela `incidentes_ligacao_interinstitucional`

### Índices recomendados

- índice simples em `incidente_id`
- índice simples em `orgao_participante_id`
- índice composto em `(incidente_id, orgao_participante_id)`

## 11.16. Tabela `incidentes_financas`

### Constraints recomendados

- `NOT NULL` em:
    - `incidente_id`

### Índices recomendados

- índice simples em `incidente_id`
- índice simples em `periodo_operacional_id`
- índice simples em `tipo_despesa`, se existir
- índice composto em `(incidente_id, periodo_operacional_id)`

### Check recomendados

- valores financeiros >= 0

## 11.17. Tabela `incidentes_registros_operacionais`

### Constraints recomendados

- `FOREIGN KEY (periodo_operacional_id) REFERENCES incidentes_periodos_operacionais(id)`
- `NOT NULL` em:
    - `incidente_id`
    - `data_hora_registro`
    - `tipo_registro`
    - `status_registro`

### Índices recomendados

- índice simples em `incidente_id`
- índice simples em `periodo_operacional_id`
- índice simples em `tipo_registro`
- índice simples em `status_registro`
- índice composto em `(incidente_id, data_hora_registro)`
- índice composto em `(periodo_operacional_id, tipo_registro)`
- índice composto em `(status_registro, data_hora_registro)`

## 11.18. Tabela `incidentes_desmobilizacao`

### Índices recomendados

- índice simples em `incidente_id`
- índice simples em `data_hora_encerramento`, se existir

---

# 12. Constraints e índices do domínio documental

## 12.1. Tabela `anexos`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (usuario_envio_id) REFERENCES usuarios(id)`
- `NOT NULL` em:
    - `nome_original`
    - `nome_interno`
    - `tipo_arquivo`
    - `caminho_armazenamento`
    - `data_envio`

### Índices recomendados

- índice simples em `usuario_envio_id`
- índice simples em `modulo_origem`
- índice simples em `data_envio`
- índice composto em `(modulo_origem, data_envio)`

### Check recomendados

- `tamanho_bytes >= 0`

## 12.2. Tabela `documentos_vinculados`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (anexo_id) REFERENCES anexos(id)`
- `NOT NULL` em:
    - `anexo_id`
    - `entidade_tipo`, se houver modelo polimórfico
    - `entidade_id`, se houver modelo polimórfico

### Índices recomendados

- índice simples em `anexo_id`
- índice composto em `(entidade_tipo, entidade_id)`

### Unique recomendados

- `UNIQUE (anexo_id, entidade_tipo, entidade_id)` para evitar vínculo duplicado exato

## 12.3. Tabela `relatorios_gerados`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (usuario_id) REFERENCES usuarios(id)`
- `NOT NULL` em:
    - `usuario_id`
    - `tipo_relatorio`, se existir
    - `data_geracao`, se existir

### Índices recomendados

- índice simples em `usuario_id`
- índice simples em `tipo_relatorio`
- índice simples em `data_geracao`
- índice composto em `(tipo_relatorio, data_geracao)`

## 12.4. Tabela `exportacoes`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (usuario_id) REFERENCES usuarios(id)`
- `NOT NULL` em:
    - `usuario_id`
    - `tipo_exportacao`, se existir
    - `data_exportacao`, se existir

### Índices recomendados

- índice simples em `usuario_id`
- índice simples em `tipo_exportacao`
- índice simples em `data_exportacao`
- índice composto em `(tipo_exportacao, data_exportacao)`

---

# 13. Constraints e índices do domínio de auditoria e conformidade

## 13.1. Tabela `logs_auditoria`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (conta_id) REFERENCES contas(id)`
- `FOREIGN KEY (orgao_id) REFERENCES orgaos(id)`
- `FOREIGN KEY (unidade_id) REFERENCES unidades(id)`
- `FOREIGN KEY (usuario_id) REFERENCES usuarios(id)`
- `NOT NULL` em:
    - `tipo_evento`
    - `modulo`
    - `acao`
    - `data_hora`

### Índices recomendados

- índice simples em `usuario_id`
- índice simples em `conta_id`
- índice simples em `orgao_id`
- índice simples em `unidade_id`
- índice simples em `modulo`
- índice simples em `acao`
- índice simples em `data_hora`
- índice composto em `(conta_id, data_hora)`
- índice composto em `(orgao_id, data_hora)`
- índice composto em `(usuario_id, data_hora)`
- índice composto em `(modulo, acao, data_hora)`

## 13.2. Tabela `logs_acesso`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (usuario_id) REFERENCES usuarios(id)`
- `NOT NULL` em:
    - `data_hora_evento`, se existir
    - `tipo_evento_acesso`, se existir

### Índices recomendados

- índice simples em `usuario_id`
- índice simples em `data_hora_evento`
- índice composto em `(usuario_id, data_hora_evento)`

## 13.3. Tabela `termos_aceites`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (conta_id) REFERENCES contas(id)`
- `FOREIGN KEY (assinatura_id) REFERENCES assinaturas(id)`
- `FOREIGN KEY (usuario_responsavel_id) REFERENCES usuarios(id)`
- `NOT NULL` em:
    - `conta_id`
    - `data_aceite`

### Índices recomendados

- índice simples em `conta_id`
- índice simples em `assinatura_id`
- índice simples em `data_aceite`
- índice composto em `(conta_id, data_aceite)`

## 13.4. Tabela `eventos_sensiveis`

### Constraints recomendados

- `PRIMARY KEY (id)`
- `FOREIGN KEY (usuario_id) REFERENCES usuarios(id)`
- `FOREIGN KEY (log_auditoria_id) REFERENCES logs_auditoria(id)`
- `NOT NULL` em:
    - `classificacao_evento`, se existir
    - `data_hora`, se existir

### Índices recomendados

- índice simples em `usuario_id`
- índice simples em `log_auditoria_id`
- índice simples em `data_hora`
- índice composto em `(usuario_id, data_hora)`

---

# 14. Defaults recomendados

Alguns campos do SIGERD devem possuir valores padrão para reduzir inconsistência operacional.

### Exemplos recomendados

- `status_cadastro`: default coerente como `PRE_CADASTRO`
- `status_usuario`: default coerente como `PENDENTE` ou `ATIVO`, conforme fluxo
- `status_assinatura`: default coerente como `PENDENTE` ou `TRIAL`
- `status_fatura`: default coerente como `ABERTA`
- `status_plancon`: default coerente como `EM_ELABORACAO` ou equivalente, se adotado
- `status_incidente`: default coerente como `ABERTO` ou equivalente
- `teste_gratis`: default `false`
- `renovacao_automatica`: default `false`
- `autenticacao_2fator`: default `false`
- `exigir_troca_senha`: default `false`

### Observação

Defaults devem servir para coerência inicial, não para mascarar ausência de regra de negócio.

---

# 15. Estratégia de `ON DELETE` e `ON UPDATE`

No SIGERD, a prioridade é preservação histórica. Portanto:

### Recomendações gerais

- `ON UPDATE CASCADE` pode ser aceitável em várias FKs técnicas
- `ON DELETE RESTRICT` ou `NO ACTION` deve ser o padrão em entidades históricas e sensíveis
- `ON DELETE SET NULL` deve ser usado em referências secundárias opcionais
- `ON DELETE CASCADE` deve ser restrito a tabelas realmente subordinadas e descartáveis sem valor histórico independente

### Exemplos em que evitar `CASCADE`

- `contas` → `assinaturas`
- `orgaos` → `usuarios`
- `plancons` → blocos do plano, se o plano possuir histórico institucional relevante
- `incidentes` → registros operacionais
- `usuarios` → logs e auditoria

### Exemplos em que `CASCADE` pode ser aceitável com cautela

- tabelas puramente associativas sem histórico relevante, como:
    - `usuarios_perfis`
    - `perfis_permissoes`
    - `assinatura_modulos`

---

# 16. Índices compostos estratégicos para relatórios

Como o SIGERD depende fortemente de filtros administrativos e operacionais, alguns índices compostos são especialmente relevantes.

### Administrativos

- `(conta_id, status_assinatura)`
- `(status_fatura, data_vencimento)`
- `(plano_id, status_assinatura)`

### Institucionais

- `(orgao_id, status_usuario)`
- `(orgao_id, status_unidade)`

### PLANCON

- `(orgao_id, status_plancon)`
- `(vigencia_inicio, vigencia_fim)`
- `(plancon_id, nivel_risco)` nas tabelas analíticas quando aplicável

### Incidentes

- `(orgao_id, status_incidente)`
- `(municipio, status_incidente)`
- `(data_hora_abertura, status_incidente)`
- `(incidente_id, data_hora_registro)`
- `(periodo_operacional_id, tipo_registro)`

### Auditoria

- `(usuario_id, data_hora)`
- `(modulo, acao, data_hora)`
- `(orgao_id, data_hora)`

---

# 17. Checks recomendados por domínio

## Financeiro

- valores >= 0
- datas finais não menores que datas iniciais

## Operacional

- período final >= período inicial
- vigência final >= vigência inicial
- duração real >= 0, quando houver campo derivado manual

## Estrutural

- impedir autorrelacionamento inválido simples
- impedir duplicidade lógica em códigos funcionais quando necessário

### Observação

Nem todos os checks complexos devem ser implementados no banco. Regras muito contextuais continuam pertencendo à aplicação. O banco deve proteger o essencial.

---

# 18. Riscos a evitar na estratégia de índices e constraints

Os principais erros a evitar são:

1. criar índices em excesso sem base no uso real;
2. deixar FKs críticas sem índice;
3. esquecer unique em tabelas associativas;
4. usar `CASCADE` em entidades com valor histórico;
5. não indexar colunas usadas em filtros recorrentes;
6. aplicar unique onde o negócio admite repetição histórica;
7. tentar resolver toda regra de negócio via constraint de banco;
8. deixar campos de status e período sem apoio de índice em módulos operacionais.

---

# 19. Conclusão técnica

A estratégia de índices e constraints do SIGERD precisa refletir a natureza do sistema: uma plataforma institucional com forte dependência de integridade referencial, filtros por escopo, histórico operacional, relatórios recorrentes e controle contratual. O desenho correto aqui exige equilíbrio.

As decisões mais importantes são estas:

- preservar o eixo **conta → órgão → unidade → usuário**;
- reforçar o eixo **plancon** e o eixo **incidente** com integridade formal;
- proteger tabelas associativas com unique composto;
- indexar status, período, conta, órgão e data nas áreas críticas;
- evitar exclusões destrutivas em dados históricos e operacionais.

Com este documento, a modelagem do banco do SIGERD já está suficientemente amadurecida para avançar para os documentos finais de consolidação ou para a transformação direta em schema físico.