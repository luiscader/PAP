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
    if (isset($_POST['delete_restaurante']) && isset($_POST['restaurante_id'])) {
        $restaurante_id = $_POST['restaurante_id'];
        $sql = "DELETE FROM restaurante WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $restaurante_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao excluir restaurante: ' . $conn->error]);
            }
            $stmt->close();
        }
        $conn->close();
        exit();
    } elseif (isset($_POST['edit_restaurante']) && isset($_POST['restaurante_id'])) {
        $restaurante_id = $_POST['restaurante_id'];
        $nome_empresa = $_POST['nome_empresa'];
        $email = $_POST['email'];
        $telefone = $_POST['telefone'] ?: null;
        $nif = $_POST['nif'];
        $morada = $_POST['morada'] ?: null;
        $codigo_postal = $_POST['codigo_postal'] ?: null;
        $distrito = $_POST['distrito'] ?: null;
        $pais = $_POST['pais'] ?: null;
        $iban = $_POST['iban'] ?: null;

        $sql = "UPDATE restaurante SET nome_empresa = ?, email = ?, telefone = ?, nif = ?, morada = ?, codigo_postal = ?, distrito = ?, pais = ?, iban = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssssssssi", $nome_empresa, $email, $telefone, $nif, $morada, $codigo_postal, $distrito, $pais, $iban, $restaurante_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar restaurante: ' . $conn->error]);
            }
            $stmt->close();
        }
        $conn->close();
        exit();
    }
}

$sql = "SELECT r.id, r.nome_empresa, r.email_contato AS email, r.telefone, r.nif, r.morada, r.codigo_postal, r.distrito, r.pais, r.iban,
               u.id AS id_proprietario, u.nome AS nome_proprietario, u.email AS email_proprietario, u.telefone AS telefone_proprietario
        FROM restaurante r
        LEFT JOIN utilizador u ON r.id_proprietario = u.id AND u.tipo = 'proprietario'";
