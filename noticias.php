<?php
include 'conexao.php'; // Inclui arquivo de conexão com o banco de dados

// Captura filtros enviados pelo usuário via GET
$filtro = isset($_GET['busca']) ? "%" . $_GET['busca'] . "%" : "%%"; // Filtro de busca com curinga SQL
$fonte = isset($_GET['fonte']) ? $_GET['fonte'] : ""; // Filtro de fonte específica

// Busca fontes disponíveis dinamicamente
$fontes = $conn->query("SELECT nome_fonte FROM fontes WHERE status_coleta = 'Ativa'"); // Seleciona apenas fontes ativas

// Monta consulta das notícias com filtragem inteligente
if ($filtro === "%%" && empty($fonte)) { // Nenhum filtro aplicado
    // Listar últimas 50 notícias com resumo mínimo, evitando duplicidades
    $sql = "SELECT DISTINCT titulo, resumo, link_fonte, nome_fonte, data_publicacao 
            FROM noticias 
            WHERE CHAR_LENGTH(resumo) >= 50 
            ORDER BY data_publicacao DESC 
            LIMIT 50";
    $result = $conn->query($sql); // Executa a query
} else { // Caso haja filtros
    // Prepara SQL com parâmetros para busca por título ou resumo
    $sql = "SELECT DISTINCT titulo, resumo, link_fonte, nome_fonte, data_publicacao 
            FROM noticias 
            WHERE (titulo LIKE ? OR resumo LIKE ?) 
              AND CHAR_LENGTH(resumo) >= 50"; // mínimo de caracteres no resumo
    $params = [$filtro, $filtro]; // Array de parâmetros
    $types = "ss"; // Tipos dos parâmetros (strings)

    if (!empty($fonte)) { // Se foi selecionada uma fonte específica
        $sql .= " AND nome_fonte = ?"; // Adiciona condição por fonte
        $params[] = $fonte; // Adiciona ao array de parâmetros
        $types .= "s"; // Tipo string adicional
    }

    // Ordena notícias pelas mais recentes
    $sql .= " ORDER BY data_publicacao DESC";
    $stmt = $conn->prepare($sql); // Prepara statement para evitar SQL injection
    $stmt->bind_param($types, ...$params); // Associa parâmetros ao statement
    $stmt->execute(); // Executa a query
    $result = $stmt->get_result(); // Recupera resultado
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8"> <!-- Define codificação UTF-8 -->
<title>Notícias - Foco Diário</title> <!-- Título da página -->
<link rel="stylesheet" href="style.css"> <!-- Link para CSS externo -->
</head>
<body>

<header class="header-container">
  <h1><a href="index.php">Foco Diário</a></h1> <!-- Cabeçalho com link para a página inicial -->
</header>

<main class="container-cadastro">
  <h2>Notícias</h2>

  <!-- Formulário de busca e filtro -->
  <form method="GET" action="">
    <input type="text" name="busca" placeholder="Buscar palavra-chave" value="<?php echo isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : ''; ?>"> <!-- Campo de busca -->
    <select name="fonte">
      <option value="">Todas as fontes</option>
      <?php while ($f = $fontes->fetch_assoc()): ?> <!-- Loop pelas fontes ativas -->
        <option value="<?php echo htmlspecialchars($f['nome_fonte']); ?>" <?php if ($fonte === $f['nome_fonte']) echo 'selected'; ?>> <!-- Marca a fonte selecionada -->
          <?php echo htmlspecialchars($f['nome_fonte']); ?> <!-- Exibe nome da fonte -->
        </option>
      <?php endwhile; ?>
    </select>
    <button type="submit">Filtrar</button> <!-- Botão de envio do filtro -->
  </form>

  <div class="lista-noticias">
    <?php if ($result->num_rows > 0): ?> <!-- Verifica se há notícias -->
      <?php while ($row = $result->fetch_assoc()): ?> <!-- Loop pelas notícias -->
        <div class="noticia-card">
          <h3><?php echo htmlspecialchars($row['titulo']); ?></h3> <!-- Exibe título da notícia -->
          <p><?php echo htmlspecialchars($row['resumo']); ?></p> <!-- Exibe resumo da notícia -->
          <small>Fonte: <?php echo htmlspecialchars($row['nome_fonte']); ?> | 
          <?php echo date('d/m/Y H:i', strtotime($row['data_publicacao'])); ?></small><br> <!-- Exibe fonte e data formatada -->
          <a href="<?php echo htmlspecialchars($row['link_fonte']); ?>" target="_blank">Ler notícia completa</a> <!-- Link externo para a notícia completa -->
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="text-align:center; color:#666;">Nenhuma notícia encontrada para os filtros selecionados.</p> <!-- Mensagem caso não haja resultados -->
    <?php endif; ?>
  </div>
</main>

<footer>
  <p>&copy; 2025 Foco Diário</p> <!-- Rodapé -->
</footer>

</body>
</html>
