const PAGE_SIZE = 10;

const normalizeText = (text) =>
  text
    .toString()
    .toLowerCase()
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .trim();

const extractStrings = (value) => {
  const strings = [];
  if (Array.isArray(value)) {
    value.forEach((child) => strings.push(...extractStrings(child)));
  } else if (value && typeof value === 'object') {
    Object.values(value).forEach((child) => strings.push(...extractStrings(child)));
  } else if (typeof value === 'string') {
    strings.push(value);
  }
  return strings;
};

const joinTextFields = (item) => extractStrings(item).join(' ');

const normalizePecas = (item) => (Array.isArray(item?.pecas_histologicas) ? item.pecas_histologicas : []);

const collectStructuredAtypia = (pecas) => {
  const values = [];
  pecas.forEach((peca) => {
    const grau = peca?.estruturado?.grau_atipia;
    if (typeof grau === 'string' && grau.trim()) {
      values.push(grau.trim());
    }
  });
  if (!values.length) {
    return null;
  }
  return [...new Set(values)].join(' / ');
};

const collectDiagnosticos = (item, pecas) => {
  let diagnosticos = item?.diagnosticos ?? [];
  if (typeof diagnosticos === 'string') {
    diagnosticos = [diagnosticos];
  }
  if (!Array.isArray(diagnosticos)) {
    diagnosticos = [];
  }
  if (!diagnosticos.length && typeof item?.diagnostico === 'string') {
    diagnosticos = [item.diagnostico];
  }
  pecas.forEach((peca) => {
    const literal = peca?.literal ?? {};
    ['diagnostico', 'microscopia_conclusao', 'conclusao'].forEach((field) => {
      if (typeof literal[field] === 'string' && literal[field].trim()) {
        diagnosticos.push(literal[field]);
      }
    });
  });
  return [...new Set(diagnosticos.filter(Boolean))];
};

const normalizeAge = (idade) => {
  if (typeof idade === 'number') {
    return Math.trunc(idade);
  }
  if (typeof idade === 'string') {
    const match = idade.match(/(\d{1,3})/);
    if (match) {
      return parseInt(match[1], 10);
    }
  }
  return idade;
};

const normalizeItem = (item) => {
  const pacienteData = item?.paciente ?? {};
  const pacienteEstruturado = pacienteData?.estruturado ?? (typeof pacienteData === 'object' ? pacienteData : {});
  const pacienteLiteral = pacienteData?.literal ?? {};

  const exameData = item?.exame ?? {};
  const exameEstruturado = exameData?.estruturado ?? (typeof exameData === 'object' ? exameData : {});
  const exameLiteral = exameData?.literal ?? {};

  const laudo = item?.laudo ?? {};
  const laudoLiteralCompleto = item?.laudo_literal_completo ?? {};

  const pecas = normalizePecas(item);
  const diagnosticos = collectDiagnosticos(item, pecas);
  const atipia = item?.atipia ?? collectStructuredAtypia(pecas);

  return {
    ...item,
    paciente: {
      nome: pacienteEstruturado?.nome ?? pacienteLiteral?.nome ?? null,
      idade: normalizeAge(pacienteEstruturado?.idade ?? pacienteLiteral?.idade ?? null),
      prontuario: pacienteEstruturado?.prontuario ?? pacienteLiteral?.prontuario ?? null,
      sexo:
        pacienteEstruturado?.sexo ??
        pacienteEstruturado?.genero ??
        pacienteLiteral?.sexo ??
        pacienteLiteral?.genero ??
        null,
    },
    paciente_literal: pacienteLiteral && typeof pacienteLiteral === 'object' ? pacienteLiteral : {},
    laudo: {
      peca: laudoLiteralCompleto?.numero_peca ?? laudo?.peca ?? null,
      data: exameEstruturado?.data_laudo ?? exameLiteral?.data ?? laudo?.data ?? null,
      cid: exameEstruturado?.cid ?? exameLiteral?.cid ?? laudo?.cid ?? null,
    },
    material: item?.material ?? exameLiteral?.material ?? exameEstruturado?.tipo ?? null,
    localizacao: item?.localizacao ?? exameLiteral?.local ?? exameLiteral?.localizacao ?? null,
    diagnosticos,
    atipia,
    displasia: item?.displasia ?? null,
    pecas_histologicas: pecas,
  };
};

