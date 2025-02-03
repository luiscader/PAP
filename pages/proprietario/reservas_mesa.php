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

// Obter ID do restaurante
$id_utilizador = $_SESSION['id'];
$sql_restaurante = "SELECT id FROM Restaurante WHERE id_proprietario = ?";
$stmt_restaurante = $conn->prepare($sql_restaurante);
$stmt_restaurante->bind_param("i", $id_utilizador);
$stmt_restaurante->execute();
$stmt_restaurante->store_result();
$stmt_restaurante->bind_result($id_restaurante);
$stmt_restaurante->fetch();
$stmt_restaurante->close();

// Processa a reserva se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_completo = $_POST['nome_completo'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $preferencia_contato = $_POST['preferencia_contato'];
    $data_reserva = $_POST['data_reserva'];
    $hora_reserva = $_POST['hora_reserva'];
    $num_pessoas = $_POST['num_pessoas'];

    // Insere a reserva, incluindo o ID do restaurante
    $sql = "INSERT INTO Reserva (nome_completo, telefone, email, preferencia_contato, data_reserva, hora_reserva, num_pessoas, id_restaurante) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssiii", $nome_completo, $telefone, $email, $preferencia_contato, $data_reserva, $hora_reserva, $num_pessoas, $id_restaurante);

    if ($stmt->execute()) {
        echo "Reserva realizada com sucesso!";
    } else {
        echo "Erro ao realizar a reserva: " . $stmt->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva de Mesa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 50%;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            margin-top: 50px;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            color: #555;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #5cb85c;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #4cae4c;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Reserva de Mesa</h1>

    <!-- Formulário de Reserva -->
    <form method="post" action="reservas_mesa.php">
        <h2>Informações Pessoais</h2>

        <label for="nome_completo">Nome Completo:</label>
        <input type="text" id="nome_completo" name="nome_completo" required>

        <label for="telefone">Telefone:</label>
        <input type="tel" id="telefone" name="telefone" required>

        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" required>

        <label for="preferencia_contato">Preferência de Confirmação:</label>
        <select id="preferencia_contato" name="preferencia_contato" required>
            <option value="telefone">Telefone</option>
            <option value="whatsapp">WhatsApp</option>
            <option value="email">E-mail</option>
        </select>

        <h2>Detalhes da Reserva</h2>

        <label for="data_reserva">Data da Reserva:</label>
        <input type="date" id="data_reserva" name="data_reserva" required>

        <label for="hora_reserva">Horário da Reserva:</label>
        <input type="time" id="hora_reserva" name="hora_reserva" required>

        <label for="num_pessoas">Número de Pessoas:</label>
        <input type="number" id="num_pessoas" name="num_pessoas" min="1" required>

        <input type="submit" value="Reservar">
    </form>
</div>

</body>
</html>
