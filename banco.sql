CREATE DATABASE IF NOT EXISTS focodiario;
USE focodiario;

CREATE TABLE IF NOT EXISTS administrador (
  id_admin INT NOT NULL AUTO_INCREMENT,
  nome VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  senha VARCHAR(255) NOT NULL,
  nivel_acesso VARCHAR(50) NOT NULL,
  PRIMARY KEY (id_admin),
  UNIQUE INDEX email_UNIQUE (email ASC));
  
  CREATE TABLE IF NOT EXISTS noticias (
  id_noticia INT NOT NULL AUTO_INCREMENT,
  titulo VARCHAR(255) NOT NULL,
  resumo TEXT NOT NULL,
  link_fonte VARCHAR(255) NOT NULL,
  nome_fonte VARCHAR(100) NOT NULL,
  data_publicacao DATETIME NOT NULL,
  editoria VARCHAR(100) NOT NULL,
  cliques INT NULL DEFAULT 0,
  PRIMARY KEY (id_noticia));
  
  ALTER TABLE noticias
ADD COLUMN curtidas INT NULL DEFAULT 0 AFTER cliques;

CREATE TABLE IF NOT EXISTS usuario (
  id_usuario INT NOT NULL AUTO_INCREMENT,
  nome VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  senha VARCHAR(255) NOT NULL,
  data_cadastro DATE NOT NULL,
  status_conta VARCHAR(50) NULL DEFAULT 'Pendente de validação',
  PRIMARY KEY (id_usuario),
  UNIQUE INDEX email_UNIQUE (email ASC));
  
  ALTER TABLE usuario
  ADD COLUMN codigo_verificacao_hash VARCHAR(255) NULL,
  ADD COLUMN codigo_criado_em DATETIME NULL,
  ADD COLUMN codigo_expires_em DATETIME NULL,
  ADD COLUMN qtd_reenvios INT NOT NULL DEFAULT 0,
  ADD COLUMN ultimo_reenvio DATETIME NULL,
  ADD COLUMN verificado TINYINT(1) NOT NULL DEFAULT 0;
  
  ALTER TABLE usuario
ADD COLUMN codigo_redefinicao_hash VARCHAR(255) NULL;

ALTER TABLE usuario
ADD COLUMN codigo_redefinicao_expires DATETIME NULL;

ALTER TABLE usuario
ADD COLUMN codigo_redefinicao_criado DATETIME NULL;

CREATE TABLE IF NOT EXISTS feedback (
  id_feedback INT NOT NULL AUTO_INCREMENT,
  id_usuario INT NULL DEFAULT NULL,
  assunto VARCHAR(255) NULL DEFAULT NULL,
  mensagem TEXT NOT NULL,
  data_envio DATETIME NOT NULL,
  status VARCHAR(50) NULL DEFAULT 'Novo',
  PRIMARY KEY (id_feedback),
  INDEX id_usuario_idx (id_usuario ASC),
  CONSTRAINT fk_feedback_usuario
    FOREIGN KEY (id_usuario)
    REFERENCES usuario (id_usuario));
    
    CREATE TABLE IF NOT EXISTS fontes (
  id_fonte INT NOT NULL AUTO_INCREMENT,
  nome_fonte VARCHAR(100) NOT NULL,
  url_base VARCHAR(255) NOT NULL,
  url_feed VARCHAR(255) NOT NULL,
  status_coleta VARCHAR(50) NULL DEFAULT 'Ativa',
  PRIMARY KEY (id_fonte),
  UNIQUE INDEX nome_fonte_UNIQUE (nome_fonte ASC));
  
  
  INSERT INTO fontes (nome_fonte, url_base, url_feed, status_coleta)
VALUES 
('BBCBrasil', 'https://www.bbc.com/portuguese', 'http://feeds.bbci.co.uk/news/rss.xml', 'Ativa'),
('meupositivo', 'https://www.meupositivo.com.br/panoramapositivo/positivo-tecnologia-a-parceira-do-cio-na-era-da-inteligencia-artificial/', 'https://www.meupositivo.com.br/panoramapositivo/positivo-tecnologia-a-parceira-do-cio-na-era-da-inteligencia-artificial/','Ativa'),
('G1', 'https://g1.globo.com', 'https://g1.globo.com/dynamo/rss2.xml','Ativa'),
('GE', 'https://ge.globo.com', 'https://g1.globo.com/dynamo/rss2.xml', 'Ativa'),
('UOL','https://www.uol.com.br', 'https://rss.uol.com.br/feed/noticias.xml','Ativa'),
('TechCrunch', 'https://techcrunch.com', 'http://feeds.feedburner.com/TechCrunch/', 'Ativa'),
('CNNBrasil', 'https://www.cnnbrasil.com.br', 'http://rss.cnn.com/rss/edition.rss', 'Ativa');

