<?php
include 'C:/wamp64/www/PAP/includes/config.php';

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Criar Pedido</title>
</head>
<body>
    <h1>Criar Pedido</h1>

    <form method="post" action="processar_pedido.php">
        <!-- Selecionar Restaurante -->
        <label for="id_restaurante">Restaurante:</label>
        <select id="id_restaurante" name="id_restaurante" required>
            <?php
            // Buscar os restaurantes disponíveis
            $sql_restaurantes = "SELECT id, nome FROM restaurante";
            $result_restaurantes = $conn->query($sql_restaurantes);

            if ($result_restaurantes->num_rows > 0) {
                while ($restaurante = $result_restaurantes->fetch_assoc()) {
                    echo '<option value="' . $restaurante['id'] . '">' . $restaurante['nome'] . '</option>';
                }
            } else {
                echo '<option value="">Nenhum restaurante disponível</option>';
            }
            ?>
        </select><br><br>

        <!-- Selecionar Mesa -->
        <label for="id_mesa">Mesa:</label>
        <select id="id_mesa" name="id_mesa" required>
            <?php
            // Buscar as mesas disponíveis
            $sql_mesas = "SELECT id, numero_mesa FROM mesa";
            $result_mesas = $conn->query($sql_mesas);

            if ($result_mesas->num_rows > 0) {
                while ($mesa = $result_mesas->fetch_assoc()) {
                    echo '<option value="' . $mesa['id'] . '">Mesa ' . $mesa['numero_mesa'] . '</option>';
                }
            } else {
                echo '<option value="">Nenhuma mesa disponível</option>';
            }
            ?>
        </select><br><br>

        <!-- Selecionar Empregado -->
        <label for="id_empregado">Empregado:</label>
        <select id="id_empregado" name="id_empregado" required>
            <?php
            // Buscar os empregados disponíveis
            $sql_empregados = "SELECT id, nome FROM empregado";
            $result_empregados = $conn->query($sql_empregados);

            if ($result_empregados->num_rows > 0) {
                while ($empregado = $result_empregados->fetch_assoc()) {
                    echo '<option value="' . $empregado['id'] . '">' . $empregado['nome'] . '</option>';
                }
            } else {
                echo '<option value="">Nenhum empregado disponível</option>';
            }
            ?>
        </select><br><br>

        <!-- Inserir Preço Total -->
        <label for="preco_total">Preço Total:</label>
        <input type="number" step="0.01" id="preco_total" name="preco_total" required><br><br>

        <input type="submit" value="Criar Pedido">
    </form>
</body>
</html>

<?php
$conn->close();
?>
