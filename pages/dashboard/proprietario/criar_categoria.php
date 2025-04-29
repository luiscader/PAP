<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../geral/login.php");
    exit();
}

$id_cliente = $_SESSION['id'];

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
        
        if (!$id_restaurante) {
            echo "Erro: Nenhum restaurante associado a este usuário.";
            exit();
        }
    } else {
        echo "Utilizador não encontrado.";
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
        echo "Utilizador não encontrado.";
        exit();
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['name']);
    $descricao = trim($_POST['comment']);
    
    if (empty($nome)) {
        $error = "O nome da categoria é obrigatório.";
    } else {
        $sql = "INSERT INTO categoria (nome, descricao, id_restaurante) VALUES (?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $nome, $descricao, $id_restaurante);
            
            if ($stmt->execute()) {
                $success = "Categoria criada com sucesso!";
                header("Location: categorias.php");
                exit();
            } else {
                $error = "Erro ao criar categoria: " . $conn->error;
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
    <title>Criar Categoria - Restomate</title>
    
    <link rel="stylesheet" href="../assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/favicon.png" />
</head>
<body class="sidebar-fixed">
    <div class="container-scroller">
        <?php include 'sidebar.php'?>
        <div class="page-body-wrapper">
            <?php include 'navbar.php'?>
            
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="page-header">
                        <h3 class="page-title">Criar Categoria</h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="categorias.php">Categorias</a></li>
                                <li class="breadcrumb-item" aria-current="page"><strong>Criar Categoria</strong></li>
                            </ol>
                        </nav>
                    </div>
                    <div class="row grid-margin">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Nova Categoria</h4>
                                    <?php if (isset($error)): ?>
                                        <div class="alert alert-danger"><?php echo $error; ?></div>
                                    <?php endif; ?>
                                    <?php if (isset($success)): ?>
                                        <div class="alert alert-success"><?php echo $success; ?></div>
                                    <?php endif; ?>
                                    <form class="cmxform" id="categoryForm" method="POST" action="">
                                        <fieldset>
                                            <div class="form-group">
                                                <label for="cname">Nome da Categoria *</label>
                                                <input id="cname" class="form-control" name="name" type="text" 
                                                    placeholder="Nome da Categoria..." required>
                                            </div>
                                            <div class="form-group">
                                                <label for="ccomment">Descrição (Opcional)</label>
                                                <textarea id="ccomment" class="form-control" name="comment" 
                                                    placeholder="Descrição..."></textarea>
                                            </div>
                                            <div class="mt-4">
                                                <input class="btn btn-inverse-primary" type="submit" value="Criar Categoria">
                                                <a href="categorias.php" class="btn btn-inverse-secondary">Cancelar</a>
                                            </div>
                                        </fieldset>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include '../footer.php'; ?>
            </div>
        </div>
    </div>

    <script src="../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../assets/vendors/datatables.net/jquery.dataTables.js"></script>
    <script src="../assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
    <script src="../assets/js/off-canvas.js"></script>
    <script src="../assets/js/misc.js"></script>
    <script src="../assets/js/settings.js"></script>
    <script src="../assets/js/todolist.js"></script>
    <script src="../assets/js/data-table.js"></script>
</body>
</html>