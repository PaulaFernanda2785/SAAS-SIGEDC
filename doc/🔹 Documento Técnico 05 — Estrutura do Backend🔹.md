**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Estrutura do Backend  
**Objetivo:** definir a organização técnica do backend do sistema, estabelecendo seus componentes, responsabilidades, fluxos, módulos, serviços, persistência, segurança e integração com as demais camadas da aplicação.

## 1. Finalidade do documento

Este documento estabelece a estrutura do backend do SIGERD, detalhando como a camada servidor deverá ser organizada para sustentar:

- a área pública comercial;
- a área administrativa SaaS;
- a área interna operacional;
- o módulo de planos de contingência;
- o módulo de gerenciamento de desastres;
- o controle institucional;
- a autenticação e autorização;
- os relatórios, logs, auditoria e conformidade.

O objetivo não é apenas “ter código no servidor”, mas criar um backend coerente com o porte do sistema, capaz de suportar regras institucionais, operação crítica, planos de assinatura e crescimento progressivo.

## 2. Papel do backend no SIGERD

No SIGERD, o backend é o núcleo executivo do sistema. É ele que garante que a plataforma deixe de ser apenas uma interface visual e passe a operar como ambiente institucional real.

O backend deverá ser responsável por:

- processar regras de negócio;
- validar dados e contexto;
- controlar autenticação e autorização;
- aplicar limites contratuais;
- manter integridade lógica entre módulos;
- registrar rastreabilidade operacional;
- persistir dados no banco;
- gerar respostas para páginas, relatórios, exports e integrações.

A consequência prática é simples: qualquer decisão importante do sistema deve ser garantida no backend, nunca apenas no frontend.

## 3. Princípios estruturais do backend

A estrutura do backend do SIGERD deverá obedecer aos seguintes princípios:

1. modularização por domínio de negócio;
2. controllers enxutos;
3. regras centrais concentradas em services;
4. persistência isolada em repositories ou camada equivalente;
5. validação técnica e funcional separadas;
6. controle de acesso executado no backend;
7. rastreabilidade por logs e auditoria;
8. independência relativa entre módulos;
9. coerência com a arquitetura em camadas;
10. crescimento progressivo sem colapso estrutural.

## 4. Visão geral da estrutura do backend

O backend do SIGERD deverá ser organizado em grandes blocos técnicos:

- núcleo de entrada e roteamento;
- núcleo de autenticação e sessão;
- núcleo de aplicação;
- núcleo de domínio;
- núcleo de persistência;
- núcleo de suporte técnico;
- núcleo de integrações e saída.

Essa estrutura é adequada porque separa o fluxo de entrada, o processamento do negócio, o armazenamento e os serviços de apoio.

## 5. Macroestrutura recomendada do backend

A estrutura do backend deverá ser composta, no mínimo, pelos seguintes grupos:

### 5.1. Núcleo de Entrada

Responsável por receber requisições, inicializar a aplicação e despachar o fluxo correto.

### 5.2. Núcleo de Controle de Acesso

Responsável por autenticação, autorização, sessão, escopo e validação de permissões.

### 5.3. Núcleo de Casos de Uso

Responsável por controllers, orquestração de fluxo e coordenação da ação solicitada.

### 5.4. Núcleo de Regras de Negócio

Responsável por services, validadores de negócio, políticas e transições de estado.

### 5.5. Núcleo de Persistência

Responsável por repositories, consultas, escrita em banco e abstração de armazenamento.

### 5.6. Núcleo de Serviços Técnicos

Responsável por PDF, exportações, e-mail, anexos, logs, auditoria, notificações e configuração.

### 5.7. Núcleo de Integrações

Responsável por conexões externas, APIs futuras, webhooks e sincronizações.

## 6. Estrutura lógica do fluxo backend

O fluxo padrão do backend deverá seguir esta cadeia:

**requisição → roteador → middleware → controller → service → repository → banco/infraestrutura → resposta**

Esse fluxo precisa ser adotado de forma consistente. O principal erro seria pular services e empurrar lógica diretamente para controllers ou consultas SQL dispersas.

## 7. Componentes centrais do backend

