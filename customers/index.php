<?php 
    session_start();
    $page_title = "Customers";
    include('../includes/header.php'); 
    include('../db_config.php');
    $customers = mysqli_query($conn, "select * from customers");
?>
<a href="create.php" class="btn btn-primary">
    <i class="bi bi-pencil-fill me-2"></i>Create new customer
</a>
<?php if(!empty($_SESSION['message'])): ?> 
    <div class="alert alert-success mt-3">
        <?php echo $_SESSION['message']; unset($_SESSION['message']) ?>
    </div>
<?php endif; ?>
<table class="table table-bordered my-3" id="table">
    <thead>
        <tr class="text-center">
            <th>Date</th>
            <th>Name</th>
            <th>Mobile No.</th>
            <th>Address</th>
            <th>Show</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>

        <?php while($customer = mysqli_fetch_assoc($customers)): ?>
            <tr>
                <td><?php echo $customer['name'] ?></td>
                <td><?php echo $customer['mobile_no'] ?></td>
                <td><?php echo $customer['address'] ?></td>
                <td class="text-center">
                    <a href="show.php?id=<?php echo $customer['id'] ?>" class="btn btn-info btn-sm">
                        <i class="bi bi-eye-fill me-2"></i>Show
                    </a>
                </td>
                <td class="text-center">
                    <a href="edit.php?id=<?php echo $customer['id'] ?>" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil-fill me-2"></i>Edit
                    </a>
                </td>
                <td class="text-center">
                    <form action="delete.php" method="POST" onsubmit="return confirm('Do you really want to delete?');">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($customer['id']); ?>">
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