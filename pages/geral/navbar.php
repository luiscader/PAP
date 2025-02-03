<nav class="navbar">
    <div class="logo">
        <a href="index.php">
            <img src="assets/images/logo.svg" alt="Logo">
            <span>Restomate</span>
        </a>
    </div>

    <!-- Barra de pesquisa -->
    <form class="search-bar" action="index.php" method="GET">
        <div class="location">
            <i class="fas fa-location-arrow"></i>
            <span>Lisboa</span>
        </div>
        <input type="text" placeholder="Cozinha, nome do restaurante..." name="search">
        <button type="submit">PESQUISA</button>
    </form>

    <div class="user-actions">
        <?php if (isset($_SESSION['id'])): ?>
            <a href="perfil_cliente.php" class="profile-link">
                <img src="assets/images/profile.png" alt="Profile" class="profile-img">
            </a>
        <?php else: ?>
            <a href="login.php" class="btn login">Login</a>
            <a href="signup.php" class="btn signup">Sign up</a>
        <?php endif; ?>
    </div>
</nav>
