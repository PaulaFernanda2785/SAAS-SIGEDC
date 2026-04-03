**Sistema:** SIGERD — Sistema Integrado de Gerenciamento de Riscos e Desastres  
**Tipo de documento:** Padrão de Layout e Navegação  
**Objetivo:** definir o padrão visual-estrutural das páginas, a hierarquia de navegação, a distribuição dos elementos de interface e a lógica de circulação do usuário nas áreas pública, administrativa e operacional do sistema.

## 1. Finalidade do documento

Este documento estabelece o padrão de layout e navegação do SIGERD. Seu propósito é garantir coerência visual, previsibilidade de uso, legibilidade funcional e fluxo adequado entre os módulos do sistema.

A finalidade não é apenas definir “como a tela deve parecer”, mas estruturar:

- como o usuário entende onde está;
- como encontra o que precisa;
- como executa ações;
- como diferencia áreas e contextos;
- como navega com segurança entre módulos de natureza distinta.

No SIGERD, isso é especialmente crítico porque o sistema possui três ambientes com objetivos completamente diferentes:

- ambiente público comercial;
- ambiente administrativo SaaS;
- ambiente operacional institucional.

## 2. Objetivos do padrão de layout e navegação

O padrão de layout e navegação deverá atender aos seguintes objetivos:

- garantir coerência entre páginas;
- reduzir esforço cognitivo do usuário;
- diferenciar claramente os contextos do sistema;
- facilitar acesso a ações críticas;
- melhorar leitura situacional em ambiente operacional;
- padronizar estrutura de cabeçalho, menu, conteúdo e rodapé;
- facilitar manutenção visual e frontend;
- permitir uso responsivo em múltiplos dispositivos.

## 3. Princípios gerais de layout

O layout do SIGERD deverá seguir os seguintes princípios:

1. clareza antes de ornamentação;
2. hierarquia visual evidente;
3. coerência estrutural entre telas do mesmo contexto;
4. distinção explícita entre público, administrativo e operacional;
5. foco em leitura e ação;
6. redução de ruído visual em módulos críticos;
7. modularidade visual;
8. responsividade funcional;
9. previsibilidade na posição de menus, filtros e ações;
10. apoio à decisão em telas operacionais.

## 4. Princípios gerais de navegação

A navegação do SIGERD deverá seguir os seguintes princípios:

1. o usuário deve saber em que área está;
2. o usuário deve saber em que módulo está;
3. o sistema deve indicar o caminho percorrido;
4. ações principais devem estar visíveis sem excesso de cliques;
5. itens sensíveis devem ser segregados por contexto;
6. menus devem refletir a arquitetura funcional do sistema;
7. fluxos devem ser consistentes entre telas equivalentes;
8. navegação deve funcionar em desktop e mobile sem perda crítica de entendimento.

---

# 5. Estrutura macro de layout do sistema

O SIGERD deverá adotar três padrões principais de layout, um para cada contexto do produto.

## 5.1. Layout da área pública

Objetivo: apresentação institucional, conversão comercial e acesso.

### Características:

- aparência aberta e atrativa;
- foco em comunicação de valor;
- navegação superior horizontal;
- seções longas e explicativas;
- CTAs evidentes;
- menor densidade de ferramentas operacionais.

## 5.2. Layout da área administrativa

Objetivo: gestão do negócio SaaS.

### Características:

- aparência mais técnica e gerencial;
- foco em tabelas, cards, filtros e indicadores;
- menu lateral estruturado;
- dashboard administrativo como ponto central;
- ações administrativas organizadas por módulos.

## 5.3. Layout da área operacional

Objetivo: operação institucional, leitura situacional e gestão de incidentes/plano.

### Características:

- foco em rapidez de leitura;
- maior prioridade para indicadores, status, mapa e registros recentes;
- navegação lateral clara;
- títulos diretos;
- filtros consistentes;
- redução de excesso decorativo;
- organização por blocos de trabalho.

