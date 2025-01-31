<?php
// Verifica autenticação
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

// Cabeçalho HTML
include 'header.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Setor Financeiro</title>
    <link rel="stylesheet" href="./src/style/style.css">
    <script>
        // Função para alternar entre abas
        function abrirAba(event, abaId) {
            const abas = document.querySelectorAll('.aba-conteudo');
            abas.forEach(aba => aba.style.display = 'none');
            
            const botoes = document.querySelectorAll('.aba-botao');
            botoes.forEach(botao => botao.classList.remove('active'));
            
            document.getElementById(abaId).style.display = 'block';
            event.currentTarget.classList.add('active');
        }
    </script>
    <style>
        /* Estilos básicos */
        .container {
            width: 90%;
            margin: auto;
        }
        .abas {
            display: flex;
            margin-bottom: 20px;
        }
        .aba-botao {
            padding: 10px 20px;
            cursor: pointer;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }
        .aba-botao.active {
            background-color: #007bff;
            color: white;
        }
        .aba-conteudo {
            display: none;
        }
        .aba-conteudo.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bem-vindo ao Setor Financeiro</h1>

        <!-- Navegação entre abas -->
        <div class="abas">
            <button class="aba-botao active" onclick="abrirAba(event, 'aba-lancamentos')">Lançamentos</button>
            <button class="aba-botao" onclick="abrirAba(event, 'aba-contas')">Contas a Pagar</button>
            <button class="aba-botao" onclick="abrirAba(event, 'aba-receitas')">Receitas</button>
            <button class="aba-botao" onclick="abrirAba(event, 'aba-relatorios')">Relatórios</button>
        </div>

        <!-- Conteúdo das abas -->
        <div id="aba-lancamentos" class="aba-conteudo" style="display: block;">
            <h2>Lançamentos Financeiros</h2>
            <form action="processar_lancamento.php" method="POST">
                <label for="descricao">Descrição:</label>
                <input type="text" name="descricao" id="descricao" required>

                <label for="tipo">Tipo:</label>
                <select name="tipo" id="tipo" required>
                    <option value="receita">Receita</option>
                    <option value="despesa">Despesa</option>
                </select>

                <label for="valor">Valor:</label>
                <input type="number" name="valor" id="valor" step="0.01" required>

                <label for="data">Data:</label>
                <input type="date" name="data" id="data" required>

                <button type="submit">Salvar Lançamento</button>
            </form>
        </div>

        <div id="aba-contas" class="aba-conteudo">
            <h2>Contas a Pagar</h2>
            <form action="processar_contas.php" method="POST">
                <label for="fornecedor">Fornecedor:</label>
                <input type="text" name="fornecedor" id="fornecedor" required>

                <label for="valor-conta">Valor:</label>
                <input type="number" name="valor-conta" id="valor-conta" step="0.01" required>

                <label for="vencimento">Data de Vencimento:</label>
                <input type="date" name="vencimento" id="vencimento" required>

                <button type="submit">Registrar Conta</button>
            </form>
        </div>

        <div id="aba-receitas" class="aba-conteudo">
            <h2>Receitas Recebidas</h2>
            <form action="processar_receitas.php" method="POST">
                <label for="cliente">Cliente:</label>
                <input type="text" name="cliente" id="cliente" required>

                <label for="valor-receita">Valor:</label>
                <input type="number" name="valor-receita" id="valor-receita" step="0.01" required>

                <label for="data-receita">Data do Pagamento:</label>
                <input type="date" name="data-receita" id="data-receita" required>

                <button type="submit">Registrar Receita</button>
            </form>
        </div>

        <div id="aba-relatorios" class="aba-conteudo">
            <h2>Relatórios Financeiros</h2>
            <form action="gerar_relatorio.php" method="GET">
                <label for="relatorio-tipo">Tipo de Relatório:</label>
                <select name="relatorio-tipo" id="relatorio-tipo" required>
                    <option value="mensal">Mensal</option>
                    <option value="anual">Anual</option>
                    <option value="customizado">Customizado</option>
                </select>

                <label for="data-inicio">Data Início:</label>
                <input type="date" name="data-inicio" id="data-inicio" required>

                <label for="data-fim">Data Fim:</label>
                <input type="date" name="data-fim" id="data-fim" required>

                <button type="submit">Gerar Relatório</button>
            </form>
        </div>
    </div>
   
    <!-- Tabela de levantamento -->
    <div class="table-container">
        <table id="tabela-levantamento">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Valor</th>
                    <th>Localização</th>
                    <th>Situação</th>
                     <!-- <th>Operação</th>  -->
                    <th>Cadastrado Por</th>
                    <th>Categoria</th>
                    <th>Código</th>
                    <th>Ações</th>
                    
                </tr>
            </thead>
            <tbody>
                <!-- Dados carregados dinamicamente -->
            </tbody>
        </table>
    </div>

    <!-- Modal de Detalhes -->
    <div class="modal" id="modal-detalhes">
    <div class="modal-content">
        <!-- Cabeçalho com o logotipo -->
        <div class="">
        <img src="./src/img/Logo CENTRAL (colorida).png" alt="logo-central" class="e">
            <span class="modal-close" onclick="fecharModal()">&times;</span>
        </div>
        
        <!-- Conteúdo do modal -->
        <div id="modal-informacoes">
            <!-- As informações do patrimônio serão carregadas aqui -->
        </div>
        
        <!-- Botão de impressão -->
        <button id="imprimir-btn" onclick="imprimirDetalhes()">Imprimir</button>
    </div>
