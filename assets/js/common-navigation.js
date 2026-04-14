const initializeNavigation = () => {
    const navbar = document.querySelector('.navbar');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    const navbarToggler = document.querySelector('.navbar-toggler');
    const backToTopButton = document.getElementById('backToTop');
    const navLinks = Array.from(document.querySelectorAll('.nav-link[href*="#"]'));

    if (navbar) {
        const onScroll = () => {
            navbar.classList.toggle('navbar-scrolled', window.scrollY > 50);

            if (backToTopButton) {
                backToTopButton.classList.toggle('d-none', window.scrollY <= 300);
            }
        };

        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    if (backToTopButton) {
        backToTopButton.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    if (navbarToggler) {
        navbarToggler.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                navbarToggler.click();
            }
        });
    }

    if (navbarCollapse) {
        document.addEventListener('click', (event) => {
            if (window.innerWidth > 991 || !navbarCollapse.classList.contains('show')) {
                return;
            }

            if (!navbar.contains(event.target) && window.bootstrap?.Collapse) {
                window.bootstrap.Collapse.getOrCreateInstance(navbarCollapse).hide();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && navbarCollapse.classList.contains('show') && window.bootstrap?.Collapse) {
                window.bootstrap.Collapse.getOrCreateInstance(navbarCollapse).hide();
            }
        });
    }

    const closeMobileMenu = () => {
        if (window.innerWidth <= 991 && navbarCollapse?.classList.contains('show') && window.bootstrap?.Collapse) {
            window.bootstrap.Collapse.getOrCreateInstance(navbarCollapse).hide();
        }
    };

    navLinks.forEach((link) => {
        link.addEventListener('click', (event) => {
            const href = link.getAttribute('href');
            if (!href || !href.includes('#')) {
                return;
            }

            const [, hash] = href.split('#');
            const target = hash ? document.getElementById(hash) : null;

            if (!target) {
                return;
            }

            event.preventDefault();
            const navbarHeight = navbar?.offsetHeight ?? 0;
            const offsetTop = target.getBoundingClientRect().top + window.scrollY - navbarHeight - 16;

            window.scrollTo({
                top: offsetTop,
                behavior: 'smooth',
            });

            closeMobileMenu();
        });
    });

    const sections = ['about', 'projects', 'contact']
        .map((id) => document.getElementById(id))
        .filter(Boolean);

    if (sections.length > 0) {
        const updateActiveSection = () => {
            const navbarHeight = navbar?.offsetHeight ?? 0;
            const scrollPosition = window.scrollY + navbarHeight + 120;

            sections.forEach((section) => {
                const matchingLinks = navLinks.filter((link) => link.getAttribute('href')?.endsWith(`#${section.id}`));
                const isCurrent = scrollPosition >= section.offsetTop && scrollPosition < section.offsetTop + section.offsetHeight;

                matchingLinks.forEach((link) => {
                    link.classList.toggle('current-section', isCurrent);
                });
            });
        };

        updateActiveSection();
        window.addEventListener('scroll', updateActiveSection, { passive: true });
    }

    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    if ('IntersectionObserver' in window && animatedElements.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.15,
            rootMargin: '0px 0px -60px 0px',
        });

        animatedElements.forEach((element) => observer.observe(element));
    } else {
        animatedElements.forEach((element) => element.classList.add('visible'));
    }
};

document.addEventListener('DOMContentLoaded', initializeNavigation);