---

# 6. Padrão de layout da área pública

## 6.1. Cabeçalho público

O cabeçalho da área pública deverá conter, no mínimo:

- logomarca do sistema;
- menu principal;
- botão “Solicitar demonstração”;
- botão “Entrar”;
- botão “Assinar agora”, quando aplicável.

### Requisitos de layout

- posição fixa ou semifiixa no topo, conforme estratégia visual;
- leitura limpa;
- espaçamento confortável;
- destaque para CTA principal;
- comportamento responsivo com menu recolhível no mobile.

## 6.2. Menu público

O menu principal deverá conter:

- Início
- Solução
- Funcionalidades
- Planos
- Demonstração
- Sobre o sistema
- Contato
- Acessar plataforma

### Requisitos de navegação

- rolagem ou navegação por âncora quando a página for one-page;
- ou navegação entre páginas públicas específicas;
- indicação do item ativo, quando aplicável.

## 6.3. Corpo da página pública

A página pública deverá seguir hierarquia de seções bem definida:

1. hero principal;
2. dor do problema;
3. solução do sistema;
4. módulos em destaque;
5. benefícios institucionais;
6. planos;
7. prova de valor;
8. chamada final para ação.

### Requisito de layout

cada seção deve possuir:

- título claro;
- subtítulo ou apoio textual;
- componente visual coerente;
- espaçamento consistente;
- leitura progressiva.

## 6.4. Rodapé público

O rodapé público deverá conter:

- logo;
- descrição institucional curta;
- links rápidos;
- planos;
- política de privacidade;
- termos de uso;
- contato;
- redes sociais, se houver;
- copyright.

---

# 7. Padrão de layout da área administrativa SaaS

## 7.1. Estrutura base

A área administrativa deverá utilizar layout interno padrão composto por:

- cabeçalho fixo ou estável;
- menu lateral administrativo;
- área central de conteúdo;
- rodapé interno discreto.

## 7.2. Cabeçalho administrativo

O cabeçalho administrativo deverá conter:

- logo compacta;
- nome do sistema;
- busca global, quando aplicável;
- notificações;
- atalhos rápidos;
- perfil do usuário;
- ação de sair.

### Requisitos visuais

- altura fixa;
- aparência limpa;
- baixo ruído visual;
- alinhamento consistente entre ícones e ações.

## 7.3. Menu lateral administrativo

O menu lateral administrativo deverá conter, no mínimo:

- Dashboard administrativo
- Contas contratantes
- Órgãos e instituições
- Assinaturas
- Planos
- Pagamentos / Faturas
- Módulos liberados
- Usuários
- Relatórios administrativos
- Configurações
- Logs / Auditoria

### Requisitos de navegação

- agrupamento por contexto;
- ícone + texto;
- destaque do item ativo;
- possibilidade de recolhimento;
- adaptação para telas menores.

## 7.4. Corpo das páginas administrativas

Toda página administrativa deverá, preferencialmente, seguir esta hierarquia:

1. título da página;
2. breadcrumb;
3. faixa de ações rápidas;
4. filtros;
5. cards de indicadores, quando aplicável;
6. tabela, gráfico ou formulário principal;
7. rodapé técnico ou paginação.

### Benefício

Esse padrão reduz improvisação visual e melhora manutenção entre módulos.

## 7.5. Rodapé administrativo

O rodapé administrativo deverá conter:

- versão do sistema;
- ambiente;
- copyright;
- link de suporte ou referência institucional interna.

---

# 8. Padrão de layout da área operacional

## 8.1. Estrutura base operacional

A área operacional deverá utilizar layout interno orientado à ação, com:

- cabeçalho funcional;
- menu lateral operacional;
- barra de contexto da página;
- conteúdo central organizado por blocos;
- rodapé técnico discreto.

## 8.2. Cabeçalho operacional

O cabeçalho operacional deverá conter:

