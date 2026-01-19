# Análise de Dados de Pacientes (Laravel 11 + Blade + Bootstrap 5) bbb

Sistema web para análise epidemiológica/histopatológica de laudos armazenados **exclusivamente** em JSON (`storage/app/dadospacientes.json`). O dashboard normaliza os dados em memória (Collections), permite visualizar tabelas e estatísticas e inclui parser robusto de tamanho de pólipos.

## Objetivo

- Ler o JSON e gerar dashboards/tabelas de análise epidemiológica/histopatológica.
- Normalizar múltiplos laudos por paciente e múltiplos diagnósticos por registro.
- Classificar histologia, atipia/displasia e tamanho de pólipos em memória.

## Stack

- Laravel 11
- Blade
- Bootstrap 5 (via Vite)
- Sem banco SQL, sem migrations

## Estrutura principal

```
app/
  Http/Controllers/
    DashboardController.php
  Services/
    LaudoJsonRepository.php
    LaudoAnalyzer.php
resources/
  views/
    layouts/app.blade.php
    dashboard.blade.php
  scss/app.scss
routes/
  web.php
storage/
  app/dadospacientes.json
```

## Como executar

> **Pré-requisitos:** PHP 8.2+, Composer, Node.js 18+

1. Instale dependências PHP:
   ```bash
   composer install
   ```
2. Instale dependências front-end:
   ```bash
   npm install
   ```
3. Gere o build de assets:
   ```bash
   npm run dev
   ```
4. Inicie o servidor:
   ```bash
   php artisan serve
   ```
5. Acesse: `http://localhost:8000/dashboard`

## GitHub Pages (publicação estática)

O GitHub Pages não executa PHP/Laravel. Para publicar o dashboard como site estático, há um build pronto em `docs/` que carrega o dataset e reproduz as tabelas diretamente no navegador.

1. Garanta que o arquivo `docs/dadospacientes.json` esteja atualizado com base em `storage/app/dadospacientes.json`.
2. No GitHub, configure **Settings → Pages** para servir a partir de `docs/` (recomendado). Caso esteja apontando para o root do repositório, o `index.html` da raiz redireciona para `docs/`.
3. Acesse a URL do GitHub Pages (ex.: `https://<usuario>.github.io/<repositorio>/`).

## Dataset (JSON)

O arquivo é editado manualmente e deve permanecer em:

```
storage/app/dadospacientes.json
```

> Observação: o Laravel 11 usa `storage/app/private` como disco local padrão. O repositório lê diretamente de `storage/app` para respeitar o caminho exigido.

### Exemplo de JSON

```json
[
  {
    "paciente": {
      "nome": "Maria Souza",
      "idade": 54,
      "prontuario": "12345",
      "sexo": "Feminino"
    },
    "laudo": {
      "peca": "Polipectomia",
      "data": "2024-06-12",
      "cid": "D12"
    },
    "material": "Peça polipoide com 8 mm.",
    "localizacao": "Cólon descendente",
    "diagnosticos": [
      "Adenoma tubular com displasia de baixo grau",
      "Margens livres"
    ],
    "atipia": "Ausente",
    "displasia": "Baixo grau"
  }
]
```

### Campos aceitos (por registro)

- `paciente`: `{ nome, idade, prontuario, sexo }`
- `laudo`: `{ peca, data, cid }`
- `material`: string
- `localizacao`: string
- `diagnostico`: string (opcional)
- `diagnosticos`: array de strings (opcional)
- `atipia`: string (opcional)
- `displasia`: string (opcional)
- outros campos textuais (opcionais)

> **Observação:** quando existir `diagnosticos`, ele tem prioridade. Caso contrário, `diagnostico` é convertido para array.

## Rotas

- `/` → redireciona para `/dashboard`
- `/dashboard` → painel principal

## Funcionalidades do dashboard

1. **Tabela Geral**
   - Listagem completa com diagnóstico(s), atipia/displasia, classificação histológica, grau de atipia e tamanho do pólipo.
   - Busca client-side simples.
   - Paginação simples com `LengthAwarePaginator`.

2. **Tabela por Sexo**
   - Percentuais de polipectomias por sexo.
   - Mostra alerta caso o dataset não tenha sexo em todos os registros.

3. **Tabela de Idade**
   - Média, mediana, mais jovem, mais velho e total de pacientes únicos.
   - Consolidação por prontuário (ou nome quando prontuário não existe).

4. **Tabela de Tipo Histológico**
   - Classes: **Pólipo**, **Inflamatório/Não neoplásico**, **Adenocarcinoma/Câncer**, **Indefinido**.
   - Percentuais sobre o total geral e sobre registros com lesões.

5. **Tabela de Graus de Atipia**
   - Classes: **Alto**, **Moderado**, **Baixo**, **Ausente**, **Não informado**.

