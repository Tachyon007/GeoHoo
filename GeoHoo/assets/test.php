<?php
echo "TEST:<br>";


function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Load the environment variables from .env file
loadEnv('../hoo.env');
$servername = getenv('DB_SERVER');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');

$radius = 200; // Radius in meters
$user_lat = 38.9072;
$user_lon = -77.0369;

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT *
        FROM Posts
        WHERE ST_Distance_Sphere(Point(lon, lat), Point($user_lon, $user_lat)) <= $radius";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . "<br>";
        echo "Author ID: " . $row['author_id'] . "<br>";
        echo "Text: " . $row['text'] . "<br>";
        echo "Image URL: " . $row['image_url'] . "<br>";
        echo "Timestamp: " . $row['timestamp'] . "<br>";
        echo "Visible: " . $row['visible'] . "<br>";
        echo "Location: " . $row['lon'] . "<br><br>";
    }
} else {
    echo "Query failed: " . $conn->error;
}


$conn = null;
?>

?>