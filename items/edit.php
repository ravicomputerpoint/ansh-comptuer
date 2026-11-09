<?php
    $page_title = "Edit item";
    include('../includes/header.php');
    include('../db_config.php');
    $id = $_GET['id'];
    $query = mysqli_query($conn, "select * from items where id='$id'");
    $item = mysqli_fetch_assoc($query);
?>
<div class="text-end">
    <a href="index.php" class="btn btn-danger"><i class="bi bi-arrow-left fill me-2"></i>Back</a>
</div>
<form action="update.php" method="POST">
    <label for="name" class="form-label">Enter item name</label>
    <input type="text" class="form-control mb-3" id="name" name="name" value="<?php echo $item['name'] ?>">

    <input type="hidden" name="id" value="<?php echo $item['id'] ?>">
    <button type="submit" class="btn btn-warning">
        <i class="bi bi-save me-2"></i>Update
    </button>
</form>
<?php include('../includes/footer.php'); ?>