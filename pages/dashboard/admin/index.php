<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../geral/login.php");
    exit();
}

$id_cliente = $_SESSION['id'];
$message = '';

$sql = "SELECT id, nome, email, senha, tipo FROM Utilizador WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_cliente);
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


$sql = "SELECT COUNT(*) as total FROM Utilizador";
$result = $conn->query($sql);
$usuarios = $result->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) as total FROM Utilizador WHERE tipo = 'associado'";
$result = $conn->query($sql);
$funcionarios = $result->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) as total FROM restaurante";
$result = $conn->query($sql);
$restaurantes = $result->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) as total FROM fornecedor";
$result = $conn->query($sql);
$fornecedores = $result->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) as total FROM restaurante WHERE status = 'pendente'";
$result = $conn->query($sql);
$restaurantes_aprovar = $result->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) as total FROM fornecedor WHERE status = 'pendente'";
$result = $conn->query($sql);
$fornecedores_aprovar = $result->fetch_assoc()['total'];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}


$sql_restaurantes = "SELECT id, nome_empresa, nif, email_contato, status, criado_em 
                     FROM restaurante 
                     ORDER BY criado_em DESC 
                     LIMIT 5";
$result_restaurantes = $conn->query($sql_restaurantes);
$ultimos_restaurantes = [];
if ($result_restaurantes->num_rows > 0) {
    while ($row = $result_restaurantes->fetch_assoc()) {
        $ultimos_restaurantes[] = $row;
    }
}


$sql_fornecedores = "SELECT id, empresa, email_empresa, nif_empresa, status 
                     FROM fornecedor 
                     ORDER BY id DESC 
                     LIMIT 5";
