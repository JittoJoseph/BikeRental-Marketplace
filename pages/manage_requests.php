<?php
require_once __DIR__ . '/../config.php';
requireAdmin();
require_once __DIR__ . '/../db_connect.php';

$pageTitle = 'Manage Booking Requests';

$success = '';
$error = '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id']) && isset($_POST['action'])) {
    $bookingId = intval($_POST['booking_id']);
    $action = $_POST['action'];
    
    $statusMap = [
        'approve' => 'approved',
        'reject' => 'rejected',
        'complete' => 'completed',
        'cancel' => 'cancelled'
    ];
    
    if (isset($statusMap[$action])) {
        $newStatus = $statusMap[$action];
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        
        if ($stmt->execute([$newStatus, $bookingId])) {
            $success = ucfirst($action) . 'd booking successfully!';
        } else {
            $error = 'Failed to update booking status.';
        }
    }
}

// Get filter
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query
$query = "SELECT b.*, u.name as user_name, u.email as user_email, 
          bk.name as bike_name, bk.image_url as bike_image, bk.price as bike_price,
          DATEDIFF(b.end_date, b.start_date) as rental_days
          FROM bookings b
          JOIN users u ON b.user_id = u.id
          JOIN bikes bk ON b.bike_id = bk.id";

if ($statusFilter !== 'all') {
    $query .= " WHERE b.status = :status";
}

$query .= " ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($query);
if ($statusFilter !== 'all') {
    $stmt->execute(['status' => $statusFilter]);
} else {
    $stmt->execute();
}
$bookings = $stmt->fetchAll();

// Get statistics
$stats = [
    'pending' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
    'approved' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'approved'")->fetchColumn(),
    'completed' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'completed'")->fetchColumn(),
    'rejected' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'rejected'")->fetchColumn()
];

require_once __DIR__ . '/../components/header.php';
?>

