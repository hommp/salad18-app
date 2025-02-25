<?php
session_start();
require_once('../db.php');

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$product = [
    'id' => '',
    'name' => '',
    'description' => '',
    'price' => '',
    'image' => ''
];

// Edit mode - fetch product details
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header('Location: dashboard.php');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    
    // Handle file upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image = 'uploads/' . $fileName;
        } else {
            $error = "Failed to upload image";
        }
    }
    
    try {
        if (isset($_GET['id'])) {
            // Update existing product
            $sql = "UPDATE products SET name = ?, description = ?, price = ?";
            $params = [$name, $description, $price];
            
            if ($image) {
                $sql .= ", image = ?";
                $params[] = $image;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $_GET['id'];
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            // Add new product
            if (!$image) {
                throw new Exception("Image is required for new products");
            }
            
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $image]);
        }
        
        $success = "Product saved successfully";
        if (!isset($_GET['id'])) {
            // Clear form after successful add
            $product = [
                'id' => '',
                'name' => '',
                'description' => '',
                'price' => '',
                'image' => ''
            ];
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($_GET['id']) ? 'Edit' : 'Add'; ?> Product - Salad App</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold mb-6">
                <?php echo isset($_GET['id']) ? 'Edit' : 'Add'; ?> Product
            </h1>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" id="name" name="name" required 
                        value="<?php echo htmlspecialchars($product['name']); ?>"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" rows="3" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                    ><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                    <input type="number" id="price" name="price" step="0.01" required 
                        value="<?php echo htmlspecialchars($product['price']); ?>"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                </div>
                
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">
                        <?php echo isset($_GET['id']) ? 'New Image (optional)' : 'Image'; ?>
                    </label>
                    <input type="file" id="image" name="image" accept="image/*" 
                        <?php echo isset($_GET['id']) ? '' : 'required'; ?>
                        class="mt-1 block w-full">
                </div>
                
                <?php if ($product['image']): ?>
                    <div>
                        <p class="text-sm text-gray-600">Current Image:</p>
                        <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                            alt="Current product image" 
                            class="mt-2 max-w-xs">
                    </div>
                <?php endif; ?>
                
                <div class="flex gap-4">
                    <button type="submit" 
                        class="bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Save Product
                    </button>
                    <a href="dashboard.php" 
                        class="bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
