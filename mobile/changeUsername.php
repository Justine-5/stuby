<?php
require_once '../db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token']) && $_POST['token'] === 'StubyBuddy1') {
    if (!isset($_POST['user_id'], $_POST['username'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit;
    }

    $userId = intval($_POST['user_id']);
    $newUsername = trim($_POST['username']);

    if (empty($newUsername)) {
        echo json_encode(['success' => false, 'message' => 'Username cannot be empty.']);
        exit;
    }

    try {
        // Update the username
        $stmt = $conn->prepare("UPDATE accounts SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $newUsername, $userId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Username updated successfully.', 'username' => $newUsername]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update username. Please try again.']);
        }
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating username: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method or token.']);
}
