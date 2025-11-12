<?php
require_once __DIR__ . '/../config.php';
requireAdmin();
require_once __DIR__ . '/../db_connect.php';

$pageTitle = 'User Management';

// Handle unverify action
if (isset($_GET['unverify'])) {
    $userId = intval($_GET['unverify']);
    $stmt = $pdo->prepare("UPDATE users SET id_proof = NULL WHERE id = ? AND is_admin = 0");
    if ($stmt->execute([$userId])) {
        $success = 'User verification removed successfully. They will be prompted to upload ID proof again.';
        header('refresh:2;url=' . BASE_URL . '/pages/user_management.php');
    } else {
        $error = 'Failed to remove user verification.';
    }
}

// Fetch all users
$usersStmt = $pdo->query("SELECT id, name, email, id_proof, created_at FROM users WHERE is_admin = 0 ORDER BY created_at DESC");
$users = $usersStmt->fetchAll();

// Get user statistics
$userStatsStmt = $pdo->query("SELECT
    (SELECT COUNT(*) FROM users WHERE is_admin = 0) as total_users,
    (SELECT COUNT(*) FROM users WHERE is_admin = 0 AND id_proof IS NOT NULL AND id_proof != '') as users_with_id,
    (SELECT COUNT(*) FROM users WHERE is_admin = 0 AND (id_proof IS NULL OR id_proof = '')) as users_without_id");
$userStats = $userStatsStmt->fetch();

require_once __DIR__ . '/../components/header.php';
?>

<!-- Premium Dark Hero Section -->
<div class="relative overflow-hidden py-16">
    <div class="absolute inset-0">
        <div class="absolute top-0 left-0 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-green-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 glass rounded-full mb-6 border border-white/10">
                <i class="fas fa-users text-2xl text-emerald-400"></i>
            </div>
            <h1 class="text-5xl md:text-6xl font-black text-white mb-4 tracking-tight">
                User <span class="animated-gradient text-transparent bg-clip-text">Management</span>
            </h1>
            <p class="text-xl text-gray-400 max-w-2xl mx-auto leading-relaxed">
                Manage registered users and their ID proof documents
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

    <!-- User Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Users -->
        <div class="group relative glass rounded-2xl border border-white/10 hover:border-emerald-500/50 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-green-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl shadow-lg shadow-emerald-500/20">
                        <i class="fas fa-users text-2xl text-white"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-black text-white"><?php echo $userStats['total_users']; ?></p>
                        <p class="text-sm text-gray-400 font-medium">Total Users</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-emerald-400 font-semibold">Registered Users</span>
                    <i class="fas fa-user-check text-emerald-400"></i>
                </div>
            </div>
        </div>

        <!-- Users with ID Proof -->
        <div class="group relative glass rounded-2xl border border-white/10 hover:border-blue-500/50 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-indigo-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-500/20">
                        <i class="fas fa-file-alt text-2xl text-white"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-black text-white"><?php echo $userStats['users_with_id']; ?></p>
                        <p class="text-sm text-gray-400 font-medium">With ID Proof</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-blue-400 font-semibold">Verified Users</span>
                    <i class="fas fa-check-circle text-blue-400"></i>
                </div>
            </div>
        </div>

        <!-- Users without ID Proof -->
        <div class="group relative glass rounded-2xl border border-white/10 hover:border-amber-500/50 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-yellow-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-xl shadow-lg shadow-amber-500/20">
                        <i class="fas fa-exclamation-triangle text-2xl text-white"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-black text-white"><?php echo $userStats['users_without_id']; ?></p>
                        <p class="text-sm text-gray-400 font-medium">Missing ID Proof</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-amber-400 font-semibold">Needs Attention</span>
                    <i class="fas fa-clock text-amber-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Management Table -->
    <div class="glass rounded-2xl border border-white/10 overflow-hidden">
        <div class="p-8 border-b border-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-white">Registered Users</h2>
                    <p class="text-gray-400 mt-1">Manage user accounts and verify ID proof documents</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="text-sm text-gray-400">
                        <i class="fas fa-circle text-green-400 mr-1"></i><?php echo count($users); ?> users total
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-8 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">User</th>
                        <th class="px-8 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-8 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">ID Proof Status</th>
                        <th class="px-8 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Registered</th>
                        <th class="px-8 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-white/5 transition-colors duration-200">
                        <td class="px-8 py-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-10 h-10">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center">
                                        <span class="text-white font-semibold text-sm"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-white"><?php echo htmlspecialchars($user['name']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="text-sm text-gray-300"><?php echo htmlspecialchars($user['email']); ?></span>
                        </td>
                        <td class="px-8 py-6">
                            <?php if (!empty($user['id_proof'])): ?>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-file-alt text-green-400"></i>
                                    <span class="text-sm text-green-400 font-medium">Verified</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                                        <i class="fas fa-check mr-1"></i>Uploaded
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-exclamation-triangle text-amber-400"></i>
                                    <span class="text-sm text-amber-400 font-medium">Pending</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                        <i class="fas fa-clock mr-1"></i>Missing
                                    </span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-8 py-6">
                            <span class="text-sm text-gray-400"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center space-x-3">
                                <?php if (!empty($user['id_proof'])): ?>
                                    <a href="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($user['id_proof']); ?>" target="_blank"
                                       class="inline-flex items-center px-3 py-2 border border-blue-500/30 text-blue-400 bg-blue-500/10 rounded-lg hover:bg-blue-500/20 hover:border-blue-500/50 transition-all duration-200 text-sm font-medium">
                                        <i class="fas fa-eye mr-1"></i>View ID
                                    </a>
                                    <button class="inline-flex items-center px-3 py-2 border border-red-500/30 text-red-400 bg-red-500/10 rounded-lg hover:bg-red-500/20 hover:border-red-500/50 transition-all duration-200 text-sm font-medium"
                                            onclick="if(confirm('Are you sure you want to unverify this user? They will be prompted to upload ID proof again.')) { window.location.href='?unverify=<?php echo $user['id']; ?>'; }">
                                        <i class="fas fa-times-circle mr-1"></i>Unverify
                                    </button>
                                <?php else: ?>
                                    <button class="inline-flex items-center px-3 py-2 border border-amber-500/30 text-amber-400 bg-amber-500/10 rounded-lg hover:bg-amber-500/20 hover:border-amber-500/50 transition-all duration-200 text-sm font-medium"
                                            onclick="alert('ID proof not uploaded yet. User needs to provide identification document.')">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Pending
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (count($users) === 0): ?>
        <div class="text-center py-16">
            <div class="mx-auto w-24 h-24 glass rounded-full flex items-center justify-center mb-4 border border-white/10">
                <i class="fas fa-users text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-white mb-2">No users found</h3>
            <p class="text-gray-400 mb-6">Users will appear here once they register on the platform.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../components/footer.php'; ?>