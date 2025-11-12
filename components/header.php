<?php
require_once __DIR__ . '/../config.php';

// Check if user needs to upload ID proof
$idProofRequired = false;
if (isLoggedIn() && !isAdmin()) {
    require_once __DIR__ . '/../db_connect.php';
    $stmt = $pdo->prepare("SELECT id_proof FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $idProofRequired = empty($user['id_proof']);
}

// Handle ID proof upload
$upload_error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_id_proof'])) {
    require_once __DIR__ . '/../db_connect.php';
    
    if (isset($_FILES['id_proof']) && $_FILES['id_proof']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (in_array($_FILES['id_proof']['type'], $allowed_types) && $_FILES['id_proof']['size'] <= $max_size) {
            $upload_dir = __DIR__ . '/../uploads/';
            $file_extension = pathinfo($_FILES['id_proof']['name'], PATHINFO_EXTENSION);
            $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $unique_filename;
            $relative_path = 'uploads/' . $unique_filename;
            
            if (move_uploaded_file($_FILES['id_proof']['tmp_name'], $upload_path)) {
                // Update database
                $stmt = $pdo->prepare("UPDATE users SET id_proof = ? WHERE id = ?");
                if ($stmt->execute([$relative_path, $_SESSION['user_id']])) {
                    header('Location: ' . BASE_URL . '/index.php?success=id_proof_uploaded');
                    exit;
                } else {
                    $upload_error = 'Failed to update database.';
                }
            } else {
                $upload_error = 'Failed to upload file.';
            }
        } else {
            $upload_error = 'Invalid file type or size. Please upload PDF, DOC, DOCX, JPG, or PNG files up to 5MB.';
        }
    } else {
        $upload_error = 'Please select a file to upload.';
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Urban Spokes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to bottom, #0f172a, #1e293b);
            min-height: 100vh;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: #1e293b;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, #2563eb, #7c3aed);
        }

        /* Glassmorphism effect */
        .glass {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Glow effect */
        .glow {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
        }

        /* Animated gradient */
        .animated-gradient {
            background: linear-gradient(-45deg, #3b82f6, #8b5cf6, #ec4899, #f59e0b);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
</head>
<body class="bg-slate-900 text-gray-100">
    <!-- Premium Dark Header -->
    <nav class="glass fixed top-0 left-0 right-0 z-50 border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="<?php echo BASE_URL; ?>/index.php" class="flex items-center space-x-3 group">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl blur opacity-75 group-hover:opacity-100 transition"></div>
                            <div class="relative bg-gradient-to-r from-blue-600 to-purple-600 p-2.5 rounded-xl">
                                <i class="fas fa-motorcycle text-2xl text-white"></i>
                            </div>
                        </div>
                        <div>
                            <span class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 text-transparent bg-clip-text">
                                Urban Spokes
                            </span>
                            <p class="text-xs text-gray-400 -mt-1">Ride Premium</p>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden lg:flex items-center space-x-1">
                    <a href="<?php echo BASE_URL; ?>/index.php" 
                       class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-all duration-300 font-medium">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
                    <a href="<?php echo BASE_URL; ?>/pages/explore.php" 
                       class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-all duration-300 font-medium">
                        <i class="fas fa-compass mr-2"></i>Explore
                    </a>
                    
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <a href="<?php echo BASE_URL; ?>/pages/admin_dashboard.php" 
                               class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-all duration-300 font-medium">
                                <i class="fas fa-cog mr-2"></i>Dashboard
                            </a>
                            <a href="<?php echo BASE_URL; ?>/pages/user_management.php" 
                               class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-all duration-300 font-medium">
                                <i class="fas fa-users mr-2"></i>Users
                            </a>
                            <a href="<?php echo BASE_URL; ?>/pages/manage_requests.php" 
                               class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-all duration-300 font-medium relative">
                                <i class="fas fa-clipboard-list mr-2"></i>Requests
                            </a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>/pages/my_bookings.php" 
                               class="px-4 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-all duration-300 font-medium">
                                <i class="fas fa-calendar-check mr-2"></i>My Bookings
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Auth Buttons -->
                <div class="hidden lg:flex items-center space-x-3">
                    <?php if (isLoggedIn()): ?>
                        <div class="flex items-center space-x-3 px-4 py-2 rounded-lg bg-white/5 border border-white/10">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-bold">
                                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                                </span>
                            </div>
                            <span class="text-gray-300 font-medium hidden xl:inline">
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </span>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/pages/logout.php" 
                           class="px-5 py-2.5 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20 hover:border-red-500/30 transition-all duration-300 font-medium">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/pages/login.php" 
                           class="px-5 py-2.5 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-all duration-300 font-medium">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                        <a href="<?php echo BASE_URL; ?>/pages/register.php" 
                           class="px-6 py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold transition-all duration-300 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40">
                            <i class="fas fa-user-plus mr-2"></i>Get Started
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile menu button -->
                <div class="lg:hidden">
                    <button id="mobile-menu-button" 
                            class="p-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-all">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden lg:hidden border-t border-white/5">
            <div class="px-4 pt-2 pb-4 space-y-1">
                <a href="<?php echo BASE_URL; ?>/index.php" 
                   class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-all">
                    <i class="fas fa-home mr-2"></i>Home
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/explore.php" 
                   class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-all">
                    <i class="fas fa-compass mr-2"></i>Explore Bikes
                </a>
                
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="<?php echo BASE_URL; ?>/pages/admin_dashboard.php" 
                           class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-all">
                            <i class="fas fa-cog mr-2"></i>Dashboard
                        </a>
                        <a href="<?php echo BASE_URL; ?>/pages/user_management.php" 
                           class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-all">
                            <i class="fas fa-users mr-2"></i>User Management
                        </a>
                        <a href="<?php echo BASE_URL; ?>/pages/manage_requests.php" 
                           class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-all">
                            <i class="fas fa-clipboard-list mr-2"></i>Requests
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/pages/my_bookings.php" 
                           class="block px-4 py-3 rounded-lg text-gray-300 hover:text-white hover:bg-white/5 transition-all">
                            <i class="fas fa-calendar-check mr-2"></i>My Bookings
                        </a>
                    <?php endif; ?>
                    
                    <!-- Mobile Auth -->
                    <div class="pt-4 mt-4 border-t border-white/10">
                        <div class="flex items-center space-x-3 px-4 py-3 rounded-lg bg-white/5 mb-2">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold">
                                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                                </span>
                            </div>
                            <span class="text-gray-300 font-medium">
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </span>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/pages/logout.php" 
                           class="block px-4 py-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20 text-center font-medium">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </div>
                <?php else: ?>
                    <div class="pt-4 mt-4 border-t border-white/10 space-y-2">
                        <a href="<?php echo BASE_URL; ?>/pages/login.php" 
                           class="block px-4 py-3 rounded-lg text-center text-gray-300 hover:text-white hover:bg-white/5 transition-all font-medium">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                        <a href="<?php echo BASE_URL; ?>/pages/register.php" 
                           class="block px-4 py-3 rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 text-white text-center font-semibold">
                            <i class="fas fa-user-plus mr-2"></i>Get Started
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Add spacing for fixed header -->
    <div class="h-20"></div>

    <!-- ID Proof Upload Modal -->
    <?php if ($idProofRequired): ?>
    <div id="id-proof-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-slate-800 p-6 rounded-lg shadow-xl max-w-md w-full mx-4 glass glow">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                <i class="fas fa-id-card mr-2 text-blue-400"></i>Upload ID Proof Required
            </h2>
            <p class="text-gray-300 mb-4">To continue using Urban Spokes services, please upload a valid ID proof document. This helps us verify your identity for security purposes.</p>
            <?php if ($upload_error): ?>
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-3 rounded-lg mb-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i><?php echo htmlspecialchars($upload_error); ?>
                </div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="id_proof" class="block text-sm font-medium text-gray-300 mb-2">
                        <i class="fas fa-file-upload mr-2"></i>Select ID Proof File
                    </label>
                    <input type="file" name="id_proof" id="id_proof" accept=".pdf,.doc,.docx,.jpg,.png" required
                           class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Accepted formats: PDF, DOC, DOCX, JPG, PNG (Max 5MB)</p>
                </div>
                <button type="submit" name="upload_id_proof" value="1"
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium py-3 px-4 rounded-lg transition-all duration-300 shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40">
                    <i class="fas fa-upload mr-2"></i>Upload & Continue
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
