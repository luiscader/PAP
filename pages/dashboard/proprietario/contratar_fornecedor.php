<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../geral/login.php");
    exit();
}

$id_cliente = $_SESSION['id'];


$sql = "SELECT tipo FROM utilizador WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $stmt->bind_result($tipo);
    $stmt->fetch();
    $stmt->close();
    
    if ($tipo !== 'proprietario') {
        $_SESSION['message'] = "Acesso negado. Esta página é exclusiva para proprietários.";
        header("Location: ../../geral/index.php");
        exit();
    }
} else {
    die("Erro na verificação do tipo de usuário: " . $conn->error);
}

$message = '';
$restaurante_id = null;

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
    } else {
        echo "Usuário não encontrado.";
        exit();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['associar'])) {
    $id_fornecedor = filter_input(INPUT_POST, 'id_fornecedor', FILTER_VALIDATE_INT);

    if ($id_fornecedor && $restaurante_id) {
        $sql_associar = "INSERT IGNORE INTO restaurante_fornecedor (id_restaurante, id_fornecedor) 
                         VALUES (?, ?)";
        
        if ($stmt = $conn->prepare($sql_associar)) {
            $stmt->bind_param("ii", $restaurante_id, $id_fornecedor);
            if ($stmt->execute()) {
                $message = "Fornecedor associado com sucesso!";
                $message_type = "success";
            } else {
                $message = "Erro ao associar fornecedor: " . $conn->error;
                $message_type = "danger";
            }
            $stmt->close();
        }
    } else {
        $message = "Dados inválidos para associação!";
        $message_type = "danger";
    }
}


$fornecedores = [];
if ($restaurante_id) {
    $sql_fornecedores = "SELECT f.id, f.empresa, f.email_empresa, f.telefone_empresa 
                         FROM fornecedor f
                         WHERE f.id NOT IN (
                             SELECT id_fornecedor 
                             FROM restaurante_fornecedor 
                             WHERE id_restaurante = ?
                         )
                         ORDER BY f.empresa ASC";
    
    if ($stmt = $conn->prepare($sql_fornecedores)) {
        $stmt->bind_param("i", $restaurante_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $fornecedores = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
$sql = "SELECT id, nome, email, senha, tipo FROM utilizador WHERE id = ?";
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
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Associar Fornecedores - Restomate</title>
    <link rel="stylesheet" href="../assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../assets/vendors/jvectormap/jquery-jvectormap.css">
    <link rel="stylesheet" href="../assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="../assets/vendors/owl-carousel-2/owl.carousel.min.css">
    <link rel="stylesheet" href="../assets/vendors/owl-carousel-2/owl.theme.default.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
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
                        <h3 class="page-title">Associar Fornecedores</h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="fornecedores.php">Fornecedores</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><strong>Contratar Fornecedor</strong></li>
                            </ol>
                        </nav>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <?php if ($message): ?>
                                <div class="alert alert-<?= htmlspecialchars($message_type ?? 'danger') ?>"><?= htmlspecialchars($message) ?></div>
                            <?php endif; ?>

                            <h4 class="card-title">Fornecedores Disponíveis</h4>
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
                                                    <?php foreach ($fornecedores as $fornecedor): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($fornecedor['id']) ?></td>
                                                            <td><?= htmlspecialchars($fornecedor['empresa']) ?></td>
                                                            <td><?= htmlspecialchars($fornecedor['email_empresa'] ?? 'N/A') ?></td>
                                                            <td><?= htmlspecialchars($fornecedor['telefone_empresa'] ?? 'N/A') ?></td>
                                                            <td>
                                                                <form method="POST" class="form-inline d-inline">
                                                                    <input type="hidden" name="id_fornecedor" value="<?= htmlspecialchars($fornecedor['id']) ?>">
                                                                    <button type="submit" name="associar" class="btn btn-success btn-sm">
                                                                        <i class="mdi mdi-link-variant"></i> Associar
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td>Nenhum fornecedor disponível</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
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
                <?php include '../footer.php'; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
    <script src="../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../assets/vendors/datatables.net/jquery.dataTables.js"></script>
    <script src="../assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
    <script src="../assets/js/off-canvas.js"></script>
    <script src="../assets/js/misc.js"></script>
    <script src="../assets/js/settings.js"></script>
    <script src="../assets/js/data-table.js"></script>

    <script>
        $(document).ready(function() {

            <?php if (!empty($funcionarios)): ?>
                $('#order-listing').DataTable();
            <?php endif; ?>
        $(document).ready(function() {
            <?php if (!empty($funcionarios)): ?>
                $('#order-listing').DataTable();
            <?php endif; ?>
            $('#order-listing').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Portuguese.json"
                },
                "order": [[1, 'asc']],
                "columnDefs": [
                    { "orderable": false, "targets": [5,6] }
                ]
            });
        });
    </script>
</body>
</html>