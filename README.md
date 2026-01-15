# Análise de Dados de Pacientes

Sistema em **Laravel 11 + Blade + Bootstrap 5 (Vite)** para análise epidemiológica/histopatológica de laudos armazenados **exclusivamente** em `storage/app/dadospacientes.json`.

## ✅ O que foi implementado
- Leitura do JSON via `Storage::json` sem banco de dados.
- Normalização de dados em memória com `Collection`.
- Dashboard em `/dashboard` com 5 blocos:
  1. **Tabela Geral**
  2. **Tabela por Sexo (polipectomias)**
  3. **Tabela de Idade (média, mediana, extremos)**
  4. **Tabela de Tipo Histológico**
  5. **Tabela de Graus de Atipia**
- Parser robusto para tamanho do pólipo em mm/cm.

## 📁 Estrutura principal de arquivos
```
app/
  Http/Controllers/DashboardController.php
  Services/LaudoAnalyzer.php
  Services/LaudoJsonRepository.php
routes/web.php
resources/views/layouts/app.blade.php
resources/views/dashboard.blade.php
resources/js/app.js
resources/scss/app.scss
storage/app/dadospacientes.json
vite.config.js
```

## ▶️ Como executar
> Requer PHP 8.2+, Composer e Node.js.

```bash
composer install
npm install
npm run dev
php artisan serve
```

Acesse: `http://localhost:8000/dashboard`

## 📌 Exemplo de JSON esperado
> Arquivo: `storage/app/dadospacientes.json`

```json
[
  {
    "paciente": {
      "nome": "Maria Silva",
      "idade": 52,
      "prontuario": "12345",
      "sexo": "Feminino"
    },
    "laudo": {
      "peca": "Polipectomia",
      "data": "2024-02-12",
      "cid": "K63.5"
    },
    "material": "Pólipo de cólon, 03 mm x 04 mm x 08 mm",
    "localizacao": "Cólon descendente",
    "diagnosticos": [
      "Adenoma tubular com displasia leve",
      "Margens livres"
    ],
    "atipia": "Ausente",
    "displasia": "Leve"
  }
]
```

## ℹ️ Observações
- Campos ausentes são tratados como **"Não informado"**.
- Diagnósticos múltiplos usam `diagnosticos` (array), senão `diagnostico`.
- Estatísticas de idade consolidam pacientes únicos por `prontuario` ou `nome`.
- O parser busca tamanhos em todos os campos de texto (mm/cm) e usa o maior eixo.
