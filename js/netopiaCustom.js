document.addEventListener('DOMContentLoaded', function() {
    // var popupLink = document.getElementById('popup-link');
    var popupLink = document.getElementById('woocommerce_netopiapayments_wizard_button');
  
    popupLink.addEventListener('click', function(e) {
        var netopiaUIPath_dataPluginUrl = netopiaUIPath_data.plugin_url;
        var netopiaUIPath_dataSiteUrl = netopiaUIPath_data.site_url;
        console.log(netopiaUIPath_dataPluginUrl);
        console.log(netopiaUIPath_dataSiteUrl);
      e.preventDefault();
      openPopupWindow(netopiaUIPath_dataSiteUrl + netopiaUIPath_dataPluginUrl+'view/login.php', 'Popup Form', 700, 700);
    });
  });
  
  function openPopupWindow(url, title, width, height) {
    var left = (window.innerWidth - width) / 2;
    var top = (window.innerHeight - height) / 2;
    var options = 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + width + ', height=' + height + ', top=' + top + ', left=' + left;
    window.open(url, title, options);
  }