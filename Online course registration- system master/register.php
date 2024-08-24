<?php
session_start();

if (isset($_POST['lastname'])) {
    $lastname = $_POST['lastname'];
    $_SESSION["sess_lastname"] = $lastname;
} else {
    // Handle the case where the 'lastname' key is not set
    echo "The 'lastname' key is not set in the \$_POST array.";
    exit;
}

$username = $_POST["username"];
$fname = $_POST["fname"];
$lname = $_POST["lastname"]; // Use the $lastname variable set above
$gender = $_POST["gender"];
$email = $_POST["email"];
$phone = $_POST["contact"];
$deptid = $_POST["department"];
$password = $_POST["password"];
$password1 = trim($password);

$hash = hash('sha256', $password1);

$mysqli = new mysqli("localhost", "username", "password", "database_name");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Use the $mysqli object to execute queries, etc.

$salt = createSalt();
$hash1 = hash('sha256', $salt . $hash);

$result = $mysqli->query("SELECT net_id FROM user_login WHERE net_id = '$username'");

if ($result->num_rows == 0) {
    $result1 = $mysqli->query("SELECT phone FROM users WHERE email = '$email'");
    if ($result1->num_rows == 0) {
        $sql = "INSERT INTO users (net_id, firstname, lastname, email, d_id, u_role, phone, gender) VALUES (?, ?, ?, ?, ?, 'student', ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sisissi", $username, $fname, $lname, $email, $deptid, $phone, $gender);
        $stmt->execute();

        $sql1 = "INSERT INTO user_login (net_id, password, salt_value) VALUES (?, ?, ?)";
        $stmt1 = $mysqli->prepare($sql1);
        $stmt1->bind_param("sis", $username, $hash1, $salt);
        $stmt1->execute();

        if ($stmt && $stmt1) {
            session_regenerate_id();
            $_SESSION['sess_username'] = $username;
            session_write_close();
            header('Location: user/welcome.html');
        } else {
            header('Location: index.html');
        }
    } else {
        header('Location: index.html');
    }
} else {
    header('Location: index.html');
}

$mysqli->close();
?>