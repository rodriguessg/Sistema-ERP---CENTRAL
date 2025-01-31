<?php
// Inicie a sessão, caso ainda não tenha sido iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifique se a sessão está ativa
if (!isset($_SESSION['username']) || !isset($_SESSION['setor'])) {
    header("Location: login.php");
    exit();
}

// Inclua a conexão com o banco de dados
require_once 'bancoo.php';

// Verifique se a conexão foi estabelecida
try {
    if (!isset($pdo)) {
        die("A conexão com o banco de dados não foi estabelecida.");
    }
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

try {
    // Recupere as informações do usuário da sessão
    $username = $_SESSION['username'];
    $setor = $_SESSION['setor'];

    // Diretório base das fotos dos usuários
    $fotoBasePath = 'uploads/';

    // Busque as informações do usuário no banco de dados (foto)
    $query = $pdo->prepare("SELECT foto FROM usuario WHERE username = :username");
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    // Caminho completo da foto ou uma imagem padrão
    $foto = (!empty($result['foto']) && file_exists($fotoBasePath . $result['foto']))
        ? $fotoBasePath . $result['foto']
        : $fotoBasePath . 'perfil-user-673e5672cac27.png';

    // Configure as permissões de navegação com base no setor do usuário
    $menuItens = [];
    switch ($setor) {
        case 'administrador':
            $menuItens = [
                ['link' => 'painel.php', 'nome' => 'Painel', 'icon' => 'tachometer-alt'],
                ['link' => 'homefinanceiro.php', 'nome' => 'Financeiro', 'icon' => 'money-bill'],
                ['link' => 'homeestoque.php', 'nome' => 'Estoque', 'icon' => 'cogs'],
                ['link' => 'homepatrimonio.php', 'nome' => 'Patrimônio', 'icon' => 'building'],
                ['link' => 'cadastro_usuario.php', 'nome' => 'Cadastrar Usuário', 'icon' => 'user-plus'],
                ['link' => 'gerenciarpermissao.php', 'nome' => 'Gerenciar Permissões', 'icon' => 'key'],
                ['link' => 'homeRh.php', 'nome' => 'Recursos Humanos', 'icon' => 'users'],
                ['link' => 'homecontratos.php', 'nome' => 'Contratos', 'icon' => 'file-contract']

            ];
            break;

        case 'financeiro':
            $menuItens = [
                ['link' => 'homefinanceiro.php', 'nome' => 'Home', 'icon' => 'home'],
                ['link' => 'painelfinanceiro.php', 'nome' => 'Painel', 'icon' => 'chart-line'],
                ['link' => 'rh.php', 'nome' => 'Assinatura webmail', 'icon' => 'envelope'],
                ['link' => 'perfil.php', 'nome' => 'Perfil', 'icon' => 'user'],
                ['link' => 'sair.php', 'nome' => 'Sair', 'icon' => 'sign-out-alt'],
            ];
            break;

        case 'patrimonio':
            $menuItens = [
                ['link' => 'painelpatrimonio.php', 'nome' => 'Painel', 'icon' => 'cogs'],
                ['link' => 'homepatrimonio.php', 'nome' => 'Home', 'icon' => 'home'],
                ['link' => 'rh.php', 'nome' => 'Assinatura webmail', 'icon' => 'envelope'],
            ];
            break;

        case 'estoque':
            $menuItens = [
                ['link' => 'painelestoque.php', 'nome' => 'Painel', 'icon' => 'cogs'],
                ['link' => 'homeestoque.php', 'nome' => 'Home', 'icon' => 'home'],
                ['link' => 'rh.php', 'nome' => 'Assinatura webmail', 'icon' => 'envelope'],
            ];
            break;

        case 'recursos_humanos':
            $menuItens = [
                ['link' => 'painelRh.php', 'nome' => 'Painel', 'icon' => 'cogs'],
                ['link' => 'homeRh.php', 'nome' => 'Home', 'icon' => 'home'],
                ['link' => 'cracha.php', 'nome' => 'Gerador Cracha', 'icon' => 'id-card'],
                ['link' => 'rh.php', 'nome' => 'Assinatura webmail', 'icon' => 'envelope'],
            ];
            break;

        default:
            // Caso o setor não seja reconhecido, redireciona para a página de erro
            header("Location: mensagem.php?mensagem=setor_nao_reconhecido&pagina=login.php");
            exit();
    }
} catch (PDOException $e) {
    // Caso haja erro ao buscar informações do banco de dados, exibe a mensagem de erro
    die("Erro ao acessar as informações do usuário: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEMA INTEGRADO CENTRAL</title>
    <!-- Incluindo o Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Incluindo o CSS customizado -->
    <link rel="stylesheet" href="./src/style/menu-lateral.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <!-- Menu Lateral -->
        <nav id="sidebar" class="active">

            <!-- Seção de perfil -->
            <div class="profile-section">
                <a href="#" data-toggle="modal" data-target="#perfilModal">
                    <img src="<?= htmlspecialchars(!empty($foto) ? $foto : '/default.png') ?>" alt="Perfil" class="rounded-circle">
                    <p><?= htmlspecialchars($username) ?></p>
                </a>
            </div>
            
            <!-- Itens do menu -->
            <ul class="list-unstyled components">
                <?php foreach ($menuItens as $item): ?>
                    <li>
                        <a href="<?= htmlspecialchars($item['link']) ?>">
                            <i class="fa fa-<?= htmlspecialchars($item['icon']) ?>"></i> 
                            <span class="menu-text"><?= htmlspecialchars($item['nome']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <ul class="list-unstyled components">
    <!-- Outros itens do menu -->

    <!-- Botão Sair -->
    <li class="exit-btn">
        <a href="sair.php" class="exit-btn-link">
            <i class="fa fa-sign-out-alt exit-icon"></i>
            <span>Sair</span> <!-- O texto Sair será ocultado quando o menu estiver minimizado -->
        </a>
    </li>
</ul>
        </nav>

        <!-- Conteúdo Principal -->
        <div id="content" class="content">
            <!-- Conteúdo já existente -->
        </div>

        <!-- Modal Perfil -->
        <div class="modal fade" id="perfilModal" tabindex="-1" aria-labelledby="perfilModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="perfilModalLabel">Perfil do Usuário</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <img src="<?= htmlspecialchars(!empty($foto) ? $foto : '/default.png') ?>" alt="Perfil" class="rounded-circle mb-3" style="width: 70px; height: 70px;">
                        <p><strong>Nome:</strong> <?= htmlspecialchars($username) ?></p>
                        <p><strong>Email:</strong> <span id="modal-email"></span></p>
                        <p><strong>Setor:</strong> <span id="modal-setor"></span></p>
                        <p><strong>Tempo de Registro:</strong> <span id="modal-tempo-registro"></span></p>
                        <p><strong>Movimentações Realizadas:</strong> <span id="modal-movimentacoes"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery e Bootstrap 4 JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./src/js/menu-lateral.js"></script>
</body>
</html>

