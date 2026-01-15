<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard de Laudos</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light vh-100 overflow-hidden">
    {{-- NAV FIXA --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="/dashboard">Análise de Laudos</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarContent" aria-controls="navbarContent"
                aria-expanded="false" aria-label="Alternar navegação">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#geral">Geral</a></li>
                    <li class="nav-item"><a class="nav-link" href="#sexo">Sexo</a></li>
                    <li class="nav-item"><a class="nav-link" href="#idade">Idade</a></li>
                    <li class="nav-item"><a class="nav-link" href="#histologia">Histológica</a></li>
                    <li class="nav-item"><a class="nav-link" href="#atipia">Atipia</a></li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- ÁREA QUE ROLA (SÓ O MAIN) --}}
    <main class="pt-5 h-100 overflow-auto">
        <div class="container py-4">
            @yield('content')
        </div>
    </main>
</body>
</html>