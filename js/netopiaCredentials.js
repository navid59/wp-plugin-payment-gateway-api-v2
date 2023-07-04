document.addEventListener('DOMContentLoaded', function() {
    
    var loginButton = document.getElementById('loginToNetopiaPlatform');
    loginButton.addEventListener('click', function(e){
      e.preventDefault();
      getNetopiaPlatformCredentials();
    });
  });
  

  function getNetopiaPlatformCredentials(){
    var ntpUsername = document.getElementById('ntpUsername').value;
    var ntpPassword = document.getElementById('ntpPassword').value;
    
    console.log("Just a test for Credential test");
    console.log("ntp Username : "+ntpUsername);
    console.log("ntp Pass : "+ntpPassword);
    console.log(" -------------------------------- ");

    postData(ntpUsername, ntpPassword)
}

function postData(param1, param2) {
    var url = "https://admin.netopia-payments.com/api/auth/login"; // Replace with the URL to which you want to send the POST request
  
    var data = {
      login: {
        username: "navidTest",
        password: "tamasba118",
        code: ""
      }
    };
  
    var headers = new Headers();
    headers.append("Content-Type", "application/json");
  
    fetch(url, {
      method: "POST",
      headers: headers,
      body: JSON.stringify(data),
      credentials: "include", // Include cookies in the request
      mode: "cors" // Set request mode to "cors"
    })
      .then(response => {
        if (response.ok) {
          return response.json();
        } else {
          throw new Error("Error: " + response.status);
        }
      })
      .then(responseData => {
        console.log("Response:", responseData);
        // Handle the response data here
      })
      .catch(error => {
        console.log("Error:", error.message);
        // Handle any errors that occurred during the request
      });
  }
  
  