- logo do sistema;
- nome do módulo atual;
- busca rápida, quando aplicável;
- notificações;
- status do incidente ativo ou contexto operacional relevante;
- seletor de órgão/unidade, quando permitido;
- perfil do usuário.

### Requisito crítico

O cabeçalho operacional deve reforçar contexto. O usuário precisa perceber rapidamente:

- onde está;
- em qual órgão/unidade está operando;
- se há incidente ativo ou contexto selecionado.

## 8.3. Menu lateral operacional

O menu lateral operacional deverá conter:

- Painel inicial
- Ocorrências / Incidentes
- Gerenciamento do desastre
- Mapa operacional
- Recursos
- Comunicações
- Relatórios de ocorrências
- Planos de contingência
- Relatórios de PLANCON
- Usuários / Órgãos / Instituições
- Alterar senha
- Configurações

### Requisito de usabilidade

Esse menu não deve parecer um menu administrativo SaaS. Ele deve refletir fluxo operacional.

## 8.4. Barra de contexto da página

Toda página operacional deverá conter logo abaixo do cabeçalho:

- título da página;
- breadcrumb;
- ações rápidas da página;
- possível identificação do incidente ou plano corrente.

### Exemplo

Em um incidente:

- nome do incidente;
- status;
- período operacional atual;
- órgão responsável;
- botão de registro rápido;
- botão de atualização;
- botão de exportação, se permitido.

---

# 9. Padrão do corpo das páginas internas

Independentemente da área administrativa ou operacional, as páginas internas deverão adotar uma estrutura-base consistente.

## 9.1. Ordem recomendada dos blocos

1. título da página
2. breadcrumb
3. barra de ações rápidas
4. filtros, quando existirem
5. cards de resumo
6. conteúdo principal
7. tabelas, gráficos ou formulários
8. paginação/rodapé do módulo

## 9.2. Título da página

O título deve:

- identificar o módulo;
- ser curto e direto;
- evitar nomes excessivamente longos;
- refletir o contexto real da tela.

## 9.3. Breadcrumb

O breadcrumb deve:

- indicar onde o usuário está;
- permitir retorno lógico;
- refletir a hierarquia de navegação;
- não ser decorativo sem função.

### Exemplo

Painel > Incidentes > Incidente #024/2026 > Períodos Operacionais

## 9.4. Barra de ações rápidas

Deve conter apenas ações relevantes do contexto.

### Exemplos

- novo incidente;
- novo plano;
- exportar;
- atualizar;
- encerrar;
- revisar;
- aprovar;
- anexar documento.

### Regra

Não congestionar a barra com ações secundárias demais.

---

# 10. Padrão de formulários

## 10.1. Regras gerais

Os formulários do SIGERD deverão ser:

- organizados por blocos;
- responsivos;
- com labels claros;
- com indicação de obrigatoriedade;
- com mensagens de validação consistentes;
- com agrupamento lógico dos campos.

## 10.2. Formulários simples

Formulários simples podem ser apresentados em página única.

### Exemplos

- login;
- alterar senha;
- cadastro de plano comercial;
- cadastro de perfil.

## 10.3. Formulários densos

Formulários densos deverão ser segmentados por:

- abas;
- etapas;
- seções;
- accordions;
- blocos salvos parcialmente.

### Aplicação obrigatória

Principalmente em:

- PLANCON;
- Incidentes;
- cadastro institucional mais complexo;
- estruturas operacionais.

## 10.4. Ações do formulário

Os formulários deverão apresentar de forma consistente:

- salvar;
- cancelar;
- voltar;
- salvar e continuar, quando aplicável.

---

# 11. Padrão de listagens e tabelas

## 11.1. Estrutura das listagens

Toda listagem principal deverá conter, quando aplicável:

- título;
- filtros;
- resumo de quantidade;
- tabela principal;
- paginação;
- ações por linha.

## 11.2. Requisitos das tabelas

As tabelas deverão apresentar:

