<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sidebar</title>
  <!-- Linking Google Font Link For Icons -->
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <style>
    /* Importing Google font - Poppins */
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap");

    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
    }

    body {
    min-height: 100vh;
    background: #F0F4FF;
    }

    .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    width: 85px;
    display: flex;
    overflow-x: hidden;
    flex-direction: column;
    background: #4d4d4d;
    padding: 25px 20px;
    transition: all 0.4s ease;
    }

    .sidebar:hover {
    width: 260px;
    }

    .sidebar .sidebar-header {
    display: flex;
    align-items: center;
    }

    .sidebar .sidebar-header img {
    width: 42px;
    border-radius: 50%;
    }

    .sidebar .sidebar-header h2 {
    color: #fff;
    font-size: 1.25rem;
    font-weight: 600;
    white-space: nowrap;
    margin-left: 23px;
    }

    .sidebar-links h4 {
    color: #fff;
    font-weight: 500;
    white-space: nowrap;
    margin: 10px 0;
    position: relative;
    }

    .sidebar-links h4 span {
    opacity: 0;
    }

    .sidebar:hover .sidebar-links h4 span {
    opacity: 1;
    }

    .sidebar-links .menu-separator {
    position: absolute;
    left: 0;
    top: 50%;
    width: 100%;
    height: 1px;
    transform: scaleX(1);
    transform: translateY(-50%);
    background: #fff;
    transform-origin: right;
    transition-delay: 0.2s;
    }

    .sidebar:hover .sidebar-links .menu-separator {
    transition-delay: 0s;
    transform: scaleX(0);
    }

    .sidebar-links {
    list-style: none;
    margin-top: 20px;
    height: 80%;
    overflow-y: auto;
    scrollbar-width: none;
    }

    .sidebar-links::-webkit-scrollbar {
    display: none;
    }

    .sidebar-links li a {
    display: flex;
    align-items: center;
    gap: 0 20px;
    color: #fff;
    font-weight: 500;
    white-space: nowrap;
    padding: 15px 10px;
    text-decoration: none;
    transition: 0.2s ease;
    }

    .sidebar-links li a:hover {
    color: #161a2d;
    background: #fff;
    border-radius: 4px;
    }

    .user-account {
    margin-top: auto;
    padding: 12px 10px;
    margin-left: -10px;
    }

    .user-profile {
    display: flex;
    align-items: center;
    color: #161a2d;
    }

    .user-profile img {
    width: 42px;
    border-radius: 50%;
    border: 2px solid #fff;
    }

    .user-profile h3 {
    font-size: 1rem;
    font-weight: 600;
    }

    .user-profile span {
    font-size: 0.775rem;
    font-weight: 600;
    }

    .user-detail {
    margin-left: 23px;
    white-space: nowrap;
    }

    .sidebar:hover .user-account {
    background: #fff;
    border-radius: 4px;
    }
  </style>
</head>
<body>
<!-- sidebar.php -->
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="dashboard_restaurante.php">
            <img src="../geral/assets/images/logo.svg" alt="logo" />
        </a>
        <h2><b>Restomate</b></h2>
    </div>
    <ul class="sidebar-links">
        <h4>
            <span>Gestão do Restaurante</span>
            <div class="menu-separator"></div>
        </h4>
        <li>
            <a href=" atualizar_informacoes_restaurante.php">
                <span class="material-symbols-outlined"> edit </span>Informações
            </a>
        </li>
        <li>
            <a href="gestao_funcionarios.php">
                <span class="material-symbols-outlined"> group </span>Funcionários
            </a>
        </li>
        <li>
            <a href="gestao_fornecedores.php">
                <span class="material-symbols-outlined"> local_shipping </span>Fornecedores
            </a>
        </li>
        <li>
            <a href="gestao_mesas.php">
                <span class="material-symbols-outlined"> calendar_today </span>Reservas de Mesa
            </a>
        </li>

        <h4>
            <span>Relatórios e Estatísticas</span>
            <div class="menu-separator"></div>
        </h4>
        <li>
            <a href="relatorios_financeiros.php">
                <span class="material-symbols-outlined"> attach_money </span>Despesas e Lucros
            </a>
        </li>
        <li>
            <a href="salario_empregados.php">
                <span class="material-symbols-outlined"> paid </span>Salários
            </a>
        </li>
        <li>
            <a href="estatisticas_vendas.php">
                <span class="material-symbols-outlined"> bar_chart </span>Vendas
            </a>
        </li>
        <li>
            <a href="avaliacao_clientes.php">
                <span class="material-symbols-outlined"> star </span>Avaliações
            </a>
        </li>

        <h4>
            <span>Gestão</span>
            <div class="menu-separator"></div>
        </h4>
        <li>
            <a href="gestao_pedidos.php">
                <span class="material-symbols-outlined"> list_alt </span>Pedidos
            </a>
        </li>
        <li>
            <a href="historico_pedidos.php">
                <span class="material-symbols-outlined"> history </span>Histórico de Pedidos
            </a>
        </li>
        <li>
            <a href="gestao_categorias.php">
                <span class="material-symbols-outlined"> category </span>Categorias
            </a>
        </li>
        <li>
            <a href="gestao_produtos.php">
                <span class="material-symbols-outlined"> inventory </span>Produtos
            </a>
        </li>
        <li>
            <a href="gestao_pratos.php">
                <span class="material-symbols-outlined"> fastfood </span>Pratos
            </a>
        </li>

        <h4>
            <span>Notificações</span>
            <div class="menu-separator"></div>
        </h4>
        <li>
            <a href="notificacoes_estoque_critico.php">
                <span class="material-symbols-outlined"> warning </span>Estoque Crítico
            </a>
        </li>

        <h4>
            <span>Outros</span>
            <div class="menu-separator"></div>
        </h4>
        <li>
            <a href="ementa.php">
                <span class="material-symbols-outlined"> menu_book </span>Ementa
            </a>
        </li>
        <li>
            <a href="../geral/logout.php">
                <span class="material-symbols-outlined"> logout </span>Logout
            </a>
        </li>
    </ul>

    <div class="user-account">
        <div class="user-profile">
            <img src="../geral/assets/images/profile.png" alt="Profile Image" /> 
            <div class="user-detail">
            <h3><?php echo htmlspecialchars($nome); ?></h3>
            <span><?php echo htmlspecialchars($tipo); ?></span>
            </div>
        </div>
    </div>
</aside>

</body>
</html>