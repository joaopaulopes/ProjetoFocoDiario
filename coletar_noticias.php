<?php
include 'conexao.php'; // Inclui o arquivo de conexão com o banco de dados
date_default_timezone_set('America/Sao_Paulo'); // Define o fuso horário padrão para São Paulo

echo "<h2>📰 Coletor de Notícias - Foco Diário</h2>"; // Exibe um título na página

// Função para log
function registrarLog($conn, $idFonte, $status, $mensagem) {
    // Prepara uma query para inserir registro no log de coleta
    $stmtLog = $conn->prepare(
        "INSERT INTO log_coleta (data_hora, status, mensagem, fonte_id) VALUES (?, ?, ?, ?)"
    );
    $dataHora = date("Y-m-d H:i:s"); // Captura a data e hora atual
    $stmtLog->bind_param("sssi", $dataHora, $status, $mensagem, $idFonte); // Associa parâmetros à query
    $stmtLog->execute(); // Executa a query
    $stmtLog->close(); // Fecha o statement
}

// Função para inserir notícias
function inserirNoticia($conn, $titulo, $resumo, $link, $nomeFonte, $dataPublicacao, $editoria) {
    $titulo = $conn->real_escape_string($titulo); // Escapa caracteres especiais no título
    $resumo = $conn->real_escape_string($resumo); // Escapa caracteres especiais no resumo
    $link = $conn->real_escape_string($link); // Escapa caracteres especiais no link
    $editoria = $conn->real_escape_string($editoria); // Escapa caracteres especiais na editoria

    // Evita duplicatas
    $check = $conn->query("SELECT id_noticia FROM noticias WHERE link_fonte = '$link'"); // Verifica se a notícia já existe
    if ($check->num_rows > 0) return false; // Retorna falso se já existe

    // Prepara a query para inserir a notícia
    $sqlInsert = "INSERT INTO noticias (titulo, resumo, link_fonte, nome_fonte, data_publicacao, editoria)
                  VALUES ('$titulo', '$resumo', '$link', '$nomeFonte', '$dataPublicacao', '$editoria')";
    return $conn->query($sqlInsert); // Executa a query e retorna true/false
}

// Consulta todas as fontes ativas
$sql = "SELECT * FROM fontes WHERE status_coleta = 'Ativa'"; // SQL para buscar fontes ativas
$result = $conn->query($sql); // Executa a query

if ($result->num_rows > 0) { // Verifica se existem fontes ativas
    while ($fonte = $result->fetch_assoc()) { // Percorre cada fonte encontrada
        $nomeFonte = htmlspecialchars($fonte['nome_fonte']); // Sanitiza o nome da fonte
        $urlFeed = $fonte['url_feed']; // Captura o URL do feed da fonte
        $idFonte = $fonte['id_fonte']; // Captura o ID da fonte

        echo "<p>🔍 Coletando notícias de: <b>{$nomeFonte}</b></p>"; // Exibe mensagem de coleta

        // Valida o feed
        $headers = @get_headers($urlFeed); // Tenta obter os headers HTTP do feed
        if (!$headers || strpos($headers[0], '200') === false) { // Verifica se o feed está inacessível
       
            registrarLog($conn, $idFonte, "Falha", "Feed inacessível ou inválido: $urlFeed"); // Registra falha no log
            continue; // Pula para a próxima fonte
        }

        // Carrega XML
        libxml_use_internal_errors(true); // Habilita tratamento interno de erros do XML
        $rss = @simplexml_load_file($urlFeed, 'SimpleXMLElement', 0, '', true); // Tenta carregar o feed como XML
        libxml_clear_errors(); // Limpa erros de XML

        if ($rss === false || !isset($rss->channel->item)) { // Verifica se o feed é inválido ou não tem itens
            echo "<p style='color:red;'>❌ Feed inválido ou sem itens.</p>"; // Exibe erro
            registrarLog($conn, $idFonte, "Falha", "Erro ao processar feed XML."); // Registra falha no log
            continue; // Pula para a próxima fonte
        }

        $inseridas = 0; // Contador de notícias inseridas
        foreach ($rss->channel->item as $item) { // Percorre cada item do feed
            $titulo = $item->title ?? ''; // Captura o título do item ou vazio se não existir
            $link = $item->link ?? ''; // Captura o link do item ou vazio
            $resumo = isset($item->description) ? strip_tags($item->description) : ''; // Captura resumo e remove tags HTML
            $dataPublicacao = date("Y-m-d H:i:s", strtotime($item->pubDate ?? "now")); // Converte a data de publicação
            $editoria = "Geral"; // Define editoria padrão

            if (empty($titulo) || empty($link)) continue; // Pula itens sem título ou link

            if (inserirNoticia($conn, $titulo, $resumo, $link, $nomeFonte, $dataPublicacao, $editoria)) { 
                $inseridas++; // Incrementa contador se a notícia foi inserida
            }
        }

        echo "<p style='color:green;'>✅ Coleta concluída de <b>{$nomeFonte}</b> — {$inseridas} novas notícias inseridas.</p>"; // Exibe resumo da coleta
        registrarLog($conn, $idFonte, "Sucesso", "Coleta concluída — $inseridas novas notícias inseridas."); // Registra sucesso no log
    }
} else {
    echo "<p>Nenhuma fonte ativa encontrada.</p>"; // Exibe mensagem se não houver fontes ativas
}

$conn->close(); // Fecha conexão com o banco
echo "<hr><p>🕓 Execução finalizada em " . date("d/m/Y H:i:s") . "</p>"; // Exibe horário de finalização
?>
