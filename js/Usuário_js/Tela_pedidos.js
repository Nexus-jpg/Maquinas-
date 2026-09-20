document.addEventListener('DOMContentLoaded', () => {
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const navMenu = document.getElementById('navMenu');
    if (hamburgerBtn && navMenu) {
        hamburgerBtn.addEventListener('click', () => {
            navMenu.classList.toggle('show');
        });
    }
    const tabBtns = document.querySelectorAll('.tab-btn');
    const searchInput = document.getElementById('searchInput');
    const cardsPedidos = document.querySelectorAll('.card-pedido');

    let currentFilter = 'todos';
    let currentSearch = '';

    function filterCards() {
        cardsPedidos.forEach(card => {
            const cardStatus = card.getAttribute('data-status') || '';
            const cardNome = card.getAttribute('data-nome') || '';
            let matchesStatus = false;
            if (currentFilter === 'todos') {
                matchesStatus = true;
            } else if (currentFilter === 'em_andamento') {
                matchesStatus = ['em_andamento', 'confirmado', 'ativo'].includes(cardStatus);
            } else if (currentFilter === 'entregue') {
                matchesStatus = (cardStatus === 'entregue');
            } else if (currentFilter === 'concluido') {
                matchesStatus = ['concluido', 'finalizado'].includes(cardStatus);
            } else if (currentFilter === 'pendente') {
                matchesStatus = (cardStatus === 'pendente');
            }

            const matchesSearch = cardNome.includes(currentSearch.toLowerCase());
            if (matchesStatus && matchesSearch) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            currentFilter = btn.getAttribute('data-filter') || 'todos';
            filterCards();
        });
    });
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            currentSearch = e.target.value.trim();
            filterCards();
        });
    }

    const toggleCommentBtns = document.querySelectorAll('.btn-toggle-comments');

    toggleCommentBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const targetSection = document.getElementById(targetId);

            if (targetSection) {
                const isHidden = targetSection.classList.contains('hidden');
                
                if (isHidden) {
                    targetSection.classList.remove('hidden');
                    btn.innerHTML = btn.innerHTML.replace('&#9660;', '&#9650;').replace('▼', '▲');
                } else {
                    targetSection.classList.add('hidden');
                    btn.innerHTML = btn.innerHTML.replace('&#9650;', '&#9660;').replace('▲', '▼');
                }
            }
        });
    });

    const modalAvaliacao = document.getElementById('modalAvaliacao');
    const closeModalBtn = document.getElementById('closeModal');
    const btnCancelModal = document.getElementById('btnCancelModal');
    const modalPedidoId = document.getElementById('modalPedidoId');
    const modalNotaInput = document.getElementById('modalNota');
    const modalComentarioTextarea = document.getElementById('modalComentario');
    const starBtns = document.querySelectorAll('#starInputGroup .star-btn');

    function setModalRating(nota) {
        modalNotaInput.value = nota;
        starBtns.forEach(star => {
            const starValue = parseInt(star.getAttribute('data-value'));
            if (starValue <= nota) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }

    starBtns.forEach(star => {
        star.addEventListener('click', () => {
            const valor = parseInt(star.getAttribute('data-value'));
            setModalRating(valor);
        });
    });

    const btnsAvaliar = document.querySelectorAll('.btn-avaliar');
    btnsAvaliar.forEach(btn => {
        btn.addEventListener('click', () => {
            const pedidoId = btn.getAttribute('data-pedido');
            modalPedidoId.value = pedidoId;
            modalComentarioTextarea.value = '';
            setModalRating(5); 
            modalAvaliacao.classList.remove('hidden');
        });
    });

    const btnsEditarAval = document.querySelectorAll('.btn-editar-aval');
    btnsEditarAval.forEach(btn => {
        btn.addEventListener('click', () => {
            const pedidoId = btn.getAttribute('data-pedido');
            const nota = parseInt(btn.getAttribute('data-nota')) || 5;
            const comentario = btn.getAttribute('data-comentario') || '';

            modalPedidoId.value = pedidoId;
            modalComentarioTextarea.value = comentario;
            setModalRating(nota);
            modalAvaliacao.classList.remove('hidden');
        });
    });

    function closeModal() {
        modalAvaliacao.classList.add('hidden');
    }
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
    if (btnCancelModal) btnCancelModal.addEventListener('click', closeModal);
    if (modalAvaliacao) {
        modalAvaliacao.addEventListener('click', (e) => {
            if (e.target === modalAvaliacao) {
                closeModal();
            }
        });
    }
});