const convertToMillimeters = (value, unit) => {
  const number = Number.parseFloat(value.toString().replace(',', '.'));
  if (Number.isNaN(number)) {
    return null;
  }
  return unit.toLowerCase() === 'cm' ? number * 10 : number;
};

const extractStructuredPolypSizes = (item) => {
  const values = [];
  normalizePecas(item).forEach((peca) => {
    const maiorEixo = peca?.estruturado?.tamanho_mm?.maior_eixo;
    if (typeof maiorEixo === 'number') {
      values.push(maiorEixo);
    } else if (typeof maiorEixo === 'string' && maiorEixo.trim() !== '' && !Number.isNaN(Number(maiorEixo))) {
      values.push(Number(maiorEixo));
    }
  });
  return values;
};

const parsePolypSizeCategory = (item) => {
  const structuredSizes = extractStructuredPolypSizes(item);
  if (structuredSizes.length) {
    const max = Math.max(...structuredSizes);
    return {
      maior_eixo_mm: max,
      categoria: max >= 10 ? '10 mm ou mais' : max > 5 ? '5 a 9 mm' : max >= 2.1 ? '2.1 até 5 mm' : 'Menor que 2 mm',
    };
  }

  const strings = extractStrings(item);
  const values = [];

  strings.forEach((text) => {
    const normalized = normalizeText(text);

    const multiMatches = normalized.matchAll(/((?:\d+(?:[\.,]\d+)?\s*x\s*)+\d+(?:[\.,]\d+)?)(?:\s*)(mm|cm)\b/gi);
    for (const match of multiMatches) {
      const unit = match[2];
      const numbers = match[1].split(/x/i);
      numbers.forEach((number) => {
        const value = convertToMillimeters(number.trim(), unit);
        if (value !== null) {
          values.push(value);
        }
      });
    }

    const singleMatches = normalized.matchAll(/(\d+(?:[\.,]\d+)?)\s*(mm|cm)\b/gi);
    for (const match of singleMatches) {
      const value = convertToMillimeters(match[1], match[2]);
      if (value !== null) {
        values.push(value);
      }
    }
  });

  if (!values.length) {
    return { maior_eixo_mm: null, categoria: 'Não informado' };
  }

  const max = Math.max(...values);
  return {
    maior_eixo_mm: max,
    categoria: max >= 10 ? '10 mm ou mais' : max > 5 ? '5 a 9 mm' : max >= 2.1 ? '2.1 até 5 mm' : 'Menor que 2 mm',
  };
};

const classifyHistology = (item) => {
  const pecas = normalizePecas(item);
  for (const peca of pecas) {
    const estruturado = peca?.estruturado ?? {};
    if (estruturado?.adenocarcinoma) {
      return 'Adenocarcinoma / Câncer';
    }
    if (estruturado?.neoplasia) {
      return 'Pólipo';
    }
    if (typeof estruturado?.tipo_histologico === 'string') {
      const tipo = normalizeText(estruturado.tipo_histologico);
      if (/adenocarcinoma|carcinoma|neoplasia maligna|malign/iu.test(tipo)) {
        return 'Adenocarcinoma / Câncer';
      }
      if (/polip|polipectomia|mucosectomia|adenoma|peca\s*polip/iu.test(tipo)) {
        return 'Pólipo';
      }
      if (/gastrite|inflam|mucosa|tecido de granulacao|hiperplas|colite|ulcer/iu.test(tipo)) {
        return 'Inflamatório / Não neoplásico';
      }
    }
  }

  const text = normalizeText(joinTextFields(item));
  if (!text) {
    return 'Indefinido';
  }
  if (/adenocarcinoma|carcinoma|neoplasia maligna|malign/iu.test(text)) {
    return 'Adenocarcinoma / Câncer';
  }
  if (/polip|polipectomia|mucosectomia|adenoma|peca\s*polip/iu.test(text)) {
    return 'Pólipo';
  }
  if (/gastrite|inflam|mucosa|tecido de granulacao|hiperplas|colite|ulcer/iu.test(text)) {
    return 'Inflamatório / Não neoplásico';
  }
  return 'Indefinido';
};