## 7.1. Bootstrap / Inicialização

O backend deverá possuir um ponto central de inicialização da aplicação.

### Responsabilidades:

- carregar configurações de ambiente;
- iniciar sessão;
- registrar autoload;
- inicializar container simples ou registrador de dependências, se adotado;
- configurar timezone;
- carregar rotas;
- ativar tratamento de erros;
- iniciar conexão com recursos essenciais.

Esse componente deve ser pequeno, previsível e sem regra de negócio.

## 7.2. Roteador

O backend deverá ter um roteador responsável por mapear URLs e métodos para ações internas.

### Responsabilidades:

- receber rota;
- resolver controller e método;
- separar áreas pública, administrativa e operacional;
- permitir agrupamento de rotas por módulo;
- aplicar middlewares de autenticação e permissão;
- suportar parâmetros dinâmicos e rotas nomeadas, se adotado.

### Diretriz técnica:

o roteador não deve executar regra de negócio. Ele só organiza o trânsito da requisição.

## 7.3. Middlewares

Os middlewares devem atuar como filtros técnicos ou institucionais antes da ação principal.

### Responsabilidades:

- verificar autenticação;
- verificar status da sessão;
- verificar área de acesso;
- verificar plano contratado;
- verificar módulo liberado;
- verificar escopo institucional;
- verificar CSRF, se adotado;
- registrar pré-eventos de auditoria sensível, quando aplicável.

### Observação crítica:

permissão não pode ser tratada apenas por menu escondido. O middleware e a validação posterior no domínio são obrigatórios.

## 7.4. Controllers

Os controllers devem representar a camada de entrada da aplicação.

### Responsabilidades:

- receber a requisição;
- extrair dados de entrada;
- delegar validação técnica;
- acionar service ou caso de uso;
- escolher resposta adequada;
- redirecionar ou retornar dados;
- acionar renderização de view ou export.

### O que controllers não devem fazer:

- conter regra de negócio extensa;
- montar SQL bruto;
- decidir limite de assinatura diretamente;
- implementar fluxo completo de incidente dentro do método;
- misturar lógica institucional, financeira e operacional num único bloco.

No SIGERD, controllers precisam ser enxutos. Se ficarem grandes, a arquitetura falha.

## 7.5. Requests / Validadores de entrada

É recomendável que o backend possua estruturas específicas para validação de entrada.

### Responsabilidades:

- validar campos obrigatórios;
- validar formatos;
- validar tipos;
- sanitizar entrada;
- padronizar mensagens;
- reduzir repetição de validação nos controllers.

### Diferença essencial:

- validação de entrada verifica se os dados “chegaram corretamente”;
- validação de domínio verifica se os dados “fazem sentido para o negócio”.

Essa separação é importante no SIGERD, especialmente em módulos como assinatura, incidentes, PLANCON e gestão institucional.

## 7.6. Services

Os services constituem o núcleo real do backend.

### Responsabilidades:

- aplicar regras de negócio;
- coordenar lógica entre entidades;
- validar transições de estado;
- orquestrar operações que envolvem múltiplos repositories;
- verificar permissões sensíveis;
- aplicar políticas contratuais;
- manter coerência operacional.

### Tipos de services recomendados:

- services de autenticação;
- services institucionais;
- services SaaS;
- services de PLANCON;
- services de incidentes;
- services de relatórios;
- services de anexos;
- services de auditoria;
- services de exportação.

No SIGERD, quase toda regra crítica deverá passar por service.

## 7.7. Policies e regras de acesso

O backend deverá possuir um mecanismo claro de policies ou validadores de autorização por domínio.

### Responsabilidades:

- verificar se o usuário pode acessar determinado recurso;
- validar escopo por conta, órgão, unidade e perfil;
- limitar ações de criação, edição, exclusão, aprovação, exportação e assinatura;
- diferenciar permissões administrativas, operacionais e comerciais.

### Exemplo de uso:

um usuário pode visualizar relatórios do próprio órgão, mas não de outro órgão; um administrador SaaS pode gerenciar assinaturas, mas não operar incidentes do cliente.

## 7.8. Repositories

