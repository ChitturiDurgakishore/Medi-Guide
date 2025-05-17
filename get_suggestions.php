<?php
$servername = "sql113.infinityfree.com";
$username = "if0_37435582";
$password = "RdXGj90Owk";
$dbname = "if0_37435582_mediguide";

// Connect to database
$mycon = new mysqli($servername, $username, $password, $dbname);
if ($mycon->connect_error) {
    die("Connection failed: " . $mycon->connect_error);
}

$suggestions = [];
$input = $mycon->real_escape_string($_GET['name']);

// Get all table names
$tablesResult = $mycon->query("SHOW TABLES FROM `$dbname`");
if ($tablesResult) {
    while ($tableRow = $tablesResult->fetch_row()) {
        $tableName = $tableRow[0];

        // Get columns in the current table
        $columnsResult = $mycon->query("SHOW COLUMNS FROM `$tableName`");
        if ($columnsResult) {
            while ($columnRow = $columnsResult->fetch_assoc()) {
                $columnName = $columnRow['Field'];
                
                // Check if the column is likely to store medicine names
                if (stripos($columnName, 'medicine') !== false || stripos($columnName, 'substitute') !== false) {
                    // Search in this column for the input
                    $query = "SELECT DISTINCT `$columnName` FROM `$tableName` WHERE `$columnName` LIKE '%$input%' AND `$columnName` != ''";
                    $result = $mycon->query($query);
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $suggestions[] = htmlspecialchars($row[$columnName]);
                        }
                    }
                }
            }
        }
    }
}

// Remove duplicates
$suggestions = array_unique($suggestions);

// Output suggestions
if (!empty($suggestions)) {
    foreach ($suggestions as $suggestion) {
        echo "<div onclick=\"selectSuggestion('$suggestion')\">$suggestion</div>";
    }
} else {
    echo ""; // No suggestions
}

$mycon->close();
?>
