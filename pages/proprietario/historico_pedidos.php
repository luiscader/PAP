<?php
include 'C:/wamp64/www/PAP/includes/config.php';  // Inclui a conexão à base de dados

// Verificar se o parâmetro de ordenação foi passado na URL
$order_option = isset($_GET['order_option']) ? $_GET['order_option'] : 'data_pedido_desc';  // Data decresncente como padrão

// Definir a ordenação com base na opção selecionada
switch ($order_option) {
    case 'id_asc':
        $order_by = 'id_pedido';
        $order_dir = 'ASC';
        break;
    case 'id_desc':
        $order_by = 'id_pedido';
        $order_dir = 'DESC';
        break;
    case 'data_pedido_asc':
        $order_by = 'data_pedido';
        $order_dir = 'ASC';
        break;
    case 'data_pedido_desc':
        $order_by = 'data_pedido';
        $order_dir = 'DESC';
        break;
    case 'preco_total_asc':
        $order_by = 'preco_total';
        $order_dir = 'ASC';
        break;
    case 'preco_total_desc':
        $order_by = 'preco_total';
        $order_dir = 'DESC';
        break;
    default:
        $order_by = 'data_pedido';
        $order_dir = 'ASC';
}

// Consulta SQL com ordenação
$sql = "SELECT * FROM pedidos_arquivados ORDER BY $order_by $order_dir"; 
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Pedidos</title>
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
        .pedido {
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .pedido:last-child {
            border-bottom: none;
        }
        .total {
            font-weight: bold;
            color: #007BFF;
        }
        .data-hora {
            color: #777;
            font-size: 0.9em;
        }
        .sem-pedidos {
            text-align: center;
            color: #ff0000;
            font-weight: bold;
        }
        .btn-info {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-info:hover {
            background-color: #0056b3;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
        }
        .sorting {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-end; /* Alinha à direita */
            align-items: center; /* Centraliza verticalmente */
        }
        .sorting label {
            font-weight: bold;
            margin-right: 10px;
        }
        select {
            padding: 8px 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #fff;
            transition: border-color 0.3s;
        }
        select:focus {
            border-color: #007BFF;
            outline: none;
        }
        /* Estilização adicional para as opções do select */
        option {
            padding: 10px;
            background-color: #fff;
        }
    </style>

    <script>
        function changeOrder() {
            // Submeter o formulário quando o usuário mudar o critério de ordenação
            document.getElementById("orderForm").submit();
        }
    </script>
</head>
<body>

<div class="container">
    <h1>Histórico de Pedidos</h1>
    
    <!-- Select para organizar os pedidos -->
    <div class="sorting">
        <form id="orderForm" method="GET" action="">
            <label for="order_option">Ordenar por:</label>
            <select name="order_option" id="order_option" onchange="changeOrder()">
                <option value="id_asc" <?php echo ($order_option == 'id_asc') ? 'selected' : ''; ?>>ID Crescente</option>
                <option value="id_desc" <?php echo ($order_option == 'id_desc') ? 'selected' : ''; ?>>ID Decrescente</option>
                <option value="data_pedido_asc" <?php echo ($order_option == 'data_pedido_asc') ? 'selected' : ''; ?>>Data Crescente</option>
                <option value="data_pedido_desc" <?php echo ($order_option == 'data_pedido_desc') ? 'selected' : ''; ?>>Data Decrescente</option>
                <option value="preco_total_asc" <?php echo ($order_option == 'preco_total_asc') ? 'selected' : ''; ?>>Preço Crescente</option>
                <option value="preco_total_desc" <?php echo ($order_option == 'preco_total_desc') ? 'selected' : ''; ?>>Preço Decrescente</option>
            </select>
        </form>
    </div>

    <?php
    // Exibir histórico de pedidos
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Inicializa a variável do total do pedido
            $total_pedido = 0;

            // Consulta para buscar os itens do pedido
            $sql_itens = "
                SELECT 
                    pi.quantidade, 
                    p.preco 
                FROM pedido_itens pi
                JOIN pratos p ON pi.id_prato = p.id
                WHERE pi.id_pedido = ?";
            $stmt_itens = $conn->prepare($sql_itens);
            $stmt_itens->bind_param("i", $row['id_pedido']);
            $stmt_itens->execute();
            $result_itens = $stmt_itens->get_result();

            // Calcula o total do pedido somando o preço dos itens
            if ($result_itens->num_rows > 0) {
                while ($item = $result_itens->fetch_assoc()) {
                    $total_pedido += $item['quantidade'] * $item['preco'];
                }
            }

            echo "<div class='pedido'>";
            echo "<div>";
            echo "<div>Pedido <span class='pedido-id'>#" . $row['id_pedido'] . "</span> - <span class='status'>Status: " . htmlspecialchars($row['status']) . "</span> - <span class='preco_total'>Total: €" . number_format($total_pedido, 2, ',', '.') . "</span></div>";
            echo "<div class='data-hora'>Data: " . date('d/m/Y', strtotime($row['data_pedido'])) . " - Hora: " . date('H:i:s', strtotime($row['data_pedido'])) . "</div>";
            echo "</div>";
            echo "<a href='historico_detalhes.php?id_pedido=" . $row['id_pedido'] . "' class='btn-info'>Info</a>";
            echo "</div>";

            // Fecha a consulta dos itens
            $stmt_itens->close();
        }
    } else {
        echo "<div class='sem-pedidos'>Nenhum pedido registrado.</div>";
    }

    $conn->close();
    ?>
</div>

<div class="footer">
    <p>&copy; 2024 Seu Nome ou Sua Empresa. Todos os direitos reservados.</p>
</div>

</body>
</html>
