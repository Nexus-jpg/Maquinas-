document.addEventListener("DOMContentLoaded", function () {

    const btnFiltro = document.getElementById("btnFiltro");
    const grade = document.querySelector(".grade-pedidos");

    if (btnFiltro && grade) {
        btnFiltro.addEventListener("click", function () {
            const cards = Array.from(grade.children);
            cards.reverse().forEach(function (card) {
                grade.appendChild(card);
            });
        });
    }
    const formAvaliar = document.querySelector(".form-avaliar");
    if (formAvaliar) {
        formAvaliar.addEventListener("submit", function (evento) {
            const notaMarcada = formAvaliar.querySelector("input[name='nota']:checked");
            if (!notaMarcada) {
                evento.preventDefault();
                alert("Escolha uma nota de 1 a 5 estrelas antes de enviar.");
            }
        });
    }
});
