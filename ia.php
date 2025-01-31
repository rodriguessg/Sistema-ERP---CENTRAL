<?php
header('Content-Type: application/json');

// Configurações do banco de dados
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'supat';

// Conexão com o banco de dados
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['reply' => 'Erro ao conectar ao banco de dados: ' . $conn->connect_error]);
    exit;
}

// Função para processar as mensagens do usuário
function processarMensagem($mensagem, $conn) {
    $mensagem = strtolower(trim($mensagem)); // Normaliza a entrada do usuário

    if (empty($mensagem)) {
        return "Por favor, digite algo para que eu possa ajudar.";
    }

    if (strpos($mensagem, 'o que você pode fazer') !== false) {
        return listarPossibilidades();
    } elseif (strpos($mensagem, 'relatório') !== false) {
        return gerarRelatorio($conn);
    } elseif (strpos($mensagem, 'quantos usuários') !== false || strpos($mensagem, 'usuários ativos') !== false) {
        return "Atualmente temos " . obterContagem($conn, 'usuario') . " usuários cadastrados.";
    } elseif (strpos($mensagem, 'produtos') !== false) {
        return "Há um total de " . obterContagem($conn, 'produtos') . " produtos cadastrados no sistema.";
    } elseif (strpos($mensagem, 'patrimônio') !== false) {
        return "O sistema possui " . obterContagem($conn, 'patrimonio') . " itens de patrimônio registrados.";
    } elseif (strpos($mensagem, 'setores') !== false) {
        return "Atualmente temos " . obterContagem($conn, 'setores') . " setores ativos no sistema.";
    } elseif (strpos($mensagem, 'funcionários') !== false) {
        return "Há " . obterContagem($conn, 'funcionario') . " funcionários cadastrados no sistema.";
    } elseif (strpos($mensagem, 'data de criação') !== false || strpos($mensagem, 'quando começou') !== false) {
        return "O sistema foi criado em " . obterDataCriacao($conn, 'usuario') . ".";
    } elseif (strpos($mensagem, 'ajuda') !== false || strpos($mensagem, 'como usar') !== false) {
        return "Posso ajudá-lo com informações sobre usuários, produtos, patrimônio, setores, ou funcionários. Pergunte algo específico para que eu possa ajudar!";
    } else {
        return "Eu ainda estou aprendendo. Pode me explicar melhor sua solicitação?";
    }
}

// Função para listar as possibilidades do que a IA pode fazer
function listarPossibilidades() {
    return "Posso ajudá-lo com as seguintes funcionalidades:\n" .
           "- Gerar relatórios detalhados do sistema.\n" .
           "- Informar a quantidade de usuários, produtos, itens de patrimônio, setores e funcionários cadastrados.\n" .
           "- Informar a data de criação do sistema.\n" .
           "- Responder dúvidas gerais sobre o sistema.\n" .
           "- Orientar sobre como usar funcionalidades do sistema.\n" .
           "Pergunte algo específico para que eu possa ajudar!";
}

// Função para gerar um relatório do sistema
function gerarRelatorio($conn) {
    $abas = ['Central', 'Controle Patrimonial', 'Controle Financeiro', 'Estoque', 'RH'];
    $usuariosAtivos = obterContagem($conn, 'usuario');
    $produtos = obterContagem($conn, 'produtos');
    $patrimonio = obterContagem($conn, 'patrimonio');
    $setores = obterContagem($conn, 'setores');
    $funcionarios = obterContagem($conn, 'funcionario');
    $dataCriacao = obterDataCriacao($conn, 'usuario');

    return "Relatório do Sistema:\n" .
           "- Abas existentes: " . implode(', ', $abas) . ".\n" .
           "- Usuários ativos: {$usuariosAtivos}.\n" .
           "- Produtos cadastrados: {$produtos}.\n" .
           "- Itens de patrimônio: {$patrimonio}.\n" .
           "- Setores cadastrados: {$setores}.\n" .
           "- Funcionários cadastrados: {$funcionarios}.\n" .
           "- Data de criação do sistema: {$dataCriacao}.";
}

// Função para obter contagem de registros em uma tabela
function obterContagem($conn, $tabela) {
    $tabela = $conn->real_escape_string($tabela);
    $sql = "SELECT COUNT(*) AS total FROM $tabela";
    $result = $conn->query($sql);

    if (!$result) {
        return "Erro ao acessar a tabela {$tabela}: " . $conn->error;
    }

    $row = $result->fetch_assoc();
    return $row ? $row['total'] : 0;
}

// Função para obter a data de criação do sistema
function obterDataCriacao($conn, $tabela) {
    $tabela = $conn->real_escape_string($tabela);
    $sql = "SELECT MIN(data_criacao) AS data_criacao FROM $tabela";
    $result = $conn->query($sql);

    if (!$result) {
        return "Erro ao acessar a tabela {$tabela}: " . $conn->error;
    }

    $row = $result->fetch_assoc();
    return $row && $row['data_criacao'] ? $row['data_criacao'] : 'Não disponível';
}

// Recebe e processa a mensagem do cliente
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['message']) || trim($input['message']) === '') {
    echo json_encode(['reply' => 'Por favor, envie uma mensagem válida.']);
    $conn->close();
    exit;
}

$mensagemUsuario = $input['message'];
$respostaIA = processarMensagem($mensagemUsuario, $conn);

// Fecha a conexão com o banco de dados
$conn->close();

// Retorna a resposta
echo json_encode(['reply' => $respostaIA]);
?>
