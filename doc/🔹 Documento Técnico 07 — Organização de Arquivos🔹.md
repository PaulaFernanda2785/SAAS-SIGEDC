**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Organização de Arquivos  
**Objetivo:** definir o padrão de organização, distribuição, nomenclatura, agrupamento e gestão dos arquivos do projeto, garantindo coerência técnica, manutenção previsível, escalabilidade e governança documental.

## 1. Finalidade do documento

Este documento estabelece as diretrizes de organização de arquivos do SIGERD. Sua finalidade é garantir que os artefatos do projeto sejam mantidos de forma padronizada, compreensível e coerente com a arquitetura já definida.

O objetivo não é apenas evitar “bagunça no repositório”. O ponto central é impedir que a implementação se deteriore em arquivos soltos, nomes inconsistentes, duplicação de responsabilidade e mistura entre áreas pública, administrativa e operacional.

## 2. Objetivos da organização de arquivos

A organização de arquivos deverá atender aos seguintes objetivos:

- refletir a arquitetura do sistema;
- facilitar navegação e manutenção;
- reduzir duplicidade de código;
- separar arquivos por contexto e responsabilidade;
- melhorar rastreabilidade do desenvolvimento;
- facilitar testes, correções e evolução;
- proteger arquivos sensíveis;
- padronizar nomenclatura e localização dos artefatos.

## 3. Princípios de organização

A organização de arquivos do SIGERD deverá obedecer aos seguintes princípios:

1. um arquivo deve ter responsabilidade clara;
2. arquivos de contextos distintos não devem ser misturados;
3. arquivos compartilhados devem ser explicitamente centralizados;
4. arquivos sensíveis não devem ficar em área pública;
5. a nomenclatura deve ser previsível;
6. módulos complexos devem ser divididos em múltiplos arquivos;
7. a estrutura deve favorecer leitura por domínio e por camada;
8. documentação técnica, artefatos gerados e arquivos operacionais devem ser tratados de forma distinta.

## 4. Categorias de arquivos do projeto

Para o SIGERD, os arquivos do projeto devem ser tratados em grandes categorias:

- arquivos de aplicação;
- arquivos de interface;
- arquivos de configuração;
- arquivos de banco de dados;
- arquivos de ativos estáticos;
- arquivos gerados pelo sistema;
- arquivos anexados pelo usuário;
- arquivos de documentação;
- arquivos de teste;
- arquivos de dependência externa.

Essa separação é importante porque cada categoria possui ciclo de vida, responsabilidade e risco diferentes.

## 5. Organização de arquivos por contexto do produto

A organização dos arquivos deve respeitar os três contextos principais do SIGERD.

### 5.1. Arquivos da área pública

Devem conter:

- páginas institucionais;
- componentes visuais comerciais;
- formulários de demonstração e contato;
- apresentação dos planos;
- arquivos legais públicos;
- ativos visuais da marca.

### 5.2. Arquivos da área administrativa SaaS

Devem conter:

- gestão de contas;
- gestão de órgãos;
- gestão de assinaturas;
- faturamento;
- módulos liberados;
- relatórios administrativos;
- dashboard administrativo;
- conformidade e auditoria administrativa.

### 5.3. Arquivos da área operacional

Devem conter:

- painel operacional;
- incidentes;
- PLANCON;
- mapas;
- recursos;
- comunicações;
- relatórios operacionais;
- cadastro institucional;
- conta e segurança do usuário.

### Diretriz crítica

Esses três contextos não devem compartilhar arquivos de forma improvisada. O reaproveitamento deve ocorrer por componentes comuns, não por mistura estrutural.

## 6. Organização de arquivos por camada técnica

Além do contexto funcional, os arquivos devem obedecer à separação por camada:

- apresentação;
- aplicação;
- domínio;
- infraestrutura/persistência.

### Exemplo de organização coerente

Um módulo como incidente deve ter:

- arquivos de interface em `resources/views/operational/incidents/`;
- arquivos de entrada e fluxo em `app/Controllers/Operational/`;
- regras de negócio em `app/Services/Incident/` ou `app/Domain/Incident/`;
- persistência em `app/Repositories/Incident/`.

Essa organização evita acoplamento e torna a manutenção mais previsível.

## 7. Regra geral de responsabilidade por arquivo

Cada arquivo deve responder claramente a uma pergunta objetiva:

- esta classe controla fluxo?
- esta view renderiza interface?
- este service aplica regra de negócio?
- este repository acessa dados?
- este arquivo contém configuração?
- este arquivo é um componente compartilhado?
- este arquivo é um template de exportação?
- este arquivo é um anexo de usuário ou artefato gerado?

