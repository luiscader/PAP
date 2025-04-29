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
    if (isset($_POST['delete_fornecedor']) && isset($_POST['fornecedor_id'])) {
        $fornecedor_id = $_POST['fornecedor_id'];
        $sql = "DELETE FROM fornecedor WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $fornecedor_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao excluir fornecedor: ' . $conn->error]);
            }
            $stmt->close();
        }
        $conn->close();
        exit();
    } elseif (isset($_POST['edit_fornecedor']) && isset($_POST['fornecedor_id'])) {
        $fornecedor_id = $_POST['fornecedor_id'];
        $empresa = $_POST['empresa'];
        $email_empresa = $_POST['email_empresa'];
        $telefone_empresa = $_POST['telefone_empresa'] ?: null;
        $nif_empresa = $_POST['nif_empresa'];
        $morada_sede = $_POST['morada_sede'] ?: null;
        $codigo_postal = $_POST['codigo_postal'] ?: null;
        $distrito = $_POST['distrito'] ?: null;
        $pais = $_POST['pais'] ?: null;
        $iban = $_POST['iban'] ?: null;
        $status = $_POST['status'];

        $sql = "UPDATE fornecedor SET empresa = ?, email_empresa = ?, telefone_empresa = ?, nif_empresa = ?, morada_sede = ?, codigo_postal = ?, distrito = ?, pais = ?, iban = ?, status = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssssssssi", $empresa, $email_empresa, $telefone_empresa, $nif_empresa, $morada_sede, $codigo_postal, $distrito, $pais, $iban, $status, $fornecedor_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar fornecedor: ' . $conn->error]);
            }
            $stmt->close();
        }
        $conn->close();
        exit();
    }
}

$sql = "SELECT id, empresa, email_empresa, telefone_empresa, nif_empresa, morada_sede, codigo_postal, distrito, pais, iban, status 
        FROM fornecedor";
