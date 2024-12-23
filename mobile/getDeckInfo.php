<?php
require_once "../db.php";

header('Content-Type: application/json');

if (isset($_POST['token'])) {
    $token = $_POST['token'];

    if ($token === 'StubyBuddy1') {
        if (isset($_POST['user_id']) && isset($_POST['deck_id'])) {
            $userId = (int)$_POST['user_id'];
            $deckId = (int)$_POST['deck_id'];

            try {
                $stmt = $conn->prepare("UPDATE decks 
                                        SET last_opened = CURRENT_TIMESTAMP() 
                                        WHERE id = ? AND account_id = ?");
                $stmt->bind_param("ii", $deckId, $userId);
                $stmt->execute();
                $stmt->close();
            } catch (Exception $e) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error updating last_opened: " . $e->getMessage()
                ]);
                exit;
            }

            try {
                $stmt = $conn->prepare("SELECT * FROM decks WHERE id = ? AND account_id = ?");
                $stmt->bind_param("ii", $deckId, $userId);
                $stmt->execute();
                $deckResult = $stmt->get_result();
                $deck = $deckResult->fetch_assoc();
                $stmt->close();

                if (!$deck) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Deck not found or access denied."
                    ]);
                    exit;
                }

                $stmt = $conn->prepare("SELECT * FROM cards WHERE deck_id = ?");
                $stmt->bind_param("i", $deckId);
                $stmt->execute();
                $cardsResult = $stmt->get_result();
                $cards = [];
                while ($row = $cardsResult->fetch_assoc()) {
                    $cards[] = $row;
                }
                $stmt->close();
            } catch (Exception $e) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error fetching deck or cards: " . $e->getMessage()
                ]);
                exit;
            }

            $todayStats = [
                'studied' => 0,
                'total_studied' => 0,
                'reviewed' => 0,
                'total_reviewed' => 0
            ];

            try {
                $stmt = $conn->prepare("SELECT COUNT(*) AS studied_today FROM cards WHERE deck_id = ? AND last_studied = CURDATE()");
                $stmt->bind_param("i", $deckId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $todayStats['studied'] = $row['studied_today'];
                }
                $stmt->close();

                $stmt = $conn->prepare("SELECT COUNT(*) as total_studied FROM cards WHERE deck_id = ?");
                $stmt->bind_param("i", $deckId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $todayStats['total_studied'] = $row['total_studied'];
                }
                $stmt->close();

                $stmt = $conn->prepare("SELECT COUNT(*) AS reviewed_today FROM cards WHERE deck_id = ? AND last_review_date = CURDATE()");
                $stmt->bind_param("i", $deckId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $todayStats['reviewed'] = $row['reviewed_today'];
                }
                $stmt->close();

                $stmt = $conn->prepare("SELECT COUNT(*) AS total_reviewed FROM cards WHERE deck_id = ? AND (next_review_date <= CURDATE() OR last_review_date = CURDATE())");
                $stmt->bind_param("i", $deckId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $todayStats['total_reviewed'] = $row['total_reviewed'];
                }
                $stmt->close();
            } catch (Exception $e) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error fetching today's stats: " . $e->getMessage()
                ]);
                exit;
            }

            echo json_encode([
                "status" => "success",
                "deck" => $deck,
                "cards" => $cards,
                "today_stats" => $todayStats
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "User ID and Deck ID are required."
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

?>