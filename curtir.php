<?php
session_start();
header('Content-Type: application/json');

// Usando 'id_usuario' para verificar o login
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

include 'conexao.php';
$usuario_id = $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_noticia'])) {
    $noticia_id = (int)$_POST['id_noticia'];
    
    $conn->begin_transaction();
    $action = '';
    
    try {
        // 1. VERIFICA SE O USUÁRIO JÁ VOTOU
        $sql_check = "SELECT id_voto, tipo_voto FROM votos WHERE id_usuario = ? AND id_noticia = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("ii", $usuario_id, $noticia_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // Voto EXISTE.
            $existing_vote = $result_check->fetch_assoc();
            
            if ($existing_vote['tipo_voto'] === 'upvote') {
                // Usuário CLICOU EM CURTIR, mas JÁ CURTIU. -> REMOVER VOTO (UNVOTE)
                
                // A. Remove o voto da tabela 'votos'
                $sql_delete_voto = "DELETE FROM votos WHERE id_voto = ?";
                $stmt_delete_voto = $conn->prepare($sql_delete_voto);
                $stmt_delete_voto->bind_param("i", $existing_vote['id_voto']);
                $stmt_delete_voto->execute();
                
                // B. Atualiza o contador de curtidas na tabela 'noticias' (-1 para remover o upvote)
                $sql_update_count = "UPDATE noticias SET curtidas = GREATEST(0, curtidas - 1) WHERE id_noticia = ?";
                $stmt_update_count = $conn->prepare($sql_update_count);
                $stmt_update_count->bind_param("i", $noticia_id);
                $stmt_update_count->execute();
                
                $action = 'removed_upvote'; 
                
            } else { 
                // Usuário CLICOU EM CURTIR, mas JÁ TINHA DESCURTIDO ('downvote'). -> SWAP
                
                // A. Atualiza o voto (de downvote para upvote)
                $sql_update_voto = "UPDATE votos SET tipo_voto = 'upvote', data_voto = NOW() WHERE id_voto = ?";
                $stmt_update_voto = $conn->prepare($sql_update_voto);
                $stmt_update_voto->bind_param("i", $existing_vote['id_voto']);
                $stmt_update_voto->execute();
                
                // B. Atualiza o contador de curtidas na tabela 'noticias' (+2 para reverter o downvote e adicionar o upvote)
                $sql_update_count = "UPDATE noticias SET curtidas = curtidas + 2 WHERE id_noticia = ?";
                $stmt_update_count = $conn->prepare($sql_update_count);
                $stmt_update_count->bind_param("i", $noticia_id);
                $stmt_update_count->execute();
                
                $action = 'changed_from_downvote';
            }
        } else {
            // Voto NÃO EXISTE. -> INSERT
            
            $sql_log = "INSERT INTO votos (id_usuario, id_noticia, tipo_voto, data_voto) VALUES (?, ?, 'upvote', NOW())";
            $stmt_log = $conn->prepare($sql_log);
            $stmt_log->bind_param("ii", $usuario_id, $noticia_id);
            $stmt_log->execute();

            $sql_update = "UPDATE noticias SET curtidas = curtidas + 1 WHERE id_noticia = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $noticia_id);
            $stmt_update->execute();
            
            $action = 'inserted_upvote';
        }

        // 2. OBTÉM A NOVA CONTAGEM
        $sql_count = "SELECT curtidas FROM noticias WHERE id_noticia = ?";
        $stmt_count = $conn->prepare($sql_count);
        $stmt_count->bind_param("i", $noticia_id);
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        $new_count = $result_count->fetch_assoc()['curtidas'];

        $conn->commit();
        
        // Se a ação for 'removed_upvote', 'changed_from_downvote', ou 'inserted_upvote'
        echo json_encode(['success' => true, 'new_count' => $new_count, 'action' => $action]);

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        error_log("Erro ao curtir: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erro interno do servidor.']);
    } finally {
        if (isset($stmt_check)) $stmt_check->close();
        if (isset($stmt_log)) $stmt_log->close();
        if (isset($stmt_update)) $stmt_update->close();
        if (isset($stmt_count)) $stmt_count->close();
        if (isset($stmt_update_voto)) $stmt_update_voto->close();
        if (isset($stmt_update_count)) $stmt_update_count->close();
        if (isset($stmt_delete_voto)) $stmt_delete_voto->close(); // Fecha o novo statement
        $conn->close();
    }
} else {
    http_response_code(400); 
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
}
?>