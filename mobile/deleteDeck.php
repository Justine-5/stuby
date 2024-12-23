<?php
require_once "../db.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token']) && $_POST['token'] === 'StubyBuddy1') {
    if (isset($_POST['user_id'], $_POST['deck_id'])) {
        $userId = (int)$_POST['user_id'];
        $deckId = (int)$_POST['deck_id'];

        try {
            $stmt = $conn->prepare("DELETE FROM decks WHERE id = ? AND account_id = ?");
            $stmt->bind_param("ii", $deckId, $userId);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Deck deleted successfully!"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Deck not found or user unauthorized."
                ]);
            }

            $stmt->close();
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "Error deleting deck: " . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Missing required fields (user_id, deck_id)."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid token or request method."
    ]);
}
