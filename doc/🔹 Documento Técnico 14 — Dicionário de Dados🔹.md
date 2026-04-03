**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Dicionário de Dados  
**Objetivo:** definir e descrever os campos essenciais das tabelas do banco de dados do SIGERD, com seus nomes, significados, finalidades, tipos lógicos e observações de uso.

## 1. Finalidade do documento

Este documento estabelece o dicionário de dados do SIGERD. Seu objetivo é padronizar a interpretação dos campos do banco de dados, evitando ambiguidades semânticas, inconsistências de nomenclatura e erros de modelagem.

O dicionário de dados é o documento que transforma a estrutura técnica em linguagem compreensível para desenvolvimento, manutenção, testes, relatórios e governança do sistema. Ele responde, de forma explícita:

- que dado é esse;
- para que ele serve;
- onde ele pertence;
- que tipo de informação ele armazena;
- se é obrigatório ou opcional;
- quais observações de negócio devem ser respeitadas.

## 2. Escopo do dicionário

Este dicionário cobre os campos essenciais das tabelas principais do SIGERD, organizados pelos seguintes domínios:

- comercial / SaaS;
- institucional;
- identidade e acesso;
- PLANCON;
- núcleo operacional do PLANCON;
- incidentes / SCI-SCO;
- anexos e documentos;
- auditoria e conformidade.

## 3. Convenções adotadas

Para manter consistência, este dicionário usa as seguintes convenções:

**Nome do campo**  
Representa o nome técnico recomendado da coluna no banco.

**Tipo lógico**  
Representa o tipo conceitual do dado. A definição física exata pode variar entre `VARCHAR`, `TEXT`, `INT`, `BIGINT`, `DATE`, `DATETIME`, `BOOLEAN`, `DECIMAL` etc.

**Obrigatoriedade**  
Indica se o campo tende a ser:

- obrigatório;
- opcional;
- opcional condicionado ao fluxo.

**Descrição**  
Explica o significado e a função do campo.

**Observação**  
Traz regra de negócio, recomendação de uso ou cuidado de implementação.

## 4. Tipos lógicos de referência

Os principais tipos lógicos usados neste documento são:

- identificador;
- texto curto;
- texto médio;
- texto longo;
- número inteiro;
- número decimal;
- data;
- data/hora;
- booleano;
- enum/catálogo;
- referência externa (FK);
- arquivo/anexo;
- coordenada geográfica;
- JSON/estrutura complementar, quando necessário.

---

# 5. Dicionário de dados — Domínio comercial / SaaS

## 5.1. Tabela `contas`

### Campos essenciais

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador único da conta contratante.  
Observação: PK da tabela.

**nome_fantasia**  
Tipo lógico: texto curto  
Obrigatório: sim  
Descrição: nome público ou nome de exibição da conta contratante.

**razao_social**  
Tipo lógico: texto médio  
Obrigatório: opcional condicionado  
Descrição: razão social da entidade contratante, quando aplicável.

**tipo_pessoa**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: classifica a natureza da conta, como PF, PJ, órgão público, autarquia, fundação, ONG ou empresa privada.

**cpf_cnpj**  
Tipo lógico: texto curto  
Obrigatório: opcional condicionado  
Descrição: documento principal da conta contratante.

**email_principal**  
Tipo lógico: texto curto  
Obrigatório: sim  
Descrição: e-mail principal de contato da conta.

**telefone_principal**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: telefone principal da conta.

**nome_responsavel_contrato**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: nome do responsável contratual.

**cargo_responsavel_contrato**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: cargo do responsável contratual.

**endereco_logradouro**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: logradouro do endereço da conta.

**endereco_numero**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: número do endereço.

**endereco_bairro**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: bairro do endereço.

**endereco_municipio**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: município da conta.

**endereco_estado**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: estado da conta.

**endereco_cep**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: CEP da conta.

**site_oficial**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: site institucional da conta.

**logomarca**  
Tipo lógico: arquivo/anexo  
Obrigatório: opcional  
Descrição: logomarca institucional vinculada à conta.

**status_cadastro**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: situação cadastral da conta, como pré-cadastro, ativa, suspensa, cancelada ou inadimplente.

**data_cadastro**  
Tipo lógico: data/hora  
Obrigatório: sim  
Descrição: data e hora do cadastro inicial da conta.

**observacoes**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: observações administrativas ou comerciais sobre a conta.

---

## 5.2. Tabela `planos_assinatura`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador único do plano.

**nome_plano**  
Tipo lógico: texto curto  
Obrigatório: sim  
Descrição: nome técnico do plano.

**nome_comercial**  
Tipo lógico: texto curto  
Obrigatório: sim  
Descrição: nome de exibição comercial do plano.

**descricao_plano**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: descrição resumida do plano.

**tipo_plano**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: categoria do plano, como Start, Essencial, Profissional, Institucional, Enterprise ou Governo.

**publico_alvo**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: segmento principal para o qual o plano foi desenhado.

**valor_mensal**  
Tipo lógico: número decimal  
Obrigatório: sim  
Descrição: valor mensal do plano.

