<?php 
    session_start();
    $page_title = "Items";
    include('../includes/header.php'); 
    include('../db_config.php');
?>
<a href="create.php" class="btn btn-primary">
    <i class="bi bi-pencil-fill me-2"></i>Create new item
</a>
<?php if(!empty($_SESSION['message'])): ?> 
    <div class="alert alert-success mt-3">
        <?php echo $_SESSION['message']; unset($_SESSION['message']) ?>
    </div>
<?php endif; ?>
<table class="table table-bordered my-3" id="table">
    <thead>
        <tr class="text-center">
            <th>Name</th>
            <th>Show</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $items = mysqli_query($conn, "select * from items");
        ?>
        <?php while($item = mysqli_fetch_assoc($items)): ?>
            <tr>
                <td><?php echo $item['name'] ?></td>
                <td class="text-center">
                    <a href="show.php?id=<?php echo $item['id'] ?>" class="btn btn-info btn-sm">
                        <i class="bi bi-eye-fill me-2"></i>Show
                    </a>
                </td>
                <td class="text-center">
                    <a href="edit.php?id=<?php echo $item['id'] ?>" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil-fill me-2"></i>Edit
                    </a>
                </td>
                <td class="text-center">
                    <form action="delete.php" method="POST" onsubmit="return confirm('Do you really want to delete?');">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($item['id']); ?>">
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash-fill me-2"></i>Delete
                        </button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        
    </tbody>
</table>
<?php include('../includes/footer.php'); ?>