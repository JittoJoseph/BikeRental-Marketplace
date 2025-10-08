<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db_connect.php';
requireLogin();

$pageTitle = 'My Bookings';

// Handle refund claim
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_refund'])) {
    $bookingId = intval($_POST['booking_id']);

    try {
        // Check if booking is completed and security deposit exists
        $stmt = $pdo->prepare("
            SELECT sd.*, b.status as booking_status
            FROM security_deposits sd
            JOIN bookings b ON sd.booking_id = b.id
            WHERE sd.booking_id = ? AND sd.user_id = ? AND sd.status = 'held'
        ");
        $stmt->execute([$bookingId, $_SESSION['user_id']]);
        $deposit = $stmt->fetch();

        if ($deposit && $deposit['booking_status'] === 'completed') {
            // Create refund request (could be a separate table or status update)
            $updateStmt = $pdo->prepare("
                UPDATE security_deposits
                SET status = 'refund_requested', refund_date = NOW()
                WHERE id = ?
            ");
            $updateStmt->execute([$deposit['id']]);
            $message = 'Refund request submitted successfully! We will process your security deposit refund within 3-5 business days.';
        } else {
            $error = 'Unable to process refund request. Please ensure your booking is completed and deposit is held.';
        }
    } catch (Exception $e) {
        $error = 'Failed to process refund request.';
    }
}

$userId = $_SESSION['user_id'];

// Get user bookings with payment and deposit info
$stmt = $pdo->prepare("
    SELECT b.*, bk.name as bike_name, bk.image_url as bike_image,
           bk.price as bike_price, c.name as category_name,
           DATEDIFF(b.end_date, b.start_date) as rental_days,
           COALESCE(p_rental.amount, 0) as rental_paid,
           COALESCE(p_deposit.amount, 0) as deposit_paid,
           sd.status as deposit_status, sd.amount as deposit_amount
    FROM bookings b
    JOIN bikes bk ON b.bike_id = bk.id
    JOIN categories c ON bk.category_id = c.id
    LEFT JOIN payments p_rental ON p_rental.booking_id = b.id AND p_rental.payment_type = 'booking' AND p_rental.status = 'completed'
    LEFT JOIN payments p_deposit ON p_deposit.booking_id = b.id AND p_deposit.payment_type = 'security_deposit' AND p_deposit.status = 'completed'
    LEFT JOIN security_deposits sd ON sd.booking_id = b.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

// Get statistics
$stats = [
    'total' => count($bookings),
    'pending' => count(array_filter($bookings, fn($b) => $b['status'] === 'pending')),
    'approved' => count(array_filter($bookings, fn($b) => $b['status'] === 'approved')),
    'completed' => count(array_filter($bookings, fn($b) => $b['status'] === 'completed'))
];

require_once __DIR__ . '/../components/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Hero Section -->
    <div class="glass rounded-2xl border border-white/10 p-8 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">My Bookings</h1>
                <p class="text-gray-400">Track and manage all your bike rental bookings</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400">
                    <?php echo $stats['total']; ?>
                </div>
                <div class="text-sm text-gray-400">Total Bookings</div>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="glass border border-green-500/30 px-6 py-4 rounded-xl mb-8">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-400 mr-3 text-lg"></i>
                <span class="text-green-300 font-medium"><?php echo htmlspecialchars($message); ?></span>
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
        <div class="glass rounded-xl border border-white/10 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Bookings</p>
                    <p class="text-2xl font-bold text-white"><?php echo $stats['total']; ?></p>
                </div>
                <div class="p-3 bg-blue-500/20 rounded-lg">
                    <i class="fas fa-list text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="glass rounded-xl border border-white/10 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Pending</p>
                    <p class="text-2xl font-bold text-amber-400"><?php echo $stats['pending']; ?></p>
                </div>
                <div class="p-3 bg-amber-500/20 rounded-lg">
                    <i class="fas fa-clock text-amber-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="glass rounded-xl border border-white/10 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Approved</p>
                    <p class="text-2xl font-bold text-green-400"><?php echo $stats['approved']; ?></p>
                </div>
                <div class="p-3 bg-green-500/20 rounded-lg">
                    <i class="fas fa-check-circle text-green-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="glass rounded-xl border border-white/10 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Completed</p>
                    <p class="text-2xl font-bold text-blue-400"><?php echo $stats['completed']; ?></p>
                </div>
                <div class="p-3 bg-blue-500/20 rounded-lg">
                    <i class="fas fa-flag-checkered text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings List -->
    <?php if (empty($bookings)): ?>
        <div class="glass rounded-2xl border border-white/10 p-12 text-center">
            <div class="w-24 h-24 bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-calendar-times text-4xl text-gray-400"></i>
            </div>
            <h3 class="text-2xl font-bold text-white mb-2">No Bookings Yet</h3>
            <p class="text-gray-400 mb-6">You haven't made any bike rental bookings yet.</p>
            <a href="<?php echo BASE_URL; ?>/pages/explore.php"
               class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all shadow-2xl shadow-blue-500/20 transform hover:scale-105">
                <i class="fas fa-search mr-2"></i>Explore Bikes
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($bookings as $booking):
                $statusColors = [
                    'pending' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                    'approved' => 'bg-green-500/20 text-green-400 border-green-500/30',
                    'rejected' => 'bg-red-500/20 text-red-400 border-red-500/30',
                    'completed' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                    'cancelled' => 'bg-gray-500/20 text-gray-400 border-gray-500/30'
                ];
                $statusColor = $statusColors[$booking['status']] ?? 'bg-gray-500/20 text-gray-400 border-gray-500/30';

                $statusIcons = [
                    'pending' => 'clock',
                    'approved' => 'check-circle',
                    'rejected' => 'times-circle',
                    'completed' => 'flag-checkered',
                    'cancelled' => 'ban'
                ];
                $statusIcon = $statusIcons[$booking['status']] ?? 'info-circle';
            ?>
            <div class="glass rounded-2xl border border-white/10 overflow-hidden hover:border-blue-500/30 transition-all duration-300">
                <div class="md:flex">
                    <!-- Bike Image -->
                    <div class="md:w-1/3 relative">
                        <img src="<?php echo htmlspecialchars($booking['bike_image']); ?>"
                             alt="<?php echo htmlspecialchars($booking['bike_name']); ?>"
                             class="w-full h-full object-cover min-h-[200px]">
                        <div class="absolute top-4 left-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-lg <?php echo $statusColor; ?> border text-sm font-medium">
                                <i class="fas fa-<?php echo $statusIcon; ?> mr-2"></i>
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </div>
                        <div class="absolute bottom-4 left-4">
                            <span class="inline-flex items-center px-2 py-1 rounded-md bg-slate-800/70 backdrop-blur-sm text-xs font-medium text-white border border-white/10">
                                <i class="fas fa-tag mr-1 text-blue-400"></i>
                                <?php echo htmlspecialchars($booking['category_name']); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Booking Details -->
                    <div class="md:w-2/3 p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-2xl font-bold text-white mb-1">
                                    <?php echo htmlspecialchars($booking['bike_name']); ?>
                                </h3>
                                <p class="text-sm text-gray-400">Booking ID: #<?php echo $booking['id']; ?></p>
                            </div>
                            <a href="<?php echo BASE_URL; ?>/pages/bike_details.php?id=<?php echo $booking['bike_id']; ?>"
                               class="text-blue-400 hover:text-blue-300 text-sm font-medium transition-colors">
                                <i class="fas fa-external-link-alt mr-1"></i>View Bike
                            </a>
                        </div>

                        <!-- Status Message -->
                        <?php if ($booking['status'] === 'pending'): ?>
                            <div class="glass border border-amber-500/30 p-4 mb-4">
                                <p class="text-sm text-amber-300">
                                    <i class="fas fa-hourglass-half mr-2"></i>
                                    <strong>Awaiting approval:</strong> Your booking request is being reviewed by our team.
                                </p>
                            </div>
                        <?php elseif ($booking['status'] === 'approved'): ?>
                            <div class="glass border border-green-500/30 p-4 mb-4">
                                <p class="text-sm text-green-300">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>Booking confirmed!</strong> Your bike is reserved. Please pick it up on the start date.
                                </p>
                            </div>
                        <?php elseif ($booking['status'] === 'rejected'): ?>
                            <div class="glass border border-red-500/30 p-4 mb-4">
                                <p class="text-sm text-red-300">
                                    <i class="fas fa-times-circle mr-2"></i>
                                    <strong>Booking rejected:</strong> Unfortunately, this booking couldn't be processed.
                                </p>
                            </div>
                        <?php elseif ($booking['status'] === 'completed'): ?>
                            <div class="glass border border-blue-500/30 p-4 mb-4">
                                <p class="text-sm text-blue-300">
                                    <i class="fas fa-flag-checkered mr-2"></i>
                                    <strong>Rental completed:</strong> Thank you for riding with us!
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- Payment Info -->
                        <?php if ($booking['rental_paid'] > 0 || $booking['deposit_paid'] > 0): ?>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <?php if ($booking['rental_paid'] > 0): ?>
                            <div class="glass border border-green-500/30 p-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-400">Rental Paid</span>
                                    <span class="text-sm font-medium text-green-400">₹<?php echo number_format($booking['rental_paid'], 2); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($booking['deposit_paid'] > 0): ?>
                            <div class="glass border border-blue-500/30 p-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-400">Security Deposit</span>
                                    <div class="text-right">
                                        <span class="text-sm font-medium text-blue-400">₹<?php echo number_format($booking['deposit_paid'], 2); ?></span>
                                        <?php if ($booking['deposit_status']): ?>
                                        <div class="text-xs text-gray-400"><?php echo ucfirst(str_replace('_', ' ', $booking['deposit_status'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Refund Claim Button -->
                        <?php if ($booking['status'] === 'completed' && $booking['deposit_status'] === 'held'): ?>
                        <div class="mb-4">
                            <form method="POST" class="inline">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                <button type="submit" name="claim_refund"
                                        class="bg-gradient-to-r from-green-600 to-emerald-600 text-white px-4 py-2 rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all shadow-lg shadow-green-500/20 transform hover:scale-105 text-sm font-medium">
                                    <i class="fas fa-undo-alt mr-2"></i>Claim Security Deposit Refund
                                </button>
                            </form>
                        </div>
                        <?php elseif ($booking['status'] === 'completed' && $booking['deposit_status'] === 'refund_requested'): ?>
                        <div class="mb-4">
                            <div class="glass border border-purple-500/30 p-3">
                                <p class="text-sm text-purple-300">
                                    <i class="fas fa-clock mr-2"></i>
                                    <strong>Refund requested:</strong> Your refund request is being processed. We'll notify you once it's completed.
                                </p>
                            </div>
                        </div>
                        <?php elseif ($booking['status'] === 'completed' && $booking['deposit_status'] === 'refunded'): ?>
                        <div class="mb-4">
                            <div class="glass border border-green-500/30 p-3">
                                <p class="text-sm text-green-300">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>Refund completed:</strong> Your security deposit has been refunded to your original payment method.
                                </p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Booking Info Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                            <div class="glass border border-white/10 p-3">
                                <p class="text-xs text-gray-400 mb-1">Start Date</p>
                                <p class="font-bold text-sm text-white">
                                    <?php echo date('M d', strtotime($booking['start_date'])); ?>
                                </p>
                            </div>

                            <div class="glass border border-white/10 p-3">
                                <p class="text-xs text-gray-400 mb-1">End Date</p>
                                <p class="font-bold text-sm text-white">
                                    <?php echo date('M d', strtotime($booking['end_date'])); ?>
                                </p>
                            </div>

                            <div class="glass border border-white/10 p-3">
                                <p class="text-xs text-gray-400 mb-1">Duration</p>
                                <p class="font-bold text-sm text-white">
                                    <?php echo $booking['rental_days']; ?> day<?php echo $booking['rental_days'] != 1 ? 's' : ''; ?>
                                </p>
                            </div>

                            <div class="glass border border-white/10 p-3">
                                <p class="text-xs text-gray-400 mb-1">Total</p>
                                <p class="font-bold text-sm text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400">
                                    ₹<?php echo number_format($booking['total_price'], 2); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Timestamp -->
                        <div class="text-xs text-gray-400">
                            <i class="fas fa-clock mr-1"></i>Booked on: <?php echo date('M d, Y H:i', strtotime($booking['created_at'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../components/footer.php'; ?>
