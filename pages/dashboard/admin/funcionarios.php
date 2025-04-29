<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../geral/login.php");
    exit();
}

$id_admin = $_SESSION['id'];
$message = '';

$sql = "SELECT id, nome, email, senha, tipo FROM utilizador WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_admin);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $nome, $email, $senha, $tipo);
    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        if ($tipo !== "admin") {
            header("Location: ../../geral/index.php");
            exit();
        }
    } else {
        echo "Utilizador não encontrado.";
        exit();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_associado']) && isset($_POST['associado_id'])) {
        $associado_id = $_POST['associado_id'];
        $sql = "DELETE FROM utilizador WHERE id = ? AND tipo = 'associado'";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $associado_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao excluir associado: ' . $conn->error]);
            }
            $stmt->close();
        }
        $conn->close();
        exit();
    } elseif (isset($_POST['edit_associado']) && isset($_POST['associado_id'])) {
        $associado_id = $_POST['associado_id'];
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $telefone = $_POST['telefone'] ?: null;
        $data_nascimento = $_POST['data_nascimento'] ?: null;
        $nif = $_POST['nif'] ?: null;
        $morada = $_POST['morada'] ?: null;
        $codigo_postal = $_POST['codigo_postal'] ?: null;
        $pais = $_POST['pais'] ?: null;
        $distrito = $_POST['distrito'] ?: null;

        $sql = "UPDATE utilizador SET nome = ?, email = ?, telefone = ?, data_nascimento = ?, nif = ?, morada = ?, codigo_postal = ?, pais = ?, distrito = ? WHERE id = ? AND tipo = 'associado'";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssssssssi", $nome, $email, $telefone, $data_nascimento, $nif, $morada, $codigo_postal, $pais, $distrito, $associado_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar associado: ' . $conn->error]);
            }
            $stmt->close();
        }
        $conn->close();
        exit();
    }
}

$sql = "SELECT u.id, u.nome, u.email, u.telefone, u.data_nascimento, u.nif, u.morada, u.codigo_postal, u.pais, u.distrito, 
               f.cargo, r.id AS id_restaurante, r.nome_empresa 
        FROM utilizador u 
        LEFT JOIN funcionarios f ON u.id = f.id_utilizador 
        LEFT JOIN restaurante r ON f.id_restaurante = r.id 
        WHERE u.tipo = 'associado'";
