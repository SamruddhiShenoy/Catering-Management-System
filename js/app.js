function toggleTheme() {
    const html = document.documentElement;
    const body = document.body;
    const isLight = html.classList.toggle('light-mode');
    body.classList.toggle('light-mode', isLight);
    
    const theme = isLight ? 'light' : 'dark';
    localStorage.setItem('theme', theme);
    
    // Update icons
    const icons = document.querySelectorAll('.theme-toggle-btn i');
    icons.forEach(icon => {
        if (isLight) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
    });
}

// Set initial icon state
document.addEventListener('DOMContentLoaded', () => {
    const isLight = document.documentElement.classList.contains('light-mode') || document.body.classList.contains('light-mode');
    if (isLight) {
        document.body.classList.add('light-mode');
    }
    const icons = document.querySelectorAll('.theme-toggle-btn i');
    icons.forEach(icon => {
        if (isLight) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
    });
});