const classifyAtypia = (item) => {
  const pecas = normalizePecas(item);
  const atipia = item?.atipia ?? collectStructuredAtypia(pecas);
  const displasia = item?.displasia ?? null;
  const diagnostico = joinTextFields(item);
  const text = normalizeText([atipia, displasia, diagnostico].filter(Boolean).join(' '));

  if (!text) {
    return 'Não informado';
  }
  if (/ausente|sem atipia/iu.test(text)) {
    return 'Ausente / Sem atipia';
  }
  if (/alto grau|pouco diferenciado|displasia de alto grau/iu.test(text)) {
    return 'Alto grau';
  }
  if (/moderada|moderadamente diferenciado|medio/iu.test(text)) {
    return 'Moderado / Médio';
  }
  if (/baixo grau|bem diferenciado|displasia leve|displasia de baixo grau|leve/iu.test(text)) {
    return 'Baixo grau';
  }
  return 'Não informado';
};

const isPolypProcedure = (item) => {
  const pecas = normalizePecas(item);
  for (const peca of pecas) {
    const estruturado = peca?.estruturado ?? {};
    if (estruturado?.neoplasia) {
      return true;
    }
    if (typeof estruturado?.tipo_histologico === 'string') {
      const tipo = normalizeText(estruturado.tipo_histologico);
      if (/polip|polipectomia|mucosectomia|adenoma/iu.test(tipo)) {
        return true;
      }
    }
  }
  const text = normalizeText(joinTextFields(item));
  return /polip|polipectomia|mucosectomia|adenoma/iu.test(text);
};

const uniquePatients = (items) => {
  const unique = new Map();
  items.forEach((item) => {
    const paciente = item?.paciente ?? {};
    const key = paciente?.prontuario ?? paciente?.nome;
    if (!key) {
      return;
    }
    if (!unique.has(key)) {
      unique.set(key, item);
    }
  });
  return Array.from(unique.values());
};

const statsBySex = (items) => {
  const counts = {
    Feminino: 0,
    Masculino: 0,
    'Não informado': 0,
  };

  items.forEach((item) => {
    if (!isPolypProcedure(item)) {
      return;
    }
    const sexo = typeof item?.paciente?.sexo === 'string' ? normalizeText(item.paciente.sexo) : null;
    if (sexo && sexo.includes('fem')) {
      counts.Feminino += 1;
    } else if (sexo && sexo.includes('masc')) {
      counts.Masculino += 1;
    } else {
      counts['Não informado'] += 1;
    }
  });

  const total = Object.values(counts).reduce((sum, value) => sum + value, 0);
  const percentages = Object.fromEntries(
    Object.entries(counts).map(([label, count]) => [label, total > 0 ? Math.round((count / total) * 1000) / 10 : 0])
  );

  return {
    counts,
    percentages,
    total,
    missing: counts['Não informado'] > 0,
  };
};

const ageStats = (items) => {
  const unique = uniquePatients(items);
  const ages = [];
  unique.forEach((item) => {
    const idade = item?.paciente?.idade;
    if (typeof idade === 'number') {
      ages.push(idade);
    }
  });
  ages.sort((a, b) => a - b);

  const count = ages.length;
  const average = count ? Math.round((ages.reduce((sum, age) => sum + age, 0) / count) * 10) / 10 : null;
  let median = null;
  if (count) {
    const mid = Math.floor(count / 2);
    median = count % 2 ? ages[mid] : (ages[mid - 1] + ages[mid]) / 2;
  }

  let youngest = null;
  let oldest = null;
  unique.forEach((item) => {
    const idade = item?.paciente?.idade;
    if (typeof idade !== 'number') {
      return;
    }
    if (!youngest || idade < youngest.idade) {
      youngest = { idade, nome: item?.paciente?.nome ?? 'Não informado' };
    }
    if (!oldest || idade > oldest.idade) {
      oldest = { idade, nome: item?.paciente?.nome ?? 'Não informado' };
    }
  });

  return {
    average,
    median,
    count: unique.length,
    youngest,
    oldest,
  };
};