Se um mesmo arquivo tenta fazer duas ou três dessas coisas ao mesmo tempo, a organização está errada.

## 8. Organização dos arquivos de backend

## 8.1. Controllers

Os arquivos de controller devem ser organizados por contexto e módulo.

### Regra:

um controller deve representar um ponto de entrada do fluxo, não um depósito de regras.

### Organização recomendada:

- `Public/` para páginas públicas;
- `Auth/` para autenticação;
- `Admin/` para administração SaaS;
- `Operational/` para operação institucional.

### Exemplos coerentes:

- `PlanController.php`
- `SubscriptionController.php`
- `IncidentController.php`
- `PlanconController.php`
- `OperationalReportController.php`

### Diretriz:

evitar controller genérico como `SystemController.php` para tudo. Isso rapidamente degrada o projeto.

## 8.2. Services

Os arquivos de service devem ser organizados por domínio de negócio.

### Regra:

um service deve encapsular regra, coordenação de entidades e transição de estados.

### Exemplos coerentes:

- `AuthService.php`
- `AccountService.php`
- `SubscriptionService.php`
- `PlanconService.php`
- `IncidentService.php`
- `OperationalPeriodService.php`
- `AuditService.php`

### Diretriz crítica:

não concentrar toda a regra de um domínio complexo em um único arquivo gigante. Em módulos densos, o service deve ser subdividido por responsabilidade.

## 8.3. Repositories

Os arquivos de repository devem seguir a mesma lógica dos agregados principais do banco.

### Regra:

um repository deve organizar acesso a dados, não decidir lógica do negócio.

### Exemplos coerentes:

- `AccountRepository.php`
- `OrganizationRepository.php`
- `PlanRepository.php`
- `IncidentRepository.php`
- `AuditLogRepository.php`

## 8.4. Policies, middlewares e requests

Esses arquivos devem ficar separados dos controllers e services, pois possuem função técnica específica.

### Policies

Arquivos de autorização por recurso e ação.

### Middlewares

Arquivos de filtro prévio de acesso, sessão, módulo e área.

### Requests

Arquivos de validação de entrada e sanitização.

### Diretriz

não misturar validação de entrada com regra de negócio profunda. Isso gera confusão e duplicação.

## 9. Organização dos arquivos de interface

## 9.1. Layouts

Arquivos de layout devem ficar centralizados e separados por contexto.

### Recomendado:

- layout público;
- layout administrativo;
- layout operacional.

### Finalidade:

- cabeçalho;
- rodapé;
- menu;
- estrutura base;
- scripts e estilos comuns do contexto.

## 9.2. Componentes

Os componentes devem ser organizados por tipo e reuso.

### Exemplos:

- header;
- sidebar;
- footer;
- cards;
- tabelas;
- filtros;
- modais;
- gráficos;
- mapas.

### Regra:

se um componente é compartilhado entre várias telas, ele não deve ser duplicado em cada página.

## 9.3. Views por módulo

Cada módulo deve ter sua própria pasta de views.

### Exemplo:

- `admin/subscriptions/`
- `operational/incidents/`
- `operational/plancon/`
- `operational/reports/`

### Diretriz:

views de módulos densos devem ser divididas em arquivos menores por seção, aba ou bloco funcional.

## 10. Organização dos arquivos do módulo PLANCON

O PLANCON exige organização de arquivos mais granular porque foi concebido em múltiplos blocos funcionais no documento-base.

### Regra de organização

O módulo não deve ser tratado como um formulário único nem como uma página única com centenas de campos.

### Organização recomendada:

- arquivo índice do módulo;
- arquivo de criação;
- arquivo de edição;
- arquivo de visualização;
- subpasta `blocks/`;
- subpastas específicas para núcleo CSI/SCO.

### Exemplo lógico de distribuição:

- identificação geral;
- caracterização do território;
- análise de riscos;
- cenários;
- ativação operacional;
- governança;
- recursos;
- monitoramento e comunicação;
- procedimentos;
- rotas e abrigos;
- assistência;
- simulados;
- revisão;
- anexos;
- estrutura CSI/SCO;
- equipes;
- instalações;
- períodos;
- registros.

### Diretriz crítica

Cada bloco deve ter arquivos próprios de interface e, quando necessário, service/repository específicos. Isso melhora manutenção e reduz risco de quebrar o plano inteiro em uma alteração localizada.

## 11. Organização dos arquivos do módulo Incidentes / SCI-SCO

O módulo de incidentes é ainda mais crítico, porque concentra operação em tempo real, rastreabilidade e múltiplas subestruturas de comando.

### Regra de organização

O módulo não deve ser comprimido num único controller, numa única view ou num único arquivo de lógica.

