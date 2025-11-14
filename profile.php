<?php
session_start();
require_once __DIR__ . '/config.php';
$conn = getDBConnection();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'] ?? $_SESSION['acc_id'] ?? null;

if (!$userId) {
    header("Location: login.php");
    exit();
}

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $contNo = trim($_POST['contNo'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $birthdate = $_POST['birthdate'] ?? null;
    
    if (empty($firstName) || empty($lastName)) {
        $message = "First name and last name are required.";
        $messageType = 'error';
    } else {
        $stmt = $conn->prepare("UPDATE USER_ACCOUNT SET firstName = ?, lastName = ?, contNo = ?, address = ?, birthdate = ? WHERE acc_id = ?");
        $stmt->bind_param("sssssi", $firstName, $lastName, $contNo, $address, $birthdate, $userId);
        
        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
            $messageType = 'success';
            // Update session name
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        } else {
            $message = "Error updating profile: " . $conn->error;
            $messageType = 'error';
        }
        $stmt->close();
    }
}

// Get user data
$stmt = $conn->prepare("SELECT acc_id, firstName, lastName, email, contNo, address, birthdate, time_created FROM USER_ACCOUNT WHERE acc_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user) {
    header("Location: login.php");
    exit();
}

// Format birthdate for input
$birthdateFormatted = $user['birthdate'] ? date('Y-m-d', strtotime($user['birthdate'])) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Ticketix</title>
    <link rel="stylesheet" href="css/ticketix-main.css">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
    <div class="profile-container">
        <div class="page-header">
            <div class="profile-avatar">
                <?php
                $initials = strtoupper(substr($user['firstName'], 0, 1) . substr($user['lastName'], 0, 1));
                echo $initials;
                ?>
            </div>
            <h1>My Profile</h1>
            <p>Manage your personal information</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="account-info">
            <div class="account-info-item">
                <span class="account-info-label">Email:</span>
                <span class="account-info-value"><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <div class="account-info-item">
                <span class="account-info-label">Account Created:</span>
                <span class="account-info-value"><?= date('F d, Y', strtotime($user['time_created'])) ?></span>
            </div>
        </div>

        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label for="firstName">First Name *</label>
                    <input type="text" id="firstName" name="firstName" value="<?= htmlspecialchars($user['firstName']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name *</label>
                    <input type="text" id="lastName" name="lastName" value="<?= htmlspecialchars($user['lastName']) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="contNo">Contact Number</label>
                    <input type="text" id="contNo" name="contNo" value="<?= htmlspecialchars($user['contNo'] ?? '') ?>" placeholder="09123456789">
                </div>
                <div class="form-group">
                    <label for="birthdate">Birthdate</label>
                    <input type="date" id="birthdate" name="birthdate" value="<?= $birthdateFormatted ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" placeholder="Enter your address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="TICKETIX NI CLAIRE.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
            </div>
        </form>

        <div class="text-center">
            <a href="TICKETIX NI CLAIRE.php" class="back-link">← Back to Homepage</a>
        </div>
    </div>
</body>
</html>

