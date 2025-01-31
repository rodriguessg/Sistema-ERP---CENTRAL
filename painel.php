<?php
include 'banco.php'; // Inclua a conexão com o banco de dados

// Consultas para obter dados para os gráficos
$statusQuery = "
    SELECT situacao, COUNT(*) AS total
    FROM patrimonio
    GROUP BY situacao
";

$usuariosQuery = "
    SELECT p.cadastrado_por AS usuario, COUNT(*) AS quantidade_cadastros
    FROM patrimonio p
    WHERE MONTH(p.data_registro) = MONTH(CURRENT_DATE()) 
      AND YEAR(p.data_registro) = YEAR(CURRENT_DATE())
    GROUP BY p.cadastrado_por
    ORDER BY quantidade_cadastros DESC
    LIMIT 5
";

$totalBensQuery = "SELECT COUNT(*) AS total FROM patrimonio";
$ativosQuery = "SELECT COUNT(*) AS total FROM patrimonio WHERE situacao = 'ativo'";
$emBaixaQuery = "SELECT COUNT(*) AS total FROM patrimonio WHERE situacao = 'Em Processo de baixa'";
$mortosQuery = "SELECT COUNT(*) AS total FROM patrimonio WHERE situacao = 'inativo'";

// Consulta para obter os últimos patrimônios cadastrados
$ultimosPatrimoniosQuery = "SELECT id, codigo, nome, descricao, data_registro, situacao AS status FROM patrimonio ORDER BY data_registro DESC LIMIT 5";

try {
    // Executa as consultas e obtém os resultados
    $statusResult = $con->query($statusQuery);
    $usuariosResult = $con->query($usuariosQuery);

    // Processa os resultados dos status
    $statusData = [];
    while ($row = $statusResult->fetch_assoc()) {
        $statusData[] = ['label' => $row['situacao'], 'value' => (int) $row['total']];
    }

    // Processa os resultados dos usuários
    $usuariosData = [];
    while ($row = $usuariosResult->fetch_assoc()) {
        $usuariosData[] = ['usuario' => $row['usuario'], 'quantidade' => (int) $row['quantidade_cadastros']];
    }

    // Dados para as estatísticas principais
    $totalBens = $con->query($totalBensQuery)->fetch_assoc()['total'] ?? 0;
    $bensAtivos = $con->query($ativosQuery)->fetch_assoc()['total'] ?? 0;
    $bensEmBaixa = $con->query($emBaixaQuery)->fetch_assoc()['total'] ?? 0;
    $bensMortos = $con->query($mortosQuery)->fetch_assoc()['total'] ?? 0;

    // Adiciona o total de bens cadastrados nos dados do gráfico de status
    $statusData[] = ['label' => 'Total de Bens Cadastrados', 'value' => $totalBens];

    // Executa a consulta para os últimos patrimônios
    $result = $con->query($ultimosPatrimoniosQuery);
    $ultimosPatrimonios = [];
    if ($result) {
        $ultimosPatrimonios = $result->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    echo "Erro ao consultar o banco de dados: " . $e->getMessage();
    exit();
}

// Fecha a conexão
$con->close();

include 'header.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Bens</title>
    <link rel="stylesheet" type="text/css" href="./src/style/painel.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Container Principal -->
    <div class="dashboard">
        <!-- Cards Resumo -->
        <div class="cards-charts-container">
            <!-- Cards Resumo -->
            <div class="cards-container">
                <div class="card">
                    <h3>Total de Bens Cadastrados</h3>
                    <p><?php echo $totalBens; ?></p>
                </div>
                <div class="card">
                    <h3>Bens Ativos</h3>
                    <p><?php echo $bensAtivos; ?></p>
                </div>
                <div class="card">
                    <h3>Bens em Processo de Baixa</h3>
                    <p><?php echo $bensEmBaixa; ?></p>
                </div>
                <div class="card">
                    <h3>Bens Mortos</h3>
                    <p><?php echo $bensMortos; ?></p>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="charts-section">
                <div class="chart-container">
                    <h3>Status dos Bens</h3>
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="chart-container">
                    <h3>Usuários que Mais Cadastraram</h3>
                    <canvas id="usuariosChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="dashboard-container">
            <!-- Tabela de Últimos Patrimônios -->
            <div class="table-container">
                <h3>Últimos Patrimônios Cadastrados</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Data de Cadastro</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimosPatrimonios as $patrimonio): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($patrimonio['codigo']); ?></td>
                                <td><?php echo htmlspecialchars($patrimonio['nome']); ?></td>
                                <td><?php echo htmlspecialchars($patrimonio['descricao']); ?></td>
                                <td><?php echo htmlspecialchars($patrimonio['data_registro']); ?></td>
                                <td>
                                    <span class="status-<?php echo strtolower(str_replace(' ', '-', $patrimonio['status'])); ?>">
                                        <?php echo htmlspecialchars($patrimonio['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tabela de Usuários que Mais Cadastraram -->
            <div class="table-container">
                <h3>Usuários que Mais Cadastraram Bens no mês</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Usuário</th>
                            <th>Setor</th>
                            <th>Quantidade de Cadastros</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($usuario = $usuariosResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['setor']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['quantidade_cadastros']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Passando os dados do PHP para o JavaScript
        const statusData = <?php echo json_encode($statusData, JSON_HEX_TAG); ?>;
        const usuariosData = <?php echo json_encode($usuariosData, JSON_HEX_TAG); ?>;

        // Inicializar o gráfico de status
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(ctxStatus, {
            type: 'pie',
            data: {
                labels: statusData.map(item => item.label),
                datasets: [{
                    data: statusData.map(item => item.value),
                    backgroundColor: ['#4CAF50', '#F44336', '#FFC107', '#2196F3'], // Adicionando uma nova cor para o total
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // Inicializar gráfico de barra
        const ctxUsuarios = document.getElementById('usuariosChart').getContext('2d');
        const usuariosChart = new Chart(ctxUsuarios, {
            type: 'bar',
            data: {
                labels: usuariosData.map(item => item.usuario),
                datasets: [{
                    label: 'Cadastros',
                    data: usuariosData.map(item => item.quantidade),
                    backgroundColor: ['#4CAF50', '#2196F3', '#FFC107'],
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Usuários',
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Quantidade',
                        },
                        beginAtZero: true,
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>
