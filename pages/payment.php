<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db_connect.php';
requireLogin();

$pageTitle = 'Complete Payment';

// Get booking ID from URL
$bookingId = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if ($bookingId <= 0) {
    header('Location: ' . BASE_URL . '/pages/explore.php');
    exit();
}

// Fetch booking details
$stmt = $pdo->prepare("
    SELECT b.*, bk.name as bike_name, bk.image_url as bike_image, bk.price as bike_price,
           u.name as user_name, u.email as user_email,
           DATEDIFF(b.end_date, b.start_date) as rental_days
    FROM bookings b
    JOIN bikes bk ON b.bike_id = bk.id
    JOIN users u ON b.user_id = u.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$bookingId, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: ' . BASE_URL . '/pages/my_bookings.php');
    exit();
}

// Check if payment already exists
$paymentStmt = $pdo->prepare("SELECT * FROM payments WHERE booking_id = ? AND status = 'completed'");
$paymentStmt->execute([$bookingId]);
$existingPayment = $paymentStmt->fetch();

if ($existingPayment) {
    header('Location: ' . BASE_URL . '/pages/my_bookings.php');
    exit();
}

// Calculate amounts
$rentalAmount = $booking['total_price'];
$securityDeposit = 500.00; // Fixed security deposit
$totalAmount = $rentalAmount + $securityDeposit;

