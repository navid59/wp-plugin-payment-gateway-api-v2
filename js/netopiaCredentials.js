document.addEventListener('DOMContentLoaded', function() {
    
    var loginButton = document.getElementById('loginToNetopiaPlatform');
    loginButton.addEventListener('click', function(e){
      e.preventDefault();
      document.getElementById('ntpLoader').style.display = "block";
      document.getElementById('ntpPlatformAuthAlarm').style.display = "none";
      getNetopiaPlatformCredentials();
    });
  });
  

  function getNetopiaPlatformCredentials(){
    var ntpUsername = document.getElementById('ntpUsername').value;
    var ntpPassword = document.getElementById('ntpPassword').value;
    
    console.log("Just a test for Credential test");
    console.log("ntp Username : "+ntpUsername);
    console.log("ntp Pass : "+ntpPassword);
    console.log(" -----C---C--C---C---C--C---C--- ");
    
    // Call WP Rest API and return Credential:
    sendFormData(ntpUsername, ntpPassword)
    .then(function(response) {
      document.getElementById('ntpLoader').style.display = "none";
      // Access the properties and values in the response object
      console.log(response);
      if(response.status) {
        document.getElementById("ntpPlatformAuthAlarm").style.display = "none";
      } else {
        document.getElementById("ntpPlatformAuthAlarmContent").innerHTML = response.message;
        document.getElementById("ntpPlatformAuthAlarm").style.display = "block";
      }
    })
    .catch(function(error) {
      document.getElementById('ntpLoader').style.display = "none";
      console.log(error);
    });
}


function sendFormData(username, password) {
  return new Promise(function(resolve, reject) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'http://localhost/paymentGatewayApi2/index.php/wp-json/netopiapayments/v1/credential/', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
      if (xhr.status === 200) {
        var jsonResponse = JSON.parse(xhr.responseText);
        resolve(jsonResponse);
      } else {
        reject('Request failed. Status: ' + xhr.status);
      }
    };

    xhr.onerror = function() {
      reject('Request error');
    };

    var requestBody = 'username=' + encodeURIComponent(username) + '&password=' + encodeURIComponent(password);

    xhr.send(requestBody);
  });
}

  
  