const histologyStats = (items) => {
  const counts = {
    'Pólipo': 0,
    'Inflamatório / Não neoplásico': 0,
    'Adenocarcinoma / Câncer': 0,
    Indefinido: 0,
  };

  items.forEach((item) => {
    const classification = classifyHistology(item);
    counts[classification] = (counts[classification] ?? 0) + 1;
  });

  const total = Object.values(counts).reduce((sum, value) => sum + value, 0);
  const lesionTotal = counts['Pólipo'] + counts['Inflamatório / Não neoplásico'] + counts['Adenocarcinoma / Câncer'];

  const percentagesTotal = Object.fromEntries(
    Object.entries(counts).map(([label, count]) => [label, total > 0 ? Math.round((count / total) * 1000) / 10 : 0])
  );
  const percentagesLesion = Object.fromEntries(
    Object.entries(counts).map(([label, count]) => [label, lesionTotal > 0 ? Math.round((count / lesionTotal) * 1000) / 10 : 0])
  );

  return {
    counts,
    percentages_total: percentagesTotal,
    percentages_lesion: percentagesLesion,
    total,
    lesion_total: lesionTotal,
  };
};

const atypiaStats = (items) => {
  const counts = {
    'Alto grau': 0,
    'Moderado / Médio': 0,
    'Baixo grau': 0,
    'Ausente / Sem atipia': 0,
    'Não informado': 0,
  };

  items.forEach((item) => {
    const classification = classifyAtypia(item);
    counts[classification] = (counts[classification] ?? 0) + 1;
  });

  const total = Object.values(counts).reduce((sum, value) => sum + value, 0);
  const percentages = Object.fromEntries(
    Object.entries(counts).map(([label, count]) => [label, total > 0 ? Math.round((count / total) * 1000) / 10 : 0])
  );

  return {
    counts,
    percentages,
    total,
  };
};

const renderKeyValueRow = (label, value) => `<tr><th scope="row">${label}</th><td>${value}</td></tr>`;

const formatMaybe = (value) => (value === null || value === undefined || value === '' ? 'Não informado' : value);

const renderPecas = (pecas) => {
  if (!Array.isArray(pecas) || !pecas.length) {
    return 'Não informado';
  }
  return pecas
    .map((peca, index) => {
      const estruturado = peca?.estruturado ?? {};
      const literal = peca?.literal ?? {};
      const values = [];

      ['descricao_origem', 'tipo_histologico', 'grau_atipia'].forEach((campo) => {
        if (estruturado?.[campo]) {
          values.push(estruturado[campo]);
        }
      });

      if (estruturado?.quantidade_fragmentos !== undefined) {
        values.push(estruturado.quantidade_fragmentos);
      }

      const tamanho = estruturado?.tamanho_mm ?? null;
      if (tamanho && typeof tamanho === 'object') {
        const tamanhoValor = tamanho?.maior_eixo ?? null;
        const tamanhoCategoria = tamanho?.categoria ?? null;
        const textoTamanho = [tamanhoValor ? `${tamanhoValor} mm` : null, tamanhoCategoria].filter(Boolean).join(' - ');
        if (textoTamanho) {
          values.push(textoTamanho);
        }
      }

      ['diagnostico', 'microscopia_conclusao', 'macroscopia', 'conclusao'].forEach((campo) => {
        if (literal?.[campo]) {
          values.push(literal[campo]);
        }
      });

      ['neoplasia', 'adenocarcinoma'].forEach((campo) => {
        if (Object.prototype.hasOwnProperty.call(estruturado, campo)) {
          values.push(estruturado[campo] ? 'Sim' : 'Não');
        }
      });

      const listItems = values.filter(Boolean).map((valor) => `<li>${valor}</li>`).join('');

      return `
        <div class="border rounded p-2 bg-light">
          <div class="fw-semibold">Peça ${index + 1}</div>
          ${listItems ? `<ul class="mb-0 ps-3">${listItems}</ul>` : '<div class="text-muted small">Sem detalhes adicionais.</div>'}
        </div>
      `;
    })
    .join('');
};

