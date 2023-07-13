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
    console.log(" -----C---C--C---C---C--B---B--- ");

    // postData(ntpUsername, ntpPassword)
    sendFormData(ntpUsername, ntpPassword)
}

// function postData(ntpUsername, ntpPassword) {
//     // var url = "http://localhost/paymentGatewayApi2/index.php/wp-json/netopiapayments/v1/credential/";
//     var url = "../../../../index.php/wp-json/netopiapayments/v1/credential/";
  
//     var data = {
//       login: {
//         username: ntpUsername,
//         password: ntpPassword
//       }
//     };
  
//     var headers = new Headers();
//     headers.append("Content-Type", "application/json");
  
//     fetch(url, {
//       method: "POST",
//       headers: headers,
//       body: JSON.stringify(data),
//       credentials: "include", // Include cookies in the request
//       mode: "cors" // Set request mode to "cors"
//     })
//       .then(response => {
//         if (response.ok) {
//           return response.json();
//         } else {
//           throw new Error("Error: " + response.status);
//         }
//       })
//       .then(responseData => {
//         console.log("Response:", responseData);
//         // Handle the response data here
//       })
//       .catch(error => {
//         console.log("Error:", error.message);
//         // Handle any errors that occurred during the request
//       });
//   }

function sendFormData(username, password) {
  // Create the form data object
  var formData = new FormData();
  formData.append('username', username);
  formData.append('password', password);

  // Perform the POST request
  fetch('../../../../index.php/wp-json/netopiapayments/v1/credential', {
      method: 'POST',
      body: formData
  })
  .then(function(response) {
      return response.json();
  })
  .then(function(data) {
      // Handle the response data
      console.log(data);
  })
  .catch(function(error) {
      console.log('Error:', error);
  });
}

  
  
