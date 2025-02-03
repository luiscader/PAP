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

// Processa a remoção de categorias
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remover'])) {
    // Remove a categoria
    $id_categoria = $_POST['id_categoria'];

    $sql_remover = "DELETE FROM categoria WHERE id = ? AND id_restaurante = ?";
    if ($stmt = $conn->prepare($sql_remover)) {
        $stmt->bind_param("ii", $id_categoria, $id_restaurante);
        if ($stmt->execute()) {
            echo "Categoria removida com sucesso!";
        } else {
            echo "Erro ao remover categoria: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Erro ao preparar a consulta: " . $conn->error;
    }
}

// Recupera todas as categorias associadas ao restaurante
$sql = "SELECT id, nome, descricao, data_criacao, data_atualizacao FROM categoria WHERE id_restaurante = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_restaurante);
$stmt->execute();
$result = $stmt->get_result();
$categorias = [];
while ($row = $result->fetch_assoc()) {
    $categorias[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Categorias</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .actions form {
            display: inline;
        }

        .form-container {
            margin-top: 20px;
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

        .button-container {
            margin-bottom: 20px;
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

        .edit-button {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .edit-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Gestão de Categorias</h1>

    <!-- Botão para criar uma nova categoria -->
    <div class="button-container">
        <a href="criar_categoria.php">Criar Nova Categoria</a>
    </div>

    <?php if (count($categorias) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Data de Criação</th>
                    <th>Data de Atualização</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $categoria): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($categoria['id']); ?></td>
                        <td><?php echo htmlspecialchars($categoria['nome']); ?></td>
                        <td><?php echo htmlspecialchars($categoria['descricao']); ?></td>
                        <td><?php echo htmlspecialchars($categoria['data_criacao']); ?></td>
                        <td><?php echo htmlspecialchars($categoria['data_atualizacao']); ?></td>
                        <td>
                            <div class="actions">
                                <!-- Botão para editar a categoria -->
                                <a href="editar_categoria.php?id=<?php echo htmlspecialchars($categoria['id']); ?>" class="edit-button">Editar</a>

                                <!-- Formulário para remover a categoria -->
                                <form method="post" action="" style="display:inline;">
                                    <input type="hidden" name="id_categoria" value="<?php echo htmlspecialchars($categoria['id']); ?>">
                                    <input type="submit" name="remover" value="Remover" onclick="return confirm('Tem certeza de que deseja remover esta categoria?');">
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Não há categorias associadas a este restaurante.</p>
    <?php endif; ?>
</body>
</html>
