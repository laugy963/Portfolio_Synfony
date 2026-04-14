const initializeProjectReorder = () => {
    const panel = document.querySelector('[data-project-reorder]');
    if (!panel) {
        return;
    }

    const configureButton = document.getElementById('configure-order-btn');
    const cancelButton = document.getElementById('cancel-order-btn');
    const saveButton = document.getElementById('save-order-btn');
    const list = document.getElementById('sortable-projects');
    const csrfToken = panel.getAttribute('data-csrf-token');
    const reorderUrl = panel.getAttribute('data-reorder-url');

    if (!configureButton || !cancelButton || !saveButton || !list || !csrfToken || !reorderUrl) {
        return;
    }

    const updateIndicators = () => {
        list.querySelectorAll('.project-reorder-item').forEach((item, index) => {
            const indicator = item.querySelector('.position-indicator');
            if (indicator) {
                indicator.textContent = `${index + 1}`;
            }
        });
    };

    const togglePanel = (visible) => {
        panel.hidden = !visible;
        configureButton.classList.toggle('btn-success', visible);
        configureButton.classList.toggle('btn-outline-success', !visible);
        configureButton.textContent = visible ? 'Masquer la configuration' : 'Configurer l ordre';
    };

    const moveItem = (item, direction) => {
        const sibling = direction === 'up' ? item.previousElementSibling : item.nextElementSibling;
        if (!sibling) {
            return;
        }

        if (direction === 'up') {
            list.insertBefore(item, sibling);
        } else {
            list.insertBefore(sibling, item);
        }

        updateIndicators();
    };

    configureButton.addEventListener('click', () => {
        togglePanel(panel.hidden);
        updateIndicators();
    });

    cancelButton.addEventListener('click', () => {
        togglePanel(false);
    });

    list.querySelectorAll('.project-reorder-item').forEach((item) => {
        item.addEventListener('dragstart', () => {
            item.classList.add('is-dragging');
        });

        item.addEventListener('dragend', () => {
            item.classList.remove('is-dragging');
            updateIndicators();
        });

        item.addEventListener('dragover', (event) => {
            event.preventDefault();
            const dragging = list.querySelector('.is-dragging');
            if (!dragging || dragging === item) {
                return;
            }

            const itemBounds = item.getBoundingClientRect();
            const shouldInsertBefore = event.clientY < itemBounds.top + (itemBounds.height / 2);
            list.insertBefore(dragging, shouldInsertBefore ? item : item.nextElementSibling);
        });

        item.querySelectorAll('[data-reorder-move]').forEach((button) => {
            button.addEventListener('click', () => moveItem(item, button.getAttribute('data-reorder-move')));
        });
    });

    saveButton.addEventListener('click', async () => {
        const projectIds = Array.from(list.querySelectorAll('.project-reorder-item'))
            .map((item) => Number.parseInt(item.getAttribute('data-project-id') ?? '', 10))
            .filter((value) => Number.isInteger(value));

        saveButton.disabled = true;
        const originalLabel = saveButton.innerHTML;
        saveButton.innerHTML = 'Enregistrement...';

        try {
            const response = await window.fetch(reorderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ projectIds }),
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Erreur lors de la sauvegarde.');
            }

            window.location.reload();
        } catch (error) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger mt-3';
            alert.textContent = error.message;
            panel.querySelector('.card-body')?.prepend(alert);
        } finally {
            saveButton.disabled = false;
            saveButton.innerHTML = originalLabel;
        }
    });
};

document.addEventListener('DOMContentLoaded', initializeProjectReorder);
