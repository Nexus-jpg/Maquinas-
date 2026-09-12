document.addEventListener("DOMContentLoaded", function () {
    const selectRecurso = document.getElementById("recurso");
    const selectTipo    = document.getElementById("tipo");
    const btnFiltro     = document.getElementById("btnFiltro");


    const listaTipos = typeof tiposPorRecurso !== "undefined" ? tiposPorRecurso : {};

    function popularTipos() {
        const recursoEscolhido = selectRecurso.value;
        const tipos = listaTipos[recursoEscolhido] || {};

        selectTipo.innerHTML = "";

        if (recursoEscolhido === "" || Object.keys(tipos).length === 0) {
            selectTipo.disabled = true;
            const opcaoVazia = document.createElement("option");
            opcaoVazia.value = "";
            opcaoVazia.textContent = "Selecione um recurso primeiro...";
            opcaoVazia.disabled = true;
            opcaoVazia.selected = true;
            selectTipo.appendChild(opcaoVazia);
            atualizarBotaoFiltro();
            return;
        }

        selectTipo.disabled = false;

        const opcaoPadrao = document.createElement("option");
        opcaoPadrao.value = "";
        opcaoPadrao.textContent = "Selecione...";
        opcaoPadrao.disabled = true;
        opcaoPadrao.selected = true;
        selectTipo.appendChild(opcaoPadrao);

        for (const valor in tipos) {
            const opcao = document.createElement("option");
            opcao.value = valor;
            opcao.textContent = tipos[valor];
            selectTipo.appendChild(opcao);
        }

        atualizarBotaoFiltro();
    }

    function atualizarBotaoFiltro() {
        const recursoPreenchido = selectRecurso.value !== "";
        const tipoPreenchido    = selectTipo.value !== "";
        btnFiltro.disabled = !(recursoPreenchido && tipoPreenchido);
    }

    if (selectRecurso && selectTipo && btnFiltro) {
        selectRecurso.addEventListener("change", popularTipos);
        selectTipo.addEventListener("change", atualizarBotaoFiltro);
    }


    const selectPagina = document.getElementById("pagina");
    const btnPagina    = document.getElementById("btnPagina");

    function atualizarBotaoPagina() {
        btnPagina.disabled = selectPagina.value === "";
    }

    if (selectPagina && btnPagina) {
        selectPagina.addEventListener("change", atualizarBotaoPagina);
    }

});