const buildItemRows = (item, index) => {
  const paciente = item?.paciente ?? {};
  const patientHeader = `
    <tr class="table-primary">
      <th colspan="2">
        <div class="fw-semibold">Registro ${index + 1}</div>
        <small class="text-muted">
          ${paciente?.nome ?? 'Paciente não informado'}
          ${paciente?.prontuario ? ` • Prontuário ${paciente.prontuario}` : ''}
        </small>
      </th>
    </tr>
  `;

  const pacienteLiteral = item?.paciente_literal ?? {};
  const pacienteLiteralHtml = Object.keys(pacienteLiteral).length
    ? Object.entries(pacienteLiteral)
        .map(
          ([campo, valor]) =>
            `<div><strong>${campo.replace(/_/g, ' ')}:</strong> ${
              Array.isArray(valor) ? JSON.stringify(valor) : formatMaybe(valor)
            }</div>`
        )
        .join('')
    : 'Não informado';

  const diagnosticos = Array.isArray(item?.diagnosticos) && item.diagnosticos.length
    ? `<ul class="mb-0 ps-3">${item.diagnosticos.map((diagnostico) => `<li>${diagnostico}</li>`).join('')}</ul>`
    : 'Não informado';

  const polypSize = item?.polyp_size ?? { categoria: 'Não informado', maior_eixo_mm: null };
  const polypSizeHtml = `
    <div class="fw-semibold">${polypSize.categoria}</div>
    ${polypSize.maior_eixo_mm !== null ? `<small class="text-muted">Maior eixo: ${polypSize.maior_eixo_mm} mm</small>` : ''}
  `;

  return `
    ${patientHeader}
    ${renderKeyValueRow('Prontuário', formatMaybe(paciente?.prontuario))}
    ${renderKeyValueRow('Nome', formatMaybe(paciente?.nome))}
    ${renderKeyValueRow('Idade', formatMaybe(paciente?.idade))}
    ${renderKeyValueRow('Sexo', formatMaybe(paciente?.sexo))}
    ${renderKeyValueRow('Dados literais do paciente', pacienteLiteralHtml)}
    ${renderKeyValueRow('Peças histológicas', renderPecas(item?.pecas_histologicas))}
    ${renderKeyValueRow('Peça (laudo)', formatMaybe(item?.laudo?.peca))}
    ${renderKeyValueRow('Data do laudo', formatMaybe(item?.laudo?.data))}
    ${renderKeyValueRow('CID', formatMaybe(item?.laudo?.cid))}
    ${renderKeyValueRow('Material', formatMaybe(item?.material))}
    ${renderKeyValueRow('Localização', formatMaybe(item?.localizacao))}
    ${renderKeyValueRow('Diagnóstico(s)', diagnosticos)}
    ${renderKeyValueRow('Atipia/Displasia', `<div><strong>Atipia:</strong> ${formatMaybe(item?.atipia)}</div><div><strong>Displasia:</strong> ${formatMaybe(item?.displasia)}</div>`)}
    ${renderKeyValueRow('Classificação Histológica', `<span class="badge text-bg-info">${item?.histology ?? 'Indefinido'}</span>`)}
    ${renderKeyValueRow('Grau de Atipia', `<span class="badge text-bg-secondary">${item?.atypia_class ?? 'Não informado'}</span>`)}
    ${renderKeyValueRow('Tamanho do pólipo', polypSizeHtml)}
  `;
};

