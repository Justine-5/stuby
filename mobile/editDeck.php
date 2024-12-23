<?php
require_once "../db.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token']) && $_POST['token'] === 'StubyBuddy1') {
    if (isset($_POST['user_id'], $_POST['deck_id'], $_POST['deck_name'], $_POST['deck_description'])) {
        $userId = (int)$_POST['user_id'];
        $deckId = (int)$_POST['deck_id'];
        $deckName = trim($_POST['deck_name']);
        $deckDescription = trim($_POST['deck_description']);

        if (empty($deckName)) {
            echo json_encode([
                "status" => "error",
                "message" => "Deck name is required."
            ]);
            exit;
        }

        try {
            $stmt = $conn->prepare("UPDATE decks SET name = ?, description = ? WHERE id = ? AND account_id = ?");
            $stmt->bind_param("ssii", $deckName, $deckDescription, $deckId, $userId);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Deck updated successfully!"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No changes were made or invalid deck ID."
                ]);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "Error updating deck: " . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Missing required fields (user_id, deck_id, deck_name, deck_description)."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid token or request method."
    ]);
}
