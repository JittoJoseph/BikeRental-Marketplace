<?php
$pageTitle = 'Home';
require_once __DIR__ . '/components/header.php';
require_once __DIR__ . '/db_connect.php';

// Fetch featured bikes
$stmt = $pdo->query("SELECT b.*, c.name as category_name FROM bikes b 
                     JOIN categories c ON b.category_id = c.id 
                     ORDER BY b.rating DESC LIMIT 6");
$featuredBikes = $stmt->fetchAll();

// Get statistics
$totalBikes = $pdo->query("SELECT COUNT(*) FROM bikes")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();
$totalBookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
?>

<!-- Modern Hero Section -->
<section class="relative min-h-screen flex items-center overflow-hidden">
    <!-- Dynamic Background -->
    <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950">
        <!-- Animated Mesh Gradient -->
        <div class="absolute inset-0 opacity-30">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-[128px] animate-blob"></div>
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-[128px] animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-0 left-1/2 w-96 h-96 bg-pink-500 rounded-full mix-blend-multiply filter blur-[128px] animate-blob animation-delay-4000"></div>
        </div>
        
        <!-- Dot Pattern Overlay -->
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px); background-size: 20px 20px;"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center">
            <!-- Announcement Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 mb-8 glass rounded-full border border-white/10 hover:border-blue-500/50 transition-all cursor-pointer group">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                </span>
                <span class="text-sm font-medium text-gray-300 group-hover:text-white transition-colors">
                    #1 Premium Bike Rental in India
                </span>
                <svg class="w-4 h-4 text-gray-400 group-hover:text-white group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </div>

            <!-- Hero Headline -->
            <h1 class="text-6xl md:text-7xl lg:text-8xl xl:text-9xl font-black tracking-tight mb-8">
                <span class="block text-white mb-2">Ride The</span>
                <span class="block">
                    <span class="inline-block animated-gradient text-transparent bg-clip-text">
                        Future
                    </span>
                </span>
                <span class="block text-white mt-2">Today</span>
            </h1>

            <!-- Subheadline -->
            <p class="text-xl md:text-2xl text-gray-400 max-w-3xl mx-auto mb-12 leading-relaxed">
                Experience the thrill of premium bikes. <span class="text-white font-semibold">₹250/day</span> onwards.
                <span class="block mt-2">No hidden charges. Instant booking. 24/7 support.</span>
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16">
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>/pages/explore.php" 
                       class="group relative px-10 py-5 bg-white text-slate-900 font-bold text-lg rounded-2xl overflow-hidden shadow-2xl hover:shadow-white/20 transition-all duration-300 hover:scale-105 inline-flex items-center">
                        <span class="relative z-10 flex items-center gap-3">
                            <i class="fas fa-rocket"></i>
                            <span>Start Your Ride</span>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </span>
                    </a>
                    <?php if (isAdmin()): ?>
                        <a href="<?php echo BASE_URL; ?>/pages/admin_dashboard.php" 
                           class="group px-10 py-5 glass border border-white/20 text-white font-semibold text-lg rounded-2xl hover:bg-white/10 hover:border-white/40 transition-all duration-300 inline-flex items-center gap-3">
                            <i class="fas fa-crown text-yellow-400"></i>
                            <span>Admin Panel</span>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/pages/my_bookings.php" 
                           class="group px-10 py-5 glass border border-white/20 text-white font-semibold text-lg rounded-2xl hover:bg-white/10 hover:border-white/40 transition-all duration-300 inline-flex items-center gap-3">
                            <i class="fas fa-calendar-check"></i>
                            <span>My Bookings</span>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/pages/explore.php" 
                       class="group relative px-10 py-5 bg-white text-slate-900 font-bold text-lg rounded-2xl overflow-hidden shadow-2xl hover:shadow-white/20 transition-all duration-300 hover:scale-105 inline-flex items-center">
                        <span class="relative z-10 flex items-center gap-3">
                            <i class="fas fa-rocket"></i>
                            <span>Explore Bikes</span>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/pages/register.php" 
                       class="group px-10 py-5 glass border border-white/20 text-white font-semibold text-lg rounded-2xl hover:bg-white/10 hover:border-white/40 transition-all duration-300 inline-flex items-center gap-3">
                        <i class="fas fa-user-plus"></i>
                        <span>Sign Up Free</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Trust Indicators -->
            <div class="flex flex-wrap items-center justify-center gap-8 text-sm text-gray-400">
                <div class="flex items-center gap-2">
                    <i class="fas fa-shield-alt text-green-400"></i>
                    <span>100% Secure</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-check-circle text-blue-400"></i>
                    <span>Verified Bikes</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-headset text-purple-400"></i>
                    <span>24/7 Support</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-award text-yellow-400"></i>
                    <span>Best Prices</span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-3 gap-6 max-w-4xl mx-auto mt-20">
                <div class="glass rounded-3xl p-6 border border-white/10 hover:border-blue-500/50 transition-all duration-300 group">
                    <div class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-400 mb-2">
                        <?php echo $totalBikes; ?>+
                    </div>
                    <div class="text-gray-400 font-medium">Premium Bikes</div>
                    <div class="h-1 w-16 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full mx-auto mt-3 group-hover:w-full transition-all duration-300"></div>
                </div>

                <div class="glass rounded-3xl p-6 border border-white/10 hover:border-purple-500/50 transition-all duration-300 group">
                    <div class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400 mb-2">
                        <?php echo $totalUsers; ?>+
                    </div>
                    <div class="text-gray-400 font-medium">Happy Riders</div>
                    <div class="h-1 w-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full mx-auto mt-3 group-hover:w-full transition-all duration-300"></div>
                </div>

                <div class="glass rounded-3xl p-6 border border-white/10 hover:border-green-500/50 transition-all duration-300 group">
                    <div class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-emerald-400 mb-2">
                        <?php echo $totalBookings; ?>+
                    </div>
                    <div class="text-gray-400 font-medium">Total Bookings</div>
                    <div class="h-1 w-16 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full mx-auto mt-3 group-hover:w-full transition-all duration-300"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
        <div class="flex flex-col items-center gap-2">
            <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Scroll</span>
            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
            </svg>
        </div>
    </div>
