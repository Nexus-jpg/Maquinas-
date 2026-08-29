document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('contactForm');
    form.addEventListener('submit', (event) => {
        const emailInput = document.getElementById('email').value;
        if (!emailInput.includes('@')) {
            alert('Por favor, insira um e-mail válido.');
            event.preventDefault();
        }
    });
});