**valor_anual**  
Tipo lógico: número decimal  
Obrigatório: opcional  
Descrição: valor anual do plano.

**limite_usuarios**  
Tipo lógico: número inteiro  
Obrigatório: sim  
Descrição: quantidade máxima de usuários permitidos.

**limite_orgaos**  
Tipo lógico: número inteiro  
Obrigatório: opcional  
Descrição: quantidade máxima de órgãos permitidos.

**limite_unidades**  
Tipo lógico: número inteiro  
Obrigatório: opcional  
Descrição: quantidade máxima de unidades permitidas.

**limite_ocorrencias_mes**  
Tipo lógico: número inteiro  
Obrigatório: opcional  
Descrição: limite operacional mensal de ocorrências, se adotado.

**limite_plancon**  
Tipo lógico: número inteiro  
Obrigatório: opcional  
Descrição: quantidade máxima de planos de contingência permitidos.

**limite_armazenamento_gb**  
Tipo lógico: número decimal  
Obrigatório: opcional  
Descrição: limite de armazenamento do plano.

**suporte_tipo**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: modalidade de suporte oferecida.

**sla_atendimento**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: janela ou regra de SLA do plano.

**status_plano**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: situação do plano no catálogo comercial.

**ordem_exibicao**  
Tipo lógico: número inteiro  
Obrigatório: opcional  
Descrição: ordem de exibição na área pública.

---

## 5.3. Tabela `assinaturas`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador único da assinatura.

**conta_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: referência à conta contratante.

**plano_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: referência ao plano contratado.

**codigo_assinatura**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: código interno ou comercial da assinatura.

**data_inicio**  
Tipo lógico: data  
Obrigatório: sim  
Descrição: data de início da vigência.

**data_fim**  
Tipo lógico: data  
Obrigatório: opcional  
Descrição: data de término da vigência.

**ciclo_cobranca**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: frequência de cobrança, como mensal ou anual.

**valor_contratado**  
Tipo lógico: número decimal  
Obrigatório: sim  
Descrição: valor efetivamente contratado.

**desconto_aplicado**  
Tipo lógico: número decimal  
Obrigatório: opcional  
Descrição: desconto aplicado na contratação.

**forma_pagamento**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: forma de pagamento pactuada.

**status_assinatura**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: situação da assinatura, como trial, ativa, pendente, inadimplente, suspensa, cancelada ou encerrada.

**teste_gratis**  
Tipo lógico: booleano  
Obrigatório: sim  
Descrição: indica se a assinatura possui período de teste.

**data_inicio_teste**  
Tipo lógico: data  
Obrigatório: opcional  
Descrição: início do período de teste.

**data_fim_teste**  
Tipo lógico: data  
Obrigatório: opcional  
Descrição: fim do período de teste.

**renovacao_automatica**  
Tipo lógico: booleano  
Obrigatório: opcional  
Descrição: indica se a assinatura será renovada automaticamente.

**contrato_anexo**  
Tipo lógico: arquivo/anexo  
Obrigatório: opcional  
Descrição: documento contratual associado.

**aceite_termos**  
Tipo lógico: booleano  
Obrigatório: opcional  
Descrição: indica se houve aceite dos termos.

**aceite_lgpd**  
Tipo lógico: booleano  
Obrigatório: opcional  
Descrição: indica se houve aceite de LGPD.

**aceite_data**  
Tipo lógico: data/hora  
Obrigatório: opcional  
Descrição: data e hora do aceite.

**responsavel_aceite**  
Tipo lógico: texto médio ou referência  
Obrigatório: opcional  
Descrição: identifica o responsável pelo aceite.

**observacoes**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: observações sobre a assinatura.

---

## 5.4. Tabela `faturas`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador único da fatura.

**assinatura_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: referência à assinatura.

**competencia**  
Tipo lógico: texto curto  
Obrigatório: sim  
Descrição: competência financeira da cobrança.

**data_emissao**  
Tipo lógico: data  
Obrigatório: sim  
Descrição: data de emissão da fatura.

**data_vencimento**  
Tipo lógico: data  
Obrigatório: sim  
Descrição: data de vencimento.

**data_pagamento**  
Tipo lógico: data  
Obrigatório: opcional  
Descrição: data de pagamento efetivo.

**valor_bruto**  
Tipo lógico: número decimal  
Obrigatório: sim  
Descrição: valor bruto da cobrança.

**valor_desconto**  
Tipo lógico: número decimal  
Obrigatório: opcional  
Descrição: desconto aplicado.

**valor_liquido**  
Tipo lógico: número decimal  
Obrigatório: sim  
Descrição: valor líquido da cobrança.

**valor_pago**  
Tipo lógico: número decimal  
Obrigatório: opcional  
Descrição: valor efetivamente pago.

**forma_pagamento**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: forma de pagamento.

**meio_pagamento**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: meio operacional de pagamento.

**codigo_transacao**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: código da transação financeira.

**comprovante_pagamento**  
Tipo lógico: arquivo/anexo  
Obrigatório: opcional  
Descrição: comprovante associado.

