<?php
include 'C:/wamp64/www/PAP/includes/config.php';  // Inclui a conexão à base de dados

$nivel_critico = 10; // Definir nível crítico

$sql_estoque = "SELECT * FROM Produto WHERE quantidade <= $nivel_critico";
$result_estoque = $conn->query($sql_estoque);

// Exibir produtos com estoque crítico
if ($result_estoque->num_rows > 0) {
    echo "<h1>Estoque Crítico</h1>";
    while ($row = $result_estoque->fetch_assoc()) {
        echo "Produto: " . $row['nome_produto'] . " - Quantidade: " . $row['quantidade'] . "<br>";
        echo "<a href='reordenar.php?id=" . $row['id'] . "'>Reordenar</a><br><hr>";
    }
} else {
    echo "Nenhum produto com estoque crítico.";
}

$conn->close();
?>