if ($stmt = $conn->prepare($sql)) {
    $stmt->execute();
    $result = $stmt->get_result();
    $restaurantes = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$funcionarios_restaurantes = [];
$sql = "SELECT f.id_restaurante, u.id AS id_funcionario, u.nome AS nome_funcionario, f.cargo
        FROM funcionarios f
        JOIN utilizador u ON f.id_utilizador = u.id AND u.tipo = 'associado'";
if ($stmt = $conn->prepare($sql)) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $funcionarios_restaurantes[$row['id_restaurante']][] = [
            'id_funcionario' => $row['id_funcionario'],
            'nome_funcionario' => $row['nome_funcionario'],
            'cargo' => $row['cargo']
        ];
    }
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
    <title>Gestão de Restaurantes - Restomate</title>
    
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
                        <h3 class="page-title">Gestão de Restaurantes</h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="restaurantes.php">Restaurantes</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><strong>Restaurantes</strong></li>
                            </ol>
                        </nav>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="card-title">Restaurantes</h4>
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
                                                    <th>Nome Empresa</th>
                                                    <th>Email</th>
                                                    <th>Telefone</th>
                                                    <th>NIF</th>
                                                    <th>Proprietário</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($restaurantes)): ?>
                                                    <?php foreach ($restaurantes as $restaurante): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($restaurante['id']); ?></td>
                                                            <td><?php echo htmlspecialchars($restaurante['nome_empresa']); ?></td>
                                                            <td><?php echo htmlspecialchars($restaurante['email']); ?></td>
                                                            <td><?php echo htmlspecialchars($restaurante['telefone'] ?? '-'); ?></td>
                                                            <td><?php echo htmlspecialchars($restaurante['nif']); ?></td>
                                                            <td><?php echo htmlspecialchars($restaurante['nome_proprietario'] ?? 'Sem proprietário'); ?></td>
                                                            <td>
                                                                <button class="btn btn-outline-primary btn-sm details-btn" 
                                                                        data-restaurante-id="<?php echo $restaurante['id']; ?>">Detalhes</button>
                                                                <button class="btn btn-outline-primary btn-sm edit-btn" 
                                                                        data-restaurante-id="<?php echo $restaurante['id']; ?>">Editar</button>
                                                                <button class="btn btn-outline-danger btn-sm delete-btn" 
                                                                        data-restaurante-id="<?php echo $restaurante['id']; ?>" 
                                                                        data-restaurante-nome="<?php echo htmlspecialchars($restaurante['nome_empresa']); ?>">Excluir</button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">Nenhum restaurante encontrado</td>
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
                                    <h5 class="modal-title" id="detailsModalLabel">Detalhes do Restaurante</h5>
                                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <h6>Informações do Restaurante</h6>
                                    <table class="table table-bordered">
                                        <tr><th>ID</th><td id="detalhe-id"></td></tr>
                                        <tr><th>Nome Empresa</th><td id="detalhe-nome-empresa"></td></tr>
                                        <tr><th>Email</th><td id="detalhe-email"></td></tr>
                                        <tr><th>Telefone</th><td id="detalhe-telefone"></td></tr>
                                        <tr><th>NIF</th><td id="detalhe-nif"></td></tr>
                                        <tr><th>Morada</th><td id="detalhe-morada"></td></tr>
                                        <tr><th>Código Postal</th><td id="detalhe-codigo-postal"></td></tr>
                                        <tr><th>Distrito</th><td id="detalhe-distrito"></td></tr>
                                        <tr><th>País</th><td id="detalhe-pais"></td></tr>
                                        <tr><th>IBAN</th><td id="detalhe-iban"></td></tr>
                                    </table> <br>
                                    <h6>Proprietário</h6>
                                    <table class="table table-bordered">
                                        <tr><th>ID</th><td id="detalhe-id-proprietario"></td></tr>
                                        <tr><th>Nome</th><td id="detalhe-nome-proprietario"></td></tr>
                                        <tr><th>Email</th><td id="detalhe-email-proprietario"></td></tr>
                                        <tr><th>Telefone</th><td id="detalhe-telefone-proprietario"></td></tr>
                                    </table><br>
                                    <h6>Funcionários</h6>
                                    <table class="table table-bordered" id="detalhe-funcionarios">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nome</th>
                                                <th>Cargo</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
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
                                    <h5 class="modal-title" id="editModalLabel">Editar Restaurante</h5>
                                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="editRestauranteForm">
                                        <div class="form-group">
                                            <label for="edit-nome-empresa">Nome Empresa</label>
                                            <input type="text" class="form-control" id="edit-nome-empresa" name="nome_empresa" required>
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
                                            <label for="edit-nif">NIF</label>
                                            <input type="text" class="form-control" id="edit-nif" name="nif" required>
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
                                        <input type="hidden" id="edit-restaurante-id">
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
                                    <p>Tem certeza que deseja excluir o restaurante <strong id="delete-restaurante-nome"></strong>? Esta ação não pode ser desfeita.</p>
                                    <input type="hidden" id="delete-restaurante-id">
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
            <?php if (!empty($restaurantes)): ?>
                $('#order-listing').DataTable();
            <?php endif; ?>

            $(document).on('click', '.details-btn', function() {
                var restauranteId = $(this).data('restaurante-id');
                <?php foreach ($restaurantes as $restaurante): ?>
                    if (restauranteId == <?php echo $restaurante['id']; ?>) {
                        $('#detalhe-id').text(<?php echo json_encode($restaurante['id']); ?>);
                        $('#detalhe-nome-empresa').text(<?php echo json_encode($restaurante['nome_empresa']); ?>);
                        $('#detalhe-email').text(<?php echo json_encode($restaurante['email']); ?>);
                        $('#detalhe-telefone').text(<?php echo json_encode($restaurante['telefone'] ?? '-'); ?>);
                        $('#detalhe-nif').text(<?php echo json_encode($restaurante['nif']); ?>);
                        $('#detalhe-morada').text(<?php echo json_encode($restaurante['morada'] ?? '-'); ?>);
                        $('#detalhe-codigo-postal').text(<?php echo json_encode($restaurante['codigo_postal'] ?? '-'); ?>);
                        $('#detalhe-distrito').text(<?php echo json_encode($restaurante['distrito'] ?? '-'); ?>);
                        $('#detalhe-pais').text(<?php echo json_encode($restaurante['pais'] ?? '-'); ?>);
                        $('#detalhe-iban').text(<?php echo json_encode($restaurante['iban'] ?? '-'); ?>);
                        $('#detalhe-id-proprietario').text(<?php echo json_encode($restaurante['id_proprietario'] ?? '-'); ?>);
                        $('#detalhe-nome-proprietario').text(<?php echo json_encode($restaurante['nome_proprietario'] ?? 'Sem proprietário'); ?>);
                        $('#detalhe-email-proprietario').text(<?php echo json_encode($restaurante['email_proprietario'] ?? '-'); ?>);
                        $('#detalhe-telefone-proprietario').text(<?php echo json_encode($restaurante['telefone_proprietario'] ?? '-'); ?>);

                        var funcionariosTable = $('#detalhe-funcionarios tbody');
                        funcionariosTable.empty();
                        <?php
                        $funcionarios = isset($funcionarios_restaurantes[$restaurante['id']]) ? $funcionarios_restaurantes[$restaurante['id']] : [];
                        if (empty($funcionarios)) {
                            echo "funcionariosTable.append('<tr><td colspan=\"3\">Nenhum funcionário associado</td></tr>');";
                        } else {
                            foreach ($funcionarios as $funcionario) {
                                $cargo_class = ($funcionario['cargo'] === '-') ? 'badge-secondary' : ($cargo_cores[$funcionario['cargo']] ?? 'badge-secondary');
                                echo "funcionariosTable.append('<tr><td>" . htmlspecialchars($funcionario['id_funcionario']) . "</td><td>" . htmlspecialchars($funcionario['nome_funcionario']) . "</td><td><span class=\"badge " . $cargo_class . "\">" . htmlspecialchars($funcionario['cargo']) . "</span></td></tr>');";
                            }
                        }
                        ?>
                    }
                <?php endforeach; ?>
                $('#detailsModal').modal('show');
            });

            $(document).on('click', '.edit-btn', function() {
                var restauranteId = $(this).data('restaurante-id');
                <?php foreach ($restaurantes as $restaurante): ?>
                    if (restauranteId == <?php echo $restaurante['id']; ?>) {
                        $('#edit-restaurante-id').val(<?php echo json_encode($restaurante['id']); ?>);
                        $('#edit-nome-empresa').val(<?php echo json_encode($restaurante['nome_empresa']); ?>);
                        $('#edit-email').val(<?php echo json_encode($restaurante['email']); ?>);
                        $('#edit-telefone').val(<?php echo json_encode($restaurante['telefone'] ?? ''); ?>);
                        $('#edit-nif').val(<?php echo json_encode($restaurante['nif']); ?>);
                        $('#edit-morada').val(<?php echo json_encode($restaurante['morada'] ?? ''); ?>);
                        $('#edit-codigo-postal').val(<?php echo json_encode($restaurante['codigo_postal'] ?? ''); ?>);
                        $('#edit-distrito').val(<?php echo json_encode($restaurante['distrito'] ?? ''); ?>);
                        $('#edit-pais').val(<?php echo json_encode($restaurante['pais'] ?? ''); ?>);
                        $('#edit-iban').val(<?php echo json_encode($restaurante['iban'] ?? ''); ?>);
                    }
                <?php endforeach; ?>
                $('#editModal').modal('show');
            });

            $('#confirmEdit').on('click', function() {
                var restauranteId = $('#edit-restaurante-id').val();
                var data = {
                    edit_restaurante: true,
                    restaurante_id: restauranteId,
                    nome_empresa: $('#edit-nome-empresa').val(),
                    email: $('#edit-email').val(),
                    telefone: $('#edit-telefone').val(),
                    nif: $('#edit-nif').val(),
                    morada: $('#edit-morada').val(),
                    codigo_postal: $('#edit-codigo-postal').val(),
                    distrito: $('#edit-distrito').val(),
                    pais: $('#edit-pais').val(),
                    iban: $('#edit-iban').val()
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
                            alert('Erro ao atualizar restaurante: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao comunicar com o servidor: ' + error);
                    }
                });
            });

            $(document).on('click', '.delete-btn', function() {
                var restauranteId = $(this).data('restaurante-id');
                var restauranteNome = $(this).data('restaurante-nome');
                $('#delete-restaurante-id').val(restauranteId);
                $('#delete-restaurante-nome').text(restauranteNome);
                $('#deleteModal').modal('show');
            });


            $('#confirmDelete').on('click', function() {
                var restauranteId = $('#delete-restaurante-id').val();
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { delete_restaurante: true, restaurante_id: restauranteId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#deleteModal').modal('hide');
                            location.reload();
                        } else {
                            alert('Erro ao excluir restaurante: ' + response.message);
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