(function () {
    "use strict";

    const fields = Array.from(document.querySelectorAll("[data-municipio-autocomplete='true']"));
    if (fields.length === 0) {
        return;
    }

    const cache = new Map();

    const resolveUf = (field) => {
        const ufSource = field.getAttribute("data-uf-source");
        if (ufSource) {
            const source = document.querySelector(ufSource);
            if (source && typeof source.value === "string") {
                return source.value.trim().toUpperCase();
            }
        }

        const staticUf = (field.getAttribute("data-uf") || "").trim().toUpperCase();
        return staticUf;
    };

    const ensureDatalist = (field) => {
        const explicit = field.getAttribute("list");
        if (explicit) {
            return document.getElementById(explicit);
        }

        const id = "municipio-list-" + Math.random().toString(36).slice(2, 8);
        const datalist = document.createElement("datalist");
        datalist.id = id;
        document.body.appendChild(datalist);
        field.setAttribute("list", id);
        return datalist;
    };

    const fetchMunicipios = async (uf, query) => {
        const key = uf + "::" + query;
        if (cache.has(key)) {
            return cache.get(key);
        }

        const url = "/api/territorios/municipios?uf=" + encodeURIComponent(uf) + "&q=" + encodeURIComponent(query);
        const response = await fetch(url, { headers: { "Accept": "application/json" } });
        if (!response.ok) {
            return [];
        }

        const payload = await response.json();
        const data = Array.isArray(payload.data) ? payload.data : [];
        cache.set(key, data);
        return data;
    };

    fields.forEach((field) => {
        const datalist = ensureDatalist(field);
        let timer = null;

        field.addEventListener("input", function () {
            const term = this.value.trim();
            const uf = resolveUf(this);
            if (uf.length !== 2 || term.length < 2) {
                datalist.innerHTML = "";
                return;
            }

            if (timer) {
                clearTimeout(timer);
            }

            timer = setTimeout(async () => {
                const items = await fetchMunicipios(uf, term);
                datalist.innerHTML = "";
                items.forEach((item) => {
                    const option = document.createElement("option");
                    option.value = item.nome_municipio || "";
                    datalist.appendChild(option);
                });
            }, 250);
        });
    });
})();