**nota_fiscal_numero**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: número da nota fiscal.

**nota_fiscal_arquivo**  
Tipo lógico: arquivo/anexo  
Obrigatório: opcional  
Descrição: arquivo da nota fiscal.

**status_fatura**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: situação da fatura, como aberta, paga, vencida, cancelada, estornada ou em análise.

**observacoes**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: observações financeiras.

---

# 6. Dicionário de dados — Domínio institucional

## 6.1. Tabela `orgaos`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador único do órgão/instituição.

**conta_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: referência à conta contratante.

**nome_oficial**  
Tipo lógico: texto médio  
Obrigatório: sim  
Descrição: nome oficial da instituição operadora.

**nome_curto**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: nome reduzido do órgão.

**sigla**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: sigla institucional.

**tipo_instituicao**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: natureza da instituição, como Defesa Civil, Corpo de Bombeiros, Prefeitura, Secretaria, Universidade, ONG ou outra.

**esfera_administrativa**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: esfera de atuação, como municipal, estadual, federal, privada ou mista.

**cnpj**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: CNPJ institucional.

**codigo_ibge_municipio**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: código IBGE do município-sede.

**municipio_sede**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: município de referência.

**estado_sede**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: estado de referência.

**endereco**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: endereço institucional.

**telefone_institucional**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: telefone institucional.

**email_institucional**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: e-mail institucional.

**site**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: site institucional.

**autoridade_maxima_nome**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: autoridade máxima do órgão.

**autoridade_maxima_cargo**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: cargo da autoridade máxima.

**coordenador_nome**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: nome do coordenador responsável.

**coordenador_cargo**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: cargo do coordenador.

**coordenador_email**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: e-mail do coordenador.

**coordenador_telefone**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: telefone do coordenador.

**area_atuacao**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: descrição da área de atuação institucional.

**nivel_atuacao**  
Tipo lógico: enum/catálogo  
Obrigatório: opcional  
Descrição: nível de atuação, como local, regional, estadual ou nacional.

**status_orgao**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: situação do órgão dentro da plataforma.

**logotipo**  
Tipo lógico: arquivo/anexo  
Obrigatório: opcional  
Descrição: logotipo da instituição.

**brasao**  
Tipo lógico: arquivo/anexo  
Obrigatório: opcional  
Descrição: brasão institucional.

**observacoes**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: observações institucionais.

---

## 6.2. Tabela `unidades`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador único da unidade.

**orgao_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: referência ao órgão.

**nome_unidade**  
Tipo lógico: texto médio  
Obrigatório: sim  
Descrição: nome da unidade.

**sigla_unidade**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: sigla da unidade.

**tipo_unidade**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: tipo da unidade, como sede, regional, coordenadoria, COMPDEC, base operacional, sala de situação ou centro logístico.

**codigo_unidade**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: código interno da unidade.

**municipio**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: município da unidade.

**estado**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: estado da unidade.

**endereco**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: endereço da unidade.

**coordenadas**  
Tipo lógico: coordenada geográfica  
Obrigatório: opcional  
Descrição: localização geográfica da unidade.

**telefone**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: telefone da unidade.

**email**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: e-mail da unidade.

**responsavel_nome**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: responsável pela unidade.

**responsavel_cargo**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: cargo do responsável.

**responsavel_email**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: e-mail do responsável.

**responsavel_telefone**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: telefone do responsável.

**unidade_superior_id**  
Tipo lógico: referência externa  
Obrigatório: opcional  
Descrição: vínculo hierárquico com unidade superior.

**status_unidade**  
Tipo lógico: enum/catálogo  
Obrigatório: opcional  
Descrição: situação da unidade.

**observacoes**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: observações.

---

# 7. Dicionário de dados — Identidade e acesso

## 7.1. Tabela `usuarios`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador único do usuário.

**conta_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: conta à qual o usuário pertence.

**orgao_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: órgão principal do usuário.

**unidade_id**  
Tipo lógico: referência externa  
Obrigatório: opcional  
Descrição: unidade do usuário, quando aplicável.

**nome_completo**  
Tipo lógico: texto médio  
Obrigatório: sim  
Descrição: nome civil do usuário.

**nome_social**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: nome social do usuário.

**cpf**  
Tipo lógico: texto curto  
Obrigatório: opcional condicionado  
Descrição: CPF do usuário.

**matricula_funcional**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: matrícula institucional.

**cargo**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: cargo do usuário.

**funcao_sistema**  
Tipo lógico: enum/catálogo  
Obrigatório: opcional  
Descrição: função principal do usuário na plataforma.

**email_login**  
Tipo lógico: texto curto  
Obrigatório: sim  
Descrição: e-mail usado no login.

**email_institucional**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: e-mail institucional do usuário.

**telefone**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: telefone do usuário.

**whatsapp**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: WhatsApp do usuário.

**foto_perfil**  
Tipo lógico: arquivo/anexo  
Obrigatório: opcional  
Descrição: imagem de perfil.

