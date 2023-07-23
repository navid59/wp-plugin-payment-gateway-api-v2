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
    
    // console.log("ntp Username : "+ntpUsername);
    // console.log("ntp Pass : "+ntpPassword);
    
    
    // Call WP Rest API and return Credential:
    sendFormData(ntpUsername, ntpPassword)
    .then(function(response) {
      document.getElementById('ntpLoader').style.display = "none";
      // Access the properties and values in the response object
      console.log(response);
      if(response.status) {
        document.getElementById("ntpPlatformAuthAlarm").style.display = "none";
        document.getElementById("ntpPlatformLoginForm").style.display = "none";
        document.getElementById("ntpPlatformCredentialDataForm").style.display = "block";

          // Populate section 1: Signature
          createSignatureRadioOptions('signatureList', response.signature);

          // Populate section 2: apiKeyLive
          createApiKeyRadioOptions('apiKeyLiveList', response.apiKeyLive);

          // Populate section 3: apiKeySandbox
          createApiKeyRadioOptions('apiKeySandboxList', response.apiKeySandbox);

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

// Create Signature radio box options 
function createSignatureRadioOptions(sectionId, dataArray) {
  var section = document.getElementById(sectionId);

  // Check if dataArray is empty
  if (!Array.isArray(dataArray) || dataArray.length === 0) {
    document.getElementById("ntpSignatoreAlarmContent").innerHTML = 'Unable to get Pos Signature. Make sure if you already have at least one ACTIVE & APPROVED point of sales.';
    document.getElementById('ntpSignatureLoader').style.display = "none";
    document.getElementById('ntpSignatoreAlarm').style.display = "block";
    return;
  }

  dataArray.forEach(function(item) {
    var radioLabel = document.createElement('label');
    var radioInput = document.createElement('input');
    radioInput.type = 'radio';
    radioInput.name = sectionId;
    radioInput.value = item.posSignature;
    radioInput.required = true;

    radioLabel.appendChild(radioInput);
    radioLabel.appendChild(document.createTextNode(item.posSignature));
    section.appendChild(radioLabel);
  });
  document.getElementById('ntpSignatureLoader').style.display = "none";
}

// Create Api Key radio box options 
function createApiKeyRadioOptions(sectionId, dataArray) {
  console.log(dataArray);
  console.log("---------------------");

  var section = document.getElementById(sectionId);

  // Check if dataArray is empty
  if (!Array.isArray(dataArray) || dataArray.length === 0) {
    if(sectionId === 'apiKeyLiveList') {
      document.getElementById("ntpApiKeyLiveAlarmContent").innerHTML = 'Unable to get any Apikey for LIVE. Make sure if you already created one.';
      document.getElementById('ntpApiKeyLiveLoader').style.display = "none";
      document.getElementById('ntpApiKeyLiveAlarm').style.display = "block";
    } else if(sectionId === 'apiKeySandboxList'){
      document.getElementById("ntpApiKeySandboxAlarmContent").innerHTML = 'Unable to get any Apikey for Sandbox. Make sure if you already created one.';
      document.getElementById('ntpApiKeySandboxLoader').style.display = "none";
      document.getElementById('ntpApiKeySandboxAlarm').style.display = "block";
    }    
    return;
  }

  dataArray.forEach(function(item) {
    var radioLabel = document.createElement('label');
    var radioInput = document.createElement('input');
    radioInput.type = 'radio';
    radioInput.name = sectionId;
    radioInput.value = item.key;
    radioInput.required = true;

    radioLabel.appendChild(radioInput);
    radioLabel.appendChild(document.createTextNode(item.key));
    section.appendChild(radioLabel);
  });

  if(sectionId === 'apiKeyLiveList') {
    document.getElementById('ntpApiKeyLiveLoader').style.display = "none";
  } else if(sectionId === 'apiKeySandboxList') {
    document.getElementById('ntpApiKeySandboxLoader').style.display = "none";
  }
}

 // Function to display selected radio box options
 function displaySelected() {
  // Get the selected radio box options from each section
  var selectedSignature = document.querySelector('input[name="signatureList"]:checked');
  var selectedApiKeyLive = document.querySelector('input[name="apiKeyLiveList"]:checked');
  var selectedApiKeySandbox = document.querySelector('input[name="apiKeySandboxList"]:checked');

  // alert("1 ) "+selectedSignature.value);
  // alert("2 ) "+selectedApiKeyLive.value);
  // alert("3) "+selectedApiKeySandbox.value);

  // Validate if the selected options are not empty
  if (!selectedSignature && !selectedApiKeyLive && !selectedApiKeySandbox) {
    alert('Please select at least one option.');
    return;
  }

  // Create a message displaying the selected options
  var message = "Selected Options:\n";
  if (selectedSignature) {
    message += "Signature: " + selectedSignature.value + "\n";
  }
  if (selectedApiKeyLive) {
    message += "API Key for production environment: " + selectedApiKeyLive.value + "\n";
  }
  if (selectedApiKeySandbox) {
    message += "API Key for sandbox environment: " + selectedApiKeySandbox.value + "\n";
  }

  // Display the selected options
  var selectedOptionsDiv = document.getElementById('selectedOptions');
  selectedOptionsDiv.innerText = message;

  alert("Data will send to Wp Rest API");
  // self.close();
  // document.getElementById('woocommerce_netopiapayments_account_id').value = "This is the data what is changed";

  // Display the response of wp update endpoint 
  var wpRestResponse = document.getElementById('wpRestResponse');
    
  // Send the selected values to another URL (replace 'your_url' with the actual URL)
  var formData = new FormData();
  if (selectedSignature) {
    formData.append('signature', selectedSignature.value);
  }
  if (selectedApiKeyLive) {
    formData.append('apiKeyLive', selectedApiKeyLive.value);
  }
  if (selectedApiKeySandbox) {
    formData.append('apiKeySandbox', selectedApiKeySandbox.value);
  }

  // Perform the form update the credential Data
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'http://localhost/paymentGatewayApi2/index.php/wp-json/netopiapayments/v1/updatecredential/', true);
  xhr.onload = function () {
    if (xhr.status === 200) {
      // Display the result of the request
      var response = JSON.parse(xhr.responseText);
      wpRestResponse.innerText += '\n\nResponse from server1) :\n' + response;
      // wpRestResponse.innerText += '\n\nResponse from server1) :\n' + response.data.params.signature;
      // wpRestResponse.innerText += '\n\nResponse from server2) :\n' + response.data.params.apiKeyLive;
      // wpRestResponse.innerText += '\n\nResponse from server3) :\n' + response.data.params.apiKeySandbox;
      console.log(response);
    } else {
      // Log error if there is an issue with the request
      console.error('Error occurred:', xhr.statusText);
    }
  };
  xhr.onerror = function () {
    // Log error if there is a network error
    console.error('Network error occurred.');
  };
  xhr.send(formData);
  

}

  
  
