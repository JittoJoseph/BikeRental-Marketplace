<?php
// Load config and database FIRST (before any HTML output)
require_once __DIR__ . '/../config.php';
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

// Handle booking request BEFORE any HTML output
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
                // Redirect BEFORE any HTML output
                header('Location: ' . BASE_URL . '/pages/payment.php?booking_id=' . $bookingId);
                exit();
            } else {
                $bookingError = 'Failed to submit booking request. Please try again.';
            }
        }
    }
}

// Handle review submission BEFORE any HTML output
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
                // Redirect BEFORE any HTML output
                header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $bikeId);
                exit();
            } else {
                $bookingError = 'Failed to submit review. Please try again.';
            }
        }
    }
}

// Fetch reviews
$reviewsStmt = $pdo->prepare("SELECT r.*, u.name as user_name FROM reviews r
                               JOIN users u ON r.user_id = u.id
                               WHERE r.bike_id = ?
                               ORDER BY r.created_at DESC");
$reviewsStmt->execute([$bikeId]);
$reviews = $reviewsStmt->fetchAll();

// NOW include header (which outputs HTML)
$pageTitle = 'Bike Details';
require_once __DIR__ . '/../components/header.php';
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
        <div class="glass border border-green-500/30 px-6 py-4 rounded-xl mb-8">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-400 mr-3 text-lg"></i>
                <span class="text-green-300 font-medium"><?php echo htmlspecialchars($bookingMessage); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($bookingError): ?>
        <div class="glass border border-red-500/30 px-6 py-4 rounded-xl mb-8">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-400 mr-3 text-lg"></i>
                <span class="text-red-300 font-medium"><?php echo htmlspecialchars($bookingError); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Left: Bike Image -->
        <div class="space-y-6">
            <div class="relative overflow-hidden rounded-2xl shadow-2xl shadow-blue-500/20">
                <img src="<?php echo htmlspecialchars($bike['image_url']); ?>"
                     alt="<?php echo htmlspecialchars($bike['name']); ?>"
                     class="w-full h-96 lg:h-[500px] object-cover">

                <!-- Rating Badge -->
                <div class="absolute top-4 left-4 glass px-4 py-2 rounded-xl border border-white/20">
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center text-yellow-400">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?php echo $i <= $bike['rating'] ? '' : '-half-alt'; ?> text-sm"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="text-white font-semibold"><?php echo number_format($bike['rating'], 1); ?></span>
                        <span class="text-gray-300 text-sm">(<?php echo count($reviews); ?>)</span>
                    </div>
                </div>

                <!-- Category Badge -->
                <div class="absolute top-4 right-4 glass px-4 py-2 rounded-xl border border-white/20">
                    <span class="text-blue-400 font-semibold"><?php echo htmlspecialchars($bike['category_name']); ?></span>
                </div>

                <!-- Price Badge -->
                <div class="absolute bottom-4 left-4 glass px-6 py-3 rounded-xl border border-white/20">
                    <div class="text-center">
                        <p class="text-gray-400 text-sm">Starting from</p>
                        <p class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400">
                            ₹<?php echo number_format($bike['price'], 0); ?>
                        </p>
                        <p class="text-gray-400 text-sm">per day</p>
                    </div>
                </div>
            </div>

            <!-- Key Features -->
            <div class="grid grid-cols-3 gap-4">
                <div class="glass rounded-xl p-4 border border-white/10 text-center hover:border-green-500/50 transition-all duration-300 group">
                    <div class="p-3 bg-green-500/20 rounded-lg inline-flex items-center justify-center mb-2 group-hover:bg-green-500/30 transition-colors">
                        <i class="fas fa-shield-alt text-green-400 text-lg"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-300">Fully Insured</p>
                </div>
                <div class="glass rounded-xl p-4 border border-white/10 text-center hover:border-blue-500/50 transition-all duration-300 group">
                    <div class="p-3 bg-blue-500/20 rounded-lg inline-flex items-center justify-center mb-2 group-hover:bg-blue-500/30 transition-colors">
                        <i class="fas fa-tools text-blue-400 text-lg"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-300">Well Maintained</p>
                </div>
                <div class="glass rounded-xl p-4 border border-white/10 text-center hover:border-purple-500/50 transition-all duration-300 group">
                    <div class="p-3 bg-purple-500/20 rounded-lg inline-flex items-center justify-center mb-2 group-hover:bg-purple-500/30 transition-colors">
                        <i class="fas fa-headset text-purple-400 text-lg"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-300">24/7 Support</p>
                </div>
            </div>
        </div>

        <!-- Right: Bike Details & Booking -->
        <div class="space-y-8">
            <!-- Bike Title -->
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-lg bg-blue-500/20 text-blue-400 text-sm font-medium mb-4">
                    <?php echo htmlspecialchars($bike['category_name']); ?>
                </span>
                <h1 class="text-4xl font-bold text-white mb-4"><?php echo htmlspecialchars($bike['name']); ?></h1>
                <p class="text-xl text-gray-400 leading-relaxed">
                    Experience premium bike rental with top-quality service and maintenance
                </p>
            </div>

            <!-- Rating & Reviews -->
            <div class="flex items-center space-x-4">
                <div class="flex items-center text-yellow-400">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star<?php echo $i <= $bike['rating'] ? '' : '-half-alt'; ?>"></i>
                    <?php endfor; ?>
                </div>
                <span class="text-white font-semibold"><?php echo number_format($bike['rating'], 1); ?></span>
                <span class="text-gray-400">(<?php echo count($reviews); ?> reviews)</span>
            </div>

            <!-- Description -->
            <div class="glass rounded-xl p-6 border border-white/10">
                <h3 class="text-lg font-semibold text-white mb-3">About This Bike</h3>
                <p class="text-gray-400 leading-relaxed"><?php echo nl2br(htmlspecialchars($bike['description'])); ?></p>
            </div>

            <!-- Booking Form -->
            <div class="glass rounded-xl p-8 border border-white/10">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-white">Book This Bike</h3>
                    <div class="text-right">
                        <p class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400">
                            ₹<?php echo number_format($bike['price'], 0); ?>
                        </p>
                        <p class="text-gray-400 text-sm">per day</p>
                    </div>
                </div>

                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-300">Pickup Date</label>
                            <div class="relative">
                                <input type="date" name="start_date" required
                                       min="<?php echo date('Y-m-d'); ?>"
                                       class="w-full px-4 py-3 glass border border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 bg-slate-800/50">
                                <i class="fas fa-calendar-alt absolute right-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-300">Return Date</label>
                            <div class="relative">
                                <input type="date" name="end_date" required
                                       min="<?php echo date('Y-m-d'); ?>"
                                       class="w-full px-4 py-3 glass border border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 bg-slate-800/50">
                                <i class="fas fa-calendar-alt absolute right-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Price Calculator -->
                    <div id="price-calculator" class="hidden glass rounded-xl p-4 border border-blue-500/30 bg-blue-500/10">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-300">Rental Period:</span>
                            <span id="rental-days" class="text-white font-semibold">0 days</span>
                        </div>
                        <div class="flex items-center justify-between text-sm mt-2">
                            <span class="text-gray-300">Total Amount:</span>
                            <span id="total-amount" class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400">₹0</span>
                        </div>
                    </div>

                    <?php if (isLoggedIn()): ?>
                        <button type="submit" name="request_booking"
                                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-300 font-bold text-lg shadow-2xl shadow-blue-500/20 hover:shadow-blue-500/40 transform hover:scale-105">
                            <i class="fas fa-rocket mr-2"></i>Book Now - Instant Confirmation
                        </button>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/pages/login.php"
                           class="block w-full glass border-2 border-white/20 text-white py-4 rounded-xl hover:bg-white/10 transition-all duration-300 font-bold text-lg text-center shadow-lg">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login to Book This Bike
                        </a>
                    <?php endif; ?>

                    <div class="flex items-center justify-center space-x-6 text-sm text-gray-400">
                        <div class="flex items-center">
                            <i class="fas fa-shield-alt text-green-400 mr-2"></i>
                            <span>Secure Payment</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-undo-alt text-blue-400 mr-2"></i>
                            <span>Free Cancellation</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-headset text-purple-400 mr-2"></i>
                            <span>24/7 Support</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="mt-16">
        <div class="glass rounded-2xl border border-white/10 p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-2">Reviews & Ratings</h2>
                    <p class="text-gray-400">See what other riders have to say</p>
                </div>
                <?php if (isLoggedIn()): ?>
                <button onclick="document.getElementById('review-form').scrollIntoView({behavior: 'smooth'})"
                        class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white px-6 py-3 rounded-xl hover:from-yellow-600 hover:to-orange-600 transition-all shadow-lg shadow-yellow-500/20 transform hover:scale-105">
                    <i class="fas fa-star mr-2"></i>Write Review
                </button>
                <?php endif; ?>
            </div>

            <!-- Rating Summary -->
            <?php if (count($reviews) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <div class="text-center">
                    <div class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-500 mb-2">
                        <?php echo number_format($bike['rating'], 1); ?>
                    </div>
                    <div class="flex items-center justify-center mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star<?php echo $i <= $bike['rating'] ? '' : '-half-alt'; ?> text-yellow-400"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-gray-400"><?php echo count($reviews); ?> reviews</p>
                </div>

                <div class="md:col-span-2">
                    <div class="space-y-2">
                        <?php
                        $ratingCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
                        foreach ($reviews as $review) {
                            $ratingCounts[$review['rating']]++;
                        }
                        for ($i = 5; $i >= 1; $i--):
                            $percentage = count($reviews) > 0 ? ($ratingCounts[$i] / count($reviews)) * 100 : 0;
                        ?>
                        <div class="flex items-center space-x-3">
                            <span class="text-sm text-gray-400 w-6"><?php echo $i; ?>★</span>
                            <div class="flex-1 bg-slate-700 rounded-full h-2">
                                <div class="bg-gradient-to-r from-yellow-400 to-orange-500 h-2 rounded-full transition-all duration-500"
                                     style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <span class="text-sm text-gray-400 w-8"><?php echo $ratingCounts[$i]; ?></span>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Reviews List -->
            <?php if (count($reviews) > 0): ?>
                <div class="space-y-6 mb-8">
                    <?php foreach ($reviews as $review): ?>
                    <div class="border-b border-white/10 pb-6 last:border-b-0">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">
                                        <?php echo strtoupper(substr($review['user_name'], 0, 1)); ?>
                                    </span>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-white"><?php echo htmlspecialchars($review['user_name']); ?></h4>
                                    <div class="flex items-center space-x-2">
                                        <div class="flex items-center text-yellow-400 text-sm">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="text-gray-400 text-sm">• <?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if ($review['comment']): ?>
                            <p class="text-gray-400 leading-relaxed"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-comments text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">No Reviews Yet</h3>
                    <p class="text-gray-400 mb-6">Be the first to share your experience with this bike!</p>
                </div>
            <?php endif; ?>

            <!-- Write Review Form -->
            <?php if (isLoggedIn()): ?>
            <div id="review-form" class="border-t border-white/10 pt-8 mt-8">
                <h3 class="text-2xl font-bold text-white mb-6">Write a Review</h3>
                <form method="POST" class="max-w-2xl">
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-300 mb-4">Rate Your Experience</label>
                        <div class="flex space-x-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="rating" value="<?php echo $i; ?>" required class="hidden peer">
                                    <div class="w-12 h-12 bg-slate-800/50 rounded-xl flex items-center justify-center border-2 border-transparent peer-checked:border-yellow-400 peer-checked:bg-yellow-400/20 transition-all duration-300 group-hover:scale-110">
                                        <i class="far fa-star text-2xl text-gray-400 peer-checked:fas peer-checked:text-yellow-500"></i>
                                    </div>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-300 mb-3">Share Your Thoughts</label>
                        <textarea name="comment" rows="4"
                                  class="w-full px-4 py-3 glass border border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 bg-slate-800/50 resize-none"
                                  placeholder="Tell others about your riding experience..."></textarea>
                    </div>

                    <button type="submit" name="submit_review"
                            class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-3 rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-300 font-bold shadow-2xl shadow-blue-500/20 hover:shadow-blue-500/40 transform hover:scale-105">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Review
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Price calculator functionality
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.querySelector('input[name="start_date"]');
    const endDateInput = document.querySelector('input[name="end_date"]');
    const priceCalculator = document.getElementById('price-calculator');
    const rentalDaysSpan = document.getElementById('rental-days');
    const totalAmountSpan = document.getElementById('total-amount');
    const dailyRate = <?php echo $bike['price']; ?>;

    function calculatePrice() {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);

        if (startDate && endDate && startDate < endDate) {
            const timeDiff = endDate.getTime() - startDate.getTime();
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

            rentalDaysSpan.textContent = daysDiff + ' days';
            totalAmountSpan.textContent = '₹' + (daysDiff * dailyRate).toLocaleString();
            priceCalculator.classList.remove('hidden');
        } else {
            priceCalculator.classList.add('hidden');
        }
    }

    startDateInput.addEventListener('change', calculatePrice);
    endDateInput.addEventListener('change', calculatePrice);
});
</script>
