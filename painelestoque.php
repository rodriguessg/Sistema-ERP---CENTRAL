<?php
include 'header.php'; ?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Estoque</title>
    <link rel="stylesheet" href="./src/style/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
         /* Estilo do painel */
        .painel { padding: 20px; font-family: Arial, sans-serif;    margin-bottom: 111px; }
        .cardss { display: flex; gap: 20px; margin-bottom: 20px; }
        .card { flex: 1; padding: 20px; background: #f9f9f9; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); text-align: center; }
        .card2 { flex: 1; padding: 20px; background: #dc3737; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); text-align: center; }
        .grafico-container { max-width: 800px; margin: 0 auto; }
        #totalProdutos {
            margin-top: 0;
    font-size: 23px;
    margin-bottom: 2rem;
        } 
        .modal {
    /* background: #fff; */
    padding: 20px;
    border-radius: 8px;
    /* width: 400px; */
    /* margin-left: 30%; */
    /* max-width: 90%; */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);

}
    </style>
</head>
<body>
    <div class="painel">
        <h1>Painel de Estoque</h1>

        <!-- Cards -->
        <div class="cardss">
    <div class="card">
        <h3>Total de Produtos</h3>
        <p id="totalProdutos">Carregando...</p>
    </div>
    <div class="card2" id="cardProdutoAcabando" style="display: none;">
        <h3>Produto Acabando</h3>
        <p id="produtoAcabando">Carregando...</p>
        <ul id="listaProdutosAcabando"></ul> <!-- Lista de produtos com pouca unidade -->
    </div>
</div>


        <!-- Gráfico -->
        <div class="grafico-container">
            <canvas id="graficoProdutos"></canvas>
        </div>
    </div>

    <script>
        // Carregar dados do backend para os cards e gráfico
        document.addEventListener("DOMContentLoaded", () => {
            // Buscar dados do backend
            fetch('dados_estoque.php')
                .then(response => response.json())
                .then(data => {
                    // Atualizar cards
                    document.getElementById('totalProdutos').textContent = data.totalProdutos;
                    document.getElementById('produtoAcabando').textContent = data.produtoAcabando.nome 
                        + " (" + data.produtoAcabando.quantidade + " unidades)";

                    // Renderizar gráfico
                    const ctx = document.getElementById('graficoProdutos').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.produtos.map(p => p.nome),
                            datasets: [{
                                label: 'Quantidade de Produtos',
                                data: data.produtos.map(p => p.quantidade),
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: { beginAtZero: true }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Estoque por Produto (Mês/Ano)'
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Erro ao carregar dados:', error));
        });


        document.addEventListener("DOMContentLoaded", () => {
    // Função para atualizar os cards com os dados do backend
    function atualizarCards(data) {
        const totalProdutosCard = document.getElementById('totalProdutos');
        const produtoAcabandoCard = document.getElementById('produtoAcabando');
        const cardProdutoAcabando = document.getElementById('cardProdutoAcabando');
        const listaProdutosAcabando = document.getElementById('listaProdutosAcabando');
        
        // Atualiza o total de produtos
        totalProdutosCard.textContent = data.totalProdutos;

        // Verifica se há produtos acabando
        const produtosAcabando = data.produtos.filter(produto => produto.quantidade < 10);
        
        if (produtosAcabando.length > 0) {
            // Exibe o card de produtos acabando
            cardProdutoAcabando.style.display = 'block';

            // Preenche a lista de produtos com pouca unidade
            listaProdutosAcabando.innerHTML = ''; // Limpa a lista antes de adicionar os novos itens
            produtosAcabando.forEach(produto => {
                const li = document.createElement('li');
                li.textContent = `${produto.nome} - ${produto.quantidade} unidades`;
                listaProdutosAcabando.appendChild(li);
            });

            // Atualiza o texto do produto que está acabando
            produtoAcabandoCard.textContent = `${produtosAcabando.length} produto(s) acabando`;
        } else {
            // Se não houver produtos acabando, oculta o card
            cardProdutoAcabando.style.display = 'none';
        }
    }

    // Função para buscar dados do backend
    function buscarDados() {
        fetch('dados_estoque.php')
            .then(response => response.json())
            .then(data => {
                atualizarCards(data);
            })
            .catch(error => console.error('Erro ao carregar dados:', error));
    }

    // Iniciar o carregamento dos dados
    buscarDados();
});

    </script>

</body>
</html>


<?php include 'footer.php'; ?>