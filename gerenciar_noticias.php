<?php
session_start(); // Inicia a sessão para gerenciar login do administrador
include 'conexao.php'; // Inclui o arquivo de conexão com o banco de dados

// Verifica se o administrador está logado
if(!isset($_SESSION['id_admin'])) {
    header("Location: login_admin.php"); // Redireciona para a página de login se não estiver logado
    exit(); // Encerra a execução do script
}

$mensagem = ""; // Inicializa variável para mensagens de sucesso ou erro

// Processa atualização ou exclusão
if ($_SERVER["REQUEST_METHOD"] == "POST") { // Verifica se o formulário foi enviado via POST
    $id_noticia = $_POST['id_noticia']; // Captura o ID da notícia do formulário

    if (isset($_POST['atualizar'])) { // Verifica se o botão "atualizar" foi clicado
        $titulo = $_POST['titulo']; // Captura o título atualizado
        $resumo = $_POST['resumo']; // Captura o resumo atualizado
        $link_fonte = $_POST['link_fonte']; // Captura o link da fonte atualizado
        $nome_fonte = $_POST['nome_fonte']; // Captura o nome da fonte atualizado
        $editoria = $_POST['editoria']; // Captura a editoria atualizada
        $data_publicacao = $_POST['data_publicacao']; // Captura a data de publicação atualizada

        // Prepara a query SQL para atualizar a notícia
        $sql = "UPDATE noticias SET titulo=?, resumo=?, link_fonte=?, nome_fonte=?, editoria=?, data_publicacao=? WHERE id_noticia=?";
        $stmt = $conn->prepare($sql); // Prepara a query para evitar SQL injection
        $stmt->bind_param("ssssssi", $titulo, $resumo, $link_fonte, $nome_fonte, $editoria, $data_publicacao, $id_noticia); // Associa os parâmetros

        if ($stmt->execute()) { // Executa a query
            $mensagem = "Notícia atualizada com sucesso!"; // Mensagem de sucesso
        } else {
            $mensagem = "Erro ao atualizar: " . $conn->error; // Mensagem de erro caso falhe
        }
    }

    if (isset($_POST['excluir'])) { // Verifica se o botão "excluir" foi clicado
        $stmt = $conn->prepare("DELETE FROM noticias WHERE id_noticia=?"); // Prepara query de exclusão
        $stmt->bind_param("i", $id_noticia); // Associa o ID da notícia
        if ($stmt->execute()) { // Executa a exclusão
            $mensagem = "Notícia excluída com sucesso!"; // Mensagem de sucesso
        } else {
            $mensagem = "Erro ao excluir: " . $conn->error; // Mensagem de erro caso falhe
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <!-- Define codificação de caracteres -->
    <title>Gerenciar Notícias - Foco Diário</title> <!-- Título da página -->
    <link rel="stylesheet" href="style.css"> <!-- Link para o CSS externo -->
</head>
<body>
<header class="header-container">
    <div class="site-title">
        <h1><a href="painel_admin.php">Foco Diário</a></h1> <!-- Título com link para painel do admin -->
    </div>
</header>

<main class="container-cadastro">
    <h2>Lista de Notícias</h2>

    <?php if ($mensagem != "") { echo "<p class='mensagem-erro'>$mensagem</p>"; } ?> <!-- Exibe mensagem de sucesso ou erro -->

    <table border="1" cellpadding="10"> <!-- Inicia tabela para listar notícias -->
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Resumo</th>
                <th>Link Fonte</th>
                <th>Fonte</th>
                <th>Editoria</th>
                <th>Data Publicação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM noticias ORDER BY data_publicacao DESC"; // Consulta todas notícias ordenadas por data
            $result = $conn->query($sql); // Executa a query
            if ($result->num_rows > 0) { // Verifica se existem notícias
                while ($noticia = $result->fetch_assoc()) { // Percorre cada notícia
                    echo "<tr id='editar-{$noticia['id_noticia']}'>
                        <form method='POST'>
                            <input type='hidden' name='id_noticia' value='{$noticia['id_noticia']}'> <!-- ID oculto para atualização/exclusão -->
                            <td>{$noticia['id_noticia']}</td>
                            <td><input type='text' name='titulo' value='".htmlspecialchars($noticia['titulo'])."'></td> <!-- Campo título -->
                            <td><textarea name='resumo'>".htmlspecialchars($noticia['resumo'])."</textarea></td> <!-- Campo resumo -->
                            <td><input type='text' name='link_fonte' value='".htmlspecialchars($noticia['link_fonte'])."'></td> <!-- Campo link da fonte -->
                            <td><input type='text' name='nome_fonte' value='".htmlspecialchars($noticia['nome_fonte'])."'></td> <!-- Campo nome da fonte -->
                            <td><input type='text' name='editoria' value='".htmlspecialchars($noticia['editoria'])."'></td> <!-- Campo editoria -->
                            <td><input type='datetime-local' name='data_publicacao' value='".date('Y-m-d\TH:i', strtotime($noticia['data_publicacao']))."'></td> <!-- Campo data -->
                            <td>
                                <button type='submit' name='atualizar'>Atualizar</button> <!-- Botão atualizar -->
                                <button type='submit' name='excluir' onclick=\"return confirm('Tem certeza que deseja excluir esta notícia?');\" style='background-color:red;'>Excluir</button> <!-- Botão excluir com confirmação -->
                            </td>
                        </form>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='8'>Nenhuma notícia encontrada.</td></tr>"; // Exibe mensagem se não houver notícias
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

<?php $conn->close(); ?> <!-- Fecha a conexão com o banco -->
