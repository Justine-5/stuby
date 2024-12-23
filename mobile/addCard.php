<?php
require_once "../db.php";
header('Content-Type: application/json');

if (isset($_POST['token']) && $_POST['token'] === 'StubyBuddy1') {
	if (isset($_POST['user_id'])) {
		$userId = (int)$_POST['user_id'];
		$deckId = (int)$_POST['deck_id'];

		$cardFront = trim($_POST['front']);
		$cardBack = trim($_POST['back']);

		// Validate card input
		if (empty($cardFront) || empty($cardBack)) {
			echo json_encode([
				"status" => "error",
				"message" => "Both front and back of the card are required."
			]);
			exit;
		}

		try {
			$stmt = $conn->prepare("INSERT INTO cards (deck_id, front, back) VALUES (?, ?, ?)");
			$stmt->bind_param("iss", $deckId, $cardFront, $cardBack);
			$stmt->execute();
			$cardId = $conn->insert_id;
			$stmt->close();

			echo json_encode([
				"status" => "success",
				"message" => "Card added successfully!",
				"card_id" => $cardId
			]);
		} catch (Exception $e) {
			echo json_encode([
				"status" => "error",
				"message" => "Error adding card: " . $e->getMessage()
			]);
		}
	} else {
		echo json_encode([
			"status" => "error",
			"message" => "User ID is required."
		]);
	}
} else {
	echo json_encode([
		"status" => "error",
		"message" => "Invalid token or token is missing."
	]);
}
