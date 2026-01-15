<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard de Laudos</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="/dashboard">Análise de Laudos</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Alternar navegação">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="#geral">Geral</a></li>
                <li class="nav-item"><a class="nav-link" href="#sexo">Sexo</a></li>
                <li class="nav-item"><a class="nav-link" href="#idade">Idade</a></li>
                <li class="nav-item"><a class="nav-link" href="#histologia">Histologia</a></li>
                <li class="nav-item"><a class="nav-link" href="#atipia">Atipia</a></li>
            </ul>
        </div>
    </div>
</nav>

<main class="py-4">
    <div class="container">
        @yield('content')
    </div>
</main>
</body>
</html>
