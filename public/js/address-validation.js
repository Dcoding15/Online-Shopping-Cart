document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-address-field]').forEach((field) => {
        field.addEventListener('input', () => {
            field.value = field.value.replace(/[^A-Za-z0-9 ,\-()]/g, '');
        });
    });
});
