
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
    $customer_id = (int)($_POST['customer_id'] ?? 0);

    $items = $_POST['items'] ?? [];
    $quantities = $_POST['quantities'] ?? [];
    $rates = $_POST['rates'] ?? [];

    // Basic validation
    $dateObject = DateTime::createFromFormat('!Y-m-d', $date);

    if (!$dateObject || $dateObject->format('Y-m-d') !== $date) {
        throw new Exception("Please select a valid sales date.");
    }

    if ($customer_id <= 0) {
        throw new Exception("Please select a customer.");
    }

    if (
        !is_array($items) ||
        !is_array($quantities) ||
        !is_array($rates) ||
        count($items) === 0 ||
        count($items) !== count($quantities) ||
        count($items) !== count($rates)
    ) {
        throw new Exception("Invalid sales items.");
    }

    // Prepare sales items and calculate total
    $salesItems = [];
    $grandTotal = 0;

    foreach ($items as $key => $itemId) {

        if (!array_key_exists($key, $quantities) ||
            !array_key_exists($key, $rates)) {
            throw new Exception("Invalid sales item data.");
        }

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

        $salesItems[] = [
            'item_id' => $itemId,
            'quantity' => $quantity,
            'rate' => $rate,
            'total' => $total
        ];
    }

    $grandTotal = round($grandTotal, 2);

    // Start database transaction
    $conn->begin_transaction();

    // Check customer
    $checkCustomer = $conn->prepare("
        SELECT id FROM customers WHERE id = ?
    ");

    $checkCustomer->bind_param("i", $customer_id);
    $checkCustomer->execute();

    if ($checkCustomer->get_result()->num_rows === 0) {
        throw new Exception("Selected customer does not exist.");
    }

    $checkCustomer->close();

    // Insert sales
    $salesQuery = $conn->prepare("
        INSERT INTO sales
        (date, invoice_no, customer_id, total_amount)
        VALUES (?, ?, ?, ?)
    ");

    $salesQuery->bind_param(
        "ssid",
        $date,
        $invoice_no,
        $customer_id,
        $grandTotal
    );

    $salesQuery->execute();

    $salesId = $conn->insert_id;

    $salesQuery->close();

    // Prepare item validation query
    $checkItem = $conn->prepare("
        SELECT id FROM items WHERE id = ?
    ");

    // Prepare sales item insert query
    $itemQuery = $conn->prepare("
        INSERT INTO sales_items
        (sales_id, item_id, quantity, rate, total)
        VALUES (?, ?, ?, ?, ?)
    ");

    // Save sales items
    foreach ($salesItems as $salesItem) {

        $itemId = $salesItem['item_id'];
        $quantity = $salesItem['quantity'];
        $rate = $salesItem['rate'];
        $total = $salesItem['total'];

        // Check item exists
        $checkItem->bind_param("i", $itemId);
        $checkItem->execute();

        if ($checkItem->get_result()->num_rows === 0) {
            throw new Exception("Selected item does not exist.");
        }

        // Insert sales item
        $itemQuery->bind_param(
            "iiddd",
            $salesId,
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

    echo "<h3>Sales Error:</h3>";
    echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    echo '<br><br>';
    echo '<a href="create.php">Go Back</a>';
}
