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

// Recupera a lista de fornecedores associados ao restaurante
$sql = "SELECT f.id, f.nome_representante, f.telefone_representante, f.email_representante, f.nif_empresa, f.morada_sede, f.codigo_postal, f.distrito, f.pais, f.iban 
        FROM fornecedor f
        JOIN produto p ON f.id = p.id_fornecedor
        WHERE p.id_restaurante = ?
        GROUP BY f.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_restaurante);    
$stmt->execute();
$result = $stmt->get_result();

// Verifica se há fornecedores cadastrados
if ($result->num_rows == 0) {
    echo "Não há fornecedores associados a este restaurante.";
}

// Fecha a consulta
$stmt->close();

// Conta o número de fornecedores associados ao restaurante
$sql_count = "SELECT COUNT(DISTINCT f.id) as total_fornecedores 
              FROM fornecedor f
              JOIN produto p ON f.id = p.id_fornecedor
              WHERE p.id_restaurante = ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("i", $id_restaurante);
$stmt_count->execute();
$stmt_count->bind_result($total_fornecedores);
$stmt_count->fetch();
$stmt_count->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Fornecedores</title>
    <!-- Inclui o Bootstrap para estilos modernos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }

        h1 {
            margin-bottom: 30px;
            color: #343a40;
        }

        table {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        th {
            background-color: #007bff;
            color: black;
        }

        td, th {
            padding: 15px;
            text-align: left;
        }

        .table-actions {
            display: flex;
            gap: 10px;
        }

        .btn-remove {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-remove:hover {
            background-color: #c82333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestão de Fornecedores</h1>

        <!-- Exibe o número total de fornecedores -->
        <p class="text-secondary">Número total de fornecedores associados ao restaurante: <strong><?php echo $total_fornecedores; ?></strong></p>

        <?php if ($result->num_rows > 0): ?>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome do Representante</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th>NIF</th>
                        <th>Morada</th>
                        <th>Código Postal</th>
                        <th>Distrito</th>
                        <th>País</th>
                        <th>IBAN</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['nome_representante']); ?></td>
                            <td><?php echo htmlspecialchars($row['telefone_representante']); ?></td>
                            <td><?php echo htmlspecialchars($row['email_representante']); ?></td>
                            <td><?php echo htmlspecialchars($row['nif_empresa']); ?></td>
                            <td><?php echo htmlspecialchars($row['morada_sede']); ?></td>
                            <td><?php echo htmlspecialchars($row['codigo_postal']); ?></td>
                            <td><?php echo htmlspecialchars($row['distrito']); ?></td>
                            <td><?php echo htmlspecialchars($row['pais']); ?></td>
                            <td><?php echo htmlspecialchars($row['iban']); ?></td>
                            <td>
                                <div class="table-actions">
                                    <!-- Formulário para remover o fornecedor -->
                                    <form method="post" action="remover_fornecedor.php">
                                        <input type="hidden" name="id_fornecedor" value="<?php echo htmlspecialchars($row['id']); ?>">
                                        <button type="submit" class="btn-remove">Remover</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="alert alert-warning">Não há fornecedores associados a este restaurante.</p>
        <?php endif; ?>

        <?php $conn->close(); ?>
    </div>

    <!-- Inclui o JavaScript do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
