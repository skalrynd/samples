var gcuser = null;
var gcapiKey = null;

chrome.storage.sync.get(['user','apiKey'], function(data) {
    gcuser = data.user;
    gcapiKey = data.apiKey;
    
    if (gcuser.length < 1 || gcapiKey.length < 1) {
        renderMessage('ERROR: you must first go to extension options and set your user and api key.');
    } else {
        renderPopup();
    }
});

function attachLoader(element) {
    if (element.querySelectorAll('.overlay').length > 0) {
        return;
    }
    let overlayContainer = document.createElement('div');
    element.appendChild(overlayContainer);
    overlayContainer.classList.add('overlay');
    let spinnerContainer = document.createElement('div');
    spinnerContainer.classList.add('spinnerContainer');
    overlayContainer.appendChild(spinnerContainer);
    let spinner = document.createElement('div');
    spinner.classList.add('lds-dual-ring');
    spinner.style.margin = 'auto';
    spinnerContainer.appendChild(spinner);
}

function detatchLoaderFrom(element) {
    $(element).find('.overlay').remove();
}

function attachSelectionEvents(container) {
    $(container).find('img').on('click', function() {
        let imageContainers = document.getElementById('popupThumbnails').querySelectorAll('.imageContainer');
        for(i = 0; i < imageContainers.length; i++) {
            if (imageContainers[i] !== container) {
                imageContainers[i].classList.add('hidden');
            }
        }
        attachLoader(container);
        sendDetectCert(container);
    });
}

function clearOptionsMessage() {
    let optionsError = document.getElementById('popupMessage');
    optionsError.classList.remove('error', 'success');
}

function clearThumbnails() {
    let container = document.getElementById('popupThumbnails');
    container.innerHTML = '';
}

function renderAddToList(imageContainer, data) {
    $(imageContainer).find('div[name=addToListContainer]').remove();
    if (typeof(data.activeUser.comicLists) == 'undefined') {
        console.log('error: user has no lists? A more refined error routine here');
        return;
    }
    detatchLoaderFrom(imageContainer);
    let listDiv = $('<div>')
            .attr('name', 'addToListContainer')
            .css('margin-top', '10px')
            .appendTo(imageContainer);
    let sel = $('<select>').attr('name', 'selectList').appendTo(listDiv);
    $('<option>Select A List</option>').appendTo(sel).val('');
    $.each(data.activeUser.comicLists, function(i, list) {
        let o = $('<option>'+list.name+'</option>').val(list.id);
        sel.append(o);
    });
    let bDiv = $('<div>').appendTo(listDiv);
    let b = $('<button>Save</button>')
            .attr('type', 'button')
            .appendTo(bDiv)
            .on('click', function(e) {
                attachLoader(imageContainer);
                sendToList(imageContainer, data);
    });
}

function renderMessage(message, success) {
    clearOptionsMessage();
    let popupMessage = document.getElementById('popupMessage');
    popupMessage.classList.add((success == true ? 'success' : 'error'));
    popupMessage.innerHTML = message;
}

function renderPopup() {
    chrome.tabs.query({active: true, currentWindow: true}, function(tabs) {
      chrome.tabs.sendMessage(tabs[0].id, {message: "getImages"}, function(response) {
          renderThumbnails(response.images);
      });
    });
}

function renderSelection(data) {
    let div = document.createElement('div');
    div.classList.add('imageContainer');
    let img = document.createElement('img');
    img.setAttribute('src', data.src);
    img.setAttribute('width', data.width);
    img.setAttribute('height', data.height);
    div.appendChild(img);
    let textDiv = document.createElement('div');
    textDiv.classList.add('description');
    textDiv.innerHTML = 'height: ' + data.height + '; width: ' + data.width;
    div.appendChild(textDiv);
    return div;
}

function renderThumbnails(images) {
    let container = document.getElementById('popupThumbnails');
    
    for(i in images) {
        let selection = renderSelection(images[i]);
        attachSelectionEvents(selection);
        container.appendChild(selection);
    } 
}

function renderVerifyFailed(imageContainer) {
    detatchLoaderFrom(imageContainer);
    $('<div>Certification not found</dvi>')
            .css({color: '#FF0000', 'font-weight': 'bold'})
            .attr('name', 'addToListContainer')
            .appendTo(imageContainer);
}

//The example below is one reason why js frameworks are so awesome...
function send(url, successCallback) {
    $.ajax(url, {
        method: 'GET',
        dataType: 'JSON',
        success: function(data) {
            successCallback(data);
        },
        error: function() {
            console.log('ERROR');
            console.log(xmlhttp);
        }
    });
//    var xmlhttp = new XMLHttpRequest();
//    xmlhttp.onreadystatechange = function() {
//        if (xmlhttp.readyState == XMLHttpRequest.DONE) {   // XMLHttpRequest.DONE == 4
//           if (xmlhttp.status >= 200 && xmlhttp.status <= 299) {
//              if (typeof(successCallback) != 'undefined') {
//                  console.log(xmlhttp.response);
//                  let data = JSON.parse(xmlhttp.response);
//                  successCallback(data);
//              }
//           } else { //error
//               console.log('ERROR');
//               console.log(xmlhttp);
//           }
//        }
//    };
//    xmlhttp.open("GET", url, true);
//    xmlhttp.send();
}

function sendDetectCert(imageContainer) {
    var url = 'https://comics.gocollect.com/api/certVision';
    var imageUrl = imageContainer.querySelectorAll('img')[0].src;
    chrome.storage.sync.get(['user', 'apiKey'], function(data) {
        url = url + '?user=' + data.user 
                + '&key=' + data.apiKey
                + '&returnWith[]=activeUser'
                + '&imageUrl=' + encodeURIComponent(imageUrl) 
                ;
        send(url, function(response) {
           if (typeof(response.status) != 'undefined' && response.status == 'success') {  //Successful evaluation.
               renderAddToList(imageContainer, response);
           } else {
               renderVerifyFailed(imageContainer);
           }
        });
    });
}

function sendToList(imageContainer, data) {
    var url = 'https://comics.gocollect.com/api/certToCurrentUserList';
    var listId = $(imageContainer).find('select[name=selectList]').first().val();
    if (listId.length < 1) {
        detatchLoaderFrom(imageContainer);
        return;
    }
    chrome.storage.sync.get(['user', 'apiKey'], function(res) {
        let params = {
            user: res.user,
            key: res.apiKey,
            listId: listId,
            certNumber: data.cert.certNumber
        };
        $.ajax(url, {
            data: params,
            dataType: 'json',
            success: function(data) {
                detatchLoaderFrom(imageContainer);
                $(imageContainer).find('div[name=addToListContainer]')
                        .css({color: '#008800', 'font-weight': 'bold'})
                        .html('Comic book added to list!');
            }}
        );
    });
    
}