<?php
require_once __DIR__ . '/../config.php';
requireAdmin();
require_once __DIR__ . '/../db_connect.php';

$pageTitle = 'Edit Bike';

$error = '';
$success = '';

// Get bike ID
$bikeId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($bikeId <= 0) {
    header('Location: ' . BASE_URL . '/pages/admin_dashboard.php');
    exit();
}

// Fetch bike details
$stmt = $pdo->prepare("SELECT * FROM bikes WHERE id = ?");
$stmt->execute([$bikeId]);
$bike = $stmt->fetch();

if (!$bike) {
    header('Location: ' . BASE_URL . '/pages/admin_dashboard.php');
    exit();
}

// Fetch categories
$categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $categoriesStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $categoryId = intval($_POST['category_id']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $imageUrl = trim($_POST['image_url']);
    
    if (empty($name) || $categoryId <= 0 || empty($price)) {
        $error = 'Please fill all required fields';
    } elseif ($price <= 0) {
        $error = 'Price must be greater than 0';
    } else {
        $stmt = $pdo->prepare("UPDATE bikes SET name = ?, category_id = ?, description = ?, price = ?, image_url = ? 
                               WHERE id = ?");
        
        if ($stmt->execute([$name, $categoryId, $description, $price, $imageUrl, $bikeId])) {
            $success = 'Bike updated successfully!';
            // Refresh bike data
            $stmt = $pdo->prepare("SELECT * FROM bikes WHERE id = ?");
            $stmt->execute([$bikeId]);
            $bike = $stmt->fetch();
        } else {
            $error = 'Failed to update bike. Please try again.';
        }
    }
}

require_once __DIR__ . '/../components/header.php';
?>

<div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold mb-4">Edit Bike</h1>
        <p class="text-xl text-blue-100">Update bike information</p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="<?php echo BASE_URL; ?>/pages/admin_dashboard.php" 
           class="text-blue-600 hover:text-blue-800 inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
        </a>
    </div>

    <!-- Messages -->
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-lg p-8">
        <form method="POST" class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Bike Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" required
                       value="<?php echo htmlspecialchars($bike['name']); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="e.g., Yamaha MT-15">
            </div>

            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Category <span class="text-red-500">*</span>
                </label>
                <select id="category_id" name="category_id" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"
                                <?php echo ($bike['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                    Price per Day (₹) <span class="text-red-500">*</span>
                </label>
                <input type="number" id="price" name="price" step="0.01" min="0" required
                       value="<?php echo htmlspecialchars($bike['price']); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="25.00">
            </div>

            <div>
                <label for="image_url" class="block text-sm font-medium text-gray-700 mb-2">
                    Image URL
                </label>
                <input type="url" id="image_url" name="image_url"
                       value="<?php echo htmlspecialchars($bike['image_url']); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="https://example.com/bike-image.jpg">
                <p class="text-sm text-gray-500 mt-1">Enter a direct URL to the bike image</p>
                
                <?php if (!empty($bike['image_url'])): ?>
                    <div class="mt-3">
                        <p class="text-sm font-medium text-gray-700 mb-2">Current Image:</p>
                        <img src="<?php echo htmlspecialchars($bike['image_url']); ?>" 
                             alt="Current bike image"
                             class="w-48 h-32 object-cover rounded-lg border">
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description
                </label>
                <textarea id="description" name="description" rows="6"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Describe the bike features, specifications, and key highlights..."><?php echo htmlspecialchars($bike['description']); ?></textarea>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600">
                    <strong>Current Rating:</strong> 
                    <span class="text-yellow-500">
                        <i class="fas fa-star"></i> <?php echo number_format($bike['rating'], 1); ?>
                    </span>
                </p>
                <p class="text-xs text-gray-500 mt-1">Rating is automatically calculated from user reviews</p>
            </div>

            <div class="flex items-center justify-between pt-4">
                <a href="<?php echo BASE_URL; ?>/pages/admin_dashboard.php" 
                   class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-save mr-2"></i>Update Bike
                </button>
            </div>
        </form>
    </div>

    <!-- Delete Section -->
    <div class="mt-8 bg-red-50 rounded-xl p-6 border border-red-200">
        <h3 class="text-lg font-semibold text-red-800 mb-2">
            <i class="fas fa-exclamation-triangle mr-2"></i>Danger Zone
        </h3>
        <p class="text-sm text-red-600 mb-4">
            Deleting this bike will also remove all associated bookings and reviews. This action cannot be undone.
        </p>
        <a href="<?php echo BASE_URL; ?>/pages/admin_dashboard.php?delete=<?php echo $bikeId; ?>" 
           class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition inline-block"
           onclick="return confirm('Are you absolutely sure? This will permanently delete this bike and all related data.')">
            <i class="fas fa-trash mr-2"></i>Delete This Bike
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../components/footer.php'; ?>
