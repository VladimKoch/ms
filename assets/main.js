// Initialize Nette Forms on page load
import netteForms from 'nette-forms';

netteForms.initOnLoad();

// Zavření menu po kliknutí na odkaz
document.addEventListener('DOMContentLoaded', function() {
    const navbarCollapse = document.getElementById('navbarNav');
    const navLinks = navbarCollapse.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Použijeme Bootstrap Collapse API
            const bsCollapse = new bootstrap.Collapse(navbarCollapse, {
                toggle: false
            });
            bsCollapse.hide();
        });
    });
});
