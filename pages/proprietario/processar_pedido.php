<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';  // Certifique-se de que o caminho está correto

// Verifica se o utilizador está logado
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'empregado') {
    die('Acesso restrito. Apenas empregados podem processar pedidos.');
}

// Obtém o ID do restaurante e o ID do empregado
$id_restaurante = $_SESSION['id_restaurante'];
$id_empregado = $_SESSION['id'];

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Processa o pedido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_mesa = $_POST['id_mesa'];
    $itens_pedido = $_POST['itens'];  // Array com ID do prato e quantidade

    // Inicia uma transação
    $conn->begin_transaction();

    try {
        // Insere o pedido
        $sql_pedido = "INSERT INTO pedido (id_restaurante, id_mesa, id_empregado, data_pedido, preco_total) VALUES (?, ?, ?, NOW(), ?)";
        $stmt_pedido = $conn->prepare($sql_pedido);
        
        // Calcula o preço total do pedido
        $preco_total = 0;
        foreach ($itens_pedido as $item) {
            $id_prato = $item['id_prato'];
            $quantidade = $item['quantidade'];

            // Obtem o preço do prato
            $sql_preco_prato = "SELECT preco FROM pratos WHERE id = ?";
            $stmt_preco_prato = $conn->prepare($sql_preco_prato);
            $stmt_preco_prato->bind_param("i", $id_prato);
            $stmt_preco_prato->execute();
            $stmt_preco_prato->bind_result($preco_prato);
            $stmt_preco_prato->fetch();
            $stmt_preco_prato->close();

            $preco_total += $preco_prato * $quantidade;

            // Verifica a disponibilidade de ingredientes
            $sql_ingredientes = "SELECT id_produto, quantidade_necessaria FROM ingrediente_prato WHERE id_prato = ?";
            $stmt_ingredientes = $conn->prepare($sql_ingredientes);
            $stmt_ingredientes->bind_param("i", $id_prato);
            $stmt_ingredientes->execute();
            $result_ingredientes = $stmt_ingredientes->get_result();

            while ($row = $result_ingredientes->fetch_assoc()) {
                $id_produto = $row['id_produto'];
                $quantidade_necessaria = $row['quantidade_necessaria'] * $quantidade;

                // Verifica o estoque do produto
                $sql_estoque = "SELECT quantidade FROM produto WHERE id = ?";
                $stmt_estoque = $conn->prepare($sql_estoque);
                $stmt_estoque->bind_param("i", $id_produto);
                $stmt_estoque->execute();
                $stmt_estoque->bind_result($quantidade_estoque);
                $stmt_estoque->fetch();
                $stmt_estoque->close();

                if ($quantidade_estoque < $quantidade_necessaria) {
                    throw new Exception("Estoque insuficiente para o produto ID $id_produto.");
                }
            }
            $stmt_ingredientes->close();
        }

        $stmt_pedido->bind_param("ii", $id_restaurante, $id_mesa, $id_empregado, $preco_total);
        $stmt_pedido->execute();
        $id_pedido = $stmt_pedido->insert_id;

        // Atualiza o estoque dos produtos
        foreach ($itens_pedido as $item) {
            $id_prato = $item['id_prato'];
            $quantidade = $item['quantidade'];

            // Atualiza o estoque dos ingredientes do prato
            $sql_ingredientes = "SELECT id_produto, quantidade_necessaria FROM ingrediente_prato WHERE id_prato = ?";
            $stmt_ingredientes = $conn->prepare($sql_ingredientes);
            $stmt_ingredientes->bind_param("i", $id_prato);
            $stmt_ingredientes->execute();
            $result_ingredientes = $stmt_ingredientes->get_result();

            while ($row = $result_ingredientes->fetch_assoc()) {
                $id_produto = $row['id_produto'];
                $quantidade_necessaria = $row['quantidade_necessaria'] * $quantidade;

                // Atualiza o estoque do produto
                $sql_update_produto = "UPDATE produto SET quantidade = quantidade - ? WHERE id = ?";
                $stmt_update_produto = $conn->prepare($sql_update_produto);
                $stmt_update_produto->bind_param("di", $quantidade_necessaria, $id_produto);
                $stmt_update_produto->execute();
                $stmt_update_produto->close();
            }
            $stmt_ingredientes->close();
        }

        // Commit da transação
        $conn->commit();
        echo "Pedido processado com sucesso!";
    } catch (Exception $e) {
        // Rollback da transação em caso de erro
        $conn->rollback();
        echo "Erro ao processar o pedido: " . $e->getMessage();
    }

    $stmt_pedido->close();
}

$sql_mesas = "SELECT id, numero_mesa FROM mesa WHERE id_restaurante = ?";
$stmt_mesas = $conn->prepare($sql_mesas);
$stmt_mesas->bind_param("i", $id_restaurante);
$stmt_mesas->execute();
$result_mesas = $stmt_mesas->get_result();
$mesas = [];
while ($row = $result_mesas->fetch_assoc()) {
    $mesas[] = $row;
}
$stmt_mesas->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processar Pedido</title>
    <style>
        form {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
        }

        label, select, input, textarea {
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }

        input[type="number"], textarea {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        input[type="submit"] {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Processar Novo Pedido</h1>

    <form method="post" action="">
        <label for="id_mesa">Mesa:</label>
        <select id="id_mesa" name="id_mesa" required>
            <?php foreach ($mesas as $mesa): ?>
                <option value="<?php echo $mesa['id']; ?>">
                    <?php echo "Mesa " . $mesa['numero_mesa']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="itens">Itens do Pedido:</label>
        <div id="itens">
            <div class="item">
                <label for="prato">Prato:</label>
                <select name="itens[0][id_prato]" required>
                    <!-- Substitua os valores pelo ID e Nome dos pratos disponíveis -->
                    <?php foreach ($pratos as $prato): ?>
                        <option value="<?php echo $prato['id']; ?>">
                            <?php echo $prato['nome']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="quantidade">Quantidade:</label>
                <input type="number" name="itens[0][quantidade]" placeholder="Quantidade" step="1" required>
            </div>
        </div>

        <button type="button" onclick="adicionarItem()">Adicionar Item</button><br><br>

        <input type="submit" value="Processar Pedido">
    </form>

    <script>
        let itemIndex = 1;

        function adicionarItem() {
            const div = document.getElementById('itens');
            const itemDiv = document.createElement('div');
            itemDiv.className = 'item';
            itemDiv.innerHTML = `
                <label for="prato">Prato:</label>
                <select name="itens[${itemIndex}][id_prato]" required>
                    <?php foreach ($pratos as $prato): ?>
                        <option value="<?php echo $prato['id']; ?>">
                            <?php echo $prato['nome']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="quantidade">Quantidade:</label>
                <input type="number" name="itens[${itemIndex}][quantidade]" placeholder="Quantidade" step="1" required>
            `;
            div.appendChild(itemDiv);
            itemIndex++;
        }
    </script>
</body>
</html>
