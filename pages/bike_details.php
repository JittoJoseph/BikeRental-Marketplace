<?php
$pageTitle = 'Bike Details';
require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../db_connect.php';

// Get bike ID
$bikeId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($bikeId <= 0) {
    header('Location: ' . BASE_URL . '/pages/explore.php');
    exit();
}

// Fetch bike details
$stmt = $pdo->prepare("SELECT b.*, c.name as category_name FROM bikes b 
                       JOIN categories c ON b.category_id = c.id 
                       WHERE b.id = ?");
$stmt->execute([$bikeId]);
$bike = $stmt->fetch();

if (!$bike) {
    header('Location: ' . BASE_URL . '/pages/explore.php');
    exit();
}

// Fetch reviews
$reviewsStmt = $pdo->prepare("SELECT r.*, u.name as user_name FROM reviews r 
                               JOIN users u ON r.user_id = u.id 
                               WHERE r.bike_id = ? 
                               ORDER BY r.created_at DESC");
$reviewsStmt->execute([$bikeId]);
$reviews = $reviewsStmt->fetchAll();

// Handle booking request
$bookingMessage = '';
$bookingError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_booking'])) {
    if (!isLoggedIn()) {
        $bookingError = 'Please login to book a bike';
    } else {
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        $userId = $_SESSION['user_id'];
        
        if (empty($startDate) || empty($endDate)) {
            $bookingError = 'Please select start and end dates';
        } elseif (strtotime($startDate) < strtotime('today')) {
            $bookingError = 'Start date cannot be in the past';
        } elseif (strtotime($endDate) < strtotime($startDate)) {
            $bookingError = 'End date must be after start date';
        } else {
            // Calculate total price
            $days = ceil((strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24));
            $totalPrice = $days * $bike['price'];
            
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, bike_id, start_date, end_date, total_price, status) 
                                   VALUES (?, ?, ?, ?, ?, 'pending')");
            if ($stmt->execute([$userId, $bikeId, $startDate, $endDate, $totalPrice])) {
                $bookingId = $pdo->lastInsertId();
                header('Location: ' . BASE_URL . '/pages/payment.php?booking_id=' . $bookingId);
                exit();
            } else {
                $bookingError = 'Failed to submit booking request. Please try again.';
            }
        }
    }
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        $bookingError = 'Please login to submit a review';
    } else {
        $rating = intval($_POST['rating']);
        $comment = trim($_POST['comment']);
        $userId = $_SESSION['user_id'];
        
        if ($rating < 1 || $rating > 5) {
            $bookingError = 'Invalid rating';
        } else {
            $stmt = $pdo->prepare("INSERT INTO reviews (user_id, bike_id, rating, comment) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$userId, $bikeId, $rating, $comment])) {
                // Update bike average rating
                $avgStmt = $pdo->prepare("UPDATE bikes SET rating = (SELECT AVG(rating) FROM reviews WHERE bike_id = ?) WHERE id = ?");
                $avgStmt->execute([$bikeId, $bikeId]);
                
                $bookingMessage = 'Review submitted successfully!';
                header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $bikeId);
                exit();
            } else {
                $bookingError = 'Failed to submit review. Please try again.';
            }
        }
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-gray-400">
            <li><a href="<?php echo BASE_URL; ?>/index.php" class="hover:text-blue-400 transition-colors">Home</a></li>
            <li><i class="fas fa-chevron-right text-xs"></i></li>
            <li><a href="<?php echo BASE_URL; ?>/pages/explore.php" class="hover:text-blue-400 transition-colors">Explore</a></li>
            <li><i class="fas fa-chevron-right text-xs"></i></li>
            <li class="text-white font-semibold"><?php echo htmlspecialchars($bike['name']); ?></li>
        </ol>
    </nav>

    <!-- Messages -->
    <?php if ($bookingMessage): ?>
        <div class="glass border border-green-500/30 px-4 py-3 rounded-xl mb-6">
            <div class="flex items-center text-green-300">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($bookingMessage); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($bookingError): ?>
        <div class="glass border border-red-500/30 px-4 py-3 rounded-xl mb-6">
            <div class="flex items-center text-red-300">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($bookingError); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        <!-- Left: Image -->
        <div>
            <div class="glass rounded-xl overflow-hidden border border-white/10">
                <img src="<?php echo htmlspecialchars($bike['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($bike['name']); ?>"
                     class="w-full h-96 object-cover">
            </div>
        </div>

        <!-- Right: Details and Booking -->
        <div>
            <div class="glass rounded-xl border border-white/10 p-8">
                <span class="text-blue-400 text-sm font-semibold"><?php echo htmlspecialchars($bike['category_name']); ?></span>
                <h1 class="text-3xl font-bold text-white mt-2 mb-4"><?php echo htmlspecialchars($bike['name']); ?></h1>
                
                <div class="flex items-center mb-6">
                    <div class="flex items-center text-yellow-400 mr-4">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star<?php echo $i <= $bike['rating'] ? '' : '-half-alt'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="text-gray-400"><?php echo number_format($bike['rating'], 1); ?> (<?php echo count($reviews); ?> reviews)</span>
                </div>

                <div class="mb-6">
                    <div class="flex items-baseline">
                        <span class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400">₹<?php echo number_format($bike['price'], 2); ?></span>
                        <span class="text-gray-400 text-lg ml-2">/day</span>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-white mb-2">Description</h3>
                    <p class="text-gray-400"><?php echo nl2br(htmlspecialchars($bike['description'])); ?></p>
                </div>

                <!-- Booking Form -->
                <div class="border-t border-white/10 pt-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Book This Bike</h3>
                    <form method="POST">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Start Date</label>
                                <input type="date" name="start_date" required
                                       min="<?php echo date('Y-m-d'); ?>"
                                       class="w-full px-4 py-2 glass border border-white/10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">End Date</label>
                                <input type="date" name="end_date" required
                                       min="<?php echo date('Y-m-d'); ?>"
                                       class="w-full px-4 py-2 glass border border-white/10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400">
                            </div>
                        </div>
                        
                        <?php if (isLoggedIn()): ?>
                            <button type="submit" name="request_booking" 
                                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 rounded-lg hover:from-blue-700 hover:to-purple-700 transition font-semibold shadow-lg shadow-blue-500/20">
                                <i class="fas fa-calendar-check mr-2"></i>Request Booking
                            </button>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>/pages/login.php" 
                               class="block w-full glass border border-white/20 text-white py-3 rounded-lg hover:bg-white/10 transition font-semibold text-center">
                                <i class="fas fa-sign-in-alt mr-2"></i>Login to Book
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="glass rounded-xl border border-white/10 p-8">
        <h2 class="text-2xl font-bold text-white mb-6">Reviews & Ratings</h2>

        <!-- Submit Review Form -->
        <?php if (isLoggedIn()): ?>
        <div class="mb-8 p-6 glass rounded-lg border border-white/10">
            <h3 class="text-lg font-semibold text-white mb-4">Write a Review</h3>
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Rating</label>
                    <div class="flex space-x-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="rating" value="<?php echo $i; ?>" required class="hidden peer">
                                <i class="far fa-star text-2xl text-gray-400 peer-checked:fas peer-checked:text-yellow-500"></i>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Comment</label>
                    <textarea name="comment" rows="4" 
                              class="w-full px-4 py-2 glass border border-white/10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400"
                              placeholder="Share your experience..."></textarea>
                </div>
                
                <button type="submit" name="submit_review" 
                        class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-2 rounded-lg hover:from-blue-700 hover:to-purple-700 transition shadow-lg shadow-blue-500/20">
                    Submit Review
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Reviews List -->
        <?php if (count($reviews) > 0): ?>
            <div class="space-y-6">
                <?php foreach ($reviews as $review): ?>
                <div class="border-b border-white/10 pb-6">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <h4 class="font-semibold text-white"><?php echo htmlspecialchars($review['user_name']); ?></h4>
                            <div class="flex items-center text-yellow-400 text-sm">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <span class="text-sm text-gray-400"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                    </div>
                    <?php if ($review['comment']): ?>
                        <p class="text-gray-400"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-400 text-center py-8">No reviews yet. Be the first to review this bike!</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../components/footer.php'; ?>