**senha_hash**  
Tipo lógico: texto médio  
Obrigatório: sim  
Descrição: hash da senha do usuário.

**ultimo_login**  
Tipo lógico: data/hora  
Obrigatório: opcional  
Descrição: último acesso bem-sucedido.

**status_usuario**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: situação do usuário, como pendente, ativo, bloqueado, inativo ou excluído.

**exigir_troca_senha**  
Tipo lógico: booleano  
Obrigatório: opcional  
Descrição: indica obrigatoriedade de troca de senha.

**autenticacao_2fator**  
Tipo lógico: booleano  
Obrigatório: opcional  
Descrição: indica se 2FA está habilitado.

**idioma**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: idioma preferencial.

**fuso_horario**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: fuso horário do usuário.

**assinatura_digital_habilitada**  
Tipo lógico: booleano  
Obrigatório: opcional  
Descrição: indica habilitação de assinatura digital.

**certificado_vinculado**  
Tipo lógico: texto médio ou arquivo  
Obrigatório: opcional  
Descrição: referência ao certificado vinculado.

**data_cadastro**  
Tipo lógico: data/hora  
Obrigatório: sim  
Descrição: data de criação do usuário.

**observacoes**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: observações sobre o usuário.

---

## 7.2. Tabela `perfis`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador do perfil.

**nome_perfil**  
Tipo lógico: texto curto  
Obrigatório: sim  
Descrição: nome do perfil.

**descricao_perfil**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: descrição do perfil.

**nivel_hierarquico**  
Tipo lógico: número inteiro  
Obrigatório: opcional  
Descrição: nível hierárquico do perfil.

**escopo_acesso**  
Tipo lógico: enum/catálogo  
Obrigatório: opcional  
Descrição: escopo de atuação do perfil, como própria unidade, próprio órgão, municipal, regional, estadual, multiinstitucional ou global.

**status_perfil**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: situação do perfil.

---

## 7.3. Tabela `usuarios_perfis`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador do vínculo usuário-perfil.

**usuario_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: usuário vinculado.

**perfil_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: perfil vinculado.

**data_vinculo**  
Tipo lógico: data/hora  
Obrigatório: opcional  
Descrição: data do vínculo.

**status_vinculo**  
Tipo lógico: enum/catálogo  
Obrigatório: opcional  
Descrição: situação do vínculo.

---

# 8. Dicionário de dados — PLANCON

## 8.1. Tabela `plancons`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador único do plano.

**conta_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: conta vinculada.

**orgao_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: órgão responsável.

**unidade_id**  
Tipo lógico: referência externa  
Obrigatório: opcional  
Descrição: unidade vinculada.

**titulo_plano**  
Tipo lógico: texto médio  
Obrigatório: sim  
Descrição: título do plano.

**municipio_estado**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: município/estado de referência.

**versao_documento**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: versão atual resumida.

**data_elaboracao**  
Tipo lógico: data  
Obrigatório: opcional  
Descrição: data de elaboração.

**data_ultima_atualizacao**  
Tipo lógico: data  
Obrigatório: opcional  
Descrição: data da última atualização.

**responsavel_tecnico**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: responsável técnico pela elaboração.

**contato_institucional**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: contato institucional relacionado ao plano.

**vigencia_inicio**  
Tipo lógico: data  
Obrigatório: opcional  
Descrição: início da vigência.

**vigencia_fim**  
Tipo lógico: data  
Obrigatório: opcional  
Descrição: fim da vigência.

**area_abrangencia**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: área de abrangência do plano.

**tipo_desastre_principal**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: desastre principal.

**outros_desastres_associados**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: outros desastres associados.

**base_legal_utilizada**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: base legal utilizada no plano.

**objetivo_geral**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: objetivo geral do plano.

**objetivos_especificos**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: objetivos específicos.

**publico_alvo**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: público-alvo do plano.

**status_plancon**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: situação do plano, como ativo, em revisão, vencido ou equivalente.

**observacoes_gerais**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: observações gerais.

---

## 8.2. Tabela `plancon_territorios`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador do registro territorial.

**plancon_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: plano vinculado.

**nome_territorio**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: nome do território ou localidade.

**regiao_administrativa**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: região administrativa.

**populacao_total**  
Tipo lógico: número inteiro  
Obrigatório: opcional  
Descrição: população total.

**populacao_area_risco**  
Tipo lógico: número inteiro  
Obrigatório: opcional  
Descrição: população estimada em área de risco.

**comunidades_vulneraveis**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: comunidades vulneráveis.

**bairros_expostos**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: bairros ou localidades expostas.

**caracteristicas_geograficas**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: características geográficas.

**caracteristicas_climaticas**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: características climáticas.

**principais_vias_acesso**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: vias de acesso.

**areas_dificil_acesso**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: áreas de difícil acesso.

**infraestruturas_criticas**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: infraestruturas críticas existentes.

**unidades_saude**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: unidades de saúde existentes.

**escolas_predios_estrategicos**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: escolas e prédios públicos estratégicos.

**abrigos_existentes**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: abrigos existentes.

