<?php
session_start();
include 'conexao.php'; // Conexão com o banco focodiario

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmaSenha = $_POST['confirmaSenha'];

    // Validação básica
    if ($senha !== $confirmaSenha) {
        $mensagem = "As senhas não coincidem!";
    } else {
        // Verifica se o e-mail já existe
        $sql = "SELECT * FROM usuario WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $mensagem = "E-mail já cadastrado!";
        } else {
            // Criptografa a senha
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            // Insere no banco
            $sql = "INSERT INTO usuario (nome, email, senha, data_cadastro) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $nome, $email, $senhaHash);

            if ($stmt->execute()) {
                $mensagem = "Cadastro realizado com sucesso! <a href='login.php'>Faça login aqui</a>.";
            } else {
                $mensagem = "Erro ao cadastrar: " . $conn->error;
            }
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Foco Diário</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header-container">
        <div class="site-title">
            <h1><a href="index.php">Foco Diário</a></h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Início</a></li>
                <li><a href="noticias-brasil.php">Brasil</a></li>
                <li><a href="noticias-mundo.php">Mundo</a></li>
                <li><a href="esportes.php">Esportes</a></li>
                <li><a href="entretenimento.php">Entretenimento</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="cadastro.php" class="active">Cadastro</a></li>
            </ul>
        </nav>
    </header>

    <main class="container-cadastro">
        <div class="cadastro-form">
            <h2>Criar uma nova conta</h2>
            <form id="cadastroForm" action="#" method="POST">
                <div class="form-group">
                    <label for="nome">Nome Completo</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required>
                </div>
                <div class="form-group">
                    <label for="confirmaSenha">Confirmar Senha</label>
                    <input type="password" id="confirmaSenha" name="confirmaSenha" required>
                </div>
                <p class="mensagem-erro"><?php echo $mensagem; ?></p>
                <button type="submit">Cadastrar</button>
                <p>Para visualizar todos os usuários cadastrados, <a href="consultar-usuario.php">clique aqui</a>.</p>
            </form>
            <p>Já tem uma conta? <a href="login.php">Faça login aqui</a></p>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Foco Diário. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
