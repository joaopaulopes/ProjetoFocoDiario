<?php
session_start(); // Inicia a sessão do PHP
include 'conexao.php'; // Inclui a conexão com o banco de dados
use PHPMailer\PHPMailer\PHPMailer; // Importa a classe PHPMailer
use PHPMailer\PHPMailer\Exception; // Importa a classe Exception
require 'vendor/autoload.php'; // Carrega autoload do Composer (PHPMailer)

$mensagem = ""; // Variável para armazenar mensagens de erro ou sucesso

if ($_SERVER["REQUEST_METHOD"] == "POST") { // Verifica se o formulário foi enviado via POST
    $email = $_POST['email']; // Recebe e-mail digitado
    $senha = $_POST['senha']; // Recebe senha digitada

    $sql = "SELECT * FROM usuario WHERE email = ?"; // SQL para buscar usuário pelo e-mail
    $stmt = $conn->prepare($sql); // Prepara a consulta
    $stmt->bind_param("s", $email); // Substitui o parâmetro com o e-mail
    $stmt->execute(); // Executa a consulta
    $result = $stmt->get_result(); // Obtém o resultado

    if ($result->num_rows === 1) { // Se usuário encontrado
        $usuario = $result->fetch_assoc(); // Pega os dados do usuário
        
        if (password_verify($senha, $usuario['senha'])) { // Verifica se a senha está correta

            if ($usuario['verificado'] == 0) { // Se usuário ainda não verificou o código
                // Usuário ainda não validou código, gerar e enviar
                $codigo = ''; // Inicializa código
                for ($i = 0; $i < 6; $i++) { // Loop para gerar código numérico de 6 dígitos
                    $codigo .= rand(0, 9);
                }
                $hashCodigo = password_hash($codigo, PASSWORD_DEFAULT); // Cria hash do código

                $agora = date("Y-m-d H:i:s"); // Data/hora atual
                $expiracao = date("Y-m-d H:i:s", strtotime("+15 minutes")); // Data/hora de expiração

                // SQL para atualizar usuário com código de verificação
                $sqlUpdate = "UPDATE usuario 
                              SET codigo_verificacao_hash = ?, 
                                  codigo_criado_em = ?, 
                                  codigo_expires_em = ?,
                                  qtd_reenvios = qtd_reenvios + 1,
                                  ultimo_reenvio = ?
                              WHERE id_usuario = ?";
                $stmtUpdate = $conn->prepare($sqlUpdate); // Prepara consulta de update
                $stmtUpdate->bind_param(
                    "ssssi",
                    $hashCodigo,
                    $agora,
                    $expiracao,
                    $agora,
                    $usuario['id_usuario']
                );
                $stmtUpdate->execute(); // Executa atualização
                $stmtUpdate->close(); // Fecha statement de update

                // Enviar código por e-mail
                $mail = new PHPMailer(true); // Cria instância PHPMailer
                try {
                    $mail->isSMTP(); // Define SMTP
                    $mail->Host = 'smtp.seuprovedor.com'; // Servidor SMTP
                    $mail->SMTPAuth = true; // Habilita autenticação
                    $mail->Username = 'seuemail@dominio.com'; // Usuário SMTP
                    $mail->Password = 'suasenha'; // Senha SMTP
                    $mail->SMTPSecure = 'tls'; // Define protocolo TLS
                    $mail->Port = 587; // Porta SMTP

                    $mail->setFrom('seuemail@dominio.com', 'Foco Diário'); // Remetente
                    $mail->addAddress($usuario['email'], $usuario['nome']); // Destinatário

                    $mail->isHTML(true); // Formato HTML
                    $mail->Subject = 'Código de Verificação - Foco Diário'; // Assunto
                    $mail->Body = "Olá <b>{$usuario['nome']}</b>,<br>Seu código de verificação é: <b>{$codigo}</b><br>Expira em 15 minutos."; // Corpo HTML
                    $mail->AltBody = "Olá {$usuario['nome']}, Seu código de verificação é: {$codigo}. Expira em 15 minutos."; // Corpo texto

                    $mail->send(); // Envia o e-mail

                    // Redireciona para página de validação do código
                    $_SESSION['id_usuario'] = $usuario['id_usuario']; // Armazena ID do usuário na sessão
                    $_SESSION['email'] = $usuario['email']; // Armazena e-mail na sessão
                    header("Location: validar_codigo.php"); // Redireciona
                    exit(); // Finaliza execução

                } catch (Exception $e) { // Caso ocorra erro ao enviar
                    $mensagem = "Erro ao enviar o código: {$mail->ErrorInfo}"; // Armazena mensagem de erro
                }

            } else { // Usuário já verificado
                // Login normal
                $_SESSION['id_usuario'] = $usuario['id_usuario']; // Armazena ID na sessão
                $_SESSION['nome'] = $usuario['nome']; // Armazena nome na sessão
                header("Location: index.php"); // Redireciona para página inicial
                exit(); // Finaliza execução
            }

        } else { // Senha incorreta
            $mensagem = "Senha incorreta!"; // Mensagem de erro
        }
    } else { // Usuário não encontrado
        $mensagem = "Usuário não encontrado!"; // Mensagem de erro
    }

    $stmt->close(); // Fecha statement de select
    $conn->close(); // Fecha conexão com o banco
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <!-- Codificação -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsividade -->
    <title>Login - Foco Diário</title> <!-- Título -->
    <link rel="stylesheet" href="style.css"> <!-- CSS -->
</head>
<body>

<header class="header-container"> <!-- Cabeçalho -->
    <div class="site-title">
        <h1><a href="index.php">Foco Diário</a></h1> <!-- Título clicável -->
    </div>
    <nav>
        <ul>
            <li><a href="index.php">Início</a></li>
            <li><a href="noticias-brasil.php">Brasil</a></li>
            <li><a href="noticias-mundo.php">Mundo</a></li>
            <li><a href="esportes.php">Esportes</a></li>
            <li><a href="entretenimento.php">Entretenimento</a></li>
            <li><a href="login.php" class="active">Login</a></li>
            <li><a href="cadastro.php">Cadastro</a></li>
        </ul>
    </nav>
</header>

<main class="container-login"> <!-- Conteúdo principal -->
    <div class="login-form"> <!-- Formulário de login -->
        <h2>Acessar sua conta</h2>
        <form id="loginForm" action="#" method="POST"> <!-- Formulário via POST -->
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="text" id="email" name="email" required> <!-- Input e-mail -->
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required> <!-- Input senha -->
            </div>
            <p class="mensagem-erro"><?php echo $mensagem; ?></p> <!-- Exibe mensagem de erro -->
            <button type="submit">Entrar</button> <!-- Botão de login -->
        </form>
        <p>Ainda não tem uma conta? <a href="cadastro.php">Cadastre-se aqui</a></p>
<p><a href="esqueci_senha.php">Esqueci minha senha</a></p>
<p><a href="redefinir_senha.php">Redefinir senha</a></p>
<p>Para visualizar todos os usuários cadastrados, <a href="consultar-usuario.php">clique aqui</a>.</p>
<!-- Link para login do administrador -->
<p>Sou administrador: <a href="login_admin.php">Login Administrativo</a></p>

    </div>
</main>

<footer>
    <p>&copy; 2025 Foco Diário. Todos os direitos reservados.</p> <!-- Rodapé -->
</footer>

</body>
</html>