- colunas relevantes;
- ordenação clara;
- ações coerentes;
- leitura confortável;
- responsividade aceitável;
- comportamento previsível.

## 11.3. Ações por linha

As ações mais comuns deverão ser apresentadas como:

- visualizar;
- editar;
- exportar;
- imprimir;
- abrir mapa;
- encerrar/cancelar, quando autorizado.

## 11.4. Tabelas em mobile

Em telas menores, a tabela poderá:

- colapsar colunas secundárias;
- priorizar colunas críticas;
- converter parte da linha em cartão resumido, se necessário.

---

# 12. Padrão de cards e indicadores

## 12.1. Uso de cards

Cards deverão ser usados para:

- indicadores;
- status resumidos;
- blocos de destaque;
- leitura rápida.

## 12.2. Regras de uso

Cards não devem competir com tabelas e gráficos na mesma área sem hierarquia.

### Regra

Primeiro os indicadores principais.  
Depois os detalhes.

## 12.3. Aplicação por contexto

- área pública: cards de valor, funcionalidades, planos;
- área administrativa: cards de receita, assinaturas, inadimplência;
- área operacional: cards de incidentes ativos, recursos mobilizados, pessoas afetadas, alertas críticos.

---

# 13. Padrão de gráficos e visualização analítica

## 13.1. Regras gerais

Gráficos deverão ser usados para complementar leitura, não para substituir tabelas estruturadas.

## 13.2. Aplicações recomendadas

- evolução de assinaturas;
- receita por período;
- ocorrências por tipologia;
- ocorrências por município;
- evolução temporal de incidentes;
- distribuição de recursos;
- indicadores operacionais.

## 13.3. Regras de layout

Gráficos devem ter:

- título;
- legenda quando necessária;
- escala legível;
- leitura clara;
- espaço adequado;
- não competir visualmente com muitos outros gráficos simultâneos.

---

# 14. Padrão do mapa operacional

## 14.1. Papel do mapa

O mapa não deve ser um adorno visual. Ele é componente operacional central do sistema.

## 14.2. Posição recomendada

No painel operacional, o mapa deve ocupar posição de destaque, preferencialmente no bloco central principal.

## 14.3. Conteúdos exibíveis

O mapa poderá exibir:

- ocorrências georreferenciadas;
- áreas afetadas;
- instalações;
- abrigos;
- recursos;
- pontos de apoio;
- camadas de risco.

## 14.4. Regras de interação

O mapa deverá permitir:

- leitura do contexto atual;
- alternância de camadas;
- abertura de detalhe;
- atualização coerente com filtros, quando aplicável.

---

# 15. Navegação entre módulos

## 15.1. Navegação pública

Fluxo principal:  
Home → Solução → Funcionalidades → Planos → Demonstração → Contato → Login

## 15.2. Navegação administrativa

Fluxo principal:  
Dashboard → Contas → Órgãos → Assinaturas → Planos → Faturas → Módulos → Usuários → Relatórios → Configurações → Logs

## 15.3. Navegação operacional

Fluxo principal:  
Painel → Incidentes → Gerenciamento do desastre → Mapa → Recursos → Comunicações → Relatórios → PLANCON → Cadastro institucional → Conta e segurança

## 15.4. Regra de navegação contextual

O sistema deverá permitir que o usuário entre em um módulo e aprofunde a navegação sem perder o contexto geral.

### Exemplo

Incidentes → Incidente X → Períodos Operacionais → Registro Operacional → Evidências

---

# 16. Padrão de navegação por contexto de registro

## 16.1. Em incidentes

O usuário deverá poder navegar, a partir do incidente, para:

- briefing;
- comando;
- períodos;
- objetivos;
- operações;
- registros;
- recursos;
- instalações;
- segurança;
- desmobilização.

### Recomendação

Essa navegação pode ocorrer por abas internas ou submenu contextual do incidente.

## 16.2. Em PLANCON

O usuário deverá poder navegar, a partir do plano, para:

