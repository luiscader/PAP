<?php
include 'C:/wamp64/www/PAP/includes/config.php';  // Inclui a conexão à base de dados

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $descricao = $_POST['descricao'];
    $desconto = $_POST['desconto'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];

    $sql = "INSERT INTO Promocao (descricao, desconto, data_inicio, data_fim) VALUES ('$descricao', $desconto, '$data_inicio', '$data_fim')";

    if ($conn->query($sql) === TRUE) {
        echo "Promoção criada com sucesso!";
    } else {
        echo "Erro ao criar promoção: " . $conn->error;
    }
}

$sql_prom = "SELECT * FROM Promocao";
$result_prom = $conn->query($sql_prom);

// Exibir promoções ativas
if ($result_prom->num_rows > 0) {
    echo "<h1>Promoções Ativas</h1>";
    while ($row = $result_prom->fetch_assoc()) {
        echo "Promoção: " . $row['descricao'] . " - Desconto: " . $row['desconto'] . "%<br>";
        echo "De: " . $row['data_inicio'] . " Até: " . $row['data_fim'] . "<br><hr>";
    }
} else {
    echo "Nenhuma promoção ativa.";
}

$conn->close();
?>

<!-- Formulário para nova promoção -->
<form method="post" action="promocoes.php">
    Descrição: <input type="text" name="descricao"><br>
    Desconto (%): <input type="number" name="desconto"><br>
    Data Início: <input type="date" name="data_inicio"><br>
    Data Fim: <input type="date" name="data_fim"><br>
    <input type="submit" value="Criar Promoção">
</form>