Os repositories devem ser responsáveis pela persistência e recuperação estruturada de dados.

### Responsabilidades:

- encapsular consultas ao banco;
- recuperar entidades e coleções;
- persistir alterações;
- aplicar filtros de busca;
- reduzir SQL espalhado pelo sistema.

### Diretriz:

repositório não deve decidir regra de negócio. Ele consulta e persiste; quem decide o que é válido é o domínio.

## 7.9. Entidades e estruturas de domínio

O backend deverá possuir entidades ou modelos coerentes com os domínios do sistema.

### Principais grupos esperados:

- conta;
- órgão;
- unidade;
- usuário;
- perfil;
- plano;
- assinatura;
- fatura;
- módulo liberado;
- plano de contingência;
- risco;
- cenário;
- recurso;
- instalação;
- incidente;
- comando;
- período operacional;
- registro operacional;
- anexo;
- log de auditoria.

Essas entidades não precisam necessariamente ser ORM completo, mas precisam existir como estruturas reconhecíveis no backend.

## 7.10. Serviços técnicos auxiliares

Além do domínio, o backend precisará de serviços técnicos compartilhados.

### Serviços esperados:

- geração de PDF;
- exportação de planilhas;
- upload e armazenamento de arquivos;
- envio de e-mail;
- notificações internas;
- auditoria;
- logs técnicos;
- geração de relatórios;
- manipulação de imagens e anexos;
- tratamento de datas e timezones.

## 8. Estrutura do backend por domínio

## 8.1. Backend do domínio público

### Responsabilidades:

- carregar páginas públicas;
- consultar planos ativos;
- registrar leads de demonstração;
- registrar mensagens de contato;
- processar login inicial;
- servir conteúdo institucional.

### Característica:

backend leve, com menos densidade de regra, mas com cuidado em segurança, sanitização e persistência de leads.

## 8.2. Backend do domínio SaaS

### Responsabilidades:

- gerenciar contas contratantes;
- gerenciar órgãos;
- gerenciar unidades;
- gerenciar planos;
- gerenciar assinaturas;
- controlar limites por plano;
- bloquear ou liberar módulos;
- registrar faturas;
- controlar conformidade;
- alimentar dashboard administrativo.

### Ponto crítico:

esse backend não deve ser confundido com a operação do cliente. Seu foco é governança do negócio.

## 8.3. Backend do domínio institucional

### Responsabilidades:

- cadastrar e editar órgãos;
- cadastrar e editar unidades;
- cadastrar e editar usuários;
- atribuir perfis;
- controlar escopo;
- registrar status de usuário;
- manter coerência entre conta, órgão e unidade.

## 8.4. Backend do domínio PLANCON

### Responsabilidades:

- criar plano;
- versionar plano;
- validar vigência;
- gerenciar blocos do plano;
- registrar riscos, cenários e níveis de ativação;
- gerenciar governança;
- gerenciar recursos e rotas;
- gerenciar simulados e revisões;
- manter núcleo CSI/SCO do plano.

### Ponto crítico:

o PLANCON não deve ser tratado como um único cadastro gigante. O backend precisa operar em blocos ou submódulos.

## 8.5. Backend do domínio Incidentes

### Responsabilidades:

- abrir ocorrência;
- registrar briefing;
- gerir comando;
- gerir staff;
- gerir seções;
- gerir objetivos;
- gerir PAI;
- gerir operações de campo;
- gerir planejamento e situação;
- gerir recursos do incidente;
- gerir instalações;
- gerir comunicação;
- gerir segurança;
- gerir diário operacional;
- gerir desmobilização e encerramento.

### Ponto crítico:

esse é o domínio mais denso do backend e exige forte separação interna.

## 8.6. Backend do domínio Relatórios

### Responsabilidades:

- receber filtros;
- consolidar consultas;
- aplicar regras de escopo;
- montar tabelas e indicadores;
- exportar PDF e planilhas;
- alimentar painéis e gráficos.

## 8.7. Backend do domínio Auditoria e Conformidade

### Responsabilidades:

- registrar eventos de acesso;
- registrar eventos administrativos;
- registrar eventos operacionais relevantes;
- armazenar contexto do evento;
- registrar aceite de termos;
- consultar trilha de auditoria por escopo.

