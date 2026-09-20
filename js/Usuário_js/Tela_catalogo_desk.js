document.addEventListener('DOMContentLoaded', () => {
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const navMenu = document.getElementById('navMenu');

    if (hamburgerBtn && navMenu) {
        hamburgerBtn.addEventListener('click', () => {
            navMenu.classList.toggle('show');
        });
    }
    const inputBusca = document.getElementById('inputBuscaCatalogo');
    const cardsMaquinas = document.querySelectorAll('.card-maquina');
    if (inputBusca && cardsMaquinas.length > 0) {
        inputBusca.addEventListener('input', (e) => {
            const termo = e.target.value.toLowerCase().trim();
            cardsMaquinas.forEach(card => {
                const nomeMaquina = card.querySelector('.maquina-nome')?.textContent.toLowerCase() || '';
                const descMaquina = card.querySelector('.maquina-desc')?.textContent.toLowerCase() || '';
                if (nomeMaquina.includes(termo) || descMaquina.includes(termo)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
