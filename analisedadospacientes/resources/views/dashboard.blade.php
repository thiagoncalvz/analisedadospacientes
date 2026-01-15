@extends('layouts.app')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted">Registros no JSON</h6>
                    <div class="display-6 fw-semibold">{{ $totalItems }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted">Pacientes únicos</h6>
                    <div class="display-6 fw-semibold">{{ $uniquePatientsCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted">Pólipos com tamanho identificado</h6>
                    <div class="display-6 fw-semibold">{{ $polypSizedCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <section id="geral" class="mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h5 class="mb-1">Tabela Geral</h5>
                        <small class="text-muted">Todos os laudos registrados no JSON.</small>
                    </div>
                    <div class="input-group w-auto">
                        <span class="input-group-text">Buscar</span>
                        <input type="search" class="form-control" id="tableSearch" placeholder="Digite nome, CID, diagnóstico...">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle" id="generalTable">
                        <thead class="table-light">
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
                                <th>Atipia/Displasia</th>
                                <th>Classificação Histológica</th>
                                <th>Grau de Atipia</th>
                                <th>Tamanho do pólipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
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
                                        @if (!empty($item['diagnosticos']))
                                            <ul class="mb-0 ps-3">
                                                @foreach ($item['diagnosticos'] as $diagnostico)
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
                                        <span class="badge text-bg-info">{{ $item['histology'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-secondary">{{ $item['atypia_class'] }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $item['polyp_size']['categoria'] }}</div>
                                        @if ($item['polyp_size']['maior_eixo_mm'] !== null)
                                            <small class="text-muted">Maior eixo: {{ $item['polyp_size']['maior_eixo_mm'] }} mm</small>
                                        @endif
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

                <div class="d-flex justify-content-center">
                    {{ $items->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </section>

    <section id="sexo" class="mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-1">Tabela por Sexo</h5>
                <small class="text-muted">Percentuais de polipectomias e procedimentos relacionados a pólipo por sexo.</small>
            </div>
            <div class="card-body">
                @if ($sexStats['missing'])
                    <div class="alert alert-warning">O dataset não possui sexo informado em todos os registros.</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Sexo</th>
                                <th>Procedimentos</th>
                                <th>Percentual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sexStats['counts'] as $label => $count)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td>{{ $count }}</td>
                                    <td>{{ $sexStats['percentages'][$label] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th>Total</th>
                                <th>{{ $sexStats['total'] }}</th>
                                <th>100%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section id="idade" class="mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-1">Tabela de Idade</h5>
                <small class="text-muted">Estatísticas considerando pacientes únicos (prontuário ou nome).</small>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted">Média</div>
                            <div class="fs-4 fw-semibold">{{ $ageStats['average'] ?? 'Não informado' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted">Mediana</div>
                            <div class="fs-4 fw-semibold">{{ $ageStats['median'] ?? 'Não informado' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted">Paciente mais jovem</div>
                            <div class="fw-semibold">{{ $ageStats['youngest']['nome'] ?? 'Não informado' }}</div>
                            <div class="text-muted">{{ $ageStats['youngest']['idade'] ?? '-' }} anos</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted">Paciente mais velho</div>
                            <div class="fw-semibold">{{ $ageStats['oldest']['nome'] ?? 'Não informado' }}</div>
                            <div class="text-muted">{{ $ageStats['oldest']['idade'] ?? '-' }} anos</div>
                        </div>
                    </div>
                </div>
                <div class="mt-3 text-muted">Total de pacientes únicos: {{ $ageStats['count'] }}</div>
            </div>
        </div>
    </section>

    <section id="histologia" class="mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-1">Tabela de Tipo Histológico</h5>
                <small class="text-muted">
                    Percentuais sobre total geral ({{ $histologyStats['total'] }}) e sobre registros com lesões/pólipos ({{ $histologyStats['lesion_total'] }}).
                </small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Classificação</th>
                                <th>Contagem</th>
                                <th>% sobre total geral</th>
                                <th>% sobre lesões</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($histologyStats['counts'] as $label => $count)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td>{{ $count }}</td>
                                    <td>{{ $histologyStats['percentages_total'][$label] }}%</td>
                                    <td>{{ $histologyStats['percentages_lesion'][$label] }}%</td>
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
                <h5 class="mb-1">Tabela de Graus de Atipia</h5>
                <small class="text-muted">Classificação baseada em atipia, displasia e diagnóstico.</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Grau</th>
                                <th>Contagem</th>
                                <th>Percentual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($atypiaStats['counts'] as $label => $count)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td>{{ $count }}</td>
                                    <td>{{ $atypiaStats['percentages'][$label] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th>Total</th>
                                <th>{{ $atypiaStats['total'] }}</th>
                                <th>100%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('tableSearch');
            const table = document.getElementById('generalTable');
            const rows = table ? Array.from(table.querySelectorAll('tbody tr')) : [];

            if (!searchInput) {
                return;
            }

            searchInput.addEventListener('input', (event) => {
                const term = event.target.value.toLowerCase();

                rows.forEach((row) => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });
            });
        });
    </script>
@endsection
