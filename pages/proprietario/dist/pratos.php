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

// Query necessária para a navbar
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

// Handle dish deletion
if (isset($_POST['delete_prato']) && isset($_POST['prato_id'])) {
    $prato_id = $_POST['prato_id'];
    
    // First verify if the dish belongs to the user's restaurant
    $sql = "SELECT p.id 
            FROM pratos p 
            JOIN restaurante r ON p.id_restaurante = r.id 
            WHERE p.id = ? AND r.id_proprietario = ?";
            
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $prato_id, $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Dish belongs to user's restaurant, proceed with deletion
            $delete_sql = "DELETE FROM pratos WHERE id = ?";
            if ($delete_stmt = $conn->prepare($delete_sql)) {
                $delete_stmt->bind_param("i", $prato_id);
                if ($delete_stmt->execute()) {
                    $message = "Prato excluído com sucesso!";
                    $message_type = "success";
                } else {
                    $message = "Erro ao excluir prato.";
                    $message_type = "danger";
                }
                $delete_stmt->close();
            }
        } else {
            $message = "Você não tem permissão para excluir este prato.";
            $message_type = "danger";
        }
        $stmt->close();
    }
}

// Get restaurant ID for the current user
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
        $restaurante_id = $user['id_restaurante'];
    } else {
        echo "Cliente não encontrado.";
        exit();
    }
    $stmt->close();
}

// Get dishes for the restaurant
$pratos = [];
if ($restaurante_id) {
    $sql = "SELECT id, nome, descricao, preco, data_criacao, data_atualizacao 
            FROM pratos 
            WHERE id_restaurante = ?";
            
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $restaurante_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $pratos[] = $row;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Gestão de Pratos - Restomate</title>

    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/vendors/jvectormap/jquery-jvectormap.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/owl-carousel-2/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/vendors/owl-carousel-2/owl.theme.default.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <link rel="stylesheet" href="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
</head>
<body class="sidebar-fixed">
    <div class="container-scroller">
        <?php include'sidebar.php'?>
        <div class="page-body-wrapper">
            <?php include'navbar.php'?>
            <div class="main-panel">
                <div class="content-wrapper">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="page-header">
                        <h3 class="page-title">Gestão Pratos</h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="pratos.php">Pratos</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Pratos</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Pratos</h4>
                            <a href="criar_prato.php" class="btn btn-primary mb-4">Criar Prato</a>
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table id="order-listing" class="table">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Nome</th>
                                                    <th>Descrição</th>
                                                    <th>Preço</th>
                                                    <th>Data Criação</th>
                                                    <th>Data Atualização</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($pratos)): ?>
                                                    <?php foreach ($pratos as $prato): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($prato['id']); ?></td>
                                                            <td><?php echo htmlspecialchars($prato['nome']); ?></td>
                                                            <td><?php echo htmlspecialchars($prato['descricao']); ?></td>
                                                            <td><?php echo number_format($prato['preco'], 2); ?>€</td>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($prato['data_criacao'])); ?></td>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($prato['data_atualizacao'])); ?></td>
                                                            <td>
                                                                <a href="editar_prato.php?id=<?php echo $prato['id']; ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                                                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                                                        onclick="confirmarExclusao(<?php echo $prato['id']; ?>, '<?php echo htmlspecialchars($prato['nome'], ENT_QUOTES); ?>')">
                                                                    Excluir
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">Nenhum prato encontrado</td>
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
    </div>

    <!-- Modal de confirmação -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir o prato <span id="pratoName"></span>?
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="prato_id" id="pratoId">
                        <input type="hidden" name="delete_prato" value="1">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
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

    <script>
    function confirmarExclusao(id, nome) {
        document.getElementById('pratoId').value = id;
        document.getElementById('pratoName').textContent = nome;
        $('#deleteModal').modal('show');
    }
    </script>
</body>
</html>