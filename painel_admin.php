<?php
session_start(); // Inicia a sessão para acessar os dados de login do administrador
if(!isset($_SESSION['id_admin'])) { // Verifica se o administrador não está logado
    header("Location: login_admin.php"); // Redireciona para a página de login administrativo
    exit(); // Encerra a execução do script para garantir o redirecionamento imediato
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <!-- Define a codificação de caracteres como UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Ajusta a página para dispositivos móveis -->
    <title>Painel do Administrador - Foco Diário</title> <!-- Título da página -->
    <link rel="stylesheet" href="style.css"> <!-- Link para o arquivo CSS externo -->
</head>
<body>
<div class="container-painel">
    <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['nome_admin']); ?></h1> <!-- Exibe o nome do administrador logado de forma segura -->
    <a href="consultar-usuario.php">Gerenciar Usuários</a> <!-- Link para gerenciar usuários -->
    <a href="gerenciar_noticias.php">Gerenciar Notícias</a> <!-- Link para gerenciar notícias -->
    <a href="gerenciar_admin.php">Gerenciar Administradores</a> <!-- Link para gerenciar administradores -->
     <a href="coletar_noticias.php">Coletar Noticias</a> <!-- Link para logout do administrador -->
     <a href="noticias.php">Filtrar Noticias</a> <!-- Link para logout do administrador -->
    <a href="logout_admin.php" class="logout">Sair</a> <!-- Link para logout do administrador -->
</div>
</body>
</html>