// Handle payment submission
$paymentMessage = '';
$paymentError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $paymentMethod = $_POST['payment_method'];

    // Validate payment method
    $validMethods = ['card', 'upi', 'net_banking', 'wallet'];
    if (!in_array($paymentMethod, $validMethods)) {
        $paymentError = 'Invalid payment method selected.';
    } else {
        // Generate transaction ID
        $transactionId = 'TXN' . time() . rand(1000, 9999);

        try {
            $pdo->beginTransaction();

            // Insert rental payment
            $paymentStmt = $pdo->prepare("
                INSERT INTO payments (booking_id, user_id, amount, payment_type, payment_method, transaction_id, status)
                VALUES (?, ?, ?, 'booking', ?, ?, 'completed')
            ");
            $paymentStmt->execute([$bookingId, $_SESSION['user_id'], $rentalAmount, $paymentMethod, $transactionId]);

            // Insert security deposit
            $depositStmt = $pdo->prepare("
                INSERT INTO payments (booking_id, user_id, amount, payment_type, payment_method, transaction_id, status)
                VALUES (?, ?, ?, 'security_deposit', ?, ?, 'completed')
            ");
            $depositStmt->execute([$bookingId, $_SESSION['user_id'], $securityDeposit, $paymentMethod, $transactionId . '_SD']);

            // Create security deposit record
            $securityStmt = $pdo->prepare("
                INSERT INTO security_deposits (booking_id, user_id, amount, status)
                VALUES (?, ?, ?, 'held')
            ");
            $securityStmt->execute([$bookingId, $_SESSION['user_id'], $securityDeposit]);

            // Update booking status to approved (assuming payment success means approval)
            $updateStmt = $pdo->prepare("UPDATE bookings SET status = 'approved' WHERE id = ?");
            $updateStmt->execute([$bookingId]);

            $pdo->commit();

            $paymentMessage = 'Payment processed successfully! Your booking is now confirmed.';

            // Redirect after 3 seconds
            header('refresh:3;url=' . BASE_URL . '/pages/my_bookings.php');

        } catch (Exception $e) {
            $pdo->rollBack();
            $paymentError = 'Payment failed. Please try again.';
        }
    }
}

require_once __DIR__ . '/../components/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-gray-400">
            <li><a href="<?php echo BASE_URL; ?>/pages/my_bookings.php" class="hover:text-blue-400 transition-colors">My Bookings</a></li>
            <li><i class="fas fa-chevron-right text-xs"></i></li>
            <li class="text-white font-semibold">Complete Payment</li>
        </ol>
    </nav>

    <!-- Messages -->
    <?php if ($paymentMessage): ?>
        <div class="glass border border-green-500/30 px-6 py-4 rounded-xl mb-8">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-400 mr-3 text-lg"></i>
                <span class="text-green-300 font-medium"><?php echo htmlspecialchars($paymentMessage); ?></span>
            </div>
            <p class="text-gray-400 text-sm mt-2">Redirecting to your bookings...</p>
        </div>
    <?php endif; ?>

    <?php if ($paymentError): ?>
        <div class="glass border border-red-500/30 px-6 py-4 rounded-xl mb-8">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-400 mr-3 text-lg"></i>
                <span class="text-red-300 font-medium"><?php echo htmlspecialchars($paymentError); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$paymentMessage): ?>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left: Booking Summary -->
        <div class="space-y-6">
            <div class="glass rounded-2xl border border-white/10 p-6">
                <h2 class="text-2xl font-bold text-white mb-6">Booking Summary</h2>

                <!-- Bike Info -->
                <div class="flex items-center space-x-4 mb-6">
                    <img src="<?php echo htmlspecialchars($booking['bike_image']); ?>"
                         alt="<?php echo htmlspecialchars($booking['bike_name']); ?>"
                         class="w-20 h-20 rounded-xl object-cover border border-white/10">
                    <div>
                        <h3 class="text-lg font-semibold text-white"><?php echo htmlspecialchars($booking['bike_name']); ?></h3>
                        <p class="text-gray-400">Booking #<?php echo $booking['id']; ?></p>
                    </div>
                </div>

                <!-- Booking Details -->
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Start Date:</span>
                        <span class="text-white"><?php echo date('M d, Y', strtotime($booking['start_date'])); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">End Date:</span>
                        <span class="text-white"><?php echo date('M d, Y', strtotime($booking['end_date'])); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Duration:</span>
                        <span class="text-white"><?php echo $booking['rental_days']; ?> days</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Daily Rate:</span>
                        <span class="text-white">₹<?php echo number_format($booking['bike_price'], 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Security Deposit Info -->
            <div class="glass rounded-2xl border border-amber-500/30 p-6">
                <div class="flex items-start space-x-3">
                    <div class="p-2 bg-amber-500/20 rounded-lg">
                        <i class="fas fa-shield-alt text-amber-400 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-2">Security Deposit</h3>
                        <p class="text-gray-400 text-sm mb-3">
                            A refundable security deposit of ₹500 is required to protect against damages or violations.
                        </p>
                        <div class="space-y-1 text-sm text-gray-400">
                            <p>• Fully refundable upon return of bike in good condition</p>
                            <p>• Processing time: 3-5 business days after return</p>
                            <p>• Refunded to original payment method</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Payment Form -->
        <div class="glass rounded-2xl border border-white/10 p-6">
            <h2 class="text-2xl font-bold text-white mb-6">Payment Details</h2>

            <!-- Amount Breakdown -->
            <div class="space-y-4 mb-8">
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Bike Rental (<?php echo $booking['rental_days']; ?> days)</span>
                    <span class="text-white font-semibold">₹<?php echo number_format($rentalAmount, 2); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Security Deposit</span>
                    <span class="text-white font-semibold">₹<?php echo number_format($securityDeposit, 2); ?></span>
                </div>
                <div class="border-t border-white/10 pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-white">Total Amount</span>
                        <span class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400">
                            ₹<?php echo number_format($totalAmount, 2); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Payment Method Selection -->
            <form method="POST">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-300 mb-4">Select Payment Method</label>
                    <div class="space-y-3">
                        <label class="flex items-center p-4 glass border border-white/10 rounded-xl cursor-pointer hover:border-blue-500/50 transition-all">
                            <input type="radio" name="payment_method" value="card" required class="text-blue-500 focus:ring-blue-500">
                            <div class="ml-3 flex items-center">
                                <i class="fas fa-credit-card text-blue-400 mr-3"></i>
                                <div>
                                    <div class="text-white font-medium">Credit/Debit Card</div>
                                    <div class="text-gray-400 text-sm">Visa, MasterCard, RuPay</div>
                                </div>
                            </div>
                        </label>

                        <label class="flex items-center p-4 glass border border-white/10 rounded-xl cursor-pointer hover:border-purple-500/50 transition-all">
                            <input type="radio" name="payment_method" value="upi" class="text-purple-500 focus:ring-purple-500">
                            <div class="ml-3 flex items-center">
                                <i class="fas fa-mobile-alt text-purple-400 mr-3"></i>
                                <div>
                                    <div class="text-white font-medium">UPI Payment</div>
                                    <div class="text-gray-400 text-sm">Google Pay, PhonePe, Paytm</div>
                                </div>
                            </div>
                        </label>

                        <label class="flex items-center p-4 glass border border-white/10 rounded-xl cursor-pointer hover:border-green-500/50 transition-all">
                            <input type="radio" name="payment_method" value="net_banking" class="text-green-500 focus:ring-green-500">
                            <div class="ml-3 flex items-center">
                                <i class="fas fa-university text-green-400 mr-3"></i>
                                <div>
                                    <div class="text-white font-medium">Net Banking</div>
                                    <div class="text-gray-400 text-sm">All major banks supported</div>
                                </div>
                            </div>
                        </label>

                        <label class="flex items-center p-4 glass border border-white/10 rounded-xl cursor-pointer hover:border-pink-500/50 transition-all">
                            <input type="radio" name="payment_method" value="wallet" class="text-pink-500 focus:ring-pink-500">
                            <div class="ml-3 flex items-center">
                                <i class="fas fa-wallet text-pink-400 mr-3"></i>
                                <div>
                                    <div class="text-white font-medium">Digital Wallet</div>
                                    <div class="text-gray-400 text-sm">Paytm, Mobikwik, Ola Money</div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Payment Button -->
                <button type="submit" name="process_payment"
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 rounded-xl font-bold text-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-300 shadow-2xl shadow-blue-500/20 hover:shadow-blue-500/40 transform hover:scale-105">
                    <i class="fas fa-lock mr-2"></i>Pay ₹<?php echo number_format($totalAmount, 2); ?> Securely
                </button>

                <p class="text-center text-gray-400 text-sm mt-4">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Your payment is secured with 256-bit SSL encryption
                </p>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../components/footer.php'; ?>