<?php
include 'check.inc.php';
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
      <title>Caraft</title>
  
      <!-- owl carousel style -->
      <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.0.0-beta.2.4/assets/owl.carousel.min.css" />
      <!-- bootstrap css -->
      <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
      <!-- style css -->
      <link rel="stylesheet" type="text/css" href="css/style.css">
      <!-- Responsive-->
      <link rel="stylesheet" href="css/responsive.css">
      <!-- fevicon -->
      <link rel="icon" href="images/fevicon.png" type="image/gif" />
      <!-- Scrollbar Custom CSS -->
      <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
      <!-- Tweaks for older IEs-->
      <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
      <!-- owl stylesheets --> 
      <link rel="stylesheet" href="css/owl.carousel.min.css">
      <link rel="stylesheet" href="css/owl.theme.default.min.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css" media="screen">
 
   </head>
   <body>
      <!--header section start -->
      <div class="header_section">
  <div class="container">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <!-- Logo -->
      <a class="navbar-brand" href="index.php">
        <img src="images/logo.png" alt="Caraft">
      </a>
      
      <!-- Toggler  -->
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      
    
      <div class="collapse navbar-collapse" id="navbarContent">
        <!-- Main navigation links -->
        <ul class="navbar-nav mr-auto">
          <li class="nav-item active">
            <a class="nav-link" href="index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="category.html">Category</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="products.php">Products</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="clients.html">Client</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="contact.php">Contact Us</a>
          </li>
        </ul>
        
        <!-- Dropdown for login/logout -->
        <ul class="navbar-nav ml-auto search_section">
          <?php if(isLoggedIn()): ?>
            <!--  -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?php echo getUserName(); ?>
              </a>
              <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="profile.php">My Profile</a>
                <a class="dropdown-item" href="order.php">My Orders</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="logout.php">Logout</a>
              </div>
            </li>
          <?php else: ?>
            <!--  -->
            <li class="nav-item">
              <a class="nav-link" href="#" data-toggle="modal" data-target="#loginModal">Log In</a>
            </li>
          <?php endif; ?>
          
          <li class="nav-item">
            <a class="nav-link" href="order.php"><img src="images/shopping-bag.png" alt="Cart"></a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#"><img src="images/search-icon.png" alt="Search"></a>
          </li>
        </ul>
      </div>
    </nav>
  </div>