CREATE TABLE IF NOT EXISTS log_coleta (
  id_log INT NOT NULL AUTO_INCREMENT,
  data_hora DATETIME NOT NULL,
  status VARCHAR(50) NOT NULL,
  mensagem TEXT NULL DEFAULT NULL,
  fonte_id INT NULL DEFAULT NULL,
  PRIMARY KEY (id_log),
  INDEX fonte_id_idx (fonte_id ASC),
  CONSTRAINT fk_log_coleta_fontes
    FOREIGN KEY (fonte_id)
    REFERENCES fontes (id_fonte));
    
    CREATE TABLE IF NOT EXISTS tags (
  id_tag INT NOT NULL AUTO_INCREMENT,
  nome_tag VARCHAR(100) NOT NULL,
  PRIMARY KEY (id_tag),
  UNIQUE INDEX nome_tag_UNIQUE (nome_tag ASC));
  
  CREATE TABLE IF NOT EXISTS noticias_tags (
  id_noticia_tag INT NOT NULL AUTO_INCREMENT,
  id_noticia INT NULL DEFAULT NULL,
  id_tag INT NULL DEFAULT NULL,
  PRIMARY KEY (id_noticia_tag),
  INDEX id_noticia_idx (id_noticia ASC),
  INDEX id_tag_idx (id_tag ASC),
  CONSTRAINT fk_noticias_tags_noticias
    FOREIGN KEY (id_noticia)
    REFERENCES noticias (id_noticia),
  CONSTRAINT fk_noticias_tags_tags
    FOREIGN KEY (id_tag)
    REFERENCES tags (id_tag));
    
    CREATE TABLE IF NOT EXISTS votos (
  id_voto INT NOT NULL AUTO_INCREMENT,
  id_noticia INT NOT NULL,
  id_usuario INT NOT NULL,
  tipo_voto ENUM('upvote', 'downvote') NOT NULL, -- O voto será 'upvote' ou 'downvote'
  data_voto DATETIME NOT NULL,
  PRIMARY KEY (id_voto),
  UNIQUE INDEX usuario_noticia_UNIQUE (id_usuario ASC, id_noticia ASC),
  CONSTRAINT fk_votos_noticias
    FOREIGN KEY (id_noticia)
    REFERENCES noticias (id_noticia),
  CONSTRAINT fk_votos_usuario
    FOREIGN KEY (id_usuario)
    REFERENCES usuario (id_usuario)
);

ALTER TABLE votos
DROP FOREIGN KEY fk_votos_usuario;

ALTER TABLE votos
ADD CONSTRAINT fk_votos_usuario
    FOREIGN KEY (id_usuario)
    REFERENCES usuario(id_usuario)
    ON DELETE CASCADE;

INSERT INTO noticias  -- Página inicial
    (titulo, resumo, link_fonte, nome_fonte, data_publicacao, editoria, cliques, curtidas) 
VALUES
    ('Trump diz estar surpreso e descontente com condenação de Bolsonaro', 
     'Trump já havia chamado o caso contra Bolsonaro de "caça às bruxas" e criticado duramente o Brasil', 
     'artigo.html', -- Link genérico do HTML original
     'CNN Brasil', NOW(), 'Brasil', 100, 50),
    ('Primeira Turma do STF forma maioria para condenar Bolsonaro por organização criminosa', 
     'Ex-presidente é apontado como líder de uma organização criminosa que tentou impedir Lula de assumir a presidência; ministros Cármen Lúcia e Cristiano Zanin ainda vão votar.', 
     'https://g1.globo.com/politica/noticia/2025/09/11/primeira-turma-do-stf-forma-maioria-para-condenar-bolsonaro-por-organizacao-criminosa.ghtml', 
     'G1', NOW(), 'Brasil', 90, 40),
    ('Explosão em imovél com fogos de artifícios deixa ao menos 2 feridos em SP', 
     'Explosão em um galpão que armazenava fogos de artificio na avenida Celso Garcia, no Tatuapé, zona Leste de São Paulo.', 
     'https://noticias.uol.com.br/cotidiano/ultimas-noticias/2025/11/13/explosao-em-imovel-com-fogos-de-artificios-e-registrada-na-zona-leste.htm', 
     'Carta Capital', NOW(), 'Mundo', 65, 25),
    ('Urgente! Novo concurso Câmara dos Deputados é anunciado', 
     'Um novo concurso Câmada dos Deputados foi anunciado oficialmente pelo presidente Hugo Motta nesta quinta-feira, 11. Veja detalhes!', 
     'https://folha.qconcursos.com/n/concurso-camara-dos-deputados-2025-presidente-anuncia-novo-edital', 
     'Folha QConcursos', NOW(), 'Brasil', 35, 12);
     
     INSERT INTO noticias  -- Brasil
    (titulo, resumo, link_fonte, nome_fonte, data_publicacao, editoria, cliques, curtidas) 
