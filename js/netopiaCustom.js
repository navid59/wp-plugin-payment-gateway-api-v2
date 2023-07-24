document.addEventListener('DOMContentLoaded', function() {
    var popupLink = document.getElementById('woocommerce_netopiapayments_wizard_button');
    popupLink.addEventListener('click', function(e) {
        var netopiaUIPath_dataPluginUrl = netopiaUIPath_data.plugin_url;
        var netopiaUIPath_dataSiteUrl = netopiaUIPath_data.site_url;
        console.log(netopiaUIPath_dataPluginUrl);
        console.log(netopiaUIPath_dataSiteUrl);
      e.preventDefault();
      openPopupWindow(netopiaUIPath_dataSiteUrl + netopiaUIPath_dataPluginUrl+'view/login.php', 'Popup Form', 700, 700);
    });

    // var loginButton = document.getElementById('loginToNetopiaPlatform');
    // loginButton.addEventListener('click', function(e){
    //   e.preventDefault();
    //   getNetopiaPlatformCredentials();
    // });
  });
  
  function openPopupWindow(url, title, width, height) {
    var left = (window.innerWidth - width) / 2;
    var top = (window.innerHeight - height) / 2;
    var options = 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + width + ', height=' + height + ', top=' + top + ', left=' + left;
    window.open(url, title, options);
  }

  // Function to handle the popup window close event
  function handlePopupWindowClose() {
    
    // Reload the parent window (NETOPIA Payments admin page)
    location.reload();


  }