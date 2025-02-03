<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$id_cliente = $_SESSION['id'];
$message = '';
$restaurante_id = null;

// Buscar dados do usuário e restaurante
$sql = "SELECT u.id, u.nome, u.email, u.tipo, r.id AS restaurante_id 
        FROM utilizador u
        LEFT JOIN restaurante r ON u.id = r.id_proprietario
        WHERE u.id = ?";
        
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $restaurante_id = $user_data['restaurante_id'];
        $tipo_usuario = $user_data['tipo'];
        
        if ($tipo_usuario !== 'proprietario' || !$restaurante_id) {
            $message = "Acesso restrito a proprietários com restaurante válido";
        }
    } else {
        echo "Usuário não encontrado.";
        exit();
    }
    $stmt->close();
}

// Buscar funcionários do restaurante
$funcionarios = [];
if ($restaurante_id && $tipo_usuario === 'proprietario') {
    $sql = "SELECT u.id, u.nome, u.email, u.telefone, u.nif, f.cargo 
            FROM funcionarios f
            INNER JOIN utilizador u ON f.id_utilizador = u.id
            WHERE f.id_restaurante = ?";
            
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $restaurante_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $funcionarios = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
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

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Gestão de Funcionarios - Restomate</title>

    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/vendors/jvectormap/jquery-jvectormap.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/owl-carousel-2/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/vendors/owl-carousel-2/owl.theme.default.min.css">

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="shortcut icon" href="assets/images/favicon.png" />

    <link rel="stylesheet" href="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
    <style>
    </style>
</head>
<body class="sidebar-fixed">
    <div class="container-scroller">
      <?php include'sidebar.php'?>
      <div class="page-body-wrapper">
        <?php include'navbar.php'?>
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="page-header">
                    <h3 class="page-title">Gestão Funcionários</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="funcionarios.php">Funcionários</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Funcionários</li>
                        </ol>
                    </nav>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Funcionários</h4>
                        <a href="contratar_funcionario.php" class="btn btn-primary">Contratar Funcionário</a>
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table id="order-listing" class="table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nome</th>
                                                <th>Email</th>
                                                <th>Telefone</th>
                                                <th>NIF</th>
                                                <th>Cargo</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($funcionarios)): ?>
                                                <?php foreach($funcionarios as $row): ?>
                                                <tr>
                                                    <td><?= $row['id'] ?></td>
                                                    <td><?= htmlspecialchars($row['nome']) ?></td>
                                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                                    <td><?= htmlspecialchars($row['telefone']) ?></td>
                                                    <td><?= htmlspecialchars($row['nif']) ?></td>
                                                    <td>
                                                        <?php 
                                                        $badge_class = [
                                                            'Gerente' => 'badge-danger',
                                                            'Chefe de Cozinha' => 'badge-warning',
                                                            'Cozinheiro' => 'badge-success',
                                                            'Ajudante de Cozinha' => 'badge-info',
                                                            'Empregado de Mesa' => 'badge-primary'
                                                        ][$row['cargo']] ?? 'badge-secondary';
                                                        ?>
                                                        <label class="badge <?= $badge_class ?>">
                                                            <?= $row['cargo'] ?>
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-outline-primary btn-sm">Editar</button>
                                                        <button class="btn btn-outline-danger btn-sm">Excluir</button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">Nenhum funcionário encontrado</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <!-- plugins:js -->
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="assets/vendors/datatables.net/jquery.dataTables.js"></script>
    <script src="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>

    <!-- Scripts customizados -->
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/misc.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/todolist.js"></script>
    <script src="assets/js/data-table.js"></script>
</body>
</html>