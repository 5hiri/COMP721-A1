<?php 
session_start(); // Start the session to access session data
ini_set('display_errors', 1); // Show errors on screen
ini_set('display_startup_errors', 1); // Show startup errors
error_reporting(E_ALL); // Report all errors

// Database connection details
require_once('../../files/sqlinfo.inc.php');

// mysqli_connect returns false if connection failed, otherwise a connection value
$conn = @mysqli_connect($sql_host,
    $sql_user,
    $sql_pass,
    $sql_db
);

$successes = []; // Initialize an array to hold success messages
$errors = []; // Initialize an array to hold error messages

// Checks if connection is successful
if (!$conn) {
    // Displays an error message
    $errors[] = "Database connection failure";
} else {
    // --- Check if table exists and create if not ---
    $tableName = 'StatusCodes';
    $checkTableSql = "SHOW TABLES LIKE '$tableName'";
    $tableResult = mysqli_query($conn, $checkTableSql);

    if (!$tableResult) {
        // Error checking the SHOW TABLES query
        $errors[] = "Error checking for table $tableName: " . mysqli_error($conn);
    } elseif (mysqli_num_rows($tableResult) == 0) {
        // Table does not exist, create it
        $errors[] = "Table '$tableName' not found, visit post status form...";
        header("Location: poststatusform.php"); // Redirect to the form page
        exit; // Stop script execution after redirect
    }
    // --- End table check/create ---

    if (isset($_GET['code'])) {
        // Get the status code from the form submission
        $statusCode = $_GET['code'];

        // Prepare the SQL statement to delete the status code
        $deleteSql = "DELETE FROM StatusCodes WHERE code = ?";
        $stmt = mysqli_prepare($conn, $deleteSql);
        mysqli_stmt_bind_param($stmt, 's', $statusCode);

        // Execute the statement and check for success
        if (mysqli_stmt_execute($stmt)) {
            $successes[] = "Status code '$statusCode' deleted successfully.";
            $_SESSION['successes'] = $successes; // Store success messages in session
        } else {
            $errors[] = "Error deleting status code '$statusCode': " . mysqli_error($conn);
        }

        // Close the prepared statement
        mysqli_stmt_close($stmt);
    }
    
    mysqli_close($conn); // Close connection when done
}
header("Location: searchstatusprocess.php?search=" . urlencode($_SESSION['old_search'] ?? '')); // Keep search term
?>