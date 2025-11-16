<?php
// Inclui o arquivo de conexão com o banco de dados
include 'conexao.php'; 
// Inicia ou retoma a sessão do usuário (necessário para pegar o ID do usuário logado)
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
        // Sites de Entretenimento
        'omelete.com.br' => 'https://www.google.com/s2/favicons?domain=omelete.com.br&sz=32',
        'tecmundo.com.br' => 'https://www.google.com/s2/favicons?domain=tecmundo.com.br&sz=32',
        'legiaodosherois.com.br' => 'https://www.google.com/s2/favicons?domain=legiaodosherois.com.br&sz=32',
        'splash.com.br' => 'https://www.google.com/s2/favicons?domain=splash.com.br&sz=32',
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
    <title>Notícias de Entretenimento - Foco Diário</title>
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
                <li><a href="noticias-brasil.php">Brasil</a></li>
                <li><a href="noticias-mundo.php" >Mundo</a></li>
                <li><a href="esportes.php">Esportes</a></li>
                <li><a href="entretenimento.php" class="active">Entretenimento</a></li>
                
                <?php if (isset($_SESSION['id_usuario'])): // CORREÇÃO: Usando 'id_usuario' para verificar se o usuário está logado ?>
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
                
                <section class="noticias-destaque-entretenimento">
                    <h2>Notícias de Entretenimento em Destaque</h2>
                    
                    <?php
                    // Query para buscar notícias da editoria 'Entretenimento' (LIMIT 2 para simular o layout de destaque)
                    $sql_destaque = "SELECT id_noticia, titulo, resumo, link_fonte, curtidas FROM noticias WHERE editoria = 'Entretenimento' ORDER BY data_publicacao DESC LIMIT 2";
                    $result_destaque = $conn->query($sql_destaque);

                    if ($result_destaque->num_rows > 0) {
                        $count = 0;
                        while($noticia = $result_destaque->fetch_assoc()) {
                            $count++;
                            
                            // Lógica de Imagens Hardcoded (Solução temporária para o problema das imagens não aparecerem, 
                            // já que a tabela 'noticias' não possui um campo 'imagem')
                            $img_url = '';
                            if ($count == 1) {
                                $img_url = 'https://s2-monet.glbimg.com/sSWnPc4-bR2pWQcS4_roOFlytBY=/0x0:1169x729/888x0/smart/filters:strip_icc()/i.s3.glbimg.com/v1/AUTH_e7c91519bbbb4fadb4e509085746275d/internal_photos/bs/2025/L/v/sbj8mXREOdMoo8wO7FEA/chalamet-nova.jpg';
                            } else if ($count == 2) {
                                $img_url = 'https://conteudo.imguol.com.br/c/splash/6f/2025/11/13/virginia-celina-locks-ronaldo-e-sabrina-sato-1763034329484_v2_900x506.jpg.webp';
                            } else {
                                $img_url = 'URL_DEFAULT_PARA_NOTICIAS_DE_ENTRETENIMENTO'; // Imagem de fallback
                            }
                            
                            // Extrair informações da fonte
                            $fonte_url = parse_url($noticia['link_fonte'], PHP_URL_HOST);
                            $fonte_nome = str_replace(['www.', '.com', '.com.br', '.org', '.net'], '', $fonte_url);
                            $fonte_nome = ucfirst($fonte_nome);
                            $fonte_favicon = obterFavicon($noticia['link_fonte']);
                            
                            // INÍCIO DA LÓGICA DE VOTO DO USUÁRIO (PHP)
                            $user_vote = null; // Inicializa a variável para armazenar o voto do usuário nesta notícia
                            if (isset($_SESSION['id_usuario'])) { // Verifica se o usuário está logado
                                // Prepara a consulta para buscar o tipo de voto (upvote ou downvote) do usuário para a notícia atual
                                $sql_voto = "SELECT tipo_voto FROM votos WHERE id_usuario = ? AND id_noticia = ?";
                                $stmt_voto = $conn->prepare($sql_voto);
                                $stmt_voto->bind_param("ii", $_SESSION['id_usuario'], $noticia['id_noticia']);
                                $stmt_voto->execute();
                                $result_voto = $stmt_voto->get_result();
                                
                                if ($result_voto->num_rows > 0) {
                                    $user_vote = $result_voto->fetch_assoc()['tipo_voto'];
                                }
                                $stmt_voto->close(); // Fecha a declaração preparada
                            }
                            // FIM DA LÓGICA DE VOTO DO USUÁRIO
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
                                            <img src="<?php echo $img_url; ?>" alt="Imagem da Notícia de Entretenimento">
                                        </a>
                                        
                                        <div class="noticia-destaque-texto">
                                            <!-- EXPLICAÇÃO/RESUMO (EMBAIXO DO TÍTULO) -->
                                            <p class="resumo-noticia"><?php echo htmlspecialchars($noticia['resumo']); ?></p>
                                            
                                            <!-- BOTÕES DE CURTIR (EMBAIXO DA EXPLICAÇÃO) -->
                                            <div class="feedback-area">
                                                <button 
                                                    class="like-btn" 
                                                    data-id-noticia="<?php echo $noticia['id_noticia']; ?>"
                                                    >
                                                    <?php echo ($user_vote === 'upvote') ? 'Curtiu!' : '👍 Curtir'; // Exibe o texto correto se o usuário já curtiu ?>
                                                </button>
                                                
                                                <button 
                                                    class="downvote-btn" 
                                                    data-id-noticia="<?php echo $noticia['id_noticia']; ?>"
                                                    >
                                                    <?php echo ($user_vote === 'downvote') ? 'Descurtiu!' : '👎 Descurtir'; // Exibe o texto correto se o usuário já descurtiu ?>
                                                </button>
                                                
                                                <span class="like-count-<?php echo $noticia['id_noticia']; ?>">
                                                    <?php echo $noticia['curtidas']; // Exibe a contagem atual de curtidas ?>
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
                        }
                    } else {
                        echo "<p>Nenhuma notícia de Entretenimento em destaque encontrada no banco de dados.</p>";
                    }
                    ?>
                </section>
                <section class="noticias-recentes-entretenimento">
                    <h2>Notícias Recentes</h2>
                    
                    <?php
                    // Query para buscar notícias mais recentes (exclui as 2 de destaque já mostradas)
                    // LIMIT 10 OFFSET 2: Pega 10 notícias a partir da terceira (índice 2)
                    $sql_recentes = "SELECT id_noticia, titulo, resumo, link_fonte, curtidas FROM noticias WHERE editoria = 'Entretenimento' ORDER BY data_publicacao DESC LIMIT 10 OFFSET 2";
                    $result_recentes = $conn->query($sql_recentes);

                    if ($result_recentes->num_rows > 0) {
                        while($noticia = $result_recentes->fetch_assoc()) {
                            
                            // Extrair informações da fonte
                            $fonte_url = parse_url($noticia['link_fonte'], PHP_URL_HOST);
                            $fonte_nome = str_replace(['www.', '.com', '.com.br', '.org', '.net'], '', $fonte_url);
                            $fonte_nome = ucfirst($fonte_nome);
                            $fonte_favicon = obterFavicon($noticia['link_fonte']);
                            
                            // INÍCIO DA LÓGICA DE VOTO DO USUÁRIO (PHP) - Repetido para as notícias recentes
                            $user_vote = null; 
                            if (isset($_SESSION['id_usuario'])) { 
                                $sql_voto = "SELECT tipo_voto FROM votos WHERE id_usuario = ? AND id_noticia = ?";
                                $stmt_voto = $conn->prepare($sql_voto);
                                $stmt_voto->bind_param("ii", $_SESSION['id_usuario'], $noticia['id_noticia']);
                                $stmt_voto->execute();
                                $result_voto = $stmt_voto->get_result();
                                
                                if ($result_voto->num_rows > 0) {
                                    $user_vote = $result_voto->fetch_assoc()['tipo_voto'];
                                }
                                $stmt_voto->close();
                            }
                            // FIM DA LÓGICA DE VOTO DO USUÁRIO
                    ?>
                            <article class="noticia-recente" data-id="<?php echo $noticia['id_noticia']; ?>">
                                <!-- TÍTULO CLICÁVEL (já existente, mantido) -->
                                <h4><a href="<?php echo htmlspecialchars($noticia['link_fonte']); ?>" target="_blank"><?php echo htmlspecialchars($noticia['titulo']); ?></a></h4>
                                
                                <div class="feedback-area-recente">
                                    <button 
                                        class="like-btn" 
                                        data-id-noticia="<?php echo $noticia['id_noticia']; ?>"
                                        >
                                        <?php echo ($user_vote === 'upvote') ? 'Curtiu!' : '👍 Curtir'; ?>
                                    </button>
                                    
                                    <button 
                                        class="downvote-btn" 
                                        data-id-noticia="<?php echo $noticia['id_noticia']; ?>"
                                        >
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
                        }
                    } else {
                        echo "<p>Nenhuma notícia recente de Entretenimento encontrada no banco de dados.</p>";
                    }
                    ?>
                </section>
                </div>

            <aside class="barra-lateral">
                <section class="mais-lidas">
                    <h3>Mais Lidas</h3>
                    <ul>
                        <li><h4><a href="https://www.omelete.com.br/filmes/cara-de-barro-foto-set-confirma-asa-noturna-robin-dcu">Cara de Barro | Foto do set confirma Asa Noturna/Robin no DCU</a></h4></li>
                        <li><h4><a href="https://www.tecmundo.com.br/minha-serie/602076-marvel-revela-primeiras-imagens-de-doutor-destino-de-robert-downey-jr-confira-visual.htm">Marvel revela imagens de Doutor Destino de Robert Downey Jr em Vingadores Doomsday</a></h4></li>
                        <li><h4><a href="https://www.legiaodosherois.com.br/2025/invocacao-do-mal-franquia-pode-ganhar-um-filme-prequel.html">Invocação do Mal: Franquia pode ganhar um filme prequel</a></h4></li>
                        <li><h4><a href="https://www.metropoles.com/entretenimento/novo-filme-de-downton-abbey-e-mais-estreias-imperdiveis-da-semana">Novo filme de Downton Abbey e mais estreias imperdíveis da semana</a></h4></li>
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
// Fechar a conexão com o banco de dados
$conn->close();
?>

    <footer>
        <p>&copy; 2025 Foco Diário. Todos os direitos reservados.</p>
    </footer>

