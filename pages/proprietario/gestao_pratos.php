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

// Exclui um prato se solicitado
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_prato = $_GET['id'];
    $sql_delete = "DELETE FROM pratos WHERE id = ?";
    if ($stmt_delete = $conn->prepare($sql_delete)) {
        $stmt_delete->bind_param("i", $id_prato);
        if ($stmt_delete->execute()) {
            echo "Prato excluído com sucesso!";
        } else {
            echo "Erro ao excluir prato: " . $conn->error;
        }
        $stmt_delete->close();
    }
}

// Recupera a lista de pratos
$sql_pratos = "SELECT id, nome, descricao, preco, data_criacao FROM pratos";
$stmt_pratos = $conn->prepare($sql_pratos);
$stmt_pratos->execute();
$result_pratos = $stmt_pratos->get_result();
$pratos = [];
while ($row = $result_pratos->fetch_assoc()) {
    $pratos[] = $row;
}
$stmt_pratos->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Pratos</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7f6;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h1 {
            color: #2c3e50;
            margin: 20px 0;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }
        p {
            margin: 10px 0;
        }
        a {
            text-decoration: none;
            color: #3498db;
            font-weight: bold;
            transition: color 0.3s;
        }
        a:hover {
            color: #2980b9;
        }
        table {
            width: 80%;
            margin: 20px 0;
            border-collapse: collapse;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        thead {
            background-color: #3498db;
            color: white;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        tbody tr {
            transition: background-color 0.3s;
        }
        tbody tr:hover {
            background-color: #f1f1f1;
        }
        .actions a {
            margin-right: 10px;
            color: #2c3e50;
            background-color: #e7f1ff;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }
        .actions a:hover {
            background-color: #d4e6ff;
            color: #0056b3;
        }
        .actions a.delete {
            background-color: #e74c3c;
            color: white;
        }
        .actions a.delete:hover {
            background-color: #c0392b;
        }
        @media (max-width: 768px) {
            table {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <h1>Gestão de Pratos</h1>
    <p><a href="criar_prato.php">Criar Novo Prato</a></p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Preço</th>
                <th>Data de Criação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pratos as $prato): ?>
                <tr>
                    <td><?php echo $prato['id']; ?></td>
                    <td><?php echo htmlspecialchars($prato['nome']); ?></td>
                    <td><?php echo htmlspecialchars($prato['descricao']); ?></td>
                    <td><?php echo number_format($prato['preco'], 2, ',', '.'); ?> €</td>
                    <td><?php echo date('d/m/Y H:i:s', strtotime($prato['data_criacao'])); ?></td>
                    <td class="actions">
                        <a href="ver_prato.php?id=<?php echo $prato['id']; ?>">Visualizar</a>
                        <a href="editar_prato.php?id=<?php echo $prato['id']; ?>">Editar</a>
                        <a class="delete" href="?action=delete&id=<?php echo $prato['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir este prato?');">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
