const initializePasswordToggles = () => {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const targetId = button.getAttribute('data-password-target');
        const input = targetId ? document.getElementById(targetId) : null;
        const icon = button.querySelector('i');

        if (!input || !icon || button.dataset.bound === 'true') {
            return;
        }

        button.dataset.bound = 'true';

        button.addEventListener('click', () => {
            const shouldReveal = input.type === 'password';
            input.type = shouldReveal ? 'text' : 'password';

            icon.classList.toggle('fa-eye', shouldReveal);
            icon.classList.toggle('fa-eye-slash', !shouldReveal);
            button.setAttribute('aria-pressed', shouldReveal ? 'true' : 'false');
            button.setAttribute('aria-label', shouldReveal ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        });
    });
};

document.addEventListener('DOMContentLoaded', initializePasswordToggles);
