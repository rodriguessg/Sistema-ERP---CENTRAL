<?php
// contrato.php

session_start();

// Conexão com o banco de dados
$dsn = 'mysql:host=localhost;dbname=supat';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// Processar assinatura do contrato
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_contrato'])) {
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $validade = $_POST['validade'];
    $assinatura = $_POST['assinatura'];

    $sql = "INSERT INTO gestao_contratos (titulo, descricao, assinatura, validade) VALUES (:titulo, :descricao, :assinatura, :validade)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            ':titulo' => $titulo,
            ':descricao' => $descricao,
            ':assinatura' => $assinatura,
            ':validade' => $validade
        ]);

        $_SESSION['success'] = "Contrato cadastrado com sucesso!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Erro ao cadastrar contrato: " . $e->getMessage();
    }
}

// Buscar contratos próximos de expirar
$notificacoes = [];
$sql = "SELECT * FROM gestao_contratos WHERE validade <= DATE_ADD(CURDATE(), INTERVAL 1 MONTH) AND validade >= CURDATE()";
$stmt = $pdo->query($sql);
$notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Contratos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 30px;
        }

        .btn-sign {
            display: flex;
            justify-content: space-between;
        }

        .notification {
            position: relative;
            display: inline-block;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -10px;
            background: red;
            color: white;
            padding: 5px;
            border-radius: 50%;
            font-size: 12px;
        }
    </style>
