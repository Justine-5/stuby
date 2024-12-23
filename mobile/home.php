<?php
require_once "../db.php";
header('Content-Type: application/json');

if (isset($_POST['token']) && $_POST['token'] === 'StubyBuddy1') {
    if (isset($_POST['user_id'])) {
        $userId = (int)$_POST['user_id'];

        $today = date('Y-m-d');
        $dayOfWeek = date('w');

        try {
            // Fetch streak data
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

            $stmt = $conn->prepare("
                SELECT d.id, d.name, COUNT(c.id) AS card_count
                FROM decks d
                LEFT JOIN cards c ON d.id = c.deck_id
                WHERE d.account_id = ?
                GROUP BY d.id
                ORDER BY d.last_opened DESC
                LIMIT 3
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            $recentDecks = [];
            while ($row = $result->fetch_assoc()) {
                $recentDecks[] = $row;
            }
            $stmt->close();

            echo json_encode([
                "status" => "success",
                "streak" => [
                    "current_streak" => $currentStreak,
                    "max_streak" => $maxStreak,
                    "last_login" => $lastLoginDate,
                    "last_reset" => $lastResetDate,
                    "week_activity" => $weekActivity,
                ],
                "recent_decks" => $recentDecks
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "An error occurred: " . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "User ID is required."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid token or token is missing."
    ]);
}
?>