VALUES
    ('Gabarito oficial do Enem 2025 é divulgado pelo Inep; confira as respostas do 1º domingo', 
     'Prova trouxe 45 questões de linguagens, 45 de ciências humanas e a redação. Em 16 de novembro, candidatos resolverão perguntas de matemática e de ciências da natureza.', 
     'https://g1.globo.com/educacao/enem/2025/noticia/2025/11/13/gabarito-oficial-do-enem-2025-e-divulgado-pelo-inep-confira-as-respostas-do-1o-domingo.ghtml', 
     'G1', NOW(), 'Brasil', 80, 45),
    ('VÍDEO: explosão com incêndio provoca destruição e deixa feridos na Zona Leste de SP', 
     'Caso foi registrado na esquina da Avenida Celso Garcia com a Avenida Salim Farah Maluf, no Tatuapé; pelo menos duas pessoas se feriram.', 
     'https://g1.globo.com/sp/sao-paulo/noticia/2025/11/13/explosao-com-incendido-e-registrada-na-zona-leste-de-sp.ghtml', 
     'G1', NOW(), 'Brasil', 75, 30),
    ('PF:Ex-ministro de Bolsonaro mandou mensagem agradecendo propina', 
     'A investigação da Policia federal sobre José Carlos Oliveira aponta que o ex-ministro da previdencia e ex-presidente do INSS  no governo Jair Bolsonaro enviou mensasgens de WhatsApp agradecendo pelo recebimento de propina por auxiliar o esquema de desvio de dinheiro dos aposentados.', 
     'https://noticias.uol.com.br/colunas/daniela-lima/2025/11/13/pf-ex-ministro-de-bolsonaro-mandou-mensagem-agradecendo-propina.htm', 
     'UOL', NOW(), 'Brasil', 40, 10),
    ('Lula faz reunião ministerial e cobra empenho na aprovação de pautas ligadas à segurança pública', 
     'Em menos de uma semana, o relator do texto antifacção, Guilherme Derrite, do Progressistas, apresentou quatro versões. A sucessão de versões reflete a polarização em torno da matéria.', 
     'https://g1.globo.com/jornal-nacional/noticia/2025/11/13/lula-faz-reuniao-ministerial-e-cobra-empenho-na-aprovacao-de-pautas-ligadas-a-seguranca-publica.ghtml', 
     'G1', NOW(), 'Brasil', 25, 5);
     
     INSERT INTO noticias -- Mundo 
    (titulo, resumo, link_fonte, nome_fonte, data_publicacao, editoria, cliques, curtidas) 
VALUES
    ('Blue Origin lança supernave New Glenn pela 2ª vez, com satélites que vão estudar Marte', 
     'Missão não tripulada soltou no espaço sondas da Nasa que chegarão a Marte em 2027. Nave pertence a Jeff Bezos, que foi parabenizado pelo rival Elon Musk.', 
     'https://g1.globo.com/inovacao/noticia/2025/11/13/blue-origin-lanca-supernave-new-glenn.ghtml', 
     'G1', NOW(), 'Mundo', 25, 10),
    ('EUA anunciam operação militar contra narcotráfico na América Latina', 
     'Secretário da Defesa americano faz anúncio em meio a desgaste de governo Trump com caso Jeffrey Epstein', 
     'https://www1.folha.uol.com.br/mundo/2025/11/eua-anunciam-operacao-lanca-do-sul-contra-narcotrafico-na-america-latina.shtml', 
     'UOL', NOW(), 'Mundo', 35, 8),
    ('Irã pede que ONU responsabilize EUA por ataques contra instalações nuclear', 
     'O ministro das Relações Exteriores do país disse que o presidente americano e outros funcionários tem "responsabilidade criminal" pela ofensiva', 
     'https://www.cnnbrasil.com.br/internacional/ira-pede-que-onu-responsabilize-eua-por-ataques-contra-instalacoes-nuclear', 
     'CNNBrasil', NOW(), 'Mundo', 12, 4),
    ('Itália investiga denúncias de que turistas pagaram para atirar em civis na guerra da Bósnia', 
     'Durante a guerra da Bósnia, civis arriscavam suas vidas para atravessar o principal bulevar de Sarajevo', 
     'https://www.bbc.com/portuguese/articles/cj97g9prdw7o', 
     'BBCBrasil', NOW(), 'Mundo', 40, 21);
     
     INSERT INTO noticias -- Esportes
    (titulo, resumo, link_fonte, nome_fonte, data_publicacao, editoria, cliques, curtidas) 
