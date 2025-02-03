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

// Buscar clientes não contratados
$clientes = [];
if ($restaurante_id && $tipo_usuario === 'proprietario') {
    $sql_clientes = "SELECT u.id, u.nome, u.email, u.telefone, u.nif 
                    FROM utilizador u
                    LEFT JOIN funcionarios f ON u.id = f.id_utilizador
                    WHERE u.tipo = 'cliente' 
                    AND f.id_utilizador IS NULL
                    ORDER BY u.nome ASC";
    
    if ($stmt = $conn->prepare($sql_clientes)) {
        $stmt->execute();
        $result = $stmt->get_result();
        $clientes = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

// Processar contratação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contratar'])) {
    $id_utilizador = filter_input(INPUT_POST, 'id_utilizador', FILTER_VALIDATE_INT);
    $cargo = filter_input(INPUT_POST, 'cargo', FILTER_SANITIZE_STRING);

    if ($id_utilizador && $cargo && $restaurante_id) {
        $sql_contratar = "INSERT INTO funcionarios (id_utilizador, cargo, id_restaurante) 
                         VALUES (?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql_contratar)) {
            $stmt->bind_param("isi", $id_utilizador, $cargo, $restaurante_id);
            if ($stmt->execute()) {
                header("Location: funcionarios.php?success=1");
                exit();
            } else {
                $message = "Erro ao contratar: " . $conn->error;
            }
            $stmt->close();
        }
    } else {
        $message = "Dados inválidos para contratação!";
    }
}

// Query para dados do navbar/sidebar
$sql = "SELECT id, nome, email, senha, tipo FROM utilizador WHERE id = ?";
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
    <title>Contratar Funcionarios - Restomate</title>
    
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="assets/vendors/jvectormap/jquery-jvectormap.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/owl-carousel-2/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/vendors/owl-carousel-2/owl.theme.default.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css --> 
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/images/favicon.png" />

    <!-- Plugin css for this page -->
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
                    <h3 class="page-title">Contratar Funcionários</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="funcionarios.php">Funcionários</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Contratar Funcionários</li>
                        </ol>
                    </nav>
                </div>
                <div class="card">
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-danger"><?= $message ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">Funcionário contratado com sucesso!</div>
                        <?php endif; ?>

                        <h4 class="card-title">Clientes Disponíveis</h4>
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
                                            <?php foreach ($clientes as $cliente): ?>
                                            <tr>
                                                <td><?= $cliente['id'] ?></td>
                                                <td><?= htmlspecialchars($cliente['nome']) ?></td>
                                                <td><?= htmlspecialchars($cliente['email']) ?></td>
                                                <td><?= htmlspecialchars($cliente['telefone'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($cliente['nif'] ?? 'N/A') ?></td>
                                                <td>
                                                    <form method="POST" class="form-inline">
                                                        <input type="hidden" name="id_utilizador" value="<?= $cliente['id'] ?>">
                                                        <select name="cargo" class="form-control form-control-sm" required>
                                                            <option value="">Selecionar Cargo</option>
                                                            <option value="Gerente">Gerente</option>
                                                            <option value="Chefe de Cozinha">Chefe de Cozinha</option>
                                                            <option value="Cozinheiro">Cozinheiro</option>
                                                            <option value="Ajudante de Cozinha">Ajudante de Cozinha</option>
                                                            <option value="Empregado de Mesa">Empregado de Mesa</option>
                                                        </select>
                                                </td>
                                                <td>
                                                    <button type="submit" name="contratar" class="btn btn-success btn-sm">
                                                        <i class="mdi mdi-account-plus"></i> Contratar
                                                    </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
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
    <script>
        $(document).ready(function() {
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