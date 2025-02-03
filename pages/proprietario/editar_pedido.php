<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';

// Verifica se o usuário é um proprietário
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'proprietario') {
    die('Acesso restrito. Apenas proprietários podem acessar esta página.');
}

$id_restaurante = $_SESSION['id_restaurante'];
$id_pedido = isset($_GET['id_pedido']) ? intval($_GET['id_pedido']) : 0;

// Conectar ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Atualiza a quantidade de um prato ou remove se a quantidade for zero
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_prato = isset($_POST['id_prato']) ? intval($_POST['id_prato']) : 0;

    if (isset($_POST['update_prato'])) {
        $nova_quantidade = intval($_POST['nova_quantidade']);

        if ($nova_quantidade > 0) {
            // Atualiza a quantidade de um prato no pedido_itens
            $sql_update_quantidade = "UPDATE pedido_itens SET quantidade = ? WHERE id_pedido = ? AND id_prato = ?";
            $stmt_update_quantidade = $conn->prepare($sql_update_quantidade);
            $stmt_update_quantidade->bind_param("iii", $nova_quantidade, $id_pedido, $id_prato);
            $stmt_update_quantidade->execute();
            $stmt_update_quantidade->close();
        } else {
            // Remove o prato se a quantidade for zero
            $sql_remove_prato = "DELETE FROM pedido_itens WHERE id_pedido = ? AND id_prato = ?";
            $stmt_remove_prato = $conn->prepare($sql_remove_prato);
            $stmt_remove_prato->bind_param("ii", $id_pedido, $id_prato);
            $stmt_remove_prato->execute();
            $stmt_remove_prato->close();
        }
    }

    // Remove um prato manualmente
    if (isset($_POST['remove_prato'])) {
        // Remove o prato do pedido_itens
        $sql_remove_prato = "DELETE FROM pedido_itens WHERE id_pedido = ? AND id_prato = ?";
        $stmt_remove_prato = $conn->prepare($sql_remove_prato);
        $stmt_remove_prato->bind_param("ii", $id_pedido, $id_prato);
        $stmt_remove_prato->execute();
        $stmt_remove_prato->close();
    }

    // Processa a adição de um novo prato ao pedido
    if (isset($_POST['add_prato'])) {
        $quantidade = intval($_POST['quantidade']);
        // Recupera o preço do prato
        $sql_preco = "SELECT preco FROM pratos WHERE id = ?";
        $stmt_preco = $conn->prepare($sql_preco);
        $stmt_preco->bind_param("i", $id_prato);
        $stmt_preco->execute();
        $result_preco = $stmt_preco->get_result();

        if ($result_preco->num_rows > 0) {
            $row_preco = $result_preco->fetch_assoc();
            $preco_unitario = $row_preco['preco'];
            $preco_total = $preco_unitario * $quantidade; // Cálculo do preço total

            // Verifica se o prato já está no pedido
            $sql_check = "SELECT quantidade FROM pedido_itens WHERE id_pedido = ? AND id_prato = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("ii", $id_pedido, $id_prato);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                // Atualizar a quantidade se o prato já estiver no pedido
                $row = $result_check->fetch_assoc();
                $nova_quantidade = $row['quantidade'] + $quantidade;
                $sql_update = "UPDATE pedido_itens SET quantidade = ?, preco_total = ? WHERE id_pedido = ? AND id_prato = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("idii", $nova_quantidade, $preco_total, $id_pedido, $id_prato);
                $stmt_update->execute();
                $stmt_update->close();
            } else {
                // Inserir um novo prato no pedido_itens
                $sql_insert = "INSERT INTO pedido_itens (id_pedido, id_prato, quantidade, id_restaurante, preco_total) VALUES (?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("iiidd", $id_pedido, $id_prato, $quantidade, $id_restaurante, $preco_total);
                $stmt_insert->execute();
                $stmt_insert->close();
            }

            $stmt_check->close();
        } else {
            echo "Prato não encontrado.";
        }

        $stmt_preco->close();
    }
}

