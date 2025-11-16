<?php
session_start(); // Inicia a sessão para poder acessá-la e destruí-la
session_destroy(); // Encerra a sessão atual, removendo todas as variáveis de sessão
header("Location: login_admin.php"); // Redireciona o usuário para a página de login administrativo
exit(); // Encerra a execução do script para garantir que o redirecionamento ocorra imediatamente
?>
