<?php
session_start(); // Inicia a sessão para poder usar variáveis de sessão
include 'conexao.php'; // Inclui o arquivo de conexão com o banco de dados

$mensagem = ""; // Inicializa a variável de mensagem vazia

if ($_SERVER["REQUEST_METHOD"] == "POST") { // Verifica se o formulário foi enviado via POST
    $nome = trim($_POST['nome']); // Recebe e remove espaços do início/fim do nome
    $email = trim($_POST['email']); // Recebe e remove espaços do e-mail
    $senha = trim($_POST['senha']); // Recebe e remove espaços da senha
    $nivel_acesso = trim($_POST['nivel_acesso']); // Recebe e remove espaços do nível de acesso

    if (empty($nome) || empty($email) || empty($senha) || empty($nivel_acesso)) { // Verifica se algum campo está vazio
        $mensagem = "Todos os campos são obrigatórios!"; // Define mensagem de erro
    } else {
        // Verifica se já existe administrador com o mesmo email
        $sqlVerifica = "SELECT * FROM administrador WHERE email = ?"; // Consulta para verificar duplicidade de email
        $stmtVerifica = $conn->prepare($sqlVerifica); // Prepara a consulta SQL
        $stmtVerifica->bind_param("s", $email); // Vincula o parâmetro email à consulta
        $stmtVerifica->execute(); // Executa a consulta
        $result = $stmtVerifica->get_result(); // Obtém o resultado da consulta

        if ($result->num_rows > 0) { // Se existir algum registro com o mesmo email
            $mensagem = "Já existe um administrador com este e-mail!"; // Define mensagem de erro
        } else {
            // Cria hash da senha
            $hashSenha = password_hash($senha, PASSWORD_DEFAULT); // Gera um hash seguro da senha

            // Insere no banco
            $sqlInsert = "INSERT INTO administrador (nome, email, senha, nivel_acesso) VALUES (?, ?, ?, ?)"; // SQL para inserir novo administrador
            $stmtInsert = $conn->prepare($sqlInsert); // Prepara a consulta de inserção
            $stmtInsert->bind_param("ssss", $nome, $email, $hashSenha, $nivel_acesso); // Vincula os parâmetros à consulta

            if ($stmtInsert->execute()) { // Tenta executar a inserção
                $mensagem = "Administrador cadastrado com sucesso!"; // Define mensagem de sucesso
            } else {
                $mensagem = "Erro ao cadastrar administrador: " . $stmtInsert->error; // Define mensagem de erro com detalhe
            }

            $stmtInsert->close(); // Fecha o statement de inserção
        }

        $stmtVerifica->close(); // Fecha o statement de verificação
    }

    $conn->close(); // Fecha a conexão com o banco
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8"> <!-- Define codificação UTF-8 -->
<meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Ajusta responsividade -->
<title>Cadastrar Administrador - Foco Diário</title> <!-- Título da página -->
<link rel="stylesheet" href="style.css"> <!-- Link para o CSS externo -->
</head>
<body>

<header class="header-container">
    <div class="site-title">
        <h1><a href="index.php">Foco Diário</a></h1> <!-- Título clicável que leva à página inicial -->
    </div>
</header>

<main class="container-cadastro">
    <div class="cadastro-form">
        <h2>Cadastrar Novo Administrador</h2> <!-- Título do formulário -->
        <form action="#" method="POST"> <!-- Formulário que envia dados via POST para a mesma página -->
            <div class="form-group">
                <label for="nome">Nome</label> <!-- Label para o campo nome -->
                <input type="text" id="nome" name="nome" required> <!-- Input de texto para nome -->
            </div>
            <div class="form-group">
                <label for="email">E-mail</label> <!-- Label para o campo email -->
                <input type="email" id="email" name="email" required> <!-- Input de email -->
            </div>
            <div class="form-group">
                <label for="senha">Senha</label> <!-- Label para o campo senha -->
                <input type="password" id="senha" name="senha" required> <!-- Input de senha -->
            </div>
            <div class="form-group">
                <label for="nivel_acesso">Nível de Acesso</label> <!-- Label para o campo nível de acesso -->
                <select id="nivel_acesso" name="nivel_acesso" required> <!-- Select para escolher nível -->
                    <option value="">Selecione</option>
                    <option value="Super">Super</option>
                    <option value="Editor">Editor</option>
                    <option value="Moderador">Moderador</option>
                </select>
            </div>
            <p class="mensagem-erro"><?php echo $mensagem; ?></p> <!-- Exibe mensagens de erro ou sucesso -->
            <button type="submit">Cadastrar</button> <!-- Botão de envio do formulário -->
        </form>
        <p><a href="login_admin.php"> login administrativo</a></p> <!-- Link para login de administrador -->
        <p><a href="login.php">Voltar</a><br></p> <!-- Link para voltar à página de login normal -->
    </div>
</main>

<footer>
    <p>&copy; 2025 Foco Diário. Todos os direitos reservados.</p> <!-- Rodapé da página -->
</footer>

</body>
</html>
