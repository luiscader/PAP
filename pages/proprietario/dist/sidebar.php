<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <div class="sidebar-brand-wrapper d-none d-lg-flex align-items-center justify-content-center fixed-top">
    <a class="sidebar-brand brand-logo" href="index.php"><img src="https://demo.bootstrapdash.com/corona-new/themes/assets/images/logo.svg" alt="logo" /></a>
    <a class="sidebar-brand brand-logo-mini" href="index.php"><img src="https://demo.bootstrapdash.com/corona-new/themes/assets/images/logo-mini.svg" alt="logo" /></a>
  </div>
  <ul class="nav">
    <li class="nav-item profile">
      <div class="profile-desc">
        <div class="profile-pic">
          <div class="count-indicator">
            <img class="img-xs rounded-circle " src="assets/images/faces/face15.jpg" alt="">
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
          <li class="nav-item"> <a class="nav-link" href="funcionarios.php">Gestão Funcionários</a></li>
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
          <li class="nav-item"> <a class="nav-link" href="fornecedores.php">Gestão Fornecedores</a></li>
          <li class="nav-item"> <a class="nav-link" href="contratar_fornecedor.php">Contratar Fornecedor</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#relatorios" aria-expanded="false" aria-controls="relatorios">
        <span class="menu-icon">
          <i class="mdi mdi-chart-bar"></i>
        </span>
        <span class="menu-title">Finanças</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="relatorios">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="#">Despesas / Lucros</a></li>
          <li class="nav-item"> <a class="nav-link" href="#">Salarios</a></li>
          <li class="nav-item"> <a class="nav-link" href="#">Vendas</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#gestao" aria-expanded="false" aria-controls="gestao">
        <span class="menu-icon">
          <i class="mdi mdi-bulletin-board"></i>
        </span>
        <span class="menu-title">Rservas de Mesa</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="gestao">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="#">Criar Reservas de Mesa</a></li>
          <li class="nav-item"> <a class="nav-link" href="#">Reservas de Mesa</a></li>
          <li class="nav-item"> <a class="nav-link" href="#.php">Categoria</a></li>
          <li class="nav-item"> <a class="nav-link" href="#">Produtos</a></li>
          <li class="nav-item"> <a class="nav-link" href="#">Pratos</a></li>
        </ul>
      </div>
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
          <li class="nav-item"> <a class="nav-link" href="pedidos.php">Pedidos</a></li>
          <li class="nav-item"> <a class="nav-link" href="#">Historico de Pedidos</a></li>
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
          <li class="nav-item"> <a class="nav-link" href="criar_categoria.php">Criar Categoria</a></li>
          <li class="nav-item"> <a class="nav-link" href="categorias.php">Categorias</a></li>
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
          <li class="nav-item"> <a class="nav-link" href="#">Criar Produtos</a></li>
          <li class="nav-item"> <a class="nav-link" href="#">Produtos</a></li>
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
          <li class="nav-item"> <a class="nav-link" href="#">Criar Pratos</a></li>
          <li class="nav-item"> <a class="nav-link" href="#">Pratos</a></li>
        </ul>
      </div>
    </li>

