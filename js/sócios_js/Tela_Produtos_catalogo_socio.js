
document.addEventListener("DOMContentLoaded", function () {

    const formularios = document.querySelectorAll(".form-solicitar");

    formularios.forEach(function (form) {
        const select = form.querySelector("select[name='tipo_contrato']");
        const botao  = form.querySelector(".btn-solicitar");

        if (!select || !botao) return;

        botao.disabled = true;
        botao.style.opacity = "0.5";

        select.addEventListener("change", function () {
            const preenchido = select.value !== "";
            botao.disabled = !preenchido;
            botao.style.opacity = preenchido ? "1" : "0.5";
        });
    });
});
