<?php
session_start();
ini_set('display_errors', 1); // Show errors on screen
ini_set('display_startup_errors', 1); // Show startup errors
error_reporting(E_ALL); // Report all errors

// Initialize variables to hold form data and errors
$errors = [];
$statusCode = '';
$status = '';
$currentDate = date('Y-m-d');
$dateValue = $currentDate; // Use a different name like $dateValue to avoid conflicts
$shareOption = 'university'; // Default value for radio
$permissions = []; // Default for checkboxes
$pattern = '/^[a-zA-Z0-9,.!? ]+$/';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Validation ---
    $statusCode = isset($_POST['stcode']) ? trim($_POST['stcode']) : '';
    $status = isset($_POST['st']) ? trim($_POST['st']) : '';
    $dateValue = isset($_POST['date']) && !empty($_POST['date']) ? trim($_POST['date']) : $currentDate;
    $shareOption = isset($_POST['share_option']) ? $_POST['share_option'] : 'university';
    $permissions = isset($_POST['permission']) && is_array($_POST['permission']) ? $_POST['permission'] : [];

    // Example Validation: Status Code must start with 'S'
    if (empty($statusCode)) {
        $errors[] = "Status Code is required.";
    } elseif (strlen($statusCode) > 0 && $statusCode[0] !== 'S') {
        $errors[] = "Status Code must start with an 'S'.";
    } elseif (strlen($statusCode) < 5) { // Add check for minimum length
        $errors[] = "Status Code must be at least 5 characters long (e.g., S0001).";
    } elseif (!is_numeric($statusCode[1]) || !is_numeric($statusCode[2]) || !is_numeric($statusCode[3]) || !is_numeric($statusCode[4])) { // Corrected check for indices 1, 2, 3
        $errors[] = "The 1st, 2nd, 3rd, and 4th characters of the Status Code must be numeric.";
    } elseif (!preg_match($pattern, $status)) { // Check for valid characters
        $errors[] = "Status Code can only contain letters, numbers, and the following characters: ,.!?";
    } elseif (empty($dateValue)) {
        $errors[] = "Date is required.";
        // You might want to reset $dateValue to $currentDate here if it's invalid
        // $dateValue = $currentDate;
    }

    // Example Validation: Status cannot be empty
    if (empty($status)) {
        $errors[] = "Status is required.";
    }

    // Add more validation rules here...

    // If there are no errors, process the data (e.g., save to database)
    // For now, we'll just assume processing happens if $errors is empty
    if (empty($errors)) {
        // Store validated data in session
        $_SESSION['form_data'] = [
            'statusCode' => $statusCode,
            'status' => $status,
            'dateValue' => $dateValue,
            'shareOption' => $shareOption,
            'permissions' => $permissions
            // Add any other validated data you need
        ];

        // Redirect to the processing script
        header("Location: poststatusprocess.php");
        exit; // Important: Stop script execution after redirect
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Post Status Form</title>
        <link rel="stylesheet" type="text/css" href="css/style.css" />
        <link rel="stylesheet" type="text/css" href="css/statusform.css" />
    </head>
    <body>
        <div class="content">
            <h1>Status Posting System</h1>
            <form class="statusform" action="poststatusform.php" method="post">
                <!-- Status Code Input -->
                <div class="form-row">
                    <label for="stcode">Status Code:</label>
                    <input type="text" id="stcode" name="stcode" required value="<?php echo htmlspecialchars($statusCode); ?>" />
                </div>
                <!-- Status Input -->
                <div class="form-row">
                    <label for="st">Status:</label>
                    <input type="text" id="st" name="st" required value="<?php echo htmlspecialchars($status); ?>" />
                </div>
                <!-- Date Input -->
                <div class="form-row">
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required value="<?php echo htmlspecialchars($dateValue); ?>" />
                </div>
                
                <!-- Add Radio Buttons Here -->
                <div class="radio-buttons"> <!-- Wrap radio buttons for better structure -->
                    <p>Share:</p>
                    <input type="radio" id="share_university" name="share_option" value="university" <?php echo ($shareOption === 'university') ? 'checked' : ''; ?>>
                    <label for="share_university">University</label><br>

                    <input type="radio" id="share_class" name="share_option" value="class" <?php echo ($shareOption === 'class') ? 'checked' : ''; ?>>
                    <label for="share_class">Class</label><br>

                    <input type="radio" id="share_private" name="share_option" value="private" <?php echo ($shareOption === 'private') ? 'checked' : ''; ?>>
                    <label for="share_private">Private</label><br>
                </div>
                <!-- End Radio Buttons -->

                <!-- Permission Checklist -->
                <div class="permissions"> <!-- Wrap checkboxes for better structure -->
                    <p>Permission:</p> <!-- Use a paragraph for the main label -->
                    <input type="checkbox" id="perm_like" name="permission[]" value="like" <?php echo (in_array('like', $permissions)) ? 'checked' : ''; ?>>
                    <label for="perm_like">Allow Like</label><br>

                    <input type="checkbox" id="perm_comments" name="permission[]" value="comments" <?php echo (in_array('comments', $permissions)) ? 'checked' : ''; ?>>
                    <label for="perm_comments">Allow Comments</label><br>

                    <input type="checkbox" id="perm_share" name="permission[]" value="share" <?php echo (in_array('share', $permissions)) ? 'checked' : ''; ?>>
                    <label for="perm_share">Allow Share</label><br>
                </div>
                <!-- End Permission Checklist -->

                <!-- Area for displaying error messages -->
                <div id="error-messages" class="error-container">
                    <?php if (!empty($errors)): ?>
                        <?php foreach ($errors as $error): ?>
                            <p class="error"><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php
                        // Example: Display success message if form submitted without errors
                        if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($errors)) {
                            echo "<p class=\"success\">Status posted successfully!</p>";
                        }
                    ?>
                </div>

                <input type="submit" value="Submit" />
            </form>
            <div class="urls">
                <a href="index.php">Return to Home Page</a>
            </div>
        </div>
    </body>
</html>