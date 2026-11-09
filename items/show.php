<?php 
    $page_title = "Show item";
    include('../includes/header.php'); 
    include('../db_config.php');
?>
<table class="table table-bordered my-3">
    <?php
        $id = $_GET['id'];
        $query = mysqli_query($conn, "select * from items where id='$id'");
        $item = mysqli_fetch_assoc($query);
    ?>
    <tr>
        <th>Name</th>
        <td><?php echo $item['name']; ?></td>
    </tr>
</table>
<a href="index.php" class="btn btn-danger">
    <i class="bi bi-arrow-left me-2"></i>Back
</a>
<?php include('../includes/footer.php'); ?>