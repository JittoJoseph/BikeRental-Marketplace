<?php
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Bike Rental System</title>
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
                                BikeRental
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

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