$result_fornecedores = $conn->query($sql_fornecedores);
$ultimos_fornecedores = [];
if ($result_fornecedores->num_rows > 0) {
    while ($row = $result_fornecedores->fetch_assoc()) {
        $ultimos_fornecedores[] = $row;
    }
}

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
    <link rel="stylesheet" href="css/style.css">
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
              <div class="col-xl-2 col-sm-6 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center">
                          <h3 class="mb-0"><?php echo $usuarios; ?></h3>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="icon icon-box-success">
                          <span class="mdi mdi-account-multiple icon-item"></span>
                        </div>
                      </div>
                    </div>
                    <h4 class="text-muted font-weight-normal">Utilizadores</h4>
                  </div>
                </div>
              </div>
              <div class="col-xl-2 col-sm-6 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                          <h3 class="mb-0"><?php echo $funcionarios; ?></h3>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="icon icon-box-success">
                          <span class="mdi mdi-account-group icon-item"></span>
                        </div>
                      </div>
                    </div>
                    <h4 class="text-muted font-weight-normal">Funcionários</h4>
                  </div>
                </div>
              </div>
              <div class="col-xl-2 col-sm-6 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                          <h3 class="mb-0"><?php echo $restaurantes; ?></h3>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="icon icon-box-success">
                          <span class="mdi mdi-store icon-item"></span>
                        </div>
                      </div>
                    </div>
                    <h4 class="text-muted font-weight-normal">Restaurantes</h4>
                  </div>
                </div>
              </div>
              <div class="col-xl-2 col-sm-6 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                          <h3 class="mb-0"><?php echo $fornecedores; ?></h3>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="icon icon-box-danger">
                          <span class="mdi mdi-truck icon-item"></span>
                        </div>
                      </div>
                    </div>
                    <h4 class="text-muted font-weight-normal">Fornecedores</h4>
                  </div>
                </div>
              </div>
              <div class="col-xl-2 col-sm-6 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                          <h3 class="mb-0"><?php echo $restaurantes_aprovar; ?></h3>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="icon icon-box-warning">
                          <span class="mdi mdi-store-plus icon-item"></span>
                        </div>
                      </div>
                    </div>
                    <h4 class="text-muted font-weight-normal">Restaurantes por aprovar</h4>
                  </div>
                </div>
              </div>
              <div class="col-xl-2 col-sm-6 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                          <h3 class="mb-0"><?php echo $fornecedores_aprovar; ?></h3>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="icon icon-box-warning">
                          <span class="mdi mdi-truck-plus icon-item"></span>
                        </div>
                      </div>
                    </div>
                    <h4 class="text-muted font-weight-normal">Fornecedores por aprovar</h4>
                  </div>
                </div>
              </div>
            </div>
            <div class="row ">
              <div class="col-12 grid-margin">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Registros de Restaurantes</h4>
                    <div class="table-responsive">
                      <table class="table">
                        <thead>
                          <tr>
                            <th>ID</th>
                            <th>Nome da Empresa</th>
                            <th>NIF</th>
                            <th>Email de Contato</th>
                            <th>Status</th>
                            <th>Data de Criação</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($ultimos_restaurantes as $restaurante): ?>
                            <tr>
                              <td><?php echo $restaurante['id']; ?></td>
                              <td><?php echo $restaurante['nome_empresa']; ?></td>
                              <td><?php echo $restaurante['nif']; ?></td>
                              <td><?php echo $restaurante['email_contato']; ?></td>
                              <td>
                                <div class="badge badge-outline-<?php echo $restaurante['status'] == 'ativo' ? 'success' : ($restaurante['status'] == 'pendente' ? 'warning' : 'danger'); ?>">
                                  <?php echo ucfirst($restaurante['status']); ?>
                                </div>
                              </td>
                              <td><?php echo date('d/m/Y H:i', strtotime($restaurante['criado_em'])); ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row ">
              <div class="col-12 grid-margin">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Registros de Fornecedores</h4>
                    <div class="table-responsive">
                      <table class="table">
                        <thead>
                          <tr>
                            <th>ID</th>
                            <th>Empresa</th>
                            <th>Email</th>
                            <th>NIF</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($ultimos_fornecedores as $fornecedor): ?>
                            <tr>
                              <td><?php echo $fornecedor['id']; ?></td>
                              <td><?php echo $fornecedor['empresa']; ?></td>
                              <td><?php echo $fornecedor['email_empresa']; ?></td>
                              <td><?php echo $fornecedor['nif_empresa']; ?></td>
                              <td>
                                <div class="badge badge-outline-<?php echo $fornecedor['status'] == 'ativo' ? 'success' : ($fornecedor['status'] == 'pendente' ? 'warning' : 'danger'); ?>">
                                  <?php echo ucfirst($fornecedor['status']); ?>
                                </div>
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
                                <td class="text-end">1500</td>
                                <td class="text-end font-weight-medium">56.35%</td>
                              </tr>
                              <tr>
                                <td><i class="flag-icon flag-icon-de"></i></td>
                                <td>Germany</td>
                                <td class="text-end">800</td>
                                <td class="text-end font-weight-medium">33.25%</td>
                              </tr>
                              <tr>
                                <td><i class="flag-icon flag-icon-au"></i></td>
                                <td>Australia</td>
                                <td class="text-end">760</td>
                                <td class="text-end font-weight-medium">15.45%</td>
                              </tr>
                              <tr>
                                <td><i class="flag-icon flag-icon-gb"></i></td>
                                <td>United Kingdom</td>
                                <td class="text-end">450</td>
                                <td class="text-end font-weight-medium">25.00%</td>
                              </tr>
                              <tr>
                                <td><i class="flag-icon flag-icon-ro"></i></td>
                                <td>Romania</td>
                                <td class="text-end">620</td>
                                <td class="text-end font-weight-medium">10.25%</td>
                              </tr>
                              <tr>
                                <td><i class="flag-icon flag-icon-br"></i></td>
                                <td>Brasil</td>
                                <td class="text-end">230</td>
                                <td class="text-end font-weight-medium">75.00%</td>
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