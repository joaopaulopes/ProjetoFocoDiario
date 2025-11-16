<?php
session_start(); // Inicia a sessão atual para poder destruí-la
session_destroy(); // Encerra a sessão, removendo todas as variáveis de sessão (efetivamente desloga o usuário)
header("Location: index.php"); // Redireciona o usuário para a página de index padrão
exit(); // Encerra a execução do script para garantir que o redirecionamento ocorra imediatamente
?>
