<?php
session_start(); // Inicia a sessão do PHP
include 'conexao.php'; // Inclui a conexão com o banco de dados Foco Diário

$mensagem = ""; // Variável para armazenar mensagens de sucesso ou erro

// Processa atualização, exclusão ou ativação manual
if ($_SERVER["REQUEST_METHOD"] == "POST") { // Verifica se o formulário foi enviado via POST
    $id_usuario = $_POST['id_usuario']; // Recebe o ID do usuário selecionado

    if (isset($_POST['atualizar'])) { // Verifica se o botão "Atualizar" foi clicado
        $nome = $_POST['nome']; // Recebe o nome atualizado
        $email = $_POST['email']; // Recebe o e-mail atualizado
        $senha = $_POST['senha']; // Recebe a nova senha (opcional)

        if (!empty($senha)) { // Se foi digitada uma nova senha
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT); // Cria hash da nova senha
            $sql = "UPDATE usuario SET nome=?, email=?, senha=? WHERE id_usuario=?"; // SQL para atualizar nome, email e senha
            $stmt = $conn->prepare($sql); // Prepara a consulta SQL
            $stmt->bind_param("sssi", $nome, $email, $senhaHash, $id_usuario); // Substitui os parâmetros
        } else { // Se não foi digitada nova senha
            $sql = "UPDATE usuario SET nome=?, email=? WHERE id_usuario=?"; // SQL para atualizar apenas nome e email
            $stmt = $conn->prepare($sql); // Prepara a consulta SQL
            $stmt->bind_param("ssi", $nome, $email, $id_usuario); // Substitui os parâmetros
        }

        if ($stmt->execute()) { // Executa a atualização
            $mensagem = "Usuário atualizado com sucesso!"; // Mensagem de sucesso
        } else {
            $mensagem = "Erro ao atualizar: " . $conn->error; // Mensagem de erro caso falhe
        }
    }

    if (isset($_POST['excluir'])) { // Verifica se o botão "Excluir" foi clicado
        $sql = "DELETE FROM usuario WHERE id_usuario=?"; // SQL para deletar o usuário
        $stmt = $conn->prepare($sql); // Prepara a consulta
        $stmt->bind_param("i", $id_usuario); // Substitui o parâmetro
        if ($stmt->execute()) { // Executa a exclusão
            $mensagem = "Usuário excluído com sucesso!"; // Mensagem de sucesso
        } else {
            $mensagem = "Erro ao excluir: " . $conn->error; // Mensagem de erro
        }
    }

    if (isset($_POST['ativar'])) { // Verifica se o botão "Ativar" foi clicado
        $sql = "UPDATE usuario SET verificado=1 WHERE id_usuario=?"; // SQL para ativar o usuário
        $stmt = $conn->prepare($sql); // Prepara a consulta
        $stmt->bind_param("i", $id_usuario); // Substitui o parâmetro
        if ($stmt->execute()) { // Executa a atualização
            $mensagem = "Usuário ativado manualmente!"; // Mensagem de sucesso
        } else {
            $mensagem = "Erro ao ativar usuário: " . $conn->error; // Mensagem de erro
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <!-- Define codificação de caracteres -->
    <title>Consultar Usuário - Foco Diário</title> <!-- Título da página -->
    <link rel="stylesheet" href="style.css"> <!-- Link para arquivo CSS -->
</head>
<body>

<header class="header-container"> <!-- Cabeçalho do site -->
    <div class="site-title">
        <h1><a href="index.php">Foco Diário</a></h1> <!-- Título clicável -->
    </div>
    <nav> <!-- Menu de navegação -->
        <ul>
            <li><a href="index.php">Início</a></li>
            <li><a href="consultar-usuario.php" class="active">Consultar Usuário</a></li>
        </ul>
    </nav>
</header>

<main class="container-cadastro"> <!-- Conteúdo principal -->
    <h2>Lista de Usuários</h2>

    <?php if ($mensagem != "") { echo "<p class='mensagem-erro'>$mensagem</p>"; } ?> <!-- Exibe mensagem de erro ou sucesso -->

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Senha (nova)</th>
                <th>Verificado</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM usuario ORDER BY id_usuario ASC"; // Seleciona todos os usuários ordenados pelo ID
            $result = $conn->query($sql); // Executa a consulta
            if ($result->num_rows > 0) { // Se houver usuários
                while ($usuario = $result->fetch_assoc()) { // Percorre cada usuário
                    $verificado = $usuario['verificado'] ? 'Sim' : 'Não'; // Define se está verificado
                    echo "<tr id='editar-{$usuario['id_usuario']}'>
                        <form method='POST'> <!-- Formulário para cada usuário -->
                            <input type='hidden' name='id_usuario' value='{$usuario['id_usuario']}'> <!-- Armazena ID do usuário -->
                            <td>{$usuario['id_usuario']}</td> <!-- Exibe ID -->
                            <td><input type='text' name='nome' value='".htmlspecialchars($usuario['nome'])."'></td> <!-- Campo editável do nome -->
                            <td><input type='email' name='email' value='".htmlspecialchars($usuario['email'])."'></td> <!-- Campo editável do e-mail -->
                            <td><input type='password' name='senha' placeholder='Nova senha'></td> <!-- Campo para nova senha -->
                            <td>$verificado</td> <!-- Exibe status de verificação -->
                            <td>
                                <button type='submit' name='atualizar'>Atualizar</button> <!-- Botão de atualização -->
                                <button type='submit' name='excluir' onclick=\"return confirm('Tem certeza que deseja excluir este usuário?');\" style='background-color:red;'>Excluir</button> <!-- Botão de exclusão com confirmação -->
                                ".(!$usuario['verificado'] ? "<button type='submit' name='ativar' style='background-color:green;'>Ativar</button>" : "")." <!-- Botão para ativar se não verificado -->
                            </td>
                        </form>
                    </tr>";
                }
            } else { // Se não houver usuários
                echo "<tr><td colspan='6'>Nenhum usuário encontrado.</td></tr>"; // Mensagem informando que não há usuários
            }
            ?>
        </tbody>
    </table>
</main>

<footer>
    <p>&copy; 2025 Foco Diário. Todos os direitos reservados.</p> <!-- Rodapé -->
</footer>

</body>
</html>

<?php
$conn->close(); // Fecha a conexão com o banco de dados
?>
