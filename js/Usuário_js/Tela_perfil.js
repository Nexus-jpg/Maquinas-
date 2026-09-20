document.addEventListener('DOMContentLoaded', () => {
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const navMenu = document.getElementById('navMenu');

    if (hamburgerBtn && navMenu) {
        hamburgerBtn.addEventListener('click', () => {
            navMenu.classList.toggle('show');
        });
    }
    const formSenha = document.getElementById('formSenha');
    const novaSenhaInput = document.getElementById('nova_senha');
    const confirmarSenhaInput = document.getElementById('confirmar_senha');

    if (formSenha) {
        formSenha.addEventListener('submit', (e) => {
            if (novaSenhaInput.value !== confirmarSenhaInput.value) {
                e.preventDefault();
                alert('A nova senha e a confirmação de senha não coincidem.');
            }
        });
    }
});
