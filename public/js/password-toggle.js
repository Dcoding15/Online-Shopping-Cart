document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.password-toggle').forEach((button) => {
        button.addEventListener('click', () => {
            const field = button.closest('.password-field');
            const input = field?.querySelector('input[type="password"], input[type="text"]');

            if (!input) return;

            const willShow = input.type === 'password';
            input.type = willShow ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(willShow));
            const label = button.dataset.passwordLabel || 'password';
            button.setAttribute('aria-label', `${willShow ? 'Hide' : 'Show'} ${label}`);
        });
    });
});
