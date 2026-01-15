@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <h1 class="h3">Dashboard de Laudos</h1>
        <p class="text-muted">Análises epidemiológicas e histopatológicas baseadas no arquivo JSON em <code>storage/app/dadospacientes.json</code>.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Total de registros</div>
                    <div class="h4 mb-0">{{ $totalItems }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Pacientes únicos</div>
                    <div class="h4 mb-0">{{ $uniquePatients->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Tamanhos de pólipo identificados</div>
                    <div class="h4 mb-0">{{ $sizeKnown }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Registros com sexo informado</div>
                    <div class="h4 mb-0">{{ collect($sexStats['rows'])->where('sexo', '!=', 'Não informado')->sum('quantidade') }}</div>
                </div>
            </div>
        </div>
    </div>

    <section id="geral" class="mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex flex-column flex-md-row gap-3 justify-content-between align-items-md-center">
                    <div>
                        <h2 class="h5 mb-1">Tabela Geral</h2>
                        <small class="text-muted">Todos os registros normalizados, incluindo diagnósticos e classificações automáticas.</small>
                    </div>
                    <div class="d-flex gap-2">
                        <input id="searchInput" type="text" class="form-control" placeholder="Buscar em todos os campos">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle" id="generalTable">
                        <thead>
                        <tr>
                            <th>Prontuário</th>
                            <th>Nome</th>
                            <th>Idade</th>
                            <th>Sexo</th>
                            <th>Peça</th>
                            <th>Data do laudo</th>
                            <th>CID</th>
                            <th>Material</th>
                            <th>Localização</th>
                            <th>Diagnóstico(s)</th>
                            <th>Atipia / Displasia</th>
                            <th>Classificação Histológica</th>
                            <th>Grau de Atipia</th>
                            <th>Categoria de Tamanho</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $item['paciente']['prontuario'] ?? 'Não informado' }}</td>
                                <td>{{ $item['paciente']['nome'] ?? 'Não informado' }}</td>
                                <td>{{ $item['paciente']['idade'] ?? 'Não informado' }}</td>
                                <td>{{ $item['paciente']['sexo'] ?? 'Não informado' }}</td>
                                <td>{{ $item['laudo']['peca'] ?? 'Não informado' }}</td>
                                <td>{{ $item['laudo']['data'] ?? 'Não informado' }}</td>
                                <td>{{ $item['laudo']['cid'] ?? 'Não informado' }}</td>
                                <td>{{ $item['material'] ?? 'Não informado' }}</td>
                                <td>{{ $item['localizacao'] ?? 'Não informado' }}</td>
                                <td>
                                    @if(!empty($item['diagnosticos']))
                                        <ul class="mb-0 ps-3">
                                            @foreach($item['diagnosticos'] as $diagnostico)
                                                <li>{{ $diagnostico }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        Não informado
                                    @endif
                                </td>
                                <td>
                                    <div><strong>Atipia:</strong> {{ $item['atipia'] ?? 'Não informado' }}</div>
                                    <div><strong>Displasia:</strong> {{ $item['displasia'] ?? 'Não informado' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">{{ $item['classificacao_histologica'] }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $item['grau_atipia'] }}</span>
                                </td>
                                <td>
                                    <div>{{ $item['categoria_tamanho'] }}</div>
                                    <small class="text-muted">{{ $item['tamanho_polipo_mm'] ? $item['tamanho_polipo_mm'] . ' mm' : '' }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center text-muted">Nenhum registro encontrado no JSON.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </section>

    <section id="sexo" class="mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0">Tabela por Sexo (polipectomias)</h2>
            </div>
            <div class="card-body">
                @if(collect($sexStats['rows'])->where('sexo', '!=', 'Não informado')->sum('quantidade') === 0)
                    <div class="alert alert-warning">O dataset atual não possui sexo informado. Os percentuais foram calculados como "Não informado".</div>
                @endif
                <p class="text-muted">Total de procedimentos relacionados a pólipos: <strong>{{ $sexStats['total'] }}</strong>.</p>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Sexo</th>
                            <th>Quantidade</th>
                            <th>Percentual</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($sexStats['rows'] as $row)
                            <tr>
                                <td>{{ $row['sexo'] }}</td>
                                <td>{{ $row['quantidade'] }}</td>
                                <td>{{ $row['percentual'] }}%</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section id="idade" class="mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0">Tabela de Idade</h2>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-muted">Média</div>
                            <div class="h5 mb-0">{{ $ageStats['mean'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-muted">Mediana</div>
                            <div class="h5 mb-0">{{ $ageStats['median'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-muted">Mais jovem</div>
                            <div class="h6 mb-0">
                                {{ $ageStats['youngest']['nome'] ?? 'N/A' }}
                                <small class="text-muted">({{ $ageStats['min'] ?? 'N/A' }} anos)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-muted">Mais velho</div>
                            <div class="h6 mb-0">
                                {{ $ageStats['oldest']['nome'] ?? 'N/A' }}
                                <small class="text-muted">({{ $ageStats['max'] ?? 'N/A' }} anos)</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3 text-muted">Total de pacientes únicos: <strong>{{ $ageStats['count'] }}</strong>.</div>
            </div>
        </div>
    </section>

    <section id="histologia" class="mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0">Tabela de Tipo Histológico</h2>
            </div>
            <div class="card-body">
                <p class="text-muted">Percentuais sobre o total geral ({{ $histologyStats['total'] }}) e sobre registros classificados como lesões/pólipos ({{ $histologyStats['total_polyp'] }}).</p>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Classe</th>
                            <th>Quantidade</th>
                            <th>% Total Geral</th>
                            <th>% Apenas Lesões</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($histologyStats['rows'] as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ $row['count'] }}</td>
                                <td>{{ $row['percent_all'] }}%</td>
                                <td>{{ $row['percent_polyp'] }}%</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section id="atipia" class="mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0">Tabela de Graus de Atipia</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Quantidade</th>
                            <th>Percentual</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($atypiaStats['rows'] as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ $row['count'] }}</td>
                                <td>{{ $row['percent'] }}%</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
