(() => {
    const forms = document.querySelectorAll("form[data-guard-submit='true']");

    forms.forEach((form) => {
        let isSubmitting = false;
        form.addEventListener("submit", () => {
            if (isSubmitting) {
                return false;
            }

            isSubmitting = true;

            const button = form.querySelector("button[type='submit']");
            if (!button) {
                return true;
            }

            const text = button.querySelector(".button-text");
            const loading = button.querySelector(".button-loading");

            button.disabled = true;
            if (text) text.hidden = true;
            if (loading) loading.hidden = false;
            if (!loading) button.textContent = "Processando...";

            return true;
        });
    });
})();

