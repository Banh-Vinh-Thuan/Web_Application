<?php
include '../dbconnect.php';

// Initialize variables for journey data
$city = $region = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve journey data from the form if available
    $city = isset($_POST["city"]) ? $_POST["city"] : "";
    $region = isset($_POST["region"]) ? $_POST["region"] : "";

    // Prepare a SQL statement to insert the journey
    $sql = "INSERT INTO cities (city, region) VALUES (?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ss", $city, $region);

        if ($stmt->execute()) {
            // Insertion was successful
            echo "Journey added successfully. <a href='adminviewcity.php'>Back to journeys</a>";
            header("location: adminviewcity.php");
        } else {
            // Error occurred during insertion
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error in the prepared statement: " . $conn->error;
    }
} 

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../css/addjourney.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <title>Admin - Add City</title>
    <style>
        body {
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed; /* Ensures the image doesn't scroll with the content */
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <form method="post" action="addjourney.php">
        <h2>Add City</h2><hr weight="4px">
        <label for="city">City:</label>
        <required><input type="text" id="city" name="city" placeholder="City"></required><br><br>

        <label for="region">Region:</label>
        <required><input type="text" id="region" name="region" placeholder="Region"></required><br><br>

        <input type="submit" value="Add Journey">
        <a href="adminviewcity.php" style="margin-left: 15px; background-color: #cc0000; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold;">Cancel</a>
    </form>
</body>
</html>