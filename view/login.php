<?php 
  /**
   * 
   * IMPORTANT 
   * To be aproved by wordpress team must be use "wp_enqueue_script"
   */

  require_once('../../../../wp-load.php');
  // echo bloginfo('version')."<br>";
  // echo plugin_dir_url( __FILE__ )."<br>";;
  // echo plugin_dir_url( __DIR__ )."<br>";
  // wp_enqueue_script( 'netopiaCredentials', plugin_dir_url( __DIR__ ) . 'js/netopiaCredentials.js',array(),'1.0' ,true); 
  ?>
<link rel="stylesheet" href="<?php echo plugin_dir_url( __DIR__ ).'/css/custom.css';?>">
<script src="<?php echo plugin_dir_url( __DIR__ ).'/js/netopiaCredentials.js';?>"></script>

  <form class="modal-content animate" method="post">
    <div class="imgcontainer">
      <span onclick="self.close();" class="close" title="Close Modal">&times;</span>
      <img src="../img/NETOPIA_Payments.svg" alt="Avatar" class="avatar" width="250px">
    </div>

    <div class="container">
      <label for="username"><b>Username</b></label>
      <input type="text" placeholder="Enter Username" id="ntpUsername" name="username" required>

      <label for="password"><b>Password</b></label>
      <input type="password" placeholder="Enter Password" id="ntpPassword" name="password" required>
        
      <button type="buttion" id="loginToNetopiaPlatform">Login</button>
    </div>

    <div class="container" style="background-color:#f1f1f1">
      <button type="button" onclick="self.close();" class="cancelbtn">Cancel</button>
      <span class="psw"><a href="https://admin.netopia-payments.com/reset-password" target="_blank">Forgot password?</a></span>
    </div>
  </form>