<!-- Premium Dark Hero Section -->
<div class="relative overflow-hidden py-16">
    <div class="absolute inset-0">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 glass rounded-full mb-6 border border-white/10">
                <i class="fas fa-clipboard-list text-2xl text-indigo-400"></i>
            </div>
            <h1 class="text-5xl md:text-6xl font-black text-white mb-4 tracking-tight">
                Booking <span class="animated-gradient text-transparent bg-clip-text">Requests</span>
            </h1>
            <p class="text-xl text-gray-400 max-w-2xl mx-auto leading-relaxed">
                Manage and process customer booking requests efficiently
            </p>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Navigation -->
    <div class="mb-8">
        <nav class="flex items-center space-x-2 text-sm text-gray-500">
            <a href="<?php echo BASE_URL; ?>/pages/admin_dashboard.php" class="hover:text-blue-600 transition-colors">
                <i class="fas fa-home mr-1"></i>Dashboard
            </a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-700 font-medium">Manage Requests</span>
        </nav>
    </div>

    <!-- Messages -->
    <?php if ($success): ?>
        <div class="glass border border-green-500/30 px-6 py-4 rounded-xl mb-8">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-400 mr-3 text-lg"></i>
                <span class="text-green-300 font-medium"><?php echo htmlspecialchars($success); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="glass border border-red-500/30 px-6 py-4 rounded-xl mb-8">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-400 mr-3 text-lg"></i>
                <span class="text-red-300 font-medium"><?php echo htmlspecialchars($error); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="group relative glass rounded-2xl border border-white/10 hover:border-yellow-500/50 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-yellow-500/10 to-orange-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl shadow-lg shadow-yellow-500/20">
                        <i class="fas fa-clock text-2xl text-white"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-black text-white"><?php echo $stats['pending']; ?></p>
                        <p class="text-sm text-gray-400 font-medium">Pending</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-yellow-400 font-semibold">Awaiting Review</span>
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
            </div>
        </div>

        <div class="group relative glass rounded-2xl border border-white/10 hover:border-green-500/50 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-emerald-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg shadow-green-500/20">
                        <i class="fas fa-check text-2xl text-white"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-black text-white"><?php echo $stats['approved']; ?></p>
                        <p class="text-sm text-gray-400 font-medium">Approved</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-green-400 font-semibold">Active Bookings</span>
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
            </div>
        </div>

        <div class="group relative glass rounded-2xl border border-white/10 hover:border-blue-500/50 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-indigo-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/20">
                        <i class="fas fa-flag-checkered text-2xl text-white"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-black text-white"><?php echo $stats['completed']; ?></p>
                        <p class="text-sm text-gray-400 font-medium">Completed</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-blue-400 font-semibold">Successfully Done</span>
                    <i class="fas fa-trophy text-blue-400"></i>
                </div>
            </div>
        </div>

        <div class="group relative glass rounded-2xl border border-white/10 hover:border-red-500/50 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-red-500/10 to-rose-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-red-500 to-rose-600 rounded-xl shadow-lg shadow-red-500/20">
                        <i class="fas fa-times text-2xl text-white"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-black text-white"><?php echo $stats['rejected']; ?></p>
                        <p class="text-sm text-gray-400 font-medium">Rejected</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-red-400 font-semibold">Not Approved</span>
                    <i class="fas fa-times-circle text-red-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="glass rounded-2xl border border-white/10 p-2 mb-8">
        <div class="flex flex-wrap gap-2">
            <a href="?status=all" 
               class="px-6 py-3 rounded-xl font-semibold transition-all <?php echo $statusFilter === 'all' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/20' : 'text-gray-400 hover:text-white hover:bg-white/10'; ?>">
                <i class="fas fa-list mr-2"></i>All Requests
            </a>
            <a href="?status=pending" 
               class="px-6 py-3 rounded-xl font-semibold transition-all <?php echo $statusFilter === 'pending' ? 'bg-gradient-to-r from-yellow-500 to-orange-500 text-white shadow-lg shadow-yellow-500/20' : 'text-gray-400 hover:text-white hover:bg-white/10'; ?>">
                <i class="fas fa-clock mr-2"></i>Pending
            </a>
            <a href="?status=approved" 
               class="px-6 py-3 rounded-xl font-semibold transition-all <?php echo $statusFilter === 'approved' ? 'bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-lg shadow-green-500/20' : 'text-gray-400 hover:text-white hover:bg-white/10'; ?>">
                <i class="fas fa-check mr-2"></i>Approved
            </a>
            <a href="?status=completed" 
               class="px-6 py-3 rounded-xl font-semibold transition-all <?php echo $statusFilter === 'completed' ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/20' : 'text-gray-400 hover:text-white hover:bg-white/10'; ?>">
                <i class="fas fa-flag-checkered mr-2"></i>Completed
            </a>
            <a href="?status=rejected" 
               class="px-6 py-3 rounded-xl font-semibold transition-all <?php echo $statusFilter === 'rejected' ? 'bg-gradient-to-r from-red-500 to-rose-500 text-white shadow-lg shadow-red-500/20' : 'text-gray-400 hover:text-white hover:bg-white/10'; ?>">
                <i class="fas fa-times mr-2"></i>Rejected
            </a>
            <a href="?status=cancelled" 
               class="px-6 py-3 rounded-xl font-semibold transition-all <?php echo $statusFilter === 'cancelled' ? 'bg-gradient-to-r from-gray-500 to-slate-500 text-white shadow-lg shadow-gray-500/20' : 'text-gray-400 hover:text-white hover:bg-white/10'; ?>">
                <i class="fas fa-ban mr-2"></i>Cancelled
            </a>
        </div>
    </div>

    <!-- Bookings List -->
    <?php if (empty($bookings)): ?>
        <div class="glass rounded-2xl border border-white/10 p-12 text-center">
            <div class="w-24 h-24 glass rounded-full flex items-center justify-center mx-auto mb-6 border border-white/10">
                <i class="fas fa-inbox text-4xl text-gray-400"></i>
            </div>
            <h3 class="text-2xl font-bold text-white mb-2">No Bookings Found</h3>
            <p class="text-gray-400">There are no booking requests matching your filter.</p>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($bookings as $booking): 
                $statusColors = [
                    'pending' => 'from-yellow-500 to-orange-500',
                    'approved' => 'from-green-500 to-emerald-500',
                    'rejected' => 'from-red-500 to-rose-500',
                    'completed' => 'from-blue-500 to-indigo-500',
                    'cancelled' => 'from-gray-500 to-slate-500'
                ];
                $statusColor = $statusColors[$booking['status']] ?? 'from-gray-500 to-gray-600';
            ?>
            <div class="glass rounded-2xl border border-white/10 overflow-hidden hover:border-white/20 transition-all duration-300">
                <div class="md:flex">
                    <!-- Bike Image -->
                    <div class="md:w-1/4 relative">
                        <img src="<?php echo htmlspecialchars($booking['bike_image']); ?>" 
                             alt="<?php echo htmlspecialchars($booking['bike_name']); ?>"
                             class="w-full h-full object-cover min-h-[200px]">
                        <div class="absolute top-4 right-4">
                            <span class="inline-flex items-center px-4 py-2 rounded-xl bg-gradient-to-r <?php echo $statusColor; ?> text-white font-bold text-sm shadow-lg">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Booking Details -->
                    <div class="md:w-3/4 p-6">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($booking['bike_name']); ?></h3>
                                    <span class="text-sm text-gray-400">#<?php echo $booking['id']; ?></span>
                                </div>
                                <div class="flex items-center space-x-4 text-sm text-gray-400 mb-4">
                                    <span class="flex items-center">
                                        <i class="fas fa-user mr-2 text-indigo-400"></i>
                                        <?php echo htmlspecialchars($booking['user_name']); ?>
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-envelope mr-2 text-indigo-400"></i>
                                        <?php echo htmlspecialchars($booking['user_email']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Info Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="glass border border-blue-500/30 p-4 rounded-xl hover:border-blue-500/50 transition-all">
                                <p class="text-xs text-gray-400 mb-1">Start Date</p>
                                <p class="font-bold text-white">
                                    <i class="fas fa-calendar-check text-blue-400 mr-1"></i>
                                    <?php echo date('M d, Y', strtotime($booking['start_date'])); ?>
                                </p>
                            </div>

                            <div class="glass border border-purple-500/30 p-4 rounded-xl hover:border-purple-500/50 transition-all">
                                <p class="text-xs text-gray-400 mb-1">End Date</p>
                                <p class="font-bold text-white">
                                    <i class="fas fa-calendar-times text-purple-400 mr-1"></i>
                                    <?php echo date('M d, Y', strtotime($booking['end_date'])); ?>
                                </p>
                            </div>

                            <div class="glass border border-green-500/30 p-4 rounded-xl hover:border-green-500/50 transition-all">
                                <p class="text-xs text-gray-400 mb-1">Duration</p>
                                <p class="font-bold text-white">
                                    <i class="fas fa-clock text-green-400 mr-1"></i>
                                    <?php echo $booking['rental_days']; ?> day<?php echo $booking['rental_days'] != 1 ? 's' : ''; ?>
                                </p>
                            </div>

                            <div class="glass border border-amber-500/30 p-4 rounded-xl hover:border-amber-500/50 transition-all">
                                <p class="text-xs text-gray-400 mb-1">Total Price</p>
                                <p class="font-bold text-white">
                                    <i class="fas fa-rupee-sign text-amber-400 mr-1"></i>
                                    ₹<?php echo number_format($booking['total_price'], 2); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <?php if ($booking['status'] === 'pending'): ?>
                        <div class="flex flex-wrap gap-3 pt-4 border-t border-white/10">
                            <form method="POST" class="inline">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" 
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-green-500/20 hover:shadow-green-500/40 transform hover:-translate-y-0.5">
                                    <i class="fas fa-check mr-2"></i>Approve
                                </button>
                            </form>

                            <form method="POST" class="inline">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" 
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-red-500/20 hover:shadow-red-500/40 transform hover:-translate-y-0.5"
                                        onclick="return confirm('Are you sure you want to reject this booking request?')">
                                    <i class="fas fa-times mr-2"></i>Reject
                                </button>
                            </form>
                        </div>
                        <?php elseif ($booking['status'] === 'approved'): ?>
                        <div class="flex flex-wrap gap-3 pt-4 border-t border-white/10">
                            <form method="POST" class="inline">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                <input type="hidden" name="action" value="complete">
                                <button type="submit" 
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40 transform hover:-translate-y-0.5">
                                    <i class="fas fa-flag-checkered mr-2"></i>Mark as Completed
                                </button>
                            </form>

                            <form method="POST" class="inline">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                <input type="hidden" name="action" value="cancel">
                                <button type="submit" 
                                        class="inline-flex items-center px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-gray-500/20 hover:shadow-gray-500/40 transform hover:-translate-y-0.5"
                                        onclick="return confirm('Are you sure you want to cancel this booking?')">
                                    <i class="fas fa-ban mr-2"></i>Cancel
                                </button>
                            </form>
                        </div>
                        <?php else: ?>
                        <div class="pt-4 border-t border-white/10">
                            <p class="text-sm text-gray-400 italic">
                                <i class="fas fa-info-circle mr-2"></i>
                                This booking is <?php echo $booking['status']; ?>. No further actions available.
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Timestamps -->
                        <div class="mt-4 pt-4 border-t border-white/10 flex items-center justify-between text-xs text-gray-500">
                            <span><i class="fas fa-clock mr-1"></i>Requested: <?php echo date('M d, Y H:i', strtotime($booking['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../components/footer.php'; ?>
