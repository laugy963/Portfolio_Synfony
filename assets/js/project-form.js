const createImagePreview = (file, altText) => {
    const wrapper = document.createElement('figure');
    wrapper.className = 'project-preview-card';

    const image = document.createElement('img');
    image.alt = altText;
    image.width = 240;
    image.height = 135;

    const caption = document.createElement('figcaption');
    caption.textContent = file.name;

    const reader = new FileReader();
    reader.addEventListener('load', (event) => {
        image.src = event.target?.result ?? '';
    });
    reader.readAsDataURL(file);

    wrapper.append(image, caption);

    return wrapper;
};

const initializeProjectForm = () => {
    const form = document.querySelector('[data-project-form]');
    if (!form) {
        return;
    }

    const bannerInput = form.querySelector('[data-project-banner-input]');
    const bannerPreview = form.querySelector('[data-banner-preview]');
    const galleryInput = form.querySelector('[data-project-gallery-input]');
    const galleryPreview = form.querySelector('[data-gallery-preview]');

    if (bannerInput && bannerPreview) {
        bannerInput.addEventListener('change', () => {
            bannerPreview.replaceChildren();

            const [file] = bannerInput.files ?? [];
            if (!file) {
                return;
            }

            bannerPreview.appendChild(createImagePreview(file, 'Apercu de la banniere selectionnee'));
        });
    }

    if (galleryInput && galleryPreview) {
        galleryInput.addEventListener('change', () => {
            galleryPreview.replaceChildren();

            Array.from(galleryInput.files ?? []).forEach((file, index) => {
                galleryPreview.appendChild(createImagePreview(file, `Apercu de l image ${index + 1}`));
            });
        });
    }
};

document.addEventListener('DOMContentLoaded', initializeProjectForm);
