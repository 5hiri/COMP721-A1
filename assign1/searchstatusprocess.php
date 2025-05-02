<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Status Search Page</title>
        <link rel="stylesheet" href="style/search_results.css">
        <link rel="stylesheet" href="style/style.css">
    </head>
    <body>
        <?php
        session_start(); // Start the session to access session data
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        // Database connection details
        require_once('../../files/sqlinfo.inc.php'); // Include your SQL info file

        // --- Handle Pagination Action FIRST ---
        if (isset($_GET['action'])) {
            $totalPages = isset($_SESSION['pages']) ? $_SESSION['pages'] : 0; // Get total pages if set
            $currentPage = isset($_SESSION['page']) ? $_SESSION['page'] : 1; // Get current page if set

            if ($_GET['action'] == 'next' && $totalPages > 0 && $currentPage < $totalPages) {
                $_SESSION['page']++;
                // Redirect to remove the action parameter from URL after processing
                header("Location: searchstatusprocess.php?search=" . urlencode($_GET['search'] ?? '')); // Keep search term
                exit;
            } elseif ($_GET['action'] == 'prev' && $currentPage > 1) {
                $_SESSION['page']--;
                // Redirect to remove the action parameter from URL after processing
                header("Location: searchstatusprocess.php?search=" . urlencode($_GET['search'] ?? '')); // Keep search term
                exit;
            }
        }

        if (isset($_SESSION['successes'])) {
            // Display success message if set in session
            $successes = $_SESSION['successes'];
            unset($_SESSION['successes']); // Clear the success message after displaying it
        }

        if (!isset($_SESSION['old_search'])) {
            $_SESSION['old_search'] = ""; // Initialize old_search in session if not set
        }

        if ($_SESSION['old_search'] !== $_GET['search']) {
            // Reset page to 1 if a new search term is provided
            $_SESSION['page'] = 1; // Reset page number in session
        }

        // mysqli_connect returns false if connection failed, otherwise a connection value
        $conn = @mysqli_connect($sql_host,
            $sql_user,
            $sql_pass,
            $sql_db
        );

        $errors = []; // Initialize an array to hold error messages
        $resultRows = []; // Initialize resultRows as empty array
        $searchTerm = isset($_GET['search']) ? $_GET['search'] : ''; // Initialize searchTerm

        if (isset($_SESSION['page'])) {
            $page = $_SESSION['page']; // Retrieve the page number from the session
        } else {
            $page = 1; // Default to page 0 if not set in session
            $_SESSION['page'] = $page; // Store the page number in the session
        }

        $mpage = ($page - 1) * 3; // Calculate the starting index for the current page
        $lpage = $mpage + 3; // Calculate the ending index for the current page

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
            if (isset($_GET['search'])) {
                $searchTerm = mysqli_real_escape_string($conn, $_GET['search']);
                $searchSql = "SELECT * FROM $tableName WHERE statusinfo LIKE '%$searchTerm%'";
                if ($searchTerm == "*") {
                    $searchSql = "SELECT * FROM $tableName";
                }
                $searchResult = mysqli_query($conn, $searchSql);
                $old_search = $_SESSION['old_search'];
                if ($searchTerm !== $old_search) {
                    $_SESSION['old_search'] = $searchTerm; // Store the new search term in session
                }
                if (!$searchResult) {
                    $errors[] = "Error executing search query: " . mysqli_error($conn);
                } else {
                    $numResults = mysqli_num_rows($searchResult);
                    if ($numResults > 0) {
                        $count = 1;
                        $local_row = array();
                        while ($row = mysqli_fetch_assoc($searchResult)) {
                            array_push($local_row, $row);
                            if ($count % 4 == 0 || $count == $numResults) {
                                array_push($resultRows, $local_row);
                                $local_row = array();
                            }
                            $count++;
                        }
                    }
                }
            }
            mysqli_close($conn); // Close connection when done
            $_SESSION['pages'] = ceil(count($resultRows)/3); // Store the number of pages in the session
        }
        ?>
        <div class="content">
            <h1>Status Posting System</h1>

            <form class="search-form" method="get" action="searchstatusprocess.php">
                <label for="search">Search Status:</label>
                <input type="text" id="search" name="search" placeholder="Enter search term..." value="<?php echo htmlspecialchars($searchTerm); ?>" required>
                <input type="submit" value="Show Results">
            </form>

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

            <div class="search-results">
                <?php if (!empty($resultRows)): ?>
                    <h2>Search Results for '<?php echo htmlspecialchars($searchTerm); ?>':</h2>
                    
                    <!-- Pagination Controls -->
                    <div class="pagination">
                        <?php
                        $totalPages = $_SESSION['pages'] ?? 0;
                        // Show Previous button if not on the first page
                        if ($page > 1): ?>
                            <a class="active" href="searchstatusprocess.php?action=prev&search=<?php echo urlencode($searchTerm); ?>" class="prev">Previous</a>
                        <?php else: ?>
                            <a class="disabled" href="" class="prev">Previous</a>
                        <?php endif; ?>

                        <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>

                        <?php // Show Next button if not on the last page
                        if ($page < $totalPages): ?>
                            <a class="active" href="searchstatusprocess.php?action=next&search=<?php echo urlencode($searchTerm); ?>" class="next">Next</a>
                        <?php else: ?>
                            <a class="disabled" href="" class="next">Next</a>
                        <?php endif; ?>
                    </div>

                    <?php 
                    $totalRows = count($resultRows);
                    for ($i = $mpage; $i < $lpage; $i++) {
                        if ($i >= $totalRows) {
                            break; // Stop the loop if gone past the available rows
                        }
                        $rowGroup = $resultRows[$i];
                    ?>
                        <div class="result-row">    
                            <?php foreach ($rowGroup as $item) { 
                                ?>
                                <div class="result-item">
                                    <h3>Status Code: <?php echo htmlspecialchars($item['code']); ?></h3>
                                    <p>Status: <?php echo htmlspecialchars($item['statusinfo']); ?></p>
                                    <p>Date: <?php echo htmlspecialchars($item['status_date']); ?></p>
                                    <p>Share Option: <?php echo htmlspecialchars($item['share_option']); ?></p>
                                    <p>Permissions: <?php echo htmlspecialchars($item['permissions']); ?></p>
                                    <button class="delete-button" onclick="confirmDelete('<?php echo htmlspecialchars($item['code']); ?>')">Delete</button>
                                    <script>
                                        function confirmDelete(code) {
                                            if (confirm("Are you sure you want to delete the status with code: " + code + "?")) {
                                                window.location.href = "deletestatus.php?code=" + code; // Redirect to delete script
                                            }
                                        }
                                    </script>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                <?php else: ?>
                    <h2>No results found for '<?php echo htmlspecialchars($searchTerm); ?>'</h2>
                <?php endif; ?>
            </div>

            <div class="urls">
                <div class="left">
                    <a href="index.html">Home</a>
                    <a href="poststatusform.php">Post a new status</a>
                </div>
                <div class="right">
                    <a class="align-right" href="about.html">About this assignment</a>
                </div>
            </div>
        </div>
    </body>
</html>