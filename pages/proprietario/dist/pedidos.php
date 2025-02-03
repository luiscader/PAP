<?php
include 'C:/wamp64/www/PAP/includes/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$id_cliente = $_SESSION['id'];
$message = '';


// Fetch user data
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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>restomate Admin</title>
    
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.css">
    <link rel="stylesheet" href="assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css">
    
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/images/favicon.png" />
</head>
<body>
    <div class="container-scroller">
        <?php include 'navbar.php'; ?>
        <?php include 'sidebar.php'; ?>
        
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="page-header">
              <h3 class="page-title"> Orders </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Sample pages</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Orders</li>
                </ol>
              </nav>
            </div>
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Orders</h4>
                    <div class="row grid-margin">
                      <div class="col-12">
                        <div class="alert alert-warning" role="alert">
                          <strong>Heads up!</strong> This alert needs your attention, but it's not super important.
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-12">
                        <div class="table-responsive">
                          <table id="order-listing" class="table" cellspacing="0" width="100%">
                            <thead>
                              <tr class="bg-primary text-white">
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Ship to</th>
                                <th>Base Price</th>
                                <th>Purchased Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <td>WD-61</td>
                                <td>Edinburgh</td>
                                <td>New York</td>
                                <td>$1500</td>
                                <td>$3200</td>
                                <td>
                                  <label class="badge badge-info">Open</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-62</td>
                                <td>Doe</td>
                                <td>Brazil</td>
                                <td>$4500</td>
                                <td>$7500</td>
                                <td>
                                  <label class="badge badge-danger">Pending</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-63</td>
                                <td>Sam</td>
                                <td>Tokyo</td>
                                <td>$2100</td>
                                <td>$6300</td>
                                <td>
                                  <label class="badge badge-success">Closed</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-64</td>
                                <td>Joe</td>
                                <td>Netherland</td>
                                <td>$1100</td>
                                <td>$7300</td>
                                <td>
                                  <label class="badge badge-warning">Open</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-65</td>
                                <td>Edward</td>
                                <td>Indonesia</td>
                                <td>$3600</td>
                                <td>$4800</td>
                                <td>
                                  <label class="badge badge-success">Closed</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-66</td>
                                <td>Stella</td>
                                <td>Japan</td>
                                <td>$5600</td>
                                <td>$3600</td>
                                <td>
                                  <label class="badge badge-info">On hold</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-67</td>
                                <td>Jaqueline</td>
                                <td>Germany</td>
                                <td>$1100</td>
                                <td>$6300</td>
                                <td>
                                  <label class="badge badge-success">Closed</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-68</td>
                                <td>Tim</td>
                                <td>Italy</td>
                                <td>$6300</td>
                                <td>$2100</td>
                                <td>
                                  <label class="badge badge-warning">Open</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-69</td>
                                <td>John</td>
                                <td>Tokyo</td>
                                <td>$2100</td>
                                <td>$6300</td>
                                <td>
                                  <label class="badge badge-success">Closed</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-70</td>
                                <td>Tom</td>
                                <td>Germany</td>
                                <td>$1100</td>
                                <td>$2300</td>
                                <td>
                                  <label class="badge badge-danger">Pending</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-71</td>
                                <td>Aleena</td>
                                <td>New York</td>
                                <td>$1600</td>
                                <td>$3500</td>
                                <td>
                                  <label class="badge badge-danger">Pending</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-72</td>
                                <td>Alphy</td>
                                <td>Brazil</td>
                                <td>$5500</td>
                                <td>$7200</td>
                                <td>
                                  <label class="badge badge-warning">Open</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-73</td>
                                <td>Twinkle</td>
                                <td>Italy</td>
                                <td>$1560</td>
                                <td>$2530</td>
                                <td>
                                  <label class="badge badge-warning">Open</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-74</td>
                                <td>Catherine</td>
                                <td>Brazil</td>
                                <td>$1600</td>
                                <td>$5600</td>
                                <td>
                                  <label class="badge badge-success">Closed</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-75</td>
                                <td>Daniel</td>
                                <td>Singapore</td>
                                <td>$6800</td>
                                <td>$9800</td>
                                <td>
                                  <label class="badge badge-danger">Pending</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-76</td>
                                <td>Tom</td>
                                <td>Tokyo</td>
                                <td>$1600</td>
                                <td>$6500</td>
                                <td>
                                  <label class="badge badge-info">On hold</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-77</td>
                                <td>Cris</td>
                                <td>Tokyo</td>
                                <td>$2100</td>
                                <td>$6300</td>
                                <td>
                                  <label class="badge badge-warning">Open</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-78</td>
                                <td>Tim</td>
                                <td>Italy</td>
                                <td>$6300</td>
                                <td>$2100</td>
                                <td>
                                  <label class="badge badge-success">Closed</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-79</td>
                                <td>Jack</td>
                                <td>Tokyo</td>
                                <td>$3100</td>
                                <td>$7300</td>
                                <td>
                                  <label class="badge badge-danger">Pending</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                              <tr>
                                <td>WD-80</td>
                                <td>Tony</td>
                                <td>Germany</td>
                                <td>$1100</td>
                                <td>$2300</td>
                                <td>
                                  <label class="badge badge-info">On hold</label>
                                </td>
                                <td class="text-end">
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-eye text-primary"></i>View </button>
                                  <button class="btn btn-light">
                                    <i class="mdi mdi-close text-danger"></i>Remove </button>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
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
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/misc.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/todolist.js"></script>
</body>
</html>