<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';  // Verifique se o caminho está correto

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

// Variável para armazenar pratos disponíveis
$pratos = [];

// Consulta para obter todos os pratos disponíveis na tabela 'pratos'
$sql_pratos = "SELECT id, nome, preco FROM pratos WHERE id_restaurante = ?";
$stmt_pratos = $conn->prepare($sql_pratos);
$stmt_pratos->bind_param("i", $id_restaurante);
$stmt_pratos->execute();
$result_pratos = $stmt_pratos->get_result();

// Preenche a lista de pratos
if ($result_pratos->num_rows > 0) {
    while ($row = $result_pratos->fetch_assoc()) {
        $pratos[] = $row;
    }
} else {
    echo "<p>Nenhum prato encontrado para o restaurante com ID: $id_restaurante.</p>";
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_mesa = $_POST['id_mesa']; // ID da mesa
    $pratosSelecionados = isset($_POST['prato']) ? $_POST['prato'] : [];
    $quantidades = $_POST['quantidade']; // Quantidade dos pratos

    // Verifica se ao menos um prato foi selecionado
    $pedidoCriado = false;
    $totalPedido = 0; // Para calcular o preço total do pedido
    $id_pedido = null; // Inicializa a variável para o id do pedido

    // Insere o pedido no banco de dados
    foreach ($quantidades as $id_prato => $quantidade) {
        if (isset($pratosSelecionados[$id_prato]) && !empty($quantidade) && $quantidade > 0) {
            // Obtém o preço do prato
            $sql_preco = "SELECT preco FROM pratos WHERE id = ?";
            $stmt_preco = $conn->prepare($sql_preco);
            $stmt_preco->bind_param("i", $id_prato);
            $stmt_preco->execute();
            $result_preco = $stmt_preco->get_result();
            
            if ($row_preco = $result_preco->fetch_assoc()) {
                $preco = $row_preco['preco'];
                $preco_total = $quantidade * $preco; // Cálculo do preço total do prato
                $totalPedido += $preco_total; // Adiciona ao total do pedido

                // Se o pedido ainda não foi criado, insere o pedido
                if (!$pedidoCriado) {
                    $sql_pedido = "INSERT INTO pedidos (id_restaurante, id_mesa, data_pedido, preco_total) VALUES (?, ?, NOW(), ?)";
                    $stmt_pedido = $conn->prepare($sql_pedido);
                    $stmt_pedido->bind_param("iid", $id_restaurante, $id_mesa, $totalPedido);

                    if ($stmt_pedido->execute()) {
                        $id_pedido = $stmt_pedido->insert_id; // Obtém o id do pedido criado
                        $pedidoCriado = true; // Marca que o pedido foi criado
                    } else {
                        echo "<p>Erro ao criar o pedido: " . $stmt_pedido->error . "</p>";
                    }
                }

                // Insere a relação entre o pedido e os pratos
                if ($pedidoCriado && $id_pedido) {
                    $sql_item = "INSERT INTO pedido_itens (id_pedido, id_prato, id_restaurante, quantidade, preco_total) VALUES (?, ?, ?, ?, ?)";
                    $stmt_item = $conn->prepare($sql_item);
                    $stmt_item->bind_param("iiidd", $id_pedido, $id_prato, $id_restaurante, $quantidade, $preco_total); // Adiciona id_restaurante

                    if (!$stmt_item->execute()) {
                        echo "<p>Erro ao adicionar o prato ID $id_prato ao pedido: " . $stmt_item->error . "</p>";
                    }
                }
            } else {
                echo "<p>Erro ao obter o preço para o prato ID $id_prato.</p>";
            }

            $stmt_preco->close();
        }
    }

    echo "<p>Pedido criado com sucesso!</p>";
    header("Location: gestao_pedidos.php"); // Redireciona para a página de gestão de pedidos
    exit;
}
?>


<!-- Formulário HTML para criar pedido -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Pedido</title>
    <style>
        /* CSS para estilizar a página */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
        }

        .container {
            width: 80%;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .prato-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin: 10px;
            text-align: center;
            background-color: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .prato-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .prato-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }

        label {
            margin-top: 10px;
            display: block;
        }

        input[type="number"], select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 20px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            background-color: #3498db;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }

        button:hover {
            background-color: #2980b9;
        }
    </style>
    <script>
        function validateForm() {
            const pratoCards = document.querySelectorAll('.prato-card');
            let isValid = true;

            pratoCards.forEach(card => {
                const checkbox = card.querySelector('input[type="checkbox"]');
                const quantidadeInput = card.querySelector('input[type="number"]');

                if (checkbox.checked && (!quantidadeInput.value || quantidadeInput.value <= 0)) {
                    alert('Por favor, insira uma quantidade válida para o prato selecionado.');
                    isValid = false;
                }
            });

            return isValid;
        }

        function toggleQuantidadeInput(checkbox) {
            const quantityInput = checkbox.parentElement.querySelector('input[type="number"]');
            if (checkbox.checked) {
                quantityInput.disabled = false; // Habilita o input de quantidade
                quantityInput.focus(); // Foca no input
            } else {
                quantityInput.disabled = true; // Desabilita o input de quantidade
                quantityInput.value = ''; // Limpa o valor
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Criar Pedido</h1>

        <form action="criar_pedido.php" method="POST" onsubmit="return validateForm()">
            <label for="id_mesa">Número da Mesa:</label>
            <input type="number" name="id_mesa" required>

            <div class="prato-list">
                <?php if (count($pratos) > 0): ?>
                    <?php foreach ($pratos as $prato): ?>
                        <div class="prato-card">
                            <h3><?php echo htmlspecialchars($prato['nome']); ?></h3>
                            <p>Preço: €<?php echo number_format($prato['preco'], 2, ',', ' '); ?></p>
                            <input type="checkbox" name="prato[<?php echo $prato['id']; ?>]" onchange="toggleQuantidadeInput(this)">
                            <label for="quantidade">Quantidade:</label>
                            <input type="number" name="quantidade[<?php echo $prato['id']; ?>]" min="1" placeholder="Quantidade" disabled>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nenhum prato disponível.</p>
                <?php endif; ?>
            </div>

            <button type="submit">Finalizar Pedido</button>
        </form>
    </div>
</body>
</html>
