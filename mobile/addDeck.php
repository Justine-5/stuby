<?php
require_once "../db.php";

if (isset($_POST['token'])) {
    $token = $_POST['token'];
    if ($token === 'StubyBuddy1') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deckName'], $_POST['deckDescription'], $_POST['userId'])) {
            $deckName = trim($_POST['deckName']);
            $deckDescription = trim($_POST['deckDescription']);
            $userId = (int)$_POST['userId'];

            header('Content-Type: application/json');

            if (empty($deckName)) {
                echo json_encode(["status" => "error", "message" => "Deck name is required."]);
                exit;
            }

            try {
                $originalDeckName = $deckName;
                $counter = 0;

                do {
                    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM decks WHERE account_id = ? AND name = ?");
                    $stmt->bind_param("is", $userId, $deckName);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $stmt->close();

                    if ($row['count'] > 0) {
                        $counter++;
                        $deckName = $originalDeckName . " ($counter)";
                    }
                } while ($row['count'] > 0);

                $stmt = $conn->prepare("INSERT INTO decks (account_id, name, description) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $userId, $deckName, $deckDescription);
                $stmt->execute();
                $stmt->close();

                $deckId = $conn->insert_id;

                echo json_encode([
                    "status" => "success",
                    "message" => "Deck added successfully!",
                    "deck" => [
                        "id" => $deckId,
                        "name" => $deckName,
                        "description" => $deckDescription
                    ]
                ]);
            } catch (Exception $e) {
                echo json_encode(["status" => "error", "message" => "Error adding deck: " . $e->getMessage()]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Missing fields."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid token."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No token provided."]);
}

?>