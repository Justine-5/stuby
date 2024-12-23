<?php

session_start();
if (!isset($_SESSION['LoggedIn']) || $_SESSION['LoggedIn'] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'nav.php';
require_once 'sidebar.php';
require_once "db.php";

$userId = $_SESSION['UserId'];
$username = $_SESSION['Username'];
$email = $_SESSION['Email'];

$today = date('Y-m-d');
$dayOfWeek = date('w');


$stmt = $conn->prepare("SELECT current_streak, max_streak, last_login, week_activity, last_reset FROM streaks WHERE account_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$streak = $result->fetch_assoc();
$stmt->close();

$currentStreak = $streak['current_streak'] ?? 0;
$maxStreak = $streak['max_streak'] ?? 0;
$lastLoginDate = $streak['last_login'] ?? null;
$lastResetDate = $streak['last_reset'] ?? null;
$weekActivity = json_decode($streak['week_activity'] ?? '{}', true);

if ($dayOfWeek == 0) {
    $lastSunday = $today;
} else {
    $lastSunday = date('Y-m-d', strtotime('last Sunday', strtotime($today)));
}

if (!$lastResetDate || $lastResetDate < $lastSunday) {
    $weekActivity = [];
    $lastResetDate = $today;

    $weekActivityJson = json_encode($weekActivity);
    $stmt = $conn->prepare("
        INSERT INTO streaks (account_id, current_streak, max_streak, last_login, week_activity, last_reset)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            week_activity = ?,
            last_reset = ?
    ");
    $stmt->bind_param(
        "iiisssss",
        $userId,
        $currentStreak,
        $maxStreak,
        $lastLoginDate,
        $weekActivityJson,
        $lastResetDate,
        $weekActivityJson,
        $lastResetDate
    );
    $stmt->execute();
    $stmt->close();
}

// Studied Today
$stmt = $conn->prepare("
    SELECT COUNT(*) AS studied_today 
    FROM cards 
    WHERE deck_id IN (SELECT id FROM decks WHERE account_id = ?)
    AND DATE(last_studied) = CURDATE()
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$studiedToday = $result->fetch_assoc()['studied_today'] ?? 0;
$stmt->close();

// Reviewed Today
$stmt = $conn->prepare("
    SELECT COUNT(*) AS reviewed_today 
    FROM cards 
    WHERE deck_id IN (SELECT id FROM decks WHERE account_id = ?)
    AND DATE(last_review_date) = CURDATE()
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$reviewedToday = $result->fetch_assoc()['reviewed_today'] ?? 0;
$stmt->close();

// Total Decks
$stmt = $conn->prepare("SELECT COUNT(*) AS total_decks FROM decks WHERE account_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$totalDecks = $result->fetch_assoc()['total_decks'] ?? 0;
$stmt->close();

// Total Cards
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total_cards 
    FROM cards 
    WHERE deck_id IN (SELECT id FROM decks WHERE account_id = ?)
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$totalCards = $result->fetch_assoc()['total_cards'] ?? 0;
$stmt->close();

// recents
$stmt = $conn->prepare("
    SELECT d.id, d.name, COUNT(c.id) AS card_count
    FROM decks d
    LEFT JOIN cards c ON d.id = c.deck_id
    WHERE d.account_id = ?
    GROUP BY d.id
    ORDER BY d.last_opened DESC
    LIMIT 12
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$recentDecks = [];
while ($row = $result->fetch_assoc()) {
    $recentDecks[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/general.css">
    <link rel="stylesheet" href="styles/cards-wrapper.css">
    <link rel="stylesheet" href="styles/home.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rammetto+One&display=swap" rel="stylesheet">
    <script src="script/home.js" defer></script>
    <link rel="icon" type="image/png" href="images/icon_app.png">
    <title>Home</title>
</head>

<body>
    <?php renderNav(true); ?>
    <?php renderSidebar('home'); ?>

    <main>
        <section class="dashboard">
            <div class="greeting">
                <div class="greeting-text">
                    <h2 class="greetings">Welcome,</h2>
                    <h2 class="greetings"><?= $username ?></h2>
                </div>
                <img src="images/stubylogin.png" alt="">
            </div>

            <div class="streak-wrapper">
                <div class="streaks">
                    <div class="streak-top">
                        <p><?= $currentStreak ?> Day Streak!</p>
                        <?php
                            if ($currentStreak > 0) {
                                echo '<img src="icons/fire2.svg">';
                            }
                        ?>
                    </div>

                    <div class="streak-bottom">
                        <?php
                        $days = ['S', 'M', 'T', 'W', 'Th', 'F', 'Sa'];
                        $currentDayIndex = (int) date('w');

                        foreach ($days as $index => $day) {
                            $filled = isset($weekActivity[$day]) && $weekActivity[$day] ? 'filled' : '';
                            $isToday = ($index === $currentDayIndex) ? 'current-day' : '';
                            echo "<div class='day $isToday'>
                                    <p>$day</p>
                                    <div class='streak-fill $filled'></div>
                                </div>";
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="blocks">
                <div class="block-text">
                    <h3><?= $studiedToday ?></h3>
                    <p>Studied Today</p>
                </div>
                <img src="icons/study.svg" alt="">
            </div>

            <div class="blocks">
                <div class="block-text">
                    <h3><?= $reviewedToday ?></h3>
                    <p>Reviewed Today</p>
                </div>
                <img src="icons/review.svg" alt="">
            </div>

            <div class="blocks">
                <div class="block-text">
                    <h3><?= $totalDecks ?></h3>
                    <p>Total Decks</p>
                </div>
                <img src="icons/decks.svg" alt="">
            </div>

            <div class="blocks">
                <div class="block-text">
                    <h3><?= $totalCards ?></h3>
                    <p>Total Cards</p>
                </div>
                <img src="icons/card2.svg" alt="">
            </div>

        </section>

        <section class="recents">
            <h2>Recent Decks</h2>
            <div class="home-wrapper">
                <?php if (!empty($recentDecks)) : ?>
                    <?php foreach ($recentDecks as $deck) : ?>
                        <a href="deck-info.php?id=<?= htmlspecialchars($deck['id']) ?>" class="card-link">
                            <div class="home-card">
                                <div class="card-img"></div>
                                <div class="card-info">
                                    <h3><?= htmlspecialchars($deck['name']) ?></h3>
                                    <p><?= htmlspecialchars($deck['card_count']) ?> cards</p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </main>


</body>

</html>