<?php
// Include the check file which already starts a session and sets up database connection
require_once 'check.inc.php';

// Debug current path - will help identify if there are path issues
$current_path = dirname($_SERVER['PHP_SELF']);
$debug_info = "Current path: " . $current_path;

// Check if user is logged in (does not redirect, just sets $isLoggedIn variable)
$isLoggedIn = isLoggedIn();
$current_db_id = $_SESSION["id"] ?? null;
$current_user_name = $_SESSION["name"] ?? null; // Get user name for dropdown

// Function to get cart count
function getCartCount($db_id) {
    global $conn;
    try {
        $query = "SELECT SUM(quantity) as count FROM cart WHERE db_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $db_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

// Get products from database
function getProducts() {
    global $conn;
    try {
        // Use a simpler query without the is_active condition
        $query = "SELECT * FROM products ORDER BY id DESC";
        $result = $conn->query($query);
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    } catch (Exception $e) {
        return [];
    }
}

$products = getProducts();
$cart_count = $isLoggedIn ? getCartCount($current_db_id) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- mobile metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <!-- site metas -->
    <title>Products</title>
    <meta name="keywords" content="">
  
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.0.0-beta.2.4/assets/owl.carousel.min.css" />
    <!-- bootstrap css -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <!-- style css -->
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <!-- Responsive-->
    <link rel="stylesheet" href="css/responsive.css">

    <!-- Scrollbar Custom CSS -->
    <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
       
        .carousel-container {
            max-width: 1200px;
            margin: 30px auto;
            overflow: hidden;
            padding: 30px 20px  15px;
        }

        .carousel {
            display: flex;
            gap: 15px;
            padding: 10px 0;
            overflow-x: auto;
            scroll-behavior: smooth;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .carousel::-webkit-scrollbar {
            display: none;
        }

        .product-card {
            min-width: 220px;
            max-width: 220px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            position: relative;
            transition: transform 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .discount-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: #8bc34a;
            color: white;
            font-size: 12px;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 12px;
        }

        .favorite-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .favorite-btn svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: #666;
            stroke-width: 2;
            transition: all 0.3s ease;
        }

        .favorite-btn.active svg {
            fill: #ff5252;
            stroke: #ff5252;
        }

        .product-image {
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .product-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .product-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-unit {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .unit-text {
            font-size: 12px;
            color: #666;
            margin-right: 5px;
        }

        .rating {
            display: flex;
            align-items: center;
        }

        .star {
            color: #ffc107;
            font-size: 14px;
            margin-right: 2px;
        }

        .rating-value {
            font-size: 12px;
            color: #666;
            font-weight: bold;
        }

        .product-price {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            background: #f5f5f5;
            border: none;
            color: #333;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-btn:hover {
            background: #e0e0e0;
        }

        .quantity {
            width: 30px;
            height: 30px;
            border: none;
            text-align: center;
            font-size: 14px;
        }
        
        .add-to-cart {
            background: none;
            border: none;
            color: #2196f3;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .add-to-cart:hover {
            background-color: rgba(33, 150, 243, 0.1);
        }

        .navigation {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .nav-btn {
            background: #fff;
            border: 1px solid #ddd;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin: 0 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .nav-btn:hover {
            background: #f5f5f5;
        }
        
        /* Add notification badge for cart */
        .cart-badge {
            position: relative;
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #ff5252;
            color: white;
            font-size: 10px;
            font-weight: bold;
            min-width: 15px;
            height: 15px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2px;
        }
        
        /* Toast notification */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .toast {
            background-color: #333;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }
        
        .toast.show {
            opacity: 1;
        }
        
        .toast.success {
            background-color: #4CAF50;
        }
        
        .toast.error {
            background-color: #f44336;
        }
        
        /* User dropdown menu */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 5px;
        }
        
        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            font-size: 14px;
        }
        
        .dropdown-content a:hover {
            background-color: #f1f1f1;
            border-radius: 5px;
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
        }
        
        .dropdown-toggle {
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        
        .dropdown-toggle::after {
            display: inline-block;
            margin-left: 5px;
            vertical-align: middle;
            content: "";
            border-top: 5px solid;
            border-right: 5px solid transparent;
            border-bottom: 0;
            border-left: 5px solid transparent;
        }
    </style>
</head>
<body>
    <!-- Toast notifications -->
    <div class="toast-container" id="toastContainer"></div>
    
    <!-- Debug info (hidden in production) -->
    <div class="container mt-2" style="display: none;">
        <div class="alert alert-info">
            <?php echo $debug_info; ?>
        </div>
    </div>

    <!--header section start -->
    <div class="header_section">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                <!-- Logo -->
                <a class="navbar-brand" href="index.php">
                    <img src="images/logo.png" alt="Caraft">
                </a>
                
                <!-- Toggler for mobile -->
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <!-- This will contain all the items that should collapse on mobile -->
                <div class="collapse navbar-collapse" id="navbarContent">
                    <!-- Main navigation links -->
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="category.php">Category</a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link" href="products.php">Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contact.php">Contact Us</a>
                        </li>
                    </ul>
                    
                    <!-- Right-aligned items - Modified for login/logout -->
                    <ul class="navbar-nav ml-auto search_section">
                        <?php if ($isLoggedIn): ?>
                            <!-- Show user account dropdown if logged in -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <?php echo isset($_SESSION["name"]) ? $_SESSION["name"] : "User"; ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                                    <a class="dropdown-item" href="profile.php">My Profile</a>
                                    <a class="dropdown-item" href="order.php">Orders & Favorites</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="logout.php">Logout</a>
                                </div>
                            </li>
                        <?php else: ?>
                            <!-- Show login link if not logged in -->
                            <li class="nav-item">
                                <a class="nav-link" href="login.php">Log In</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="register.php">Register</a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item">
                            <a class="nav-link cart-badge" href="cart.php">
                                <img src="images/shopping-bag.png" alt="Cart">
                                <?php if ($cart_count > 0): ?>
                                    <span class="cart-count"><?= $cart_count ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#"><img src="images/search-icon.png" alt="Search"></a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
    <!--header section end -->
                                
                                <?php if ($cart_count > 0): ?>
                                    <span class="cart-count"><?= $cart_count ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="search-icon" style="color: white;">
                                <i class="fa fa-search" style="font-size: 1.2rem;"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
    <!--header section end -->
    
    <!-- Page heading -->
    <div class="container mt-4">
        <h2 class="text-center mb-4">Our Products</h2>
       
    </div>
    
    <!-- Products grid -->
    <div class="carousel-container">
        <div class="carousel" id="productCarousel">
            <?php if (empty($products)): ?>
                <div class="alert alert-info w-100 text-center">No products found</div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card" data-product-id="<?= $product['id'] ?>">
                        <?php if (isset($product['discount']) && $product['discount'] > 0): ?>
                            <div class="discount-badge">-<?= $product['discount'] ?>%</div>
                        <?php endif; ?>
                        
                        <button class="favorite-btn <?= ($isLoggedIn && function_exists('isProductFavorite') && isProductFavorite($product['id'], $current_db_id)) ? 'active' : '' ?>" data-product-id="<?= $product['id'] ?>">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                            </svg>
                        </button>
                        
                        <div class="product-image">
                            <img src="<?= !empty($product['image']) ? $product['image'] : '/api/placeholder/150/150' ?>" alt="<?= htmlspecialchars($product['name']) ?>" />
                        </div>
                        
                        <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                        
                        <div class="product-unit">
                            <span class="unit-text"><?= htmlspecialchars($product['unit'] ?? '1 UNIT') ?></span>
                            <div class="rating">
                                <span class="star">★</span>
                                <span class="rating-value"><?= number_format($product['rating'] ?? 4.5, 1) ?></span>
                            </div>
                        </div>
                        
                        <div class="product-price">
                             <?= number_format($product['price'], 2) ?> DT
                        </div>
                        
                        <div class="quantity-controls">
                            <div class="quantity-selector">
                                <button class="quantity-btn decrease">−</button>
                                <input type="text" class="quantity" value="1" readonly>
                                <button class="quantity-btn increase">+</button>
                            </div>
                            <form method="get" action="add_to_cart.php" style="display: inline;">
    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
    <input type="hidden" name="quantity" value="1" class="quantity-input">
    <button type="submit" class="add-to-cart">Add to Cart</button>
</form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="navigation">
            <button class="nav-btn prev">◀</button>
            <button class="nav-btn next">▶</button>
        </div>
    </div>
    
    <!-- footer section start -->
    <div class="footer_section layout_padding">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-sm-12">
                    <h4 class="information_text">Category</h4>
                    <p class="dummy_text">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim </p>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="information_main">
                        <h4 class="information_text">Useful Links</h4>
                        <p class="many_text">Contrary to popular belief, Lorem Ipsum is not simply random text. It </p>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="information_main">
                        <h4 class="information_text">Contact Us</h4>
                        <p class="call_text"><a href="#">+01 1234567890</a></p>
                        <p class="call_text"><a href="#">+01 9876543210</a></p>
                        <p class="call_text"><a href="#">demo@gmail.com</a></p>
                        <div class="social_icon">
                            <ul>
                                <li><a href="#"><img src="images/fb-icon.png"></a></li>
                                <li><a href="#"><img src="images/twitter-icon.png"></a></li>
                                <li><a href="#"><img src="images/linkedin-icon.png"></a></li>
                                <li><a href="#"><img src="images/instagram-icon.png"></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    <!-- footer section end -->
    
    <!-- Javascript files-->
    <script src="js/jquery.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery-3.0.0.min.js"></script>
    <script src="js/plugin.js"></script>
    <!-- sidebar -->
    <script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="js/custom.js"></script>
    <!-- javascript --> 
    <script src="js/owl.carousel.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.js"></script> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.0.0-beta.2.4/owl.carousel.min.js"></script>
    
    <script>
        // Add functionality to the buttons
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap components
            // This ensures the dropdown menu works correctly
            $('.dropdown-toggle').dropdown();
            
            // Toast notification function
            if (window.innerWidth < 992) {
                const dropdownToggle = document.querySelector('.dropdown-toggle');
                if (dropdownToggle) {
                    dropdownToggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        const dropdownContent = this.nextElementSibling;
                        if (dropdownContent.style.display === 'block') {
                            dropdownContent.style.display = 'none';
                        } else {
                            dropdownContent.style.display = 'block';
                        }
                    });
                }
            }
            
            // Toast notification function
            function showToast(message, type = 'success') {
                const toastContainer = document.getElementById('toastContainer');
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.textContent = message;
                
                toastContainer.appendChild(toast);
                
                // Trigger reflow to enable transition
                toast.offsetHeight;
                
                // Show toast
                toast.classList.add('show');
                
                // Auto hide after 3 seconds
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        toast.remove();
                    }, 300);
                }, 3000);
            }
            
            // Favorite button functionality
            const favoriteButtons = document.querySelectorAll('.favorite-btn');
            favoriteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    <?php if (!$isLoggedIn): ?>
                        showToast('Please log in to add favorites', 'error');
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 1500);
                        return;
                    <?php endif; ?>
                    
                    const productId = this.getAttribute('data-product-id');
                    
                    // Create form data
                    const formData = new FormData();
                    formData.append('product_id', productId);
                    
                    // Send AJAX request
                    fetch('api/toggle_favorite.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.action === 'added') {
                                this.classList.add('active');
                                showToast('Added to favorites');
                            } else {
                                this.classList.remove('active');
                                showToast('Removed from favorites');
                            }
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        
                    });
                });
            });
            
            // Quantity selector functionality
            const decreaseButtons = document.querySelectorAll('.decrease');
            const increaseButtons = document.querySelectorAll('.increase');
            const quantityInputs = document.querySelectorAll('.quantity');
            
            decreaseButtons.forEach((button, index) => {
                button.addEventListener('click', function() {
                    let value = parseInt(quantityInputs[index].value);
                    if (value > 1) {
                        quantityInputs[index].value = value - 1;
                    }
                });
            });
            
            increaseButtons.forEach((button, index) => {
                button.addEventListener('click', function() {
                    let value = parseInt(quantityInputs[index].value);
                    quantityInputs[index].value = value + 1;
                });
            });
            
            // Carousel navigation
            const carousel = document.getElementById('productCarousel');
            const prevBtn = document.querySelector('.prev');
            const nextBtn = document.querySelector('.next');
            
            prevBtn.addEventListener('click', function() {
                carousel.scrollBy({ left: -240, behavior: 'smooth' });
            });
            
            nextBtn.addEventListener('click', function() {
                carousel.scrollBy({ left: 240, behavior: 'smooth' });
            });
            
            // Add to cart functionality
            const addToCartButtons = document.querySelectorAll('.add-to-cart');
            addToCartButtons.forEach((button, index) => {
                button.addEventListener('click', function() {
                    <?php if (!$isLoggedIn): ?>
                        showToast('Please log in to add items to cart', 'error');
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 1500);
                        return;
                    <?php endif; ?>
                    
                    const productId = this.getAttribute('data-product-id');
                    const quantity = quantityInputs[index].value;
                    
                    // Create form data
                    const formData = new FormData();
                    formData.append('product_id', productId);
                    formData.append('quantity', quantity);
                    
                    // Send AJAX request
                    fetch('api/add_to_cart.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message);
                            
                            // Update cart count
                            const cartBadge = document.querySelector('.cart-badge');
                            let cartCount = cartBadge.querySelector('.cart-count');
                            
                            if (data.cart_count > 0) {
                                if (!cartCount) {
                                    cartCount = document.createElement('span');
                                    cartCount.className = 'cart-count';
                                    cartBadge.appendChild(cartCount);
                                }
                                cartCount.textContent = data.cart_count;
                            } else if (cartCount) {
                                cartCount.remove();
                            }
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        
                    });
                });
            });
            
            // Make search icon clickable (add functionality if needed)
            const searchIcon = document.getElementById('search-icon');
            if (searchIcon) {
                searchIcon.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Add search functionality here if needed
                    showToast('Search feature coming soon');
                });
            }
        });
    </script>
    <?php
// Display cart items
if (isset($_SESSION['id'])) {
    $db_id = (int)$_SESSION['id'];
    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, (c.quantity * p.price) as total 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.db_id = ?
    ");
    $stmt->bind_param("i", $db_id);
    $stmt->execute();
    $cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    if (!empty($cart_items)) {
        echo '<h2>Your Cart</h2>';
        echo '<table>';
        echo '<tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th></tr>';
        
        $grand_total = 0;
        foreach ($cart_items as $item) {
            echo '<tr>';
            echo '<td>'.htmlspecialchars($item['name']).'</td>';
            echo '<td>$'.number_format($item['price'], 2).'</td>';
            echo '<td>'.$item['quantity'].'</td>';
            echo '<td>$'.number_format($item['total'], 2).'</td>';
            echo '</tr>';
            $grand_total += $item['total'];
        }
        
        echo '<tr><td colspan="3"><strong>Grand Total</strong></td><td><strong>$'.number_format($grand_total, 2).'</strong></td></tr>';
        echo '</table>';
    } else {
        echo '<p>Your cart is empty</p>';
    }
}
?>
</body>
</html>