<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$id_cliente = $_SESSION['id'];

// Get user info and restaurant ID
$sql = "SELECT u.id, u.nome, u.email, u.tipo, r.id as id_restaurante 
        FROM Utilizador u 
        LEFT JOIN restaurante r ON r.id_proprietario = u.id 
        WHERE u.id = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $id_restaurante = $user['id_restaurante'];
    } else {
        echo "Cliente não encontrado.";
        exit();
    }
    $stmt->close();
}

$sql = "SELECT id, nome, email, senha, tipo FROM Utilizador WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $nome, $email, $senha, $tipo);
    if ($stmt->num_rows > 0) {
        $stmt->fetch();
    } else {
        echo "Cliente não encontrado.";
        exit();
    }
    $stmt->close();
}

// Get categories for dropdown
$categories = [];
if ($id_restaurante) {
    $sql = "SELECT id, nome FROM categoria WHERE id_restaurante = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id_restaurante);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        $stmt->close();
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['name']);
    $descricao = trim($_POST['description']);
    $preco = trim($_POST['price']);
    
    // Validate input
    if (empty($nome)) {
        $error = "O nome do prato é obrigatório.";
    } elseif (empty($preco) || !is_numeric($preco)) {
        $error = "O preço é obrigatório e deve ser um número válido.";
    } else {
        // Insert new dish
        $sql = "INSERT INTO pratos (id_restaurante, nome, descricao, preco) VALUES (?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("issd", $id_restaurante, $nome, $descricao, $preco);
            
            if ($stmt->execute()) {
                $success = "Prato criado com sucesso!";
                // Redirect to dishes list
                header("Location: pratos.php");
                exit();
            } else {
                $error = "Erro ao criar prato: " . $conn->error;
            }
            
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Criar Prato - Restomate</title>
    
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/images/favicon.png" />
</head>
<body class="sidebar-fixed">
    <div class="container-scroller">
        <?php include 'sidebar.php'?>
        <div class="page-body-wrapper">
            <?php include 'navbar.php'?>
            
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="page-header">
                        <h3 class="page-title">Criar Prato</h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="pratos.php">Pratos</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Criar Prato</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="row grid-margin">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Novo Prato</h4>
                                    <?php if (isset($error)): ?>
                                        <div class="alert alert-danger"><?php echo $error; ?></div>
                                    <?php endif; ?>
                                    <?php if (isset($success)): ?>
                                        <div class="alert alert-success"><?php echo $success; ?></div>
                                    <?php endif; ?>
                                    <form class="cmxform" id="dishForm" method="POST" action="">
                                        <fieldset>
                                            <div class="form-group">
                                                <label for="name">Nome do Prato *</label>
                                                <input id="name" class="form-control" name="name" type="text" 
                                                    placeholder="Nome do prato..." required>
                                            </div>
                                            <div class="form-group">
                                                <label for="description">Descrição</label>
                                                <textarea id="description" class="form-control" name="description" 
                                                    placeholder="Descrição do prato..."></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="price">Preço (€) *</label>
                                                <input id="price" class="form-control" name="price" type="number" 
                                                    step="0.01" min="0" placeholder="0.00" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="category">Categoria</label>
                                                <select id="category" class="form-control" name="category">
                                                    <option value="">Selecione uma categoria...</option>
                                                    <?php foreach ($categories as $category): ?>
                                                        <option value="<?php echo $category['id']; ?>">
                                                            <?php echo htmlspecialchars($category['nome']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <input class="btn btn-inverse-primary" type="submit" value="Criar Prato">
                                            <a href="pratos.php" class="btn btn-inverse-secondary">Cancelar</a>
                                        </fieldset>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include 'footer.php'; ?>
            </div>
        </div>
    </div>

    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="assets/vendors/datatables.net/jquery.dataTables.js"></script>
    <script src="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/misc.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/todolist.js"></script>
    <script src="assets/js/data-table.js"></script>
</body>
</html>