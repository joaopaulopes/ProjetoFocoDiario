<?php
session_start();
include 'conexao.php';

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM usuario WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nome'] = $usuario['nome'];
            // Redireciona para a página principal
            header("Location: index.php");
            exit();
        } else {
            $mensagem = "Senha incorreta!";
        }
    } else {
        $mensagem = "Usuário não encontrado!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Foco Diário</title>
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
            <li><a href="login.php" class="active">Login</a></li>
            <li><a href="cadastro.php">Cadastro</a></li>
        </ul>
    </nav>
</header>

<main class="container-login">
    <div class="login-form">
        <h2>Acessar sua conta</h2>
        <form id="loginForm" action="#" method="POST">
            <div class="form-group">
                <label for="email">E-mail ou Usuário</label>
                <input type="text" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <p class="mensagem-erro"><?php echo $mensagem; ?></p>
            <button type="submit">Entrar</button>
            <p>Para visualizar todos os usuários cadastrados, <a href="consultar-usuario.php">clique aqui</a>.</p>
        </form>
        <p>Ainda não tem uma conta? <a href="cadastro.php">Cadastre-se aqui</a></p>
    </div>
</main>

<footer>
    <p>&copy; 2025 Foco Diário. Todos os direitos reservados.</p>
</footer>

</body>
</html>
