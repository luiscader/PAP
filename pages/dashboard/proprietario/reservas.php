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
$success_message = '';
$delete_success = '';
$delete_error = '';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_reserva = $_GET['id'];

    $sql = "SELECT u.id, r.id AS restaurante_id, u.tipo 
            FROM utilizador u
            LEFT JOIN restaurante r ON u.id = r.id_proprietario
            WHERE u.id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $restaurante_id = $user_data['restaurante_id'];
        $tipo_usuario = $user_data['tipo'];
        $stmt->close();

        if ($tipo_usuario === 'proprietario' && $restaurante_id) {

            $sql = "DELETE FROM reserva WHERE id = ? AND id_restaurante = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ii", $id_reserva, $restaurante_id);
                if ($stmt->execute()) {
                    $delete_success = "Reserva eliminada com sucesso!";
                } else {
                    $delete_error = "Erro ao eliminar reserva: " . $conn->error;
                }
                $stmt->close();
            } else {
                $delete_error = "Erro na preparação da query";
            }
        } else {
            $delete_error = "Acesso negado";
        }
    } else {
        $delete_error = "Erro ao verificar usuário";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'create') {
    $nome_completo = $_POST['nome_completo'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $preferencia_contato = $_POST['preferencia_contato'];
    $data_reserva = $_POST['data_reserva'];
    $hora_reserva = $_POST['hora_reserva'];
    $num_pessoas = $_POST['num_pessoas'];
    $id_restaurante = $_POST['id_restaurante'];

    if (empty($nome_completo) || empty($telefone) || empty($email) || empty($data_reserva) || empty($hora_reserva) || empty($num_pessoas)) {
        $message = "Todos os campos são obrigatórios.";
    } else {
        $sql = "INSERT INTO reserva (nome_completo, telefone, email, preferencia_contato, data_reserva, hora_reserva, num_pessoas, id_restaurante) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssssii", $nome_completo, $telefone, $email, $preferencia_contato, $data_reserva, $hora_reserva, $num_pessoas, $id_restaurante);
            if ($stmt->execute()) {
                $success_message = "Reserva criada com sucesso!";
            } else {
                $message = "Erro ao criar reserva: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id_reserva = $_POST['id_reserva'];
    $nome_completo = $_POST['nome_completo'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $preferencia_contato = $_POST['preferencia_contato'];
    $data_reserva = $_POST['data_reserva'];
    $hora_reserva = $_POST['hora_reserva'];
    $num_pessoas = $_POST['num_pessoas'];

    if (empty($nome_completo) || empty($telefone) || empty($email) || empty($data_reserva) || empty($hora_reserva) || empty($num_pessoas)) {
        $message = "Todos os campos são obrigatórios.";
    } else {
        $sql = "UPDATE reserva SET nome_completo = ?, telefone = ?, email = ?, preferencia_contato = ?, data_reserva = ?, hora_reserva = ?, num_pessoas = ? 
                WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssssii", $nome_completo, $telefone, $email, $preferencia_contato, $data_reserva, $hora_reserva, $num_pessoas, $id_reserva);
            if ($stmt->execute()) {
                $success_message = "Reserva atualizada com sucesso!";
            } else {
                $message = "Erro ao atualizar reserva: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

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

$reservas = [];
if ($restaurante_id && $tipo_usuario === 'proprietario') {
    $sql = "SELECT id, nome_completo, telefone, email, data_reserva, hora_reserva, num_pessoas, preferencia_contato 
            FROM reserva 
            WHERE id_restaurante = ? 
            ORDER BY data_reserva, hora_reserva";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $restaurante_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $reservas = $result->fetch_all(MYSQLI_ASSOC);
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

        if ($tipo !== "proprietario") {
            header("Location: ../../geral/index.php");
            exit();
        }
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
    <title>Gestão de Reservas - Restomate</title>

    <link rel="stylesheet" href="../assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../assets/vendors/jvectormap/jquery-jvectormap.css">
    <link rel="stylesheet" href="../assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="../assets/vendors/owl-carousel-2/owl.carousel.min.css">
    <link rel="stylesheet" href="../assets/vendors/owl-carousel-2/owl.theme.default.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/favicon.png" />
    <link rel="stylesheet" href="../assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css">

    <style>
        .badge-pendente { background-color: #ffc107; }
        .badge-confirmada { background-color: #28a745; }
        .badge-cancelada { background-color: #dc3545; }
        input::placeholder, select:invalid {
            color: #999 !important;
            opacity: 1 !important;
        }
        select:invalid {
            color: #999 !important;
        }
        select option {
            color: #000 !important;
        }
    </style>
</head>
<body class="sidebar-fixed">
    <div class="container-scroller">
      <?php include 'sidebar.php'?>
      <div class="page-body-wrapper">
        <?php include 'navbar.php'?>
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="page-header">
                    <h3 class="page-title">Gestão de Reservas</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="reservas.php">Reservas</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><strong>Reservas</strong></li>
                        </ol>
                    </nav>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title">Reservas</h4>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#novaReservaModal">
                                Nova Reserva
                            </button>
                        </div>
                        <?php if ($message): ?>
                            <div class="alert alert-warning"><?php echo $message; ?></div>
                        <?php endif; ?>
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        <?php if ($delete_success): ?>
                            <div class="alert alert-success"><?php echo $delete_success; ?></div>
                        <?php endif; ?>
                        <?php if ($delete_error): ?>
                            <div class="alert alert-danger"><?php echo $delete_error; ?></div>
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table id="order-listing" class="table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nome</th>
                                                <th>Telefone</th>
                                                <th>Email</th>
                                                <th>Data</th>
                                                <th>Hora</th>
                                                <th>Pessoas</th>
                                                <th>Contato</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($reservas)): ?>
                                                <?php foreach($reservas as $row): ?>
                                                <tr>
                                                    <td><?= $row['id'] ?></td>
                                                    <td><?= htmlspecialchars($row['nome_completo']) ?></td>
                                                    <td><?= htmlspecialchars($row['telefone']) ?></td>
                                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                                    <td><?= date('d/m/Y', strtotime($row['data_reserva'])) ?></td>
                                                    <td><?= substr($row['hora_reserva'], 0, 5) ?></td>
                                                    <td><?= $row['num_pessoas'] ?></td>
                                                    <td>
                                                        <?php 
                                                        $badge_class = [
                                                            'telefone' => 'badge-primary',
                                                            'whatsapp' => 'badge-success',
                                                            'email' => 'badge-info'
                                                        ][$row['preferencia_contato']] ?? 'badge-secondary';
                                                        ?>
                                                        <label class="badge <?= $badge_class ?>">
                                                            <?= ucfirst($row['preferencia_contato']) ?>
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-outline-primary btn-sm edit-reserva-btn" 
                                                                data-toggle="modal" data-target="#editarReservaModal"
                                                                data-id="<?= $row['id'] ?>"
                                                                data-nome="<?= htmlspecialchars($row['nome_completo']) ?>"
                                                                data-telefone="<?= htmlspecialchars($row['telefone']) ?>"
                                                                data-email="<?= htmlspecialchars($row['email']) ?>"
                                                                data-preferencia="<?= $row['preferencia_contato'] ?>"
                                                                data-data="<?= $row['data_reserva'] ?>"
                                                                data-hora="<?= $row['hora_reserva'] ?>"
                                                                data-pessoas="<?= $row['num_pessoas'] ?>">
                                                            Editar
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-sm delete-reserva-btn" 
                                                                data-toggle="modal" data-target="#confirmarExclusaoModal"
                                                                data-id="<?= $row['id'] ?>">
                                                            Eliminar
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="9" class="text-center">Nenhuma reserva encontrada</td>
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

    <div class="modal fade" id="novaReservaModal" tabindex="-1" role="dialog" aria-labelledby="novaReservaModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="novaReservaModalLabel">Criar Nova Reserva</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="createReservaForm" action="reservas.php" method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nome_completo">Nome Completo</label>
                            <input type="text" class="form-control" id="nome_completo" name="nome_completo" placeholder="Ex: João Silva" required>
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" placeholder="Ex: 912 345 678" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Ex: joao.silva@email.com" required>
                        </div>
                        <div class="form-group">
                            <label for="preferencia_contato">Preferência de Contato</label>
                            <select class="form-control" id="preferencia_contato" name="preferencia_contato" required>
                                <option value="" disabled selected>Selecione uma opção</option>
                                <option value="telefone">Telefone</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="email">Email</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="data_reserva">Data da Reserva</label>
                            <input type="date" class="form-control" id="data_reserva" name="data_reserva" min="<?php echo date('Y-m-d'); ?>" required>
                            <small class="form-text text-muted">Ex: 2025-03-15</small>
                        </div>
                        <div class="form-group">
                            <label for="hora_reserva">Hora da Reserva</label>
                            <input type="time" class="form-control" id="hora_reserva" name="hora_reserva" required>
                            <small class="form-text text-muted">Ex: 19:00</small>
                        </div>
                        <div class="form-group">
                            <label for="num_pessoas">Número de Pessoas</label>
                            <input type="number" class="form-control" id="num_pessoas" name="num_pessoas" min="1" placeholder="Ex: 4" required>
                        </div>
                        <input type="hidden" name="id_restaurante" value="<?php echo $restaurante_id; ?>">
                        <input type="hidden" name="action" value="create">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Reserva</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editarReservaModal" tabindex="-1" role="dialog" aria-labelledby="editarReservaModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarReservaModalLabel">Editar Reserva</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="editReservaForm" action="reservas.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="edit_id_reserva" name="id_reserva">
                        <div class="form-group">
                            <label for="edit_nome_completo">Nome Completo</label>
                            <input type="text" class="form-control" id="edit_nome_completo" name="nome_completo" placeholder="Ex: João Silva" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_telefone">Telefone</label>
                            <input type="text" class="form-control" id="edit_telefone" name="telefone" placeholder="Ex: 912 345 678" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_email">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" placeholder="Ex: joao.silva@email.com" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_preferencia_contato">Preferência de Contato</label>
                            <select class="form-control" id="edit_preferencia_contato" name="preferencia_contato" required>
                                <option value="" disabled selected>Selecione uma opção</option>
                                <option value="telefone">Telefone</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="email">Email</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_data_reserva">Data da Reserva</label>
                            <input type="date" class="form-control" id="edit_data_reserva" name="data_reserva" min="<?php echo date('Y-m-d'); ?>" required>
                            <small class="form-text text-muted">Ex: 2025-03-15</small>
                        </div>
                        <div class="form-group">
                            <label for="edit_hora_reserva">Hora da Reserva</label>
                            <input type="time" class="form-control" id="edit_hora_reserva" name="hora_reserva" required>
                            <small class="form-text text-muted">Ex: 19:00</small>
                        </div>
                        <div class="form-group">
                            <label for="edit_num_pessoas">Número de Pessoas</label>
                            <input type="number" class="form-control" id="edit_num_pessoas" name="num_pessoas" min="1" placeholder="Ex: 4" required>
                        </div>
                        <input type="hidden" name="action" value="edit">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmarExclusaoModal" tabindex="-1" role="dialog" aria-labelledby="confirmarExclusaoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmarExclusaoModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja eliminar esta reserva? Esta ação não pode ser desfeita.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <a href="#" id="confirmarExclusaoBtn" class="btn btn-danger">Eliminar</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../assets/vendors/datatables.net/jquery.dataTables.js"></script>
    <script src="../assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
    <script src="../assets/js/off-canvas.js"></script>
    <script src="../assets/js/misc.js"></script>
    <script src="../assets/js/settings.js"></script>
    <script src="../assets/js/todolist.js"></script>
    <script src="../assets/js/data-table.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.querySelectorAll('.edit-reserva-btn').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const nome = this.getAttribute('data-nome');
                const telefone = this.getAttribute('data-telefone');
                const email = this.getAttribute('data-email');
                const preferencia = this.getAttribute('data-preferencia');
                const data = this.getAttribute('data-data');
                const hora = this.getAttribute('data-hora');
                const pessoas = this.getAttribute('data-pessoas');

                document.getElementById('edit_id_reserva').value = id;
                document.getElementById('edit_nome_completo').value = nome;
                document.getElementById('edit_telefone').value = telefone;
                document.getElementById('edit_email').value = email;
                document.getElementById('edit_preferencia_contato').value = preferencia;
                document.getElementById('edit_data_reserva').value = data;
                document.getElementById('edit_hora_reserva').value = hora;
                document.getElementById('edit_num_pessoas').value = pessoas;

                $('#editarReservaModal').modal('show');
            });
        });

        document.querySelectorAll('.delete-reserva-btn').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const deleteUrl = `reservas.php?action=delete&id=${id}`;
                document.getElementById('confirmarExclusaoBtn').setAttribute('href', deleteUrl);
            });
        });

        $(document).ready(function() {
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }

            <?php if ($success_message || $delete_success): ?>
                $('#novaReservaModal').modal('hide');
                $('#editarReservaModal').modal('hide');
                $('#confirmarExclusaoModal').modal('hide');
                $('#createReservaForm')[0].reset();
                $('#editReservaForm')[0].reset();
            <?php endif; ?>

            $('#novaReservaModal, #editarReservaModal, #confirmarExclusaoModal').on('hidden.bs.modal', function () {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
            });
        });
    </script>
</body>
</html>