</div>


<!-- Modal de Atualização -->
<div class="modal" id="modal-atualizar">
    <div class="modal-content">
        <span class="modal-close" onclick="fecharModalAtualizar()">&times;</span>
        <h3>Atualizar Patrimônio</h3>
        <form id="form-atualizar" >
            <label for="atualizar-id">ID:</label>
            <input type="text" id="atualizar-id" name="id" readonly oninput="sincronizarValor()">

            <label for="atualizar-nome">Nome:</label>
            <input type="text" id="atualizar-nome" name="nome" required>

            <label for="atualizar-descricao">Descrição:</label>
            <input type="text" id="atualizar-descricao" name="descricao" required>

            <label for="atualizar-valor">Valor:</label>
            <input type="text" id="atualizar-valor" name="valor" step="0.01" readonly>

            <label for="atualizar-localizacao">Localização:</label>
            <input type="text" id="atualizar-localizacao" name="localizacao" disabled>

            <label for="atualizar-situacao">Situação:</label>
            <select id="atualizar-situacao" name="situacao">
                <option value="ativo">Ativo</option>
                <option value="inativo">Inativo</option>
                <option value="Em uso">Em Uso</option>
                <option value="Em Processo de baixa">Em Processo de baixa</option>
            </select>
            

            <label for="atualizar-cadastrado-por">Cadastrado Por:</label>
            <input type="text" id="atualizar-cadastrado-por" name="cadastrado_por" disabled>

            <label for="atualizar-categoria">Categoria:</label>
            <input type="text" id="atualizar-categoria" name="categoria" required>

            <label for="atualizar-codigo">Código:</label>
            <input type="text" id="atualizar-codigo" name="codigo" disabled>

            <div class="button-group">
                <button  type="submit">Salvar Alterações</button>
                <button  onclick="fecharModalAtualizar()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Função para buscar o valor pelo ID e preencher o campo Valor
 function carregarValorPorId() {
    const idField = document.getElementById('atualizar-id');
    const valorField = document.getElementById('atualizar-valor');
    const id = idField.value;

    if (id) {
        fetch(`getValor.php?id=${id}`)
            .then((response) => response.json())
            .then((data) => {
                if (data.valor !== null) {
                    valorField.value = data.valor;
                } else {
                    valorField.value = 'Valor não encontrado';
                }
            })
            .catch((error) => {
                console.error('Erro ao buscar valor:', error);
                valorField.value = 'Erro ao carregar';
            });
    } else {
        valorField.value = '';
        console.warn('ID não preenchido');
    }
}


</script>





    <!-- Paginação -->
<div class="pagination"></div>
 </div>

