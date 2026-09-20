document.addEventListener('DOMContentLoaded', () => {
    const inputInicio = document.getElementById('dataInicio');
    const inputFim = document.getElementById('dataFim');
    const displayTotal = document.getElementById('valorTotalDisplay');
    const formCheckout = document.querySelector('.checkout-grid');
    const valorDiaria = window.VALOR_DIARIA || parseFloat(document.getElementById('valorDiariaHidden')?.value || 0);
  
    function calcularTotal() {
        if (!inputInicio || !inputFim || !displayTotal) return;

        const dataInicioVal = inputInicio.value;
        const dataFimVal = inputFim.value;

        if (dataInicioVal && dataFimVal) {
            const d1 = new Date(dataInicioVal + 'T00:00:00');
            const d2 = new Date(dataFimVal + 'T00:00:00');

            const diffTempo = d2 - d1;
            const diffDias = Math.ceil(diffTempo / (1000 * 60 * 60 * 24)) + 1;

            if (diffDias > 0) {
                const total = diffDias * valorDiaria;
                displayTotal.innerText = 'R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else {
                displayTotal.innerText = 'R$ 0,00';
            }
        }
    }

    if (inputInicio && inputFim) {
        inputInicio.addEventListener('change', calcularTotal);
        inputFim.addEventListener('change', calcularTotal);
        calcularTotal();
    }

    const inputCep = document.querySelector('input[name="cep"]');
    const inputLogradouro = document.querySelector('input[name="logradouro"]');
    const inputBairro = document.querySelector('input[name="bairro"]');
    const inputCidade = document.querySelector('input[name="cidade"]');
    const inputEstado = document.querySelector('input[name="estado"]');

    if (inputCep) {
        inputCep.addEventListener('blur', () => {
            const cepLimpo = inputCep.value.replace(/\D/g, '');

            if (cepLimpo.length === 8) {
                fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`)
                    .then(response => response.json())
                    .then(dados => {
                        if (!dados.erro) {
                            if (inputLogradouro) inputLogradouro.value = dados.logradouro;
                            if (inputBairro) inputBairro.value = dados.bairro;
                            if (inputCidade) inputCidade.value = dados.localidade;
                            if (inputEstado) inputEstado.value = dados.uf;
                            
                            const inputNumero = document.querySelector('input[name="numero"]');
                            if (inputNumero) inputNumero.focus();
                        } else {
                            alert('CEP não encontrado.');
                        }
                    })
                    .catch(() => {
                        console.error('Erro ao buscar o CEP.');
                    });
            }
        });
    }
    if (formCheckout) {
        formCheckout.addEventListener('submit', (e) => {
            const d1 = new Date(inputInicio.value + 'T00:00:00');
            const d2 = new Date(inputFim.value + 'T00:00:00');

            if (d2 < d1) {
                e.preventDefault();
                alert('A data de término do aluguel deve ser igual ou posterior à data de início.');
            }
        });
    }
});
