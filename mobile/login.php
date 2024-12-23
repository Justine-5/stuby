<?php
require_once "../db.php";

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (isset($data['token'])) {
    $token = $data['token'];

    if ($token === 'StubyBuddy1') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['email'], $data['password'])) {
            $email = trim($data['email']);
            $password = $data['password'];

            if (empty($email) || empty($password)) {
                echo json_encode(["status" => "error", "message" => "All fields are required."]);
                return;
            }

            $stmt = $conn->prepare("SELECT * FROM accounts WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "Login successful",
                        "user" => [
                            "username" => $user["username"],
                            "email" => $user['email'],
                            "userId" => $user['id']
                        ]
                    ]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
            }

            $stmt->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Missing fields"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid token"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Token not provided"]);
}
?>
