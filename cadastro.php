<?php
session_start(); // Inicia a sessão do PHP para armazenar dados temporários do usuário
include 'conexao.php'; // Inclui o arquivo de conexão com o banco de dados
require 'vendor/autoload.php'; // Inclui o autoload do Composer, necessário para o PHPMailer

use PHPMailer\PHPMailer\PHPMailer; // Importa a classe PHPMailer
use PHPMailer\PHPMailer\Exception; // Importa a classe Exception do PHPMailer

$mensagem = ""; // Variável para armazenar mensagens de erro ou sucesso

/**
 * Função para gerar código numérico aleatório
 */
function gerarCodigo($tamanho = 6) { // Recebe o tamanho do código (padrão 6)
    $codigo = ''; // Inicializa a variável que irá armazenar o código
    for ($i = 0; $i < $tamanho; $i++) { // Loop para adicionar números ao código
        $codigo .= rand(0, 9); // Adiciona um número aleatório de 0 a 9
    }
    return $codigo; // Retorna o código gerado
}

/**
 * Função para enviar e-mail com PHPMailer
 */
function enviarCodigo($email, $codigo) { // Recebe o e-mail do usuário e o código de verificação
    $mail = new PHPMailer(true); // Cria uma nova instância do PHPMailer
    try {
        // Configurações do servidor SMTP
        $mail->isSMTP(); // Define que o envio será via SMTP
        $mail->Host = 'smtp.gmail.com'; // Endereço do servidor SMTP do Gmail
        $mail->SMTPAuth = true; // Habilita autenticação SMTP
        $mail->Username = 'cunhanair.cds@gmail.com'; // Usuário do SMTP
        $mail->Password = 'omcyaubizjfnwdpw'; // Senha ou token do SMTP
        $mail->SMTPSecure = 'tls'; // Define protocolo de segurança TLS
        $mail->Port = 587; // Porta de envio do SMTP

        $mail->setFrom('seuemail@dominio.com', 'Foco Diário'); // Define o remetente do e-mail
        $mail->addAddress($email); // Adiciona o destinatário do e-mail

        $mail->isHTML(true); // Define que o e-mail será em HTML
        $mail->Subject = 'Código de verificação - Foco Diário'; // Assunto do e-mail
        $mail->Body    = "Olá!<br>Seu código de verificação é: <b>$codigo</b>.<br>Ele expira em 15 minutos."; // Corpo do e-mail

        $mail->send(); // Envia o e-mail
        return true; // Retorna verdadeiro se enviado com sucesso
    } catch (Exception $e) {
        return false; // Retorna falso em caso de erro
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") { // Verifica se o formulário foi enviado via POST
    $nome = trim($_POST['nome']); // Recebe e limpa espaços do nome
    $email = trim($_POST['email']); // Recebe e limpa espaços do e-mail
    $senha = $_POST['senha']; // Recebe a senha
    $confirmaSenha = $_POST['confirmaSenha']; // Recebe a confirmação de senha

    if ($senha !== $confirmaSenha) { // Verifica se as senhas coincidem
        $mensagem = "As senhas não coincidem!"; // Mensagem de erro caso não coincidam
    } else {
        // Verifica se o e-mail já existe
        $sql = "SELECT * FROM usuario WHERE email = ?"; // Consulta SQL para verificar e-mail
        $stmt = $conn->prepare($sql); // Prepara a consulta
        $stmt->bind_param("s", $email); // Substitui o parâmetro com o e-mail
        $stmt->execute(); // Executa a consulta
        $result = $stmt->get_result(); // Obtém o resultado da consulta

        if ($result->num_rows > 0) { // Se já existir algum registro
            $mensagem = "E-mail já cadastrado!"; // Mensagem de erro
        } else {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT); // Cria um hash da senha

            // Insere usuário com status pendente
            $sql = "INSERT INTO usuario (nome, email, senha, data_cadastro, verificado) VALUES (?, ?, ?, NOW(), 0)"; // SQL de inserção
            $stmt = $conn->prepare($sql); // Prepara a consulta
            $stmt->bind_param("sss", $nome, $email, $senhaHash); // Substitui os parâmetros

            if ($stmt->execute()) { // Executa a inserção
                $id_usuario = $stmt->insert_id; // Obtém o ID do usuário inserido

                // Gerar código
                $codigo = gerarCodigo(); // Gera código numérico
                $hashCodigo = password_hash($codigo, PASSWORD_DEFAULT); // Cria hash do código
                $agora = date("Y-m-d H:i:s"); // Obtém data e hora atual
                $expiracao = date("Y-m-d H:i:s", strtotime("+15 minutes")); // Define data de expiração do código

                // Atualizar usuário com código
                $sqlUpdate = "UPDATE usuario 
                              SET codigo_verificacao_hash = ?, 
                                  codigo_criado_em = ?, 
                                  codigo_expires_em = ?,
                                  qtd_reenvios = 1,
                                  ultimo_reenvio = ?
                              WHERE id_usuario = ?"; // SQL para atualizar usuário com código de verificação
                $stmtUpdate = $conn->prepare($sqlUpdate); // Prepara a consulta
                $stmtUpdate->bind_param(
                    "ssssi",
                    $hashCodigo,
                    $agora,
                    $expiracao,
                    $agora,
                    $id_usuario
                ); // Substitui parâmetros
                $stmtUpdate->execute(); // Executa a atualização
                $stmtUpdate->close(); // Fecha a declaração

                // Enviar e-mail
                if (enviarCodigo($email, $codigo)) { // Tenta enviar o código por e-mail
                    $mensagem = "Cadastro realizado! Verifique seu e-mail para o código de ativação."; // Mensagem de sucesso
                } else {
                    $mensagem = "Cadastro realizado, mas não foi possível enviar o e-mail."; // Mensagem de alerta
                }
            } else {
                $mensagem = "Erro ao cadastrar: " . $conn->error; // Mensagem de erro no cadastro
            }
        }
        $stmt->close(); // Fecha a declaração
    }
    $conn->close(); // Fecha a conexão com o banco de dados
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <!-- Define codificação de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsividade -->
    <title>Cadastro - Foco Diário</title> <!-- Título da página -->
    <link rel="stylesheet" href="style.css"> <!-- Link para arquivo CSS -->
</head>
<body>
<header class="header-container"> <!-- Cabeçalho do site -->
    <div class="site-title"> <!-- Título do site -->
        <h1><a href="index.php">Foco Diário</a></h1>
    </div>
    <nav> <!-- Menu de navegação -->
        <ul>
            <li><a href="index.php">Início</a></li>
            <li><a href="noticias-brasil.php">Brasil</a></li>
            <li><a href="noticias-mundo.php">Mundo</a></li>
            <li><a href="esportes.php">Esportes</a></li>
            <li><a href="entretenimento.php">Entretenimento</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="cadastro.php" class="active">Cadastro</a></li>
        </ul>
    </nav>
</header>

<main class="container-cadastro"> <!-- Conteúdo principal da página -->
    <div class="cadastro-form"> <!-- Formulário de cadastro -->
        <h2>Criar uma nova conta</h2>
        <form id="cadastroForm" action="#" method="POST"> <!-- Formulário enviado via POST -->
            <div class="form-group">
                <label for="nome">Nome Completo</label> <!-- Label para campo nome -->
                <input type="text" id="nome" name="nome" required> <!-- Input do nome -->
            </div>
            <div class="form-group">
                <label for="email">E-mail</label> <!-- Label para campo e-mail -->
                <input type="email" id="email" name="email" required> <!-- Input do e-mail -->
            </div>
            <div class="form-group">
                <label for="senha">Senha</label> <!-- Label para campo senha -->
                <input type="password" id="senha" name="senha" required> <!-- Input da senha -->
            </div>
            <div class="form-group">
                <label for="confirmaSenha">Confirmar Senha</label> <!-- Label para confirmação de senha -->
                <input type="password" id="confirmaSenha" name="confirmaSenha" required> <!-- Input da confirmação de senha -->
            </div>
            <p class="mensagem-erro"><?php echo $mensagem; ?></p> <!-- Exibe mensagem de erro ou sucesso -->
<button type="submit">Cadastrar</button> <!-- Botão de envio do formulário -->
<p>Após o cadastro, você receberá um código por e-mail para ativar sua conta.</p>
<p>Já recebeu o e-mail com o código? <a href="validar_codigo.php">Clique aqui para validar sua conta</a></p>
<p>Já tem uma conta? <a href="login.php">Faça login aqui</a></p>
<!-- Link para cadastrar um novo administrador -->
<p>Deseja cadastrar um novo administrador? <a href="cadastrar_admin.php">Clique aqui</a></p>
        </form>
    </div>
</main>

<footer>
    <p>&copy; 2025 Foco Diário. Todos os direitos reservados.</p> <!-- Rodapé -->
</footer>
</body>
</html>
