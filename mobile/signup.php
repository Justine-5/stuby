<?php
require_once "../db.php";

if (empty($_POST)) {
    $_POST = json_decode(file_get_contents("php://input"), true);
}

if (isset($_POST['token'])) {
    $token = $_POST['token'];
    if ($token === 'StubyBuddy1') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['confirmPassword'])) {
            $un = trim($_POST['username']);
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $pass = $_POST['password'];
            $confirmPass = $_POST['confirmPassword'];

            if (empty($un) || empty($email) || empty($pass) || empty($confirmPass)) {
                echo json_encode(["status" => "error", "message" => "All fields are required"]);
                return;
            }

            if (!$email) {
                echo json_encode(["status" => "error", "message" => "Invalid Email Format"]);
                return;
            }

            if ($pass === $confirmPass) {
                $sql = "SELECT * FROM accounts WHERE email=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo json_encode(["status" => "error", "message" => "Email already taken"]);
                } else {
                    $insert = "INSERT INTO accounts (username, email, password) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($insert);
                    $hashedPass = password_hash($pass, PASSWORD_BCRYPT);
                    $stmt->bind_param("sss", $un, $email, $hashedPass);

                    if ($stmt->execute()) {
                        $user_id = $conn->insert_id;

                        echo json_encode([
                            "status" => "success",
                            "message" => "Account created successfully",
                            "user_id" => $user_id,
                            "username" => $un,
                            "email" => $email
                        ]);
                    } else {
                        echo json_encode(["status" => "error", "message" => "Failed to create account. Please try again."]);
                    }
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Passwords don't match"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Missing Fields"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid Token"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No value provided"]);
}
?>