if ($stmt = $conn->prepare($sql)) {
    $stmt->execute();
    $result = $stmt->get_result();
    $fornecedores = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$fornecedores_restaurantes = [];
$sql = "SELECT rf.id_fornecedor, r.id AS id_restaurante, r.nome_empresa 
        FROM restaurante_fornecedor rf 
        JOIN restaurante r ON rf.id_restaurante = r.id";
if ($stmt = $conn->prepare($sql)) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $fornecedores_restaurantes[$row['id_fornecedor']][] = [
            'id_restaurante' => $row['id_restaurante'],
            'nome_empresa' => $row['nome_empresa']
        ];
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
    <title>Gestão de Fornecedores - Restomate</title>
    
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
                        <h3 class="page-title">Gestão de Fornecedores</h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="fornecedores.php">Fornecedores</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><strong>Fornecedores</strong></li>
                            </ol>
                        </nav>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="card-title">Fornecedores</h4>
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
                                                    <th>Empresa</th>
                                                    <th>Email</th>
                                                    <th>Telefone</th>
                                                    <th>NIF</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($fornecedores)): ?>
                                                    <?php foreach ($fornecedores as $fornecedor): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($fornecedor['id']); ?></td>
                                                            <td><?php echo htmlspecialchars($fornecedor['empresa']); ?></td>
                                                            <td><?php echo htmlspecialchars($fornecedor['email_empresa'] ?? '-'); ?></td>
                                                            <td><?php echo htmlspecialchars($fornecedor['telefone_empresa'] ?? '-'); ?></td>
                                                            <td><?php echo htmlspecialchars($fornecedor['nif_empresa']); ?></td>
                                                            <td>
                                                                <button class="btn btn-outline-primary btn-sm details-btn" 
                                                                        data-fornecedor-id="<?php echo $fornecedor['id']; ?>">Detalhes</button>
                                                                <button class="btn btn-outline-primary btn-sm edit-btn" 
                                                                        data-fornecedor-id="<?php echo $fornecedor['id']; ?>">Editar</button>
                                                                <button class="btn btn-outline-danger btn-sm delete-btn" 
                                                                        data-fornecedor-id="<?php echo $fornecedor['id']; ?>" 
                                                                        data-fornecedor-nome="<?php echo htmlspecialchars($fornecedor['empresa']); ?>">Excluir</button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">Nenhum fornecedor encontrado</td>
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
                                    <h5 class="modal-title" id="detailsModalLabel">Detalhes do Fornecedor</h5>
                                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered">
                                        <tr><th>ID</th><td id="detalhe-id"></td></tr>
                                        <tr><th>Empresa</th><td id="detalhe-empresa"></td></tr>
                                        <tr><th>Email</th><td id="detalhe-email-empresa"></td></tr>
                                        <tr><th>Telefone</th><td id="detalhe-telefone-empresa"></td></tr>
                                        <tr><th>NIF</th><td id="detalhe-nif-empresa"></td></tr>
                                        <tr><th>Morada</th><td id="detalhe-morada-sede"></td></tr>
                                        <tr><th>Código Postal</th><td id="detalhe-codigo-postal"></td></tr>
                                        <tr><th>Distrito</th><td id="detalhe-distrito"></td></tr>
                                        <tr><th>País</th><td id="detalhe-pais"></td></tr>
                                        <tr><th>IBAN</th><td id="detalhe-iban"></td></tr>
                                        <tr><th>Status</th><td id="detalhe-status"></td></tr>
                                        <tr><th>Restaurantes Associados</th><td id="detalhe-restaurantes"></td></tr>
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
                                    <h5 class="modal-title" id="editModalLabel">Editar Fornecedor</h5>
                                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="editFornecedorForm">
                                        <div class="form-group">
                                            <label for="edit-empresa">Empresa</label>
                                            <input type="text" class="form-control" id="edit-empresa" name="empresa" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-email-empresa">Email</label>
                                            <input type="email" class="form-control" id="edit-email-empresa" name="email_empresa">
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-telefone-empresa">Telefone</label>
                                            <input type="text" class="form-control" id="edit-telefone-empresa" name="telefone_empresa">
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-nif-empresa">NIF</label>
                                            <input type="text" class="form-control" id="edit-nif-empresa" name="nif_empresa" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-morada-sede">Morada</label>
                                            <input type="text" class="form-control" id="edit-morada-sede" name="morada_sede">
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-codigo-postal">Código Postal</label>
                                            <input type="text" class="form-control" id="edit-codigo-postal" name="codigo_postal">
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-distrito">Distrito</label>
                                            <input type="text" class="form-control" id="edit-distrito" name="distrito">
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-pais">País</label>
                                            <input type="text" class="form-control" id="edit-pais" name="pais">
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-iban">IBAN</label>
                                            <input type="text" class="form-control" id="edit-iban" name="iban">
                                        </div>
                                        <div class="form-group">
                                            <label for="edit-status">Status</label>
                                            <select class="form-control" id="edit-status" name="status" required>
                                                <option value="pendente">Pendente</option>
                                                <option value="ativo">Ativo</option>
                                                <option value="reprovado">Reprovado</option>
                                            </select>
                                        </div>
                                        <input type="hidden" id="edit-fornecedor-id">
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
                                    <p>Tem certeza que deseja excluir o fornecedor <strong id="delete-fornecedor-nome"></strong>? Esta ação não pode ser desfeita.</p>
                                    <input type="hidden" id="delete-fornecedor-id">
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
            <?php if (!empty($fornecedores)): ?>
                $('#order-listing').DataTable();
            <?php endif; ?>

            $(document).on('click', '.details-btn', function() {
                var fornecedorId = $(this).data('fornecedor-id');
                <?php foreach ($fornecedores as $fornecedor): ?>
                    if (fornecedorId == <?php echo $fornecedor['id']; ?>) {
                        $('#detalhe-id').text(<?php echo json_encode($fornecedor['id']); ?>);
                        $('#detalhe-empresa').text(<?php echo json_encode($fornecedor['empresa']); ?>);
                        $('#detalhe-email-empresa').text(<?php echo json_encode($fornecedor['email_empresa'] ?? '-'); ?>);
                        $('#detalhe-telefone-empresa').text(<?php echo json_encode($fornecedor['telefone_empresa'] ?? '-'); ?>);
                        $('#detalhe-nif-empresa').text(<?php echo json_encode($fornecedor['nif_empresa']); ?>);
                        $('#detalhe-morada-sede').text(<?php echo json_encode($fornecedor['morada_sede'] ?? '-'); ?>);
                        $('#detalhe-codigo-postal').text(<?php echo json_encode($fornecedor['codigo_postal'] ?? '-'); ?>);
                        $('#detalhe-distrito').text(<?php echo json_encode($fornecedor['distrito'] ?? '-'); ?>);
                        $('#detalhe-pais').text(<?php echo json_encode($fornecedor['pais'] ?? '-'); ?>);
                        $('#detalhe-iban').text(<?php echo json_encode($fornecedor['iban'] ?? '-'); ?>);
                        $('#detalhe-status').text(<?php echo json_encode($fornecedor['status']); ?>);
                        <?php
                        $restaurantes = isset($fornecedores_restaurantes[$fornecedor['id']]) ? $fornecedores_restaurantes[$fornecedor['id']] : [];
                        if (empty($restaurantes)) {
                            echo "$('#detalhe-restaurantes').text('Nenhum restaurante associado');";
                        } else {
                            $restaurantes_list = array_map(function($r) { return $r['nome_empresa']; }, $restaurantes);
                            echo "$('#detalhe-restaurantes').text(" . json_encode(implode(', ', $restaurantes_list)) . ");";
                        }
                        ?>
                    }
                <?php endforeach; ?>
                $('#detailsModal').modal('show');
            });

            $(document).on('click', '.edit-btn', function() {
                var fornecedorId = $(this).data('fornecedor-id');
                <?php foreach ($fornecedores as $fornecedor): ?>
                    if (fornecedorId == <?php echo $fornecedor['id']; ?>) {
                        $('#edit-fornecedor-id').val(<?php echo json_encode($fornecedor['id']); ?>);
                        $('#edit-empresa').val(<?php echo json_encode($fornecedor['empresa']); ?>);
                        $('#edit-email-empresa').val(<?php echo json_encode($fornecedor['email_empresa'] ?? ''); ?>);
                        $('#edit-telefone-empresa').val(<?php echo json_encode($fornecedor['telefone_empresa'] ?? ''); ?>);
                        $('#edit-nif-empresa').val(<?php echo json_encode($fornecedor['nif_empresa']); ?>);
                        $('#edit-morada-sede').val(<?php echo json_encode($fornecedor['morada_sede'] ?? ''); ?>);
                        $('#edit-codigo-postal').val(<?php echo json_encode($fornecedor['codigo_postal'] ?? ''); ?>);
                        $('#edit-distrito').val(<?php echo json_encode($fornecedor['distrito'] ?? ''); ?>);
                        $('#edit-pais').val(<?php echo json_encode($fornecedor['pais'] ?? ''); ?>);
                        $('#edit-iban').val(<?php echo json_encode($fornecedor['iban'] ?? ''); ?>);
                        $('#edit-status').val(<?php echo json_encode($fornecedor['status']); ?>);
                    }
                <?php endforeach; ?>
                $('#editModal').modal('show');
            });

            $('#confirmEdit').on('click', function() {
                var fornecedorId = $('#edit-fornecedor-id').val();
                var data = {
                    edit_fornecedor: true,
                    fornecedor_id: fornecedorId,
                    empresa: $('#edit-empresa').val(),
                    email_empresa: $('#edit-email-empresa').val(),
                    telefone_empresa: $('#edit-telefone-empresa').val(),
                    nif_empresa: $('#edit-nif-empresa').val(),
                    morada_sede: $('#edit-morada-sede').val(),
                    codigo_postal: $('#edit-codigo-postal').val(),
                    distrito: $('#edit-distrito').val(),
                    pais: $('#edit-pais').val(),
                    iban: $('#edit-iban').val(),
                    status: $('#edit-status').val()
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
                            alert('Erro ao atualizar fornecedor: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao comunicar com o servidor: ' + error);
                    }
                });
            });

            $(document).on('click', '.delete-btn', function() {
                var fornecedorId = $(this).data('fornecedor-id');
                var fornecedorNome = $(this).data('fornecedor-nome');
                $('#delete-fornecedor-id').val(fornecedorId);
                $('#delete-fornecedor-nome').text(fornecedorNome);
                $('#deleteModal').modal('show');
            });

            $('#confirmDelete').on('click', function() {
                var fornecedorId = $('#delete-fornecedor-id').val();
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { delete_fornecedor: true, fornecedor_id: fornecedorId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#deleteModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Erro ao excluir fornecedor: ' + response.message);
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