<?php
    // Conexão com o banco de dados
    $host = 'localhost';
    $dbname = 'supat';
    $username = 'root';
    $password = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Erro na conexão: ' . $e->getMessage();
            exit;
        }

    // Buscar dados da tabela patrimonio
    $sql = "SELECT id, codigo, nome, valor, data_registro FROM patrimonio";
    $stmt = $pdo->query($sql);
    $patrimonios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Variáveis para armazenar os valores
    $codigo = $nome = $valor = $data_registro = '';
    $depreciacao = $vida_util = 0;  // Inicializando a depreciação e vida útil
    $depreciacoes_por_ano = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patrimonio_id'])) {
        $id_selecionado = $_POST['patrimonio_id'];
        $sql = "SELECT codigo, nome, valor, data_registro FROM patrimonio WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_selecionado]);
        $patrimonio = $stmt->fetch(PDO::FETCH_ASSOC);

        $codigo = $patrimonio['codigo'];
        $nome = $patrimonio['nome'];
        $valor = $patrimonio['valor'];
        $data_registro = $patrimonio['data_registro'];

        // Definir vida útil (em anos)
        $vida_util = 5; // Exemplo genérico: vida útil de 5 anos

        // Cálculo de depreciação com base na data de registro
        $data_registro_obj = new DateTime($data_registro);

        // Depreciação linear
        $valor_residual = 0; // Supondo valor residual zero
        $depreciacao_anual = ($valor - $valor_residual) / $vida_util;

        // Cálculo da depreciação acumulada por ano
        for ($ano = 0; $ano <= $vida_util; $ano++) {
            $data_anual = clone $data_registro_obj;
            $data_anual->modify("+$ano years");

            if ($ano === $vida_util || $ano > (new DateTime())->diff($data_registro_obj)->y) {
                $depreciacao_atual = $valor;
            } else {
                $depreciacao_atual = $depreciacao_anual * $ano;
            }

            $depreciacoes_por_ano[] = [
                'ano' => $data_anual->format('Y'),
                'depreciacao' => min($depreciacao_atual, $valor)
            ];
        }
    }
?>