if ($stmt = $conn->prepare($sql)) {
    $stmt->execute();
    $result = $stmt->get_result();
    $associados = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$cargo_cores = [
    'Gerente' => 'badge-danger',
    'Chefe de Cozinha' => 'badge-warning',
    'Cozinheiro' => 'badge-success',
    'Ajudante de Cozinha' => 'badge-info',
    'Empregado de Mesa' => 'badge-primary'
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Gestão de Funcionários - Restomate</title>
    
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
                        <h3 class="page-title">Gestão de Funcionários</h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="funcionarios.php">Funcionários</a></li>
                                <li class="breadcrumb-item" aria-current="page"><strong>Funcionários</strong></li>
                            </ol>
                        </nav>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="card-title">Funcionários</h4>
                            </div>
                            <?php if (!empty($message)): ?>
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    <?php echo $message; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
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
                                                    <th>Cargo</th>
                                                    <th>Restaurante</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($associados)): ?>
                                                    <?php foreach ($associados as $associado): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($associado['id']); ?></td>
                                                            <td><?php echo htmlspecialchars($associado['nome']); ?></td>
                                                            <td><?php echo htmlspecialchars($associado['email']); ?></td>
                                                            <td><?php echo htmlspecialchars($associado['telefone'] ?? '-'); ?></td>
                                                            <td>
                                                                <?php 
                                                                $cargo = $associado['cargo'] ?? '-';
                                                                $cargo_class = ($cargo === '-') ? 'badge-secondary' : ($cargo_cores[$cargo] ?? 'badge-secondary');
                                                                ?>
                                                                <span class="badge <?php echo $cargo_class; ?>">
                                                                    <?php echo htmlspecialchars($cargo); ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($associado['nome_empresa'] ?? 'Não associado'); ?></td>
                                                            <td>
                                                                <button class="btn btn-outline-primary btn-sm details-btn" 
                                                                        data-associado-id="<?php echo $associado['id']; ?>">Detalhes</button>
                                                                <button class="btn btn-outline-primary btn-sm edit-btn" 
                                                                        data-associado-id="<?php echo $associado['id']; ?>">Editar</button>
                                                                <button class="btn btn-outline-danger btn-sm delete-btn" 
                                                                        data-associado-id="<?php echo $associado['id']; ?>" 
                                                                        data-associado-nome="<?php echo htmlspecialchars($associado['nome']); ?>">Excluir</button>
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
                    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="detailsModalLabel">Detalhes do Funcionário</h5>
                                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered">
                                        <tr><th>ID</th><td id="detalhe-id"></td></tr>
                                        <tr><th>Nome</th><td id="detalhe-nome"></td></tr>
                                        <tr><th>Email</th><td id="detalhe-email"></td></tr>
                                        <tr><th>Telefone</th><td id="detalhe-telefone"></td></tr>
                                        <tr><th>Data Nascimento</th><td id="detalhe-data-nascimento"></td></tr>
                                        <tr><th>NIF</th><td id="detalhe-nif"></td></tr>
                                        <tr><th>Morada</th><td id="detalhe-morada"></td></tr>
                                        <tr><th>Código Postal</th><td id="detalhe-codigo-postal"></td></tr>
                                        <tr><th>País</th><td id="detalhe-pais"></td></tr>
                                        <tr><th>Distrito</th><td id="detalhe-distrito"></td></tr>
                                        <tr><th>Cargo</th><td id="detalhe-cargo"></td></tr>
                                        <tr><th>Restaurante Associado</th><td id="detalhe-restaurante"></td></tr>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel">Editar Funcionário</h5>
                                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="editAssociadoForm">
                                        <div class="form-group">
                                            <label for="edit-nome">Nome</label>
                                            <input type="text" class="form-control" id="edit-nome" name="nome" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-email">Email</label>
                                            <input type="email" class="form-control" id="edit-email" name="email" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-telefone">Telefone</label>
                                            <input type="text" class="form-control" id="edit-telefone" name="telefone">
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-data-nascimento">Data de Nascimento</label>
                                            <input type="date" class="form-control" id="edit-data-nascimento" name="data_nascimento">
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-nif">NIF</label>
                                            <input type="text" class="form-control" id="edit-nif" name="nif">
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-morada">Morada</label>
                                            <input type="text" class="form-control" id="edit-morada" name="morada">
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-codigo-postal">Código Postal</label>
                                            <input type="text" class="form-control" id="edit-codigo-postal" name="codigo_postal">
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-pais">País</label>
                                            <input type="text" class="form-control" id="edit-pais" name="pais">
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-distrito">Distrito</label>
                                            <input type="text" class="form-control" id="edit-distrito" name="distrito">
                                        </div>
                                        <input type="hidden" id="edit-associado-id">
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-primary" id="confirmEdit">Salvar Alterações</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Tem certeza que deseja excluir o funcionário <strong id="delete-associado-nome"></strong>? Esta ação não pode ser desfeita.</p>
                                    <input type="hidden" id="delete-associado-id">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-danger" id="confirmDelete">Confirmar Exclusão</button>
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
    <script src="../assets/js/todolist.js"></script>
    <script src="../assets/js/data-table.js"></script>

    <script>
        $(document).ready(function() {
            <?php if (!empty($associados)): ?>
                $('#order-listing').DataTable();
            <?php endif; ?>

            $(document).on('click', '.details-btn', function() {
                var associadoId = $(this).data('associado-id');
                <?php foreach ($associados as $associado): ?>
                    if (associadoId == <?php echo $associado['id']; ?>) {
                        $('#detalhe-id').text(<?php echo json_encode($associado['id']); ?>);
                        $('#detalhe-nome').text(<?php echo json_encode($associado['nome']); ?>);
                        $('#detalhe-email').text(<?php echo json_encode($associado['email']); ?>);
                        $('#detalhe-telefone').text(<?php echo json_encode($associado['telefone'] ?? '-'); ?>);
                        $('#detalhe-data-nascimento').text(<?php echo $associado['data_nascimento'] ? json_encode(date('d/m/Y', strtotime($associado['data_nascimento']))) : '"-"'; ?>);
                        $('#detalhe-nif').text(<?php echo json_encode($associado['nif'] ?? '-'); ?>);
                        $('#detalhe-morada').text(<?php echo json_encode($associado['morada'] ?? '-'); ?>);
                        $('#detalhe-codigo-postal').text(<?php echo json_encode($associado['codigo_postal'] ?? '-'); ?>);
                        $('#detalhe-pais').text(<?php echo json_encode($associado['pais'] ?? '-'); ?>);
                        $('#detalhe-distrito').text(<?php echo json_encode($associado['distrito'] ?? '-'); ?>);
                        <?php 
                        $cargo = $associado['cargo'] ?? '-';
                        $cargo_class = ($cargo === '-') ? 'badge-secondary' : ($cargo_cores[$cargo] ?? 'badge-secondary');
                        ?>
                        $('#detalhe-cargo').html('<span class="badge <?php echo $cargo_class; ?>"><?php echo htmlspecialchars($cargo); ?></span>');
                        $('#detalhe-restaurante').text(<?php echo json_encode($associado['nome_empresa'] ?? 'Não associado'); ?>);
                    }
                <?php endforeach; ?>
                $('#detailsModal').modal('show');
            });

            $(document).on('click', '.edit-btn', function() {
                var associadoId = $(this).data('associado-id');
                <?php foreach ($associados as $associado): ?>
                    if (associadoId == <?php echo $associado['id']; ?>) {
                        $('#edit-associado-id').val(<?php echo json_encode($associado['id']); ?>);
                        $('#edit-nome').val(<?php echo json_encode($associado['nome']); ?>);
                        $('#edit-email').val(<?php echo json_encode($associado['email']); ?>);
                        $('#edit-telefone').val(<?php echo json_encode($associado['telefone'] ?? ''); ?>);
                        $('#edit-data-nascimento').val(<?php echo json_encode($associado['data_nascimento'] ?? ''); ?>);
                        $('#edit-nif').val(<?php echo json_encode($associado['nif'] ?? ''); ?>);
                        $('#edit-morada').val(<?php echo json_encode($associado['morada'] ?? ''); ?>);
                        $('#edit-codigo-postal').val(<?php echo json_encode($associado['codigo_postal'] ?? ''); ?>);
                        $('#edit-pais').val(<?php echo json_encode($associado['pais'] ?? ''); ?>);
                        $('#edit-distrito').val(<?php echo json_encode($associado['distrito'] ?? ''); ?>);
                    }
                <?php endforeach; ?>
                $('#editModal').modal('show');
            });

            $('#confirmEdit').on('click', function() {
                var associadoId = $('#edit-associado-id').val();
                var data = {
                    edit_associado: true,
                    associado_id: associadoId,
                    nome: $('#edit-nome').val(),
                    email: $('#edit-email').val(),
                    telefone: $('#edit-telefone').val(),
                    data_nascimento: $('#edit-data-nascimento').val(),
                    nif: $('#edit-nif').val(),
                    morada: $('#edit-morada').val(),
                    codigo_postal: $('#edit-codigo-postal').val(),
                    pais: $('#edit-pais').val(),
                    distrito: $('#edit-distrito').val()
                };

                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#editModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Erro ao atualizar associado: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao comunicar com o servidor: ' + error);
                    }
                });
            });

            $(document).on('click', '.delete-btn', function() {
                var associadoId = $(this).data('associado-id');
                var associadoNome = $(this).data('associado-nome');
                $('#delete-associado-id').val(associadoId);
                $('#delete-associado-nome').text(associadoNome);
                $('#deleteModal').modal('show');
            });


            $('#confirmDelete').on('click', function() {
                var associadoId = $('#delete-associado-id').val();
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { delete_associado: true, associado_id: associadoId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#deleteModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Erro ao excluir associado: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao comunicar com o servidor: ' + error);
                    }
                });
            });
        });
    </script>
</body>
</html>