document.addEventListener('DOMContentLoaded', function () {
    const botao = document.getElementById('botao-conta');
    const painel = document.getElementById('painel-conta');

    if (!botao || !painel) return;

    botao.addEventListener('click', function () {
        painel.classList.toggle('aberto');
    });

    document.addEventListener('click', function (evento) {
        if (!painel.contains(evento.target) && !botao.contains(evento.target)) {
            painel.classList.remove('aberto');
        }
    });
});