**mapa_area**  
Tipo lógico: arquivo/anexo  
Obrigatório: opcional  
Descrição: mapa da área.

**coordenadas_geograficas**  
Tipo lógico: coordenada geográfica  
Obrigatório: opcional  
Descrição: coordenadas do território.

**observacoes_territoriais**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: observações territoriais.

---

## 8.3. Tabela `plancon_riscos`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador do risco.

**plancon_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: plano vinculado.

**tipo_ameaca**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: tipo de ameaça.

**descricao_risco**  
Tipo lógico: texto longo  
Obrigatório: sim  
Descrição: descrição do risco.

**origem_risco**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: origem do risco.

**historico_ocorrencias**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: histórico de ocorrências.

**frequencia_ocorrencia**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: frequência do evento.

**periodo_sazonal**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: sazonalidade.

**areas_suscetiveis**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: áreas suscetíveis.

**populacao_exposta**  
Tipo lógico: texto longo ou número  
Obrigatório: opcional  
Descrição: população exposta.

**infraestruturas_expostas**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: infraestruturas expostas.

**vulnerabilidades_identificadas**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: vulnerabilidades identificadas.

**capacidade_local_resposta**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: capacidade local de resposta.

**probabilidade_evento**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: probabilidade do evento.

**impacto_potencial**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: impacto potencial.

**nivel_risco**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: nível de risco consolidado.

**fatores_agravantes**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: fatores agravantes.

**fatores_atenuantes**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: fatores atenuantes.

**fontes_informacao_utilizadas**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: fontes de informação utilizadas.

**responsavel_analise**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: responsável pela análise.

**data_analise**  
Tipo lógico: data  
Obrigatório: opcional  
Descrição: data da análise.

---

## 8.4. Tabela `plancon_cenarios`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador do cenário.

**plancon_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: plano vinculado.

**nome_cenario**  
Tipo lógico: texto curto  
Obrigatório: sim  
Descrição: nome do cenário.

**tipo_desastre_associado**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: tipo de desastre associado.

**descricao_cenario**  
Tipo lógico: texto longo  
Obrigatório: sim  
Descrição: descrição do cenário.

**evento_disparador**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: evento disparador do cenário.

**area_afetada_estimada**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: área afetada estimada.

**populacao_potencialmente_afetada**  
Tipo lógico: número inteiro ou texto  
Obrigatório: opcional  
Descrição: população potencialmente afetada.

**danos_humanos_esperados**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: danos humanos esperados.

**danos_materiais_esperados**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: danos materiais esperados.

**danos_ambientais_esperados**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: danos ambientais esperados.

**danos_sociais_esperados**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: danos sociais esperados.

**servicos_interrompidos**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: serviços essenciais passíveis de interrupção.

**tempo_evolucao_evento**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: tempo estimado de evolução.

**necessidades_iniciais**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: necessidades iniciais esperadas.

**prioridades_operacionais**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: prioridades operacionais do cenário.

**classificacao_cenario**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: classificação do cenário.

**observacoes_cenario**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: observações do cenário.

---

# 9. Dicionário de dados — Núcleo operacional do PLANCON

## 9.1. Tabela `plancon_estruturas_operacionais`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador da estrutura operacional.

**plancon_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: plano vinculado.

**nome_estrutura**  
Tipo lógico: texto médio  
Obrigatório: sim  
Descrição: nome da estrutura ativada.

**tipo_estrutura**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: tipo da estrutura.

**evento_relacionado**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: evento ou desastre relacionado.

**cenario_id**  
Tipo lógico: referência externa  
Obrigatório: opcional  
Descrição: cenário vinculado.

**data_ativacao**  
Tipo lógico: data  
Obrigatório: opcional  
Descrição: data de ativação.

**hora_ativacao**  
Tipo lógico: texto curto ou hora  
Obrigatório: opcional  
Descrição: hora de ativação.

**data_desativacao**  
Tipo lógico: data  
Obrigatório: opcional  
Descrição: data de desativação.

**hora_desativacao**  
Tipo lógico: texto curto ou hora  
Obrigatório: opcional  
Descrição: hora de desativação.

**status_estrutura**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: situação da estrutura.

**objetivo_operacional**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: objetivo operacional da ativação.

**base_legal_ativacao**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: ato ou base legal de ativação.

**local_funcionamento**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: local de funcionamento.

**coordenadas_local**  
Tipo lógico: coordenada geográfica  
Obrigatório: opcional  
Descrição: coordenadas do local.

**orgao_responsavel**  
Tipo lógico: texto médio ou referência  
Obrigatório: opcional  
Descrição: órgão responsável pela coordenação.

**autoridade_maxima_responsavel**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: autoridade máxima responsável.

**comandante_operacao**  
Tipo lógico: texto médio ou referência  
Obrigatório: opcional  
Descrição: comandante da operação.

**substituto_imediato**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: substituto imediato.

**nivel_ativacao**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: nível de ativação.

**periodo_operacional**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: período operacional resumido.

**turno_funcionamento**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: turno de funcionamento.