</div>
         <!--banner section start -->
         <div class="banner_section layout_padding">
            <div id="my_slider" class="carousel slide" data-ride="carousel">
               <div class="carousel-inner">
                  <div class="carousel-item active">
                     <div class="container">
                        <div class="row">
                           <div class="col-md-6">
                              <div class="taital_main">
                                 <h4 class="banner_taital"><span class="font_size_90">50%</span> Discount Online Shop</h4>
                                 <p class="banner_text">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration </p>
                                 <div class="book_bt"><a href="#">Shop Now</a></div>
                              </div>
                           </div>
                           <div class="col-md-6">
                              <div><img src="images/img-1.png" class="image_1"></div>
                           </div>
                        </div>
                        <div class="button_main"><button class="all_text">All</button><input type="text" class="Enter_text" placeholder="Enter keywords" name=""><button class="search_text">Search</button></div>
                     </div>
                  </div>
                  <div class="carousel-item">
                     <div class="container">
                        <div class="row">
                           <div class="col-md-6">
                              <div class="taital_main">
                                 <h4 class="banner_taital"><span class="font_size_90">50%</span> Discount Online Shop</h4>
                                 <p class="banner_text">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration </p>
                                 <div class="book_bt"><a href="#">Shop Now</a></div>
                              </div>
                           </div>
                           <div class="col-md-6">
                              <div><img src="images/img-1.png" class="image_1"></div>
                           </div>
                        </div>
                        <div class="button_main"><button class="all_text">All</button><input type="text" class="Enter_text" placeholder="Enter keywords" name=""><button class="search_text">Search</button></div>
                     </div>
                  </div>
                  <div class="carousel-item">
                     <div class="container">
                        <div class="row">
                           <div class="col-md-6">
                              <div class="taital_main">
                                 <h4 class="banner_taital"><span class="font_size_90">50%</span> Discount Online Shop</h4>
                                 <p class="banner_text">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration </p>
                                 <div class="book_bt"><a href="#">Shop Now</a></div>
                              </div>
                           </div>
                           <div class="col-md-6">
                              <div><img src="images/img-1.png" class="image_1"></div>
                           </div>
                        </div>
                        <div class="button_main"><button class="all_text">All</button><input type="text" class="Enter_text" placeholder="Enter keywords" name=""><button class="search_text">Search</button></div>
                     </div>
                  </div>
               </div>
               <a class="carousel-control-prev" href="#my_slider" role="button" data-slide="prev">
               <i class=""><img src="images/left-icon.png"></i>
               </a>
               <a class="carousel-control-next" href="#my_slider" role="button" data-slide="next">
               <i class=""><img src="images/right-icon.png"></i>
               </a>
            </div>
         </div>
         <!--banner section end -->
      </div>
      <!--header section end -->

      <!-- Login Modal -->
      <div class="modal fade login-modal" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loginModalLabel">Welcome Back</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- login error  -->
        <?php if(isset($_SESSION['login_error'])): ?>
          <div class="alert alert-danger">
            <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
          </div>
        <?php endif; ?>
        
        <!-- The form  -->
        <form class="login-form" action="login.php" method="post">
          <div class="form-group">
            <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" required>
          </div>
          <div class="form-group">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
          </div>
          <button type="submit" class="btn login-btn">Sign In</button>
        </form>
        
        <div class="forgot-password">
          <a href="#">Forgot password?</a>
        </div>
        
        <div class="divider">
          <div class="divider-line"></div>
          <span class="divider-text">OR</span>
          <div class="divider-line"></div>
        </div>
        
        <div class="alternate-login">
          <button type="button" class="btn">
            <img src="https://cdn-icons-png.flaticon.com/512/281/281764.png" alt="Google" class="social-icon-login">
          </button>
          <button type="button" class="btn">
            <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" class="social-icon-login">
          </button>
          <button type="button" class="btn">
            <img src="https://cdn-icons-png.flaticon.com/512/0/747.png" alt="Apple" class="social-icon-login">
          </button>
        </div>
        
        <div class="create-account">
          Don't have an account? <a href="#" data-toggle="modal" data-target="#signupModal" data-dismiss="modal">Sign up</a>
        </div>
      </div>
    </div>
  </div>
</div>
      
      <!-- Signup Modal -->
      <div class="modal fade login-modal" id="signupModal" tabindex="-1" role="dialog" aria-labelledby="signupModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="signupModalLabel">Create Account</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Display signup error message if any -->
        <?php if(isset($_SESSION['signup_error'])): ?>
          <div class="alert alert-danger">
            <?php echo $_SESSION['signup_error']; unset($_SESSION['signup_error']); ?>
          </div>
        <?php endif; ?>
        
        <!-- Display signup success message if any -->
        <?php if(isset($_SESSION['signup_success'])): ?>
          <div class="alert alert-success">
            <?php echo $_SESSION['signup_success']; unset($_SESSION['signup_success']); ?>
          </div>
        <?php endif; ?>
        
        <!-- The form now points to signup_process.php -->
        <form class="login-form" action="signup.php" method="post">
          <div class="form-group">
            <input type="text" class="form-control" id="fullName" name="fullName" placeholder="Full Name" required>
          </div>
          <div class="form-group">
            <input type="email" class="form-control" id="signupEmail" name="signupEmail" placeholder="Email Address" required>
          </div>
          <div class="form-group">
            <input type="password" class="form-control" id="signupPassword" name="signupPassword" placeholder="Password" required minlength="6">
          </div>
          <div class="form-group">
            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required>
          </div>
          
          <button type="submit" class="btn login-btn">Create Account</button>
        </form>
        
        <div class="divider">
          <div class="divider-line"></div>
          <span class="divider-text">OR</span>
          <div class="divider-line"></div>
        </div>
        
        <div class="alternate-login">
          <button type="button" class="btn">
            <img src="https://cdn-icons-png.flaticon.com/512/281/281764.png" alt="Google" class="social-icon-login">
          </button>
          <button type="button" class="btn">
            <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" class="social-icon-login">
          </button>
          <button type="button" class="btn">
            <img src="https://cdn-icons-png.flaticon.com/512/0/747.png" alt="Apple" class="social-icon-login">
          </button>
        </div>
        
        <div class="create-account">
          Already have an account? <a href="#" data-toggle="modal" data-target="#loginModal" data-dismiss="modal">Sign in</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!--  Category Section Start -->
