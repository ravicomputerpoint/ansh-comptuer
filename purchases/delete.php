
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
        throw new Exception("Invalid purchase ID.");
    }

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

    // Delete purchase
    // purchase_items will be deleted automatically
    // because of ON DELETE CASCADE
    $deletePurchase = $conn->prepare("
        DELETE FROM purchases WHERE id = ?
    ");

    $deletePurchase->bind_param("i", $id);
    $deletePurchase->execute();

    $deletePurchase->close();

    // Commit transaction
    $conn->commit();

    $_SESSION['message'] = "Purchase deleted successfully.";

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
