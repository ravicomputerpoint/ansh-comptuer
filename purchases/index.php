
<?php

session_start();

$page_title = "Purchases";

include('../includes/header.php');
include('../db_config.php');

// Fetch purchases with supplier name
$sql = "
    SELECT
        purchases.id,
        purchases.date,
        purchases.invoice_no,
        purchases.total_amount,
        suppliers.name AS supplier_name
    FROM purchases
    LEFT JOIN suppliers
        ON purchases.supplier_id = suppliers.id
    ORDER BY purchases.id DESC
";

$purchases = mysqli_query($conn, $sql);

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Purchases</h4>

    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>
        Create New Purchase
    </a>
</div>

<?php if (!empty($_SESSION['message'])): ?>

    <div class="alert alert-success mt-3">
        <?php
            echo htmlspecialchars($_SESSION['message']);
            unset($_SESSION['message']);
        ?>
    </div>

<?php endif; ?>

<div class="table-responsive">

    <table class="table table-bordered table-hover align-middle my-3" id="table">

        <thead>
            <tr class="text-center">
                <th>Date</th>
                <th>Invoice No.</th>
                <th>Supplier</th>
                <th>Total</th>
                <th>Show</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>

        <tbody>

            <?php if (mysqli_num_rows($purchases) > 0): ?>

                <?php while ($purchase = mysqli_fetch_assoc($purchases)): ?>

                    <tr>

                        <td>
                            <?php echo htmlspecialchars($purchase['date']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($purchase['invoice_no'] ?? ''); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($purchase['supplier_name'] ?? 'N/A'); ?>
                        </td>

                        <td class="text-end">
                            ₹<?php echo number_format((float)$purchase['total_amount'], 2); ?>
                        </td>

                        <td class="text-center">
                            <a href="show.php?id=<?php echo (int)$purchase['id']; ?>"
                               class="btn btn-info btn-sm">
                                <i class="bi bi-eye-fill me-1"></i>
                                Show
                            </a>
                        </td>

                        <td class="text-center">
                            <a href="edit.php?id=<?php echo (int)$purchase['id']; ?>"
                               class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil-fill me-1"></i>
                                Edit
                            </a>
                        </td>

                        <td class="text-center">

                            <form action="delete.php"
                                  method="POST"
                                  onsubmit="return confirm('Do you really want to delete this purchase?');">

                                <input type="hidden"
                                       name="id"
                                       value="<?php echo (int)$purchase['id']; ?>">

                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash-fill me-1"></i>
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        No purchases found.
                    </td>
                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</div>

<?php include('../includes/footer.php'); ?>
