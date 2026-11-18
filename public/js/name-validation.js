document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-name-field]').forEach((field) => {
        field.addEventListener('input', () => {
            field.value = field.value.replace(/[^A-Za-z]/g, '');
        });
    });
});
