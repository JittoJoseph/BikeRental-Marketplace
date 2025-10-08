<?php
require_once __DIR__ . '/../config.php';
requireAdmin();
require_once __DIR__ . '/../db_connect.php';

$pageTitle = 'Add New Bike';

$error = '';
$success = '';

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
        $stmt = $pdo->prepare("INSERT INTO bikes (name, category_id, description, price, image_url, rating)
                               VALUES (?, ?, ?, ?, ?, 0)");

        if ($stmt->execute([$name, $categoryId, $description, $price, $imageUrl])) {
            $success = 'Bike added successfully!';
            // Clear form
            $_POST = array();
        } else {
            $error = 'Failed to add bike. Please try again.';
        }
    }
}

require_once __DIR__ . '/../components/header.php';
?>

<!-- Premium Hero Section -->
<div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600">
    <div class="absolute inset-0 bg-black/10"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-cyan-500/20"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/10 backdrop-blur-sm rounded-full mb-6">
                <i class="fas fa-plus text-2xl text-white"></i>
            </div>
            <h1 class="text-5xl md:text-6xl font-bold text-white mb-4 tracking-tight">
                Add New <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-200 to-cyan-200">Bike</span>
            </h1>
            <p class="text-xl text-emerald-100 max-w-2xl mx-auto leading-relaxed">
                Expand your premium fleet with carefully curated motorcycles
            </p>
        </div>
    </div>

    <!-- Decorative Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-emerald-400/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-cyan-400/10 rounded-full blur-3xl"></div>
    </div>
