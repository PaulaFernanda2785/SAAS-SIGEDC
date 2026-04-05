(() => {
  const API_UFS = '/api/public/territorios/ufs';
  const API_MUNICIPIOS = '/api/public/territorios/municipios';

  const fetchJson = async (url) => {
    const response = await fetch(url, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    return response.json();
  };

  const populateUfSelects = async () => {
    const selects = Array.from(document.querySelectorAll('[data-uf-select]'));
    if (selects.length === 0) {
      return;
    }

    try {
      const payload = await fetchJson(API_UFS);
      const rows = Array.isArray(payload?.data) ? payload.data : [];
      if (rows.length === 0) {
        return;
      }

      selects.forEach((select) => {
        const previousValue = (select.value || '').toUpperCase();
        select.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Selecione';
        select.appendChild(placeholder);

        rows.forEach((row) => {
          const sigla = String(row?.sigla || '').toUpperCase();
          if (!sigla) {
            return;
          }

          const option = document.createElement('option');
          option.value = sigla;
          option.textContent = `${sigla} - ${String(row?.nome || '')}`;
          if (sigla === previousValue) {
            option.selected = true;
          }
          select.appendChild(option);
        });
      });
    } catch (_error) {
      // fallback: keep server-rendered options
    }
  };

  const attachMunicipioAutocomplete = () => {
    const inputs = Array.from(document.querySelectorAll('[data-municipio-input]'));
    if (inputs.length === 0) {
      return;
    }

    inputs.forEach((input) => {
      const form = input.closest('form');
      if (!form) {
        return;
      }

      const ufSelect = form.querySelector('[data-uf-select]');
      const listId = input.getAttribute('data-municipio-list') || '';
      if (!ufSelect || !listId) {
        return;
      }

      const list = document.getElementById(listId);
      if (!list) {
        return;
      }

      let requestId = 0;
      let debounceTimer = null;

      const clearOptions = () => {
        list.innerHTML = '';
      };

      const renderOptions = (rows) => {
        clearOptions();
        rows.forEach((row) => {
          const name = String(row?.nome_municipio || '').trim();
          if (!name) {
            return;
          }

          const option = document.createElement('option');
          option.value = name;
          list.appendChild(option);
        });
      };

      const loadMunicipios = async () => {
        const uf = String(ufSelect.value || '').toUpperCase();
        const query = String(input.value || '').trim();

        if (!uf || query.length < 2) {
          clearOptions();
          return;
        }

        const currentRequestId = ++requestId;
        try {
          const params = new URLSearchParams({ uf, q: query, limit: '20' });
          const payload = await fetchJson(`${API_MUNICIPIOS}?${params.toString()}`);
          if (currentRequestId !== requestId) {
            return;
          }

          const rows = Array.isArray(payload?.data) ? payload.data : [];
          renderOptions(rows);
        } catch (_error) {
          if (currentRequestId === requestId) {
            clearOptions();
          }
        }
      };

      input.addEventListener('input', () => {
        if (debounceTimer) {
          window.clearTimeout(debounceTimer);
        }

        debounceTimer = window.setTimeout(loadMunicipios, 180);
      });

      ufSelect.addEventListener('change', () => {
        input.value = '';
        clearOptions();
      });
    });
  };

  document.addEventListener('DOMContentLoaded', async () => {
    await populateUfSelects();
    attachMunicipioAutocomplete();
  });
})();
