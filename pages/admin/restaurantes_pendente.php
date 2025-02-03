<?php
include 'C:/wamp64/www/PAP/includes/config.php'; 
session_start();

// Verifica se o usuário está autenticado e é administrador
if (!isset($_SESSION['id']) || $_SESSION['tipo'] != 'admin') {
    echo "Acesso negado. Somente administradores podem acessar esta página.";
    exit();
}

// Atualizar status do restaurante
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_restaurante = $_POST['id_restaurante'];
    $acao = $_POST['acao'];

    if ($acao == 'aprovar') {
        $novo_status = 'ativo';
    } elseif ($acao == 'reprovar') {
        $novo_status = 'reprovado';
    } else {
        echo "Ação inválida.";
        exit();
    }

    $sql_update = "UPDATE Restaurante SET status = '$novo_status' WHERE id = $id_restaurante";

    if ($conn->query($sql_update) === TRUE) {
        echo "Status do restaurante atualizado com sucesso.";
    } else {
        echo "Erro ao atualizar o status: " . $conn->error;
    }
}

// Buscar restaurantes pendentes
$sql_pendentes = "SELECT id, nome_empresa, nif, email_contato, telefone FROM Restaurante WHERE status = 'pendente'";
$result = $conn->query($sql_pendentes);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Restaurantes Pendentes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f7f7f7;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #ffffff;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f4f4f4;
        }

        button {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            color: white;
            cursor: pointer;
        }

        .aprovar {
            background-color: #4CAF50;
        }

        .aprovar:hover {
            background-color: #45a049;
        }

        .reprovar {
            background-color: #f44336;
        }

        .reprovar:hover {
            background-color: #da190b;
        }
    </style>
</head>
<body>
    <h1>Admin - Restaurantes Pendentes</h1>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>NIF</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nome_empresa']) ?></td>
                        <td><?= htmlspecialchars($row['nif']) ?></td>
                        <td><?= htmlspecialchars($row['email_contato']) ?></td>
                        <td><?= htmlspecialchars($row['telefone']) ?></td>
                        <td>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="id_restaurante" value="<?= $row['id'] ?>">
                                <button type="submit" name="acao" value="aprovar" class="aprovar">Aprovar</button>
                            </form>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="id_restaurante" value="<?= $row['id'] ?>">
                                <button type="submit" name="acao" value="reprovar" class="reprovar">Reprovar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Não há restaurantes pendentes.</p>
    <?php endif; ?>

    <?php $conn->close(); ?>
</body>
</html>
