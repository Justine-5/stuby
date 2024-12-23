<?php
require_once '../db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token']) && $_POST['token'] === 'StubyBuddy1') {

    if (!isset($_POST['user_id'], $_POST['card_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit;
    }

    $userId = intval($_POST['user_id']);
    $cardId = intval($_POST['card_id']);

    try {
        $stmt = $conn->prepare("UPDATE cards 
                                SET last_studied = CURRENT_DATE() 
                                WHERE id = ? AND deck_id IN (SELECT id FROM decks WHERE account_id = ?)");
        $stmt->bind_param("ii", $cardId, $userId);
        $stmt->execute();
        $stmt->close();

        require_once '../functions.php';
        updateStreak($userId, $conn);

        echo json_encode(['success' => true, 'message' => 'Last studied updated successfully.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating last studied: ' . $e->getMessage()]);
    }
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method or token.']);
}
