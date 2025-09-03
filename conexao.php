<?php
$servername = "localhost";      // geralmente localhost
$username = "root";             // usuário do MySQL
$password = "";                 // senha do MySQL (XAMPP/WAMP normalmente é vazia)
$dbname = "portal_noticias";   // nome do seu banco

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Checar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Opcional: definir charset
$conn->set_charset("utf8");
echo "Conexão com sucesso!";

?>