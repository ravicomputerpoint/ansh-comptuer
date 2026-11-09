<?php 
    $page_title = "Show Customer";
    include('../includes/header.php'); 
    include('../db_config.php');
?>
<table class="table table-bordered my-3">
    <?php
        $id = $_GET['id'];
        $query = mysqli_query($conn, "select * from customers where id='$id'");
        $customer = mysqli_fetch_assoc($query);
    ?>
    <tr>
        <th>Name</th>
        <td><?php echo $customer['name']; ?></td>
    </tr>
    <tr>
        <th>Mobile No.</th>
        <td><?php echo $customer['mobile_no']; ?></td>
    </tr>
    <tr>
        <th>Address</th>
        <td><?php echo $customer['address']; ?></td>
    </tr>
</table>
<a href="index.php" class="btn btn-danger">
    <i class="bi bi-arrow-left me-2"></i>Back
</a>
<?php include('../includes/footer.php'); ?>