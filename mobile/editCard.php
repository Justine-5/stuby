<?php
require_once '../db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token']) && $_POST['token'] === 'StubyBuddy1') {
    if (isset($_POST['user_id'], $_POST['card_id'], $_POST['front'], $_POST['back'])) {
        $userId = intval($_POST['user_id']);
        $cardId = intval($_POST['card_id']);
        $front = trim($_POST['front']);
        $back = trim($_POST['back']);

        if (empty($userId) || empty($cardId) || empty($front) || empty($back)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid data: All fields are required.'
            ]);
            exit;
        }

        try {
            $stmt = $conn->prepare("
                UPDATE cards 
                SET front = ?, back = ? 
                WHERE id = ? AND deck_id IN (SELECT id FROM decks WHERE account_id = ?)
            ");
            $stmt->bind_param("ssii", $front, $back, $cardId, $userId);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Card updated successfully!'
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
                'message' => 'Error updating card: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required fields (user_id, card_id, front, back).'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid token or request method.'
    ]);
}
