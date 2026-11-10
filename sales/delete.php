
<?php

session_start();

require_once('../db_config.php');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Only POST request allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id = (int)($_POST['id'] ?? 0);

try {

    if ($id <= 0) {
        throw new Exception("Invalid sales ID.");
    }

    // Start transaction
    $conn->begin_transaction();

    // Check sales exists
    $checkSales = $conn->prepare("
        SELECT id FROM sales WHERE id = ?
    ");

    $checkSales->bind_param("i", $id);
    $checkSales->execute();

    if ($checkSales->get_result()->num_rows === 0) {
        throw new Exception("Sales record not found.");
    }

    $checkSales->close();

    // Delete sales
    // sales_items will be deleted automatically
    // because of ON DELETE CASCADE
    $deleteSales = $conn->prepare("
        DELETE FROM sales WHERE id = ?
    ");

    $deleteSales->bind_param("i", $id);
    $deleteSales->execute();

    $deleteSales->close();

    // Commit transaction
    $conn->commit();

    $_SESSION['message'] = "Sales deleted successfully.";

} catch (Throwable $e) {

    try {
        $conn->rollback();
    } catch (Throwable $rollbackError) {
        // Ignore rollback errors
    }

    $_SESSION['message'] = "Error: " . $e->getMessage();
}

header("Location: index.php");
exit;

?>
