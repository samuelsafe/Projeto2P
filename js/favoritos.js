document.addEventListener('DOMContentLoaded', function () {
    const botoes = document.querySelectorAll('.btn-favoritar');

    botoes.forEach(function (botao) {
        botao.addEventListener('click', function () {
            const livroId = botao.dataset.livroId;

            botao.disabled = true;

            fetch('favoritar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'livro_id=' + encodeURIComponent(livroId)
            })
                .then(function (resposta) {
                    return resposta.json();
                })
                .then(function (dados) {
                    if (dados.sucesso) {
                        if (dados.favoritado) {
                            botao.classList.add('ativo');
                            botao.textContent = 'Salvo';
                        } else {
                            botao.classList.remove('ativo');
                            botao.textContent = 'Salvo';

                            if (document.body.dataset.pagina === 'favoritos') {
                                const box = botao.closest('.box');
                                if (box) box.remove();
                            }
                        }
                    } else {
                        alert(dados.mensagem || 'Não foi possível salvar.');
                    }
                })
                .catch(function () {
                    alert('Erro de conexão. Tente novamente.');
                })
                .finally(function () {
                    botao.disabled = false;
                });
        });
    });
});
