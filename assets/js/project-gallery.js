const initializeProjectGallery = () => {
    document.querySelectorAll('[data-project-gallery]').forEach((galleryRoot) => {
        const slides = Array.from(galleryRoot.querySelectorAll('.project-gallery__slide'));
        const controls = galleryRoot.querySelectorAll('[data-gallery-direction]');
        let currentIndex = slides.findIndex((slide) => slide.classList.contains('is-active'));

        if (slides.length === 0) {
            return;
        }

        currentIndex = currentIndex >= 0 ? currentIndex : 0;

        const showSlide = (nextIndex) => {
            slides[currentIndex]?.classList.remove('is-active');
            currentIndex = (nextIndex + slides.length) % slides.length;
            slides[currentIndex]?.classList.add('is-active');
        };

        controls.forEach((control) => {
            control.addEventListener('click', () => {
                const direction = Number.parseInt(control.getAttribute('data-gallery-direction') ?? '0', 10);
                showSlide(currentIndex + direction);
            });
        });
    });
};

document.addEventListener('DOMContentLoaded', initializeProjectGallery);
