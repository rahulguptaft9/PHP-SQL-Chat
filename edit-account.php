<?php
session_start();
require_once "pdo.php";
date_default_timezone_set('UTC');

if (!isset($_SESSION["email"])) {
    echo "PLEASE LOGIN";
    echo "<br />";
    echo "Redirecting in 3 seconds";
    header("refresh:3;url=index.php");
    die();
}


if (isset($_POST["submit"])) {
    if (!file_exists($_FILES['fileToUpload']['tmp_name']) || !is_uploaded_file($_FILES['fileToUpload']['tmp_name'])) {
        $stmta = $pdo->prepare("SELECT pfp FROM account WHERE name=?");
        $stmta->execute([$_SESSION['name']]);
        $pfptemp = $stmta->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pfptemp as $test) {
            if ($test['pfp'] != null) {
                $base64 = $test['pfp'];
            }
        }
    } else {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        $uploadOk = 1;
        $path = $_FILES["fileToUpload"]["tmp_name"];
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
    if ($check !== false) {
        $sql = "UPDATE account SET pfp = :pfp, 
        name = :newName,
        email = :email
        WHERE name = :name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            ':pfp' => $base64,
            ':name' => $_SESSION['name'],
            ':newName' => $_POST['name'],
            ':email' => $_POST['email']
        ));
        $_SESSION['success'] = 'Account details updated.';
    } else {
        $_SESSION['error'] = "File is not an image.";
        $uploadOk = 0;
    }
    header("Location: ./index.php");
}
?>

<head>
    <title>Upload Image</title>
    <link rel="stylesheet" href="./style.css">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
        }

        .btn {
            font-family: Arial, Helvetica, sans-serif;
            text-decoration: none;
            color: #ffa500;
            background-color: rgba(41, 41, 41, 1);
            padding: 8px;
            border: none;
            font-size: 14px;
            cursor: pointer;
            transition: all .15s ease-in;
        }

        .btn:hover {
            color: #fff;
        }
        .btn:active {
            background-color: transparent;
        }
    </style>
</head>

<form action="edit-account.php" method="post" enctype="multipart/form-data">
    Select image to upload for <?= $_SESSION['name'] ?>
    <input type="file" name="fileToUpload" id="fileToUpload">
    <label for=""></label>
    <p>Name:
        <input type="text" name="name" value="<?= $_SESSION['name'] ?>">
    </p>
    <p>Email:
        <input type="text" name="email" value="<?= $_SESSION['email'] ?>">
    </p>
    <br /><input type="submit" value="Submit Changes" class="btn" name="submit">
    <a href="./index.php" class="btn">Cancel</a>
</form><br />