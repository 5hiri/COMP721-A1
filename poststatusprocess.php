<?php
session_start(); // Start the session to access session data

// Check if form data exists in the session
if (isset($_SESSION['form_data'])) {
    // Retrieve data from session
    $formData = $_SESSION['form_data'];

    $statusCode = $formData['statusCode'];
    $status = $formData['status'];
    $dateValue = $formData['dateValue'];
    $shareOption = $formData['shareOption'];
    $permissions = $formData['permissions'];

    

} else {
    // If accessed directly without session data, redirect back to the form
    header("Location: poststatusform.php");
    exit;
}

?>