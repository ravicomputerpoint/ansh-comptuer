<?php
    session_start();
    include('../db_config.php');
    $id = $_POST['id'];
    $query = mysqli_query($conn, "delete from suppliers where id='$id'");
    
    if(!$query){
        header('location: ../error.php');
    }else{
        $_SESSION['message'] = "Supplier deleted successfully!";
        header('location: index.php');
    }
?>