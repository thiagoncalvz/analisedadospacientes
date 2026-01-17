<?php $__env->startSection('content'); ?>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted">Registros no Banco de Dados</h6>
                    <div class="display-6 fw-semibold"><?php echo e($totalItems); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted">Pacientes únicos</h6>
                    <div class="display-6 fw-semibold"><?php echo e($uniquePatientsCount); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted">Pólipos com tamanho identificado</h6>
                    <div class="display-6 fw-semibold"><?php echo e($polypSizedCount); ?></div>
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
                <?php if($sexStats['missing']): ?>
                    <div class="alert alert-warning">O dataset não possui sexo informado em todos os registros.</div>
                <?php endif; ?>
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
                            <?php $__currentLoopData = $sexStats['counts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($label); ?></td>
                                    <td><?php echo e($count); ?></td>
                                    <td><?php echo e($sexStats['percentages'][$label]); ?>%</td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th>Total</th>
                                <th><?php echo e($sexStats['total']); ?></th>
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
                            <div class="fs-4 fw-semibold"><?php echo e($ageStats['average'] ?? 'Não informado'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted">Mediana</div>
                            <div class="fs-4 fw-semibold"><?php echo e($ageStats['median'] ?? 'Não informado'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted">Paciente mais jovem</div>
                            <div class="fw-semibold"><?php echo e($ageStats['youngest']['nome'] ?? 'Não informado'); ?></div>
                            <div class="text-muted"><?php echo e($ageStats['youngest']['idade'] ?? '-'); ?> anos</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted">Paciente mais velho</div>
                            <div class="fw-semibold"><?php echo e($ageStats['oldest']['nome'] ?? 'Não informado'); ?></div>
                            <div class="text-muted"><?php echo e($ageStats['oldest']['idade'] ?? '-'); ?> anos</div>
                        </div>
                    </div>
                </div>
                <div class="mt-3 text-muted">Total de pacientes únicos: <?php echo e($ageStats['count']); ?></div>
            </div>
        </div>
    </section>

    <section id="histologia" class="mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-1">Tabela de Tipo Histológico</h5>
                <small class="text-muted">
                    Percentuais sobre total geral (<?php echo e($histologyStats['total']); ?>) e sobre registros com lesões/pólipos (<?php echo e($histologyStats['lesion_total']); ?>).
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
                            <?php $__currentLoopData = $histologyStats['counts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($label); ?></td>
                                    <td><?php echo e($count); ?></td>
                                    <td><?php echo e($histologyStats['percentages_total'][$label]); ?>%</td>
                                    <td><?php echo e($histologyStats['percentages_lesion'][$label]); ?>%</td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                            <?php $__currentLoopData = $atypiaStats['counts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($label); ?></td>
                                    <td><?php echo e($count); ?></td>
                                    <td><?php echo e($atypiaStats['percentages'][$label]); ?>%</td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th>Total</th>
                                <th><?php echo e($atypiaStats['total']); ?></th>
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
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tbody data-item>
                                <tr class="table-primary">
                                    <th colspan="2">
                                        <div class="fw-semibold">Registro <?php echo e($loop->iteration + (($items->currentPage() - 1) * $items->perPage())); ?></div>
                                        <small class="text-muted">
                                            <?php echo e($item['paciente']['nome'] ?? 'Paciente não informado'); ?>

                                            <?php if(!empty($item['paciente']['prontuario'])): ?>
                                                • Prontuário <?php echo e($item['paciente']['prontuario']); ?>

                                            <?php endif; ?>
                                        </small>
                                    </th>
                                </tr>
                                <tr>
                                    <th scope="row">Prontuário</th>
                                    <td><?php echo e($item['paciente']['prontuario'] ?? 'Não informado'); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Nome</th>
                                    <td><?php echo e($item['paciente']['nome'] ?? 'Não informado'); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Idade</th>
                                    <td><?php echo e($item['paciente']['idade'] ?? 'Não informado'); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Sexo</th>
                                    <td><?php echo e($item['paciente']['sexo'] ?? 'Não informado'); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Dados literais do paciente</th>
                                    <td>
                                        <?php if(!empty($item['paciente_literal'])): ?>
                                            <div class="d-flex flex-column gap-1">
                                                <?php $__currentLoopData = $item['paciente_literal']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campo => $valor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div>
                                                        <strong><?php echo e(ucfirst(str_replace('_', ' ', $campo))); ?>:</strong>
                                                        <?php echo e(is_array($valor) ? json_encode($valor, JSON_UNESCAPED_UNICODE) : ($valor ?? 'Não informado')); ?>

                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php else: ?>
                                            Não informado
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Peças histológicas</th>
                                    <td>
                                        <?php if(!empty($item['pecas_histologicas'])): ?>
                                            <div class="d-flex flex-column gap-2">
                                                <?php $__currentLoopData = $item['pecas_histologicas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $peca): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
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
                                                    ?>
                                                    <div class="border rounded p-2 bg-light">
                                                        <div class="fw-semibold">Peça <?php echo e($loop->iteration); ?></div>
                                                        <?php if(!empty($valores)): ?>
                                                            <ul class="mb-0 ps-3">
                                                                <?php $__currentLoopData = $valores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $valor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <li><?php echo e($valor); ?></li>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </ul>
                                                        <?php else: ?>
                                                            <div class="text-muted small">Sem detalhes adicionais.</div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php else: ?>
                                            Não informado
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Peça (laudo)</th>
                                    <td><?php echo e($item['laudo']['peca'] ?? 'Não informado'); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Data do laudo</th>
                                    <td><?php echo e($item['laudo']['data'] ?? 'Não informado'); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">CID</th>
                                    <td><?php echo e($item['laudo']['cid'] ?? 'Não informado'); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Material</th>
                                    <td><?php echo e($item['material'] ?? 'Não informado'); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Localização</th>
                                    <td><?php echo e($item['localizacao'] ?? 'Não informado'); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Diagnóstico(s)</th>
                                    <td>
                                        <?php if(!empty($item['diagnosticos'])): ?>
                                            <ul class="mb-0 ps-3">
                                                <?php $__currentLoopData = $item['diagnosticos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $diagnostico): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li><?php echo e($diagnostico); ?></li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </ul>
                                        <?php else: ?>
                                            Não informado
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Atipia/Displasia</th>
                                    <td>
                                        <div><strong>Atipia:</strong> <?php echo e($item['atipia'] ?? 'Não informado'); ?></div>
                                        <div><strong>Displasia:</strong> <?php echo e($item['displasia'] ?? 'Não informado'); ?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Classificação Histológica</th>
                                    <td><span class="badge text-bg-info"><?php echo e($item['histology']); ?></span></td>
                                </tr>
                                <tr>
                                    <th scope="row">Grau de Atipia</th>
                                    <td><span class="badge text-bg-secondary"><?php echo e($item['atypia_class']); ?></span></td>
                                </tr>
                                <tr>
                                    <th scope="row">Tamanho do pólipo</th>
                                    <td>
                                        <div class="fw-semibold"><?php echo e($item['polyp_size']['categoria']); ?></div>
                                        <?php if($item['polyp_size']['maior_eixo_mm'] !== null): ?>
                                            <small class="text-muted">Maior eixo: <?php echo e($item['polyp_size']['maior_eixo_mm']); ?> mm</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tbody>
                                <tr>
                                    <td colspan="2" class="text-center text-muted">Nenhum registro encontrado no JSON.</td>
                                </tr>
                            </tbody>
                        <?php endif; ?>
                    </table>
                </div>

                <div class="d-flex justify-content-center">
                    <?php echo e($items->links('pagination::bootstrap-5')); ?>

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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/t-silva/Documentos/GitHub/analisedadospacientes/resources/views/dashboard.blade.php ENDPATH**/ ?>