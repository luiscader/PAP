<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';  // Certifique-se de que o caminho está correto

// Verifica se o utilizador está logado e se é proprietário
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'proprietario') {
    die('Acesso restrito. Apenas proprietários podem criar categorias.');
}

$id_restaurante = $_SESSION['id_restaurante'];  // Assumindo que o id_restaurante está na sessão

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Processa o formulário de criação de categoria
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_categoria = trim($_POST['nome_categoria']);
    $descricao_categoria = trim($_POST['descricao_categoria']);

    // Valida se o nome da categoria não está vazio
    if (!empty($nome_categoria)) {
        // Prepara a consulta SQL para inserir a nova categoria
        $sql_inserir_categoria = "INSERT INTO categoria (nome, descricao, id_restaurante, data_criacao, data_atualizacao) VALUES (?, ?, ?, NOW(), NOW())";
        if ($stmt = $conn->prepare($sql_inserir_categoria)) {
            $stmt->bind_param("ssi", $nome_categoria, $descricao_categoria, $id_restaurante);

            // Executa a inserção e verifica se foi bem-sucedida
            if ($stmt->execute()) {
                echo "Categoria criada com sucesso!";
            } else {
                echo "Erro ao criar categoria: " . $conn->error;
            }

            $stmt->close();
        } else {
            echo "Erro ao preparar a consulta: " . $conn->error;
        }
    } else {
        echo "Por favor, insira um nome válido para a categoria.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Nova Categoria</title>
    <style>
        form {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 10px;
        }

        label {
            display: block;
            margin-bottom: 8px;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .message {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Criar Nova Categoria</h1>

    <form method="post" action="">
        <label for="nome_categoria">Nome da Categoria:</label>
        <input type="text" id="nome_categoria" name="nome_categoria" required>

        <label for="descricao_categoria">Descrição da Categoria (opcional):</label>
        <textarea id="descricao_categoria" name="descricao_categoria" rows="4"></textarea>

        <input type="submit" value="Criar Categoria">
    </form>

    <!-- Exibir mensagens de sucesso ou erro, se houver -->
    <div class="message">
        <?php if (!empty($mensagem)) echo htmlspecialchars($mensagem); ?>
    </div>
</body>
</html>
