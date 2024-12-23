<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['LoggedIn']) || !$_SESSION['LoggedIn']) {
    header("Location: login.php");
    exit;
}

require_once 'nav.php';
require_once 'sidebar.php';

$userId = $_SESSION['UserId'];


// Handle username change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['changeUsername'])) {
    $newUsername = trim($_POST['username']);

    // Check if the new username is empty
    if (empty($newUsername)) {
        $_SESSION['accountUpdateError'] = "Username cannot be empty.";
    } else {
        // Update the username
        $stmt = $conn->prepare("UPDATE accounts SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $newUsername, $userId);

        if ($stmt->execute()) {
            $_SESSION['Username'] = $newUsername;
            $_SESSION['accountUpdateSuccess'] = "Username updated Successfully!";
        } else {
            $_SESSION['accountUpdateError'] = "Failed to update username. Please try again.";
        }
    }
    header("Location: settings.php");
    exit;
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['oldPassword'], $_POST['newPassword'], $_POST['confirmNewPassword']) && isset($_POST['changePassword'])) {
    $oldPassword = $_POST['oldPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmNewPassword = $_POST['confirmNewPassword'];

    // Check if the old password matches the current password
    $stmt = $conn->prepare("SELECT password FROM accounts WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (password_verify($oldPassword, $row['password'])) {
        // Check if new password and confirm new password match
        if ($newPassword === $confirmNewPassword) {
            // Update password
            $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE accounts SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedNewPassword, $userId);

            if ($stmt->execute()) {
                $_SESSION['accountUpdateSuccess'] = "Password updated Successfully!";
            } else {
                $_SESSION['accountUpdateError'] = "Failed to update password. Please try again.";
            }
        } else {
            $_SESSION['accountUpdateError'] = "New passwords do not match.";
        }
    } else {
        $_SESSION['accountUpdateError'] = "Old password is incorrect.";
    }
    header("Location: settings.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/general.css">
    <link rel="stylesheet" href="styles/settings.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rammetto+One&display=swap" rel="stylesheet">
    <script src="script/home.js" defer></script>
    <link rel="icon" type="image/png" href="images/icon_app.png">
    <title>Decks</title>
</head>

<body>
    <?php renderNav(true); ?>
    <?php renderSidebar('settings'); ?>

    <main>
        <section id="account-section">
            <?php
                if (isset($_SESSION['accountUpdateError'])) {
                    echo '<p class="accountUpdateError">' . htmlspecialchars($_SESSION['accountUpdateError']) . "</p>";
                    unset($_SESSION['accountUpdateError']);
                }
                if (isset($_SESSION['accountUpdateSuccess'])) {
                    echo '<p class="accountUpdateSuccess">' . htmlspecialchars($_SESSION['accountUpdateSuccess']) . "</p>";
                    unset($_SESSION['accountUpdateSuccess']);
                }
            ?>
            <div class="head-label">
                <h2>Accounts</h2>
            </div>

            <!-- Display accountUpdateErrors or accountUpdateSuccess -->
            

            <!-- Change Username Form -->
            <div>
                <form method="post" action="settings.php" class="account-forms">
                    <h3>Change Username</h3>
                    <label for="changeUsername">Username</label>
                    <input id="changeUsername" name="username" type="text" placeholder="Enter new username" value="<?= htmlspecialchars($_SESSION['Username']) ?>" required>
                    <button name="changeUsername" type="submit">Save</button>
                </form>
            </div>

            <!-- Change Password Form -->
            <div>
                <form method="post" action="settings.php" class="account-forms">
                    <h3>Change Password</h3>
                    <label for="oldPass">Old Password</label>
                    <input id="oldPass" name="oldPassword" type="password" placeholder="Enter old password" required>

                    <label for="newPass">New Password</label>
                    <input id="newPass" name="newPassword" type="password" placeholder="Enter new password" required>

                    <label for="confirmNewPass">Confirm Password</label>
                    <input id="confirmNewPass" name="confirmNewPassword" type="password" placeholder="Confirm new password" required>

                    <button name="changePassword" type="submit">Save</button>
                </form>
            </div>
        </section>
    </main>
</body>

</html>