</section>

<style>
    @keyframes blob {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
    }
    .animate-blob {
        animation: blob 7s infinite;
    }
    .animation-delay-2000 {
        animation-delay: 2s;
    }
    .animation-delay-4000 {
        animation-delay: 4s;
    }
</style>

<style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</section>

<!-- Features Section -->
<section class="py-20 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <span class="inline-flex items-center px-4 py-2 glass rounded-full border border-white/10 text-sm mb-6">
                <div class="w-2 h-2 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full mr-2 animate-pulse"></div>
                <span class="text-gray-300 font-semibold">Why Choose Us</span>
            </span>
            <h2 class="text-4xl md:text-5xl font-black text-white mb-4">
                Your Perfect Ride,<br>Our <span class="animated-gradient text-transparent bg-clip-text">Promise</span>
            </h2>
            <p class="text-xl text-gray-400 max-w-2xl mx-auto">
                Experience hassle-free bike rentals with unmatched service quality
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="group relative glass rounded-3xl p-8 border border-white/10 hover:border-green-500/50 transition-all duration-300 transform hover:-translate-y-2">
                <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-emerald-500/10 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative">
                    <div class="bg-gradient-to-br from-green-500 to-emerald-600 w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6 transform group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-green-500/50">
                        <i class="fas fa-shield-alt text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-white">Safe & Secure</h3>
                    <p class="text-gray-400 leading-relaxed">All bikes are well-maintained, regularly serviced, and fully insured for your complete safety and peace of mind</p>
                    <div class="mt-6 flex items-center text-green-400 font-semibold">
                        <span>100% Verified</span>
                        <i class="fas fa-check-circle ml-2"></i>
                    </div>
                </div>
            </div>
            
            <!-- Feature 2 -->
            <div class="group relative glass rounded-3xl p-8 border border-white/10 hover:border-blue-500/50 transition-all duration-300 transform hover:-translate-y-2">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-indigo-500/10 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative">
                    <div class="bg-gradient-to-br from-blue-500 to-indigo-600 w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6 transform group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-blue-500/50">
                        <i class="fas fa-rupee-sign text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-white">Best Prices</h3>
                    <p class="text-gray-400 leading-relaxed">Competitive pricing with transparent billing. No hidden charges, no surprises - just honest rates you can trust</p>
                    <div class="mt-6 flex items-center text-blue-400 font-semibold">
                        <span>From ₹250/day</span>
                        <i class="fas fa-tag ml-2"></i>
                    </div>
                </div>
            </div>
            
            <!-- Feature 3 -->
            <div class="group relative glass rounded-3xl p-8 border border-white/10 hover:border-purple-500/50 transition-all duration-300 transform hover:-translate-y-2">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-pink-500/10 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative">
                    <div class="bg-gradient-to-br from-purple-500 to-pink-600 w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6 transform group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-purple-500/50">
                        <i class="fas fa-headset text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-white">24/7 Support</h3>
                    <p class="text-gray-400 leading-relaxed">Round-the-clock customer support ready to assist you. We're here whenever you need us, day or night</p>
                    <div class="mt-6 flex items-center text-purple-400 font-semibold">
                        <span>Always Available</span>
                        <i class="fas fa-phone-volume ml-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Features -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-12">
            <div class="text-center p-6 glass rounded-2xl border border-white/10 hover:border-blue-500/30 transition-all group">
                <i class="fas fa-clock text-3xl text-blue-400 mb-3 group-hover:scale-110 transform transition-transform"></i>
                <p class="font-semibold text-gray-300">Quick Booking</p>
            </div>
            <div class="text-center p-6 glass rounded-2xl border border-white/10 hover:border-green-500/30 transition-all group">
                <i class="fas fa-home text-3xl text-green-400 mb-3 group-hover:scale-110 transform transition-transform"></i>
                <p class="font-semibold text-gray-300">Doorstep Delivery</p>
            </div>
            <div class="text-center p-6 glass rounded-2xl border border-white/10 hover:border-orange-500/30 transition-all group">
                <i class="fas fa-tools text-3xl text-orange-400 mb-3 group-hover:scale-110 transform transition-transform"></i>
                <p class="font-semibold text-gray-300">Free Maintenance</p>
            </div>
            <div class="text-center p-6 glass rounded-2xl border border-white/10 hover:border-yellow-500/30 transition-all group">
                <i class="fas fa-medal text-3xl text-yellow-400 mb-3 group-hover:scale-110 transform transition-transform"></i>
                <p class="font-semibold text-gray-300">Premium Quality</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Bikes Section -->
