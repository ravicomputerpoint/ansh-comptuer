
<?php

session_start();

$page_title = "Sales";

include('../includes/header.php');
include('../db_config.php');

// Fetch sales with customer name
$sql = "
    SELECT
        sales.id,
        sales.date,
        sales.invoice_no,
        sales.total_amount,
        customers.name AS customer_name
    FROM sales
    LEFT JOIN customers
        ON sales.customer_id = customers.id
    ORDER BY sales.id DESC
";

$sales = mysqli_query($conn, $sql);

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Sales</h4>

    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>
        Create New Sale
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
                <th>Customer</th>
                <th>Total</th>
                <th>Show</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>

        <tbody>

            <?php if (mysqli_num_rows($sales) > 0): ?>

                <?php while ($sale = mysqli_fetch_assoc($sales)): ?>

                    <tr>

                        <td>
                            <?php echo htmlspecialchars($sale['date']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($sale['invoice_no'] ?? ''); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($sale['customer_name'] ?? 'N/A'); ?>
                        </td>

                        <td class="text-end">
                            ₹<?php echo number_format((float)$sale['total_amount'], 2); ?>
                        </td>

                        <td class="text-center">
                            <a href="show.php?id=<?php echo (int)$sale['id']; ?>"
                               class="btn btn-info btn-sm">
                                <i class="bi bi-eye-fill me-1"></i>
                                Show
                            </a>
                        </td>

                        <td class="text-center">
                            <a href="edit.php?id=<?php echo (int)$sale['id']; ?>"
                               class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil-fill me-1"></i>
                                Edit
                            </a>
                        </td>

                        <td class="text-center">

                            <form action="delete.php"
                                  method="POST"
                                  onsubmit="return confirm('Do you really want to delete this sale?');">

                                <input type="hidden"
                                       name="id"
                                       value="<?php echo (int)$sale['id']; ?>">

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
                        No sales found.
                    </td>
                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</div>

<?php include('../includes/footer.php'); ?>
