<?php
session_start();

// Initialize variables
$name = $phone = $email = $message = "";
$nameErr = $phoneErr = $emailErr = $messageErr = "";
$success = "";
$error = "";

// Check for success message
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = "Thank you for contacting us. We will get back to you soon!";
}

// Check for error message
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

// Check for form validation errors
if (isset($_GET['form_error']) && $_GET['form_error'] == 1 && isset($_SESSION['form_errors'])) {
    $nameErr = $_SESSION['form_errors']['nameErr'];
    $phoneErr = $_SESSION['form_errors']['phoneErr'];
    $emailErr = $_SESSION['form_errors']['emailErr'];
    $messageErr = $_SESSION['form_errors']['messageErr'];
    
    // Retrieve form data
    $name = $_SESSION['form_errors']['form_data']['name'];
    $phone = $_SESSION['form_errors']['form_data']['phone'];
    $email = $_SESSION['form_errors']['form_data']['email'];
    $message = $_SESSION['form_errors']['form_data']['message'];
    
    // Clear session data
    unset($_SESSION['form_errors']);
}
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
      <title>Contact</title>
      <meta name="keywords" content="">
      <meta name="description" content="">
      <meta name="author" content="">
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
      <style>
        .text-danger {
          color: #dc3545;
          font-size: 14px;
          margin-top: 5px;
          display: block;
        }
        .alert {
          padding: 15px;
          margin-bottom: 20px;
          border: 1px solid transparent;
          border-radius: 4px;
        }
        .alert-success {
          color: #155724;
          background-color: #d4edda;
          border-color: #c3e6cb;
        }
        .is-invalid {
          border-color: #dc3545 !important;
        }
      </style>
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
              
              <!-- Toggler for mobile -->
              <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
              </button>
              
              <!-- menu -->
              <div class="collapse navbar-collapse" id="navbarContent">
               
                <ul class="navbar-nav mr-auto">
                  <li class="nav-item">
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
                  <li class="nav-item active">
                    <a class="nav-link" href="contact.php">Contact Us</a>
                  </li>
                </ul>
                
                <!-- Right-aligned items -->
                <ul class="navbar-nav ml-auto search_section">
                  <li class="nav-item">
                    <a class="nav-link" href="#">Log In</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="#"><img src="images/shopping-bag.png" alt="Cart"></a>
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
      
      <!-- contact section start -->
      <div class="contact_section layout_padding">
         <div class="container">
            <h1 class="touch_taital">Contact Us</h1>
            
            <?php if(!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <div class="contact_section_2">
               <div class="row">
                  <div class="col-md-6">
                     <div class="email_text">
                        <form method="post" action="send.php">
                           <div class="form-group">
                              <input type="text" class="email-bt <?php echo (!empty($nameErr)) ? 'is-invalid' : ''; ?>" 
                                 placeholder="Name" name="name" value="<?php echo $name; ?>">
                              <span class="text-danger"><?php echo $nameErr; ?></span>
                           </div>
                           <div class="form-group">
                              <input type="text" class="email-bt <?php echo (!empty($phoneErr)) ? 'is-invalid' : ''; ?>" 
                                 placeholder="Phone Number" name="phone" value="<?php echo $phone; ?>">
                              <span class="text-danger"><?php echo $phoneErr; ?></span>
                           </div>
                           <div class="form-group">
                              <input type="text" class="email-bt <?php echo (!empty($emailErr)) ? 'is-invalid' : ''; ?>" 
                                 placeholder="Email" name="email" value="<?php echo $email; ?>">
                              <span class="text-danger"><?php echo $emailErr; ?></span>
                           </div>
                           <div class="form-group">
                              <textarea class="massage-bt <?php echo (!empty($messageErr)) ? 'is-invalid' : ''; ?>" 
                                 placeholder="Message" rows="5" name="message"><?php echo $message; ?></textarea>
                              <span class="text-danger"><?php echo $messageErr; ?></span>
                           </div>
                           <div class="send_btn">
                              <button type="submit" class="btn">SEND</button>
                           </div>
                        </form>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="map_main">
                        <div class="map-responsive">
                           <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3190.6874465022397!2d10.187512275307679!3d36.89782476219125!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12fd34cc25bd5aff%3A0x495e852ae57f3ff5!2sTEK-UP%20University!5e0!3m2!1sfr!2stn!4v1744970803567!5m2!1sfr!2stn"  width="600" height="400" frameborder="0" style="border:0; width: 100%;" allowfullscreen=""></iframe>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- contact section end -->
      
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
      <script src="https:cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.js"></script> 
      <script type="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2//2.0.0-beta.2.4/owl.carousel.min.js"></script>
      <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
      <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
      <script src="../../assets/js/vendor/popper.min.js"></script>
      <script src="../../dist/js/bootstrap.min.js"></script>
   </body>
</html>