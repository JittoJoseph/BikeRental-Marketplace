<?php
require_once __DIR__ . '/../config.php';
requireLogin();
requireAdmin();

$pageTitle = 'Security Deposit Management';
// Get statistics
$stats = [
    'total_held' => 0,
    'total_refunded' => 0,
    'total_forfeited' => 0,
    'pending_returns' => 0,
    'refund_requests' => 0
];

foreach ($deposits as $deposit) {
    $stats['total_held'] += $deposit['amount'];

    if ($deposit['status'] === 'refunded') {
        $stats['total_refunded'] += $deposit['amount'];
    } elseif ($deposit['status'] === 'forfeited') {
        $stats['total_forfeited'] += $deposit['amount'];
    } elseif ($deposit['status'] === 'refund_requested') {
        $stats['refund_requests']++;
    } elseif ($deposit['status'] === 'held' && $deposit['booking_status'] === 'completed') {
        $stats['pending_returns']++;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $depositId = intval($_POST['deposit_id']);
    $action = $_POST['action'];
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

    if ($action === 'refund') {
        try {
            $pdo->beginTransaction();

            // Update security deposit status
            $stmt = $pdo->prepare("
                UPDATE security_deposits
                SET status = 'refunded', refund_date = NOW(), refund_reason = ?
                WHERE id = ?
            ");
            $stmt->execute([$reason, $depositId]);

            // Create refund payment record
            $depositStmt = $pdo->prepare("
                SELECT sd.*, b.user_id, b.total_price
                FROM security_deposits sd
                JOIN bookings b ON sd.booking_id = b.id
                WHERE sd.id = ?
            ");
            $depositStmt->execute([$depositId]);
            $deposit = $depositStmt->fetch();

            if ($deposit) {
                $refundStmt = $pdo->prepare("
                    INSERT INTO payments (booking_id, user_id, amount, payment_type, payment_method, transaction_id, status)
                    VALUES (?, ?, ?, 'refund', 'original', ?, 'completed')
                ");
                $refundStmt->execute([
                    $deposit['booking_id'],
                    $deposit['user_id'],
                    $deposit['amount'],
                    'REFUND' . time() . rand(1000, 9999)
                ]);
            }

            $pdo->commit();
            $message = 'Security deposit refunded successfully.';

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to process refund.';
        }

    } elseif ($action === 'forfeit') {
        try {
            $stmt = $pdo->prepare("
                UPDATE security_deposits
                SET status = 'forfeited', refund_date = NOW(), refund_reason = ?
                WHERE id = ?
            ");
            $stmt->execute([$reason, $depositId]);
            $message = 'Security deposit forfeited successfully.';

        } catch (Exception $e) {
            $error = 'Failed to forfeit deposit.';
        }

    } elseif ($action === 'process_refund_request') {
        try {
            $pdo->beginTransaction();

            // Update security deposit status to refunded
            $stmt = $pdo->prepare("
                UPDATE security_deposits
                SET status = 'refunded', refund_date = NOW(), refund_reason = 'User refund request processed'
                WHERE id = ?
            ");
            $stmt->execute([$depositId]);

            // Create refund payment record
            $depositStmt = $pdo->prepare("
                SELECT sd.*, b.user_id
                FROM security_deposits sd
                JOIN bookings b ON sd.booking_id = b.id
                WHERE sd.id = ?
            ");
            $depositStmt->execute([$depositId]);
            $deposit = $depositStmt->fetch();

            if ($deposit) {
                $refundStmt = $pdo->prepare("
                    INSERT INTO payments (booking_id, user_id, amount, payment_type, payment_method, transaction_id, status)
                    VALUES (?, ?, ?, 'refund', 'original', ?, 'completed')
                ");
                $refundStmt->execute([
                    $deposit['booking_id'],
                    $deposit['user_id'],
                    $deposit['amount'],
                    'REFUND' . time() . rand(1000, 9999)
                ]);
            }

            $pdo->commit();
            $message = 'Refund request processed successfully.';

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to process refund request.';
        }
    }
}

// Fetch all security deposits with booking details
$stmt = $pdo->prepare("
    SELECT sd.*, b.id as booking_id, b.start_date, b.end_date, b.status as booking_status,
           bk.name as bike_name, u.name as user_name, u.email as user_email
    FROM security_deposits sd
    JOIN bookings b ON sd.booking_id = b.id
    JOIN bikes bk ON b.bike_id = bk.id
    JOIN users u ON b.user_id = u.id
    ORDER BY sd.created_at DESC
");
$stmt->execute();
$deposits = $stmt->fetchAll();

// Statistics
$stats = [
    'total_held' => 0,
    'total_refunded' => 0,
    'total_forfeited' => 0,
    'pending_returns' => 0
];

foreach ($deposits as $deposit) {
    $stats['total_held'] += $deposit['amount'];

    if ($deposit['status'] === 'refunded') {
        $stats['total_refunded'] += $deposit['amount'];
    } elseif ($deposit['status'] === 'forfeited') {
        $stats['total_forfeited'] += $deposit['amount'];
    } elseif ($deposit['status'] === 'held' && $deposit['booking_status'] === 'completed') {
        $stats['pending_returns']++;
    }
}

require_once __DIR__ . '/../components/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="glass rounded-2xl border border-white/10 p-8 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Security Deposit Management</h1>
                <p class="text-gray-400">Manage refunds and forfeitures for completed bookings</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400">
                    ₹<?php echo number_format($stats['total_held'], 2); ?>
                </div>
                <div class="text-sm text-gray-400">Total Deposits Held</div>
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
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div class="glass rounded-xl border border-white/10 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Held</p>
                    <p class="text-2xl font-bold text-white">₹<?php echo number_format($stats['total_held'], 2); ?></p>
                </div>
                <div class="p-3 bg-blue-500/20 rounded-lg">
                    <i class="fas fa-shield-alt text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="glass rounded-xl border border-white/10 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Refunded</p>
                    <p class="text-2xl font-bold text-green-400">₹<?php echo number_format($stats['total_refunded'], 2); ?></p>
                </div>
                <div class="p-3 bg-green-500/20 rounded-lg">
                    <i class="fas fa-undo-alt text-green-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="glass rounded-xl border border-white/10 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Forfeited</p>
                    <p class="text-2xl font-bold text-red-400">₹<?php echo number_format($stats['total_forfeited'], 2); ?></p>
                </div>
                <div class="p-3 bg-red-500/20 rounded-lg">
                    <i class="fas fa-times-circle text-red-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="glass rounded-xl border border-white/10 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Refund Requests</p>
                    <p class="text-2xl font-bold text-purple-400"><?php echo $stats['refund_requests']; ?></p>
                </div>
                <div class="p-3 bg-purple-500/20 rounded-lg">
                    <i class="fas fa-clock text-purple-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="glass rounded-xl border border-white/10 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Pending Returns</p>
                    <p class="text-2xl font-bold text-amber-400"><?php echo $stats['pending_returns']; ?></p>
                </div>
                <div class="p-3 bg-amber-500/20 rounded-lg">
                    <i class="fas fa-clock text-amber-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Deposits Table -->
    <div class="glass rounded-2xl border border-white/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-white/10">
            <h2 class="text-xl font-bold text-white">All Security Deposits</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Booking</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Bike</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Booking Period</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <?php foreach ($deposits as $deposit): ?>
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-white">#<?php echo $deposit['booking_id']; ?></div>
                            <div class="text-sm text-gray-400"><?php echo ucfirst($deposit['booking_status']); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-white"><?php echo htmlspecialchars($deposit['user_name']); ?></div>
                            <div class="text-sm text-gray-400"><?php echo htmlspecialchars($deposit['user_email']); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-white"><?php echo htmlspecialchars($deposit['bike_name']); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-white">₹<?php echo number_format($deposit['amount'], 2); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                            $statusClasses = [
                                'held' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                                'refunded' => 'bg-green-500/20 text-green-400 border-green-500/30',
                                'forfeited' => 'bg-red-500/20 text-red-400 border-red-500/30',
                                'refund_requested' => 'bg-purple-500/20 text-purple-400 border-purple-500/30'
                            ];
                            $statusClass = $statusClasses[$deposit['status']] ?? 'bg-gray-500/20 text-gray-400 border-gray-500/30';
                            ?>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full border <?php echo $statusClass; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $deposit['status'])); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-white">
                                <?php echo date('M d', strtotime($deposit['start_date'])); ?> - <?php echo date('M d, Y', strtotime($deposit['end_date'])); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($deposit['status'] === 'refund_requested'): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="deposit_id" value="<?php echo $deposit['id']; ?>">
                                    <input type="hidden" name="action" value="process_refund_request">
                                    <button type="submit" onclick="return confirm('Process this refund request?')"
                                            class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                                        <i class="fas fa-check mr-1"></i>Process Refund
                                    </button>
                                </form>
                            <?php elseif ($deposit['status'] === 'held' && $deposit['booking_status'] === 'completed'): ?>
                                <div class="flex space-x-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="deposit_id" value="<?php echo $deposit['id']; ?>">
                                        <input type="hidden" name="action" value="refund">
                                        <button type="submit" onclick="return confirm('Are you sure you want to refund this security deposit?')"
                                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                                            <i class="fas fa-undo-alt mr-1"></i>Refund
                                        </button>
                                    </form>
                                    <button onclick="openForfeitModal(<?php echo $deposit['id']; ?>, '<?php echo htmlspecialchars($deposit['user_name']); ?>')"
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                                        <i class="fas fa-times-circle mr-1"></i>Forfeit
                                    </button>
                                </div>
                            <?php elseif ($deposit['status'] === 'refunded'): ?>
                                <div class="text-green-400 text-sm">
                                    <i class="fas fa-check-circle mr-1"></i>Refunded
                                    <?php if ($deposit['refund_date']): ?>
                                        <br><span class="text-gray-400"><?php echo date('M d, Y', strtotime($deposit['refund_date'])); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($deposit['status'] === 'forfeited'): ?>
                                <div class="text-red-400 text-sm">
                                    <i class="fas fa-times-circle mr-1"></i>Forfeited
                                    <?php if ($deposit['refund_date']): ?>
                                        <br><span class="text-gray-400"><?php echo date('M d, Y', strtotime($deposit['refund_date'])); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400 text-sm">No action needed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if (empty($deposits)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-400">
                                <i class="fas fa-shield-alt text-4xl mb-4 block"></i>
                                <p>No security deposits found</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Forfeit Modal -->
<div id="forfeitModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="glass rounded-2xl border border-white/10 p-6 w-full max-w-md">
            <h3 class="text-xl font-bold text-white mb-4">Forfeit Security Deposit</h3>
            <form method="POST">
                <input type="hidden" name="deposit_id" id="forfeit_deposit_id">
                <input type="hidden" name="action" value="forfeit">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Reason for forfeiture</label>
                    <textarea name="reason" required rows="3"
                              class="w-full px-3 py-2 glass border border-white/10 rounded-lg text-white placeholder-gray-400 focus:border-red-500/50 focus:outline-none"
                              placeholder="Please provide a reason for forfeiting the security deposit..."></textarea>
                </div>

                <div class="flex space-x-3">
                    <button type="button" onclick="closeForfeitModal()"
                            class="flex-1 bg-gray-600 hover:bg-gray-700 text-white py-2 rounded-lg font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-medium transition-colors">
                        Forfeit Deposit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openForfeitModal(depositId, userName) {
    document.getElementById('forfeit_deposit_id').value = depositId;
    document.getElementById('forfeitModal').classList.remove('hidden');
}

function closeForfeitModal() {
    document.getElementById('forfeitModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../components/footer.php'; ?>