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
    if ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $nome_usuario = $row['nome'];
        $email = $row['email'];
        $tipo = $row['tipo'];
        $id_restaurante = $row['id_restaurante'];
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

        
        if ($tipo !== "proprietario") {
            header("Location: ../../geral/index.php");
            exit();
        }
    } else {
        echo "Utilizador não encontrado.";
        exit();
    }
    $stmt->close();
}

$categorias = [];
$sql = "SELECT id, nome FROM categoria WHERE id_restaurante = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_restaurante);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
    $stmt->close();
}

$fornecedores = [];
$sql = "SELECT f.id, f.empresa 
        FROM fornecedor f 
        INNER JOIN restaurante_fornecedor rf ON f.id = rf.id_fornecedor 
        WHERE rf.id_restaurante = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_restaurante);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $fornecedores[] = $row;
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['name']);
    $descricao = trim($_POST['comment']);
    $quantidade = trim($_POST['quantidade']);
    $unidade_medida = $_POST['unidade_medida'];
    $id_categoria = $_POST['categoria'];
    $id_fornecedor = $_POST['fornecedor'];
    
    if (empty($nome) || empty($quantidade) || empty($unidade_medida)) {
        $error = "Os campos nome, quantidade e unidade de medida são obrigatórios.";
    } else {
        $sql = "INSERT INTO produtos (nome, descricao, quantidade, unidade_medida, id_categoria, id_restaurante, id_fornecedor) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssdssii", $nome, $descricao, $quantidade, $unidade_medida, $id_categoria, $id_restaurante, $id_fornecedor);
            
            if ($stmt->execute()) {
                $success = "Produto criado com sucesso!";
                header("Location: produtos.php");
                exit();
            } else {
                $error = "Erro ao criar produto: " . $conn->error;
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
    <title>Criar Produto</title>
    
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
                        <h3 class="page-title">Criar Produtos</h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Gestão</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><strong>Criar Produto</strong></li>
                            </ol>
                        </nav>
                    </div>
                    <div class="row grid-margin">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Produto</h4>
                                    <?php if (isset($error)): ?>
                                        <div class="alert alert-danger"><?php echo $error; ?></div>
                                    <?php endif; ?>
                                    <?php if (isset($success)): ?>
                                        <div class="alert alert-success"><?php echo $success; ?></div>
                                    <?php endif; ?>
                                    <form class="cmxform" id="commentForm" method="POST" action="">
                                        <fieldset>
                                            <div class="form-group">
                                                <label for="cname">Nome do Produto: *</label>
                                                <input id="cname" class="form-control" name="name" type="text" placeholder="Nome do Produto ..." required>
                                            </div>
                                            <div class="form-group">
                                                <label for="ccomment">Descrição (Opcional):</label>
                                                <textarea id="ccomment" class="form-control" name="comment" placeholder="Descrição..."></textarea>
                                            </div>
                                            <div class="ingredients-section">
                                                <div id="ingredients-container">
                                                    <div class="ingredient-row row">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>Quantidade</label>
                                                                <input type="number" class="form-control" placeholder="Quantidade" name="quantidade" step="0.01" min="0">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>Unidade</label>
                                                                <select class="form-select" name="unidade_medida" required>
                                                                    <option value="Kg">Kg</option>
                                                                    <option value="Gr">Gr</option>
                                                                    <option value="L">L</option>
                                                                    <option value="Ml">Ml</option>
                                                                    <option value="unidade">Unidade</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Categoria</label>
                                                        <div class="col-sm-9">
                                                            <select class="form-select" name="categoria" required>
                                                                <option value="">Selecione uma categoria...</option>
                                                                <?php foreach ($categorias as $categoria): ?>
                                                                    <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nome']); ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Fornecedor</label>
                                                        <div class="col-sm-9">
                                                            <select class="form-select" name="fornecedor" required>
                                                                <option value="">Selecione um fornecedor...</option>
                                                                <?php foreach ($fornecedores as $fornecedor): ?>
                                                                    <option value="<?php echo $fornecedor['id']; ?>"><?php echo htmlspecialchars($fornecedor['empresa']); ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <input class="btn btn-inverse-primary" type="submit" value="Criar Produto">
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