## Regras de classificação

### Histologia (resumo)

- **Adenocarcinoma/Câncer**: termos como `adenocarcinoma`, `carcinoma`, `neoplasia maligna`.
- **Pólipo**: termos como `pólipo`, `polipectomia`, `mucosectomia`, `adenoma`.
- **Inflamatório/Não neoplásico**: termos como `gastrite`, `inflam`, `mucosa`, `hiperplasia`, `colite`.

### Atipia/Displasia (resumo)

- **Ausente**: `ausente`, `sem atipia`.
- **Alto grau**: `alto grau`, `pouco diferenciado`.
- **Moderado**: `moderada`, `moderadamente diferenciado`, `médio`.
- **Baixo grau**: `leve`, `baixo grau`, `bem diferenciado`.

### Tamanho do pólipo (maior eixo)

O parser busca **todas** as medidas em `mm` ou `cm` em qualquer campo textual do item, por exemplo:
- `8 mm`, `08mm`
- `03 mm x 04 mm x 08 mm`
- `3x4x8mm`
- `1,2 cm`

Sempre usa o **maior valor** encontrado (convertendo cm para mm).

**Categorias:**
- `2.1 até 5 mm`
- `5 a 9 mm`
- `10 mm ou mais`
- `Não informado` (quando nada é encontrado)

## Bootstrap + Vite

O Bootstrap 5 é carregado via Vite, com SCSS em `resources/scss/app.scss` e entry JS em `resources/js/app.js`. O layout principal está em `resources/views/layouts/app.blade.php`.

## Observações importantes

- Sem banco de dados: todo processamento é feito em memória.
- Se o JSON estiver vazio ou inválido, o dashboard mostra a tabela vazia.
- O sistema é resiliente a campos ausentes.

## Dicas de manutenção

- Sempre mantenha o JSON válido (sem vírgulas extras).
- Para testes rápidos, substitua o conteúdo do arquivo em `storage/app/dadospacientes.json`.

## Licença

MIT.





Essas são algumas informações de uma sistema de analise de dados de pacientes;
Quero que extraia as informações desta imagem de laudo de acordo com as informações do sistema em um objeto json;
Siga sempre em todos os laudo o formato padrão do objeto json;

Alguns pacientes tem mais de uma peça histológica;

Quero que o sistema tenha:

1 - Uma tabela geral com os dados de todos os pacientes;
2 - Uma segunda tabela de sexo que defina as porcentagens das polipectomias no sexo feminino e no sexo masculino;
3 - Uma terceira tabela de idade dos pacientes que calcule a idade média, mediana e os extremos de idade de todos os pacientes ( O paciente mais jovem e o paciente mais velho identificado na tabela);
4 - Uma quarta tabela de tipo histológico contendo todos os pólipos, os inflamatórios e adenocarcinoma, pois alguns pólipos são câncer;
5 - Uma quinta tabela de Graus de Atipia, se é alto, médio ou baixo que é aquele moderadamente diferenciado, pouco diferenciado, displasia de alto grau, de baixo grau ou moderada;

Observação:

1 - O tamanho do Pólipo nem sempre tá escrito como Pólipo, às vezes vem escrito como Peça, Polipectomia, Mucosectomia, "Foram recebidas peças com as seguintes característica", Biópsia ( exemplo: Biópsia: 03 mm x 04 mm x 08 mm; nesse caso se usa o 08 mm, sempre o maior eixo);

2 - É dividido em três categorias:

2.1 - até 5 mm;
2.2 - de 5 mm a 9 mm;
2.3 - 10 mm ou mais;

Quando um laudo for identificado como duplicado (mesmo paciente + prontuário + número da peça) NÃO será gerado JSON;

A partir de agora, cada laudo terá DUAS CAMADAS no JSON:

1 - Camada estruturada (normalizada) → usada para cálculos, tabelas e estatísticas;
2 - Camada literal (bruta) → texto exatamente como consta no laudo, sem interpretação;

Formato padrão do objeto json:

{
  "paciente": {
    "estruturado": {
      "nome": "",
      "sexo": "",
      "idade": 0,
      "prontuario": ""
    },
    "literal": {
      "nome": "",
      "idade": "",
      "prontuario": "",
      "enfermaria": ""
    }
  },
  "exame": {
    "estruturado": {
      "tipo": "",
      "material": "",
      "data_laudo": "",
      "instituicao": ""
    },
    "literal": {
      "material": "",
      "medico_solicitante": "",
      "local": "",
      "data": ""
    }
  },
  "macroscopia": {
    "estruturado": {
      "fragmentos": 0,
      "aspecto": "",
      "dimensao_maior_cm": ""
    },
    "literal": ""
  },
  "microscopia_conclusao": {
    "estruturado": {
      "localizacao": "",
      "diagnostico": "",
      "grau_displasia": ""
    },
    "literal": ""
  },
  "classificacao": {
    "cid": ""
  },
  "laudo_literal_completo": {
    "numero_peca": "",
    "assinatura": {
      "medico": "",
      "crm": ""
    }
  }
},



















