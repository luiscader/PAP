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

// Buscar restaurantes recusados
$sql_recusados = "SELECT * FROM Restaurante WHERE status = 'reprovado'";
$result = $conn->query($sql_recusados);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Restaurantes Recusados</title>
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

        .detalhes {
            background-color: #007BFF;
        }

        .detalhes:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Admin - Restaurantes Recusados</h1>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>NIF</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Endereço</th>
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
                        <td><?= htmlspecialchars($row['morada']) . ', ' . htmlspecialchars($row['codigo_postal']) ?></td>
                        <td>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="id_restaurante" value="<?= $row['id'] ?>">
                                <button type="submit" name="acao" value="aprovar" class="aprovar">Aprovar</button>
                            </form>
                            <button class="detalhes" onclick="alertarDetalhes('<?= addslashes(json_encode($row)) ?>')">Detalhes</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Não há restaurantes recusados.</p>
    <?php endif; ?>

    <?php $conn->close(); ?>

    <script>
        function alertarDetalhes(dadosRestaurante) {
            const detalhes = JSON.parse(dadosRestaurante);
            let mensagem = `
                Nome: ${detalhes.nome_empresa}\n
                NIF: ${detalhes.nif}\n
                Designação Legal: ${detalhes.designacao_legal}\n
                Morada: ${detalhes.morada}\n
                Código Postal: ${detalhes.codigo_postal}\n
                Distrito: ${detalhes.distrito}\n
                País: ${detalhes.pais}\n
                Telefone: ${detalhes.telefone}\n
                Email Contato: ${detalhes.email_contato}\n
                Número Contato: ${detalhes.numero_contato}\n
                Nome Banco: ${detalhes.nome_banco}\n
                IBAN: ${detalhes.iban}\n
                Titular da Conta: ${detalhes.titular_conta}\n
            `;
            alert(mensagem);
        }
    </script>
</body>
</html>
