<?php
require_once '../db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token']) && $_POST['token'] === 'StubyBuddy1') {
    if (isset($_POST['user_id'], $_POST['card_id'])) {
        $userId = intval($_POST['user_id']);
        $cardId = intval($_POST['card_id']);

        if ($cardId <= 0 || $userId <= 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid user ID or card ID.'
            ]);
            exit;
        }

        try {
            $stmt = $conn->prepare("
                DELETE FROM cards 
                WHERE id = ? AND deck_id IN (SELECT id FROM decks WHERE account_id = ?)
            ");
            $stmt->bind_param("ii", $cardId, $userId);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Card deleted successfully!'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Card not found or unauthorized access.'
                ]);
            }

            $stmt->close();
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error deleting card: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required fields (user_id, card_id).'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid token or request method.'
    ]);
}
