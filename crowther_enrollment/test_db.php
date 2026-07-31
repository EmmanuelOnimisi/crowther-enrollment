<?php
include 'includes/config.php';

echo "<h1>Database Connection Test</h1>";
echo "<p>Connected successfully to <strong>" . $db_name . "</strong></p>";

// Test query
$result = mysqli_query($conn, "SELECT * FROM users");
echo "<h3>Users in database:</h3>";
echo "<ul>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<li>" . $row['fullname'] . " - " . $row['email'] . " - " . $row['role'] . "</li>";
}
echo "</ul>";

mysqli_close($conn);
?>