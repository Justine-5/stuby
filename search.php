<?php

require_once 'db.php';

$searchTerm = isset($_GET['query']) ? trim($_GET['query']) : '';

$response = [];

if (!empty($searchTerm)) {
    $searchTerm = "%$searchTerm%";

    $stmt = $conn->prepare("
        SELECT id, name 
        FROM decks 
        WHERE name LIKE ? 
        AND account_id = ? 
        LIMIT 10
    ");

    session_start();
    $accountId = $_SESSION['UserId'];

    $stmt->bind_param("si", $searchTerm, $accountId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch and format results
    while ($row = $result->fetch_assoc()) {
        $response[] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }

    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($response);
