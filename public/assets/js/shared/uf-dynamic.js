(function () {
    "use strict";

    const selects = Array.from(document.querySelectorAll("[data-uf-dynamic='true']"));
    if (selects.length === 0) {
        return;
    }

    let sharedPromise = null;

    const loadUfs = async () => {
        if (sharedPromise) {
            return sharedPromise;
        }

        sharedPromise = fetch("/api/territorios/ufs", {
            headers: { "Accept": "application/json" }
        })
            .then(async (response) => {
                if (!response.ok) {
                    return [];
                }

                const payload = await response.json();
                return Array.isArray(payload.data) ? payload.data : [];
            })
            .catch(() => [])
            .then((rows) => rows);

        return sharedPromise;
    };

    const render = async (select) => {
        const rows = await loadUfs();
        if (rows.length === 0) {
            return;
        }

        const includeEmpty = select.getAttribute("data-uf-include-empty") === "true";
        const emptyLabel = select.getAttribute("data-uf-empty-label") || "Selecione";
        const preferred = (select.getAttribute("data-uf-selected") || select.value || "").trim().toUpperCase();

        select.innerHTML = "";

        if (includeEmpty) {
            const option = document.createElement("option");
            option.value = "";
            option.textContent = emptyLabel;
            select.appendChild(option);
        }

        rows.forEach((item) => {
            const sigla = String(item.sigla || "").toUpperCase();
            const nome = String(item.nome || "");
            if (sigla.length !== 2) {
                return;
            }

            const option = document.createElement("option");
            option.value = sigla;
            option.textContent = nome ? (sigla + " - " + nome) : sigla;
            if (preferred !== "" && sigla === preferred) {
                option.selected = true;
            }
            select.appendChild(option);
        });

        if (preferred !== "") {
            select.value = preferred;
        }
    };

    selects.forEach((select) => {
        render(select);
    });
})();