### Organização recomendada por submódulo:

- abertura do incidente;
- briefing inicial;
- comando;
- staff do comando;
- staff geral;
- objetivos;
- estratégias e PAI;
- operações de campo;
- planejamento e situação;
- recursos;
- instalações;
- comunicações;
- segurança;
- informação pública;
- ligação interinstitucional;
- administração e finanças;
- períodos operacionais;
- registros operacionais;
- desmobilização.

### Diretriz

Cada um desses blocos deve ter arquivos de interface e backend coerentes com sua responsabilidade. Isso é essencial para não transformar o SCI/SCO em um “cadastro único disfarçado”.

## 12. Organização de arquivos compartilhados

O SIGERD inevitavelmente terá arquivos compartilhados. O problema não é compartilhar; o problema é compartilhar sem critério.

### Arquivos que podem ser compartilhados:

- layouts base;
- componentes visuais;
- helpers;
- utilitários de data e string;
- responses padrão;
- serviços transversais de exportação;
- serviços de auditoria;
- arquivos de configuração;
- enums e catálogos gerais.

### Regra:

arquivos compartilhados devem ficar em localização explícita de compartilhamento, e não copiados em cada módulo.

## 13. Padrão de nomenclatura de arquivos

A nomenclatura dos arquivos deve ser consistente em todo o projeto.

## 13.1. Arquivos de classe

Devem usar **PascalCase**, com nome igual ao da classe.

### Exemplos:

- `IncidentController.php`
- `PlanconService.php`
- `AuditLogRepository.php`

## 13.2. Arquivos de view

Podem usar `kebab-case` ou `snake_case`, mas o padrão deve ser único em todo o projeto.

### Recomendação prática:

usar `kebab-case` em views e arquivos de interface.

### Exemplos:

- `create-incident.php`
- `operational-dashboard.php`
- `forgot-password.php`

## 13.3. Arquivos de configuração

Devem ter nomes curtos e descritivos.

### Exemplos:

- `database.php`
- `auth.php`
- `session.php`
- `plans.php`

## 13.4. Arquivos de exportação e templates

Devem indicar claramente o tipo de saída.

### Exemplos:

- `incident-report-pdf.php`
- `plancon-summary-pdf.php`
- `invoice-template.php`

## 14. Organização de arquivos gerados pelo sistema

Os arquivos gerados pelo sistema devem ser tratados separadamente do código-fonte.

### Exemplos de arquivos gerados:

- PDFs;
- planilhas;
- CSVs;
- relatórios temporários;
- documentos exportados;
- comprovantes gerados;
- caches temporários.

### Regra:

esses arquivos devem ficar em `storage/`, nunca misturados com os arquivos da aplicação.

### Subdivisão recomendada:

- relatórios administrativos;
- relatórios operacionais;
- exports financeiros;
- PDFs de incidentes;
- PDFs de PLANCON;
- arquivos temporários.

## 15. Organização dos anexos enviados pelos usuários

Os anexos dos usuários precisam de organização por domínio e por tipo documental.

### Exemplos de anexos:

- anexos de contratos;
- comprovantes de pagamento;
- anexos do plano;
- mapas;
- croquis;
- evidências do incidente;
- imagens;
- documentos institucionais;
- termos assinados.

### Regra estrutural:

os anexos devem ser organizados por módulo e subcategoria, com nomenclatura padronizada e associação segura ao registro correspondente.

### Diretriz crítica

Não utilizar uma pasta única e genérica como `uploads/arquivos/` para tudo. Isso compromete governança, segurança e manutenção.

## 16. Organização de arquivos de documentação

O projeto deverá manter documentação técnica separada do código de execução.

### Recomendação:

criar pasta específica para documentação do projeto, quando necessário, por exemplo:

docs/  
├── architecture/  
├── database/  
├── requirements/  
├── flows/  
├── api/  
└── decisions/

### Tipos de documentos:

- documentos técnicos;
- diagramas;
- regras de negócio;
- decisões arquiteturais;
- especificações de integração;
- manuais internos de implantação.

### Benefício

Isso evita que conhecimento do projeto fique espalhado em conversas, arquivos avulsos ou comentários incompletos no código.

## 17. Organização de arquivos de banco de dados

Os arquivos de banco devem ser organizados por finalidade.

### Recomendação:

- `schema/` para estrutura consolidada;
- `migrations/` para evolução incremental;
- `seeds/` para dados iniciais;
- `scripts/` para utilitários e ajustes controlados.

### Diretriz

Nunca misturar script de correção pontual, dump aleatório e estrutura oficial no mesmo diretório sem classificação.

## 18. Organização de arquivos de ativos estáticos

Os ativos estáticos devem ser organizados por contexto e tipo.