========================== PROMPT CODEX PARA CRIAR O SISTEMA ==========================

Você é o Codex. Crie um sistema completo em Laravel 11 + Blade usando Bootstrap 5 via Vite para analisar laudos de pacientes armazenados SOMENTE em um arquivo JSON (sem banco SQL). O arquivo JSON é editado manualmente e fica em: storage/app/dadospacientes.json.

OBJETIVO
- Ler o JSON e gerar dashboards/tabelas de análise epidemiológica/histopatológica.
- Alguns pacientes podem ter mais de uma peça histológica (múltiplos laudos/entradas no JSON) e alguns laudos podem conter múltiplos diagnósticos (campo "diagnosticos": array), além do campo "diagnostico": string.
- O sistema deve normalizar os dados em memória (Collections) e produzir tabelas e estatísticas.

STACK E REQUISITOS
- Laravel 11
- Blade
- Bootstrap 5 com Vite (SCSS opcional)
- Rotas web somente (sem API obrigatória)
- Sem migrations e sem banco de dados
- Leitura do JSON via Storage facade
- Painel principal em /dashboard
- Layout com navbar, container, cards e tabelas responsivas

FORMATO DO JSON (baseado no arquivo de exemplo)
Cada item do array representa UM REGISTRO DE LAUDO, com chaves possíveis:
- paciente: { nome, idade, prontuario }
- laudo: { peca, data, cid }
- material: string (às vezes descreve pólipo/biopsia/peca etc.)
- localizacao: string (ex.: "Cólon descendente", "reto", "sigmoide/reto", etc.)
- diagnostico: string (opcional)
- diagnosticos: array de strings (opcional)
- atipia: string (opcional) (ex.: "Ausente")
- displasia: string (opcional) (ex.: "Leve", "Moderada", "Leve e focal", "displasia de baixo grau"...)
- outros campos opcionais (helicobacter_pylori, margens etc.)

PÁGINAS/TELAS (5 blocos conforme solicitado)
1) Tabela Geral (Todos os pacientes/laudos)
- Tabela com colunas:
  - Prontuário
  - Nome
  - Idade
  - Sexo (se existir no JSON; se não existir, mostrar "Não informado")
  - Peça (laudo.peca)
  - Data do laudo
  - CID
  - Material
  - Localização
  - Diagnóstico(s) (se "diagnosticos" existir, listar em bullets; senão usar "diagnostico")
  - Atipia/Displasia (exibir o que existir)
  - Classificação Histológica (resultado do parser, ver regras)
  - Grau de Atipia (resultado do parser, ver regras)
  - Categoria de Tamanho do Pólipo (resultado do parser, ver regras)
- Suporte a busca (client-side simples com JS) e paginação simples (server-side opcional usando LengthAwarePaginator manual).

2) Tabela por Sexo (percentuais de polipectomias)
- Objetivo: percentuais de polipectomias em Feminino vs Masculino.
- Regra: contar PROCEDIMENTOS relacionados a pólipo (polipectomia/mucosectomia/pólipo/peça/biopsia quando for de pólipo) por sexo.
- Se o JSON não tiver sexo, a tabela deve aparecer mas com "Não informado" e alertar no topo que sexo não está no dataset.

3) Tabela de Idade (média, mediana e extremos)
- Calcular:
  - média de idades
  - mediana de idades
  - menor idade + nome do paciente (mais jovem)
  - maior idade + nome do paciente (mais velho)
- Considerar a idade em cada registro; se um mesmo paciente aparecer várias vezes, consolidar por prontuário (se existir) ou por nome: usar a primeira idade encontrada para aquele paciente e ignorar duplicadas para estatística de pacientes únicos.
- Também exibir N total de pacientes únicos.

4) Tabela de Tipo Histológico
- Classificar cada registro em uma das classes:
  A) PÓLIPO (benigno/neoplásico sem carcinoma)
  B) INFLAMATÓRIO / NÃO NEOPLÁSICO (ex.: mucosa com infiltrado, gastrite, tecido de granulação, hiperplásico etc.)
  C) ADENOCARCINOMA / CÂNCER (qualquer menção a adenocarcinoma/carcinoma/neoplasia maligna)
- Exibir contagens e percentuais (sobre total de registros relacionados a lesões/pólipos e também sobre total geral; deixar claro no cabeçalho).
- Regras de parsing devem olhar material + diagnostico(s) + cid quando útil.

