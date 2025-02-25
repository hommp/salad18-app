<?php
session_start();
require_once('../db.php');

// Cek apakah admin sudah login
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

// Mode Ubah - ambil detail produk
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header('Location: dashboard.php');
        exit;
    }
}

// Tangani pengiriman form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    
    // Tangani unggahan file
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
            $error = "Gagal mengunggah gambar";
        }
    }
    
    try {
        if (isset($_GET['id'])) {
            // Perbarui produk yang sudah ada
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
            // Tambah produk baru
            if (!$image) {
                throw new Exception("Gambar wajib diunggah untuk produk baru");
            }
            
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $image]);
        }
        
        $success = "Produk berhasil disimpan";
        if (!isset($_GET['id'])) {
            // Bersihkan form setelah berhasil menambahkan produk
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
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo isset($_GET['id']) ? 'Ubah' : 'Tambah'; ?> Produk - Salad App</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <!-- Header -->
  <header class="bg-gradient-to-r from-green-500 to-teal-500 py-6 shadow">
    <div class="container mx-auto px-4">
      <h1 class="text-white text-3xl font-bold text-center">
        <?php echo isset($_GET['id']) ? 'Ubah' : 'Tambah'; ?> Produk
      </h1>
    </div>
  </header>

  <!-- Konten Utama -->
  <main class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl mx-auto">
      
      <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>
      
      <?php if ($success): ?>
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700">
          <?php echo htmlspecialchars($success); ?>
        </div>
      <?php endif; ?>
      
      <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <!-- Nama -->
        <div>
          <label for="name" class="block text-gray-700 font-medium mb-2">Nama</label>
          <input
            type="text"
            id="name"
            name="name"
            required
            value="<?php echo htmlspecialchars($product['name']); ?>"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400"
          >
        </div>
        
        <!-- Deskripsi -->
        <div>
          <label for="description" class="block text-gray-700 font-medium mb-2">Deskripsi</label>
          <textarea
            id="description"
            name="description"
            rows="4"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400"
          ><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        
        <!-- Harga -->
        <div>
          <label for="price" class="block text-gray-700 font-medium mb-2">Harga</label>
          <input
            type="number"
            id="price"
            name="price"
            step="0.01"
            required
            value="<?php echo htmlspecialchars($product['price']); ?>"
            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-400"
          >
        </div>
        
        <!-- Gambar -->
        <div>
          <label for="image" class="block text-gray-700 font-medium mb-2">
            <?php echo isset($_GET['id']) ? 'Gambar Baru (opsional)' : 'Gambar'; ?>
          </label>
          <input
            type="file"
            id="image"
            name="image"
            accept="image/*"
            <?php echo isset($_GET['id']) ? '' : 'required'; ?>
            class="w-full"
          >
        </div>
        
        <!-- Pratinjau Gambar Saat Ini -->
        <?php if ($product['image']): ?>
          <div>
            <p class="text-sm text-gray-600 mb-2">Gambar Saat Ini:</p>
            <img src="../<?php echo htmlspecialchars($product['image']); ?>"
                 alt="Gambar produk saat ini"
                 class="rounded-md shadow-md max-w-xs">
          </div>
        <?php endif; ?>
        
        <!-- Tombol -->
        <div class="flex items-center gap-4">
          <button
            type="submit"
            class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition duration-200"
          >
            Simpan Produk
          </button>
          <a
            href="dashboard.php"
            class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 transition duration-200"
          >
            Batal
          </a>
        </div>
      </form>
    </div>
  </main>
</body>
</html>