**area_abrangencia_operacao**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: área de abrangência da operação.

**observacoes_gerais**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: observações gerais.

---

## 9.2. Tabela `plancon_funcoes_equipes`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador da função/equipe.

**estrutura_operacional_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: estrutura operacional vinculada.

**nome_funcao**  
Tipo lógico: texto curto  
Obrigatório: sim  
Descrição: nome da função.

**codigo_funcao**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: código da função.

**categoria_funcao**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: categoria da função, como comando, coordenação, operações, logística, segurança etc.

**nivel_hierarquico**  
Tipo lógico: texto curto ou número  
Obrigatório: opcional  
Descrição: nível hierárquico da função.

**equipe_secao_vinculada**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: equipe ou seção vinculada.

**nome_responsavel_titular**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: responsável titular.

**cargo_funcao_institucional**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: cargo institucional.

**orgao_instituicao**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: órgão/instituição da função.

**matricula_identificacao_funcional**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: identificação funcional.

**telefone_principal**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: telefone principal.

**telefone_alternativo**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: telefone alternativo.

**email_institucional**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: e-mail institucional.

**nome_substituto_imediato**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: substituto imediato.

**periodo_turno_atuacao**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: período/turno de atuação.

**data_inicio_designacao**  
Tipo lógico: data  
Obrigatório: opcional  
Descrição: início da designação.

**hora_inicio_designacao**  
Tipo lógico: texto curto ou hora  
Obrigatório: opcional  
Descrição: hora de início.

**data_encerramento_designacao**  
Tipo lógico: data  
Obrigatório: opcional  
Descrição: encerramento da designação.

**hora_encerramento_designacao**  
Tipo lógico: texto curto ou hora  
Obrigatório: opcional  
Descrição: hora de encerramento.

**status_funcao**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: situação da função.

**atribuicoes_funcao**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: atribuições da função.

**competencias_especificas**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: competências específicas.

**limites_autoridade**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: limites de autoridade.

**superior_imediato**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: superior imediato.

**local_atuacao**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: local de atuação.

**observacoes_operacionais**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: observações operacionais.

---

# 10. Dicionário de dados — Incidentes / SCI-SCO

## 10.1. Tabela `incidentes`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador único do incidente.

**conta_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: conta vinculada.

**orgao_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: órgão responsável.

**unidade_id**  
Tipo lógico: referência externa  
Obrigatório: opcional  
Descrição: unidade vinculada.

**numero_ocorrencia**  
Tipo lógico: texto curto  
Obrigatório: sim  
Descrição: número operacional da ocorrência/incidente.

**nome_incidente**  
Tipo lógico: texto médio  
Obrigatório: sim  
Descrição: nome do incidente.

**tipo_ocorrencia**  
Tipo lógico: texto curto  
Obrigatório: sim  
Descrição: tipo de ocorrência.

**classificacao_inicial**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: classificação inicial.

**data_hora_acionamento**  
Tipo lógico: data/hora  
Obrigatório: opcional  
Descrição: data e hora do acionamento.

**data_hora_abertura**  
Tipo lógico: data/hora  
Obrigatório: sim  
Descrição: data e hora de abertura do incidente.

**municipio**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: município da ocorrência.

**local_detalhado**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: local detalhado.

**coordenadas**  
Tipo lógico: coordenada geográfica  
Obrigatório: opcional  
Descrição: coordenadas do incidente.

**orgao_primeira_informacao**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: órgão que recebeu a primeira informação.

**canal_recebimento**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: canal de recebimento da informação.

**comunicante**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: comunicante.

**descricao_inicial**  
Tipo lógico: texto longo  
Obrigatório: sim  
Descrição: descrição inicial.

**situacao_inicial_observada**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: situação inicial observada.

**populacao_potencialmente_afetada**  
Tipo lógico: número inteiro ou texto  
Obrigatório: opcional  
Descrição: população potencialmente afetada.

**danos_humanos_iniciais**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: danos humanos iniciais.

**danos_materiais_iniciais**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: danos materiais iniciais.

**danos_ambientais_iniciais**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: danos ambientais iniciais.

**riscos_imediatos**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: riscos imediatos.

**orgao_lider_inicial**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: órgão líder inicial.

**status_incidente**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: situação do incidente.

**plancon_id**  
Tipo lógico: referência externa  
Obrigatório: opcional  
Descrição: plano vinculado.

**cenario_id**  
Tipo lógico: referência externa  
Obrigatório: opcional  
Descrição: cenário vinculado.

---

## 10.2. Tabela `incidentes_briefing`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador do briefing.

**incidente_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: incidente vinculado.

**resumo_situacao**  
Tipo lógico: texto longo  
Obrigatório: sim  
Descrição: resumo da situação.

**mapa_croqui_inicial**  
Tipo lógico: arquivo/anexo  
Obrigatório: opcional  
Descrição: croqui ou mapa inicial.

**eventos_significativos**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: eventos significativos.

**objetivos_iniciais**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: objetivos iniciais.

