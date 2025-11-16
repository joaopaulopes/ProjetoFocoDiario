<?php
$servername = "localhost";      // geralmente localhost
$username = "root";             // usuário do MySQL
$password = "";                 // senha do MySQL (XAMPP/WAMP normalmente é vazia)
$dbname = "focodiario";   // nome do seu banco
$port  = 3307;
// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Checar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Opcional: definir charset
$conn->set_charset("utf8");


?>