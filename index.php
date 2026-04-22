<?php

session_start();

include("includes/db.php");
include("includes/header.php");
include("functions/functions.php");
include("includes/main.php");

?>


  <!-- Cover -->
  <main>
    <div class="hero">
      <a href="shop.php" class="btn1"><b>View all products</b>
</a>
<a href="https://bepi.mpob.gov.my/admin2/price_local_daily_view_cpo_msia.php?more=Y&jenis=6M" class="btn1"><b>Malaysia Prices Of Crude Palm Oil</b>
</a>
<!-- Palm Oil Scanner Button -->
<a href="palm_detector.php" class="btn1"><b>Palm Oil Scanner</b></a>




    </div>
    <!-- Main -->
    <div class="wrapper">
            <h1> <b><font color="#01200b">SAWIT SHOPS </b><h1></font>
                
            
      </div>



    <div id="content" class="container"><!-- container Starts -->

    <div class="row"><!-- row Starts -->

    <?php

    getPro();

    ?>

    </div><!-- row Ends -->

    </div><!-- container Ends -->
    <!-- FOOTER -->
    <footer class="page-footer">
    <div class="footer-nav">
		<div class="container clearfix">

	

		</div>
	</div>

     




      <!-- <div class="banners">
        <div class="container clearfix">

          <div class="banner-award">
            <span>Award winner</span><br> Fashion awards 2016
          </div>

          <div class="banner-social">
            <a href="#" class="banner-social__link">
            <i class="icon-facebook"></i>
          </a>
            <a href="#" class="banner-social__link">
            <i class="icon-twitter"></i>
          </a>
            <a href="#" class="banner-social__link">
            <i class="icon-instagram"></i>
          </a>
            <a href="#" class="banner-social__link">
            <i class="icon-pinterest-circled"></i>
          </a>
          </div>

        </div>
      </div> -->

      <div class="page-footer__subline">
        <div class="container clearfix">

          <div class="copyright">
            &copy; <?php echo date("Y");?> MaiSawit 2024&trade;
          </div>

          <div class="developer">
            Developed by MaiSawit
          </div>

          <div class="designby">
            Design by MaiSawit
          </div>

        </div>
      </div>
    </footer>
</body>

</html>
<?php
if (isset($_POST['run_detector'])) {
    $python = 'C:\\Users\\Asus\\AppData\\Local\\Microsoft\\WindowsApps\\python3.exe'; // Python path
    $script = 'C:\\xampp\\htdocs\\mysawit\\palm_detector.py'; // Path to your Python script
    $command = escapeshellcmd("$python $script");
    pclose(popen("start /B " . $command, "r")); // Non-blocking call for Windows
}
?>