</div>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Navigation -->
    <div class="mb-8">
        <nav class="flex items-center space-x-2 text-sm text-gray-500">
            <a href="<?php echo BASE_URL; ?>/pages/admin_dashboard.php" class="hover:text-blue-600 transition-colors">
                <i class="fas fa-home mr-1"></i>Dashboard
            </a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-700 font-medium">Add New Bike</span>
        </nav>
    </div>

    <!-- Messages -->
    <?php if ($error): ?>
        <div class="bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 text-red-800 px-6 py-4 rounded-2xl mb-8 shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3 text-lg"></i>
                <span class="font-medium"><?php echo htmlspecialchars($error); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-800 px-6 py-4 rounded-2xl mb-8 shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3 text-lg"></i>
                <span class="font-medium"><?php echo htmlspecialchars($success); ?></span>
                <a href="<?php echo BASE_URL; ?>/pages/admin_dashboard.php" class="ml-4 underline hover:text-green-700 transition-colors">View all bikes</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Form Card -->
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
        <!-- Form Header -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-8 py-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Bike Information</h2>
                    <p class="text-gray-600 mt-1">Fill in the details to add a new bike to your fleet</p>
                </div>
                <div class="hidden md:flex items-center space-x-2">
                    <div class="w-3 h-3 bg-emerald-400 rounded-full"></div>
                    <div class="w-3 h-3 bg-gray-300 rounded-full"></div>
                    <div class="w-3 h-3 bg-gray-300 rounded-full"></div>
                </div>
            </div>
        </div>

        <form method="POST" class="p-8 space-y-8">
            <!-- Basic Information Section -->
            <div class="space-y-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="flex-shrink-0 w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-info-circle text-emerald-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Basic Information</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Bike Name -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-3">
                            Bike Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-motorcycle text-gray-400"></i>
                            </div>
                            <input type="text" id="name" name="name" required
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                   class="w-full pl-12 pr-4 py-4 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200 text-gray-900 placeholder-gray-500"
                                   placeholder="e.g., Yamaha MT-15, Royal Enfield Himalayan">
                        </div>
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-3">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-tag text-gray-400"></i>
                            </div>
                            <select id="category_id" name="category_id" required
                                    class="w-full pl-12 pr-4 py-4 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200 text-gray-900 appearance-none bg-white">
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"
                                            <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Price -->
                    <div>
                        <label for="price" class="block text-sm font-semibold text-gray-700 mb-3">
                            Price per Day (₹) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-gray-400 font-semibold">₹</span>
                            </div>
                            <input type="number" id="price" name="price" step="0.01" min="0" required
                                   value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>"
                                   class="w-full pl-12 pr-4 py-4 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200 text-gray-900 placeholder-gray-500"
                                   placeholder="250.00">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <span class="text-gray-400 text-sm">/day</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Media Section -->
            <div class="space-y-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-image text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Media & Images</h3>
                </div>

                <div>
                    <label for="image_url" class="block text-sm font-semibold text-gray-700 mb-3">
                        Bike Image URL
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-link text-gray-400"></i>
                        </div>
                        <input type="url" id="image_url" name="image_url"
                               value="<?php echo isset($_POST['image_url']) ? htmlspecialchars($_POST['image_url']) : ''; ?>"
                               class="w-full pl-12 pr-4 py-4 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200 text-gray-900 placeholder-gray-500"
                               placeholder="https://example.com/bike-image.jpg">
                    </div>
                    <p class="text-sm text-gray-500 mt-2 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Enter a direct URL to a high-quality bike image (recommended: 500x500px or larger)
                    </p>
                </div>

                <!-- Image Preview -->
                <div id="image-preview" class="hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Image Preview</label>
                    <div class="relative w-full max-w-md mx-auto">
                        <img id="preview-img" src="" alt="Bike preview" class="w-full h-64 object-cover rounded-xl shadow-lg">
                        <div class="absolute inset-0 bg-black/0 hover:bg-black/10 transition-all duration-200 rounded-xl flex items-center justify-center">
                            <div class="bg-white/90 backdrop-blur-sm px-4 py-2 rounded-lg opacity-0 hover:opacity-100 transition-opacity duration-200">
                                <i class="fas fa-eye text-gray-700"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description Section -->
            <div class="space-y-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-align-left text-purple-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Description & Details</h3>
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-3">
                        Bike Description
                    </label>
                    <textarea id="description" name="description" rows="6"
                              class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200 text-gray-900 placeholder-gray-500 resize-none"
                              placeholder="Describe the bike's features, specifications, performance characteristics, and any special highlights that make it unique..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    <p class="text-sm text-gray-500 mt-2 flex items-center">
                        <i class="fas fa-lightbulb mr-2"></i>
                        Provide detailed information to help customers understand what makes this bike special
                    </p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-8 border-t border-gray-200">
                <a href="<?php echo BASE_URL; ?>/pages/admin_dashboard.php"
                   class="inline-flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>

                <div class="flex space-x-4">
                    <button type="button"
                            onclick="resetForm()"
                            class="inline-flex items-center px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-xl transition-all duration-200">
                        <i class="fas fa-undo mr-2"></i>Reset Form
                    </button>
                    <button type="submit"
                            class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="fas fa-plus mr-2"></i>Add Bike to Fleet
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Help Section -->
    <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-8 border border-blue-200">
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-lightbulb text-blue-600 text-xl"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Need Help with Images?</h3>
                <p class="text-gray-600 mb-4">Here are some high-quality bike images you can use for testing:</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow duration-200">
                        <img src="https://images.unsplash.com/photo-1558981403-c5f9899a28bc?w=300" alt="Yamaha MT-15" class="w-full h-24 object-cover rounded mb-2">
                        <p class="text-sm font-medium text-gray-800">Yamaha MT-15</p>
                        <p class="text-xs text-gray-500">https://images.unsplash.com/photo-1558981403-c5f9899a28bc?w=500</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow duration-200">
                        <img src="https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=300" alt="Honda CB350" class="w-full h-24 object-cover rounded mb-2">
                        <p class="text-sm font-medium text-gray-800">Honda CB350</p>
                        <p class="text-xs text-gray-500">https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=500</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow duration-200">
                        <img src="https://images.unsplash.com/photo-1449426468159-d96dbf08f19f?w=300" alt="KTM Duke 390" class="w-full h-24 object-cover rounded mb-2">
                        <p class="text-sm font-medium text-gray-800">KTM Duke 390</p>
                        <p class="text-xs text-gray-500">https://images.unsplash.com/photo-1449426468159-d96dbf08f19f?w=500</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Image preview functionality
document.getElementById('image_url').addEventListener('input', function() {
    const url = this.value.trim();
    const preview = document.getElementById('image-preview');
    const img = document.getElementById('preview-img');

    if (url) {
        img.src = url;
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
});

// Reset form function
function resetForm() {
    if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
        document.querySelector('form').reset();
        document.getElementById('image-preview').classList.add('hidden');
    }
}

// Auto-focus first field
document.getElementById('name').focus();
</script>

<?php require_once __DIR__ . '/../components/footer.php'; ?>