<div class="form-container" id="DPRE" style="display: flex; justify-content: space-between; gap: 40px; width: 100%;">
    <div class="form-section" style="width: 50%;">
        <h2>Calcular Depreciação</h2>
        <form method="POST">
            <div class="form-group">
                <label for="patrimonio_id">Selecione um Patrimônio:</label>
                <select name="patrimonio_id" id="patrimonio_id" onchange="this.form.submit()">
                    <option value="">-- Selecione --</option>
                    <?php foreach ($patrimonios as $patrimonio): ?>
                        <option value="<?= $patrimonio['id'] ?>" <?= isset($_POST['patrimonio_id']) && $_POST['patrimonio_id'] == $patrimonio['id'] ? 'selected' : '' ?>>
                            <?= $patrimonio['codigo'] ?> - <?= $patrimonio['nome'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="codigo">Código:</label>
                <input type="text" id="codigo" name="codigo" value="<?= htmlspecialchars($codigo) ?>" readonly>
            </div>
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" readonly>
            </div>
            <div class="form-group">
                <label for="valor">Valor:</label>
                <input type="text" id="valor" name="valor" value="<?= htmlspecialchars($valor) ?>" readonly>
            </div>
            <div class="form-group">
                <label for="data_registro">Data de Registro:</label>
                <input type="text" id="data_registro" name="data_registro" value="<?= htmlspecialchars($data_registro) ?>" readonly>
            </div>
        </form>
    </div>
    <div class="chart-section" style="width: 50%; display: flex; align-items: center; justify-content: center;">
        <canvas id="depreciationChart" style="width: 100%; height: auto; max-width: 555px; max-height: 277px;"></canvas>
    </div>
    </div>

    <div class="chart-container" style="width: 100%; margin-left: 20px;">
        <canvas id="depreciationChart" style="display: block; box-sizing: border-box; height: 400px; width: 555px;" width="555" height="400"></canvas>
    </div>

       
</div>




<script>
function showTab(tabId) {
    // Ocultar todo conteúdo de aba
    const tabsContent = document.querySelectorAll('.tab-content');
    tabsContent.forEach(content => content.style.display = 'none');

    // Exibir a aba correspondente
    const activeTab = document.getElementById(tabId);
    if (activeTab) {
        activeTab.style.display = 'block';
        loadCardData(); // Carregar os dados do patrimônio
    }
}
// Função para verificar se a imagem existe
async function imageExists(url) {
    try {
        const response = await fetch(url, { method: 'HEAD' });
        return response.ok; // Retorna true se a imagem existe
    } catch (error) {
        console.error(`Erro ao verificar a imagem: ${url}`, error);
        return false;
    }
}

async function loadCardData() {
    try {
        const response = await fetch('getPatrimonios.php'); // Endpoint PHP
        const data = await response.json();

        const cardsContainer = document.getElementById('cards-container');
        cardsContainer.innerHTML = ''; // Limpar conteúdo anterior

        for (const item of data) {
            const card = document.createElement('div');
            card.className = 'card';

            // URL da imagem informada no item
            const imageUrl = `uploads/${item.foto}`;

            // Verifica se a imagem existe
            const validImageUrl = await imageExists(imageUrl) ? imageUrl : 'uploads/default.png';

            card.innerHTML = `
                <div class="card-inner">
                    <!-- Frente do card -->
                    <div class="card-front">
                        <img src="${validImageUrl}" alt="${item.nome}" class="card-image">
                    </div>
                    <!-- Verso do card -->
                    <div class="card-back">
                        <h3>${item.nome}</h3>
                        <p><strong>Código:</strong> ${item.codigo}</p>
                        <p><strong>Categoria:</strong> ${item.categoria}</p>
                        <p><strong>Data de Registro:</strong> ${item.data_registro}</p>
                    </div>
                </div>
            `;

            cardsContainer.appendChild(card); // Adicionar o card ao contêiner
        }
    } catch (error) {
        console.error('Erro ao carregar dados:', error);
    }
}

// Chamar a função para carregar e exibir os dados ao carregar a página
document.addEventListener('DOMContentLoaded', loadCardData);


 
</script>

<script>
        const depreciationData = <?= json_encode($depreciacoes_por_ano) ?>;
        const years = depreciationData.map(data => data.ano);
        const values = depreciationData.map(data => data.depreciacao);

        const ctx = document.getElementById('depreciationChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: years,
                datasets: [{
                    label: 'Valor da Depreciação (R$)',
                    data: values,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `R$ ${context.parsed.y.toFixed(2)}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

<!-- Scripts -->
<script>
// Função para gerar o código automaticamente via AJAX
function gerarCodigo(categoria) {
    if (categoria) {
        fetch(`gerar_codigo.php?categoria=${categoria}`)
            .then(response => response.text())
            .then(data => {
                // Preencher o campo de código com o valor retornado
                document.getElementById("codigo").value = data;
            })
            .catch(error => console.error('Erro ao gerar o código:', error));
    }
}

// Função para alternar entre as abas
function showTab(tabName) {
    // Esconder todas as abas do tipo form-container e form-container2
    const tabs = document.querySelectorAll('.form-container, .form-container2');
    tabs.forEach(tab => tab.style.display = 'none');

    // Exibir a aba selecionada (form-container ou form-container2)
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        selectedTab.style.display = 'block';
    }

    // Atualizar o estilo das abas para mostrar qual está ativa
    const tabLinks = document.querySelectorAll('.tab');
    tabLinks.forEach(tab => tab.classList.remove('active'));
    const activeTabLink = document.querySelector(`[data-tab="${tabName}"]`);
    if (activeTabLink) {
        activeTabLink.classList.add('active');
    }
}


// Mostrar a aba 'cadastrar' como padrão quando a página for carregada
window.onload = function() {
    showTab('cadastrar');
};


 // Função de filtro da tabela - Exemplo para ilustrar o funcionamento
 function filtrarTabela() {
    // Obter os valores dos filtros
    const identificacao = document.getElementById('filtro-identificacao').value.toLowerCase().trim();
    const situacao = document.getElementById('filtro-situacao').value.toLowerCase().trim();
    const operacao = document.getElementById('filtro-operacao').value.toLowerCase().trim();

    // Selecionar todas as linhas do corpo da tabela
    const linhas = document.querySelectorAll('#tabela-levantamento tbody tr');

    linhas.forEach(linha => {
        // Obter os valores das colunas relevantes
        const colunaIdentificacao = linha.cells[1]?.textContent.toLowerCase().trim() || '';
        const colunaSituacao = linha.cells[5]?.textContent.toLowerCase().trim() || '';
        const colunaOperacao = linha.cells[2]?.textContent.toLowerCase().trim() || '';

        // Comparar os valores das colunas com os filtros
        const matchIdentificacao = !identificacao || colunaIdentificacao.includes(identificacao);
        const matchSituacao = !situacao || colunaSituacao.includes(situacao);
        const matchOperacao = !operacao || colunaOperacao.includes(operacao);

        // Exibir ou ocultar a linha com base nos critérios
        linha.style.display = matchIdentificacao && matchSituacao && matchOperacao ? '' : 'none';
    });
}


     // Função para abrir o modal e carregar conteúdo de modaldetalhes.php
     document.querySelectorAll('.detalhes-btn').forEach(button => {
        button.addEventListener('click', function() {
            const patrimonioId = this.getAttribute('data-id');
            abrirModal(patrimonioId);
        });
    });

  // Função para abrir o modal e carregar os detalhes
function abrirModalDetalhes(id) {
    // Encontrar a linha da tabela que corresponde ao id do patrimônio
    const linhas = document.querySelectorAll('tbody tr');
    let patrimonio = {};

    linhas.forEach(linha => {
        const tdId = linha.querySelector('td:first-child').textContent; // ID da linha
        if (tdId == id) {
            patrimonio = {
                id: tdId,
                nome: linha.cells[1].textContent, // Nome
                descricao: linha.cells[2].textContent, // Descrição
                valor: linha.cells[3].textContent, // Valor
                localizacao: linha.cells[4].textContent, // Localização
                situacao: linha.cells[5].textContent, // Situação
                cadastrado_por: linha.cells[6].textContent, // Cadastrado Por
                categoria: linha.cells[7].textContent, // Categoria
                codigo: linha.cells[8].textContent // Código
            };
        }
    });

    // Preencher as informações no modal com os dados do patrimônio
    const modalConteudo = document.getElementById('modal-informacoes');
    modalConteudo.innerHTML = `
        <h3>Detalhes do Patrimônio</h3>
        <p><strong>ID:</strong> ${patrimonio.id}</p>
        <p><strong>Nome:</strong> ${patrimonio.nome}</p>
        <p><strong>Descrição:</strong> ${patrimonio.descricao}</p>
        <p><strong>Valor:</strong> ${patrimonio.valor}</p>
        <p><strong>Localização:</strong> ${patrimonio.localizacao}</p>
        <p><strong>Situação:</strong> ${patrimonio.situacao}</p>
        <p><strong>Cadastrado Por:</strong> ${patrimonio.cadastrado_por}</p>
        <p><strong>Categoria:</strong> ${patrimonio.categoria}</p>
        <p><strong>Código:</strong> ${patrimonio.codigo}</p>
    `;

    // Exibir o modal
    const modal = document.getElementById('modal-detalhes');
    modal.style.display = 'block';
}

// Função para fechar o modal
function fecharModal() {
    const modal = document.getElementById('modal-detalhes');
    modal.style.display = 'none';
}


// Função para abrir o modal de atualização
function abrirModalAtualizar(id) {
    // Encontrar a linha correspondente ao ID
    const linhas = document.querySelectorAll('tbody tr');
    let patrimonio = {};

    linhas.forEach(linha => {
        const tdId = linha.querySelector('td:first-child').textContent; // ID da linha
        if (tdId == id) {
            patrimonio = {
                id: tdId,
                nome: linha.cells[1].textContent, // Nome
                descricao: linha.cells[2].textContent, // Descrição
                valor: linha.cells[3].textContent.replace(/[^\d,.-]/g, ''), // Remover símbolos de moeda
                localizacao: linha.cells[4].textContent, // Localização
                situacao: linha.cells[5].textContent, // Situação
                cadastrado_por: linha.cells[6].textContent, // Cadastrado Por
                categoria: linha.cells[7].textContent, // Categoria
                codigo: linha.cells[8].textContent // Código
            };
        }
    });

    // Preencher os campos do formulário com os dados do patrimônio
    document.getElementById('atualizar-id').value = patrimonio.id;
    document.getElementById('atualizar-nome').value = patrimonio.nome;
    document.getElementById('atualizar-descricao').value = patrimonio.descricao;
    document.getElementById('atualizar-valor').value = patrimonio.valor;
    document.getElementById('atualizar-localizacao').value = patrimonio.localizacao;
    document.getElementById('atualizar-situacao').value = patrimonio.situacao.toLowerCase();
    document.getElementById('atualizar-cadastrado-por').value = patrimonio.cadastrado_por;
    document.getElementById('atualizar-categoria').value = patrimonio.categoria;
    document.getElementById('atualizar-codigo').value = patrimonio.codigo;

    // Exibir o modal de atualização
    document.getElementById('modal-atualizar').style.display = 'block';
}

// Função para fechar o modal de atualização
function fecharModalAtualizar() {
    document.getElementById('modal-atualizar').style.display = 'none';
}

document.getElementById('form-atualizar').addEventListener('submit', function (event) {
    event.preventDefault(); // Evita o comportamento padrão de envio do formulário

    // Coleta os dados do formulário
    const formData = new FormData(this);

    // Envia os dados via fetch para o script de atualização
    fetch('modalatualizabp.php', {
        method: 'POST',
        body: formData,
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error(`Erro HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            if (data.success) {
                alert('Dados atualizados com sucesso!');
                fecharModalAtualizar(); // Fecha o modal
                location.reload(); // Atualiza a tabela ou página
            } else {
                alert('Erro ao atualizar os dados: ' + data.message);
            }
        })
        .catch((error) => {
            console.error('Erro ao enviar os dados:', error);
            alert('Erro ao atualizar os dados. Tente novamente.');
        });
});


