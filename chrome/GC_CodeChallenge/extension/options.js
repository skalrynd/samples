let form = document.getElementById('APICredsForm');
let actionButton = document.getElementById('saveCredsButton');

actionButton.addEventListener("click", function() {
    clearOptionsMessage();    
    let username = form.querySelectorAll('[name=user]')[0].value;
    let key = form.querySelectorAll('[name=APIKey]')[0].value;
    if (username.length < 1 || key.length < 1) {
        renderOptionsMessage('username and key inputs are required', false);
       return;
    }
    
    chrome.storage.sync.set({user: username, apiKey: key}, function() {
        renderOptionsMessage('username and API Key updated.', true);
    });
});

function clearOptionsMessage() {
    let optionsError = document.getElementById('optionsMessage');
    optionsError.classList.remove('error', 'success');
}

function renderOptionsMessage(message, success) {
    let optionsError = document.getElementById('optionsMessage');
    optionsError.classList.add((success == true ? 'success' : 'error'));
    optionsError.innerHTML = message;
}