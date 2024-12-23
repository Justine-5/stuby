<?php
require_once '../db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token']) && $_POST['token'] === 'StubyBuddy1') {
    if (!isset($_POST['user_id'], $_POST['card_id'], $_POST['answer'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit;
    }

    $userId = intval($_POST['user_id']);
    $cardId = intval($_POST['card_id']);
    $answer = $_POST['answer'];

    if (!in_array($answer, ['forgot', 'hard', 'good', 'easy'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid answer value.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("
            SELECT card_interval, ease_factor, repetition_count 
            FROM cards 
            WHERE id = ? AND deck_id IN (SELECT id FROM decks WHERE account_id = ?)
        ");
        $stmt->bind_param("ii", $cardId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $card = $result->fetch_assoc();
        $stmt->close();

        if (!$card) {
            echo json_encode(['success' => false, 'message' => 'Card not found or unauthorized access.']);
            exit;
        }

        $oldInterval = $card['card_interval'];
        $easeFactor = $card['ease_factor'];
        $repetitionCount = $card['repetition_count'];

        switch ($answer) {
            case 'forgot':
                $interval = 0;
                $easeFactor = max(1.3, $easeFactor - 0.3);
                break;
            case 'hard':
                $interval = max(1, $oldInterval * 0.8);
                $easeFactor = max(1.3, $easeFactor - 0.15);
                break;
            case 'good':
                $interval = max(1, $oldInterval * $easeFactor);
                break;
            case 'easy':
                $interval = max(1, $oldInterval * $easeFactor * 1.4);
                $easeFactor = min(3, $easeFactor + 0.15);
                break;
        }

        $repetitionCount++;
        $roundedInterval = round($interval);
        $nextReviewDate = date('Y-m-d', strtotime("+$roundedInterval days"));
        $easeFactor = round($easeFactor, 2);

        $stmt = $conn->prepare("
            UPDATE cards 
            SET card_interval = ?, ease_factor = ?, repetition_count = ?, next_review_date = ?, last_review_date = NOW() 
            WHERE id = ?
        ");
        $stmt->bind_param("ddisi", $interval, $easeFactor, $repetitionCount, $nextReviewDate, $cardId);
        $stmt->execute();
        $stmt->close();

        require_once '../functions.php';
        updateStreak($userId, $conn);

        echo json_encode([
            'success' => true,
            'message' => 'Card updated successfully.',
            'next_review_date' => $nextReviewDate
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating card: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method or token.']);
}
