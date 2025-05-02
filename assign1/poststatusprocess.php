<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Post Status Processing</title>
        <link rel="stylesheet" type="text/css" href="style/statusform.css" />
        <link rel="stylesheet" type="text/css" href="style/style.css" />
    </head>
    <body>
    <?php
        session_start(); // Start the session to access session data
        ini_set('display_errors', 1); // Show errors on screen
        ini_set('display_startup_errors', 1); // Show startup errors
        error_reporting(E_ALL); // Report all errors

        // Initialize variables to hold form data and errors
        $errors = [];
        $successes = []; // Initialize an array to hold success messages
        $formWasProcessed = false; // Flag to track if processing was attempted

        // Database connection details
        require_once('../../files/sqlinfo.inc.php');

        // mysqli_connect returns false if connection failed, otherwise a connection value
        $conn = @mysqli_connect($sql_host,
            $sql_user,
            $sql_pass,
            $sql_db
        );

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
                array_push($errors, "Table '$tableName' not found, attempting to create it...");
                $createTableSql = "
                CREATE TABLE $tableName (
                    code VARCHAR(10) PRIMARY KEY,
                    statusinfo VARCHAR(255) NOT NULL,
                    status_date DATE NOT NULL,
                    share_option VARCHAR(20),
                    permissions VARCHAR(100)
                )";
                if (mysqli_query($conn, $createTableSql)) {
                    array_push($successes, "Table '$tableName' created successfully.");
                    $formWasProcessed = true; // Set flag to indicate processing was successful
                } else {
                    $errors[] = "Error creating table '$tableName': " . mysqli_error($conn);
                }
            }
            // --- End table check/create ---

            // Check if form data exists in the session
            if (isset($_SESSION['form_data'])) {
                // Retrieve data from session
                $formData = $_SESSION['form_data'];

                $statusCode = $formData['statusCode'];
                $status = $formData['status'];
                $dateValue = $formData['dateValue'];
                $shareOption = $formData['shareOption'];
                $permissions = $formData['permissions'];

                $sql = "INSERT INTO StatusCodes (code, statusinfo, status_date, share_option, permissions) VALUES (?, ?, ?, ?, ?)";

                // Initialize a prepared statement
                $stmt = mysqli_stmt_init($conn);

                // Prepare the statement
                if (mysqli_stmt_prepare($stmt, $sql)) {
                    // Convert permissions array to string (e.g., comma-separated)
                    $permissionsString = implode(', ', $permissions);

                    // Bind parameters to the placeholders
                    // 's' denotes string type, 'd' for double/decimal, 'i' for integer, 'b' for blob
                    mysqli_stmt_bind_param($stmt, "sssss",
                        $statusCode,
                        $status,
                        $dateValue,
                        $shareOption,
                        $permissionsString // Bind the string version
                    );

                    // Execute the prepared statement
                    if (mysqli_stmt_execute($stmt)) {
                        // --- Clean up session ---
                        unset($_SESSION['form_data']);
                        $formWasProcessed = true; // Set flag to indicate processing was successful
                        array_push($successes, "Status '$statusCode' created successfully.");
                    } else {
                        $errors[] =  "Error executing statement: " . mysqli_stmt_error($stmt);
                    }

                    // Close the statement
                    mysqli_stmt_close($stmt);

                } else {
                    $errors[] = "Error preparing statement: " . mysqli_error($conn);
                }

                // Close the connection (usually done after all queries)
                mysqli_close($conn);

            } else {
                // If accessed directly without session data, redirect back to the form
                header("Location: poststatusform.php");
                exit;
            }
        }
        ?>
        <div class="content">
            <h1>Status Posting System</h1>
            <!-- Area for displaying messages -->
            <div id="messages" class="message-container">
                <!-- Display ALL Errors -->
                <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <p class="error"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Display ALL Success Messages -->
                <?php if (!empty($successes)): ?>
                    <?php foreach ($successes as $success): ?>
                        <p class="success"><?php echo htmlspecialchars($success); ?></p>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Display ALL Errors -->
                <?php if (!empty($displayErrors)): ?>
                    <?php foreach ($displayErrors as $error): ?>
                        <p class="error"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!$formWasProcessed && empty($successes) && empty($displayErrors) && empty($errors)): ?>
                    <p>No status information processed.</p>
                <?php endif; ?>
            </div>
            
            <div class="urls">
                <div class="left">
                    <a href="index.html">Home</a>
                    <a href="poststatusform.php">Post Status Form</a>
                </div>
                <div class="right">
                    <a class="align-right" href="searchstatusform.html">Search Status</a>
                    <a class="align-right" href="about.html">About this assignment</a>
                </div>
            </div>
        </div>
    </body>
</html>