<!--
    <li class="nav-item nav-category">
      <span class="nav-link">Navigation</span>
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
      <a class="nav-link" href="widgets/widgets.html">
        <span class="menu-icon">
          <i class="mdi mdi-texture"></i>
        </span>
        <span class="menu-title">Widgets</span>
      </a>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#page-layouts" aria-expanded="false" aria-controls="page-layouts">
        <span class="menu-icon">
          <i class="mdi mdi-view-list"></i>
        </span>
        <span class="menu-title">Page Layouts</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="page-layouts">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="boxed-layout.html">Boxed</a></li>
          <li class="nav-item"> <a class="nav-link" href="rtl-layout.html">RTL</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#sidebar-layouts" aria-expanded="false" aria-controls="sidebar-layouts">
        <span class="menu-icon">
          <i class="mdi mdi-crosshairs-gps"></i>
        </span>
        <span class="menu-title">Sidebar Layouts</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="sidebar-layouts">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="compact-menu.html">Compact menu</a></li>
          <li class="nav-item"> <a class="nav-link" href="sidebar-collapsed.html">Icon menu</a></li>
          <li class="nav-item"> <a class="nav-link" href="sidebar-hidden.html">Sidebar Hidden</a></li>
          <li class="nav-item"> <a class="nav-link" href="sidebar-hidden-overlay.html">Sidebar Overlay</a></li>
          <li class="nav-item"> <a class="nav-link" href="sidebar-fixed.html">Sidebar Fixed</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
        <span class="menu-icon">
          <i class="mdi mdi-laptop"></i>
        </span>
        <span class="menu-title">Basic UI Elements</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="ui-basic">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="ui-features/accordions.html">Accordions</a></li>
          <li class="nav-item"> <a class="nav-link" href="ui-features/buttons.html">Buttons</a></li>
          <li class="nav-item"> <a class="nav-link" href="ui-features/badges.html">Badges</a></li>
          <li class="nav-item"> <a class="nav-link" href="ui-features/breadcrumbs.html">Breadcrumbs</a></li>
          <li class="nav-item"> <a class="nav-link" href="ui-features/dropdowns.html">Dropdowns</a></li>
          <li class="nav-item"> <a class="nav-link" href="ui-features/modals.html">Modals</a></li>
          <li class="nav-item"> <a class="nav-link" href="ui-features/progress.html">Progress bar</a></li>
          <li class="nav-item"> <a class="nav-link" href="ui-features/pagination.html">Pagination</a></li>
          <li class="nav-item"> <a class="nav-link" href="ui-features/tabs.html">Tabs</a></li>
          <li class="nav-item"> <a class="nav-link" href="ui-features/typography.html">Typography</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#ui-advanced" aria-expanded="false" aria-controls="ui-advanced">
        <span class="menu-icon">
          <i class="mdi mdi-cog"></i>
        </span>
        <span class="menu-title">Advanced Elements</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="ui-advanced">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="ui-features/dragula.html">Dragula</a></li>
          <li class="nav-item"> <a class="nav-link" href="ui-features/clipboard.html">Clipboard</a></li>
          <li class="nav-item"> <a class="nav-link" href="ui-features/context-menu.html">Context menu</a></li>
          <li class="nav-item"> <a class="nav-link" href="ui-features/slider.html">Slider</a></li>
          <li class="nav-item"> <a class="nav-link" href="ui-features/loaders.html">Loaders</a></li>
          <li class="nav-item"> <a class="nav-link" href="ui-features/colcade.html">Colcade</a></li>
          <li class="nav-item"> <a class="nav-link" href="ui-features/carousel.html">Carousel</a></li>
          <li class="nav-item"> <a class="nav-link" href="ui-features/tooltips.html">Tooltips</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#form-elements" aria-expanded="false" aria-controls="form-elements">
        <span class="menu-icon">
          <i class="mdi mdi-playlist-play"></i>
        </span>
        <span class="menu-title">Form Elements</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="form-elements">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="forms/basic_elements.html">Basic Form</a></li>
          <li class="nav-item"> <a class="nav-link" href="forms/advanced_elements.html">Advanced Form</a></li>
          <li class="nav-item"> <a class="nav-link" href="forms/validation.html">Validation</a></li>
          <li class="nav-item"> <a class="nav-link" href="forms/wizard.html">Wizard</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#tables" aria-expanded="false" aria-controls="tables">
        <span class="menu-icon">
          <i class="mdi mdi-table-large"></i>
        </span>
        <span class="menu-title">Tables</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="tables">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="pages/tables/basic-table.html">Basic table</a></li>
          <li class="nav-item"> <a class="nav-link" href="tables/data-table.html">Data table</a></li>
          <li class="nav-item"> <a class="nav-link" href="tables/js-grid.html">Js-grid</a></li>
          <li class="nav-item"> <a class="nav-link" href="tables/sortable-table.html">Sortable table</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#editors" aria-expanded="false" aria-controls="editors">
        <span class="menu-icon">
          <i class="mdi mdi-format-text"></i>
        </span>
        <span class="menu-title">Editors</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="editors">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="forms/text_editor.html">Text editors</a></li>
          <li class="nav-item"> <a class="nav-link" href="forms/code_editor.html">Code Editor</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#charts" aria-expanded="false" aria-controls="charts">
        <span class="menu-icon">
          <i class="mdi mdi-chart-bar"></i>
        </span>
        <span class="menu-title">Charts</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="charts">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="charts/chartjs.html">ChartJs</a></li>
          <li class="nav-item"> <a class="nav-link" href="charts/morris.html">Morris</a></li>
          <li class="nav-item"> <a class="nav-link" href="charts/flot-chart.html">Flot</a></li>
          <li class="nav-item"> <a class="nav-link" href="charts/google-charts.html">Google charts</a></li>
          <li class="nav-item"> <a class="nav-link" href="charts/sparkline.html">Sparkline js</a></li>
          <li class="nav-item"> <a class="nav-link" href="charts/c3.html">C3 charts</a></li>
          <li class="nav-item"> <a class="nav-link" href="charts/chartist.html">Chartists</a></li>
          <li class="nav-item"> <a class="nav-link" href="charts/justGage.html">JustGage</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#maps" aria-expanded="false" aria-controls="maps">
        <span class="menu-icon">
          <i class="mdi mdi-map-marker-radius"></i>
        </span>
        <span class="menu-title">Maps</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="maps">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="maps/google-maps.html">Google Maps</a></li>
          <li class="nav-item"> <a class="nav-link" href="maps/mapael.html">Mapeal</a></li>
          <li class="nav-item"> <a class="nav-link" href="maps/vector-map.html">Vector map</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" href="ui-features/notifications.html">
        <span class="menu-icon">
          <i class="mdi mdi-bell-ring"></i>
        </span>
        <span class="menu-title">Notifications</span>
      </a>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#icons" aria-expanded="false" aria-controls="icons">
        <span class="menu-icon">
          <i class="mdi mdi-contacts"></i>
        </span>
        <span class="menu-title">Icons</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="icons">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="icons/flag-icons.html">Flag icons</a></li>
          <li class="nav-item"> <a class="nav-link" href="icons/mdi.html">Mdi icons</a></li>
          <li class="nav-item"> <a class="nav-link" href="icons/font-awesome.html">Font Awesome</a></li>
          <li class="nav-item"> <a class="nav-link" href="icons/simple-line-icon.html">Simple line icons</a>
          </li>
          <li class="nav-item"> <a class="nav-link" href="icons/themify.html">Themify icons</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item nav-category">
      <span class="nav-link">More</span>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" href="ui-features/popups.html">
        <span class="menu-icon">
          <i class="mdi mdi-forum"></i>
        </span>
        <span class="menu-title">Popups</span>
      </a>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
        <span class="menu-icon">
          <i class="mdi mdi-security"></i>
        </span>
        <span class="menu-title">User Pages</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="auth">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="samples/login.html"> Login </a></li>
          <li class="nav-item"> <a class="nav-link" href="samples/login-2.html"> Login 2 </a></li>
          <li class="nav-item"> <a class="nav-link" href="samples/register.html"> Register </a></li>
          <li class="nav-item"> <a class="nav-link" href="samples/register-2.html"> Register 2 </a></li>
          <li class="nav-item"> <a class="nav-link" href="samples/lock-screen.html"> Lockscreen </a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#general-pages" aria-expanded="false" aria-controls="general-pages">
        <span class="menu-icon">
          <i class="mdi mdi-earth"></i>
        </span>
        <span class="menu-title">General Pages</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="general-pages">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="samples/blank-page.html"> Blank Page </a></li>
          <li class="nav-item"> <a class="nav-link" href="samples/profile.html"> Profile </a></li>
          <li class="nav-item"> <a class="nav-link" href="samples/portfolio.html"> Portfolio </a></li>
          <li class="nav-item"> <a class="nav-link" href="samples/faq.html"> FAQ </a></li>
          <li class="nav-item"> <a class="nav-link" href="samples/faq-2.html"> FAQ 2 </a></li>
          <li class="nav-item"> <a class="nav-link" href="samples/search-results.html"> Search Results </a>
          </li>
          <li class="nav-item"> <a class="nav-link" href="samples/news-grid.html"> News grid </a></li>
          <li class="nav-item"> <a class="nav-link" href="samples/timeline.html"> Timeline </a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#error" aria-expanded="false" aria-controls="error">
        <span class="menu-icon">
          <i class="mdi mdi-lock"></i>
        </span>
        <span class="menu-title">Error pages</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="error">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="samples/error-404.html"> 404 </a></li>
          <li class="nav-item"> <a class="nav-link" href="samples/error-500.html"> 500 </a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#e-commerce" aria-expanded="false" aria-controls="e-commerce">
        <span class="menu-icon">
          <i class="mdi mdi-medical-bag"></i>
        </span>
        <span class="menu-title">E-commerce</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="e-commerce">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="samples/invoice.html"> Invoice </a></li>
          <li class="nav-item"> <a class="nav-link" href="samples/pricing-table.html"> Pricing Table </a></li>
          <li class="nav-item"> <a class="nav-link" href="samples/orders.html"> Orders </a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" href="apps/calendar.html">
        <span class="menu-icon">
          <i class="mdi mdi-calendar-today"></i>
        </span>
        <span class="menu-title">Calendar</span>
      </a>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" href="apps/todo.html">
        <span class="menu-icon">
          <i class="mdi mdi-bulletin-board"></i>
        </span>
        <span class="menu-title">Todo list</span>
      </a>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" href="apps/email.html">
        <span class="menu-icon">
          <i class="mdi mdi-email"></i>
        </span>
        <span class="menu-title">E-mail</span>
      </a>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" href="apps/gallery.html">
        <span class="menu-icon">
          <i class="mdi mdi-image-filter-center-focus-weak"></i>
        </span>
        <span class="menu-title">Gallery</span>
      </a>
    </li>
    <li class="nav-item menu-items">
      <a class="nav-link" href="https://demo.bootstrapdash.com/corona-new/docs/documentation.html">
        <span class="menu-icon">
          <i class="mdi mdi-file-document"></i>
        </span>
        <span class="menu-title">Documentation</span>
      </a>
    </li>
     -->
  </ul>
</nav>