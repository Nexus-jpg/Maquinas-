document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("formSuporte");

    if (!form) return;

    const campoNome      = document.getElementById("nome");
    const campoSobrenome = document.getElementById("sobrenome");
    const campoEmail     = document.getElementById("email");
    const campoMensagem  = document.getElementById("mensagem");

    [campoNome, campoSobrenome, campoEmail, campoMensagem].forEach(function (campo) {
        campo.addEventListener("input", function () {
            limparErro(campo);
        });
    });

    form.addEventListener("submit", function (evento) {
        let formularioValido = true;

        formularioValido = validarObrigatorio(campoNome, "Digite seu nome.") && formularioValido;
        formularioValido = validarObrigatorio(campoSobrenome, "Digite seu sobrenome.") && formularioValido;
        formularioValido = validarEmail(campoEmail) && formularioValido;
        formularioValido = validarObrigatorio(campoMensagem, "Escreva sua mensagem.") && formularioValido;

        if (!formularioValido) {
            evento.preventDefault();
        }
    });

    function validarObrigatorio(campo, textoErro) {
        const valor = campo.value.trim();

        if (valor === "") {
            mostrarErro(campo, textoErro);
            return false;
        }

        limparErro(campo);
        return true;
    }

    function validarEmail(campo) {
        const valor = campo.value.trim();
        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (valor === "") {
            mostrarErro(campo, "Digite seu e-mail.");
            return false;
        }

        if (!regexEmail.test(valor)) {
            mostrarErro(campo, "Digite um e-mail válido.");
            return false;
        }

        limparErro(campo);
        return true;
    }

    function mostrarErro(campo, texto) {
        campo.classList.add("input-erro");

        let spanErro = campo.parentElement.querySelector(".erro-campo[data-campo='" + campo.id + "']");

        if (!spanErro) {
            spanErro = document.createElement("span");
            spanErro.classList.add("erro-campo");
            spanErro.setAttribute("data-campo", campo.id);
            campo.insertAdjacentElement("afterend", spanErro);
        }

        spanErro.textContent = texto;
    }

    function limparErro(campo) {
        campo.classList.remove("input-erro");

        const spanErro = campo.parentElement.querySelector(".erro-campo[data-campo='" + campo.id + "']");
        if (spanErro) {
            spanErro.remove();
        }
    }
});