## 9. Estrutura interna recomendada para os services

Os services do backend do SIGERD podem ser organizados por domínio e por responsabilidade.

### Exemplo de organização lógica:

- `AuthService`
- `ContaService`
- `OrgaoService`
- `UnidadeService`
- `UsuarioService`
- `PerfilService`
- `PlanoAssinaturaService`
- `AssinaturaService`
- `FaturaService`
- `ModuloLiberadoService`
- `PlanconService`
- `RiscoService`
- `CenarioService`
- `AtivacaoOperacionalService`
- `GovernancaPlanconService`
- `IncidenteService`
- `BriefingIncidenteService`
- `ComandoIncidenteService`
- `PeriodoOperacionalService`
- `OperacaoCampoService`
- `RecursoIncidenteService`
- `RelatorioService`
- `ExportacaoService`
- `AuditoriaService`

A nomenclatura exata pode variar, mas a lógica de separação deve ser preservada.

## 10. Estrutura interna recomendada para os repositories

A estrutura dos repositories deve seguir os principais agregados de dados.

### Exemplo lógico:

- `ContaRepository`
- `OrgaoRepository`
- `UnidadeRepository`
- `UsuarioRepository`
- `PerfilRepository`
- `PlanoRepository`
- `AssinaturaRepository`
- `FaturaRepository`
- `PlanconRepository`
- `RiscoRepository`
- `CenarioRepository`
- `IncidenteRepository`
- `ComandoRepository`
- `PeriodoOperacionalRepository`
- `RegistroOperacionalRepository`
- `RelatorioRepository`
- `AuditoriaRepository`

A diretriz principal é evitar queries críticas espalhadas em controllers ou views.

## 11. Backend orientado por casos de uso

Uma abordagem recomendada para o SIGERD é estruturar o backend também por casos de uso críticos, mesmo que isso não apareça como camada formal separada.

### Casos de uso centrais:

- autenticar usuário;
- solicitar demonstração;
- cadastrar conta contratante;
- contratar assinatura;
- cadastrar órgão;
- cadastrar usuário;
- criar plano de contingência;
- revisar plano;
- ativar estrutura CSI/SCO;
- abrir incidente;
- transferir comando;
- abrir período operacional;
- registrar diário operacional;
- encerrar incidente;
- gerar relatório;
- exportar PDF;
- registrar auditoria.

Essa lógica ajuda a impedir que o backend seja modelado apenas como CRUD, o que seria uma simplificação inadequada para o SIGERD.

## 12. Fluxos críticos do backend

## 12.1. Fluxo de autenticação

- receber credenciais;
- validar entrada;
- localizar usuário;
- validar senha;
- verificar status;
- verificar vínculo contratual e módulo;
- abrir sessão;
- registrar log;
- redirecionar conforme contexto.

## 12.2. Fluxo de criação de assinatura

- validar conta;
- validar plano;
- validar limites;
- criar assinatura;
- registrar vigência;
- liberar módulos iniciais;
- registrar aceite e auditoria.

## 12.3. Fluxo de criação de plano de contingência

- validar escopo institucional;
- criar plano base;
- registrar identificação;
- permitir composição por blocos;
- salvar revisões;
- controlar status e vigência.

## 12.4. Fluxo de abertura de incidente

- validar permissão;
- validar contexto institucional;
- registrar dados iniciais;
- gerar número operacional;
- persistir abertura;
- registrar briefing inicial, se enviado;
- lançar auditoria;
- encaminhar para painel da ocorrência.

## 12.5. Fluxo de encerramento do incidente

- validar autoridade e perfil;
- verificar pendências críticas;
- registrar situação final;
- processar desmobilização;
- encerrar status;
- registrar log e histórico.

## 13. Regras estruturais do backend por área

## 13.1. Área pública

- backend mais leve;
- sem acesso a regras operacionais sensíveis;
- persistência controlada de formulários;
- foco em segurança de entrada e conversão.

## 13.2. Área administrativa