- identificação;
- território;
- riscos;
- cenários;
- ativação;
- governança;
- recursos;
- monitoramento;
- procedimentos;
- rotas/abrigos;
- assistência;
- simulados;
- revisão;
- anexos;
- núcleo CSI/SCO.

### Recomendação

Usar navegação por abas ou menu lateral interno do plano.

---

# 17. Padrão de estados visuais

O sistema deverá comunicar visualmente estados importantes.

## 17.1. Estados a sinalizar

- ativo;
- inativo;
- bloqueado;
- pendente;
- em revisão;
- encerrado;
- cancelado;
- crítico;
- concluído.

## 17.2. Aplicação

Esses estados devem aparecer em:

- badges;
- etiquetas;
- cards;
- linhas de tabela;
- cabeçalhos de contexto.

## 17.3. Regra

O estado visual deve ser claro, mas não excessivamente agressivo ou confuso.

---

# 18. Padrão de feedback ao usuário

## 18.1. Mensagens de sistema

O sistema deverá apresentar mensagens padronizadas para:

- sucesso;
- erro;
- aviso;
- bloqueio;
- confirmação.

## 18.2. Regras

As mensagens devem:

- dizer o que aconteceu;
- indicar o impacto;
- orientar o próximo passo quando fizer sentido.

## 18.3. Ações sensíveis

Ações como exclusão lógica, encerramento, cancelamento, bloqueio e aprovação devem possuir confirmação explícita.

---

# 19. Padrão de responsividade

## 19.1. Em desktop

Layout completo com:

- menu lateral fixo/recolhível;
- blocos simultâneos;
- visão ampla de indicadores e tabelas.

## 19.2. Em tablet

Layout adaptado com:

- reorganização de colunas;
- priorização de blocos principais;
- menus compactos.

## 19.3. Em mobile

Layout compacto com:

- menu recolhível;
- cards verticais;
- redução de colunas;
- formulários em pilha;
- ações principais acessíveis sem poluição.

---

# 20. Padrão de identidade visual entre áreas

Embora o documento específico de identidade visual trate cores, tipografia e branding, o padrão de layout deve respeitar uma linha comum.

## 20.1. Elementos comuns

- marca consistente;
- tipografia coerente;
- espaçamento padronizado;
- componentes reutilizáveis;
- sistema visual uniforme.

## 20.2. Diferenças intencionais

- área pública mais institucional/comercial;
- área administrativa mais analítica/gerencial;
- área operacional mais objetiva/funcional.

---

# 21. Erros de layout e navegação que devem ser evitados

Os principais erros a evitar no SIGERD são:

1. usar o mesmo padrão visual para as três áreas sem distinção de contexto;
2. criar menus muito extensos sem agrupamento lógico;
3. transformar formulários densos em páginas únicas enormes;
4. ocultar o contexto do usuário, órgão ou incidente;
5. sobrecarregar a área operacional com estética em detrimento de clareza;
6. colocar ações críticas em locais imprevisíveis;
7. duplicar caminhos de navegação sem consistência;
8. permitir que o usuário se perca dentro de um incidente ou de um plano;
9. criar dashboards visualmente ricos, mas operacionalmente confusos;
10. negligenciar adaptação para mobile.

---

# 22. Conclusão técnica

O padrão de layout e navegação do SIGERD precisa refletir a natureza real do produto. Não basta que o sistema seja “bonito”. Ele precisa ser:

- claro para o visitante;
- organizado para o administrador SaaS;
- rápido e legível para o operador institucional.

A decisão mais importante deste documento é esta: o layout e a navegação devem seguir a arquitetura funcional do sistema. Isso significa que menus, cabeçalhos, páginas, formulários, tabelas, dashboards e mapas precisam respeitar os três contextos do produto e a densidade distinta de cada módulo.

No SIGERD, uma boa navegação não é um detalhe de UX. Ela é parte da capacidade de operação, da redução de erro e da percepção de controle institucional.