<?php
    session_start();
    include('../db_config.php');
    $id = $_POST['id'];
    $name = $_POST['name'];
      
    $query = mysqli_query($conn, "update items set name = '$name' where id='$id'");

    if(!$query){
        header('location: ../error.php');
    }else{
        $_SESSION['message'] = "Item updated successfully!";
        header('location: index.php');
    }
?>
