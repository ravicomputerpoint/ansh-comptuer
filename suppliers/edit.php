<?php
    $page_title = "Edit supplier";
    include('../includes/header.php');
    include('../db_config.php');
    $id = $_GET['id'];
    $query = mysqli_query($conn, "select * from suppliers where id='$id'");
    $supplier = mysqli_fetch_assoc($query);
?>
<div class="text-end">
    <a href="index.php" class="btn btn-danger"><i class="bi bi-arrow-left fill me-2"></i>Back</a>
</div>
<form action="update.php" method="POST">
    <label for="name" class="form-label">Enter supplier name</label>
    <input type="text" class="form-control mb-3" id="name" name="name" value="<?php echo $supplier['name'] ?>">

    <label for="mobile_no" class="form-label">Enter mobile name</label>
    <input type="text" class="form-control mb-3" id="mobile_no" name="mobile_no" value="<?php echo $supplier['mobile_no'] ?>">

    <label for="address" class="form-label">Enter address</label>
    <input type="text" class="form-control mb-3" id="address" name="address" value="<?php echo $supplier['address'] ?>">

    <input type="hidden" name="id" value="<?php echo $supplier['id'] ?>">
    <button type="submit" class="btn btn-warning">
        <i class="bi bi-save me-2"></i>Update
    </button>
</form>
<?php include('../includes/footer.php'); ?>