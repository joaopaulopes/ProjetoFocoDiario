<?php
session_start(); // Inicia a sessão do PHP
include 'conexao.php'; // Inclui a conexão com o banco de dados
use PHPMailer\PHPMailer\PHPMailer; // Importa a classe PHPMailer
use PHPMailer\PHPMailer\Exception; // Importa a classe Exception do PHPMailer
require 'vendor/autoload.php'; // Inclui o autoload do Composer para PHPMailer

$mensagem = ""; // Variável para armazenar mensagens de sucesso ou erro

// Função para gerar código numérico aleatório
function gerarCodigo($tamanho = 6) { // Recebe o tamanho do código (padrão 6)
    $codigo = ''; // Inicializa a variável do código
    for ($i = 0; $i < $tamanho; $i++) { // Loop para adicionar números
        $codigo .= rand(0, 9); // Adiciona um número aleatório de 0 a 9
    }
    return $codigo; // Retorna o código gerado
}

// Função para enviar código por e-mail
function enviarCodigo($email, $nome, $codigo) { // Recebe e-mail, nome e código
    $mail = new PHPMailer(true); // Cria instância do PHPMailer
    try {
        $mail->isSMTP(); // Define envio via SMTP
        $mail->Host = 'smtp.gmail.com'; // Servidor SMTP do Gmail
        $mail->SMTPAuth = true; // Habilita autenticação SMTP
        $mail->Username = 'cunhanair.cds@gmail.com'; // Usuário SMTP
        $mail->Password = 'omcyaubizjfnwdpw';       // Senha ou token do app
        $mail->SMTPSecure = 'tls'; // Define protocolo TLS
        $mail->Port = 587; // Porta SMTP

        $mail->setFrom('seuemail@dominio.com', 'Foco Diário'); // Remetente do e-mail
        $mail->addAddress($email, $nome); // Destinatário

        $mail->isHTML(true); // Define e-mail em HTML
        $mail->Subject = 'Redefinir Senha - Foco Diário'; // Assunto do e-mail
        $mail->Body = "Olá <b>$nome</b>,<br>Seu código de redefinição de senha é: <b>$codigo</b>.<br>Ele expira em 15 minutos."; // Corpo em HTML
        $mail->AltBody = "Olá $nome, Seu código de redefinição de senha é: $codigo. Expira em 15 minutos."; // Corpo alternativo texto puro

        $mail->send(); // Envia e-mail
        return true; // Retorna verdadeiro se enviado
    } catch (Exception $e) {
        return false; // Retorna falso em caso de erro
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") { // Verifica se o formulário foi enviado via POST
    $email = trim($_POST['email']); // Recebe e limpa o e-mail digitado

    // Busca usuário
    $sql = "SELECT * FROM usuario WHERE email = ?"; // SQL para buscar usuário pelo e-mail
    $stmt = $conn->prepare($sql); // Prepara a consulta
    if (!$stmt) {
        die("Erro no prepare: " . $conn->error); // Termina execução se falhar
    }
    $stmt->bind_param("s", $email); // Substitui parâmetro com e-mail
    $stmt->execute(); // Executa a consulta
    $result = $stmt->get_result(); // Obtém resultado

    if ($result->num_rows === 1) { // Se usuário encontrado
        $usuario = $result->fetch_assoc(); // Pega os dados do usuário

        // Gerar novo código
        $codigo = gerarCodigo(); // Gera código numérico aleatório
        $hashCodigo = password_hash($codigo, PASSWORD_DEFAULT); // Cria hash do código
        $agora = date("Y-m-d H:i:s"); // Data/hora atual
        $expiracao = date("Y-m-d H:i:s", strtotime("+15 minutes")); // Data/hora de expiração

        // Atualiza colunas de redefinição no banco
        $sqlUpdate = "UPDATE usuario 
                      SET codigo_redefinicao_hash = ?, 
                          codigo_redefinicao_criado = ?, 
                          codigo_redefinicao_expires = ?
                      WHERE id_usuario = ?"; // SQL para atualizar usuário com código
        $stmtUpdate = $conn->prepare($sqlUpdate); // Prepara a atualização
        if (!$stmtUpdate) {
            die("Erro no prepare update: " . $conn->error); // Termina se falhar
        }
        $stmtUpdate->bind_param("sssi", $hashCodigo, $agora, $expiracao, $usuario['id_usuario']); // Substitui parâmetros
        if ($stmtUpdate->execute()) { // Executa atualização
            // Envia e-mail com o código
            if (enviarCodigo($usuario['email'], $usuario['nome'], $codigo)) { // Chama função de envio
                $mensagem = "Um código de redefinição foi enviado para seu e-mail!"; // Mensagem de sucesso
            } else {
                $mensagem = "Erro ao enviar o e-mail. Tente novamente mais tarde."; // Mensagem de erro
            }
        } else {
            $mensagem = "Erro ao atualizar usuário: " . $conn->error; // Erro ao atualizar banco
        }
        $stmtUpdate->close(); // Fecha statement de update

    } else { // Se usuário não encontrado
        $mensagem = "Usuário não encontrado!"; // Mensagem de erro
    }

    $stmt->close(); // Fecha statement de select
    $conn->close(); // Fecha conexão
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8"> <!-- Codificação -->
<meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsividade -->
<title>Esqueci a Senha - Foco Diário</title> <!-- Título -->
<link rel="stylesheet" href="style.css"> <!-- CSS -->
</head>
<body>
<header class="header-container"> <!-- Cabeçalho -->
    <div class="site-title">
        <h1><a href="index.php">Foco Diário</a></h1> <!-- Título clicável -->
    </div>
</header>

<main class="container-cadastro"> <!-- Conteúdo principal -->
    <div class="cadastro-form"> <!-- Formulário de redefinição -->
        <h2>Redefinir Senha</h2>
        <form action="#" method="POST"> <!-- Formulário via POST -->
            <div class="form-group">
                <label for="email">Digite seu e-mail</label> <!-- Label e-mail -->
                <input type="email" id="email" name="email" required> <!-- Input e-mail -->
            </div>
            <p class="mensagem-erro"><?php echo $mensagem; ?></p> <!-- Exibe mensagem -->
            <button type="submit">Enviar Código</button> <!-- Botão envio -->
        </form>
        <p>Já recebeu o código? <a href="redefinir_senha.php">Redefinir senha aqui</a></p> <!-- Link para redefinir -->
    </div>
</main>

<footer>
<p>&copy; 2025 Foco Diário. Todos os direitos reservados.</p> <!-- Rodapé -->
</footer>
</body>
</html>
