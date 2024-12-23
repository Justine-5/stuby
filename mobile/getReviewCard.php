<?php
require_once '../db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token']) && $_POST['token'] === 'StudyBuddy1') {
    if (!isset($_POST['user_id'], $_POST['deck_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit;
    }

    $userId = intval($_POST['user_id']);
    $deckId = intval($_POST['deck_id']);

    try {
        $stmt = $conn->prepare("SELECT name FROM decks WHERE id = ? AND account_id = ?");
        $stmt->bind_param("ii", $deckId, $userId);
        $stmt->execute();
        $deckResult = $stmt->get_result();
        $deck = $deckResult->fetch_assoc();
        $stmt->close();

        if (!$deck) {
            echo json_encode(['success' => false, 'message' => 'Deck not found or unauthorized access.']);
            exit;
        }

        $stmt = $conn->prepare("
            SELECT id, front, back, card_interval, ease_factor, repetition_count
            FROM cards
            WHERE deck_id = ? AND next_review_date <= CURDATE()
            ORDER BY RAND()
            LIMIT 1
        ");
        $stmt->bind_param("i", $deckId);
        $stmt->execute();
        $cardResult = $stmt->get_result();
        $card = $cardResult->fetch_assoc();
        $stmt->close();

        if (!$card) {
            echo json_encode(['success' => false, 'message' => 'No cards available for review.']);
            exit;
        }

        $oldInterval = $card['card_interval'];
        $easeFactor = $card['ease_factor'];
        $repetitionCount = $card['repetition_count'];

        $options = [
            'forgot' => 0,
            'hard' => round(max(1, $oldInterval * 0.8)),
            'good' => round(max(1, $oldInterval * $easeFactor)),
            'easy' => round(max(1, $oldInterval * $easeFactor * 1.4))
        ];
        
        $stmt = $conn->prepare("
            SELECT COUNT(*) AS card_count
            FROM cards
            WHERE deck_id = ? AND next_review_date <= CURDATE()
        ");
        $stmt->bind_param("i", $deckId);
        $stmt->execute();
        $cardsResult = $stmt->get_result();
        $cardCount = $cardsResult->fetch_assoc();
        $card_count = $cardCount['card_count'];

        echo json_encode([
            'success' => true,
            'message' => 'Card fetched successfully.',
            'card' => $card,
            'review_options' => $options,
            'card_count' => $card_count
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching card: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method or token.']);
}
