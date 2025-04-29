<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../../geral/login.php");
    exit();
}

$id_cliente = $_SESSION['id'];
$message = '';

$restaurant_data = null;
$sql = "SELECT * FROM restaurante WHERE id_proprietario = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $restaurant_data = $result->fetch_assoc();
        $id_restaurante = $restaurant_data['id']; 
    } else {
        $message = "Restaurante não encontrado.";
    }
    $stmt->close();
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

$sql = "SELECT COUNT(*) as total FROM pedidos WHERE id_restaurante = ? AND status = 'Pronto'";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_restaurante);
    $stmt->execute();
    $result = $stmt->get_result();
    $pratos_prontos = $result->fetch_assoc()['total'];
    $stmt->close();
}

$sql = "SELECT COUNT(*) as total FROM pedidos WHERE id_restaurante = ? AND status = 'Em Preparacao'";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_restaurante);
    $stmt->execute();
    $result = $stmt->get_result();
    $pratos_a_preparar = $result->fetch_assoc()['total'];
    $stmt->close();
}

$sql = "SELECT COUNT(*) as total FROM pedidos WHERE id_restaurante = ? AND status IN ('Pendente', 'Em Preparacao', 'Pronto')";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_restaurante);
    $stmt->execute();
    $result = $stmt->get_result();
    $pedidos_ativos = $result->fetch_assoc()['total'];
    $stmt->close();
}

$sql = "SELECT COUNT(*) as total FROM reserva WHERE id_restaurante = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_restaurante);
    $stmt->execute();
    $result = $stmt->get_result();
    $reservas_mesa = $result->fetch_assoc()['total'];
    $stmt->close();
}

$sql = "SELECT p.id_pedido, p.id_mesa, pr.nome AS prato_nome, p.quantidade, p.preco_total, p.data_pedido, p.status 
        FROM pedidos p 
        LEFT JOIN pratos pr ON p.id_prato = pr.id 
        WHERE p.id_restaurante = ? 
        ORDER BY p.data_pedido DESC 
        LIMIT 5";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_restaurante);
    $stmt->execute();
    $result = $stmt->get_result();
    $ultimos_pedidos = [];
    while ($row = $result->fetch_assoc()) {
        $ultimos_pedidos[] = $row;
    }
    $stmt->close();
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
              <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center">
                          <h3 class="mb-0"><?php echo $pratos_prontos; ?></h3>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="icon icon-box-success ">
                          <span class="mdi mdi-silverware-fork-knife icon-item"></span>
                        </div>
                      </div>
                    </div>
                    <h4 class="text-muted font-weight-normal">Pratos prontos</h4>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                          <h3 class="mb-0"><?php echo $pratos_a_preparar; ?></h3>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="icon icon-box-success">
                          <span class="mdi mdi-chef-hat icon-item"></span>
                        </div>
                      </div>
                    </div>
                    <h4 class="text-muted font-weight-normal">Pratos a preparar</h4>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                          <h3 class="mb-0"><?php echo $pedidos_ativos; ?></h3>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="icon icon-box-danger">
                          <span class="mdi mdi-clipboard-text icon-item"></span>
                        </div>
                      </div>
                    </div>
                    <h4 class="text-muted font-weight-normal">Pedidos ativos</h4>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                          <h3 class="mb-0"><?php echo $reservas_mesa; ?></h3>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="icon icon-box-success ">
                          <span class="mdi mdi-calendar-check icon-item"></span>
                        </div>
                      </div>
                    </div>
                    <h4 class="text-muted font-weight-normal">Reservas de mesa</h4>
                  </div>
                </div>
              </div>
            </div>
            <div class="row ">
              <div class="col-12 grid-margin">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Pedidos</h4>
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
                            <th>ID Pedido</th>
                            <th>Mesa</th>
                            <th>Prato</th>
                            <th>Quantidade</th>
                            <th>Preço Total</th>
                            <th>Data do Pedido</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (!empty($ultimos_pedidos)): ?>
                            <?php foreach ($ultimos_pedidos as $pedido): ?>
                              <tr>
                                <td>
                                  <div class="form-check form-check-muted m-0">
                                    <label class="form-check-label">
                                      <input type="checkbox" class="form-check-input">
                                    </label>
                                  </div>
                                </td>
                                <td><?php echo $pedido['id_pedido']; ?></td>
                                <td><?php echo $pedido['id_mesa']; ?></td>
                                <td><?php echo $pedido['prato_nome']; ?></td>
                                <td><?php echo $pedido['quantidade']; ?></td>
                                <td><?php echo number_format($pedido['preco_total'], 2, ',', '.'); ?> €</td>
                                <td><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>
                                <td>
                                  <div class="badge badge-outline-<?php 
                                    echo $pedido['status'] == 'Pronto' || $pedido['status'] == 'Entregue' || $pedido['status'] == 'Pago' ? 'success' : 
                                        ($pedido['status'] == 'Pendente' || $pedido['status'] == 'Em Preparacao' ? 'warning' : 'danger'); ?>">
                                    <?php echo ucfirst($pedido['status']); ?>
                                  </div>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          <?php else: ?>
                            <tr>
                              <td colspan="8" class="text-center">Nenhum pedido encontrado.</td>
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
                                <td>
                                  <i class="flag-icon flag-icon-us"></i>
                                </td>
                                <td>USA</td>
                                <td class="text-end"> 1500 </td>
                                <td class="text-end font-weight-medium"> 56.35% </td>
                              </tr>
                              <tr>
                                <td>
                                  <i class="flag-icon flag-icon-de"></i>
                                </td>
                                <td>Germany</td>
                                <td class="text-end"> 800 </td>
                                <td class="text-end font-weight-medium"> 33.25% </td>
                              </tr>
                              <tr>
                                <td>
                                  <i class="flag-icon flag-icon-au"></i>
                                </td>
                                <td>Australia</td>
                                <td class="text-end"> 760 </td>
                                <td class="text-end font-weight-medium"> 15.45% </td>
                              </tr>
                              <tr>
                                <td>
                                  <i class="flag-icon flag-icon-gb"></i>
                                </td>
                                <td>United Kingdom</td>
                                <td class="text-end"> 450 </td>
                                <td class="text-end font-weight-medium"> 25.00% </td>
                              </tr>
                              <tr>
                                <td>
                                  <i class="flag-icon flag-icon-ro"></i>
                                </td>
                                <td>Romania</td>
                                <td class="text-end"> 620 </td>
                                <td class="text-end font-weight-medium"> 10.25% </td>
                              </tr>
                              <tr>
                                <td>
                                  <i class="flag-icon flag-icon-br"></i>
                                </td>
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
    <script src="../assets/js/todolist.js"></script>
    <script src="../assets/js/dashboard.js"></script>
  </body>
</html>