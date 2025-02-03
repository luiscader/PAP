<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$id_cliente = $_SESSION['id'];
$message = '';


// Fetch user data
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


// Busca todos os usuários
$sql = "SELECT id, nome, email, telefone, nif, tipo FROM Utilizador";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Gestão de Utilizadores - Restomate</title>
    
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.css">
    <link rel="stylesheet" href="assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css">
    
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/images/favicon.png" />

    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
</head>
<body>
    <div class="container-scroller">
        <?php include 'navbar.php'; ?>
        <?php include 'sidebar.php'; ?>
        
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="page-header">
                    <h3 class="page-title">Gestão de Utilizadores</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Utilizadores</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Listagem</li>
                        </ol>
                    </nav>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Utilizadores Registados</h4>
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
                                                <th>Status</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($result->num_rows > 0): ?>
                                                <?php while($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= $row['id'] ?></td>
                                                    <td><?= htmlspecialchars($row['nome']) ?></td>
                                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                                    <td><?= htmlspecialchars($row['telefone']) ?></td>
                                                    <td><?= htmlspecialchars($row['nif']) ?></td>
                                                    <td>
                                                        <?php 
                                                        $badge_class = [
                                                            'admin' => 'badge-danger',
                                                            'proprietario' => 'badge-warning',
                                                            'cliente' => 'badge-success',
                                                            'fornecedor' => 'badge-info'
                                                        ][$row['tipo']] ?? 'badge-secondary';
                                                        ?>
                                                        <label class="badge <?= $badge_class ?>">
                                                            <?= ucfirst($row['tipo']) ?>
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-outline-primary btn-sm">Editar</button>
                                                        <button class="btn btn-outline-danger btn-sm">Excluir</button>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">Nenhum utilizador encontrado</td>
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