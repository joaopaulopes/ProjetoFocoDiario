<?php
// Inicia o bloco de código PHP.
// Inclui o arquivo de conexão com o banco de dados.
include 'conexao.php'; 
// Inicia ou retoma a sessão do usuário.
session_start();

// Função para obter o favicon da fonte
function obterFavicon($url) {
    $dominio = parse_url($url, PHP_URL_HOST);
    
    // Lista COMPLETA com todos os seus sites
    $favicons_conhecidos = [
        // Seus sites específicos
        'meupositivo.com.br' => 'https://www.google.com/s2/favicons?domain=meupositivo.com.br&sz=32',
        'g1.globo.com' => 'https://www.google.com/s2/favicons?domain=g1.globo.com&sz=32',
        'uol.com.br' => 'https://www.google.com/s2/favicons?domain=uol.com.br&sz=32',
        'estadao.com.br' => 'https://www.google.com/s2/favicons?domain=estadao.com.br&sz=32',
        'bbc.com' => 'https://www.google.com/s2/favicons?domain=bbc.com&sz=32',
        'techcrunch.com' => 'https://www.google.com/s2/favicons?domain=techcrunch.com&sz=32',
        'cnn.com' => 'https://www.google.com/s2/favicons?domain=cnn.com&sz=32',
        'cnnbrasil.com.br' => 'https://www.google.com/s2/favicons?domain=cnnbrasil.com.br&sz=32',
        'folha.uol.com.br' => 'https://www.google.com/s2/favicons?domain=folha.uol.com.br&sz=32',
        'metropoles.com' => 'https://www.google.com/s2/favicons?domain=metropoles.com&sz=32',
        'climatempo.com.br' => 'https://www.google.com/s2/favicons?domain=climatempo.com.br&sz=32',
        // Adicione mais sites conforme necessário
    ];
    
    // Procura por correspondência no domínio
    foreach ($favicons_conhecidos as $site => $favicon) {
        if (strpos($dominio, $site) !== false) {
            return $favicon;
        }
    }
    
    // Fallback para QUALQUER outro site
    return "https://www.google.com/s2/favicons?domain=" . $dominio . "&sz=32";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notícias do Brasil - Foco Diário</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="header-container">
        <div class="site-title">
            <h1><a href="index.php">Foco Diário</a></h1>
        </div>
        <nav>
            <ul>
               <li><a href="index.php">Início</a></li>
        <li><a href="noticias-brasil.php" class="active">Brasil</a></li>
        <li><a href="noticias-mundo.php">Mundo</a></li>
        <li><a href="esportes.php">Esportes</a></li>
        <li><a href="entretenimento.php">Entretenimento</a></li>
        
        <?php 
        // Verifica se a variável de sessão 'id_usuario' está definida (CORREÇÃO DE VARIÁVEL).
        if (isset($_SESSION['id_usuario'])): ?>
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="cadastro.php">Cadastro</a></li>
        <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <div class="container-principal">
            <div class="conteudo-principal">
                
                <section class="noticias-destaque-brasil">
                    <h2>Notícias do Brasil em Destaque</h2>
                    
                    <?php
                    // Query SQL para buscar as 2 notícias mais recentes da editoria 'Brasil'.
                    $sql_destaque = "SELECT id_noticia, titulo, resumo, link_fonte, curtidas FROM noticias WHERE editoria = 'Brasil' ORDER BY data_publicacao DESC LIMIT 2";
                    // Executa a query no banco de dados.
                    $result_destaque = $conn->query($sql_destaque);

                    // Verifica se a consulta retornou alguma notícia.
                    if ($result_destaque->num_rows > 0) {
                        $count = 0;
                        // Inicia o loop para processar cada notícia encontrada.
                        while($noticia = $result_destaque->fetch_assoc()) {
                            $count++;
                            
                            // Lógica para usar as URLs de imagem (hardcoded para este exemplo).
                            $img_url = '';
                            if ($count == 1) {
                                $img_url = 'https://s2-g1.glbimg.com/uAh6d83Oy_BpYIj6VdxWm8w9Jrw=/0x0:984x552/984x0/smart/filters:strip_icc()/i.s3.glbimg.com/v1/AUTH_59edd422c0c84a879bd37670ae4f538a/internal_photos/bs/2023/P/O/isgxmoQnKvn84bnuBdMg/prova-enem.jpg';
                            } else if ($count == 2) {
                                $img_url = 'https://s2-g1.glbimg.com/S8L_MOsX0jzKU_OpdVa5KXSIlRE=/0x374:1200x1300/984x0/smart/filters:strip_icc()/i.s3.glbimg.com/v1/AUTH_59edd422c0c84a879bd37670ae4f538a/internal_photos/bs/2025/H/j/cDx2sPSpCw3MuX5cTNmg/whatsapp-image-2025-11-13-at-20.18.36.jpeg';
                            } else {
                                $img_url = 'URL_DEFAULT_PARA_NOTICIAS_DO_BRASIL'; // Imagem de fallback
                            }
                            
                            // Extrair informações da fonte
                            $fonte_url = parse_url($noticia['link_fonte'], PHP_URL_HOST);
                            $fonte_nome = str_replace(['www.', '.com', '.com.br', '.org', '.net'], '', $fonte_url);
                            $fonte_nome = ucfirst($fonte_nome);
                            $fonte_favicon = obterFavicon($noticia['link_fonte']);
                            
                            // Variável para armazenar o voto do usuário ('upvote', 'downvote' ou null).
                            $user_vote = null; 
                            // Checa se o usuário está logado.
                            if (isset($_SESSION['id_usuario'])) { 
                                // SQL (Prepared Statement - SEGURO!) para buscar o voto.
                                $sql_voto = "SELECT tipo_voto FROM votos WHERE id_usuario = ? AND id_noticia = ?";
                                // Prepara a consulta.
                                $stmt_voto = $conn->prepare($sql_voto);
                                // Vincula os IDs do usuário e da notícia como inteiros ("ii").
                                $stmt_voto->bind_param("ii", $_SESSION['id_usuario'], $noticia['id_noticia']);
                                // Executa a consulta.
                                $stmt_voto->execute();
                                // Obtém o resultado.
                                $result_voto = $stmt_voto->get_result();
                                
                                // Se encontrou um voto.
                                if ($result_voto->num_rows > 0) {
                                    // Armazena o tipo de voto.
                                    $user_vote = $result_voto->fetch_assoc()['tipo_voto'];
                                }
                                // Fecha o prepared statement.
                                $stmt_voto->close(); 
                            }
                            // FIM DA LÓGICA DE VOTO (PHP)
                    ?>
                            <article class="noticia" data-id="<?php echo $noticia['id_noticia']; ?>">
                                <div class="noticia-destaque-container">
                                    <!-- TÍTULO CLICÁVEL (AGORA EM CIMA) -->
                                    <a href="<?php echo htmlspecialchars($noticia['link_fonte']); ?>" target="_blank" class="titulo-link">
                                        <h3><?php echo htmlspecialchars($noticia['titulo']); ?></h3>
                                    </a>
                                    
                                    <div class="noticia-destaque-conteudo">
                                        <!-- IMAGEM CLICÁVEL (MANTIDA NO MESMO LUGAR) -->
                                        <a href="<?php echo htmlspecialchars($noticia['link_fonte']); ?>" target="_blank" class="imagem-link">
                                            <img src="<?php echo $img_url; ?>" alt="Imagem da Notícia do Brasil">
                                        </a>
                                        
                                        <div class="noticia-destaque-texto">
                                            <!-- EXPLICAÇÃO/RESUMO (EMBAIXO DO TÍTULO) -->
                                            <p class="resumo-noticia"><?php echo htmlspecialchars($noticia['resumo']); ?></p>
                                            
                                            <!-- BOTÕES DE CURTIR (EMBAIXO DA EXPLICAÇÃO) -->
                                            <div class="feedback-area">
                                                <button class="like-btn" data-id-noticia="<?php echo $noticia['id_noticia']; ?>">
                                                    <?php echo ($user_vote === 'upvote') ? 'Curtiu!' : '👍 Curtir'; ?>
                                                </button>
                                                <button class="downvote-btn" data-id-noticia="<?php echo $noticia['id_noticia']; ?>">
                                                    <?php echo ($user_vote === 'downvote') ? 'Descurtiu!' : '👎 Descurtir'; ?>
                                                </button> 
                                                <span class="like-count-<?php echo $noticia['id_noticia']; ?>">
                                                    <?php echo $noticia['curtidas']; ?>
                                                </span> curtidas
                                            </div>
                                            
                                            <!-- FONTE COM FAVICON -->
                                            <div class="fonte-noticia">
                                                <img src="<?php echo $fonte_favicon; ?>" alt="<?php echo $fonte_nome; ?>" class="fonte-favicon" onerror="this.style.display='none'">
                                                <small>Fonte: <?php echo htmlspecialchars($fonte_nome); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                    <?php
                        } // Fim do loop while
                    } else {
                        // Mensagem se nenhuma notícia de destaque for encontrada.
                        echo "<p>Nenhuma notícia do Brasil em destaque encontrada no banco de dados.</p>";
                    }
                    ?>
                </section>
                <section class="noticias-recentes-brasil">
                    <h2>Outras Notícias do Brasil</h2>
                    
                    <?php
                    // Query para buscar notícias recentes. OFFSET 2 pula as duas notícias de destaque.
                    $sql_recentes = "SELECT id_noticia, titulo, resumo, link_fonte, curtidas FROM noticias WHERE editoria = 'Brasil' ORDER BY data_publicacao DESC LIMIT 10 OFFSET 2";
                    // Executa a query.
                    $result_recentes = $conn->query($sql_recentes);

                    // Verifica se há notícias recentes.
                    if ($result_recentes->num_rows > 0) {
                        // Loop para exibir as notícias recentes.
                        while($noticia = $result_recentes->fetch_assoc()) {
                            
                            // Extrair informações da fonte
                            $fonte_url = parse_url($noticia['link_fonte'], PHP_URL_HOST);
                            $fonte_nome = str_replace(['www.', '.com', '.com.br', '.org', '.net'], '', $fonte_url);
                            $fonte_nome = ucfirst($fonte_nome);
                            $fonte_favicon = obterFavicon($noticia['link_fonte']);
                            
                            // Variável para armazenar o voto do usuário.
                            $user_vote = null; 
                            // Checa se o usuário está logado.
                            if (isset($_SESSION['id_usuario'])) { 
                                // SQL (Prepared Statement - SEGURO!) para buscar o voto.
                                $sql_voto = "SELECT tipo_voto FROM votos WHERE id_usuario = ? AND id_noticia = ?";
                                // Prepara a consulta.
                                $stmt_voto = $conn->prepare($sql_voto);
                                // Vincula os IDs do usuário e da notícia como inteiros ("ii").
                                $stmt_voto->bind_param("ii", $_SESSION['id_usuario'], $noticia['id_noticia']);
                                // Executa a consulta.
                                $stmt_voto->execute();
                                // Obtém o resultado.
                                $result_voto = $stmt_voto->get_result();
                                
                                // Se encontrou um voto.
                                if ($result_voto->num_rows > 0) {
                                    // Armazena o tipo de voto.
                                    $user_vote = $result_voto->fetch_assoc()['tipo_voto'];
                                }
                                // Fecha o prepared statement.
                                $stmt_voto->close(); 
                            }
                            // FIM DA LÓGICA DE VOTO (PHP)
                    ?>
                            <article class="noticia-recente" data-id="<?php echo $noticia['id_noticia']; ?>">
                                <!-- TÍTULO CLICÁVEL (já existente, mantido) -->
                                <h4><a href="<?php echo htmlspecialchars($noticia['link_fonte']); ?>" target="_blank"><?php echo htmlspecialchars($noticia['titulo']); ?></a></h4>
                                
                                <div class="feedback-area-recente">
                                    <button class="like-btn" data-id-noticia="<?php echo $noticia['id_noticia']; ?>">
                                        <?php echo ($user_vote === 'upvote') ? 'Curtiu!' : '👍 Curtir'; ?>
                                    </button>
                                    <button class="downvote-btn" data-id-noticia="<?php echo $noticia['id_noticia']; ?>">
                                        <?php echo ($user_vote === 'downvote') ? 'Descurtiu!' : '👎 Descurtir'; ?>
                                    </button> 
                                    <span class="like-count-<?php echo $noticia['id_noticia']; ?>">
                                       <?php echo $noticia['curtidas']; ?>
                                    </span> curtidas
                                </div>
                                
                                <p><?php echo htmlspecialchars($noticia['resumo']); ?></p>
                                
                                <!-- FONTE COM FAVICON PARA NOTÍCIAS RECENTES -->
                                <div class="fonte-noticia">
                                    <img src="<?php echo $fonte_favicon; ?>" alt="<?php echo $fonte_nome; ?>" class="fonte-favicon" onerror="this.style.display='none'">
                                    <small>Fonte: <?php echo htmlspecialchars($fonte_nome); ?></small>
                                </div>
                            </article>
                    <?php
                        } // Fim do loop while
                    } else {
                        // Mensagem se nenhuma notícia recente for encontrada.
                        echo "<p>Nenhuma notícia recente do Brasil encontrada no banco de dados.</p>";
                    }
                    ?>
                </section>
                </div>

            <aside class="barra-lateral">
                <section class="mais-lidas">
                    <h3>Mais Lidas</h3>
                    <ul>
                        <li><h4><a href="https://www.climatempo.com.br/noticia/calor/secura-do-ar-deixa-brasil-em-emergencia">Secura do ar deixa Brasil em emergência</a></h4></li>
                        <li><h4><a href="https://g1.globo.com/sp/campinas-regiao/noticia/2025/09/11/quem-era-andre-amato-advogado-morto-a-facadas-ao-tentar-impedir-assedio-em-apartamento-de-sp.ghtml">Quem era André Amato, advogado morto a facadas ao tentar impedir assédio em apartamento de SP</a></h4></li>
                        <li><h4><a href="https://www.cnnbrasil.com.br/nacional/sudeste/mg/criminosos-invadem-casa-e-matam-mulher-em-governador-valadares-mg">Criminosos invadem casa e matam mulher em Governador Valadares (MG)</a></h4></li>
                        <li><h4><a href="https://www.cnnbrasil.com.br/nacional/sudeste/rj/video-homem-salta-da-mao-do-cristo-rendentor-no-rio-policia-investiga">VÍDEO: Missão Impossível? Homem salta da mão do Cristo Redentor e vira alvo da polícia</a></h4></li>
                    </ul>
                </section>
                
                <section class="publicidade">
                    <h3>Publicidade</h3>
                    <img src="" alt="Espaço de publicidade">
                </section>
            </aside>
        </div>
    </main>
<?php 
// Fecha a conexão com o banco de dados.
$conn->close();
?>

    <footer>
        <p>&copy; 2025 Foco Diário. Todos os direitos reservados.</p>
    </footer>

<script>
// Início do bloco JavaScript para a lógica de votos (AJAX/Fetch).
document.addEventListener('DOMContentLoaded', function() {
    
    // Função principal que trata o evento de clique nos botões de voto.
    function handleVote(button, actionType) {
        // Obtém o ID da notícia.
        const idNoticia = button.getAttribute('data-id-noticia');
        // Define o script PHP de destino.
        const phpFile = actionType === 'upvote' ? 'curtir.php' : 'downvote.php';
        
        // Verifica se a ação é um upvote.
        const isUpvote = (actionType === 'upvote');
        // Encontra o elemento <span> que exibe a contagem de curtidas.
        const countSpan = document.querySelector(`.like-count-${idNoticia}`); 
        // Encontra o botão oposto (ex: se clicou em 'like', procura o 'downvote').
        const otherButton = document.querySelector(`[data-id-noticia="${idNoticia}"]${isUpvote ? '.downvote-btn' : '.like-btn'}`);

        // Feedback: Desabilita os botões e altera o texto para "Processando..."
        button.disabled = true;
        if (otherButton) otherButton.disabled = true;
        button.textContent = 'Processando...'; 

        // Inicia a requisição assíncrona (Fetch API) via POST.
        fetch(phpFile, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id_noticia=' + idNoticia // Envia o ID da notícia.
        })
        .then(response => {
             // Trata a resposta 403 (Não logado).
             if (response.status === 403) {
                return { success: false, message: 'Não logado' }; 
             }
             // Tenta converter a resposta para JSON.
             return response.json();
        })
        .then(data => {
            // Re-habilita os botões após receber a resposta.
            button.disabled = false;
            if (otherButton) otherButton.disabled = false;

            // Se a requisição PHP foi bem-sucedida.
            if (data.success) {
                
                // Atualiza a contagem no elemento <span>.
                if (countSpan) {
                    countSpan.textContent = data.new_count;
                }

                // Define os textos dos botões para feedback visual.
                const defaultLikeText = '👍 Curtir';
                const votedLikeText = 'Curtiu!';
                const defaultDownvoteText = '👎 Descurtir';
                const votedDownvoteText = 'Descurtiu!';

                // Se a ação foi 'removed_' (unvote/remoção do voto).
                if (data.action.startsWith('removed_')) {
                    // Reseta o texto do botão clicado para o padrão.
                    button.textContent = isUpvote ? defaultLikeText : defaultDownvoteText;
                    // Reseta o texto do botão oposto para o padrão.
                    if (otherButton) otherButton.textContent = isUpvote ? defaultDownvoteText : defaultLikeText;

                // Se a ação foi 'inserted_' (novo voto) ou 'changed_' (troca de voto).
                } else if (data.action.includes('inserted_') || data.action.includes('changed_')) {
                    
                    // O botão clicado exibe o estado de voto ativo.
                    button.textContent = isUpvote ? votedLikeText : votedDownvoteText;
                    
                    // O botão oposto volta para o texto padrão.
                    if (otherButton) otherButton.textContent = isUpvote ? defaultDownvoteText : defaultLikeText;
                }
                
            } else {
                // Se houve erro e a mensagem indica que o usuário não está logado.
                if (data.message.includes('Não logado')) {
                    alert('Você precisa estar logado para votar.');
                    button.textContent = 'Fazer Login';
                } else {
                    // Outros erros: exibe um alerta e restaura o texto do botão.
                    alert('Erro ao votar: ' + data.message);
                    button.textContent = isUpvote ? defaultLikeText : defaultDownvoteText;
                }
            }
        })
        .catch(error => {
            // Bloco para lidar com erros de rede ou falhas no servidor.
            console.error('Erro na requisição:', error);
            alert('Ocorreu um erro de rede ou servidor.');
            // Restaura o estado original dos botões.
            button.disabled = false;
            if (otherButton) otherButton.disabled = false;
            button.textContent = isUpvote ? '👍 Curtir' : '👎 Descurtir';
        });
    }

    // Adiciona o evento de clique a todos os botões de curtir.
    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', function() {
            handleVote(this, 'upvote'); 
        });
    });

    // Adiciona o evento de clique a todos os botões de descurtir.
    document.querySelectorAll('.downvote-btn').forEach(button => {
        button.addEventListener('click', function() {
            handleVote(this, 'downvote'); 
        });
    });
});
</script>

</body>
</html>