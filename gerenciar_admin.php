<?php
session_start(); // Inicia a sessão para controle de login
include 'conexao.php'; // Inclui o arquivo de conexão com o banco de dados

// Verifica se o administrador está logado
if(!isset($_SESSION['id_admin'])) { // Se a variável de sessão id_admin não estiver definida
    header("Location: login_admin.php"); // Redireciona para a página de login administrativo
    exit(); // Encerra a execução do script
}

$mensagem = ""; // Inicializa variável de mensagem

// Processa atualização ou exclusão
if ($_SERVER["REQUEST_METHOD"] == "POST") { // Verifica se o formulário foi enviado via POST
    $id_admin = $_POST['id_admin']; // Recebe o ID do administrador do formulário

    if (isset($_POST['atualizar'])) { // Verifica se o botão "Atualizar" foi clicado
        $nome = $_POST['nome']; // Recebe o nome atualizado
        $email = $_POST['email']; // Recebe o e-mail atualizado
        $senha = $_POST['senha']; // Recebe a nova senha (pode estar vazia)
        $nivel_acesso = $_POST['nivel_acesso']; // Recebe o nível de acesso atualizado

        if (!empty($senha)) { // Se a senha não estiver vazia, atualiza com hash
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT); // Cria hash da nova senha
            $sql = "UPDATE administrador SET nome=?, email=?, senha=?, nivel_acesso=? WHERE id_admin=?"; // SQL de atualização com senha
            $stmt = $conn->prepare($sql); // Prepara a query
            $stmt->bind_param("ssssi", $nome, $email, $senhaHash, $nivel_acesso, $id_admin); // Vincula os parâmetros
        } else { // Se a senha estiver vazia, não altera a senha
            $sql = "UPDATE administrador SET nome=?, email=?, nivel_acesso=? WHERE id_admin=?"; // SQL de atualização sem senha
            $stmt = $conn->prepare($sql); // Prepara a query
            $stmt->bind_param("sssi", $nome, $email, $nivel_acesso, $id_admin); // Vincula os parâmetros
        }

        if ($stmt->execute()) { // Executa a query
            $mensagem = "Administrador atualizado com sucesso!"; // Mensagem de sucesso
        } else {
            $mensagem = "Erro ao atualizar: " . $conn->error; // Mensagem de erro com detalhe
        }
    }

    if (isset($_POST['excluir'])) { // Verifica se o botão "Excluir" foi clicado
        $stmt = $conn->prepare("DELETE FROM administrador WHERE id_admin=?"); // Prepara SQL de exclusão
        $stmt->bind_param("i", $id_admin); // Vincula o ID do administrador
        if ($stmt->execute()) { // Executa exclusão
            $mensagem = "Administrador excluído com sucesso!"; // Mensagem de sucesso
        } else {
            $mensagem = "Erro ao excluir: " . $conn->error; // Mensagem de erro com detalhe
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <!-- Define codificação UTF-8 -->
    <title>Gerenciar Administradores - Foco Diário</title> <!-- Título da página -->
    <link rel="stylesheet" href="style.css"> <!-- Link para o CSS externo -->
</head>
<body>
<header class="header-container">
    <div class="site-title">
        <h1><a href="painel_admin.php">Foco Diário</a></h1> <!-- Link para o painel administrativo -->
    </div>
</header>

<main class="container-cadastro">
    <h2>Lista de Administradores</h2> <!-- Título da seção -->

    <?php if ($mensagem != "") { echo "<p class='mensagem-erro'>$mensagem</p>"; } ?> <!-- Exibe mensagem de erro ou sucesso -->

    <table border="1" cellpadding="10"> <!-- Tabela de administradores -->
        <thead>
            <tr>
                <th>ID</th> <!-- Coluna do ID -->
                <th>Nome</th> <!-- Coluna do Nome -->
                <th>E-mail</th> <!-- Coluna do E-mail -->
                <th>Senha (nova)</th> <!-- Coluna para nova senha -->
                <th>Nível de Acesso</th> <!-- Coluna do nível de acesso -->
                <th>Ações</th> <!-- Coluna para botões de ação -->
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM administrador ORDER BY id_admin ASC"; // Seleciona todos os administradores
            $result = $conn->query($sql); // Executa a query
            if ($result->num_rows > 0) { // Se houver administradores
                while ($admin = $result->fetch_assoc()) { // Loop por cada administrador
                    echo "<tr id='editar-{$admin['id_admin']}'>
                        <form method='POST'>
                            <input type='hidden' name='id_admin' value='{$admin['id_admin']}'> <!-- ID oculto -->
                            <td>{$admin['id_admin']}</td> <!-- Mostra ID -->
                            <td><input type='text' name='nome' value='".htmlspecialchars($admin['nome'])."'></td> <!-- Input nome -->
                            <td><input type='email' name='email' value='".htmlspecialchars($admin['email'])."'></td> <!-- Input email -->
                            <td><input type='password' name='senha' placeholder='Nova senha'></td> <!-- Input senha -->
                            <td>
                                <select name='nivel_acesso'> <!-- Seleção do nível de acesso -->
                                    <option value='superadmin' ".($admin['nivel_acesso']=='superadmin'?'selected':'').">Super Admin</option>
                                    <option value='admin' ".($admin['nivel_acesso']=='admin'?'selected':'').">Admin</option>
                                    <option value='moderador' ".($admin['nivel_acesso']=='moderador'?'selected':'').">Moderador</option>
                                </select>
                            </td>
                            <td>
                                <button type='submit' name='atualizar'>Atualizar</button> <!-- Botão atualizar -->
                                <button type='submit' name='excluir' onclick=\"return confirm('Tem certeza que deseja excluir este administrador?');\" style='background-color:red;'>Excluir</button> <!-- Botão excluir -->
                            </td>
                        </form>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>Nenhum administrador encontrado.</td></tr>"; // Mensagem caso não haja administradores
            }
            ?>
        </tbody>
    </table>
</main>
 <a href="painel_admin.php">Gerenciar Administradores</a><br> <!-- Link para painel admin -->
<footer>
    <p>&copy; 2025 Foco Diário. Todos os direitos reservados.</p> <!-- Rodapé -->
</footer>
</body>
</html>

<?php $conn->close(); ?> <!-- Fecha a conexão com o banco de dados -->
