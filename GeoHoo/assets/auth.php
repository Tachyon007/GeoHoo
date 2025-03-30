<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


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

// Access environment variables
$servernameDB = getenv('DB_SERVER');
$usernameDB = getenv('DB_USERNAME');
$passwordDB = getenv('DB_PASSWORD');
$dbnameDB = getenv('DB_NAME');






    if (!isset($_POST['action']) || !isset($_POST['username']) || !isset($_POST['password'])) {
        echo "Invalid form";
        exit();
    }

    $action = trim($_POST['action']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $image_url = "https://orellion.com/GeoHoo/assets/images/default.png";

    // Create connection
    $conn = new mysqli($servernameDB, $usernameDB, $passwordDB, $dbnameDB);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($action === 'signup') {
        // Check if username already exists
        $sql = "SELECT id FROM Users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "Username already taken";
            exit();
        }

        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $sql = "INSERT INTO Users (username, password_hash, image_url) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $password_hash, $image_url);
        $stmt->execute();

        if ($stmt->affected_rows === 1) {
            echo "Signup successful";
        } else {
            echo "Error during signup";
        }

    } elseif ($action === 'login') {
        // Check login credentials
        $sql = "SELECT id, password_hash FROM Users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($user_id, $stored_hash);
        $stmt->fetch();

        if ($user_id && password_verify($password, $stored_hash)) {
            echo "Login successful";
        } else {
            echo "Invalid username or password";
        }
    } else {
        echo "Invalid action";
    }

    $stmt->close();
    $conn->close();
?>
