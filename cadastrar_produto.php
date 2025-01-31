<?php
// Configurações do banco de dados
$host = 'localhost';
$user = 'root'; // Substitua pelo seu usuário do banco de dados
$password = ''; // Substitua pela sua senha
$dbname = 'supat'; // Substitua pelo nome do seu banco de dados

// Conexão com o banco de dados
$conn = new mysqli($host, $user, $password, $dbname);

// Verifica se houve erro na conexão
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Captura os dados do formulário
    $produto = $_POST['produto'];
    $classificacao = $_POST['classificacao'];
    $natureza = $_POST['natureza'];
    $contabil = $_POST['contabil'];
    $codigo = $_POST['codigo'];
    $unidade = $_POST['unidade'];
    $localizacao = $_POST['localizacao'];
    $custo = $_POST['custo'];
    $quantidade = $_POST['quantidade'];
    $preco_medio = $_POST['preco_medio'];
    $nf = $_POST['nf'];

    // Consulta para verificar se o produto já existe
    $sql_verifica = "SELECT id, quantidade FROM produtos WHERE produto = ?";
    $stmt_verifica = $conn->prepare($sql_verifica);

    if ($stmt_verifica) {
        $stmt_verifica->bind_param("s", $produto);
        $stmt_verifica->execute();
        $stmt_verifica->store_result();

        if ($stmt_verifica->num_rows > 0) {
            // Produto já existe, atualiza a quantidade
            $stmt_verifica->bind_result($id_existente, $quantidade_existente);
            $stmt_verifica->fetch();

            $nova_quantidade = $quantidade_existente + $quantidade;
            $sql_atualiza = "UPDATE produtos SET quantidade = ? WHERE id = ?";
            $stmt_atualiza = $conn->prepare($sql_atualiza);

            if ($stmt_atualiza) {
                $stmt_atualiza->bind_param("di", $nova_quantidade, $id_existente);

                if ($stmt_atualiza->execute()) {
                    echo "Produto já existe. Quantidade atualizada para $nova_quantidade.";
                } else {
                    echo "Erro ao atualizar a quantidade: " . $stmt_atualiza->error;
                }

                $stmt_atualiza->close();
            }
        } else {
            // Produto não existe, insere no banco de dados
            $sql_insere = "INSERT INTO produtos (produto, classificacao, natureza, contabil, codigo, unidade, localizacao, custo, quantidade, preco_medio, nf, tipo_operacao, data_cadastro) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'cadastrado', NOW())";
            $stmt_insere = $conn->prepare($sql_insere);

            if ($stmt_insere) {
                $stmt_insere->bind_param(
                    "sssssssddds",
                    $produto,
                    $classificacao,
                    $natureza,
                    $contabil,
                    $codigo,
                    $unidade,
                    $localizacao,
                    $custo,
                    $quantidade,
                    $preco_medio,
                    $nf
                );

                if ($stmt_insere->execute()) {
                    echo "Produto cadastrado com sucesso!";
                } else {
                    echo "Erro ao cadastrar o produto: " . $stmt_insere->error;
                }

                $stmt_insere->close();
            } else {
                echo "Erro na preparação da consulta: " . $conn->error;
            }
        }

        $stmt_verifica->close();
    } else {
        echo "Erro na preparação da consulta de verificação: " . $conn->error;
    }
}

// Fecha a conexão com o banco de dados
$conn->close();
?>