5) Tabela de Graus de Atipia
- Objetivo: classificar grau de atipia/displasia em:
  - Alto grau
  - Moderado / Médio
  - Baixo grau
  - Ausente / Sem atipia
  - Não informado
- Regras:
  - Se campo "atipia" existir e contiver "ausente" => Ausente/Sem atipia
  - Se "displasia" ou texto do diagnóstico contiver:
    * "alto grau", "pouco diferenciado", "displasia de alto grau" => Alto grau
    * "moderada", "moderadamente diferenciado", "médio" => Moderado/Médio
    * "leve", "baixo grau", "bem diferenciado", "displasia leve", "displasia de baixo grau" => Baixo grau
  - Se não existir nada => Não informado

OBSERVAÇÃO CRÍTICA: TAMANHO DO PÓLIPO (maior eixo)
- O tamanho nem sempre vem como “Pólipo”. Pode vir como:
  "Peça", "Polipectomia", "Mucosectomia", "Foram recebidas peças...", "Biópsia: 03 mm x 04 mm x 08 mm"
- Implementar um parser robusto para encontrar medidas em mm no texto de:
  - material
  - diagnostico/diagnosticos
  - qualquer campo textual do item (varrer todas as strings do objeto)
- Regras:
  - Detectar padrões como:
    * "8 mm"
    * "08mm"
    * "03 mm x 04 mm x 08 mm"
    * "3x4x8mm"
    * "3 mm X 4 mm X 8 mm"
  - Extrair TODOS os números em mm e usar SEMPRE o MAIOR como "maior eixo" daquele registro.
  - Se encontrar cm (ex.: 1,2 cm), converter para mm.
- Categorias:
  2.1 até 5 mm
  2.2 de 5 a 9 mm
  2.3 10 mm ou mais
- Se não encontrar tamanho => "Não informado"

ARQUITETURA
- Criar um serviço: app/Services/LaudoJsonRepository.php
  - method: all(): Collection
  - lê Storage::json('dadospacientes.json') com fallback e validação
- Criar um serviço: app/Services/LaudoAnalyzer.php
  - methods:
    - normalize(Collection $items): Collection (padroniza campos, garante arrays, etc.)
    - uniquePatients(Collection $items): Collection
    - statsBySex(Collection $items): array
    - ageStats(Collection $items): array (media, mediana, min, max, contagem)
    - histologyStats(Collection $items): array
    - atypiaStats(Collection $items): array
    - parsePolypSizeCategory(array $item): array { maior_eixo_mm|null, categoria }
    - classifyHistology(array $item): string (POLIPO/INFLAMATORIO/CANCER/INDEFINIDO)
    - classifyAtypia(array $item): string (ALTO/MEDIO/BAIXO/AUSENTE/NAO_INFORMADO)
- As classificações devem ser baseadas em palavras-chave com regex case-insensitive e remoção de acentos quando útil.

CONTROLLERS E ROTAS
- routes/web.php:
  - Route::get('/', redirect to /dashboard)
  - Route::get('/dashboard', [DashboardController::class, 'index'])
- app/Http/Controllers/DashboardController.php:
  - carrega items do repo
  - passa para o analyzer
  - retorna view('dashboard', compact(...))

VIEWS
- resources/views/layouts/app.blade.php:
  - incluir Bootstrap via Vite
  - navbar com links âncora para as seções: Geral, Sexo, Idade, Histologia, Atipia
- resources/views/dashboard.blade.php:
  - mostrar 5 cards/seções
  - tabelas com table-responsive
  - badges para categorias (sem exagero)
  - no topo: resumo rápido (N registros, N pacientes únicos, N pólipos com tamanho identificado etc.)

VITE + BOOTSTRAP 5
- Instalar bootstrap via npm e configurar resources/js/app.js e resources/scss/app.scss
- Importar bootstrap e inicializar tooltips se quiser
- Rodar build dev

EXTRAS IMPORTANTES
- Dataset pode conter campos ausentes: sempre usar null-safe e default "Não informado".
- Consolidar múltiplos diagnósticos: se existir "diagnosticos" (array) usar; senão usar "diagnostico".
- “Paciente com mais de uma peça”: tratar como múltiplos registros, mas permitir agrupar por prontuário/nome quando fizer estatística de pacientes únicos.

ENTREGA
- Forneça:
  - Estrutura de pastas e arquivos criados
  - Código completo dos principais arquivos (routes, controller, services, views, vite config)
  - Instruções de execução (composer install, npm install, php artisan serve, npm run dev)
  - Um exemplo de JSON esperado (baseado no arquivo real)
  - Garantir que o sistema rode “do zero” sem banco.

AGORA IMPLEMENTE TUDO.
