<?php
session_start(); // Inicia a sessão para controlar o login do administrador
include 'conexao.php'; // Inclui arquivo de conexão com o banco de dados

// Verifica se o administrador está logado
if (!isset($_SESSION['id_admin'])) {
    header("Location: login_admin.php"); // Redireciona para login se não estiver logado
    exit(); // Encerra execução do script
}

// Filtros opcionais enviados via GET
$filtro_fonte = isset($_GET['fonte']) ? trim($_GET['fonte']) : ''; // Filtra por nome da fonte
$filtro_status = isset($_GET['status']) ? trim($_GET['status']) : ''; // Filtra por status do log

// Monta a query base com LEFT JOIN para exibir o nome da fonte
$query = "SELECT log_coleta.id_log, log_coleta.data_hora, log_coleta.status, log_coleta.mensagem, fontes.nome_fonte 
          FROM log_coleta 
          LEFT JOIN fontes ON log_coleta.fonte_id = fontes.id_fonte 
          WHERE 1=1"; // WHERE 1=1 facilita adicionar filtros dinamicamente

// Adiciona filtros à query se existirem
if (!empty($filtro_fonte)) {
    $query .= " AND fontes.nome_fonte LIKE '%" . $conn->real_escape_string($filtro_fonte) . "%'"; // Escapa input do usuário
}
if (!empty($filtro_status)) {
    $query .= " AND log_coleta.status = '" . $conn->real_escape_string($filtro_status) . "'"; // Escapa input do usuário
}

// Ordena os logs do mais recente para o mais antigo
$query .= " ORDER BY log_coleta.data_hora DESC";

$result = $conn->query($query); // Executa a query
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <!-- Define codificação de caracteres -->
    <title>Logs de Coleta - Foco Diário</title> <!-- Título da página -->
    <link rel="stylesheet" href="style.css"> <!-- Link para CSS externo -->
</head>
<body>
    <h1>📋 Logs de Coleta de Notícias</h1> <!-- Cabeçalho da página -->

    <div class="filtros">
        <!-- Formulário de filtros -->
        <form method="get" action="">
            <input type="text" name="fonte" placeholder="Filtrar por fonte..." value="<?php echo htmlspecialchars($filtro_fonte); ?>"> <!-- Campo de filtro por fonte -->
            <select name="status"> <!-- Dropdown de status -->
                <option value="">Todos os status</option>
                <option value="sucesso" <?php if($filtro_status == 'sucesso') echo 'selected'; ?>>Sucesso</option>
                <option value="falha" <?php if($filtro_status == 'falha') echo 'selected'; ?>>Falha</option>
            </select>
            <button type="submit">Filtrar</button> <!-- Botão de envio do filtro -->
        </form>
    </div>

    <table> <!-- Tabela para exibir os logs -->
        <tr>
            <th>ID Log</th>
            <th>Fonte</th>
            <th>Status</th>
            <th>Mensagem</th>
            <th>Data/Hora</th>
        </tr>

        <?php if ($result && $result->num_rows > 0): ?> <!-- Verifica se há resultados -->
            <?php while ($linha = $result->fetch_assoc()): ?> <!-- Loop pelos logs -->
                <tr>
                    <td><?php echo $linha['id_log']; ?></td> <!-- Exibe ID do log -->
                    <td><?php echo htmlspecialchars($linha['nome_fonte'] ?? 'Desconhecida'); ?></td> <!-- Exibe nome da fonte ou "Desconhecida" -->
                    <td class="<?php echo ($linha['status'] == 'sucesso') ? 'status-sucesso' : 'status-falha'; ?>"> <!-- Classe CSS para status -->
                        <?php echo ucfirst($linha['status']); ?> <!-- Exibe status com primeira letra maiúscula -->
                    </td>
                    <td><?php echo htmlspecialchars($linha['mensagem']); ?></td> <!-- Exibe mensagem do log -->
                    <td><?php echo date('d/m/Y H:i:s', strtotime($linha['data_hora'])); ?></td> <!-- Formata data/hora -->
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">Nenhum log encontrado.</td> <!-- Mensagem se não houver logs -->
            </tr>
        <?php endif; ?>
    </table>

    <a href="painel_admin.php" class="voltar">⬅ Voltar ao Painel</a> <!-- Link para retornar ao painel do admin -->
</body>
</html>
