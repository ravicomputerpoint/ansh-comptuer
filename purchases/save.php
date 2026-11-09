
<?php

require_once('../db_config.php');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Only POST request allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create.php');
    exit;
}

try {

    // Get form data
    $date = trim($_POST['date'] ?? '');
    $invoice_no = trim($_POST['invoice_no'] ?? '');
    $supplier_id = (int)($_POST['supplier_id'] ?? 0);

    $items = $_POST['items'] ?? [];
    $quantities = $_POST['quantities'] ?? [];
    $rates = $_POST['rates'] ?? [];

    // Basic validation
    $dateObject = DateTime::createFromFormat('!Y-m-d', $date);

    if (!$dateObject || $dateObject->format('Y-m-d') !== $date) {
        throw new Exception("Please select a valid purchase date.");
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
        throw new Exception("Invalid purchase items.");
    }

    // Prepare purchase items and calculate total
    $purchaseItems = [];
    $grandTotal = 0;

    foreach ($items as $key => $itemId) {

        $itemId = (int)$itemId;
        $quantity = filter_var($quantities[$key], FILTER_VALIDATE_FLOAT);
        $rate = filter_var($rates[$key], FILTER_VALIDATE_FLOAT);

        if ($itemId <= 0) {
            throw new Exception("Please select a valid item.");
        }

        if ($quantity === false || !is_finite($quantity) || $quantity <= 0) {
            throw new Exception("Quantity must be greater than zero.");
        }

        if ($rate === false || !is_finite($rate) || $rate < 0) {
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

    // Start database transaction
    $conn->begin_transaction();

    // Check supplier
    $checkSupplier = $conn->prepare("
        SELECT id FROM suppliers WHERE id = ?
    ");

    $checkSupplier->bind_param("i", $supplier_id);
    $checkSupplier->execute();
    $supplierResult = $checkSupplier->get_result();

    if ($supplierResult->num_rows === 0) {
        throw new Exception("Selected supplier does not exist.");
    }

    $checkSupplier->close();

    // Insert purchase
    $purchaseQuery = $conn->prepare("
        INSERT INTO purchases
        (date, invoice_no, supplier_id, total_amount)
        VALUES (?, ?, ?, ?)
    ");

    $purchaseQuery->bind_param(
        "ssid",
        $date,
        $invoice_no,
        $supplier_id,
        $grandTotal
    );

    $purchaseQuery->execute();

    $purchaseId = $conn->insert_id;

    $purchaseQuery->close();

    // Prepare item validation query
    $checkItem = $conn->prepare("
        SELECT id FROM items WHERE id = ?
    ");

    // Prepare purchase item insert query
    $itemQuery = $conn->prepare("
        INSERT INTO purchase_items
        (purchase_id, item_id, quantity, rate, total)
        VALUES (?, ?, ?, ?, ?)
    ");

    // Save purchase items
    foreach ($purchaseItems as $purchaseItem) {

        $itemId = $purchaseItem['item_id'];
        $quantity = $purchaseItem['quantity'];
        $rate = $purchaseItem['rate'];
        $total = $purchaseItem['total'];

        // Check item exists
        $checkItem->bind_param("i", $itemId);
        $checkItem->execute();

        if ($checkItem->get_result()->num_rows === 0) {
            throw new Exception("Selected item does not exist.");
        }

        // Insert purchase item
        $itemQuery->bind_param(
            "iiddd",
            $purchaseId,
            $itemId,
            $quantity,
            $rate,
            $total
        );

        $itemQuery->execute();
    }

    $checkItem->close();
    $itemQuery->close();

    // Commit transaction
    $conn->commit();

    header("Location: index.php?success=1");
    exit;

} catch (Throwable $e) {

    // Rollback if transaction fails
    try {
        $conn->rollback();
    } catch (Throwable $rollbackError) {
        // Ignore rollback errors
    }

    echo "<h3>Purchase Error:</h3>";
    echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    echo '<br><br>';
    echo '<a href="create.php">Go Back</a>';
}
