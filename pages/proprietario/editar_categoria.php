<?php
session_start();
include 'C:/wamp64/www/PAP/includes/config.php';  // Certifique-se de que o caminho está correto

// Verifica se o utilizador está logado e se é proprietário
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'proprietario') {
    die('Acesso restrito. Apenas proprietários podem acessar esta página.');
}

$id_restaurante = $_SESSION['id_restaurante'];  // Assumindo que o id_restaurante está na sessão

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verifica se o ID da categoria foi passado e recupera a categoria
if (isset($_GET['id'])) {
    $id_categoria = $_GET['id'];

    $sql = "SELECT id, nome, descricao FROM categoria WHERE id = ? AND id_restaurante = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $id_categoria, $id_restaurante);
        $stmt->execute();
        $result = $stmt->get_result();
        $categoria = $result->fetch_assoc();
        $stmt->close();
    } else {
        die("Erro ao preparar a consulta: " . $conn->error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Atualiza a categoria
        $novo_nome = $_POST['novo_nome'];
        $nova_descricao = $_POST['nova_descricao'];

        $sql_atualizar = "UPDATE categoria SET nome = ?, descricao = ?, data_atualizacao = NOW() WHERE id = ? AND id_restaurante = ?";
        if ($stmt = $conn->prepare($sql_atualizar)) {
            $stmt->bind_param("ssii", $novo_nome, $nova_descricao, $id_categoria, $id_restaurante);
            if ($stmt->execute()) {
                echo "Categoria atualizada com sucesso!";
            } else {
                echo "Erro ao atualizar categoria: " . $conn->error;
            }
            $stmt->close();
        } else {
            echo "Erro ao preparar a consulta: " . $conn->error;
        }
    }
} else {
    die('ID da categoria não especificado.');
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Categoria</title>
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input[type="text"], textarea {
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

        .button-container a {
            text-decoration: none;
            color: white;
            background-color: #28a745;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
        }

        .button-container a:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h1>Editar Categoria</h1>
    <div class="form-container">
        <form method="post" action="">
            <label for="novo_nome">Nome:</label>
            <input type="text" id="novo_nome" name="novo_nome" value="<?php echo htmlspecialchars($categoria['nome']); ?>" required>
            
            <label for="nova_descricao">Descrição:</label>
            <textarea id="nova_descricao" name="nova_descricao" required><?php echo htmlspecialchars($categoria['descricao']); ?></textarea>
            
            <input type="submit" value="Atualizar Categoria">
        </form>
    </div>
    <div class="button-container">
        <a href="gestao_categorias.php">Voltar à Gestão de Categorias</a>
    </div>
</body>
</html>
