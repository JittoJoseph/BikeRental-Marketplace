<?php
require_once __DIR__ . '/../config.php';
requireAdmin();
require_once __DIR__ . '/../db_connect.php';

$pageTitle = 'Admin Dashboard';

// Handle delete bike
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM bikes WHERE id = ?");
    if ($stmt->execute([$deleteId])) {
        $success = 'Bike deleted successfully';
    } else {
        $error = 'Failed to delete bike';
    }
}

// Fetch all bikes
$stmt = $pdo->query("SELECT b.*, c.name as category_name FROM bikes b
                     JOIN categories c ON b.category_id = c.id
                     ORDER BY b.created_at DESC");
$bikes = $stmt->fetchAll();

// Get statistics
$statsStmt = $pdo->query("SELECT
    (SELECT COUNT(*) FROM bikes) as total_bikes,
    (SELECT COUNT(*) FROM users WHERE is_admin = 0) as total_users,
    (SELECT COUNT(*) FROM bookings WHERE status = 'pending') as pending_bookings,
    (SELECT COUNT(*) FROM reviews) as total_reviews");
$stats = $statsStmt->fetch();

require_once __DIR__ . '/../components/header.php';
?>

<!-- Premium Dark Hero Section -->
<div class="relative overflow-hidden py-16">
    <div class="absolute inset-0">
        <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 glass rounded-full mb-6 border border-white/10">
                <i class="fas fa-crown text-2xl text-yellow-400"></i>
            </div>
            <h1 class="text-5xl md:text-6xl font-black text-white mb-4 tracking-tight">
                Admin <span class="animated-gradient text-transparent bg-clip-text">Dashboard</span>
            </h1>
            <p class="text-xl text-gray-400 max-w-2xl mx-auto leading-relaxed">
                Manage your premium bike rental platform with powerful insights and controls
            </p>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Messages -->
    <?php if (isset($success)): ?>
        <div class="glass border border-green-500/30 px-6 py-4 rounded-xl mb-6">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-400 mr-3 text-lg"></i>
                <span class="text-green-300"><?php echo htmlspecialchars($success); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="glass border border-red-500/30 px-6 py-4 rounded-xl mb-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-400 mr-3 text-lg"></i>
                <span class="text-red-300"><?php echo htmlspecialchars($error); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Enhanced Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Total Bikes -->
        <div class="group relative glass rounded-2xl border border-white/10 hover:border-blue-500/50 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-indigo-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-500/20">
                        <i class="fas fa-motorcycle text-2xl text-white"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-black text-white"><?php echo $stats['total_bikes']; ?></p>
                        <p class="text-sm text-gray-400 font-medium">Total Bikes</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-blue-400 font-semibold">Active Fleet</span>
                    <i class="fas fa-arrow-up text-green-400"></i>
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="group relative glass rounded-2xl border border-white/10 hover:border-emerald-500/50 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-green-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl shadow-lg shadow-emerald-500/20">
                        <i class="fas fa-users text-2xl text-white"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-black text-white"><?php echo $stats['total_users']; ?></p>
                        <p class="text-sm text-gray-400 font-medium">Registered Users</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-emerald-400 font-semibold">Active Customers</span>
                    <i class="fas fa-users text-emerald-400"></i>
                </div>
            </div>
        </div>

        <!-- Pending Bookings -->
        <div class="group relative glass rounded-2xl border border-white/10 hover:border-amber-500/50 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-yellow-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-xl shadow-lg shadow-amber-500/20">
                        <i class="fas fa-clock text-2xl text-white"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-black text-white"><?php echo $stats['pending_bookings']; ?></p>
                        <p class="text-sm text-gray-400 font-medium">Pending Bookings</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-amber-400 font-semibold">Requires Attention</span>
                    <i class="fas fa-exclamation-triangle text-amber-400"></i>
                </div>
            </div>
        </div>

        <!-- Total Reviews -->
        <div class="group relative glass rounded-2xl border border-white/10 hover:border-purple-500/50 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-violet-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl shadow-lg shadow-purple-500/20">
                        <i class="fas fa-star text-2xl text-white"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-black text-white"><?php echo $stats['total_reviews']; ?></p>
                        <p class="text-sm text-gray-400 font-medium">Total Reviews</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-purple-400 font-semibold">Customer Feedback</span>
                    <i class="fas fa-star-half-alt text-purple-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="glass rounded-2xl border border-white/10 p-8 mb-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">Quick Actions</h2>
            <div class="flex space-x-3">
                <a href="<?php echo BASE_URL; ?>/pages/add_bike.php"
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40 transform hover:-translate-y-0.5">
                    <i class="fas fa-plus mr-2"></i>Add New Bike
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/explore.php"
                   class="inline-flex items-center px-6 py-3 glass border border-white/20 text-white font-semibold rounded-xl hover:bg-white/10 transition-all duration-200 transform hover:-translate-y-0.5">
                    <i class="fas fa-eye mr-2"></i>View Public Site
                </a>
            </div>
        </div>

        <!-- Action Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="glass border border-blue-500/30 p-6 rounded-xl hover:border-blue-500/50 hover:bg-white/5 transition-all duration-200">
                <div class="flex items-center mb-4">
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg mr-4 shadow-lg shadow-blue-500/20">
                        <i class="fas fa-motorcycle text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white">Bike Management</h3>
                        <p class="text-sm text-gray-400">Add, edit, or remove bikes</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="<?php echo BASE_URL; ?>/pages/add_bike.php" class="text-blue-400 hover:text-blue-300 text-sm font-medium">Add Bike</a>
                    <span class="text-gray-600">•</span>
                    <span class="text-gray-400 text-sm"><?php echo count($bikes); ?> bikes active</span>
                </div>
            </div>

            <div class="glass border border-emerald-500/30 p-6 rounded-xl hover:border-emerald-500/50 hover:bg-white/5 transition-all duration-200">
                <div class="flex items-center mb-4">
                    <div class="p-3 bg-gradient-to-br from-emerald-500 to-green-600 rounded-lg mr-4 shadow-lg shadow-emerald-500/20">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white">User Management</h3>
                        <p class="text-sm text-gray-400">Monitor user activity</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <span class="text-emerald-400 text-sm font-medium"><?php echo $stats['total_users']; ?> registered users</span>
                </div>
            </div>

            <div class="glass border border-amber-500/30 p-6 rounded-xl hover:border-amber-500/50 hover:bg-white/5 transition-all duration-200">
                <div class="flex items-center mb-4">
                    <div class="p-3 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-lg mr-4 shadow-lg shadow-amber-500/20">
                        <i class="fas fa-calendar-check text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white">Booking Management</h3>
                        <p class="text-sm text-gray-400">Handle reservations</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <span class="text-amber-400 text-sm font-medium"><?php echo $stats['pending_bookings']; ?> pending bookings</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Bikes Table -->
    <div class="glass rounded-2xl border border-white/10 overflow-hidden">
        <div class="p-8 border-b border-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-white">Fleet Management</h2>
                    <p class="text-gray-400 mt-1">Manage your bike inventory and performance</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="text-sm text-gray-400">
                        <i class="fas fa-circle text-green-400 mr-1"></i><?php echo count($bikes); ?> bikes total
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-8 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Bike</th>
                        <th class="px-8 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Category</th>
                        <th class="px-8 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Pricing</th>
                        <th class="px-8 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Rating</th>
                        <th class="px-8 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <?php foreach ($bikes as $bike): ?>
                    <tr class="hover:bg-white/5 transition-colors duration-200">
                        <td class="px-8 py-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-16 h-16">
                                    <img class="w-16 h-16 rounded-xl object-cover shadow-sm border border-white/10" src="<?php echo htmlspecialchars($bike['image_url']); ?>" alt="<?php echo htmlspecialchars($bike['name']); ?>">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-white"><?php echo htmlspecialchars($bike['name']); ?></div>
                                    <div class="text-sm text-gray-400 truncate max-w-xs"><?php echo htmlspecialchars(substr($bike['description'], 0, 60)); ?>...</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                <i class="fas fa-tag mr-1"></i><?php echo htmlspecialchars($bike['category_name']); ?>
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            <div class="text-sm">
                                <div class="font-semibold text-white">₹<?php echo number_format($bike['price'], 2); ?><span class="text-gray-400 font-normal">/day</span></div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center">
                                <div class="flex text-yellow-400">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star text-xs <?php echo $i <= $bike['rating'] ? 'text-yellow-400' : 'text-gray-600'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="ml-2 text-sm font-medium text-white"><?php echo number_format($bike['rating'], 1); ?></span>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center space-x-3">
                                <a href="<?php echo BASE_URL; ?>/pages/bike_details.php?id=<?php echo $bike['id']; ?>"
                                   class="inline-flex items-center px-3 py-2 border border-blue-500/30 text-blue-400 bg-blue-500/10 rounded-lg hover:bg-blue-500/20 hover:border-blue-500/50 transition-all duration-200 text-sm font-medium">
                                    <i class="fas fa-eye mr-1"></i>View
                                </a>
                                <a href="<?php echo BASE_URL; ?>/pages/edit_bike.php?id=<?php echo $bike['id']; ?>"
                                   class="inline-flex items-center px-3 py-2 border border-green-500/30 text-green-400 bg-green-500/10 rounded-lg hover:bg-green-500/20 hover:border-green-500/50 transition-all duration-200 text-sm font-medium">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                                <a href="?delete=<?php echo $bike['id']; ?>"
                                   class="inline-flex items-center px-3 py-2 border border-red-500/30 text-red-400 bg-red-500/10 rounded-lg hover:bg-red-500/20 hover:border-red-500/50 transition-all duration-200 text-sm font-medium"
                                   onclick="return confirm('Are you sure you want to delete this bike? This action cannot be undone.')">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (count($bikes) === 0): ?>
        <div class="text-center py-16">
            <div class="mx-auto w-24 h-24 glass rounded-full flex items-center justify-center mb-4 border border-white/10">
                <i class="fas fa-motorcycle text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-white mb-2">No bikes found</h3>
            <p class="text-gray-400 mb-6">Get started by adding your first bike to the fleet.</p>
            <a href="<?php echo BASE_URL; ?>/pages/add_bike.php"
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg shadow-blue-500/20">
                <i class="fas fa-plus mr-2"></i>Add Your First Bike
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../components/footer.php'; ?>
