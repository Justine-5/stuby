<?php
require_once "../db.php";

header('Content-Type: application/json');

if (isset($_POST['token'])) {
    $token = $_POST['token'];
    if ($token === 'StubyBuddy1') {
        if (isset($_POST['user_id'])) {
            $user_id = (int)$_POST['user_id'];

            try {
                $stmt = $conn->prepare("
                    SELECT d.id AS deck_id, d.name, COUNT(c.id) AS card_count
                    FROM decks d
                    LEFT JOIN cards c ON d.id = c.deck_id
                    WHERE d.account_id = ?
                    GROUP BY d.id, d.name
                    ORDER BY d.date_created DESC
                ");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                $decks = [];
                while ($row = $result->fetch_assoc()) {
                    $decks[] = [
                        'deck_id' => $row['deck_id'],
                        'name' => $row['name'],
                        'card_count' => $row['card_count']
                    ];
                }

                $stmt->close();

                echo json_encode([
                    "status" => "success",
                    "decks" => $decks
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error fetching decks: " . $e->getMessage()
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
            "message" => "Invalid token."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Token is missing."
    ]);
}
