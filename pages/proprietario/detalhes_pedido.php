<?php
include 'C:/wamp64/www/PAP/includes/config.php';

$id_restaurante = $_GET['id'];

// Consultar dados do restaurante
$sql_restaurante = "SELECT * FROM restaurante WHERE id = ?";
$stmt_restaurante = $conn->prepare($sql_restaurante);
$stmt_restaurante->bind_param("i", $id_restaurante);
$stmt_restaurante->execute();
$result_restaurante = $stmt_restaurante->get_result();
$row_restaurante = $result_restaurante->fetch_assoc();

// Consultar imagens do restaurante
$sql_imagens = "SELECT * FROM imagem_restaurante WHERE id_restaurante = ?";
$stmt_imagens = $conn->prepare($sql_imagens);
$stmt_imagens->bind_param("i", $id_restaurante);
$stmt_imagens->execute();
$result_imagens = $stmt_imagens->get_result();

// Consultar ementa do restaurante
$sql_ementa = "SELECT * FROM pratos WHERE id_restaurante = ?";
$stmt_ementa = $conn->prepare($sql_ementa);
$stmt_ementa->bind_param("i", $id_restaurante);
$stmt_ementa->execute();
$result_ementa = $stmt_ementa->get_result();

// Consultar avaliações médias por critério
$sql_avaliacoes = "
    SELECT 
        AVG(comida) as media_comida, 
        AVG(servico) as media_servico, 
        AVG(valor) as media_valor, 
        AVG(ambiente) as media_ambiente
    FROM avaliacoes 
    WHERE id_restaurante = ?";
$stmt_avaliacoes = $conn->prepare($sql_avaliacoes);
$stmt_avaliacoes->bind_param("i", $id_restaurante);
$stmt_avaliacoes->execute();
$result_avaliacoes = $stmt_avaliacoes->get_result();
$row_avaliacoes = $result_avaliacoes->fetch_assoc();

// Consultar tipos de gastronomia
$sql_gastronomia = "SELECT nome FROM categoria WHERE id_restaurante = ?";
$stmt_gastronomia = $conn->prepare($sql_gastronomia);
$stmt_gastronomia->bind_param("i", $id_restaurante);
$stmt_gastronomia->execute();
$result_gastronomia = $stmt_gastronomia->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Pedido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
            padding: 20px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn-back:hover {
            background-color: #0056b3;
        }
        .total-valor {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Detalhes do Pedido - ID Pedido <?php echo $id_pedido; ?></h1>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID do Pedido</th>
                        <th>Prato</th>
                        <th>Quantidade</th>
                        <th>Preço Unitário (€)</th>
                        <th>Total (€)</th>
                        <th>Data do Pedido</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): 
                        // Calcula o total por prato (quantidade * preço)
                        $total_prato = $row['quantidade'] * $row['preco'];
                        // Soma o total ao valor final do pedido
                        $total_pedido += $total_prato;
                    ?>
                        <tr>
                            <td><?php echo $row['id_pedido']; ?></td>
                            <td><?php echo $row['nome_prato']; ?></td>
                            <td><?php echo $row['quantidade']; ?></td>
                            <td><?php echo number_format($row['preco'], 2, ',', ' '); ?> €</td>
                            <td><?php echo number_format($total_prato, 2, ',', ' '); ?> €</td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['data_pedido'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Exibe o total geral do pedido -->
            <div class="total-valor">
                Total do Pedido: <?php echo number_format($total_pedido, 2, ',', ' '); ?> €
            </div>

        <?php else: ?>
            <p class="no-data">Nenhum prato encontrado para este pedido.</p>
        <?php endif; ?>

        <a href="gestao_pedidos.php" class="btn-back">Voltar</a>

        <?php
        $stmt->close();
        $conn->close();
        ?>
    </div>
</body>
</html>
