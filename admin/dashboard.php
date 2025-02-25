<?php
session_start();
require_once('../db.php');

// Cek apakah admin sudah login
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Ambil data produk dari database
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dasbor Admin - Salad App</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
  <!-- Header -->
  <header class="bg-green-500 p-4 text-white shadow-md">
    <div class="container mx-auto">
      <h1 class="text-3xl font-bold">Dasbor Admin</h1>
    </div>
  </header>

  <!-- Konten Utama -->
  <main class="container mx-auto p-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
      <h2 class="text-2xl font-semibold mb-4 md:mb-0">Produk</h2>
      <a href="product.php" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
        Tambah Produk Baru
      </a>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-lg shadow overflow-hidden">
        <thead class="bg-gray-200">
          <tr>
            <!-- <th class="px-6 py-3 text-left">ID</th> -->
            <th class="px-6 py-3 text-left">Nama</th>
            <th class="px-6 py-3 text-left">Harga</th>
            <th class="px-6 py-3 text-left">Gambar</th>
            <th class="px-6 py-3 text-left">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <?php foreach ($products as $product): ?>
            <tr class="hover:bg-gray-100">
              <!-- <td class="px-6 py-4"><?php echo htmlspecialchars($product['id']); ?></td> -->
              <td class="px-6 py-4"><?php echo htmlspecialchars($product['name']); ?></td>
              <td class="px-6 py-4"><?php echo htmlspecialchars($product['price']); ?></td>
              <td class="px-6 py-4">
                <?php if (!empty($product['image'])): ?>
                  <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                       alt="<?php echo htmlspecialchars($product['name']); ?>" 
                       class="w-20 h-20 object-cover rounded">
                <?php else: ?>
                  <span class="text-gray-400">Tidak ada gambar</span>
                <?php endif; ?>
              </td>
              <td class="px-6 py-4 space-x-2">
                <a href="product.php?id=<?php echo $product['id']; ?>" class="text-blue-600 hover:text-blue-800 transition">
                  Edit
                </a>
                <a href="delete.php?id=<?php echo $product['id']; ?>" class="text-red-600 hover:text-red-800 transition">
                  Hapus
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
