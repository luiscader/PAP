<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../geral/login.php");
    exit();
}

$id_usuario = $_SESSION['id'];
$message = '';

$sql = "SELECT tipo, id_fornecedor FROM utilizador WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $tipo_usuario = $row['tipo'];
        $id_fornecedor = $row['id_fornecedor'];
    } else {
        echo "Usuário não encontrado.";
        exit();
    }
    $stmt->close();
} else {
    echo "Erro na preparação da consulta.";
    exit();
}

if ($tipo_usuario !== 'fornecedor') {
    $_SESSION['message'] = "Acesso negado. Esta página é exclusiva para fornecedores.";
    header("Location: ../../geral/index.php");
    exit();
}

$fornecedor_data = null;
$sql = "SELECT * FROM fornecedor WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_fornecedor);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $fornecedor_data = $result->fetch_assoc();
    } else {
        $message = "Fornecedor não encontrado.";
    }
    $stmt->close();
}

$sql = "SELECT id, nome, email, senha, tipo FROM utilizador WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $nome, $email, $senha, $tipo);
    if ($stmt->num_rows > 0) {
        $stmt->fetch();
    } else {
        echo "Usuário não encontrado.";
        exit();
    }
    $stmt->close();
}

$sql = "SELECT COUNT(*) as total_restaurantes 
        FROM restaurante r
        INNER JOIN restaurante_fornecedor rf ON r.id = rf.id_restaurante
        WHERE rf.id_fornecedor = ?";
$total_restaurantes = 0;
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_fornecedor);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $total_restaurantes = $row['total_restaurantes'];
    }
    $stmt->close();
}

$restaurantes = [];
$sql = "SELECT r.id, r.nome_empresa, r.email_contato, r.telefone, r.morada, r.criado_em 
        FROM restaurante r
        INNER JOIN restaurante_fornecedor rf ON r.id = rf.id_restaurante
        WHERE rf.id_fornecedor = ?
        ORDER BY r.criado_em DESC
        LIMIT 5";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_fornecedor);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $restaurantes[] = $row;
    }
    $stmt->close();
}

$encomendas = 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Restomate</title>
    <link rel="stylesheet" href="../assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../assets/vendors/jvectormap/jquery-jvectormap.css">
    <link rel="stylesheet" href="../assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="../assets/vendors/owl-carousel-2/owl.carousel.min.css">
    <link rel="stylesheet" href="../assets/vendors/owl-carousel-2/owl.theme.default.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/favicon.png" />
</head>
<body class="sidebar-fixed">
    <div class="container-scroller">
      <?php include 'sidebar.php' ?>
      <div class="page-body-wrapper">
        <?php include 'navbar.php' ?>
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-xl-6 col-sm-6 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                          <h3 class="mb-1"><?php echo $total_restaurantes; ?></h3>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="icon icon-box-success ">
                          <span class="mdi mdi-arrow-top-right icon-item"></span>
                        </div>
                      </div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Restaurantes</h6>
                  </div>
                </div>
              </div>
              <div class="col-xl-6 col-sm-6 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                          <h3 class="mb-0"><?php echo $encomendas; ?></h3>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="icon icon-box-danger">
                          <span class="mdi mdi-arrow-bottom-left icon-item"></span>
                        </div>
                      </div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Encomendas</h6>
                  </div>
                </div>
              </div>
            </div>
            <div class="row ">
              <div class="col-12 grid-margin">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Restaurantes Associados</h4>
                    <div class="table-responsive">
                      <table class="table">
                        <thead>
                          <tr>
                            <th>
                              <div class="form-check form-check-muted m-0">
                                <label class="form-check-label">
                                  <input type="checkbox" class="form-check-input" id="check-all">
                                </label>
                              </div>
                            </th>
                            <th>Nome do Restaurante</th>
                            <th>Email de Contato</th>
                            <th>Telefone</th>
                            <th>Morada</th>
                            <th>Data de Associação</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($restaurantes as $restaurante): ?>
                          <tr>
                            <td>
                              <div class="form-check form-check-muted m-0">
                                <label class="form-check-label">
                                  <input type="checkbox" class="form-check-input">
                                </label>
                              </div>
                            </td>
                            <td><?php echo htmlspecialchars($restaurante['nome_empresa']); ?></td>
                            <td><?php echo htmlspecialchars($restaurante['email_contato']); ?></td>
                            <td><?php echo htmlspecialchars($restaurante['telefone']); ?></td>
                            <td><?php echo htmlspecialchars($restaurante['morada']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($restaurante['criado_em'])); ?></td>
                          </tr>
                          <?php endforeach; ?>
                          <?php if (empty($restaurantes)): ?>
                          <tr>
                            <td colspan="6">Nenhum restaurante associado encontrado.</td>
                          </tr>
                          <?php endif; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Visitors by Countries</h4>
                    <div class="row">
                      <div class="col-md-5">
                        <div class="table-responsive">
                          <table class="table">
                            <tbody>
                              <tr>
                                <td><i class="flag-icon flag-icon-us"></i></td>
                                <td>USA</td>
                                <td class="text-end"> 1500 </td>
                                <td class="text-end font-weight-medium"> 56.35% </td>
                              </tr>
                              <tr>
                                <td><i class="flag-icon flag-icon-de"></i></td>
                                <td>Germany</td>
                                <td class="text-end"> 800 </td>
                                <td class="text-end font-weight-medium"> 33.25% </td>
                              </tr>
                              <tr>
                                <td><i class="flag-icon flag-icon-au"></i></td>
                                <td>Australia</td>
                                <td class="text-end"> 760 </td>
                                <td class="text-end font-weight-medium"> 15.45% </td>
                              </tr>
                              <tr>
                                <td><i class="flag-icon flag-icon-gb"></i></td>
                                <td>United Kingdom</td>
                                <td class="text-end"> 450 </td>
                                <td class="text-end font-weight-medium"> 25.00% </td>
                              </tr>
                              <tr>
                                <td><i class="flag-icon flag-icon-ro"></i></td>
                                <td>Romania</td>
                                <td class="text-end"> 620 </td>
                                <td class="text-end font-weight-medium"> 10.25% </td>
                              </tr>
                              <tr>
                                <td><i class="flag-icon flag-icon-br"></i></td>
                                <td>Brasil</td>
                                <td class="text-end"> 230 </td>
                                <td class="text-end font-weight-medium"> 75.00% </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                      <div class="col-md-7">
                        <div id="audience-map" class="vector-map"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <footer class="footer">
            <div class="d-sm-flex justify-content-center justify-content-sm-between">
              <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2023 <a href="https://www.bootstrapdash.com/" target="_blank">BootstrapDash</a>. All rights reserved.</span>
              <span class="text-muted float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Hand-crafted & made with <i class="mdi mdi-heart text-danger"></i></span>
            </div>
          </footer>
        </div>
      </div>
    </div>
    <script src="../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../assets/vendors/chart.js/chart.umd.js"></script>
    <script src="../assets/vendors/progressbar.js/progressbar.min.js"></script>
    <script src="../assets/vendors/jvectormap/jquery-jvectormap.min.js"></script>
    <script src="../assets/vendors/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
    <script src="../assets/vendors/owl-carousel-2/owl.carousel.min.js"></script>
    <script src="../assets/js/off-canvas.js"></script>
    <script src="../assets/js/hoverable-collapse.js"></script>
    <script src="../assets/js/misc.js"></script>
    <script src="../assets/js/settings.js"></script>
    <script src="../assets/js/todolist.js"></script>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>