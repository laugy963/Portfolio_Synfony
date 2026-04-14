const initializeAccountSettings = () => {
    const deleteConfirmationInput = document.getElementById('deleteConfirmation');
    const deleteAccountButton = document.getElementById('deleteAccountBtn');
    const deleteAccountForm = document.getElementById('deleteAccountForm');
    const deleteModal = document.getElementById('deleteAccountModal');

    if (deleteConfirmationInput && deleteAccountButton && deleteAccountForm) {
        const updateDeleteButtonState = () => {
            const isConfirmed = deleteConfirmationInput.value.trim().toUpperCase() === 'SUPPRIMER';
            deleteAccountButton.disabled = !isConfirmed;
            deleteAccountButton.classList.toggle('btn-outline-danger', isConfirmed);
            deleteAccountButton.classList.toggle('btn-danger', !isConfirmed);
        };

        updateDeleteButtonState();
        deleteConfirmationInput.addEventListener('input', updateDeleteButtonState);

        deleteAccountButton.addEventListener('click', () => {
            if (!window.confirm('Cette action est irréversible. Confirmez-vous la suppression définitive du compte ?')) {
                return;
            }

            deleteAccountForm.submit();
        });

        if (deleteModal) {
            deleteModal.addEventListener('hidden.bs.modal', () => {
                deleteConfirmationInput.value = '';
                updateDeleteButtonState();
            });

            deleteModal.addEventListener('shown.bs.modal', () => {
                deleteConfirmationInput.focus();
            });
        }
    }

    const passwordInput = document.getElementById('newPassword');
    if (passwordInput) {
        const requirements = {
            length: (value) => value.length >= 6,
            lowercase: (value) => /[a-z]/.test(value),
            uppercase: (value) => /[A-Z]/.test(value),
            number: (value) => /\d/.test(value),
        };

        const updateRequirements = () => {
            Object.entries(requirements).forEach(([key, validator]) => {
                const element = document.getElementById(key);
                if (!element) {
                    return;
                }

                element.classList.toggle('valid', validator(passwordInput.value));
            });
        };

        updateRequirements();
        passwordInput.addEventListener('input', updateRequirements);
    }
};

document.addEventListener('DOMContentLoaded', initializeAccountSettings);
