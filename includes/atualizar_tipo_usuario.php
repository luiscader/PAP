<?php
include 'C:/wamp64/www/PAP/includes/config.php'; 

function atualizar_tipo_usuario($conn, $user_id, $novo_tipo) {
    $sql = "UPDATE utilizador SET tipo = ? WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $novo_tipo, $user_id);
        if ($stmt->execute()) {
            $_SESSION['tipo'] = $novo_tipo;
            return true;
        } else {
            echo "Erro ao atualizar o tipo de usuário: " . $conn->error;
            return false;
        }
        $stmt->close();
    } else {
        echo "Erro ao preparar a consulta: " . $conn->error;
        return false;
    }
}

?>
