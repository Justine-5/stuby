<?php
function renderNav($showSearch = true)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $username = $_SESSION['Username'] ?? 'Guest';
    $email = $_SESSION['Email'] ?? 'guest@example.com';
    $nameParts = explode(' ', $username);
    $initial = strtoupper($nameParts[0][0] ?? 'G');
    if (count($nameParts) > 1) {
        $lastInitial = strtoupper(end($nameParts)[0] ?? '');
        $initial = $initial . ($lastInitial ? $lastInitial : '');
    }
?>
    <nav>
        <div class="top-nav">
            <div class="logo-menu">
                <button class="menu"></button>
                <h2><a href="home.php" class="logo">Stuby</a></h2>
            </div>

            <?php if ($showSearch): ?>
                <form class="search search-desktop">
                    <input type="search" placeholder="Search Decks">
                    <div class="search-result hidden"></div>
                </form>
            <?php endif; ?>

            <div class="dropdown">
                <button class="profile"><?= htmlspecialchars($initial) ?></button>
                <div class="profile-info">
                    <div class="dropdown-icon"><?= htmlspecialchars($initial) ?></div>
                    <div class="dropdown-text">
                        <h4><?= htmlspecialchars($username) ?></h4>
                        <p><?= htmlspecialchars($email) ?></p>
                        <a href="settings.php">View profile</a>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($showSearch): ?>
            <form class="search search-mobile">
                <input type="search" placeholder="Search Decks">
                <div class="search-result hidden"></div>
            </form>
        <?php endif; ?>
    </nav>
<?php
}
?>