VALUES
    ('Bruno Henrique é absolvido por fraude ligada a apostas e reverte suspensão', 
     'Atacante do Flamengo foi enquadrado somente por infrações relativas ao descumprimento de regulamentos de competição e deverá pagar multa de R$ 100 mil', 
     'https://www.cnnbrasil.com.br/esportes/futebol/bruno-henrique-e-absolvido-por-fraude-ligada-a-apostas-e-reverte-suspensao', 
     'CNNBrasil', NOW(), 'Esportes', 55, 30),
    ('Com Cristiano Ronaldo expulso, Irlanda vence Portugal e adia vaga na Copa', 
     'Com dois gols de Parrot, Irlanda surpreende em Dublin, vence por 2 a 0 e impede classificação antecipada de Portugal, que jogou com um a menos após expulsão de CR7', 
     'https://www.cnnbrasil.com.br/esportes/futebol/futebol-internacional/eliminatorias/com-cristiano-ronaldo-expulso-irlanda-vence-portugal-e-adia-vaga-na-copa', 
     'CNNBrasil', NOW(), 'Esportes', 65, 40),
    ('Veja todas as seleções já classificadas para a Copa do Mundo de 2026', 
     'Mundial vai estrear formato com 48 seleções no próximo ano', 
     'https://ge.globo.com/futebol/futebol-internacional/noticia/2025/03/26/veja-todas-as-selecoes-ja-classificadas-para-a-copa-do-mundo-de-2026.ghtml', 
     'GE Globo', NOW(), 'Esportes', 30, 15),
    ('Guia do Brasileirão 2025', 
     'Quais os favoritos ao título? Quais corem maior risco de cair? Veja as informações e faça sua avaliação', 
     'https://interativos.ge.globo.com/futebol/brasileirao-serie-a/especial/guia-do-brasileirao-2025', 
     'GE', NOW(), 'Esportes', 20, 8); 
     
     INSERT INTO noticias  -- Entretenimento
    (titulo, resumo, link_fonte, nome_fonte, data_publicacao, editoria, cliques, curtidas) 
VALUES
    ('Timothee Chalamet vem ao Brasil ', 
     'O ator Timothee Chalamet vem ao Brasil para divulgar Marty Supreme, nova produção da A24.', 
     'https://www.uol.com.br/splash/colunas/amaury-jr/2025/11/13/timothee-chalamet-vem-ao-brasil-para-a-ccxp25.htm', 
     'UOL', NOW(), 'Entretenimento', 80, 50),
    ('Vírginia, Sato e mais: Ronaldo reúne leilão beneficente com time de famosos', 
     'O leilão Galacticos, idealizado por Ronaldo Fenomeno, contou com a presença de muitos famosos na noite de ontem em São Paulo.', 
     'https://www.uol.com.br/splash/noticias/2025/11/13/virginia-sato-e-mais-ronaldo-reune-time-de-famosos-em-leilao-beneficente.htm', 
     'UOL', NOW(), 'Entretenimento', 60, 45),
    ('Grammy Latino; veja a lista dos vencedores de 2025', 
     'A 26ª edição do Grammy Latino 2025 acontece nesta quinta-feira (13), em Las Vegas.', 
     'https://g1.globo.com/pop-arte/musica/noticia/2025/11/13/grammy-latino-2025-e-nesta-quinta-feira-13-veja-tudo-o-que-voce-precisa-saber-1.ghtml', 
     'G1', NOW(), 'Entretenimento', 45, 20),
    ('Filmes de Sydney Sweeney fracassam nas bilheterias após polêmica de comercial de jeans', 
     'Lançamento mais recente da atriz, Christy arrecadou pouco mais de US$ 1 milhão em seu fim de semana de estreia. Ela foi criticada em julho por propaganda considerada eugenista.', 
     'https://g1.globo.com/pop-arte/cinema/noticia/2025/11/13/filmes-de-sydney-sweeney-fracassam-nas-bilheterias-apos-polemica-de-comercial-de-jeans.ghtml', 
     'G1', NOW(), 'Entretenimento', 30, 10); 
     
     
     SET FOREIGN_KEY_CHECKS = 0;
     
     TRUNCATE TABLE noticias; 
     
     SET FOREIGN_KEY_CHECKS = 1;
     
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
