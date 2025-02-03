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

// Processar a submissão do formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $comida = $_POST['comida'];
    $servico = $_POST['servico'];
    $valor = $_POST['valor'];
    $ambiente = $_POST['ambiente'];
    $comentario = $_POST['comentario'];

    $stmt = $conn->prepare("INSERT INTO avaliacoes (id_restaurante, comida, servico, valor, ambiente, comentario) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiiis", $id_restaurante, $comida, $servico, $valor, $ambiente, $comentario);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Avaliação inserida com sucesso.</div>";
    } else {
        echo "<div class='alert alert-danger'>Erro ao inserir avaliação: " . $stmt->error . "</div>";
    }

    $stmt->close();
}

// Recuperar avaliações
$result = $conn->query("SELECT * FROM avaliacoes ORDER BY data_avaliacao DESC");
$avaliacoes = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliações</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome para estrelas -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        h1 {
            text-align: center;
            margin-bottom: 40px;
            color: #343a40;
            font-size: 36px;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .form-group label {
            font-weight: bold;
        }
        .rating {
            font-size: 24px;
        }
        .rating .star {
            cursor: pointer;
            color: #ddd;
        }
        .rating .star.selected {
            color: #ffc107;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .card-body {
            padding: 20px;
        }
        .card-title {
            font-weight: bold;
            font-size: 18px;
            color: #343a40;
        }
        .card-text {
            font-size: 16px;
            color: #6c757d;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Avaliações de Restaurantes</h1>

    <!-- Formulário para avaliação -->
    <form method="POST" class="mb-4">
        <div class="form-group">
            <label for="comida">Avaliação da Comida:</label>
            <div class="rating" id="comidaRating">
                <i class="fas fa-star star" data-value="1"></i>
                <i class="fas fa-star star" data-value="2"></i>
                <i class="fas fa-star star" data-value="3"></i>
                <i class="fas fa-star star" data-value="4"></i>
                <i class="fas fa-star star" data-value="5"></i>
            </div>
            <input type="hidden" name="comida" id="comidaValue" required>
        </div>

        <div class="form-group">
            <label for="servico">Avaliação do Serviço:</label>
            <div class="rating" id="servicoRating">
                <i class="fas fa-star star" data-value="1"></i>
                <i class="fas fa-star star" data-value="2"></i>
                <i class="fas fa-star star" data-value="3"></i>
                <i class="fas fa-star star" data-value="4"></i>
                <i class="fas fa-star star" data-value="5"></i>
            </div>
            <input type="hidden" name="servico" id="servicoValue" required>
        </div>

        <div class="form-group">
            <label for="valor">Avaliação do Valor:</label>
            <div class="rating" id="valorRating">
                <i class="fas fa-star star" data-value="1"></i>
                <i class="fas fa-star star" data-value="2"></i>
                <i class="fas fa-star star" data-value="3"></i>
                <i class="fas fa-star star" data-value="4"></i>
                <i class="fas fa-star star" data-value="5"></i>
            </div>
            <input type="hidden" name="valor" id="valorValue" required>
        </div>

        <div class="form-group">
            <label for="ambiente">Avaliação do Ambiente:</label>
            <div class="rating" id="ambienteRating">
                <i class="fas fa-star star" data-value="1"></i>
                <i class="fas fa-star star" data-value="2"></i>
                <i class="fas fa-star star" data-value="3"></i>
                <i class="fas fa-star star" data-value="4"></i>
                <i class="fas fa-star star" data-value="5"></i>
            </div>
            <input type="hidden" name="ambiente" id="ambienteValue" required>
        </div>

        <div class="form-group">
            <label for="comentario">Comentário:</label>
            <textarea name="comentario" class="form-control" rows="4"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Enviar Avaliação</button>
    </form>

    <h2>Avaliações Recentes</h2>

    <?php foreach ($avaliacoes as $avaliacao): ?>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">ID Restaurante: <?php echo htmlspecialchars($avaliacao['id_restaurante']); ?></h5>
                <p class="card-text">
                    <strong>Comida:</strong> <?php echo htmlspecialchars($avaliacao['comida']); ?> / 5<br>
                    <strong>Serviço:</strong> <?php echo htmlspecialchars($avaliacao['servico']); ?> / 5<br>
                    <strong>Valor:</strong> <?php echo htmlspecialchars($avaliacao['valor']); ?> / 5<br>
                    <strong>Ambiente:</strong> <?php echo htmlspecialchars($avaliacao['ambiente']); ?> / 5<br>
                    <strong>Comentário:</strong> <?php echo htmlspecialchars($avaliacao['comentario']); ?>
                </p>
                <p class="text-muted">Data da Avaliação: <?php echo htmlspecialchars($avaliacao['data_avaliacao']); ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    document.querySelectorAll('.rating').forEach(rating => {
        const stars = rating.querySelectorAll('.star');
        const hiddenInput = rating.nextElementSibling;

        stars.forEach(star => {
            star.addEventListener('click', function() {
                stars.forEach(s => s.classList.remove('selected'));
                this.classList.add('selected');
                hiddenInput.value = this.getAttribute('data-value');
            });
        });
    });
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
