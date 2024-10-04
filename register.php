<?php
// Database connection
$servername = "localhost"; // your server name
$username = "root"; // your database username
$password = ""; // your database password
$dbname = "business"; // your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required fields are set
    if (isset($_POST['name'], $_POST['email'], $_POST['phone_number'], $_POST['password'], $_POST['location'], $_POST['bio'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone_number = $_POST['phone_number'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
        $location = $_POST['location'];
        $bio = $_POST['bio'];

        // Handle file upload
        $profile_picture = '';
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/'; // Directory to store uploaded pictures
            $file_name = basename($_FILES['profile_picture']['name']);
            $upload_file = $upload_dir . $file_name;

            // Move the uploaded file to the designated directory
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_file)) {
                $profile_picture = $upload_file; // Save the path to the database
            } else {
                echo "Error uploading the profile picture.";
            }
        }

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO Users (name, email, phone_number, password, location, bio, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $email, $phone_number, $password, $location, $bio, $profile_picture);

        if ($stmt->execute()) {
            echo "Registration successful!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Please fill in all required fields.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .registration-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        h2 {
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="email"], input[type="password"], textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="file"] {
            width: 100%;
            margin-top: 10px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #5cb85c;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-size: 16px;
        }
        button:hover {
            background-color: #4cae4c;
        }
    </style>
</head>
<body>

<div class="registration-form">
    <h2>User Registration</h2>
    <form method="post" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="phone_number">Phone Number</label>
            <input type="text" name="phone_number" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" name="location" required>
        </div>
        <div class="form-group">
            <label for="bio">Bio</label>
            <textarea name="bio" rows="4" required></textarea>
        </div>
        <div class="form-group">
            <label for="profile_picture">Profile Picture</label>
            <input type="file" name="profile_picture" accept="image/*" required>
        </div>
        <button type="submit">Register</button>
    </form>
</div>

</body>
</html>
