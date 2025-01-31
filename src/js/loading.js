document.addEventListener("DOMContentLoaded", function () {
    const loadingContainer = document.querySelector('.loading-container');
    const modal = document.querySelector('.modal-container');

    // Simula o carregamento com um timer (3.5 segundos)
    setTimeout(() => {
        loadingContainer.style.display = 'none'; // Esconde a tela de carregamento
        // Redireciona para outra página
        window.location.href = "painelpatrimonio.php"; // Coloque a URL desejada aqui
    }, 3500);
});

// Função para fechar o modal (se necessário)
function closeModal() {
    document.querySelector('.modal-container').style.display = 'none';
}

// Ação para um botão no modal (se existir)
function submitForm() {
    alert('Ação enviada!');
}
