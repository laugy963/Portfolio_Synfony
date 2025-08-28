/**
 * Script JavaScript commun pour la navigation et l'interactivité générale du site
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Portfolio - Scripts chargés avec succès !');
    
    // ========== GESTION DE LA NAVIGATION ==========
    const navbar = document.querySelector('.navbar');
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Effet de transparence au scroll
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(13, 27, 62, 0.98)';
            } else {
                navbar.style.background = 'rgba(13, 27, 62, 0.95)';
            }
        });
    }
    
    // Fermer le menu mobile lors du clic sur un lien
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992) {
                const collapse = bootstrap.Collapse.getInstance(navbarCollapse);
                if (collapse) {
                    collapse.hide();
                }
            }
        });
    });
    
    // Amélioration de l'accessibilité
    if (navbarToggler) {
        navbarToggler.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    }
    
    // Focus management pour le menu mobile
    if (navbarCollapse) {
        navbarCollapse.addEventListener('shown.bs.collapse', function() {
            const firstNavLink = this.querySelector('.nav-link');
            if (firstNavLink) {
                firstNavLink.focus();
            }
        });
    }
    
    // ========== HIGHLIGHT DU LIEN ACTIF AU SCROLL (pour la page d'accueil) ==========
    const sections = ['about', 'projects', 'contact'];
    
    function highlightActiveSection() {
        if (window.location.pathname !== '/') return; // Seulement sur la page d'accueil
        
        const scrollPos = window.scrollY + 100;
        
        sections.forEach(sectionId => {
            const section = document.getElementById(sectionId);
            const navLink = document.querySelector(`a[href="#${sectionId}"]`);
            
            if (section && navLink) {
                const sectionTop = section.offsetTop;
                const sectionBottom = sectionTop + section.offsetHeight;
                
                if (scrollPos >= sectionTop && scrollPos < sectionBottom) {
                    navLinks.forEach(link => link.classList.remove('current-section'));
                    navLink.classList.add('current-section');
                }
            }
        });
    }
    
    window.addEventListener('scroll', highlightActiveSection);
    
    // ========== SMOOTH SCROLL POUR LES ANCRES ==========
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const offsetTop = target.offsetTop - 80; // Compensation pour la navbar fixe
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // ========== ANIMATION D'APPARITION AU SCROLL ==========
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('content-fade-in');
            }
        });
    }, observerOptions);
    
    // Observer les éléments avec la classe "animate-on-scroll"
    document.querySelectorAll('.animate-on-scroll').forEach(element => {
        observer.observe(element);
    });
    
    // ========== GESTION DES MESSAGES FLASH ==========
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Auto-close après 5 secondes
        setTimeout(() => {
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            }
        }, 5000);
    });
    
    // ========== TOOLTIPS BOOTSTRAP ==========
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    console.log('✅ Navigation et scripts communs initialisés !');
});