// Função para imprimir os detalhes do patrimônio
function imprimirDetalhes() {
    const conteudo = document.getElementById('modal-informacoes').innerHTML;
    const logoURL = './src/img/Logo CENTRAL (colorida).png'; // Substitua pelo caminho do logotipo
    const janelaImpressao = window.open('', '_blank');

    janelaImpressao.document.open();
    janelaImpressao.document.write(`
        <html>
            <head>
                
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        margin: 20px;
                    }
                    .header2 {
                        text-align: center;
                        margin-bottom: 20px;
                    }
                    .header2 img {
                        max-height: 80px;
                    }
                </style>
            </head>
            <body>
                <div class="header2">
                    <img src="${logoURL}" alt="Logotipo da Empresa">
                </div>
                <div>${conteudo}</div>
            </body>
        </html>
    `);

    janelaImpressao.document.close();
    janelaImpressao.print();
}



let paginaAtual = 1; // Página inicial
const itensPorPagina = 5;

// Função para carregar dados do servidor
async function carregarDados(pagina) {
    try {
        const response = await fetch(`paginasTabela.php?pagina=${pagina}`); // Substitua pelo caminho correto do PHP
        const resultado = await response.json();

        atualizarTabela(resultado.dados);
        atualizarBotoes(resultado.total_paginas);
    } catch (error) {
        console.error('Erro ao carregar dados:', error);
    }
}

