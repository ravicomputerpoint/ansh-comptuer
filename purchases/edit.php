
<?php

session_start();

require_once('../db_config.php');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    die("Invalid purchase ID.");
}

$error = "";

// =====================================
// UPDATE PURCHASE
// =====================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $date = trim($_POST['date'] ?? '');
        $invoice_no = trim($_POST['invoice_no'] ?? '');
        $supplier_id = (int)($_POST['supplier_id'] ?? 0);

        $items = $_POST['items'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $rates = $_POST['rates'] ?? [];

        // Validate date
        $dateObject = DateTime::createFromFormat('!Y-m-d', $date);

        if (!$dateObject || $dateObject->format('Y-m-d') !== $date) {
            throw new Exception("Please select a valid date.");
        }

        if ($supplier_id <= 0) {
            throw new Exception("Please select a supplier.");
        }

        if (
            !is_array($items) ||
            !is_array($quantities) ||
            !is_array($rates) ||
            count($items) === 0 ||
            count($items) !== count($quantities) ||
            count($items) !== count($rates)
        ) {
            throw new Exception("Please add at least one valid item.");
        }

        // Prepare purchase items
        $purchaseItems = [];
        $grandTotal = 0;

        foreach ($items as $key => $itemId) {

            $itemId = (int)$itemId;

            $quantity = filter_var(
                $quantities[$key] ?? null,
                FILTER_VALIDATE_FLOAT
            );

            $rate = filter_var(
                $rates[$key] ?? null,
                FILTER_VALIDATE_FLOAT
            );

            if ($itemId <= 0) {
                throw new Exception("Please select an item.");
            }

            if (
                $quantity === false ||
                !is_finite($quantity) ||
                $quantity <= 0
            ) {
                throw new Exception("Quantity must be greater than zero.");
            }

            if (
                $rate === false ||
                !is_finite($rate) ||
                $rate < 0
            ) {
                throw new Exception("Rate cannot be negative.");
            }

            $total = round($quantity * $rate, 2);

            $grandTotal += $total;

            $purchaseItems[] = [
                'item_id' => $itemId,
                'quantity' => $quantity,
                'rate' => $rate,
                'total' => $total
            ];
        }

        $grandTotal = round($grandTotal, 2);

        // Start transaction
        $conn->begin_transaction();

        // Check purchase exists
        $checkPurchase = $conn->prepare("
            SELECT id FROM purchases WHERE id = ?
        ");

        $checkPurchase->bind_param("i", $id);
        $checkPurchase->execute();

        if ($checkPurchase->get_result()->num_rows === 0) {
            throw new Exception("Purchase not found.");
        }

        $checkPurchase->close();

        // Check supplier exists
        $checkSupplier = $conn->prepare("
            SELECT id FROM suppliers WHERE id = ?
        ");

        $checkSupplier->bind_param("i", $supplier_id);
        $checkSupplier->execute();

        if ($checkSupplier->get_result()->num_rows === 0) {
            throw new Exception("Selected supplier does not exist.");
        }

        $checkSupplier->close();

        // Check all selected items exist
        $checkItem = $conn->prepare("
            SELECT id FROM items WHERE id = ?
        ");

        foreach ($purchaseItems as $purchaseItem) {

            $itemId = $purchaseItem['item_id'];

            $checkItem->bind_param("i", $itemId);
            $checkItem->execute();

            if ($checkItem->get_result()->num_rows === 0) {
                throw new Exception("Selected item does not exist.");
            }
        }

        $checkItem->close();

        // Update purchase master
        $updatePurchase = $conn->prepare("
            UPDATE purchases
            SET date = ?,
                invoice_no = ?,
                supplier_id = ?,
                total_amount = ?
            WHERE id = ?
        ");

        $updatePurchase->bind_param(
            "ssidi",
            $date,
            $invoice_no,
            $supplier_id,
            $grandTotal,
            $id
        );

        $updatePurchase->execute();
        $updatePurchase->close();

        // Delete old purchase items
        $deleteItems = $conn->prepare("
            DELETE FROM purchase_items
            WHERE purchase_id = ?
        ");

        $deleteItems->bind_param("i", $id);
        $deleteItems->execute();
        $deleteItems->close();

        // Insert updated purchase items
        $insertItem = $conn->prepare("
            INSERT INTO purchase_items
            (purchase_id, item_id, quantity, rate, total)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($purchaseItems as $purchaseItem) {

            $itemId = $purchaseItem['item_id'];
            $quantity = $purchaseItem['quantity'];
            $rate = $purchaseItem['rate'];
            $total = $purchaseItem['total'];

            $insertItem->bind_param(
                "iiddd",
                $id,
                $itemId,
                $quantity,
                $rate,
                $total
            );

            $insertItem->execute();
        }

        $insertItem->close();

        // Commit transaction
        $conn->commit();

        $_SESSION['message'] = "Purchase updated successfully.";

        header("Location: index.php");
        exit;

    } catch (Throwable $e) {

        try {
            $conn->rollback();
        } catch (Throwable $rollbackError) {
            // Ignore rollback errors
        }

        $error = $e->getMessage();
    }
}

// =====================================
// FETCH PURCHASE DETAILS
// =====================================

$stmt = $conn->prepare("
    SELECT * FROM purchases WHERE id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$purchase = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$purchase) {
    die("Purchase not found.");
}

// Fetch suppliers
$suppliers = mysqli_query(
    $conn,
    "SELECT id, name FROM suppliers ORDER BY name"
);

// Fetch items
$itemsResult = mysqli_query(
    $conn,
    "SELECT id, name FROM items ORDER BY name"
);

$allItems = [];

while ($row = mysqli_fetch_assoc($itemsResult)) {
    $allItems[] = $row;
}

// Fetch existing purchase items
$itemStmt = $conn->prepare("
    SELECT
        purchase_items.item_id,
        purchase_items.quantity,
        purchase_items.rate,
        items.name AS item_name
    FROM purchase_items
    LEFT JOIN items
        ON purchase_items.item_id = items.id
    WHERE purchase_items.purchase_id = ?
    ORDER BY purchase_items.id ASC
");

$itemStmt->bind_param("i", $id);
$itemStmt->execute();

$existingItems = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$itemStmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Keep submitted values if validation fails
    $formItems = [];

    foreach ($items as $key => $itemId) {

        $selectedItem = null;

        foreach ($allItems as $availableItem) {
            if ((int)$availableItem['id'] === (int)$itemId) {
                $selectedItem = $availableItem;
                break;
            }
        }

        $formItems[] = [
            'item_id' => (int)$itemId,
            'quantity' => $quantities[$key] ?? '',
            'rate' => $rates[$key] ?? '',
            'item_name' => $selectedItem['name'] ?? ''
        ];
    }

} else {

    $date = $purchase['date'];
    $invoice_no = $purchase['invoice_no'];
    $supplier_id = $purchase['supplier_id'];

    $formItems = $existingItems;
}

if (empty($formItems)) {
    $formItems[] = [
        'item_id' => '',
        'quantity' => '',
        'rate' => '',
        'item_name' => ''
    ];
}

$page_title = "Edit Purchase";

include('../includes/header.php');

?>

<div class="d-flex justify-content-between align-items-center mb-3">

    <h4 class="mb-0">Edit Purchase</h4>

    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i>
        Back
    </a>

</div>

<?php if ($error): ?>

    <div class="alert alert-danger">
        <?php echo htmlspecialchars($error); ?>
    </div>

<?php endif; ?>

<form method="POST" action="edit.php?id=<?php echo $id; ?>">

    <input type="hidden" name="id" value="<?php echo $id; ?>">

    <!-- Purchase Information -->

    <div class="card mb-4">

        <div class="card-header">
            <strong>Purchase Information</strong>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 mb-3">

                    <label class="form-label">Purchase Date</label>

                    <input
                        type="date"
                        name="date"
                        class="form-control"
                        value="<?php echo htmlspecialchars($date); ?>"
                        required
                    >

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">Invoice No.</label>

                    <input
                        type="text"
                        name="invoice_no"
                        class="form-control"
                        value="<?php echo htmlspecialchars($invoice_no ?? ''); ?>"
                        placeholder="Enter invoice number"
                    >

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">Supplier</label>

                    <select name="supplier_id" class="form-select" required>

                        <option value="">Select Supplier</option>

                        <?php while ($supplier = mysqli_fetch_assoc($suppliers)): ?>

                            <option
                                value="<?php echo $supplier['id']; ?>"
                                <?php echo ((int)$supplier['id'] === (int)$supplier_id) ? 'selected' : ''; ?>
                            >
                                <?php echo htmlspecialchars($supplier['name']); ?>
                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

            </div>

        </div>

    </div>

    <!-- Purchase Items -->

    <div class="card mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">

            <strong>Purchase Items</strong>

            <button type="button" class="btn btn-primary btn-sm" id="addItem">
                <i class="bi bi-plus-lg me-1"></i>
                Add Item
            </button>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered align-middle">

                    <thead>
                        <tr class="text-center">
                            <th style="min-width:220px;">Item</th>
                            <th style="min-width:110px;">Quantity</th>
                            <th style="min-width:130px;">Rate</th>
                            <th style="min-width:130px;">Total</th>
                            <th style="width:70px;">Action</th>
                        </tr>
                    </thead>

                    <tbody id="purchaseItems">

                        <?php foreach ($formItems as $formItem): ?>

                            <tr class="purchase-row">

                                <td>

                                    <select name="items[]" class="form-select item-select" required>

                                        <option value="">Select Item</option>

                                        <?php foreach ($allItems as $availableItem): ?>

                                            <option
                                                value="<?php echo $availableItem['id']; ?>"
                                                <?php echo ((int)$availableItem['id'] === (int)$formItem['item_id']) ? 'selected' : ''; ?>
                                            >
                                                <?php echo htmlspecialchars($availableItem['name']); ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </td>

                                <td>
                                    <input
                                        type="number"
                                        name="quantities[]"
                                        class="form-control quantity"
                                        min="0.001"
                                        step="0.001"
                                        value="<?php echo htmlspecialchars((string)$formItem['quantity']); ?>"
                                        required
                                    >
                                </td>

                                <td>
                                    <input
                                        type="number"
                                        name="rates[]"
                                        class="form-control rate"
                                        min="0"
                                        step="0.01"
                                        value="<?php echo htmlspecialchars((string)$formItem['rate']); ?>"
                                        required
                                    >
                                </td>

                                <td>
                                    <input
                                        type="text"
                                        class="form-control row-total text-end"
                                        value="0.00"
                                        readonly
                                    >
                                </td>

                                <td class="text-center">

                                    <button
                                        type="button"
                                        class="btn btn-danger btn-sm remove-item"
                                        title="Remove item"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                    <tfoot>

                        <tr>
                            <th colspan="2">
                                Total Quantity:
                                <span id="totalQuantity">0</span>
                            </th>

                            <th colspan="2" class="text-end">
                                Grand Total:
                                <span id="grandTotal">₹0.00</span>
                            </th>

                            <th></th>
                        </tr>

                    </tfoot>

                </table>

            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle me-1"></i>
                    Update Purchase
                </button>
            </div>

        </div>

    </div>

</form>

<!-- New Item Template -->

<template id="itemTemplate">

    <tr class="purchase-row">

        <td>

            <select name="items[]" class="form-select item-select" required>

                <option value="">Select Item</option>

                <?php foreach ($allItems as $availableItem): ?>

                    <option value="<?php echo $availableItem['id']; ?>">
                        <?php echo htmlspecialchars($availableItem['name']); ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </td>

        <td>
            <input
                type="number"
                name="quantities[]"
                class="form-control quantity"
                min="0.001"
                step="0.001"
                placeholder="Quantity"
                required
            >
        </td>

        <td>
            <input
                type="number"
                name="rates[]"
                class="form-control rate"
                min="0"
                step="0.01"
                placeholder="Rate"
                required
            >
        </td>

        <td>
            <input
                type="text"
                class="form-control row-total text-end"
                value="0.00"
                readonly
            >
        </td>

        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm remove-item">
                <i class="bi bi-trash"></i>
            </button>
        </td>

    </tr>

</template>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const purchaseItems = document.getElementById('purchaseItems');
    const template = document.getElementById('itemTemplate');
    const addItemButton = document.getElementById('addItem');

    function calculateTotal() {

        let grandTotal = 0;
        let totalQuantity = 0;

        document.querySelectorAll('.purchase-row').forEach(function (row) {

            const quantity = parseFloat(
                row.querySelector('.quantity').value
            ) || 0;

            const rate = parseFloat(
                row.querySelector('.rate').value
            ) || 0;

            const total = quantity * rate;

            row.querySelector('.row-total').value = total.toFixed(2);

            grandTotal += total;
            totalQuantity += quantity;

        });

        document.getElementById('grandTotal').textContent =
            '₹' + grandTotal.toFixed(2);

        document.getElementById('totalQuantity').textContent =
            totalQuantity.toFixed(3);
    }

    // Add new item row
    addItemButton.addEventListener('click', function () {

        const newRow = template.content.cloneNode(true);

        purchaseItems.appendChild(newRow);

        calculateTotal();

    });

    // Calculate on input
    purchaseItems.addEventListener('input', function (e) {

        if (
            e.target.classList.contains('quantity') ||
            e.target.classList.contains('rate')
        ) {
            calculateTotal();
        }

    });

    // Remove item row
    purchaseItems.addEventListener('click', function (e) {

        const removeButton = e.target.closest('.remove-item');

        if (!removeButton) {
            return;
        }

        const rows = purchaseItems.querySelectorAll('.purchase-row');

        if (rows.length <= 1) {
            alert('At least one item is required.');
            return;
        }

        removeButton.closest('.purchase-row').remove();

        calculateTotal();

    });

    // Initial calculation
    calculateTotal();

});

</script>

<?php include('../includes/footer.php'); ?>
