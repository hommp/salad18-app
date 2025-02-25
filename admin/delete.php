<?php
session_start();
require_once('../db.php');

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

try {
    // Get product image before deletion
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Delete the product
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$_GET['id']]);

        // Delete the image file if it exists
        if ($product['image'] && file_exists('../' . $product['image'])) {
            unlink('../' . $product['image']);
        }

        $_SESSION['success'] = "Product deleted successfully";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error deleting product: " . $e->getMessage();
}

header('Location: dashboard.php');
exit;
?>