// Atualizar a tabela com os dados recebidos
function atualizarTabela(dados) {
    const tbody = document.querySelector('tbody');
    tbody.innerHTML = ''; // Limpar a tabela

    dados.forEach(dado => {
        const row = document.createElement('tr');
        row.innerHTML = `
                <td>${dado.id}</td>
                <td>${dado.nome}</td>
                <td>${dado.descricao}</td>
                <td>${parseFloat(dado.valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</td>
                <td>${dado.localizacao}</td>
                <td>${dado.situacao}</td>
                
                <td>${dado.cadastrado_por}</td>
                <td>${dado.categoria}</td>
                <td>${dado.codigo}</td>
                <td class="actions">
                    <button class="btn1" onclick="abrirModalDetalhes('${dado.id}')">+ Detalhes</button>
                    <button class="btn2" onclick="abrirModalAtualizar('${dado.id}')">Atualizar</button>
                </td>
            `;

        tbody.appendChild(row);
    });
}

// Atualizar os botões de paginação
function atualizarBotoes(totalPaginas) {
    const paginacao = document.querySelector('.pagination');
    paginacao.innerHTML = ''; // Limpar os botões

    for (let i = 1; i <= totalPaginas; i++) {
        const button = document.createElement('button');
        button.textContent = i;
        button.classList.toggle('active', i === paginaAtual);
        button.onclick = () => {
            paginaAtual = i;
            carregarDados(paginaAtual);
        };
        paginacao.appendChild(button);
    }
}

// Inicializar a página
window.onload = () => {
    carregarDados(paginaAtual);
};



</script>

<script src="src/js/script.js"></script>
</body>
</html>
<?php include 'footer.php'; ?>
    <!-- <script>
        // Configuração inicial para ativar a primeira aba
        document.querySelector('.aba-botao').click();
    </script>
</body>
</html> -->
