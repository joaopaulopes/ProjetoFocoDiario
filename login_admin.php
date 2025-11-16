<?php
session_start(); // Inicia a sessão para controlar o login do administrador
include 'conexao.php'; // Inclui o arquivo de conexão com o banco de dados

$mensagem = ""; // Inicializa variável para mensagens de erro ou sucesso

if ($_SERVER["REQUEST_METHOD"] == "POST") { // Verifica se o formulário foi enviado via POST
    $email = trim($_POST['email']); // Recebe o e-mail do formulário e remove espaços extras
    $senha = trim($_POST['senha']); // Recebe a senha do formulário e remove espaços extras

    $sql = "SELECT * FROM administrador WHERE email = ?"; // SQL para buscar administrador pelo e-mail
    $stmt = $conn->prepare($sql); // Prepara a query para evitar SQL Injection
    $stmt->bind_param("s", $email); // Vincula o parâmetro email à query
    $stmt->execute(); // Executa a query
    $result = $stmt->get_result(); // Obtém o resultado da query

    if ($result->num_rows === 1) { // Verifica se encontrou exatamente um administrador com esse e-mail
        $admin = $result->fetch_assoc(); // Pega os dados do administrador como array associativo
        if (password_verify($senha, $admin['senha'])) { // Verifica se a senha informada corresponde ao hash do banco
            $_SESSION['id_admin'] = $admin['id_admin']; // Salva o ID do administrador na sessão
            $_SESSION['nome_admin'] = $admin['nome']; // Salva o nome do administrador na sessão
            $_SESSION['nivel_acesso'] = $admin['nivel_acesso']; // Salva o nível de acesso na sessão
            header("Location: painel_admin.php"); // Redireciona para o painel administrativo
            exit(); // Encerra o script
        } else {
            $mensagem = "Senha incorreta!"; // Define mensagem caso a senha esteja errada
        }
    } else {
        $mensagem = "Administrador não encontrado!"; // Define mensagem caso o e-mail não exista no banco
    }
    $stmt->close(); // Fecha o statement
    $conn->close(); // Fecha a conexão com o banco
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- Define a codificação UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Ajusta a página para dispositivos móveis -->
    <title>Document</title> <!-- Título da página -->
    <link rel="stylesheet" href="style.css"> <!-- Link para arquivo CSS externo -->
</head>
<body>
    <form action="#" method="POST"> <!-- Formulário de login que envia dados via POST -->
        <input type="email" name="email" placeholder="E-mail" required> <!-- Campo de e-mail obrigatório -->
        <input type="password" name="senha" placeholder="Senha" required> <!-- Campo de senha obrigatório -->
        <p style="color:red"><?php echo $mensagem; ?></p> <!-- Exibe mensagens de erro -->
        <button type="submit">Login</button> <!-- Botão de envio do formulário -->
    </form>
    <a href="login.php">Voltar</a><br> <!-- Link para voltar à página anterior -->
</body>
</html>
