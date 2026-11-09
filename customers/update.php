<?php
    session_start();
    include('../db_config.php');
    $id = $_POST['id'];
    $name = $_POST['name'];
    $mobile_no = $_POST['mobile_no'];
    $address = $_POST['address'];

    
    $query = mysqli_query($conn, "update customers set name = '$name', mobile_no = '$mobile_no', address = '$address' where id='$id'");

    if(!$query){
        header('location: ../error.php');
    }else{
        $_SESSION['message'] = "Customer updated successfully!";
        header('location: index.php');
    }
?>
