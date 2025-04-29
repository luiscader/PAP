<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../geral/login.php");
    exit();
}

$id_cliente = $_SESSION['id'];
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
        $tipo_usuario = $user_data['tipo'];
        
        if ($tipo_usuario !== 'proprietario') {
            $_SESSION['message'] = "Acesso negado. Esta página é exclusiva para proprietários.";
            header("Location: ../../geral/index.php");
            exit();
        }
    } else {
        echo "Usuário não encontrado.";
        exit();
    }
    $stmt->close();
} else {
    die("Erro na verificação do usuário: " . $conn->error);
}


$clientes = [];
if ($restaurante_id) {
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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contratar'])) {
    $id_utilizador = filter_input(INPUT_POST, 'id_utilizador', FILTER_VALIDATE_INT);
    $cargo = filter_input(INPUT_POST, 'cargo', FILTER_SANITIZE_STRING);

    if ($id_utilizador && $cargo && $restaurante_id) {
        $sql_contratar = "INSERT INTO funcionarios (id_utilizador, cargo, id_restaurante) 
                         VALUES (?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql_contratar)) {
            $stmt->bind_param("isi", $id_utilizador, $cargo, $restaurante_id);
            if ($stmt->execute()) {
                $sql_update_tipo = "UPDATE utilizador SET tipo = 'associado' WHERE id = ?";
                if ($stmt_update = $conn->prepare($sql_update_tipo)) {
                    $stmt_update->bind_param("i", $id_utilizador);
                    $stmt_update->execute();
                    $stmt_update->close();
                }

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
    <title>Contratar Funcionários - Restomate</title>
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
                        <h3 class="page-title">Contratar Funcionários</h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="funcionarios.php">Funcionários</a></li>
                                <li class="breadcrumb-item" aria-current="page"><strong>Contratar Funcionário</strong></li>
                            </ol>
                        </nav>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <?php if ($message): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
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
                                                <?php if (!empty($clientes)): ?>
                                                    <?php foreach ($clientes as $cliente): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($cliente['id']) ?></td>
                                                            <td><?= htmlspecialchars($cliente['nome']) ?></td>
                                                            <td><?= htmlspecialchars($cliente['email']) ?></td>
                                                            <td><?= htmlspecialchars($cliente['telefone'] ?? 'N/A') ?></td>
                                                            <td><?= htmlspecialchars($cliente['nif'] ?? 'N/A') ?></td>
                                                            <td>
                                                                <form method="POST" class="form-inline">
                                                                    <input type="hidden" name="id_utilizador" value="<?= htmlspecialchars($cliente['id']) ?>">
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
                                                <?php else: ?>
                                                    <tr>
                                                        <td>Nenhum cliente disponível</td>
                                                        <td></td>
                                                        <td></td>
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