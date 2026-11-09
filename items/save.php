<?php
    session_start();
    include('../db_config.php');
    $name = $_POST['name'];

    $query = mysqli_query($conn, "insert into items(name) values('$name')");

    if(!$query){
        header('location: ../error.php');
    }else{
        $_SESSION['message'] = "Item created successfully!";
        header('location: index.php');
    }
?>
