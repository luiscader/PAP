<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';  // Certifique-se de que o caminho está correto

// Verifica se o proprietário está logado e o ID do restaurante está definido na sessão
if (!isset($_SESSION['id']) || !isset($_SESSION['id_restaurante'])) {
    die('Por favor, faça login como proprietário e selecione um restaurante.');
}

$id_restaurante = $_SESSION['id_restaurante'];

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Processar o pedido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pratos_selecionados = $_POST['pratos']; // Pratos selecionados (array de IDs)
    $quantidades = $_POST['quantidade'];     // Quantidade de cada prato selecionado (array)

    foreach ($pratos_selecionados as $index => $id_prato) {
        $quantidade = $quantidades[$index];

        // Inserir o prato no pedido
        $stmt = $conn->prepare("INSERT INTO pedidos (id_restaurante, id_prato, quantidade) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $id_restaurante, $id_prato, $quantidade);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Pedido realizado com sucesso!</div>";
        } else {
            echo "<div class='alert alert-danger'>Erro ao realizar pedido: " . $stmt->error . "</div>";
        }

        $stmt->close();
    }
}

// Recuperar todos os pratos do restaurante
$query = $conn->prepare("SELECT * FROM pratos WHERE id_restaurante = ?");
$query->bind_param("i", $id_restaurante);
$query->execute();
$result = $query->get_result();
$pratos = $result->fetch_all(MYSQLI_ASSOC);
$query->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Pedido</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        h1 {
            text-align: center;
            margin-bottom: 40px;
            color: #343a40;
        }
        .card {
            margin-bottom: 20px;
        }
        .card-body {
            padding: 15px;
        }
        .card-title {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Faça seu Pedido</h1>

    <!-- Formulário para selecionar pratos -->
    <form method="POST">
        <div class="row">
            <?php foreach ($pratos as $prato): ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($prato['nome']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($prato['descricao']); ?></p>
                            <p><strong>Preço:</strong> €<?php echo number_format($prato['preco'], 2, ',', '.'); ?></p>

                            <div class="form-group">
                                <label for="quantidade">Quantidade:</label>
                                <input type="number" name="quantidade[]" class="form-control" min="1" value="1" required>
                            </div>
                            <input type="checkbox" name="pratos[]" value="<?php echo $prato['id']; ?>"> Selecionar Prato
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-primary btn-block mt-4">Finalizar Pedido</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