const renderPagination = (paginationEl, currentPage, totalPages) => {
  paginationEl.innerHTML = '';
  const createItem = (label, page, disabled = false, active = false) => {
    const li = document.createElement('li');
    li.className = `page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}`;
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'page-link';
    button.textContent = label;
    if (!disabled) {
      button.addEventListener('click', () => {
        renderGeneralTable(page);
      });
    }
    li.appendChild(button);
    return li;
  };

  paginationEl.appendChild(createItem('Anterior', currentPage - 1, currentPage === 1));

  const range = 2;
  const start = Math.max(1, currentPage - range);
  const end = Math.min(totalPages, currentPage + range);

  for (let page = start; page <= end; page += 1) {
    paginationEl.appendChild(createItem(page.toString(), page, false, page === currentPage));
  }

  paginationEl.appendChild(createItem('Próximo', currentPage + 1, currentPage === totalPages));
};

let normalizedItems = [];
let filteredItems = [];

const renderGeneralTable = (page = 1) => {
  const tableBody = document.getElementById('generalTableBody');
  const paginationEl = document.getElementById('pagination');
  if (!tableBody || !paginationEl) {
    return;
  }
  const totalPages = Math.max(1, Math.ceil(filteredItems.length / PAGE_SIZE));
  const currentPage = Math.min(Math.max(page, 1), totalPages);
  const start = (currentPage - 1) * PAGE_SIZE;
  const pageItems = filteredItems.slice(start, start + PAGE_SIZE);

  if (!pageItems.length) {
    tableBody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">Nenhum registro encontrado no JSON.</td></tr>';
    paginationEl.innerHTML = '';
    return;
  }

  tableBody.innerHTML = pageItems
    .map((item, index) => `<tbody data-item>${buildItemRows(item, start + index)}</tbody>`)
    .join('');
  renderPagination(paginationEl, currentPage, totalPages);
};

const setupSearch = () => {
  const searchInput = document.getElementById('tableSearch');
  if (!searchInput) {
    return;
  }
  searchInput.addEventListener('input', (event) => {
    const term = normalizeText(event.target.value || '');
    if (!term) {
      filteredItems = normalizedItems;
    } else {
      filteredItems = normalizedItems.filter((item) => item.searchText.includes(term));
    }
    renderGeneralTable(1);
  });
};

const showSection = (id) => {
  const section = document.getElementById(id);
  if (section) {
    section.hidden = false;
  }
};

