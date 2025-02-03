<!DOCTYPE html>
<!-- Coding By CodingNepal - www.codingnepalweb.com -->
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sidebar Menu HTML and CSS | CodingNepal</title>
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
        <img src="../geral/assets/images/logo.svg" alt="logo" />
        <h2><b>Restomate</b></h2>
    </div>
    <ul class="sidebar-links">
        <h4>
            <span>Gestão do Fornecedor</span>
            <div class="menu-separator"></div>
        </h4>
        <li>
            <a href="atualizar_informações_fornecedor.php">
                <span class="material-symbols-outlined"> edit </span>Informações
            </a>
        </li>
        <h4>
            <span>Notificações</span>
            <div class="menu-separator"></div>
        </h4>
        <li>
            <a href="notificacoes_estoque.php">
                <span class="material-symbols-outlined"> warning </span>Estoque
            </a>
        </li>

        <h4>
            <span>Relatórios e Estatística</span>
            <div class="menu-separator"></div>
        </h4>
        <li>
            <a href="relatorios_fornecedor.php">
                <span class="material-symbols-outlined"> bar_chart </span>Vendas
            </a>
        </li>

        <h4>
            <span>Histórico</span>
            <div class="menu-separator"></div>
        </h4>
        <li>
            <a href="historico_encomendas.php">
                <span class="material-symbols-outlined"> history </span>Encomendas
            </a>
        </li>
        <h4>
            <span>Outros</span>
            <div class="menu-separator"></div>
        </h4>
        <li>
            <a href="../geral/logout.php">
                <span class="material-symbols-outlined"> logout </span>Logout
            </a>
        </li>
    </ul>

    <div class="user-account">
        <div class="user-profile">
            <img src="../geral/images/profile.png" alt="Profile Image" /> 
            <div class="user-detail">
            <h3><?php echo htmlspecialchars($nome); ?></h3>
            <span><?php echo htmlspecialchars($tipo); ?></span>
            </div>
        </div>
    </div>
</aside>

</body>
</html>