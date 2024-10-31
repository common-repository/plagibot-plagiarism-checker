<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $wp_version;
global $post;

if( (int) $_GET['p'] > 0 ){
   $post_id = $_GET['p'];
   $post    = get_post($post_id);
}
else
   exit();


?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> style="font-size:100%; margin-top:0 !important">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title><?php echo __( 'Plagibot Plagiarism Checker', 'plagibot' ) . ' | ' . get_the_title(); ?></title>
	<?php 

      

      //deregister unnecessary scripts
      add_action( 'wp_enqueue_scripts', 'plagibot_remove_script', PHP_INT_MAX - 1 );
      function plagibot_remove_script() {

         global $wp_scripts;
         foreach( $wp_scripts->queue as $handle ) :
            wp_deregister_script($handle);
         endforeach;

         global $wp_styles;
         foreach( $wp_styles->queue as $style ) {
            wp_dequeue_style( $style );
         }

         wp_enqueue_script('jquery');
         
         wp_enqueue_script("plagibot-bootstrap",  WPPBPC_URL . 'assets/js/bootstrap.min.js',[], '5.2.2', false  );
         wp_enqueue_script("plagibot-script",  'https://plagibot-3744.kxcdn.com/assets/js/plagiarism.min.js',[], '1.0.0', false  );
         
         

         wp_enqueue_style("plagibot-theme-css",  WPPBPC_URL . 'assets/css/theme.min.css' );
         wp_enqueue_style("plagibot-style-css",  WPPBPC_URL . 'assets/css/style.min.css' );

         

      }

      

      wp_head(); 
   ?>

   
   <script>
      $ = jQuery;
      $(document).ready(function(){

         $("a.btn").click(function(){
            $(this).addClass('disabled').html("<i class='spinner-border spinner-border-sm'></i> " + $(this).text());
         })

      })
   </script>

</head>
<body class="plagibot-plagiarism-checker wp-version-<?php echo esc_attr($wp_version);?>">

  <!-- ========== MAIN CONTENT ========== -->
  <main id="content" role="main" class="main">





<div class="container container-xxl content-space-t-2 content-space-t-lg-2  container-mobile">
   <div class="w-xxlg-75 text-center mx-lg-auto mb-7 mb-md-10">

      <div class="row">

         <div class="offset-md-1 offset-lg-1 col-lg-10 col-md-10 mb-4 width-transition" id="search-form">
            <div class="card card-lg  h-100 bg-light border-0 shadow-none overflow-hidden">
               <div class="card-body">

                  <div class="usage mb-3">
                     <span>Your Current Plan Usage: <?php echo esc_attr($response['response_array']['usage']);?>% (<?php echo esc_attr(number_format($response['response_array']['words_searched']));?> out of <?php echo esc_attr(number_format($response['response_array']['words_limit']));?> words limit)</span>
                     <div class="progress">
                           <div class="progress-bar" role="progressbar" style="width: <?php echo esc_attr($response['response_array']['usage']);?>%;" aria-valuenow="<?php echo esc_attr($response['response_array']['usage']);?>" aria-valuemin="0" aria-valuemax="100"></div>
                     </div>                                
                  </div>


                  <div class="searchbox">

                     <div class="progress mt-lg-0 mt-5">
                        <div class="progress-bar bg-primary" role="progressbar"  aria-valuemin="0" aria-valuemax="100" ></div><span>100%</span>
                     </div>

                     <div class="scanning">
                        <span><i class='spinner-border spinner-border-sm text-primary'></i> Scanning...</span> 
                     </div>


                     <div class="highlight form-control">

                     </div>              

                     <form id="plagiarism-form" method="POST" data-url="/wp-admin/admin-ajax.php" data-action="parse_text">
                        <div class="" style="position:relative">
                              <textarea id="plagiarismText" required class="form-control" placeholder="Enter your text here" rows="13"><?php echo esc_attr(strip_tags(str_replace(array("<br>", "<br />", "<br/>"), "\n", get_the_content())));?></textarea>
                              <small id='word-count'></small>

                        </div>
                        <div class="mb-3">
                           <?php if( $response['response_array']['usage'] > 99 ){?>
                              <p>You have reached your search limit. Please <a target="_blank" href="https://plagibot.com/signin">update your plan</a>.</p>
                              <p class=" mt-3"><a class="btn btn-primary" href="/wp-admin/post.php?post=<?php echo  esc_attr($post_id); ?>&action=edit">Go Back</a></p>
                           <?php } else { ?>
                              <button type="submit" class="btn btn-primary btn-submit" >Search</button>
                           <?php } ?>
                              
                        </div>
                     
                     </form>

                     <p class="new-search mt-3"><a class="btn btn-primary" href="/wp-admin/post.php?post=<?php echo esc_attr($post_id); ?>&action=edit">Go Back</a></p>

                  </div>                
               </div>
            </div>
         </div>
         <!-- End Col -->

         <div class="  col-md-5 col-xl-5 mb-4 result-container">
         <!-- Card -->
         <div class="card card-lg h-100 bg-light border-0 shadow-none overflow-hidden" href="#">

            <div class="card-header ">
               <div class="row col-divider">
                  <div class="col-6 text-center text-sm-end">
                  <span class="d-block h1 mb-0 " id="plagiarized-content-percent"></span>
                  <span class="d-block">Plagiarized</span>
                  </div>
                  <!-- End Col -->

                  <div class="col-6 text-center text-sm-start">
                  <span class="d-block h1 text-success mb-0" id="uniquie-content-percent"></span>
                  <span class="d-block">Unique</span>
                  </div>
                  <!-- End Col -->
            </div>
            <!-- End Row -->
            </div>

            <div class="card-header slider-header">

                  <div><a id="download-report" href="#">Download Report</a></div>
                  <div class="carousel-arrow">
                     <a class="carousel-control-prev" href="#carouselMatchResults" role="button" data-slide="prev">
                     <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                     </a>
                     <div id="slide-number"><span id="current-slide">1</span> of <span id="total-slides">1</span></div>
                     <a class="carousel-control-next" href="#carouselMatchResults" role="button" data-slide="next">
                     <span class="carousel-control-next-icon" aria-hidden="true"></span>
                     </a>
                  </div>
            </div>
            <div class="card-body">
               <div id="carouselMatchResults" class="carousel slide" data-interval="false" data-ride="carousel" data-wrap="false">
                  <div class="carousel-inner">
                  </div>
               </div>
            
            </div>
         </div>
         <!-- End Card -->
         </div>
         <!-- End Col -->

      </div>

   </div>
</div>





  </main>
  <!-- ========== END MAIN CONTENT ========== -->

</body>
</html>