**acoes_atuais**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: ações em andamento.

**recursos_alocados**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: recursos já alocados.

**recursos_solicitados**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: recursos solicitados.

**riscos_criticos_seguranca**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: riscos críticos de segurança.

**restricoes_operacionais**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: restrições operacionais.

**necessidades_imediatas**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: necessidades imediatas.

**responsavel_briefing**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: responsável pelo briefing.

**data_hora_briefing**  
Tipo lógico: data/hora  
Obrigatório: opcional  
Descrição: data/hora do briefing.

**uso_transferencia_comando**  
Tipo lógico: booleano  
Obrigatório: opcional  
Descrição: indica uso para transferência de comando.

**observacoes**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: observações.

---

## 10.3. Tabela `incidentes_comando`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador do registro de comando.

**incidente_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: incidente vinculado.

**tipo_comando**  
Tipo lógico: texto curto  
Obrigatório: sim  
Descrição: tipo de comando.

**comandante_incidente**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: comandante do incidente.

**instituicao_comandante**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: instituição do comandante.

**autoridade_legal**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: autoridade legal do comando.

**comando_unificado**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: composição do comando unificado.

**data_hora_assuncao**  
Tipo lógico: data/hora  
Obrigatório: opcional  
Descrição: data/hora de assunção.

**data_hora_transferencia**  
Tipo lógico: data/hora  
Obrigatório: opcional  
Descrição: data/hora de transferência.

**motivo_transferencia**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: motivo da transferência.

**base_legal_ativacao**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: base legal ou ato de ativação.

**local_posto_comando**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: local do posto de comando.

**status_comando**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: situação do comando.

**diretrizes_institucionais**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: diretrizes institucionais.

**restricoes_juridicas_operacionais**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: restrições jurídicas/operacionais.

**observacoes**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: observações.

---

## 10.4. Tabela `incidentes_periodos_operacionais`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador do período operacional.

**incidente_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: incidente vinculado.

**numero_periodo**  
Tipo lógico: número inteiro  
Obrigatório: sim  
Descrição: número sequencial do período.

**data_hora_inicio**  
Tipo lógico: data/hora  
Obrigatório: sim  
Descrição: início do período.

**data_hora_fim**  
Tipo lógico: data/hora  
Obrigatório: opcional  
Descrição: fim do período.

**situacao_inicial_periodo**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: situação inicial do período.

**objetivos_periodo**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: objetivos do período.

**recursos_principais_periodo**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: recursos principais do período.

**briefing_realizado**  
Tipo lógico: booleano  
Obrigatório: opcional  
Descrição: indica se o briefing foi realizado.

**pai_vinculado**  
Tipo lógico: referência externa ou texto  
Obrigatório: opcional  
Descrição: plano de ação vinculado.

**situacao_encerramento**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: situação ao encerramento.

**pendencias**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: pendências remanescentes.

**responsavel_aprovacao**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: responsável pela aprovação.

---

## 10.5. Tabela `incidentes_registros_operacionais`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador do registro operacional.

**incidente_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: incidente vinculado.

**periodo_operacional_id**  
Tipo lógico: referência externa  
Obrigatório: opcional  
Descrição: período operacional vinculado.

**data_hora_registro**  
Tipo lógico: data/hora  
Obrigatório: sim  
Descrição: data/hora do registro.

**tipo_registro**  
Tipo lógico: texto curto  
Obrigatório: sim  
Descrição: tipo do registro, como decisão, acionamento, ocorrência, mobilização ou atualização.

**titulo_registro**  
Tipo lógico: texto médio  
Obrigatório: sim  
Descrição: título do registro.

**descricao_registro**  
Tipo lógico: texto longo  
Obrigatório: sim  
Descrição: descrição detalhada.

**origem_informacao**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: origem da informação.

**responsavel_lancamento**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: responsável pelo lançamento.

**encaminhamento**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: encaminhamento definido.

**status_registro**  
Tipo lógico: enum/catálogo  
Obrigatório: sim  
Descrição: situação do registro, como aberto, em andamento, concluído, cancelado ou arquivado.

**evidencia_anexa**  
Tipo lógico: arquivo/anexo  
Obrigatório: opcional  
Descrição: evidência anexa principal.

**observacoes**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: observações complementares.

---

# 11. Dicionário de dados — Anexos, auditoria e conformidade

## 11.1. Tabela `anexos`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador do anexo.

**nome_original**  
Tipo lógico: texto médio  
Obrigatório: sim  
Descrição: nome original do arquivo.

**nome_interno**  
Tipo lógico: texto médio  
Obrigatório: sim  
Descrição: nome interno armazenado.

**tipo_arquivo**  
Tipo lógico: texto curto  
Obrigatório: sim  
Descrição: extensão ou tipo de arquivo.

**mime_type**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: MIME type do arquivo.

**tamanho_bytes**  
Tipo lógico: número inteiro  
Obrigatório: opcional  
Descrição: tamanho em bytes.

**caminho_armazenamento**  
Tipo lógico: texto longo  
Obrigatório: sim  
Descrição: caminho físico ou lógico do arquivo.

