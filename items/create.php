<?php
    $page_title = "Create new item";
    include('../includes/header.php');

?>
<div class="text-end">
    <a href="index.php" class="btn btn-danger"><i class="bi bi-arrow-left fill me-2"></i>Back</a>
</div>
<form action="save.php" method="POST">
    <label for="name" class="form-label">Enter item name</label>
    <input type="text" class="form-control mb-3" id="name" name="name" autofocus>
    
    <button type="submit" class="btn btn-success">
        <i class="bi bi-save me-2"></i>Save
    </button>
</form>
<?php include('../includes/footer.php'); ?>