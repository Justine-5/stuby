<?php
require_once '../db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token']) && $_POST['token'] === 'StubyBuddy1') {
    if (!isset($_POST['user_id'], $_POST['old_password'], $_POST['new_password'], $_POST['confirm_new_password'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit;
    }

    $userId = intval($_POST['user_id']);
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmNewPassword = $_POST['confirm_new_password'];

    try {
        // Check if the old password matches the current password
        $stmt = $conn->prepare("SELECT password FROM accounts WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row && password_verify($oldPassword, $row['password'])) {
            // Check if new password and confirm password match
            if ($newPassword === $confirmNewPassword) {
                // Update password
                $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE accounts SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashedNewPassword, $userId);

                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update password. Please try again.']);
                }
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Old password is incorrect.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating password: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method or token.']);
}
