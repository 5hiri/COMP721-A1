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

if (isset($_SESSION['form_data'])) {
    $formData = $_SESSION['form_data'];
    $statusCode = $formData['statusCode'];
    $status = $formData['status'];
    $dateValue = $formData['dateValue'];
    $shareOption = $formData['shareOption'];
    $permissions = $formData['permissions'];
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Validation ---
    $statusCode = isset($_POST['stcode']) ? trim($_POST['stcode']) : '';
    $status = isset($_POST['st']) ? trim($_POST['st']) : '';
    $dateValue = isset($_POST['date']) && !empty($_POST['date']) ? trim($_POST['date']) : $currentDate;
    $shareOption = isset($_POST['share_option']) ? $_POST['share_option'] : 'university';
    $permissions = isset($_POST['permission']) && is_array($_POST['permission']) ? $_POST['permission'] : [];

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
        $dateValue = $currentDate;
    } elseif (strlen($statusCode) > 5) {
        $errors[] = "Status Code must be exactly 5 characters long.";
    }

    if (empty($status)) {
        $errors[] = "Status is required.";
    }

    // If there are no errors, process the data (e.g., save to database)
    if (empty($errors)) {
        // Store validated data in session
        $_SESSION['form_data'] = [
            'statusCode' => $statusCode,
            'status' => $status,
            'dateValue' => $dateValue,
            'shareOption' => $shareOption,
            'permissions' => $permissions
        ];

        // Redirect to the processing script
        header("Location: poststatusprocess.php");
        exit;
    }
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
                <div class="radio-buttons">
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
                <div class="permissions">
                    <p>Permission:</p>
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
                        if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($errors)) {
                            echo "<p class=\"success\">Status posted successfully!</p>";
                        }
                    ?>
                </div>

                <input type="submit" value="Submit" />
            </form>
            <div class="urls">
                <div class="left">
                    <a href="index.html">Home</a>
                </div>
                <div class="right">
                    <a class="align-right" href="searchstatusform.html">Search Status</a>
                    <a class="align-right" href="about.html">About this assignment</a>
                </div>
            </div>
        </div>
    </body>
</html>