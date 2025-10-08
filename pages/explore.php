<?php
$pageTitle = 'Explore Bikes';
require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../db_connect.php';

// Get all categories
$categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $categoriesStmt->fetchAll();

// Filter by category
$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$sql = "SELECT b.*, c.name as category_name FROM bikes b 
        JOIN categories c ON b.category_id = c.id 
        WHERE 1=1";

$params = [];

if ($categoryFilter > 0) {
    $sql .= " AND b.category_id = ?";
    $params[] = $categoryFilter;
}

if (!empty($searchQuery)) {
    $sql .= " AND (b.name LIKE ? OR b.description LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

$sql .= " ORDER BY b.rating DESC, b.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bikes = $stmt->fetchAll();
?>

<!-- Premium Dark Hero -->
<div class="relative overflow-hidden py-16">
    <div class="absolute inset-0">
        <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl"></div>
    </div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-flex items-center px-4 py-2 glass rounded-full border border-white/10 text-sm mb-6">
            <i class="fas fa-compass text-blue-400 mr-2"></i>
            <span class="text-gray-300 font-semibold">Discover Your Ride</span>
        </span>
        <h1 class="text-5xl md:text-6xl font-black text-white mb-4">
            Explore Our <span class="animated-gradient text-transparent bg-clip-text">Collection</span>
        </h1>
        <p class="text-xl text-gray-400 max-w-2xl mx-auto">
            Find the perfect bike for your next adventure
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Search and Filter Section -->
    <div class="mb-8">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" placeholder="Search bikes by name or description..." 
                       value="<?php echo htmlspecialchars($searchQuery); ?>"
                       class="w-full px-5 py-4 glass rounded-xl border border-white/10 focus:outline-none focus:border-blue-500/50 text-white placeholder-gray-500 transition-all">
            </div>
            
            <div class="md:w-64">
                <select name="category" 
                        class="w-full px-5 py-4 glass rounded-xl border border-white/10 focus:outline-none focus:border-purple-500/50 text-white transition-all appearance-none">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                                <?php echo $categoryFilter == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-4 rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40 font-semibold">
                <i class="fas fa-search mr-2"></i>Search
            </button>
            
            <?php if ($categoryFilter > 0 || !empty($searchQuery)): ?>
                <a href="<?php echo BASE_URL; ?>/pages/explore.php" class="glass border border-white/10 text-gray-300 px-8 py-4 rounded-xl hover:bg-white/10 hover:border-red-500/50 hover:text-white transition-all text-center font-semibold">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Results Count -->
    <div class="mb-6">
        <p class="text-gray-400">
            Found <span class="font-bold bg-gradient-to-r from-blue-400 to-purple-400 text-transparent bg-clip-text"><?php echo count($bikes); ?></span> bike(s)
            <?php if ($categoryFilter > 0 || !empty($searchQuery)): ?>
                <span class="text-gray-500">matching your criteria</span>
            <?php endif; ?>
        </p>
    </div>

    <!-- Bikes Grid -->
    <?php if (count($bikes) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($bikes as $bike): ?>
            <div class="glass rounded-2xl overflow-hidden border border-white/10 hover:border-blue-500/50 hover:shadow-2xl hover:shadow-blue-500/20 transition-all duration-300 transform hover:-translate-y-2">
                <div class="relative h-48 overflow-hidden">
                    <img src="<?php echo htmlspecialchars($bike['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($bike['name']); ?>"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute top-4 right-4 bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-3 py-2 rounded-xl text-sm font-bold shadow-lg">
                        <i class="fas fa-star mr-1"></i><?php echo number_format($bike['rating'], 1); ?>
                    </div>
                </div>
                
                <div class="p-6">
                    <span class="glass px-3 py-1 rounded-lg text-xs font-semibold text-blue-400 border border-blue-500/30 inline-block mb-3">
                        <?php echo htmlspecialchars($bike['category_name']); ?>
                    </span>
                    <h3 class="text-xl font-bold text-white mt-2 mb-2 hover:text-blue-400 transition-colors"><?php echo htmlspecialchars($bike['name']); ?></h3>
                    <p class="text-gray-400 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($bike['description']); ?></p>
                    
                    <div class="flex items-center justify-between pt-4 border-t border-white/10">
                        <div>
                            <p class="text-gray-500 text-xs mb-1">Starting from</p>
                            <div>
                                <span class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 text-transparent bg-clip-text">₹<?php echo number_format($bike['price'], 0); ?></span>
                                <span class="text-gray-500 text-sm">/day</span>
                            </div>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/pages/bike_details.php?id=<?php echo $bike['id']; ?>" 
                           class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-5 py-2.5 rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40 font-semibold">
                            View <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-16 glass rounded-2xl border border-white/10">
            <i class="fas fa-search text-6xl text-gray-600 mb-4"></i>
            <h3 class="text-2xl font-semibold text-white mb-2">No bikes found</h3>
            <p class="text-gray-400 mb-6">Try adjusting your search or filter criteria</p>
            <a href="<?php echo BASE_URL; ?>/pages/explore.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition inline-block">
                View All Bikes
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../components/footer.php'; ?>
