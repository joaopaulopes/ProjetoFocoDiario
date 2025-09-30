<?php
session_start();
include 'conexao.php'; // Conexão com o banco focodiario

$mensagem = "";

// Processa atualização ou exclusão
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['atualizar'])) {
        $id_usuario = $_POST['id_usuario'];
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        if (!empty($senha)) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "UPDATE usuario SET nome=?, email=?, senha=? WHERE id_usuario=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $nome, $email, $senhaHash, $id_usuario);
        } else {
            $sql = "UPDATE usuario SET nome=?, email=? WHERE id_usuario=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $nome, $email, $id_usuario);
        }

        if ($stmt->execute()) {
            $mensagem = "Usuário atualizado com sucesso!";
        } else {
            $mensagem = "Erro ao atualizar: " . $conn->error;
        }
    }

    if (isset($_POST['excluir'])) {
        $id_usuario = $_POST['id_usuario'];
        $sql = "DELETE FROM usuario WHERE id_usuario=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        if ($stmt->execute()) {
            $mensagem = "Usuário excluído com sucesso!";
        } else {
            $mensagem = "Erro ao excluir: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Consultar Usuário - Foco Diário</title>
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
            <li><a href="consultar-usuario.php" class="active">Consultar Usuário</a></li>
        </ul>
    </nav>
</header>

<main class="container-cadastro">
    <h2>Lista de Usuários</h2>

    <?php if ($mensagem != "") { echo "<p class='mensagem-erro'>$mensagem</p>"; } ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Senha (nova)</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM usuario ORDER BY id_usuario ASC";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($usuario = $result->fetch_assoc()) {
                    echo "<tr id='editar-{$usuario['id_usuario']}'>
                        <form method='POST'>
                            <input type='hidden' name='id_usuario' value='{$usuario['id_usuario']}'>
                            <td>{$usuario['id_usuario']}</td>
                            <td><input type='text' name='nome' value='".htmlspecialchars($usuario['nome'])."'></td>
                            <td><input type='email' name='email' value='".htmlspecialchars($usuario['email'])."'></td>
                            <td><input type='password' name='senha' placeholder='Nova senha'></td>
                            <td>
                                <button type='submit' name='atualizar'>Atualizar</button>
                                <button type='submit' name='excluir' onclick=\"return confirm('Tem certeza que deseja excluir este usuário?');\" style='background-color:red;'>Excluir</button>
                            </td>
                        </form>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>Nenhum usuário encontrado.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</main>

<footer>
    <p>&copy; 2025 Foco Diário. Todos os direitos reservados.</p>
</footer>

</body>
</html>

<?php
$conn->close();
?>
