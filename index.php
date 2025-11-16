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
        'agenciabrasil.ebc.com.br' => 'https://www.google.com/s2/favicons?domain=ebc.com.br&sz=32',
        'blog.hubdodesenvolvedor.com.br' => 'https://www.google.com/s2/favicons?domain=hubdodesenvolvedor.com.br&sz=32',
        'datacenterdynamics.com' => 'https://www.google.com/s2/favicons?domain=datacenterdynamics.com&sz=32',
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
    <title>Foco Diário</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="header-container">
        <div class="site-title">
            <h1><a href="index.php">Foco Diário</a></h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php" class="active">Início</a></li>
        <li><a href="noticias-brasil.php">Brasil</a></li>
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
        <div class="container-destaques-topo">
            <div class="barra-pesquisa">
                <input type="text" placeholder="Pesquisar...">
                <button><img src="" alt="Pesquisar"></button>
            </div>
            <div class="notificacoes">
                <button><img src="" alt="Notificações"></button>
            </div>
        </div>

        <section class="destaques-topo">
            <article class="destaque-item">
                <img src="https://www.meupositivo.com.br/panoramapositivo/wp-content/uploads/2025/05/a9eee10f-5f25-44b0-bf30-7deeed63da6b.png" alt="Imagem Destaque 1">
                <h3><a href="https://www.meupositivo.com.br/panoramapositivo/positivo-tecnologia-a-parceira-do-cio-na-era-da-inteligencia-artificial/">Positivo TecnologIA: IA e inovação para CIOs modernos</a></h3>
            </article>
            <article class="destaque-item">
                <img src="https://blog.hubdodesenvolvedor.com.br/wp-content/uploads/2024/02/getty_938065026_383345.jpg" alt="Imagem Destaque 2">
                <h3><a href="https://blog.hubdodesenvolvedor.com.br/heart-intelligence-o-que-e/">Heart intelligence: por que profissionais de TI devem olhar para ela?</a></h3>
            </article>
            <article class="destaque-item">
                <img src="https://media.datacenterdynamics.com/media/images/unnamed1_Smp9thh.original.jpg" alt="Imagem Destaque 3">
                <h3><a href="https://www.datacenterdynamics.com/br/not%C3%ADcias/made-in-brazil-profissionais-de-ti-tem-sido-cada-vez-mais-valorizados-no-exterior/">Profissionais brasileiros de TI têm sido cada vez mais valorizados no exterior</a></h3>
            </article>
        </section>
        
        <div class="container-principal">
            <div class="conteudo-principal">
                
                <section class="noticias-destaque">
                    <h2>Notícias em Destaque</h2>
                    
                    <?php
                    // Query SQL para buscar as 2 notícias mais recentes de TODAS as editorias.
                    $sql_destaque_principal = "SELECT id_noticia, titulo, resumo, link_fonte, editoria, curtidas FROM noticias ORDER BY data_publicacao DESC LIMIT 2";
                    // Executa a query.
                    $result_destaque_principal = $conn->query($sql_destaque_principal);

                    // Verifica se a consulta retornou alguma notícia.
                    if ($result_destaque_principal->num_rows > 0) {
                        $count = 0;
                        // Inicia o loop para processar cada notícia de destaque.
                        while($noticia = $result_destaque_principal->fetch_assoc()) {
                            $count++;
                            
                            // Lógica de imagens simplificada para a Home.
                            $img_url = '';
                            if ($count == 1) {
                                $img_url = 'https://ichef.bbci.co.uk/ace/ws/800/cpsprodpb/c9f5/live/46fca6e0-bfc9-11f0-b880-bdabe65471f0.jpg.webp';
                            } else if ($count == 2) {
                                $img_url = 'https://admin.cnnbrasil.com.br/wp-content/uploads/sites/12/Reuters_Direct_Media/BrazilOnlineReportWorldNews/tagreuters.com2025binary_LYNXMPEL6U0QI-BASEIMAGE.jpg?w=1200&h=900&crop=0';
                            }
                            
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
                            <article class="noticia" data-id="<?php echo $noticia['id_noticia']; ?>">
                                <div class="noticia-destaque-container">
                                    <!-- TÍTULO CLICÁVEL -->
                                    <a href="<?php echo htmlspecialchars($noticia['link_fonte']); ?>" target="_blank" class="titulo-link">
                                        <h3><?php echo htmlspecialchars($noticia['titulo']); ?></h3>
                                    </a>
                                    
                                    <div class="noticia-destaque-conteudo">
                                        <!-- IMAGEM CLICÁVEL -->
                                        <a href="<?php echo htmlspecialchars($noticia['link_fonte']); ?>" target="_blank" class="imagem-link">
                                            <img src="<?php echo $img_url; ?>" alt="Imagem da Notícia">
                                        </a>
                                        
                                        <div class="noticia-destaque-texto">
                                            <!-- EDITORIA TAG -->
                                            <span class="editoria-tag">[<?php echo htmlspecialchars($noticia['editoria']); ?>]</span>
                                            
                                            <!-- EXPLICAÇÃO/RESUMO -->
                                            <p class="resumo-noticia"><?php echo htmlspecialchars($noticia['resumo']); ?></p>

                                            <!-- BOTÕES DE CURTIR -->
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
                        echo "<p>Nenhuma notícia em destaque encontrada no banco de dados.</p>";
                    }
                    ?>
                </section>
                <section class="noticias-recentes">
                    <h2>Notícias Recentes</h2>
                    
                    <?php
                    // Query SQL para buscar notícias mais recentes. OFFSET 2 pula as duas de destaque.
                    $sql_recentes = "SELECT id_noticia, titulo, resumo, link_fonte, editoria, curtidas FROM noticias ORDER BY data_publicacao DESC LIMIT 4 OFFSET 2";
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
                                <!-- TÍTULO CLICÁVEL -->
                                <h4><a href="<?php echo htmlspecialchars($noticia['link_fonte']); ?>" target="_blank"><?php echo htmlspecialchars($noticia['titulo']); ?></a></h4>
                                <span class="editoria-tag-recente">[<?php echo htmlspecialchars($noticia['editoria']); ?>]</span>

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
                                
                                <!-- RESUMO SEM "LEIA MAIS" -->
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
                        echo "<p>Nenhuma notícia recente encontrada no banco de dados.</p>";
                    }
                    ?>
                </section>
                </div>

            <aside class="barra-lateral">
                <section class="mais-lidas">
                    <h3>Mais Lidas</h3>
                    <ul>
                        <li><a href="https://agenciabrasil.ebc.com.br/justica/noticia/2025-09/por-4-1-stf-condena-bolsonaro-e-mais-sete-pela-trama-golpista">Por 4 a 1, STF condena Bolsonaro e mais sete pela trama golpista</a></li>
                        <li><a href="https://www.cnnbrasil.com.br/politica/carmen-lucia-e-dino-fazem-dobradinha-em-indiretas-a-fux-veja-falas">Cármen Lúcia e Dino fazem dobradinha em indiretas a Fux; veja falas</a></li>
                        <li><a href="https://g1.globo.com/politica/noticia/2025/09/11/trama-golpista-stf-agora-vai-decidir-as-penas-na-dosimetria-veja-como-funcional.ghtml">Trama golpista: STF agora vai decidir as penas na dosimetria; veja como funciona</a></li>
                        <li><a href="https://www.metropoles.com/distrito-federal/homem-de-61-anos-cai-de-telhado-e-entra-em-parada-cardiaca">Homem de 61 anos cai de telhado e entra em parada cardíaca</a></li>
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