### CSS

Separar:

- público;
- administrativo;
- operacional;
- compartilhado.

### JS

Separar:

- público;
- administrativo;
- operacional;
- compartilhado.

### Imagens

Separar:

- branding;
- público;
- administrativo;
- operacional;
- ícones;
- mapas e ilustrações.

### Diretriz

Evitar um diretório único de assets com centenas de arquivos sem contexto.

## 19. Organização de arquivos sensíveis

Alguns arquivos do SIGERD exigem cuidado especial:

- `.env`;
- arquivos contratuais;
- comprovantes;
- logs sensíveis;
- anexos institucionais;
- evidências do incidente;
- relatórios protegidos;
- arquivos temporários com dados operacionais.

### Regras obrigatórias:

1. não expor em pasta pública;
2. não permitir acesso direto irrestrito;
3. controlar download por backend;
4. aplicar permissões de acesso por perfil e escopo;
5. prever rastreabilidade em arquivos sensíveis, quando aplicável.

## 20. Regras de versionamento e manutenção de arquivos

A organização dos arquivos deve considerar evolução do sistema.

### Diretrizes:

- evitar renomeações desnecessárias;
- evitar duplicação de arquivo para “testar outra versão”;
- usar versionamento no repositório, não no nome do arquivo;
- usar histórico técnico/documental para mudanças estruturais;
- reservar sufixos de versão apenas para artefatos funcionais que exigem coexistência real, como templates ou documentos normativos específicos.

### Exemplo do que evitar:

- `incidentControllerNovo.php`
- `plancon-final-agora-v2.php`
- `layout_definitivo_ok.php`

Esse tipo de prática destrói legibilidade e governança do repositório.

## 21. Regras para modularização de arquivos grandes

Quando um arquivo crescer excessivamente, ele deve ser decomposto.

### Sinais de que precisa ser dividido:

- controller com dezenas de métodos não relacionados;
- service com responsabilidades demais;
- view com muitos blocos independentes;
- template de relatório com múltiplos contextos;
- arquivo JS com comportamentos de vários módulos diferentes.

### Estratégia

Dividir por:

- submódulo;
- caso de uso;
- bloco funcional;
- componente reutilizável;
- tipo de saída.

## 22. Organização de arquivos de relatório e exportação

Os arquivos relacionados a relatórios devem ser separados por natureza.

### Categorias recomendadas:

- relatórios administrativos;
- relatórios operacionais;
- relatórios financeiros;
- relatórios de PLANCON;
- relatórios do incidente;
- templates PDF;
- exportações XLSX/CSV.

### Diretriz

Não misturar lógica de geração, consulta e template visual no mesmo arquivo quando o relatório for crítico ou complexo.

## 23. Organização de arquivos de log e auditoria

Os logs técnicos em arquivo devem ser separados dos registros de auditoria persistidos em banco.

### Logs técnicos

Servem para:

- depuração;
- suporte;
- análise de erro;
- monitoramento do sistema.

### Auditoria

Serve para:

- rastreabilidade funcional;
- responsabilização;
- conformidade institucional;
- histórico de ações sensíveis.

### Regra

Não tratar auditoria apenas como log técnico. Ela é parte do negócio.

## 24. Erros de organização de arquivos que devem ser evitados

Os principais erros a evitar no SIGERD são:

1. misturar áreas pública, administrativa e operacional nos mesmos diretórios;
2. criar arquivos genéricos demais, sem contexto;
3. concentrar múltiplos módulos em um único arquivo;
4. duplicar componentes compartilhados em vez de centralizá-los;
5. usar nomes inconsistentes;
6. armazenar anexos de naturezas diferentes no mesmo local sem classificação;
7. manter arquivos temporários ou gerados junto do código-fonte;
8. deixar arquivos sensíveis em pasta pública;
9. usar nomes improvisados de versão;
10. transformar CRUDs complexos em arquivos monolíticos.

## 25. Conclusão técnica

A organização de arquivos do SIGERD deve funcionar como extensão natural da sua arquitetura. Ela precisa refletir claramente:

- os três contextos do produto;
- as quatro camadas técnicas;
- os domínios principais do sistema;
- a diferença entre código-fonte, ativos, anexos, documentos e artefatos gerados;
- a necessidade de rastreabilidade, governança e escalabilidade.

No caso do SIGERD, isso é ainda mais relevante porque o sistema lida simultaneamente com operação institucional, contratos SaaS, documentos técnicos, registros operacionais, evidências, relatórios e módulos altamente estruturados como PLANCON e SCI/SCO. Uma organização fraca de arquivos levaria rapidamente a acoplamento, risco operacional e perda de governança técnica.