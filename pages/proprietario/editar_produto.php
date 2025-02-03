<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';  // Certifique-se de que o caminho está correto

// Verifica se o utilizador está logado e se é proprietário
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'proprietario') {
    die('Acesso restrito. Apenas proprietários podem acessar esta página.');
}

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Recuperar o ID do produto que deve ser editado
$id_produto = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recuperar os dados do formulário
    $nome = $conn->real_escape_string($_POST['nome']);
    $descricao = $conn->real_escape_string($_POST['descricao']);
    $quantidade = $conn->real_escape_string($_POST['quantidade']);
    $unidade_medida = $conn->real_escape_string($_POST['unidade_medida']);
    $categoria_id = intval($_POST['categoria_id']);
    $fornecedor_id = intval($_POST['fornecedor_id']);
    
    // Atualizar o produto no banco de dados
    $query = "UPDATE produto SET nome = ?, descricao = ?, quantidade = ?, unidade_medida = ?, id_categoria = ?, id_fornecedor = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdsiii", $nome, $descricao, $quantidade, $unidade_medida, $categoria_id, $fornecedor_id, $id_produto);
    
    if ($stmt->execute()) {
        echo "<p>Produto atualizado com sucesso!</p>";
    } else {
        echo "<p>Erro ao atualizar produto: " . $stmt->error . "</p>";
    }
    
    $stmt->close();
}

// Consultar os dados do produto para preencher o formulário
$query = "SELECT p.id, p.nome, p.descricao, p.quantidade, p.unidade_medida, p.id_categoria AS categoria_id, p.id_fornecedor AS fornecedor_id
          FROM produto p
          WHERE p.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_produto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $produto = $result->fetch_assoc();
} else {
    echo "<p>Produto não encontrado.</p>";
    exit;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto</title>
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        input[type="text"], input[type="number"], textarea, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .button-container a {
            text-decoration: none;
            color: white;
            background-color: #28a745;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            margin-top: 20px;
        }

        .button-container a:hover {
            background-color: #218838;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }

        h1 {
            color: #333;
        }
    </style>
</head>
<body>
    <h1>Editar Produto</h1>
    <div class="form-container">
        <form action="" method="post">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($produto['nome']); ?>" required><br>

            <label for="descricao">Descrição:</label>
            <textarea id="descricao" name="descricao" required><?php echo htmlspecialchars($produto['descricao']); ?></textarea><br>

            <label for="quantidade">Quantidade:</label>
            <input type="number" id="quantidade" name="quantidade" step="0.01" value="<?php echo htmlspecialchars($produto['quantidade']); ?>" required><br>

            <label for="unidade_medida">Unidade de Medida:</label>
            <select id="unidade_medida" name="unidade_medida" required>
                <option value="Kg" <?php echo $produto['unidade_medida'] === 'Kg' ? 'selected' : ''; ?>>Kg</option>
                <option value="Gr" <?php echo $produto['unidade_medida'] === 'Gr' ? 'selected' : ''; ?>>Gr</option>
                <option value="L" <?php echo $produto['unidade_medida'] === 'L' ? 'selected' : ''; ?>>L</option>
                <option value="Ml" <?php echo $produto['unidade_medida'] === 'Ml' ? 'selected' : ''; ?>>Ml</option>
                <option value="Unidade" <?php echo $produto['unidade_medida'] === 'Unidade' ? 'selected' : ''; ?>>Unidade</option>
            </select><br>

            <label for="categoria_id">Categoria:</label>
            <select id="categoria_id" name="categoria_id" required>
                <?php
                // Preencher as categorias
                $query = "SELECT id, nome FROM categoria";
                $result = $conn->query($query);
                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . $row['id'] . '"' . ($row['id'] == $produto['categoria_id'] ? ' selected' : '') . '>' . htmlspecialchars($row['nome']) . '</option>';
                }
                ?>
            </select><br>

            <label for="fornecedor_id">Fornecedor:</label>
            <select id="fornecedor_id" name="fornecedor_id" required>
                <?php
                // Preencher os fornecedores
                $query = "SELECT id, nome_representante FROM fornecedor";
                $result = $conn->query($query);
                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . $row['id'] . '"' . ($row['id'] == $produto['fornecedor_id'] ? ' selected' : '') . '>' . htmlspecialchars($row['nome_representante']) . '</option>';
                }
                ?>
            </select><br>

            <input type="submit" value="Salvar">
        </form>
    </div>
    <div class="button-container">
        <a href="gestao_produtos.php">Voltar à Gestão de Produtos</a>
    </div>
</body>
</html>