const updateDashboard = (items) => {
  const summary = document.getElementById('summary');
  if (summary) {
    summary.hidden = false;
  }

  const totalItemsEl = document.getElementById('totalItems');
  const uniquePatientsEl = document.getElementById('uniquePatients');
  const polypSizedEl = document.getElementById('polypSized');

  if (totalItemsEl) totalItemsEl.textContent = items.length;
  if (uniquePatientsEl) uniquePatientsEl.textContent = uniquePatients(items).length;

  const polypSizedCount = items.filter((item) => item.polyp_size?.maior_eixo_mm !== null).length;
  if (polypSizedEl) polypSizedEl.textContent = polypSizedCount;

  const sexStats = statsBySex(items);
  const sexTableBody = document.querySelector('#sexTable tbody');
  if (sexTableBody) {
    sexTableBody.innerHTML = Object.entries(sexStats.counts)
      .map(
        ([label, count]) =>
          `<tr><td>${label}</td><td>${count}</td><td>${sexStats.percentages[label]}%</td></tr>`
      )
      .join('');
  }
  const sexTotal = document.getElementById('sexTotal');
  if (sexTotal) sexTotal.textContent = sexStats.total;
  const sexAlert = document.getElementById('sexAlert');
  if (sexAlert) {
    sexAlert.innerHTML = sexStats.missing ? '<div class="alert alert-warning">O dataset não possui sexo informado em todos os registros.</div>' : '';
  }

  const ageStatsData = ageStats(items);
  const ageAverage = document.getElementById('ageAverage');
  const ageMedian = document.getElementById('ageMedian');
  const ageYoungestName = document.getElementById('ageYoungestName');
  const ageYoungestAge = document.getElementById('ageYoungestAge');
  const ageOldestName = document.getElementById('ageOldestName');
  const ageOldestAge = document.getElementById('ageOldestAge');
  const ageCount = document.getElementById('ageCount');

  if (ageAverage) ageAverage.textContent = ageStatsData.average ?? 'Não informado';
  if (ageMedian) ageMedian.textContent = ageStatsData.median ?? 'Não informado';
  if (ageYoungestName) ageYoungestName.textContent = ageStatsData.youngest?.nome ?? 'Não informado';
  if (ageYoungestAge) ageYoungestAge.textContent = ageStatsData.youngest ? `${ageStatsData.youngest.idade} anos` : '-';
  if (ageOldestName) ageOldestName.textContent = ageStatsData.oldest?.nome ?? 'Não informado';
  if (ageOldestAge) ageOldestAge.textContent = ageStatsData.oldest ? `${ageStatsData.oldest.idade} anos` : '-';
  if (ageCount) ageCount.textContent = ageStatsData.count;

  const histologyStatsData = histologyStats(items);
  const histologyTableBody = document.querySelector('#histologyTable tbody');
  if (histologyTableBody) {
    histologyTableBody.innerHTML = Object.entries(histologyStatsData.counts)
      .map(
        ([label, count]) =>
          `<tr><td>${label}</td><td>${count}</td><td>${histologyStatsData.percentages_total[label]}%</td><td>${histologyStatsData.percentages_lesion[label]}%</td></tr>`
      )
      .join('');
  }
  const histologySummary = document.getElementById('histologySummary');
  if (histologySummary) {
    histologySummary.textContent = `Percentuais sobre total geral (${histologyStatsData.total}) e sobre registros com lesões/pólipos (${histologyStatsData.lesion_total}).`;
  }

  const atypiaStatsData = atypiaStats(items);
  const atypiaTableBody = document.querySelector('#atypiaTable tbody');
  if (atypiaTableBody) {
    atypiaTableBody.innerHTML = Object.entries(atypiaStatsData.counts)
      .map(
        ([label, count]) =>
          `<tr><td>${label}</td><td>${count}</td><td>${atypiaStatsData.percentages[label]}%</td></tr>`
      )
      .join('');
  }
  const atypiaTotal = document.getElementById('atypiaTotal');
  if (atypiaTotal) {
    atypiaTotal.textContent = atypiaStatsData.total;
  }

  normalizedItems = items.map((item) => ({
    ...item,
    searchText: normalizeText(joinTextFields(item)),
  }));
  filteredItems = normalizedItems;
  renderGeneralTable(1);
  setupSearch();

  ['sexo', 'idade', 'histologia', 'atipia', 'geral'].forEach(showSection);
};

const statusEl = document.getElementById('status');

fetch('./dadospacientes.json')
  .then((response) => {
    if (!response.ok) {
      throw new Error('Falha ao carregar o arquivo JSON.');
    }
    return response.json();
  })
  .then((data) => {
    const rawItems = Array.isArray(data) ? data : [];
    const items = rawItems.map((item) => normalizeItem(item || {}));
    const enriched = items.map((item) => ({
      ...item,
      histology: classifyHistology(item),
      atypia_class: classifyAtypia(item),
      polyp_size: parsePolypSizeCategory(item),
    }));
    if (statusEl) {
      statusEl.className = 'alert alert-success';
      statusEl.textContent = 'Dataset carregado com sucesso.';
    }
    updateDashboard(enriched);
  })
  .catch((error) => {
    if (statusEl) {
      statusEl.className = 'alert alert-danger';
      statusEl.textContent = `Erro ao carregar o dataset: ${error.message}`;
    }
  });



function syncNavbarHeight() {
  const nav = document.querySelector('.navbar');
  if (!nav) return;
  const h = nav.getBoundingClientRect().height;
  document.documentElement.style.setProperty('--navbar-h', `${h}px`);
}

window.addEventListener('load', syncNavbarHeight);
window.addEventListener('resize', syncNavbarHeight);
