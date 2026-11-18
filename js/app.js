document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', (event) => {
        const message = form.dataset.confirm;
        if (message && !window.confirm(message)) {
            event.preventDefault();
            return;
        }
        const button = event.submitter;
        if (button) {
            button.disabled = true;
            button.classList.add('is-saving');
        }
    });
});

const startDate = document.querySelector('#data_compromisso');
const endDate = document.querySelector('#data_fim');
if (startDate && endDate) {
    const syncMin = () => { endDate.min = startDate.value; };
    startDate.addEventListener('change', syncMin);
    syncMin();
}
