<?php
session_start(); // Inicia a sessão do PHP
include 'conexao.php'; // Inclui a conexão com o banco de dados
use PHPMailer\PHPMailer\PHPMailer; // Importa PHPMailer
use PHPMailer\PHPMailer\Exception; // Importa Exception
require 'vendor/autoload.php'; // Autoload do Composer (PHPMailer)

$mensagem = ""; // Variável para armazenar mensagens de sucesso ou erro

// Processa o formulário de redefinição
if ($_SERVER["REQUEST_METHOD"] == "POST") { // Verifica se o formulário foi enviado via POST
    $email = trim($_POST['email']); // Recebe e limpa o e-mail
    $codigo = trim($_POST['codigo']); // Recebe e limpa o código de redefinição
    $novaSenha = $_POST['senha']; // Recebe a nova senha
    $confirmaSenha = $_POST['confirmaSenha']; // Recebe a confirmação da nova senha

    if ($novaSenha !== $confirmaSenha) { // Verifica se as senhas coincidem
        $mensagem = "As senhas não coincidem!"; // Mensagem de erro
    } else {
        // Busca usuário pelo e-mail
        $sql = "SELECT * FROM usuario WHERE email = ?"; // SQL para buscar usuário
        $stmt = $conn->prepare($sql); // Prepara a consulta
        $stmt->bind_param("s", $email); // Substitui o parâmetro pelo e-mail
        $stmt->execute(); // Executa a consulta
        $result = $stmt->get_result(); // Obtém resultado

        if ($result->num_rows === 1) { // Se usuário encontrado
            $usuario = $result->fetch_assoc(); // Pega os dados do usuário

            // Verifica se o código de redefinição expirou
            $agora = date("Y-m-d H:i:s"); // Data/hora atual
            if (!$usuario['codigo_redefinicao_expires'] || $agora > $usuario['codigo_redefinicao_expires']) {
                $mensagem = "Código expirou. <a href='esqueci_senha.php'>Solicite um novo código</a>."; // Código expirado
            } else {
                // Verifica se o código é válido
                if (password_verify($codigo, $usuario['codigo_redefinicao_hash'])) { // Compara código informado com hash
                    // Atualiza senha
                    $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT); // Cria hash da nova senha
                    $sqlUpdate = "UPDATE usuario 
                                  SET senha = ?, 
                                      codigo_redefinicao_hash = NULL, 
                                      codigo_redefinicao_criado = NULL, 
                                      codigo_redefinicao_expires = NULL
                                  WHERE id_usuario = ?"; // SQL para atualizar senha e limpar código
                    $stmtUpdate = $conn->prepare($sqlUpdate); // Prepara update
                    $stmtUpdate->bind_param("si", $senhaHash, $usuario['id_usuario']); // Substitui parâmetros
                    if ($stmtUpdate->execute()) { // Executa update
                        $mensagem = "Senha redefinida com sucesso! <a href='login.php'>Faça login</a>."; // Sucesso
                    } else {
                        $mensagem = "Erro ao redefinir senha: " . $conn->error; // Erro ao atualizar banco
                    }
                    $stmtUpdate->close(); // Fecha statement de update
                } else {
                    $mensagem = "Código inválido. Verifique e tente novamente."; // Código incorreto
                }
            }
        } else {
            $mensagem = "Usuário não encontrado!"; // Usuário não existe
        }
        $stmt->close(); // Fecha statement de select
    }
    $conn->close(); // Fecha conexão
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8"> <!-- Codificação -->
<meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsividade -->
<title>Redefinir Senha - Foco Diário</title> <!-- Título -->
<link rel="stylesheet" href="style.css"> <!-- CSS -->
</head>
<body>

<header class="header-container"> <!-- Cabeçalho -->
    <div class="site-title">
        <h1><a href="index.php">Foco Diário</a></h1> <!-- Título clicável -->
    </div>
</header>

<main class="container-cadastro"> <!-- Conteúdo principal -->
    <div class="cadastro-form"> <!-- Formulário de redefinição -->
        <h2>Redefinir Senha</h2>
        <form action="#" method="POST"> <!-- Formulário via POST -->
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required> <!-- Input e-mail -->
            </div>
            <div class="form-group">
                <label for="codigo">Código de Redefinição</label>
                <input type="text" id="codigo" name="codigo" required> <!-- Input código -->
            </div>
            <div class="form-group">
                <label for="senha">Nova Senha</label>
                <input type="password" id="senha" name="senha" required> <!-- Input nova senha -->
            </div>
            <div class="form-group">
                <label for="confirmaSenha">Confirmar Nova Senha</label>
                <input type="password" id="confirmaSenha" name="confirmaSenha" required> <!-- Input confirmação -->
            </div>
            <p class="mensagem-erro"><?php echo $mensagem; ?></p> <!-- Exibe mensagens -->
            <button type="submit">Redefinir Senha</button> <!-- Botão enviar -->
        </form>
    </div>
</main>

<footer>
<p>&copy; 2025 Foco Diário. Todos os direitos reservados.</p> <!-- Rodapé -->
</footer>

</body>
</html>