<section class="py-20 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <span class="inline-block px-4 py-2 bg-orange-100 text-orange-600 font-semibold rounded-full text-sm mb-4">
                Top Picks
            </span>
            <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                Featured <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-red-500">Rides</span>
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Handpicked premium bikes loved by our riders
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($featuredBikes as $bike): ?>
            <div class="group glass rounded-3xl overflow-hidden border border-white/10 hover:border-blue-500/50 transition-all duration-500 transform hover:-translate-y-3 hover:shadow-2xl hover:shadow-blue-500/20">
                <!-- Image Container -->
                <div class="relative h-56 overflow-hidden">
                    <img src="<?php echo htmlspecialchars($bike['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($bike['name']); ?>"
                         class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                    
                    <!-- Rating Badge -->
                    <div class="absolute top-4 right-4 bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg flex items-center space-x-1">
                        <i class="fas fa-star"></i>
                        <span><?php echo number_format($bike['rating'], 1); ?></span>
                    </div>
                    
                    <!-- Category Badge -->
                    <div class="absolute top-4 left-4 glass px-3 py-1 rounded-lg text-xs font-semibold text-gray-300 border border-white/20">
                        <?php echo htmlspecialchars($bike['category_name']); ?>
                    </div>

                    <!-- Hover Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
                
                <!-- Content -->
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-white mb-2 group-hover:text-blue-400 transition-colors duration-300">
                        <?php echo htmlspecialchars($bike['name']); ?>
                    </h3>
                    <p class="text-gray-400 text-sm mb-4 line-clamp-2 leading-relaxed">
                        <?php echo htmlspecialchars($bike['description']); ?>
                    </p>
                    
                    <!-- Features -->
                    <div class="flex items-center space-x-4 mb-4 text-sm">
                        <span class="flex items-center text-gray-400">
                            <i class="fas fa-motorcycle mr-1 text-blue-400"></i> Available
                        </span>
                        <span class="flex items-center text-gray-400">
                            <i class="fas fa-shield-alt mr-1 text-green-400"></i> Insured
                        </span>
                    </div>

                    <!-- Price and CTA -->
                    <div class="flex items-center justify-between pt-4 border-t border-white/10">
                        <div>
                            <p class="text-gray-500 text-xs mb-1">Starting from</p>
                            <div class="flex items-baseline">
                                <span class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 text-transparent bg-clip-text">
                                    ₹<?php echo number_format($bike['price'], 0); ?>
                                </span>
                                <span class="text-gray-500 text-sm ml-1">/day</span>
                            </div>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/pages/bike_details.php?id=<?php echo $bike['id']; ?>" 
                           class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-300 font-semibold shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40 transform hover:scale-105 inline-flex items-center">
                            Book Now
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- View All Button -->
        <div class="text-center mt-12">
            <a href="<?php echo BASE_URL; ?>/pages/explore.php" 
               class="inline-flex items-center px-10 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold text-lg rounded-2xl hover:from-blue-700 hover:to-purple-700 transition-all duration-300 shadow-2xl shadow-blue-500/20 hover:shadow-blue-500/40 transform hover:-translate-y-1">
                View All Bikes 
                <i class="fas fa-arrow-right ml-3"></i>
            </a>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-20 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <span class="inline-flex items-center px-4 py-2 glass rounded-full border border-white/10 text-sm mb-6">
                <div class="w-2 h-2 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full mr-2 animate-pulse"></div>
                <span class="text-gray-300 font-semibold">Simple Process</span>
            </span>
            <h2 class="text-4xl md:text-5xl font-black text-white mb-4">
                Book in <span class="animated-gradient text-transparent bg-clip-text">3 Easy Steps</span>
            </h2>
            <p class="text-xl text-gray-400 max-w-2xl mx-auto">
                Getting your dream ride has never been this simple
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            <!-- Connecting Line -->
            <div class="hidden md:block absolute top-1/4 left-0 right-0 h-1 bg-gradient-to-r from-purple-500/20 via-pink-500/20 to-orange-500/20" style="top: 80px;"></div>

            <!-- Step 1 -->
            <div class="relative text-center">
                <div class="bg-gradient-to-br from-purple-500 to-pink-500 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-purple-500/50 relative z-10">
                    <span class="text-4xl font-bold text-white">1</span>
                </div>
                <div class="glass rounded-2xl p-6 border border-white/10 hover:border-purple-500/50 transition-all">
                    <i class="fas fa-search text-4xl text-purple-400 mb-4"></i>
                    <h3 class="text-2xl font-bold text-white mb-3">Choose Your Bike</h3>
                    <p class="text-gray-400">Browse our collection and find the perfect bike for your journey</p>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="relative text-center">
                <div class="bg-gradient-to-br from-pink-500 to-orange-500 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-pink-500/50 relative z-10">
                    <span class="text-4xl font-bold text-white">2</span>
                </div>
                <div class="glass rounded-2xl p-6 border border-white/10 hover:border-pink-500/50 transition-all">
                    <i class="fas fa-calendar-check text-4xl text-pink-400 mb-4"></i>
                    <h3 class="text-2xl font-bold text-white mb-3">Select Dates</h3>
                    <p class="text-gray-400">Pick your rental period and submit your booking request</p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="relative text-center">
                <div class="bg-gradient-to-br from-orange-500 to-red-500 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-orange-500/50 relative z-10">
                    <span class="text-4xl font-bold text-white">3</span>
                </div>
                <div class="glass rounded-2xl p-6 border border-white/10 hover:border-orange-500/50 transition-all">
                    <i class="fas fa-motorcycle text-4xl text-orange-400 mb-4"></i>
                    <h3 class="text-2xl font-bold text-white mb-3">Start Riding</h3>
                    <p class="text-gray-400">Get approved and hit the road with your chosen bike!</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-20 relative overflow-hidden">
    <!-- Background -->
    <div class="absolute inset-0">
        <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="inline-flex items-center px-4 py-2 glass rounded-full border border-white/10 text-sm mb-6">
                <div class="w-2 h-2 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full mr-2 animate-pulse"></div>
                <span class="text-gray-300 font-semibold">Customer Reviews</span>
            </span>
            <h2 class="text-4xl md:text-5xl font-black text-white mb-4">
                What Our <span class="animated-gradient text-transparent bg-clip-text">Riders Say</span>
            </h2>
            <p class="text-xl text-gray-400 max-w-2xl mx-auto">
                Join thousands of happy customers who trust us with their rides
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="glass backdrop-blur-sm rounded-2xl p-8 border border-white/10 hover:border-blue-500/30 transition-all">
                <div class="flex items-center mb-4">
                    <div class="flex text-yellow-400 text-lg">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <p class="text-gray-300 mb-6 leading-relaxed">"Amazing service! The bike was in perfect condition and the booking process was super smooth. Highly recommended!"</p>
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white font-bold">RS</span>
                    </div>
                    <div>
                        <p class="font-bold text-white">Rahul Sharma</p>
                        <p class="text-gray-400 text-sm">Delhi</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 border border-white/20">
                <div class="flex items-center mb-4">
                    <div class="flex text-yellow-400 text-lg">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <p class="text-gray-300 mb-6 leading-relaxed">"Best bike rental experience ever! Great prices, excellent support, and the bikes are top-notch quality."</p>
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white font-bold">PK</span>
                    </div>
                    <div>
                        <p class="font-bold text-white">Priya Kumar</p>
                        <p class="text-gray-400 text-sm">Mumbai</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 border border-white/20">
                <div class="flex items-center mb-4">
                    <div class="flex text-yellow-400 text-lg">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <p class="text-gray-300 mb-6 leading-relaxed">"Professional service with great attention to detail. Will definitely rent from them again on my next trip!"</p>
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white font-bold">AS</span>
                    </div>
                    <div>
                        <p class="font-bold text-white">Arjun Singh</p>
                        <p class="text-gray-400 text-sm">Bangalore</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 relative overflow-hidden">
    <div class="absolute inset-0">
        <div class="absolute top-0 left-0 w-96 h-96 bg-orange-500/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-pink-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="max-w-3xl mx-auto">
            <h2 class="text-4xl md:text-6xl font-black text-white mb-6 leading-tight">
                Ready to Hit the Road?
            </h2>
            <p class="text-2xl text-gray-300 mb-10 leading-relaxed">
                Join thousands of satisfied riders and experience the freedom of the open road today
            </p>
            
            <?php if (!isLoggedIn()): ?>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo BASE_URL; ?>/pages/register.php" 
                   class="inline-flex items-center justify-center px-10 py-5 bg-gradient-to-r from-orange-600 to-red-600 text-white font-bold text-xl rounded-2xl hover:from-orange-700 hover:to-red-700 transition-all duration-300 shadow-2xl shadow-orange-500/20 hover:shadow-orange-500/40 transform hover:-translate-y-1">
                    <i class="fas fa-user-plus mr-3"></i> Create Free Account
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/explore.php" 
                   class="inline-flex items-center justify-center px-10 py-5 glass border-2 border-white/20 text-white font-bold text-xl rounded-2xl hover:bg-white/10 hover:border-orange-500/50 transition-all duration-300 transform hover:-translate-y-1">
                    <i class="fas fa-motorcycle mr-3"></i> Browse Bikes
                </a>
            </div>
            <?php else: ?>
            <a href="<?php echo BASE_URL; ?>/pages/explore.php" 
               class="inline-flex items-center justify-center px-10 py-5 bg-gradient-to-r from-orange-600 to-red-600 text-white font-bold text-xl rounded-2xl hover:from-orange-700 hover:to-red-700 transition-all duration-300 shadow-2xl shadow-orange-500/20 hover:shadow-orange-500/40 transform hover:-translate-y-1">
                <i class="fas fa-motorcycle mr-3"></i> Start Booking Now
            </a>
            <?php endif; ?>

            <!-- Trust Indicators -->
            <div class="grid grid-cols-3 gap-8 mt-16 pt-12 border-t border-white/10">
                <div>
                    <p class="text-5xl font-bold mb-2"><?php echo $totalBikes; ?>+</p>
                    <p class="text-white/80">Premium Bikes</p>
                </div>
                <div>
                    <p class="text-5xl font-bold mb-2"><?php echo $totalUsers; ?>+</p>
                    <p class="text-white/80">Happy Riders</p>
                </div>
                <div>
                    <p class="text-5xl font-bold mb-2"><?php echo $totalBookings; ?>+</p>
                    <p class="text-white/80">Successful Rentals</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/components/footer.php'; ?>
