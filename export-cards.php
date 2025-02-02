<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['LoggedIn']) || !$_SESSION['LoggedIn']) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['UserId'];
$deckId = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    $stmt = $conn->prepare("SELECT id, name FROM decks WHERE id = ? AND account_id = ?");
    $stmt->bind_param("ii", $deckId, $userId);
    $stmt->execute();
    $deck = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$deck) {
        die("Deck not found or access denied.");
    }

    $stmt = $conn->prepare("SELECT front, back FROM cards WHERE deck_id = ? ORDER BY front ASC");
    $stmt->bind_param("i", $deckId);
    $stmt->execute();
    $result = $stmt->get_result();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$deck["name"].'.csv"');

    $output = fopen('php://output', 'w');

    fputcsv($output, ['Front', 'Back']);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [$row['front'], $row['back']]);
    }

    fclose($output);
} catch (Exception $e) {
    die("Error exporting cards: " . $e->getMessage());
}
?>
