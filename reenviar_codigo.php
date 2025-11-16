<?php
session_start(); // Inicia a sessão do PHP
include 'conexao.php'; // Inclui a conexão com o banco de dados
require 'vendor/autoload.php'; // Autoload do Composer (PHPMailer)

use PHPMailer\PHPMailer\PHPMailer; // Importa PHPMailer
use PHPMailer\PHPMailer\Exception; // Importa Exception

$mensagem = ""; // Variável para armazenar mensagens de sucesso ou erro

// Função para gerar código numérico aleatório
function gerarCodigo($tamanho = 6) { // Função recebe tamanho do código, padrão 6
    $codigo = ''; // Inicializa variável do código
    for ($i = 0; $i < $tamanho; $i++) { // Loop para gerar cada dígito
        $codigo .= rand(0, 9); // Adiciona dígito aleatório de 0 a 9
    }
    return $codigo; // Retorna o código gerado
}

// Função para enviar código por e-mail
function enviarCodigo($email, $nome, $codigo) { // Recebe e-mail, nome e código
    $mail = new PHPMailer(true); // Cria objeto PHPMailer
    try {
        $mail->isSMTP(); // Configura para usar SMTP
        $mail->Host = 'smtp.gmail.com'; // Servidor SMTP
        $mail->SMTPAuth = true; // Habilita autenticação
        $mail->Username = 'cunhanair.cds@gmail.com'; // E-mail do remetente
        $mail->Password = 'omcyaubizjfnwdpw'; // Senha do app
        $mail->SMTPSecure = 'tls'; // Criptografia TLS
        $mail->Port = 587; // Porta SMTP

        $mail->setFrom('seuemail@dominio.com', 'Foco Diário'); // Remetente
        $mail->addAddress($email, $nome); // Destinatário

        $mail->isHTML(true); // Ativa HTML no e-mail
        $mail->Subject = 'Código de Verificação - Foco Diário'; // Assunto
        $mail->Body = "Olá <b>$nome</b>,<br>Seu novo código de verificação é: <b>$codigo</b><br>Ele expira em 15 minutos."; // Corpo HTML
        $mail->AltBody = "Olá $nome, Seu novo código de verificação é: $codigo. Expira em 15 minutos."; // Corpo texto plano

        $mail->send(); // Envia o e-mail
        return true; // Retorna sucesso
    } catch (Exception $e) { // Em caso de erro
        return false; // Retorna falha
    }
}

// Se o parâmetro email foi passado na URL
$email = isset($_GET['email']) ? trim($_GET['email']) : ''; // Captura e limpa e-mail da URL

if ($_SERVER["REQUEST_METHOD"] == "POST") { // Se formulário enviado via POST
    $email = trim($_POST['email']); // Recebe e limpa e-mail do POST

    // Busca usuário
    $sql = "SELECT * FROM usuario WHERE email = ?"; // SQL para buscar usuário
    $stmt = $conn->prepare($sql); // Prepara a consulta
    $stmt->bind_param("s", $email); // Substitui parâmetro
    $stmt->execute(); // Executa consulta
    $result = $stmt->get_result(); // Obtém resultado

    if ($result->num_rows === 1) { // Se usuário encontrado
        $usuario = $result->fetch_assoc(); // Pega dados do usuário

        if ($usuario['verificado'] == 1) { // Se usuário já verificado
            $mensagem = "Usuário já está verificado. Faça login."; // Mensagem informativa
        } else {
            // Gerar novo código
            $codigo = gerarCodigo(); // Gera código aleatório
            $hashCodigo = password_hash($codigo, PASSWORD_DEFAULT); // Cria hash do código
            $agora = date("Y-m-d H:i:s"); // Hora atual
            $expiracao = date("Y-m-d H:i:s", strtotime("+15 minutes")); // Expiração em 15 minutos

            // Atualiza o usuário com novo código
            $sqlUpdate = "UPDATE usuario 
                          SET codigo_verificacao_hash = ?, 
                              codigo_criado_em = ?, 
                              codigo_expires_em = ?,
                              qtd_reenvios = qtd_reenvios + 1,
                              ultimo_reenvio = ?
                          WHERE id_usuario = ?"; // SQL de atualização
            $stmtUpdate = $conn->prepare($sqlUpdate); // Prepara update
            $stmtUpdate->bind_param(
                "ssssi",
                $hashCodigo, // Código hash
                $agora,      // Data criação
                $expiracao,  // Data expiração
                $agora,      // Último reenvio
                $usuario['id_usuario'] // ID usuário
            );
            $stmtUpdate->execute(); // Executa update
            $stmtUpdate->close(); // Fecha statement

            // Enviar e-mail
            if (enviarCodigo($usuario['email'], $usuario['nome'], $codigo)) { // Tenta enviar e-mail
                $mensagem = "Novo código enviado para o seu e-mail!"; // Sucesso
            } else {
                $mensagem = "Erro ao enviar o código. Tente novamente mais tarde."; // Falha no envio
            }
        }

    } else {
        $mensagem = "Usuário não encontrado!"; // Usuário não existe
    }

    $stmt->close(); // Fecha statement
    $conn->close(); // Fecha conexão
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8"> <!-- Codificação -->
<meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsividade -->
<title>Reenviar Código - Foco Diário</title> <!-- Título da página -->
<link rel="stylesheet" href="style.css"> <!-- CSS -->
</head>
<body>
<header class="header-container"> <!-- Cabeçalho -->
    <div class="site-title">
        <h1><a href="index.php">Foco Diário</a></h1> <!-- Logo e link para home -->
    </div>
</header>

<main class="container-cadastro"> <!-- Conteúdo principal -->
    <div class="cadastro-form"> <!-- Formulário de reenviar código -->
        <h2>Reenviar Código de Verificação</h2>
        <form action="#" method="POST"> <!-- Formulário POST -->
            <div class="form-group">
                <label for="email">E-mail cadastrado</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>"> <!-- Input e-mail -->
            </div>
            <p class="mensagem-erro"><?php echo $mensagem; ?></p> <!-- Exibe mensagens -->
            <button type="submit">Reenviar Código</button> <!-- Botão submit -->
        </form>
        <p>Já recebeu o código? <a href="validar_codigo.php">Clique aqui para validar sua conta</a></p>
    </div>
</main>

<footer>
<p>&copy; 2025 Foco Diário. Todos os direitos reservados.</p> <!-- Rodapé -->
</footer>
</body>
</html>
