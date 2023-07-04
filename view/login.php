<link rel="stylesheet" href="../css/custom.css">
<script src="../js/netopiaCredentials.js"></script>
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