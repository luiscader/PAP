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

// Processa a criação de um novo produto
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $quantidade = $_POST['quantidade'];
    $unidade_medida = $_POST['unidade_medida'];
    $id_categoria = $_POST['id_categoria'];
    $id_restaurante = $_SESSION['id_restaurante'];  // Assumindo que o id_restaurante está na sessão
    $id_fornecedor = $_POST['id_fornecedor'];

    $sql = "INSERT INTO produto (nome, descricao, quantidade, unidade_medida, id_categoria, id_restaurante, id_fornecedor, data_criacao) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssdsiii", $nome, $descricao, $quantidade, $unidade_medida, $id_categoria, $id_restaurante, $id_fornecedor);
        if ($stmt->execute()) {
            echo "Produto criado com sucesso!";
        } else {
            echo "Erro ao criar produto: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Erro ao preparar a consulta: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Produto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        form {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
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
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Criar Novo Produto</h1>
    <form method="post" action="">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="descricao">Descrição:</label>
        <textarea id="descricao" name="descricao"></textarea>

        <label for="quantidade">Quantidade:</label>
        <input type="number" id="quantidade" name="quantidade" step="0.01" required>

        <label for="unidade_medida">Unidade de Medida:</label>
        <select id="unidade_medida" name="unidade_medida" required>
            <option value="Kg">Kg</option>
            <option value="g">Gr</option>
            <option value="L">L</option>
            <option value="ml">Ml</option>
            <option value="unidade">Unidade</option>
        </select>

        <label for="id_categoria">Categoria:</label>
        <select id="id_categoria" name="id_categoria" required>
            <?php
            // Conecta ao banco de dados novamente para obter categorias
            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Falha na conexão: " . $conn->connect_error);
            }
            $sql = "SELECT id, nome FROM categoria WHERE id_restaurante = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $_SESSION['id_restaurante']);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['nome']) . "</option>";
            }
            $stmt->close();
            $conn->close();
            ?>
        </select>

        <label for="id_fornecedor">Fornecedor:</label>
        <select id="id_fornecedor" name="id_fornecedor" required>
            <?php
            // Conecta ao banco de dados novamente para obter fornecedores
            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Falha na conexão: " . $conn->connect_error);
            }
            $sql = "SELECT id, nome_representante FROM fornecedor";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['nome_representante']) . "</option>";
            }
            $conn->close();
            ?>
        </select>

        <input type="submit" value="Criar Produto">
    </form>
</body>
</html>