</head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<style>
    .form-container3 {
        display: flex;
        height: 100%;
        gap: 20px;
    }

    .form-left {
        border-right: 1px solid #ddd;
    }

    #agendamentos {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .agendamento-item {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #f9f9f9;
    }
    .calendar-container {
        width: 100%;
        padding: 20px;
    }

    .calendar-table {
        width: 100%;
        border-collapse: collapse;
    }

    .calendar-table th, .calendar-table td {
        width: 14.28%;
        text-align: center;
        padding: 10px;
        border: 1px solid #ccc;
        vertical-align: top;
    }

    .calendar-table th {
        background-color: #f2f2f2;
    }

    .calendar-table td {
        height: 60px;
    }

    .calendar-table td .task-indicator {
        margin-top: 5px;
    }

    .calendar-table td .day-number {
        cursor: pointer;
    }

    /* Estilo do modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);
        padding-top: 60px;
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
    }

    .close-btn {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        position: absolute;
        top: 10px;
        right: 25px;
    }

    .close-btn:hover,
    .close-btn:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
</style>

<body>
    <div class="container">
        <h1 class="text-center text-success">Gestão de Contratos</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success'] ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Notificação de contratos próximos de expirar -->
        <div class="notification">
            <i class="bi bi-bell-fill" style="font-size: 24px;"></i>
            <?php if (count($notificacoes) > 0): ?>
                <span class="notification-badge"><?= count($notificacoes) ?></span>
            <?php endif; ?>
        </div>

        <?php if (count($notificacoes) > 0): ?>
            <div class="alert alert-warning mt-3">
                <strong>Contratos próximos de expirar:</strong>
                <ul>
                    <?php foreach ($notificacoes as $notificacao): ?>
                        <li><?= $notificacao['titulo'] ?> (Expira em: <?= $notificacao['validade'] ?>)</li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    
        <div class="tabs">
    <div class="tab active" data-tab="cadastrar" onclick="showTab('cadastrar')">Cadastro de contratos</div>
    <div class="tab" data-tab="retirar" onclick="showTab('consultar')">Consultar constratos</div>
    <div class="tab" data-tab="levantamento" onclick="showTab('agenda')">Agendamento</div>
    <!-- <div class="tab" data-tab="DPRE" onclick="showTab('retirar')">Retirar material</div>
    <div class="tab" data-tab="relatorio" onclick="showTab('relatorio')">Relatorio</div>
    <div class="tab" data-tab="galeria" onclick="showTab('galeria')">Galeria</div> -->
</div>

<div class="form-container3" id="cadastrar" style="display:none;">
        <!-- Formulário de cadastro -->
        <form method="POST" action="">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título do Contrato</label>
                <input type="text" id="titulo" name="titulo" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="3" required></textarea>
            </div>

            <div class="mb-3">
                <label for="validade" class="form-label">Validade</label>
                <input type="date" id="validade" name="validade" class="form-control" required>
            </div>

            <div class="btn-sign">
                <button type="button" class="btn btn-outline-primary" onclick="signGovBR()">Assinar com Gov.br</button>
                <button type="button" class="btn btn-outline-secondary" onclick="signCertificate()">Assinar com Certificado Digital</button>
            </div>

            <input type="hidden" id="assinatura" name="assinatura">
            <button type="submit" name="cadastrar_contrato" class="btn btn-success mt-3">Cadastrar Contrato</button>
        </form>
</div>

<div class="form-container3" id="consultar" style="display:none;">
    <!-- Pesquisa -->
    <div class="search-container my-4 text-center">
        <input type="text" id="searchInput" class="form-control d-inline-block w-50" placeholder="Digite o título ou descrição do contrato">
        <button class="btn btn-primary" onclick="searchContracts()">Pesquisar</button>
    </div>

    <!-- Lista de Contratos -->
    <h2 class="text-center mt-3">Lista de Contratos</h2>
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Validade</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody id="contractTableBody">
            <!-- Dados carregados via PHP -->
            <?php
            $sql = "SELECT * FROM gestao_contratos ORDER BY validade DESC";
            $stmt = $pdo->query($sql);

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['titulo']}</td>";
                echo "<td>{$row['descricao']}</td>";
                echo "<td>{$row['validade']}</td>";
                echo "<td><button class='btn btn-info btn-sm' onclick='openModal(" . json_encode($row) . ")'>Visualizar</button></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<div class="container mt-5">
        <div class="form-container3 d-flex" id="agenda" style="display:none;">
            <!-- Formulário de Agendamento -->
            <div class="form-left w-50 p-3 border-end">
                <h3>Agendar Tarefa</h3>
                <form id="formAgendamento" method="POST" action="salvar_agendamento.php">
                    <!-- Campo: Nome -->
                    <div class="form-group mb-3">
                        <label for="nome">Nome</label>
                        <input type="text" id="nome" name="nome" class="form-control" placeholder="Digite o nome" required>
                    </div>
                    <!-- Campo: Descrição -->
                    <div class="form-group mb-3">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" class="form-control" placeholder="Digite a descrição" rows="3" required></textarea>
                    </div>
                    <!-- Campo: Data -->
                    <div class="form-group mb-3">
                        <label for="data_g">Data</label>
                        <input type="date" id="data_g" name="data_g" class="form-control" required>
                    </div>
                    <!-- Campo: E-mail -->
                    <div class="form-group mb-3">
                        <label for="email">E-mail para Envio</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Digite o e-mail" required>
                    </div>
                    <!-- Botão: Salvar -->
                    <button type="submit" class="btn btn-primary w-100">Salvar Agendamento</button>
                </form>
            </div>

            <?php
// Conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "supat";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Recuperando os agendamentos do banco de dados
$sql = "SELECT id, nome, descricao, data_g, email FROM agendamentos";
$result = $conn->query($sql);

$agendamentos = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data = $row['data_g'];
        $agendamentos[$data][] = $row; // Organiza os agendamentos por data
    }
}

$conn->close();
?>

<!-- Lista de Agendamentos -->
<div class="form-right w-50 p-3">
    <h3>Agendamentos de <?php echo date("F Y"); ?></h3> <!-- Exibe o mês e ano atual -->
    <div id="agendamentos" style="max-height: 500px; overflow-y: auto;">
        <div class="calendar-container">
            <?php
            // Exibindo o mês atual
            $currentMonth = date("m");
            $currentYear = date("Y");

            // Número de dias no mês atual
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

            // Começo do mês (dia da semana)
            $firstDayOfMonth = strtotime("$currentYear-$currentMonth-01");
            $firstDayWeekday = date("w", $firstDayOfMonth); // 0 (Domingo) a 6 (Sábado)

            // Cabeçalho da agenda
            echo '<table class="calendar-table">';
            echo '<tr>';
            echo '<th>Dom</th><th>Seg</th><th>Ter</th><th>Qua</th><th>Qui</th><th>Sex</th><th>Sáb</th>';
            echo '</tr><tr>';

            // Adiciona os dias em branco no início do mês
            for ($i = 0; $i < $firstDayWeekday; $i++) {
                echo '<td></td>';
            }

            // Exibe os dias do mês
            $day = 1;
            for ($i = $firstDayWeekday; $i < 7; $i++) {
                echo '<td>';
                echo '<span class="day-number" data-day="' . $day . '">' . $day . '</span>'; // Dia interativo
                $currentDate = "$currentYear-$currentMonth-" . str_pad($day, 2, '0', STR_PAD_LEFT);

                // Verifica se há agendamentos para esse dia
                if (isset($agendamentos[$currentDate])) {
                    echo '<div class="task-indicator" style="background-color: blue; color: white; font-size: 10px;">Tarefa Agendada</div>';
                }

                echo '</td>';
                $day++;
            }
            echo '</tr>';

            // Preenche o restante dos dias do mês
            while ($day <= $daysInMonth) {
                echo '<tr>';
                for ($i = 0; $i < 7; $i++) {
                    if ($day <= $daysInMonth) {
                        $currentDate = "$currentYear-$currentMonth-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                        echo '<td>';
                        echo '<span class="day-number" data-day="' . $day . '">' . $day . '</span>'; // Dia interativo

                        // Verifica se há agendamentos para esse dia
                        if (isset($agendamentos[$currentDate])) {
                            echo '<div class="task-indicator" style="background-color: blue; color: white; font-size: 10px;">Tarefa Agendada</div>';
                        }

                        echo '</td>';
                        $day++;
                    } else {
                        echo '<td></td>';
                    }
                }
                echo '</tr>';
            }
            echo '</table>';
            ?>
        </div>
    </div>
</div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


<!-- Modal para exibir as tarefas -->
<div id="taskModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h4>Agendamentos do Dia</h4>
        <div id="taskDetails"></div> <!-- Exibe as tarefas do dia selecionado -->
    </div>
</div>


<script>
    // Função para buscar contratos
    function searchContracts() {
        const searchValue = document.getElementById('searchInput').value.trim();
        const tableBody = document.getElementById('contractTableBody');

        if (searchValue === '') {
            alert('Por favor, digite algo para pesquisar.');
            return;
        }

        // Enviar pesquisa para o servidor
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'search_contracts.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);

                // Limpa a tabela
                tableBody.innerHTML = '';

                if (response.length > 0) {
                    // Adiciona os resultados encontrados
                    response.forEach(row => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${row.id}</td>
                            <td>${row.titulo}</td>
                            <td>${row.descricao}</td>
                            <td>${row.validade}</td>
                            <td><button class="btn btn-info btn-sm" onclick='openModal(${JSON.stringify(row)})'>Visualizar</button></td>
                        `;
                        tableBody.appendChild(tr);
                    });
                } else {
                    // Informa que não encontrou resultados
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td colspan="5" class="text-center">Nenhum contrato encontrado.</td>';
                    tableBody.appendChild(tr);
                }
            }
        };
        xhr.send(`searchValue=${encodeURIComponent(searchValue)}`);
    }
</script>

    

    <!-- Modal -->
    <div class="modal fade" id="modalContrato" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Detalhes do Contrato</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Título:</strong> <span id="modalTituloContrato"></span></p>
                    <p><strong>Descrição:</strong> <span id="modalDescricao"></span></p>
                    <p><strong>Validade:</strong> <span id="modalValidade"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script>
        function signGovBR() {
            alert('Redirecionando para autenticação Gov.br...');
            document.getElementById('assinatura').value = 'Assinado via Gov.br'; // Placeholder
        }

        function signCertificate() {
            alert('Iniciando assinatura com certificado digital...');
            document.getElementById('assinatura').value = 'Assinado com Certificado Digital'; // Placeholder
        }

        function openModal(contrato) {
            document.getElementById('modalTituloContrato').innerText = contrato.titulo;
            document.getElementById('modalDescricao').innerText = contrato.descricao;
            document.getElementById('modalValidade').innerText = contrato.validade;
            var modal = new bootstrap.Modal(document.getElementById('modalContrato'));
            modal.show();
        }
    </script>
    <script src="./src/js/active.js">
    // Função para alternar entre as abas
    function showTab(tabName) {
    // Esconder todas as abas do tipo form-container e form-container2
    const tabs = document.querySelectorAll('.form-container, .form-container3');
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



</script>

<!-- JavaScript para abrir o modal e exibir as tarefas -->
<script>
    // Variável para armazenar os agendamentos
    const agendamentos = <?php echo json_encode($agendamentos); ?>;

    // Modal
    const modal = document.getElementById("taskModal");
    const closeModal = document.getElementsByClassName("close-btn")[0];

    // Quando o usuário clica em um dia
    document.querySelectorAll(".day-number").forEach((dayElement) => {
        dayElement.addEventListener("click", function() {
            const selectedDay = this.innerText;
            const selectedDate = '<?php echo $currentYear . "-" . $currentMonth; ?>-' + ('0' + selectedDay).slice(-2);

            // Verificando se existem agendamentos para o dia
            if (agendamentos[selectedDate]) {
                const taskDetails = agendamentos[selectedDate].map(task => 
                    `<p><strong>${task.nome}</strong>: ${task.descricao}</p>`
                ).join("");
                document.getElementById("taskDetails").innerHTML = taskDetails;
                modal.style.display = "block";
            } else {
                document.getElementById("taskDetails").innerHTML = "<p>Não há tarefas agendadas para este dia.</p>";
                modal.style.display = "block";
            }
        });
    });

    // Fechar o modal quando o usuário clicar no "X"
    closeModal.onclick = function() {
        modal.style.display = "none";
    }

    // Fechar o modal quando o usuário clicar fora do conteúdo do modal
    window.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    }
</script>


</body>
</html>
<?php
include 'footer.php';
?>