<?php
session_start(); // Inicia a sessão para rastrear o usuário se necessário
include 'conexao.php'; // Inclui a conexão com o banco de dados
require 'vendor/autoload.php'; // Inclui o autoload do PHPMailer para enviar e-mails

use PHPMailer\PHPMailer\PHPMailer; // Importa a classe PHPMailer
use PHPMailer\PHPMailer\Exception; // Importa a classe Exception do PHPMailer

$mensagem = ""; // Inicializa variável para armazenar mensagens de sucesso ou erro

// Função para gerar código numérico aleatório
function gerarCodigo($tamanho = 6) {
    $codigo = ''; // Inicializa variável do código
    for ($i = 0; $i < $tamanho; $i++) { // Loop para gerar cada dígito
        $codigo .= rand(0, 9); // Adiciona dígito aleatório de 0 a 9
    }
    return $codigo; // Retorna o código gerado
}

// Função para enviar código por e-mail usando PHPMailer
function enviarCodigo($email, $nome, $codigo) {
    $mail = new PHPMailer(true); // Cria instância do PHPMailer
    try {
        $mail->isSMTP(); // Configura para enviar via SMTP
        $mail->Host = 'smtp.gmail.com'; // Servidor SMTP do Gmail
        $mail->SMTPAuth = true; // Habilita autenticação SMTP
        $mail->Username = 'cunhanair.cds@gmail.com'; // Usuário SMTP
        $mail->Password = 'omcyaubizjfnwdpw'; // Senha do SMTP (App Password)
        $mail->SMTPSecure = 'tls'; // Criptografia TLS
        $mail->Port = 587; // Porta SMTP

        $mail->setFrom('seuemail@dominio.com', 'Foco Diário'); // Define remetente
        $mail->addAddress($email, $nome); // Define destinatário

        $mail->isHTML(true); // Define que o e-mail é HTML
        $mail->Subject = 'Código de Verificação - Foco Diário'; // Assunto do e-mail
        $mail->Body = "Olá <b>$nome</b>,<br>Seu código de verificação é: <b>$codigo</b><br>Ele expira em 15 minutos."; // Corpo HTML
        $mail->AltBody = "Olá $nome, Seu código de verificação é: $codigo. Expira em 15 minutos."; // Corpo alternativo texto

        $mail->send(); // Envia o e-mail
        return true; // Retorna sucesso
    } catch (Exception $e) {
        return false; // Retorna falso em caso de erro
    }
}

// Processa a validação do código enviado pelo usuário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']); // Recebe e limpa o e-mail
    $codigo = trim($_POST['codigo']); // Recebe e limpa o código informado

    // Busca usuário no banco pelo e-mail
    $sql = "SELECT * FROM usuario WHERE email = ?";
    $stmt = $conn->prepare($sql); // Prepara a query
    $stmt->bind_param("s", $email); // Vincula o parâmetro email
    $stmt->execute(); // Executa a query
    $result = $stmt->get_result(); // Obtém o resultado

    if ($result->num_rows === 1) { // Se encontrou o usuário
        $usuario = $result->fetch_assoc(); // Pega os dados do usuário

        if ($usuario['verificado'] == 1) { // Se o usuário já está verificado
            $mensagem = "Usuário já verificado. <a href='login.php'>Faça login</a>."; // Mensagem informando
        } else {
            $agora = date("Y-m-d H:i:s"); // Pega data e hora atuais

            // Verifica se o código expirou
            if ($agora > $usuario['codigo_expires_em']) {
                $mensagem = "Código expirou. <a href='reenviar_codigo.php?email=".urlencode($email)."'>Clique aqui para reenviar o código</a>.";
            } else {
                // Verifica se o código digitado corresponde ao hash armazenado
                if (password_verify($codigo, $usuario['codigo_verificacao_hash'])) {
                    // Atualiza usuário como verificado e ativa a conta
                    $sqlUpdate = "UPDATE usuario SET verificado = 1, status_conta = 'Ativa' WHERE id_usuario = ?";
                    $stmtUpdate = $conn->prepare($sqlUpdate);
                    $stmtUpdate->bind_param("i", $usuario['id_usuario']);
                    $stmtUpdate->execute();
                    $stmtUpdate->close();

                    $mensagem = "Código válido! Sua conta foi ativada. <a href='login.php'>Faça login</a>.";
                } else { // Código inválido
                    $mensagem = "Código inválido. Tente novamente.";
                }
            }
        }
    } else { // Usuário não encontrado
        $mensagem = "Usuário não encontrado!";
    }

    $stmt->close(); // Fecha statement
    $conn->close(); // Fecha conexão
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8"> <!-- Define codificação UTF-8 -->
<meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsivo para dispositivos móveis -->
<title>Validar Código - Foco Diário</title> <!-- Título da página -->
<link rel="stylesheet" href="style.css"> <!-- Link para arquivo CSS -->
</head>
<body>
<header class="header-container">
    <div class="site-title">
        <h1><a href="index.php">Foco Diário</a></h1> <!-- Link para página inicial -->
    </div>
</header>

<main class="container-cadastro">
    <div class="cadastro-form">
        <h2>Validar Código de Verificação</h2> <!-- Título do formulário -->
        <form action="#" method="POST"> <!-- Formulário de validação -->
            <div class="form-group">
                <label for="email">E-mail</label> <!-- Label do e-mail -->
                <input type="email" id="email" name="email" required> <!-- Campo e-mail -->
            </div>
            <div class="form-group">
                <label for="codigo">Código</label> <!-- Label do código -->
                <input type="text" id="codigo" name="codigo" required> <!-- Campo código -->
            </div>
            <p class="mensagem-erro"><?php echo $mensagem; ?></p> <!-- Mensagem de erro ou sucesso -->
            <button type="submit">Validar Código</button> <!-- Botão de envio -->
        </form>
        <p>Não recebeu o código ou expirou? <a href="reenviar_codigo.php">Reenviar Código</a></p> <!-- Link para reenviar código -->
    </div>
</main>

<footer>
<p>&copy; 2025 Foco Diário. Todos os direitos reservados.</p> <!-- Rodapé -->
</footer>
</body>
</html>