<script>
// INÍCIO DA LÓGICA DE VOTO: JavaScript com suporte a UNVOTE (remover voto) e SWAP (trocar voto)
document.addEventListener('DOMContentLoaded', function() {
    
    // Função genérica para lidar com requisições de voto
    function handleVote(button, actionType) {
        const idNoticia = button.getAttribute('data-id-noticia'); // ID da notícia a ser votada
        const phpFile = actionType === 'upvote' ? 'curtir.php' : 'downvote.php'; // Define o arquivo PHP a ser chamado
        
        const isUpvote = (actionType === 'upvote');
        const countSpan = document.querySelector(`.like-count-${idNoticia}`); // Elemento para atualizar a contagem de curtidas
        // Seleciona o botão oposto
        const otherButton = document.querySelector(`[data-id-noticia="${idNoticia}"]${isUpvote ? '.downvote-btn' : '.like-btn'}`);

        // Desabilita os botões *temporariamente* enquanto processa (evita cliques duplos)
        button.disabled = true;
        if (otherButton) otherButton.disabled = true;
        button.textContent = 'Processando...'; // Feedback visual

        // Envia a requisição POST (AJAX/Fetch) para o arquivo PHP
        fetch(phpFile, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id_noticia=' + idNoticia // Envia o ID da notícia no corpo da requisição
        })
        .then(response => {
             // 403 Forbidden - Tratamento para usuário não logado
             if (response.status === 403) {
                return { success: false, message: 'Não logado' }; 
             }
             // Tenta analisar a resposta como JSON
             return response.json();
        })
        .then(data => {
            // Re-habilita os botões após receber a resposta do servidor
            button.disabled = false;
            if (otherButton) otherButton.disabled = false;

            if (data.success) {
                
                // 1. Atualiza a contagem no DOM
                if (countSpan) {
                    countSpan.textContent = data.new_count;
                }

                // 2. Lógica para redefinir o estado dos botões (textos)
                const defaultLikeText = '👍 Curtir';
                const votedLikeText = 'Curtiu!';
                const defaultDownvoteText = '👎 Descurtir';
                const votedDownvoteText = 'Descurtiu!';

                if (data.action.startsWith('removed_')) {
                    // Ação de REMOÇÃO (Unvote) -> Resetar o texto de AMBOS os botões
                    button.textContent = isUpvote ? defaultLikeText : defaultDownvoteText;
                    if (otherButton) otherButton.textContent = isUpvote ? defaultDownvoteText : defaultLikeText;

                } else if (data.action.includes('inserted_') || data.action.includes('changed_')) {
                    // Ação de INSERÇÃO (primeiro voto) ou TROCA (Swap)
                    
                    // Botão clicado recebe o texto de voto ativo
                    button.textContent = isUpvote ? votedLikeText : votedDownvoteText;
                    
                    // Botão oposto recebe o texto Padrão
                    if (otherButton) otherButton.textContent = isUpvote ? defaultDownvoteText : defaultLikeText;
                }
                
            } else {
                // Tratamento de Erro e não logado
                if (data.message.includes('Não logado')) {
                    alert('Você precisa estar logado para votar.');
                    button.textContent = 'Fazer Login';
                } else {
                    alert('Erro ao votar: ' + data.message);
                    // Restaura o texto do botão para o padrão em caso de erro
                    button.textContent = isUpvote ? defaultLikeText : defaultDownvoteText;
                }
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            alert('Ocorreu um erro de rede ou servidor.');
            // Restaura o texto e habilita o botão em caso de falha de rede/servidor
            button.disabled = false;
            if (otherButton) otherButton.disabled = false;
            button.textContent = isUpvote ? '👍 Curtir' : '👎 Descurtir';
        });
    }

    // Adiciona evento de clique para Curtir
    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', function() {
            handleVote(this, 'upvote'); // Chama a função para 'upvote'
        });
    });

    // Adiciona evento de clique para Descurtir
    document.querySelectorAll('.downvote-btn').forEach(button => {
        button.addEventListener('click', function() {
            handleVote(this, 'downvote'); // Chama a função para 'downvote'
        });
    });
});
</script>

</body>
</html>