@extends('layouts.app')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted">Registros no Banco de Dados</h6>
                    <div class="display-6 fw-semibold">{{ $totalItems }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted">Laudos de endoscopia</h6>
                    <div class="display-6 fw-semibold">{{ $endoscopyItemsCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted">Laudos válidos (sem endoscopia)</h6>
                    <div class="display-6 fw-semibold">{{ $validItemsCount }}</div>
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

    <section id="localizacao" class="mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-1">Tabela de Localização dos Pólipos</h5>
                <small class="text-muted">Distribuição das localizações informadas nos laudos.</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Localização</th>
                                <th>Contagem</th>
                                <th>Percentual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($locationStats['counts'] as $label => $count)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td>{{ $count }}</td>
                                    <td>{{ $locationStats['percentages'][$label] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th>Total</th>
                                <th>{{ $locationStats['total'] }}</th>
                                <th>100%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section id="numero-polipos" class="mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-1">Tabela de Número de Pólipos por Laudo</h5>
                <small class="text-muted">Quantidade de pólipos identificados por registro.</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Categoria</th>
                                <th>Contagem</th>
                                <th>Percentual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($polypCountStats['counts'] as $label => $count)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td>{{ $count }}</td>
                                    <td>{{ $polypCountStats['percentages'][$label] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th>Total</th>
                                <th>{{ $polypCountStats['total'] }}</th>
                                <th>100%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>

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
                <div class="table-responsive general-table-wrapper">
                    <table class="table table-striped align-middle" id="generalTable">
                        <thead class="table-light">
                            <tr>
                                <th>Campo</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        @forelse ($items as $item)
                            <tbody data-item>
                                <tr class="table-primary">
                                    <th colspan="2">
                                        <div class="fw-semibold">Registro {{ $loop->iteration + (($items->currentPage() - 1) * $items->perPage()) }}</div>
                                        <small class="text-muted">
                                            {{ $item['paciente']['nome'] ?? 'Paciente não informado' }}
                                            @if (!empty($item['paciente']['prontuario']))
                                                • Prontuário {{ $item['paciente']['prontuario'] }}
                                            @endif
                                        </small>
                                    </th>
                                </tr>
                                <tr>
                                    <th scope="row">Prontuário</th>
                                    <td>{{ $item['paciente']['prontuario'] ?? 'Não informado' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Nome</th>
                                    <td>{{ $item['paciente']['nome'] ?? 'Não informado' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Idade</th>
                                    <td>{{ $item['paciente']['idade'] ?? 'Não informado' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Sexo</th>
                                    <td>{{ $item['paciente']['sexo'] ?? 'Não informado' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Dados literais do paciente</th>
                                    <td>
                                        @if (!empty($item['paciente_literal']))
                                            <div class="d-flex flex-column gap-1">
                                                @foreach ($item['paciente_literal'] as $campo => $valor)
                                                    <div>
                                                        <strong>{{ ucfirst(str_replace('_', ' ', $campo)) }}:</strong>
                                                        {{ is_array($valor) ? json_encode($valor, JSON_UNESCAPED_UNICODE) : ($valor ?? 'Não informado') }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            Não informado
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Peças histológicas</th>
                                    <td>
                                        @if (!empty($item['pecas_histologicas']))
                                            <div class="d-flex flex-column gap-2">
                                                @foreach ($item['pecas_histologicas'] as $peca)
                                                    @php
                                                        $estruturado = $peca['estruturado'] ?? [];
                                                        $literal = $peca['literal'] ?? [];
                                                        $valores = [];

                                                        foreach (['descricao_origem', 'tipo_histologico', 'grau_atipia'] as $campo) {
                                                            if (!empty($estruturado[$campo])) {
                                                                $valores[] = $estruturado[$campo];
                                                            }
                                                        }

                                                        if (isset($estruturado['quantidade_fragmentos'])) {
                                                            $valores[] = $estruturado['quantidade_fragmentos'];
                                                        }

                                                        if (!empty($estruturado['tamanho_mm']) && is_array($estruturado['tamanho_mm'])) {
                                                            $tamanho = $estruturado['tamanho_mm'];
                                                            $tamanhoValor = $tamanho['maior_eixo'] ?? null;
                                                            $tamanhoCategoria = $tamanho['categoria'] ?? null;
                                                            $textoTamanho = trim(collect([$tamanhoValor ? $tamanhoValor . ' mm' : null, $tamanhoCategoria])->filter()->implode(' - '));
                                                            if ($textoTamanho !== '') {
                                                                $valores[] = $textoTamanho;
                                                            }
                                                        }

                                                        foreach (['diagnostico', 'microscopia_conclusao', 'macroscopia', 'conclusao'] as $campo) {
                                                            if (!empty($literal[$campo])) {
                                                                $valores[] = $literal[$campo];
                                                            }
                                                        }

                                                        foreach (['neoplasia', 'adenocarcinoma'] as $campo) {
                                                            if (array_key_exists($campo, $estruturado)) {
                                                                $valores[] = $estruturado[$campo] ? 'Sim' : 'Não';
                                                            }
                                                        }

                                                        $valores = array_values(array_filter($valores, fn ($valor) => $valor !== null && $valor !== ''));
                                                    @endphp
                                                    <div class="border rounded p-2 bg-light">
                                                        <div class="fw-semibold">Peça {{ $loop->iteration }}</div>
                                                        @if (!empty($valores))
                                                            <ul class="mb-0 ps-3">
                                                                @foreach ($valores as $valor)
                                                                    <li>{{ $valor }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @else
                                                            <div class="text-muted small">Sem detalhes adicionais.</div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            Não informado
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Peça (laudo)</th>
                                    <td>{{ $item['laudo']['peca'] ?? 'Não informado' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Data do laudo</th>
                                    <td>{{ $item['laudo']['data'] ?? 'Não informado' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">CID</th>
                                    <td>{{ $item['laudo']['cid'] ?? 'Não informado' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Material</th>
                                    <td>{{ $item['material'] ?? 'Não informado' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Localização</th>
                                    <td>{{ $item['localizacao'] ?? 'Não informado' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Diagnóstico(s)</th>
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
                                </tr>
                                <tr>
                                    <th scope="row">Atipia/Displasia</th>
                                    <td>
                                        <div><strong>Atipia:</strong> {{ $item['atipia'] ?? 'Não informado' }}</div>
                                        <div><strong>Displasia:</strong> {{ $item['displasia'] ?? 'Não informado' }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Classificação Histológica</th>
                                    <td><span class="badge text-bg-info">{{ $item['histology'] }}</span></td>
                                </tr>
                                <tr>
                                    <th scope="row">Grau de Atipia</th>
                                    <td><span class="badge text-bg-secondary">{{ $item['atypia_class'] }}</span></td>
                                </tr>
                                <tr>
                                    <th scope="row">Tamanho do pólipo</th>
                                    <td>
                                        <div class="fw-semibold">{{ $item['polyp_size']['categoria'] }}</div>
                                        @if ($item['polyp_size']['maior_eixo_mm'] !== null)
                                            <small class="text-muted">Maior eixo: {{ $item['polyp_size']['maior_eixo_mm'] }} mm</small>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        @empty
                            <tbody>
                                <tr>
                                    <td colspan="2" class="text-center text-muted">Nenhum registro encontrado no JSON.</td>
                                </tr>
                            </tbody>
                        @endforelse
                    </table>
                </div>

                <div class="d-flex justify-content-center">
                    {{ $items->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('tableSearch');
            const table = document.getElementById('generalTable');
            const groups = table ? Array.from(table.querySelectorAll('tbody[data-item]')) : [];

            if (!searchInput) {
                return;
            }

            searchInput.addEventListener('input', (event) => {
                const term = event.target.value.toLowerCase();

                groups.forEach((group) => {
                    const text = group.textContent.toLowerCase();
                    const show = text.includes(term);
                    group.style.display = show ? '' : 'none';
                });
            });
        });
    </script>
@endsection
