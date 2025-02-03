<?php
include 'C:/wamp64/www/PAP/includes/config.php';  // Inclui a conexão à base de dados

// Consultar pratos mais vendidos
$sql_pratos = "SELECT nome_produto, COUNT(*) AS total_vendido FROM PedidoDetalhe 
               INNER JOIN Produto ON PedidoDetalhe.id_produto = Produto.id 
               GROUP BY id_produto ORDER BY total_vendido DESC LIMIT 5";
$result_pratos = $conn->query($sql_pratos);

// Exibir estatísticas de pratos
if ($result_pratos->num_rows > 0) {
    echo "<h1>Pratos Mais Vendidos</h1>";
    while ($row = $result_pratos->fetch_assoc()) {
        echo "Prato: " . $row['nome_produto'] . " - Vendido: " . $row['total_vendido'] . "<br>";
    }
} else {
    echo "Nenhum dado disponível.";
}

$conn->close();
?>