<section class="category_section_modern">
   <div class="container">
     <div class="category_header">
       <h2 class="category_title">Shop by <span>Category</span></h2>
       <p class="category_subtitle">Explore our wide range of products organized into categories for easy shopping</p>
     </div>
     
     <div class="category_grid">
       <div class="category_card category_featured">
         <img src="images/im1.webp" alt="Fashion" class="category_image">
         <div class="category_overlay">
           <h3 class="category_name">Fashion</h3>
           <p class="category_count">120 products</p>
         </div>
         <a href="category.html?id=fashion" class="category_link"></a>
       </div>
       
       <div class="category_card">
         <img src="images/im4.webp" alt="Electronics" class="category_image">
         <div class="category_overlay">
           <h3 class="category_name">Electronics</h3>
           <p class="category_count">85 products</p>
         </div>
         <a href="category.html?id=electronics" class="category_link"></a>
       </div>
       
       <div class="category_card">
         <img src="images/im3.webp" alt="Home Decor" class="category_image">
         <div class="category_overlay">
           <h3 class="category_name">Home Decor</h3>
           <p class="category_count">64 products</p>
         </div>
         <a href="category.html?id=home-decor" class="category_link"></a>
       </div>
       
       <div class="category_card">
         <img src="images/im6.webp" alt="Beauty" class="category_image">
         <div class="category_overlay">
           <h3 class="category_name">Beauty</h3>
           <p class="category_count">42 products</p>
         </div>
         <a href="category.html?id=beauty" class="category_link"></a>
       </div>
       
       <div class="category_card">
         <img src="images/im5.webp" alt="Accessories" class="category_image">
         <div class="category_overlay">
           <h3 class="category_name">Accessories</h3>
           <p class="category_count">96 products</p>
         </div>
         <a href="category.html?id=accessories" class="category_link"></a>
       </div>
       
       <div class="category_card">
         <img src="images/im2.webp" alt="Shoes" class="category_image">
         <div class="category_overlay">
           <h3 class="category_name">Shoes</h3>
           <p class="category_count">78 products</p>
         </div>
         <a href="category.html?id=shoes" class="category_link"></a>
       </div>
     </div>
     
     <div class="category_tags">
       <div class="category_tag active">All Categories</div>
       <div class="category_tag">Fashion</div>
       <div class="category_tag">Electronics</div>
       <div class="category_tag">Home Decor</div>
       <div class="category_tag">Beauty</div>
       <div class="category_tag">Accessories</div>
       <div class="category_tag">Shoes</div>
     </div>
     
     <div class="explore_more">
       <a href="categories.html" class="explore_btn">Explore All Categories</a>
     </div>
   </div>
 </section>
 <!-- Category Section End -->



       <!-- About Us Section Start -->
  <div class="about_us_section layout_padding">
   <div class="container">
     <div class="row mb-5">
       <div class="col-md-12 text-center">
         <h1 class="section_title">About <span>Us</span></h1>
         <p class="section_subtitle">Learn about our mission and the services we provide</p>
       </div>
     </div>
     
     <div class="row align-items-center about_us_content mb-5">
       <div class="col-lg-6 col-md-12">
         <div class="about_us_text">
           <h2 class="about_subtitle">Who We Are</h2>
           <p class="about_description">We are a dedicated team of professionals committed to providing high-quality products and exceptional customer service. Since our founding in 2010, we've been passionate about connecting customers with the perfect items that enhance their daily lives.</p>
           <p class="about_description">Our curated collection represents the best in quality, style, and value. We believe shopping should be a delightful experience, and we're here to make that happen for you.</p>
           <div class="about_stats_row">
             <div class="about_stat_item">
               <div class="stat_number">10+</div>
               <div class="stat_label">Years Experience</div>
             </div>
             <div class="about_stat_item">
               <div class="stat_number">5k+</div>
               <div class="stat_label">Happy Customers</div>
             </div>
             <div class="about_stat_item">
               <div class="stat_number">100+</div>
               <div class="stat_label">Products</div>
             </div>
           </div>
         </div>
       </div>
       <div class="col-lg-6 col-md-12">
         <div class="about_us_image">
           <img src="images/undraw_about-us-page_dbh0.svg" alt="About Us" class="img-fluid about_main_image">
         </div>
       </div>
     </div>
     
     <div class="row mb-5">
       <div class="col-md-12 text-center">
         <h2 class="services_title">Our Services</h2>
         <p class="services_subtitle">Discover what sets us apart</p>
       </div>
     </div>
     
     <div class="row services_row">
       <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
         <div class="service_box">
           <div class="service_icon">
             <i class="fa fa-truck"></i>
           </div>
           <h3 class="service_title">Fast Delivery</h3>
           <p class="service_description">We offer quick and reliable shipping for all orders, ensuring your products arrive safely and on time.</p>
         </div>
       </div>
       
       <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
         <div class="service_box">
           <div class="service_icon">
             <i class="fa fa-shield"></i>
           </div>
           <h3 class="service_title">Secure Payments</h3>
           <p class="service_description">Shop with confidence using our secure payment gateway that protects your personal information.</p>
         </div>
       </div>
       
       <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
         <div class="service_box">
           <div class="service_icon">
           <i class="fa fa-phone" aria-hidden="true"></i>
           </div>
           <h3 class="service_title">24/7 Support</h3>
           <p class="service_description">Our dedicated customer service team is available around the clock to assist with any questions or concerns.</p>
         </div>
       </div>
       
       <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
         <div class="service_box">
           <div class="service_icon">
             <i class="fa fa-exchange"></i>
           </div>
           <h3 class="service_title">Easy Returns</h3>
           <p class="service_description">Not satisfied with your purchase? Our hassle-free return policy makes it easy to get a refund or exchange.</p>
         </div>
       </div>
       
       <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
         <div class="service_box">
           <div class="service_icon">
             <i class="fa fa-gift"></i>
           </div>
           <h3 class="service_title">Gift Wrapping</h3>
           <p class="service_description">Make your gift special with our premium gift wrapping service available for all products.</p>
         </div>
       </div>
       
       <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
         <div class="service_box">
           <div class="service_icon">
             <i class="fa fa-certificate"></i>
           </div>
           <h3 class="service_title">Quality Guarantee</h3>
           <p class="service_description">Every product we sell is carefully inspected to ensure it meets our high standards of quality.</p>
         </div>
       </div>
     </div>
     
     <div class="row mt-5">
       <div class="col-md-12 text-center">
         <div class="about_cta">
           <h3 class="cta_title">Ready to experience our exceptional service?</h3>
           <a href="#" class="cta_button">Shop Now</a>
         </div>
       </div>
     </div>
   </div>
 </div>
 <!-- About Us Section End -->
     <!--footer section start -->
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
      <script src="https:cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.js"></script> 
      <script type="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2//2.0.0-beta.2.4/owl.carousel.min.js"></script>
      <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
      <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
      <script src="../../assets/js/vendor/popper.min.js"></script>
      <script src="../../dist/js/bootstrap.min.js"></script>
   </body>
</html>