<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$id_cliente = $_SESSION['id'];
$fornecedores = [];

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

// Buscar o ID do restaurante do usuário logado
$sql = "SELECT r.id AS restaurante_id 
        FROM utilizador u
        JOIN restaurante r ON u.id = r.id_proprietario
        WHERE u.id = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $restaurante_id = $row['restaurante_id'];
        
        // Buscar fornecedores associados ao restaurante
        $sql = "SELECT f.id, f.empresa, f.email_empresa, f.telefone_empresa 
                FROM fornecedor f
                JOIN restaurante_fornecedor rf ON f.id = rf.id_fornecedor
                WHERE rf.id_restaurante = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $restaurante_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $fornecedores = $result->fetch_all(MYSQLI_ASSOC);
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
    <title>Gestão de Fornecedores - Restomate</title>
    
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
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Fornecedores</h4>
                        <a href="contratar_fornecedor.php" class="btn btn-primary mb-3">Contrarar Fornecedor</a>

                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table id="order-listing" class="table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Empresa</th>
                                                <th>Email</th>
                                                <th>Telefone</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($fornecedores)): ?>
                                                <?php foreach($fornecedores as $row): ?>
                                                <tr>
                                                    <td><?= $row['id'] ?></td>
                                                    <td><?= htmlspecialchars($row['empresa']) ?></td>
                                                    <td><?= htmlspecialchars($row['email_empresa']) ?></td>
                                                    <td><?= htmlspecialchars($row['telefone_empresa']) ?></td>
                                                    <td>
                                                        <button class="btn btn-outline-primary btn-sm">Editar</button>
                                                        <button class="btn btn-outline-danger btn-sm">Excluir</button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">Nenhum fornecedor encontrado</td>
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