<?php
include 'C:/wamp64/www/PAP/includes/config.php';  // Inclui a conexão à base de dados

// Verifica se o ID do pedido foi passado
if (isset($_GET['id_pedido'])) {
    $id_pedido = $_GET['id_pedido'];

    // Consulta os detalhes do pedido na tabela pedidos_arquivados
    $sql_pedido = "SELECT * FROM pedidos_arquivados WHERE id_pedido = ?";
    $stmt_pedido = $conn->prepare($sql_pedido);
    $stmt_pedido->bind_param("i", $id_pedido);
    $stmt_pedido->execute();
    $result_pedido = $stmt_pedido->get_result();

    if ($result_pedido->num_rows > 0) {
        $pedido = $result_pedido->fetch_assoc();
    } else {
        echo "Pedido não encontrado!";
        exit;
    }

    // Consulta os itens do pedido na tabela pedido_itens
    $sql_itens = "
        SELECT 
            pi.id_prato, 
            p.nome AS nome_prato, 
            pi.quantidade, 
            p.preco AS preco_unitario, 
            (pi.quantidade * p.preco) AS total_item
        FROM pedido_itens pi
        JOIN pratos p ON pi.id_prato = p.id
        WHERE pi.id_pedido = ?";
    $stmt_itens = $conn->prepare($sql_itens);
    $stmt_itens->bind_param("i", $id_pedido);
    $stmt_itens->execute();
    $result_itens = $stmt_itens->get_result();

    $itens = [];
    $total_pedido = 0; // Inicializa o total do pedido
    if ($result_itens->num_rows > 0) {
        while ($row = $result_itens->fetch_assoc()) {
            $itens[] = $row;
            $total_pedido += $row['total_item']; // Soma o total de cada item ao total do pedido
        }
    } else {
        echo "Nenhum item encontrado para este pedido.";
        exit;
    }
} else {
    echo "ID do pedido não fornecido!";
    exit;
}

$stmt_pedido->close();
$stmt_itens->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Pedido</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .pedido-info {
            margin-bottom: 20px;
        }
        .pedido-info p {
            font-size: 1.1em;
            margin: 5px 0;
        }
        .pedido-info .total {
            font-weight: bold;
            color: #007BFF;
        }
        .data-hora {
            color: #777;
            font-size: 0.9em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn-back {
            display: inline-block;
            background-color: #007BFF;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .btn-back:hover {
            background-color: #0056b3;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Detalhes do Pedido #<?php echo $pedido['id_pedido']; ?></h1>

    <!-- Informações gerais do pedido -->
    <div class="pedido-info">
        <p><strong>Data do Pedido:</strong> <?php echo date('d/m/Y H:i:s', strtotime($pedido['data_pedido'])); ?></p>
        <p><strong>Status do Pedido:</strong> <?php echo htmlspecialchars($pedido['status']); ?></p>
    </div>

    <!-- Tabela com os itens do pedido -->
    <h2>Itens do Pedido</h2>
    <table>
        <thead>
            <tr>
                <th>Nome do Prato</th>
                <th>Quantidade</th>
                <th>Preço Unitário (€)</th>
                <th>Total (€)</th>  
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itens as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['nome_prato']); ?></td>
                    <td><?php echo $item['quantidade']; ?></td>
                    <td>€<?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                    <td>€<?php echo number_format($item['total_item'], 2, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><strong>Total do Pedido:</strong> <span class="total">€<?php echo number_format($total_pedido, 2, ',', '.'); ?></span></p>

    <!-- Botão para voltar -->
    <a href="historico_pedidos.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar</a>
</div>

<div class="footer">
    <p>&copy; 2024 Seu Nome ou Sua Empresa. Todos os direitos reservados.</p>
</div>

</body>
</html>