**modulo_origem**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: módulo que originou o anexo.

**usuario_envio_id**  
Tipo lógico: referência externa  
Obrigatório: opcional  
Descrição: usuário responsável pelo envio.

**data_envio**  
Tipo lógico: data/hora  
Obrigatório: sim  
Descrição: data/hora do envio.

**status_arquivo**  
Tipo lógico: enum/catálogo  
Obrigatório: opcional  
Descrição: situação do arquivo.

---

## 11.2. Tabela `logs_auditoria`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador do log de auditoria.

**conta_id**  
Tipo lógico: referência externa  
Obrigatório: opcional  
Descrição: conta relacionada.

**orgao_id**  
Tipo lógico: referência externa  
Obrigatório: opcional  
Descrição: órgão relacionado.

**unidade_id**  
Tipo lógico: referência externa  
Obrigatório: opcional  
Descrição: unidade relacionada.

**usuario_id**  
Tipo lógico: referência externa  
Obrigatório: opcional  
Descrição: usuário relacionado.

**tipo_evento**  
Tipo lógico: texto curto  
Obrigatório: sim  
Descrição: tipo de evento auditado.

**modulo**  
Tipo lógico: texto curto  
Obrigatório: sim  
Descrição: módulo de origem.

**acao**  
Tipo lógico: texto curto  
Obrigatório: sim  
Descrição: ação realizada.

**registro_afetado**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: identificador lógico do registro afetado.

**data_hora**  
Tipo lógico: data/hora  
Obrigatório: sim  
Descrição: data/hora do evento.

**ip_origem**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: IP de origem.

**dispositivo**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: dispositivo de origem.

**navegador**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: navegador/cliente utilizado.

**resultado**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: resultado da ação.

**detalhes**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: detalhes adicionais do evento.

---

## 11.3. Tabela `termos_aceites`

**id**  
Tipo lógico: identificador  
Obrigatório: sim  
Descrição: identificador do aceite.

**conta_id**  
Tipo lógico: referência externa  
Obrigatório: sim  
Descrição: conta vinculada.

**assinatura_id**  
Tipo lógico: referência externa  
Obrigatório: opcional  
Descrição: assinatura relacionada.

**termo_uso_aceito**  
Tipo lógico: booleano  
Obrigatório: opcional  
Descrição: aceite do termo de uso.

**politica_privacidade_aceita**  
Tipo lógico: booleano  
Obrigatório: opcional  
Descrição: aceite da política de privacidade.

**lgpd_aceita**  
Tipo lógico: booleano  
Obrigatório: opcional  
Descrição: aceite de LGPD.

**data_aceite**  
Tipo lógico: data/hora  
Obrigatório: sim  
Descrição: data/hora do aceite.

**ip_aceite**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: IP do aceite.

**usuario_responsavel_aceite**  
Tipo lógico: referência externa ou texto  
Obrigatório: opcional  
Descrição: usuário responsável.

**termo_versao**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: versão do termo.

**politica_versao**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: versão da política.

**base_legal_tratamento**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: base legal do tratamento.

**encarregado_dados_nome**  
Tipo lógico: texto médio  
Obrigatório: opcional  
Descrição: nome do encarregado de dados.

**encarregado_dados_email**  
Tipo lógico: texto curto  
Obrigatório: opcional  
Descrição: e-mail do encarregado.

**observacoes**  
Tipo lógico: texto longo  
Obrigatório: opcional  
Descrição: observações.

---

# 12. Observações críticas sobre o dicionário

Há quatro pontos estratégicos que precisam ser preservados.

O primeiro é manter a distinção entre **conta contratante** e **órgão operador**. Isso precisa aparecer tanto nas tabelas quanto nos campos.

O segundo é não reduzir PLANCON e Incidentes a registros simplificados. Os campos mostram que esses módulos têm densidade documental e operacional real.

O terceiro é que vários campos de pessoas responsáveis podem existir como texto e, ao mesmo tempo, opcionalmente como referência a usuário. Isso é importante porque nem todos os agentes envolvidos necessariamente terão login ativo na plataforma.

O quarto é que o dicionário precisa permanecer vivo. Quando a modelagem física avançar, este documento deverá ser refinado com:

- tipo físico exato;
- tamanho de campo;
- domínio de valores;
- default;
- unique;
- observações de indexação.

# 13. Conclusão técnica

O dicionário de dados do SIGERD consolida semanticamente a modelagem construída até aqui. Ele mostra que o sistema possui uma base de dados institucional e operacional sofisticada, coerente com sua proposta de SaaS para gestão de riscos, planos de contingência e gerenciamento de desastres.

Este documento já oferece base suficiente para que a equipe:

- implemente migrations ou schema físico;
- padronize nomenclatura;
- escreva services e repositories com semântica consistente;
- prepare constraints e índices;
- evite ambiguidades entre campos semelhantes.

O principal risco, daqui para frente, seria alterar a estrutura física sem refletir a mudança no dicionário. Isso geraria divergência entre documentação e banco real.