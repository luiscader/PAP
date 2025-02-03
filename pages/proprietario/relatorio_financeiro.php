<?php
include 'C:/wamp64/www/PAP/includes/config.php';  // Inclui a conexão à base de dados

// Consultar total de vendas (pedidos)
$sql_vendas = "SELECT SUM(total) AS total_vendas FROM Pedido";
$result_vendas = $conn->query($sql_vendas);
$total_vendas = $result_vendas->fetch_assoc()['total_vendas'];

// Consultar total de despesas (produtos comprados para o estoque)
$sql_despesas = "SELECT SUM(preco * quantidade) AS total_despesas FROM Produto";
$result_despesas = $conn->query($sql_despesas);
$total_despesas = $result_despesas->fetch_assoc()['total_despesas'];

// Calcular lucro líquido
$lucro_liquido = $total_vendas - $total_despesas;

// Exibir resultados
echo "<h1>Relatório Financeiro</h1>";
echo "Total de Vendas: €" . number_format($total_vendas, 2) . "<br>";
echo "Total de Despesas: €" . number_format($total_despesas, 2) . "<br>";
echo "Lucro Líquido: €" . number_format($lucro_liquido, 2) . "<br>";

$conn->close();
?>
