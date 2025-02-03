<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';  // Certifique-se de que o caminho está correto

// Verifica se o utilizador está logado e se é proprietário
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'proprietario') {
    die('Acesso restrito. Apenas proprietários podem acessar esta página.');
}

$id_restaurante = $_SESSION['id_restaurante'];

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Processa a atualização de status se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id_pedido = isset($_POST['id_pedido']) ? intval($_POST['id_pedido']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    // Verifica se o status é válido antes de atualizar
    $status_permitidos = ['Pendente', 'Em Preparação', 'Pronto', 'Entregue', 'Pago', 'Cancelado'];
    if (in_array($status, $status_permitidos)) {
        // Atualiza o status do pedido
        $sql_update = "UPDATE pedidos SET status = ? WHERE id_pedido = ? AND id_restaurante = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sii", $status, $id_pedido, $id_restaurante);
        $stmt_update->execute();

        if ($stmt_update->affected_rows > 0) {
            // Se o status for 'Pago' ou 'Cancelado', arquiva o pedido
            if ($status == 'Pago' || $status == 'Cancelado') {
                // 1. Copia o pedido para a tabela de arquivados
                $sql_archive = "INSERT INTO pedidos_arquivados SELECT * FROM pedidos WHERE id_pedido = ? AND id_restaurante = ?";
                $stmt_archive = $conn->prepare($sql_archive);
                $stmt_archive->bind_param("ii", $id_pedido, $id_restaurante);
                $stmt_archive->execute();
                $stmt_archive->close();

                // 2. Remove o pedido da tabela original
                $sql_delete = "DELETE FROM pedidos WHERE id_pedido = ? AND id_restaurante = ?";
                $stmt_delete = $conn->prepare($sql_delete);
                $stmt_delete->bind_param("ii", $id_pedido, $id_restaurante);
                $stmt_delete->execute();
                $stmt_delete->close();

                $msg = "Pedido arquivado com sucesso.";
            } else {
                $msg = "Status atualizado com sucesso.";
            }
        } else {
            $msg = "Erro ao atualizar o status.";
        }

        $stmt_update->close();
    } else {
        $msg = "Status inválido.";
    }
}

// Consulta para obter os totais dos pedidos agrupados por id_pedido
$sql = "SELECT pe.id_pedido, 
       pe.id_mesa, 
       pe.status,
       SUM(pi.quantidade) AS total_pratos,
       SUM(pi.quantidade * p.preco) AS total_pedido
        FROM pedidos pe
        JOIN pedido_itens pi ON pe.id_pedido = pi.id_pedido
        JOIN pratos p ON pi.id_prato = p.id  -- Junta com a tabela de pratos para obter o preço unitário
        WHERE pe.id_restaurante = ?
        GROUP BY pe.id_pedido, pe.id_mesa, pe.status";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_restaurante);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Pedidos</title>
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
        .btn-info {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-info:hover {
            background-color: #0056b3;
        }
        .btn-create {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            display: block;
            width: 200px;
            margin: 20px auto;
            text-align: center;
        }
        .btn-create:hover {
            background-color: #218838;
        }
        .status-form {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }
        .status-form select {
            margin-right: 10px;
        }
        .message {
            text-align: center;
            color: green;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestão de Pedidos</h1>

        <!-- Mensagem de feedback -->
        <?php if (isset($msg)): ?>
            <p class="message"><?php echo $msg; ?></p>
        <?php endif; ?>

        <!-- Botão para criar um novo pedido -->
        <a href="criar_pedido.php" class="btn-create">Criar Pedido</a>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Número da Mesa</th>
                        <th>Total de Pratos</th>
                        <th>Total do Pedido</th>
                        <th>Status</th>
                        <th>Detalhes</th>
                        <th>Ação</th>
                        <th>Alterar Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id_mesa']; ?></td>  
                            <td><?php echo number_format($row['total_pratos'], 0, ',', ' '); ?></td>
                            <td>€<?php echo number_format($row['total_pedido'], 2, ',', ' '); ?></td>
                            <td><?php echo $row['status']; ?></td>
                            <td>
                                <a href="detalhes_pedido.php?id_pedido=<?php echo $row['id_pedido']; ?>" class="btn-info">+ Info</a>
                            </td>
                            <td>
                                <a href="editar_pedido.php?id_pedido=<?php echo $row['id_pedido']; ?>" class="btn-info">Editar Pratos</a>
                            </td>
                            <td>
                                <form class="status-form" method="POST" action="">
                                    <input type="hidden" name="id_pedido" value="<?php echo $row['id_pedido']; ?>">
                                    <select name="status" required>
                                        <option value="Pendente" <?php echo ($row['status'] == 'Pendente') ? 'selected' : ''; ?>>Pendente</option>
                                        <option value="Em Preparação" <?php echo ($row['status'] == 'Em Preparação') ? 'selected' : ''; ?>>Em Preparação</option>
                                        <option value="Pronto" <?php echo ($row['status'] == 'Pronto') ? 'selected' : ''; ?>>Pronto</option>
                                        <option value="Entregue" <?php echo ($row['status'] == 'Entregue') ? 'selected' : ''; ?>>Entregue</option>
                                        <option value="Pago" <?php echo ($row['status'] == 'Pago') ? 'selected' : ''; ?>>Pago</option>
                                        <option value="Cancelado" <?php echo ($row['status'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-info">Atualizar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">Nenhum pedido encontrado.</p>
        <?php endif; ?>

    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
