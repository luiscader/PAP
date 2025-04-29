<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <div class="sidebar-brand-wrapper d-none d-lg-flex align-items-center justify-content-center fixed-top">
    <a class="sidebar-brand brand-logo" href="index.php"><img src="../assets/images/logo.png" alt="logo" /></a>
    <a class="sidebar-brand brand-logo-mini" href="index.php"><img src="../assets/images/logo-mini.png" alt="logo" /></a>
  </div>
  <ul class="nav">
    <li class="nav-item profile">
      <div class="profile-desc">
        <div class="profile-pic">
          <div class="count-indicator">
            <img class="img-xs rounded-circle " src="../assets/images/faces/face15.jpg" alt="">
            <span class="count bg-success"></span>
          </div>
          <div class="profile-name">
            <h5 class="mb-0 font-weight-normal"> <?php echo htmlspecialchars($nome); ?></h5>
            <span> <?php echo htmlspecialchars($tipo); ?></span>
          </div>
        </div>
        <a href="#" id="profile-dropdown" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></a>
        <div class="dropdown-menu dropdown-menu-right sidebar-dropdown preview-list" aria-labelledby="profile-dropdown">
          <a href="#" class="dropdown-item preview-item">
            <div class="preview-thumbnail">
              <div class="preview-icon bg-dark rounded-circle">
                <i class="mdi mdi-cog text-primary"></i>
              </div>
            </div>
            <div class="preview-item-content">
              <p class="preview-subject ellipsis mb-1 text-small">Account settings</p>
            </div>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item preview-item">
            <div class="preview-thumbnail">
              <div class="preview-icon bg-dark rounded-circle">
                <i class="mdi mdi-onepassword  text-info"></i>
              </div>
            </div>
            <div class="preview-item-content">
              <p class="preview-subject ellipsis mb-1 text-small">Change Password</p>
            </div>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item preview-item">
            <div class="preview-thumbnail">
              <div class="preview-icon bg-dark rounded-circle">
                <i class="mdi mdi-calendar-today text-success"></i>
              </div>
            </div>
            <div class="preview-item-content">
              <p class="preview-subject ellipsis mb-1 text-small">To-do list</p>
            </div>
          </a>
        </div>
      </div>
    </li>
    <li class="nav-item nav-category">
      <span class="nav-link">Navegação</span>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" href="index.php">
        <span class="menu-icon">
          <i class="mdi mdi-speedometer"></i>
        </span>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#restaurante" aria-expanded="false" aria-controls="restaurante">
        <span class="menu-icon">
          <i class="mdi mdi-store"></i>
        </span>
        <span class="menu-title">Restaurante</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="restaurante">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="atualizar_informacoes_restaurante.php">Informações</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#funcionario" aria-expanded="false" aria-controls="funcionario">
        <span class="menu-icon">
          <i class="mdi mdi-account-multiple-outline"></i>
        </span>
        <span class="menu-title">Funcionarios</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="funcionario">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="funcionarios.php">Gestão de Funcionários</a></li>
          <li class="nav-item"> <a class="nav-link" href="contratar_funcionario.php">Contratar Funcionários</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#fornecedor" aria-expanded="false" aria-controls="fornecedor">
        <span class="menu-icon">
          <i class="mdi mdi-home-variant"></i>
        </span>
        <span class="menu-title">Fornecedores</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="fornecedor">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="fornecedores.php">Gestão de Fornecedores</a></li>
          <li class="nav-item"> <a class="nav-link" href="contratar_fornecedor.php">Contratar Fornecedor</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" href="reservas.php">
        <span class="menu-icon">
          <i class="mdi mdi-bulletin-board"></i>
        </span>
        <span class="menu-title">Reservas de Mesa</span>
      </a>
    </li>

    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#pedidos" aria-expanded="false" aria-controls="pedidos">
        <span class="menu-icon">
          <i class="mdi mdi-clipboard-outline"></i>
        </span>
        <span class="menu-title">Pedidos</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="pedidos">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="pedidos.php">Gestão de Pedidos</a></li>
          <li class="nav-item"> <a class="nav-link" href="criar_pedido.php">Criar Pedido</a></li>
          <li class="nav-item"> <a class="nav-link" href="historico_pedido.php">Historico de Pedidos</a></li>
        </ul>
      </div>
    </li>   
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#categorias" aria-expanded="false" aria-controls="categorias">
        <span class="menu-icon">
          <i class="mdi mdi-label-outline"></i>
        </span>
        <span class="menu-title">Categorias</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="categorias">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="categorias.php">Gestão de Categorias</a></li>
          <li class="nav-item"> <a class="nav-link" href="criar_categoria.php">Criar Categoria</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#produtos" aria-expanded="false" aria-controls="produtos">
        <span class="menu-icon">
          <i class="mdi mdi-bulletin-board"></i>
        </span>
        <span class="menu-title">Produtos</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="produtos">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="produtos.php">Gestão de Produtos</a></li>
          <li class="nav-item"> <a class="nav-link" href="criar_produto.php">Criar Produtos</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#pratos" aria-expanded="false" aria-controls="pratos">
        <span class="menu-icon">
          <i class="mdi mdi-food"></i>
        </span>
        <span class="menu-title">Pratos</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="pratos">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="pratos.php">Gestão de Pratos</a></li>
          <li class="nav-item"> <a class="nav-link" href="criar_prato.php">Criar Pratos</a></li>
        </ul>
      </div>
    </li>
  </ul>
</nav>