- backend com forte controle contratual;
- validação rígida de permissões;
- visibilidade global ou multi-institucional;
- logs obrigatórios de alteração sensível.

## 13.3. Área operacional

- backend mais robusto;
- regras institucionais e operacionais densas;
- validação por órgão, unidade, perfil e incidente;
- alta rastreabilidade.

## 14. Segurança estrutural do backend

O backend deverá garantir, no mínimo:

- autenticação obrigatória nas áreas internas;
- autorização por perfil e escopo;
- checagem de módulo liberado;
- checagem de status da assinatura;
- proteção contra acesso direto indevido a rotas;
- validação de uploads;
- tratamento de dados sensíveis;
- logs de ações críticas;
- segregação entre área administrativa e operacional.

### Ponto estratégico:

o menu visível ao usuário é apenas conforto de navegação. A segurança real deve existir no backend.

## 15. Tratamento de arquivos e anexos no backend

O SIGERD lida com anexos, documentos, evidências e possivelmente mapas, croquis, comprovantes e arquivos legais.

O backend deverá ter estrutura própria para:

- upload;
- validação de tipo e tamanho;
- armazenamento organizado por módulo;
- nomenclatura padronizada;
- associação do arquivo ao registro correto;
- controle de acesso ao download;
- exclusão segura ou inativação lógica quando necessário.

Isso é particularmente importante em:

- anexos do PLANCON;
- evidências do incidente;
- comprovantes de pagamento;
- documentos contratuais;
- relatórios exportados.

## 16. Geração de relatórios, PDF e exportações

O backend deverá possuir mecanismo próprio de exportação e geração documental.

### Responsabilidades:

- montar datasets com base em filtros autorizados;
- aplicar escopo institucional;
- gerar PDF;
- gerar planilhas;
- permitir impressão;
- manter padronização visual da saída;
- registrar auditoria das exportações sensíveis, quando necessário.

## 17. Logs, auditoria e observabilidade do backend

O backend deverá registrar eventos técnicos e funcionais relevantes.

### Tipos mínimos:

- logs de erro;
- logs de autenticação;
- logs de alteração sensível;
- logs operacionais críticos;
- trilha de auditoria de ações por usuário;
- aceite de termos e conformidade;
- eventos financeiros relevantes.

### Distinção importante:

- log técnico ajuda suporte e depuração;
- auditoria ajuda governança e responsabilização institucional.

## 18. Estrutura de crescimento do backend

A estrutura do backend do SIGERD deve permitir evolução sem ruptura.

### Fase inicial

- autenticação;
- contas e usuários;
- assinaturas básicas;
- incidentes;
- relatórios básicos;
- PLANCON inicial.

### Fase intermediária

- controle refinado de módulos;
- CSI/SCO completo;
- SCI/SCO completo;
- mapas operacionais mais ricos;
- auditoria ampliada;
- faturas e conformidade mais robustas.

### Fase avançada

- integrações externas;
- API pública ou privada;
- notificações automatizadas;
- assinatura digital avançada;
- analytics operacionais e administrativos mais sofisticados.

## 19. Erros estruturais que devem ser evitados

Há alguns erros de backend que precisam ser evitados desde o início:

1. controllers gigantes;
2. SQL espalhado pelo sistema;
3. regra de negócio dentro de view;
4. ausência de separação por domínio;
5. autenticação tratada sem política de escopo;
6. módulos sensíveis sem logs;
7. PLANCON tratado como formulário monolítico;
8. incidente tratado como simples cadastro sem ciclo operacional;
9. acoplamento direto entre faturamento e operação do cliente;
10. ausência de serviço central de auditoria.

## 20. Conclusão técnica

A estrutura do backend do SIGERD deve ser pensada como espinha dorsal do produto. É ela que sustenta a coerência entre comercialização SaaS, governança institucional, gestão do risco, gerenciamento de desastres, relatórios e rastreabilidade.

O backend não pode ser apenas um conjunto de páginas PHP conectadas ao banco. Para o porte e a ambição do SIGERD, ele precisa ser organizado por domínios, services, repositories, policies e fluxos controlados. Só assim o sistema conseguirá crescer sem perder consistência operacional nem segurança institucional.