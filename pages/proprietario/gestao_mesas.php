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


// Função para editar uma reserva
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_reserva'])) {
    $id_reserva = $_POST['id_reserva'];
    $nome_completo = $_POST['nome_completo'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $preferencia_contato = $_POST['preferencia_contato'];
    $data_reserva = $_POST['data_reserva'];
    $hora_reserva = $_POST['hora_reserva'];
    $num_pessoas = $_POST['num_pessoas'];

    $sql_update = "UPDATE Reserva SET 
        nome_completo='$nome_completo', 
        telefone='$telefone', 
        email='$email', 
        preferencia_contato='$preferencia_contato', 
        data_reserva='$data_reserva', 
        hora_reserva='$hora_reserva', 
        num_pessoas=$num_pessoas 
        WHERE id = $id_reserva";

    if ($conn->query($sql_update) === TRUE) {
        echo "<div class='alert success'>Reserva atualizada com sucesso!</div>";
    } else {
        echo "<div class='alert error'>Erro ao atualizar a reserva: " . $conn->error . "</div>";
    }
}

// Exibir todas as reservas
$sql_reservas = "SELECT * FROM Reserva";
$result_reservas = $conn->query($sql_reservas);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Reservas</title>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                background-color: #f9f9f9; /* Fundo claro */
                font-family: 'Raleway', sans-serif;
                color: #333; /* Texto escuro para contraste */
                padding: 20px;
            }

            h1 {
                font-size: 2.5rem;
                text-align: center;
                color: #ff5722; /* Laranja vibrante */
                margin-bottom: 1.5rem;
                text-shadow: 0 2px 5px rgba(255, 87, 34, 0.2);
            }

            table {
                width: 100%;
                background: #fff; /* Fundo branco para a tabela */
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            th, td {
                padding: 15px;
                text-align: center;
                color: #333; /* Texto escuro para a tabela */
            }

            th {
                background-color: #ff5722; /* Cabeçalho da tabela em laranja */
                color: #fff; /* Texto branco no cabeçalho */
                font-size: 1.1rem;
            }

            td {
                background-color: #fafafa; /* Linhas de dados em fundo claro */
            }

            a {
                color: #ff5722; /* Links em laranja */
                text-decoration: none;
                font-weight: bold;
            }

            a:hover {
                color: #ff784e; /* Efeito hover mais claro */
                transition: 0.3s ease-in-out;
            }

            .btn-edit, .btn-delete {
                padding: 10px 15px;
                border-radius: 5px;
                background-color: #ff5722; /* Botões em laranja */
                color: #fff; /* Texto branco */
                border: none;
                cursor: pointer;
                font-weight: bold;
            }

            .btn-edit:hover {
                background-color: #ff784e; /* Hover do botão de editar */
                transition: 0.3s;
            }

            .btn-delete:hover {
                background-color: #e53935; /* Hover do botão de excluir (vermelho) */
                transition: 0.3s;
            }

            .alert {
                padding: 15px;
                margin-bottom: 1rem;
                border-radius: 5px;
            }

            .alert.success {
                background-color: #ffecb3; /* Fundo claro para sucesso */
                color: #3e2723; /* Texto escuro */
            }

            .alert.error {
                background-color: #ffccbc; /* Fundo claro para erro */
                color: #3e2723; /* Texto escuro */
            }

            .form-edit {
                margin: 2rem 0;
                padding: 20px;
                background: #fff; /* Fundo branco para o formulário */
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            input[type="text"], input[type="email"], input[type="date"], input[type="time"], input[type="number"], select {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd; /* Borda clara */
                border-radius: 5px;
                margin-bottom: 1rem;
                font-size: 1rem;
            }

            input[type="text"]:focus, input[type="email"]:focus, input[type="date"]:focus, input[type="time"]:focus, input[type="number"]:focus, select:focus {
                border-color: #ff5722; /* Borda em laranja ao focar */
                outline: none; /* Remove o contorno padrão */
            }

            input[type="submit"] {
                background-color: #ff5722; /* Botão de envio em laranja */
                color: #fff; /* Texto branco */
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
            }

            input[type="submit"]:hover {
                background-color: #ff784e; /* Hover do botão de envio */
                transition: 0.3s;
                
            }
            .btn-create {
            display: inline-block;
            padding: 10px 15px;
            background-color: #ff784e;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-bottom: 20px; /* Espaçamento abaixo */
            transition: background-color 0.3s ease;
            }

            .btn-create:hover {
                background-color: #ff784e; /* Hover mais claro */
                color:#fff;
            }


    </style>
</head>
<body>

<h1>Gestão de Reservas</h1>
<a class="btn-create" href="criar_reserva.php">Criar Nova Reserva</a>


<?php
if ($result_reservas->num_rows > 0) {
    echo "<table>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Telefone</th>
                <th>Email</th>
                <th>Contato</th>
                <th>Data</th>
                <th>Hora</th>
                <th>Nº Pessoas</th>
                <th>Ações</th>
            </tr>";

    while ($row = $result_reservas->fetch_assoc()) {
        echo "<tr>
                <td>" . $row['id'] . "</td>
                <td>" . $row['nome_completo'] . "</td>
                <td>" . $row['telefone'] . "</td>
                <td>" . $row['email'] . "</td>
                <td>" . ucfirst($row['preferencia_contato']) . "</td>
                <td>" . $row['data_reserva'] . "</td>
                <td>" . $row['hora_reserva'] . "</td>
                <td>" . $row['num_pessoas'] . "</td>
                <td>
                    <a class='btn-edit' href='?edit=" . $row['id'] . "'>Editar</a> | 
                    <a class='btn-delete' href='?delete=" . $row['id'] . "' onclick=\"return confirm('Tem certeza que deseja excluir esta reserva?')\">Excluir</a>
                </td>
              </tr>";
    }

    echo "</table>";
} else {
    echo "<p>Nenhuma reserva encontrada.</p>";
}

if (isset($_GET['edit'])) {
    $id_reserva = $_GET['edit'];
    $sql_editar = "SELECT * FROM Reserva WHERE id = $id_reserva";
    $result_editar = $conn->query($sql_editar);

    if ($result_editar->num_rows == 1) {
        $row = $result_editar->fetch_assoc();
?>

<div class="form-edit">
    <h2>Editar Reserva</h2>
    <form method="post" action="">
        <input type="hidden" name="id_reserva" value="<?php echo $row['id']; ?>">
        Nome Completo: <input type="text" name="nome_completo" value="<?php echo $row['nome_completo']; ?>"><br>
        Telefone: <input type="text" name="telefone" value="<?php echo $row['telefone']; ?>"><br>
        Email: <input type="email" name="email" value="<?php echo $row['email']; ?>"><br>
        Preferência de Contato: 
        <select name="preferencia_contato">
            <option value="telefone" <?php echo $row['preferencia_contato'] == 'telefone' ? 'selected' : ''; ?>>Telefone</option>
            <option value="whatsapp" <?php echo $row['preferencia_contato'] == 'whatsapp' ? 'selected' : ''; ?>>WhatsApp</option>
            <option value="email" <?php echo $row['preferencia_contato'] == 'email' ? 'selected' : ''; ?>>E-mail</option>
        </select><br>
        Data da Reserva: <input type="date" name="data_reserva" value="<?php echo $row['data_reserva']; ?>"><br>
        Hora da Reserva: <input type="time" name="hora_reserva" value="<?php echo $row['hora_reserva']; ?>"><br>
        Número de Pessoas: <input type="number" name="num_pessoas" value="<?php echo $row['num_pessoas']; ?>"><br>
        <input type="submit" name="edit_reserva" value="Atualizar Reserva">
    </form>
</div>

<?php
    }
}
?>

</body>
</html>

<?php
$conn->close();
?>
