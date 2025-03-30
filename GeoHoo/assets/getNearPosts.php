<?php

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

if(!isset($_POST['lon']) || !isset($_POST['lat']) ){
    error("invalid form");
}



// Load the environment variables from .env file
loadEnv('../hoo.env');
$servername = getenv('DB_SERVER');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');

$radius = 2000; // Radius in meters
$user_lon = $_POST['lon'];
$user_lat = $_POST['lat'];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT *
        FROM Posts
        WHERE ST_Distance_Sphere(Point(lon, lat), Point($user_lon, $user_lat)) <= $radius";
        
$result = $conn->query($sql);

if ($result) {
    $rowCount = 0;
    $totalRows = $result->num_rows;
    
    echo "[";
    while ($row = $result->fetch_assoc()) {
        echo "{ \"author_ID\": " . $row['author_id'] . ", ";
        echo "\"text\": \"" . $row['text'] . "\", ";
        echo "\"image_URL\": \"" . $row['image_url'] . "\", ";
        echo "\"timestamp\": \"" . $row['timestamp'] . "\", ";
        echo "\"visible\": " . $row['visible'] . ", ";
        echo "\"location\": [" .$row['lon']. ", " .$row['lat']. "] }";
        
        if(++$rowCount < $totalRows){
            echo ",";
        }
    }
    echo "]";

} else {
    echo "Query failed: " . $conn->error;
}


$conn = null;

?>