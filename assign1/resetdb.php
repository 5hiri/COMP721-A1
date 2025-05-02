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

$reset_error = null;
$reset_success = null;  

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
    }  else {
        // Table exists, proceed with dropping it
        $dropSql = "DROP TABLE $tableName"; // SQL to drop the table

        if (mysqli_query($conn, $dropSql)) {
            // Query executed successfully
            $reset_success[] = "Table '$tableName' successfully dropped.";
        } else {
            // Query failed
            $reset_error[] = "Error dropping table '$tableName': " . mysqli_error($conn);
        }
    }
    // --- End table check/create ---
    
    mysqli_close($conn); // Close connection when done
}

// Store message in session
if ($reset_success) {
    $_SESSION['successes'] = $reset_success;
} elseif ($reset_error) {
    $_SESSION['errors'] = $reset_error;
}

// Redirect back to status page after attempting reset
header("Location: status.php");
exit; // Stop script execution after redirect
?>
