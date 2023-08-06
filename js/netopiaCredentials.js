var netopiaUIPath_dataPluginUrl = netopiaUIPath_data.plugin_url;
var siteUrl = netopiaUIPath_data.site_url;

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
    
    // Call WP Rest API and return Credential:
    getCredentialData(ntpUsername, ntpPassword)
    .then(function(response) {
      document.getElementById('ntpLoader').style.display = "none";

      // Access the properties and values in the response object
      if(response.status) {
        document.getElementById("ntpPlatformAuthAlarm").style.display = "none";
        document.getElementById("ntpPlatformLoginForm").style.display = "none";
        document.getElementById("ntpPlatformCredentialDataForm").style.display = "block";

          // Populate section 1: Signature
          var hasSignature = createSignatureRadioOptions('signatureList', response.signature);

          // Populate section 2: apiKeyLive
          var hasLiveApiKey = createApiKeyRadioOptions('apiKeyLiveList', response.apiKeyLive);

          // Populate section 3: apiKeySandbox
          var hasSandboxApiKey = createApiKeyRadioOptions('apiKeySandboxList', response.apiKeySandbox);

          // to display "try again" btn & disable the "confirm" btn
          if(hasSignature && (hasLiveApiKey || hasSandboxApiKey)) {
            // Do Nothing
          } else {  
            document.getElementById('ntp-confirm-btn').style.display = "none";
            document.getElementById('ntp-try-btn').style.display = "inline-block";
          }

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

// Send Authentiacation data to wp Api in order to get Credential Data
function getCredentialData(username, password) {
  return new Promise(function(resolve, reject) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', siteUrl +'/index.php/wp-json/netopiapayments/v1/credential/', true);
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
    return false;
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
  return true;
}

// Create Api Key radio box options 
function createApiKeyRadioOptions(sectionId, dataArray) {
  // console.log(dataArray);
  // console.log("---------------------");

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
    return false;
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
  return true;
}

// Validate & display selected radio box options
function displaySelected() {
  // Get the selected radio box options from each section
  var selectedSignature = document.querySelector('input[name="signatureList"]:checked');
  var selectedApiKeyLive = document.querySelector('input[name="apiKeyLiveList"]:checked');
  var selectedApiKeySandbox = document.querySelector('input[name="apiKeySandboxList"]:checked');

  // Validate if the selected options are not empty
  if (!selectedSignature || (!selectedApiKeyLive || !selectedApiKeySandbox)) {
    alert('Please select at least one Signature and one API Key.');
    return;
  }

  // change Button name
  document.getElementById('ntp-confirm-btn').innerHTML = 'Confirm / Reselect';
  document.getElementById('selectedOptions').style.display = "block";

  // Create a message displaying the selected options
  if (selectedSignature) {
    document.getElementById('selectedSignatureValue').innerHTML = selectedSignature.value;
  } else {
    document.getElementById('selectedSignatureValue').innerHTML = ' - ';
  }

  if (selectedApiKeyLive) {
    document.getElementById('selectedLiveApiKeyValue').innerHTML = selectedApiKeyLive.value;
  } else {
    document.getElementById('selectedLiveApiKeyValue').innerHTML = ' - ';
  }

  if (selectedApiKeySandbox) {
    document.getElementById('selectedSandboxApiKeyValue').innerHTML = selectedApiKeySandbox.value;
  } else {
    document.getElementById('selectedSandboxApiKeyValue').innerHTML = ' - ';
  }

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

  // Remove privious alaram
  document.getElementById('wpRestResponse').style.display = "none";

  // Perform the form update the credential Data
  var xhr = new XMLHttpRequest();
  xhr.open('POST', siteUrl + '/index.php/wp-json/netopiapayments/v1/updatecredential/', true);
  xhr.onload = function () {
    if (xhr.status === 200) {
      // Display the result of the request
      var response = JSON.parse(xhr.responseText);

      // display message
      wpRestResponse.innerText = 'Configurations updated successfully.';
      document.getElementById('wpRestResponse').style.display = "block";
      console.log(response);

      // to close the windows after 45 secound
      setTimeout(function() {
        window.close();
      }, 5000);

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

// Function to notify the parent window about the popup window close
function notifyParentWindow() {
  // Check if the parent window is available and if it has the 'handlePopupWindowClose' function
  if (window.opener && typeof window.opener.handlePopupWindowClose === 'function') {
    // Call the 'handlePopupWindowClose' function in the parent window
    window.opener.handlePopupWindowClose();
  }
}

// Add the 'beforeunload' event listener to call the 'notifyParentWindow' function before the window is closed
window.onbeforeunload = function() {
  notifyParentWindow();
};
  
  
