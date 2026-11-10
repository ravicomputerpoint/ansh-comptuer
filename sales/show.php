
<?php

session_start();

$page_title = "Sales Details";

include('../includes/header.php');
include('../db_config.php');

// Get sales ID
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    echo '<div class="alert alert-danger">Invalid sales ID.</div>';
    include('../includes/footer.php');
    exit;
}

// Fetch sales and customer details
$sql = "
    SELECT
        sales.*,
        customers.name AS customer_name
    FROM sales
    LEFT JOIN customers
        ON sales.customer_id = customers.id
    WHERE sales.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$sale = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$sale) {
    echo '<div class="alert alert-danger">Sales record not found.</div>';
    include('../includes/footer.php');
    exit;
}

// Fetch sales items
$itemSql = "
    SELECT
        sales_items.*,
        items.name AS item_name
    FROM sales_items
    LEFT JOIN items
        ON sales_items.item_id = items.id
    WHERE sales_items.sales_id = ?
    ORDER BY sales_items.id ASC
";

$itemStmt = $conn->prepare($itemSql);
$itemStmt->bind_param("i", $id);
$itemStmt->execute();

$salesItems = $itemStmt->get_result();

?>

<div class="d-flex justify-content-between align-items-center mb-3">

    <h4 class="mb-0">Sales Details</h4>

    <div>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back
        </a>

        <button onclick="window.print()" class="btn btn-dark">
            <i class="bi bi-printer me-1"></i>
            Print
        </button>
    </div>

</div>

<!-- Sales Information -->
<div class="card mb-4">

    <div class="card-header">
        <strong>Sales Information</strong>
    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-4 mb-3">
                <small class="text-muted">Invoice No.</small>
                <div>
                    <strong>
                        <?php echo htmlspecialchars($sale['invoice_no'] ?? ''); ?>
                    </strong>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <small class="text-muted">Sales Date</small>
                <div>
                    <strong>
                        <?php echo htmlspecialchars($sale['date'] ?? ''); ?>
                    </strong>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <small class="text-muted">Customer</small>
                <div>
                    <strong>
                        <?php echo htmlspecialchars($sale['customer_name'] ?? 'N/A'); ?>
                    </strong>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Sales Items -->
<div class="card">

    <div class="card-header">
        <strong>Sales Items</strong>
    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-bordered table-hover mb-0">

                <thead>
                    <tr class="text-center">
                        <th width="60">#</th>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Rate</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>

                    <?php
                    $i = 1;
                    $calculatedTotal = 0;
                    ?>

                    <?php if ($salesItems->num_rows > 0): ?>

                        <?php while ($item = $salesItems->fetch_assoc()): ?>

                            <?php
                            $calculatedTotal += (float)$item['total'];
                            ?>

                            <tr>

                                <td class="text-center">
                                    <?php echo $i++; ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($item['item_name'] ?? 'Item not found'); ?>
                                </td>

                                <td class="text-end">
                                    <?php echo number_format((float)$item['quantity'], 3); ?>
                                </td>

                                <td class="text-end">
                                    ₹<?php echo number_format((float)$item['rate'], 2); ?>
                                </td>

                                <td class="text-end">
                                    ₹<?php echo number_format((float)$item['total'], 2); ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">
                                No sales items found.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

                <tfoot>

                    <tr>
                        <th colspan="4" class="text-end">
                            Grand Total
                        </th>

                        <th class="text-end">
                            ₹<?php echo number_format((float)$sale['total_amount'], 2); ?>
                        </th>
                    </tr>

                </tfoot>

            </table>

        </div>

    </div>

</div>

<?php
$itemStmt->close();
include('../includes/footer.php');
?>

<style>
@media print {

    body {
        background: white !important;
    }

    .btn,
    nav,
    .sidebar,
    footer {
        display: none !important;
    }

    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }

    .card-header {
        background: #eee !important;
        color: black !important;
    }
}
</style>
