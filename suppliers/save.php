<?php
    session_start();
    include('../db_config.php');
    $name = $_POST['name'];
    $mobile_no = $_POST['mobile_no'];
    $address = $_POST['address'];

    
    $query = mysqli_query($conn, "insert into suppliers(name, mobile_no, address) values('$name','$mobile_no','$address')");

    if(!$query){
        header('location: ../error.php');
    }else{
        $_SESSION['message'] = "Supplier created successfully!";
        header('location: index.php');
    }
?>
