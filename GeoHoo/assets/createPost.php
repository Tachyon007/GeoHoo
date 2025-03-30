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
loadEnv('../hoo.env');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['files']) && isset($_POST['about']) && isset($_POST['lon']) && isset($_POST['lat']) ){
        
        
        //post data
        //$project_profile = json_decode($_POST['post_profile']);
        //$visible = 1;
        $about =  $_POST['about'];
        $email = "kev.jelinek@gmail.com";
        //$author_id = $project_profile->author_id;

        //user data
        /*$json = file_get_contents('../user_data/profiles.json'); 
        $json_data = json_decode($json); 
        if(!property_exists($json_data, $email)){exit;}

        $projects = $json_data -> {$email} -> projects;*/

        
        $errors = [];
        $path = './images/';
        $extensions = ['jpg', 'jpeg', 'png', 'gif'];

        $all_files = count($_FILES['files']['tmp_name']);
        $file = '';

        for ($i = 0; $i < 1; $i++) {
            $file_name = $_FILES['files']['name'][$i];
            $file_tmp = $_FILES['files']['tmp_name'][$i]; 
            $file_type = $_FILES['files']['type'][$i];
            $file_size = $_FILES['files']['size'][$i];
            $file_ext = strtolower(end(explode('.', $_FILES['files']['name'][$i])));

            $file = $path . $email . "_" . $i . rand() . "." . $file_ext;

            if (!in_array($file_ext, $extensions)) {
                $errors[] = 'Extension not allowed: ' . $file_name . ' ' . $file_type;
            }

            if ($file_size > 5000000) {
                $errors[] = 'File size exceeds limit: ' . $file_name . ' ' . $file_type;
            }

            if (empty($errors)) {
                move_uploaded_file($file_tmp, $file);
                
            }
        }
        
        if (empty($errors)) {
            //images done:
         
            $servername = getenv('DB_SERVER');
            $username = getenv('DB_USERNAME');
            $password = getenv('DB_PASSWORD');
            $dbname = getenv('DB_NAME');
            
            $user_lon = $_POST['lon'];
            $user_lat = $_POST['lat'];
            
            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
         
          
            //project
            $sql = "INSERT INTO Posts (author_id, text, image_url, timestamp, visible, lon, lat) 
                        VALUES (1, '".$about."', '".$file."', NOW(), 1, $user_lon, $user_lat);
                        ";
            $result = $conn->query($sql);
            $affected = $conn -> affected_rows;
            if($affected > 0){
                echo 'good';
            }else{
                echo 'Did not insert';
                http_response_code(504);
            }
            
            /*array_push($projects, $project_profile);
            $json_data -> {$email} -> projects = $projects;

            
            file_put_contents(
                '../user_data/profiles.json', 
                json_encode($json_data)
            );*/
        }else{
            echo "501";
            http_response_code(501);
        }

        //if ($errors) print_r($errors);
    }else{
        
        //Give specific code for Size error
        
        http_response_code(500);
        throw new Exception("files not set in create.php POST");
    }
}