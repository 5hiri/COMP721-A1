<?php
session_start();
ini_set('display_errors', 1); // Show errors on screen
ini_set('display_startup_errors', 1); // Show startup errors
error_reporting(E_ALL); // Report all errors

// Initialize variables to hold form data and errors

if (isset($_SESSION['successes'])) {
    $successes = $_SESSION['successes'];
    unset($_SESSION['successes']); // Clear the session variable after use
} elseif (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']); // Clear the session variable after use
} else {
    $errors[] = "No messages to display.";
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Post Status Form</title>
        <link rel="stylesheet" type="text/css" href="style/statusform.css" />
        <link rel="stylesheet" type="text/css" href="style/style.css" />
    </head>
    <body>
        <div class="content">
            <h1>Status Posting System</h1>
            

            <!-- Area for displaying error messages -->
            <!-- Display Success OR Errors -->
            <div id="messages" class="message-container">
                <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <p class="error"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if (!empty($successes)): ?>
                    <?php foreach ($successes as $success): ?>
                        <p class="success"><?php echo htmlspecialchars($success); ?></p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="urls">
                <div class="left">
                    <a href="index.html">Home</a>
                </div>
            </div>
        </div>
    </body>
</html>