// Consulta os pratos atuais do pedido usando pedido_itens
$sql = "SELECT pr.id, pr.nome, pi.quantidade, pr.preco
        FROM pedido_itens pi
        JOIN pratos pr ON pi.id_prato = pr.id
        WHERE pi.id_pedido = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_pedido);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pedido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1, h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        td {
            background-color: #f9f9f9;
        }
        tr:hover td {
            background-color: #f1f1f1;
        }
        .btn {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-transform: uppercase;
            display: inline-block;
            margin: 10px 0;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #218838;
        }
        .btn-remove {
            background-color: #dc3545;
        }
        .btn-remove:hover {
            background-color: #c82333;
        }
        .pratos-disponiveis {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .prato-item {
            background-color: #fff;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .prato-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        .prato-item h3 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }
        .prato-item p {
            color: #666;
            margin: 5px 0 15px 0;
            font-size: 16px;
        }
        .form-adicionar-prato {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .quantity-control {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        .quantity-control button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .quantity-control button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Editar Pedido</h1>

        <table>
            <thead>
                <tr>
                    <th>Prato</th>
                    <th>Quantidade</th>
                    <th>Preço Unitário</th>
                    <th>Preço Total</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): 
                    $preco_total = $row['preco'] * $row['quantidade']; // Cálculo do preço total
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nome']); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id_prato" value="<?php echo $row['id']; ?>">
                                <div class="quantity-control">
                                    <button type="button" onclick="updateQuantity(<?php echo $row['id']; ?>, -1)">-</button>
                                    <input type="number" name="nova_quantidade" value="<?php echo $row['quantidade']; ?>" min="0" style="width: 60px;" id="quantidade_<?php echo $row['id']; ?>">
                                    <button type="button" onclick="updateQuantity(<?php echo $row['id']; ?>, 1)">+</button>
                                    <button type="submit" name="update_prato" class="btn">Atualizar</button>
                                </div>
                            </form>
                        </td>
                        <td><?php echo number_format($row['preco'], 2, ',', '.'); ?> €</td>
                        <td><?php echo number_format($preco_total, 2, ',', '.'); ?> €</td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id_prato" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="remove_prato" class="btn btn-remove">Remover</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h2>Adicionar Prato</h2>
        <div class="pratos-disponiveis">
            <?php
            // Consulta os pratos disponíveis
            $sql_pratos = "SELECT id, nome, preco FROM pratos";
            $result_pratos = $conn->query($sql_pratos);
            while ($row_prato = $result_pratos->fetch_assoc()): ?>
                <div class="prato-item">
                    <h3><?php echo htmlspecialchars($row_prato['nome']); ?></h3>
                    <p>Preço: <?php echo number_format($row_prato['preco'], 2, ',', '.'); ?> €</p>
                    <form method="POST" class="form-adicionar-prato">
                        <input type="hidden" name="id_prato" value="<?php echo $row_prato['id']; ?>">
                        <div class="quantity-control">
                            <button type="button" onclick="updateAddQuantity(<?php echo $row_prato['id']; ?>, -1)">-</button>
                            <input type="number" name="quantidade" value="1" min="1" max="99" style="width: 60px;" id="add_quantidade_<?php echo $row_prato['id']; ?>">
                            <button type="button" onclick="updateAddQuantity(<?php echo $row_prato['id']; ?>, 1)">+</button>
                        </div>
                        <button type="submit" name="add_prato" class="btn">Adicionar</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        function updateQuantity(id, delta) {
            const quantityInput = document.getElementById('quantidade_' + id);
            let currentQuantity = parseInt(quantityInput.value) || 0;

            currentQuantity += delta;

            // Garante que a quantidade não seja menor que 0
            if (currentQuantity < 0) {
                currentQuantity = 0;
            }

            quantityInput.value = currentQuantity;
        }

        function updateAddQuantity(id, delta) {
            const quantityInput = document.getElementById('add_quantidade_' + id);
            let currentQuantity = parseInt(quantityInput.value) || 1;

            currentQuantity += delta;

            // Garante que a quantidade não seja menor que 1
            if (currentQuantity < 1) {
                currentQuantity = 1;
            }

            quantityInput.value = currentQuantity;
        }
    </script>

    <?php
    // Fecha a conexão
    $conn->close();
    ?>
</body>
</html>
