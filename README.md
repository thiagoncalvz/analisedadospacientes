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
