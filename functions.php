<?php
function updateStreak($userId, $conn) {
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
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
    }

    if ($lastLoginDate === $today) {
        return;
    }

    if ($lastLoginDate === $yesterday) {
        $currentStreak++;
    } else {
        $currentStreak = 1;
    }

    $maxStreak = max($maxStreak, $currentStreak);

    $days = ['S', 'M', 'T', 'W', 'Th', 'F', 'Sa'];
    $weekActivity[$days[$dayOfWeek]] = true;

    $weekActivityJson = json_encode($weekActivity);

    $stmt = $conn->prepare("
        INSERT INTO streaks (account_id, current_streak, max_streak, last_login, week_activity, last_reset)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            current_streak = ?,
            max_streak = ?,
            last_login = ?,
            week_activity = ?,
            last_reset = ?
    ");
    $stmt->bind_param(
        "iiisssiisss",
        $userId,
        $currentStreak,
        $maxStreak,
        $today,
        $weekActivityJson,
        $lastResetDate,
        $currentStreak,
        $maxStreak,
        $today,
        $weekActivityJson,
        $lastResetDate
    );
    $stmt->execute();
    $stmt->close();
}
?>
