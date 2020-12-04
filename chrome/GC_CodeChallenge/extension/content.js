chrome.runtime.onMessage.addListener(function(request, sender, sendResponse) {
    if (request.message == 'getImages') {
        let r = [];
        let images = document.getElementsByTagName('img');
        for (i in images) {
            //Note: there's likely some other filters may or may not go here.
            if (images[i].width <= 1 
                    || images[i].height <= 1 
                    || typeof(images[i].src) == 'undefined'
                    ) {
                continue;
            }
            let d = {
                src: images[i].src,
                width: images[i].width,
                height: images[i].height,
            }
            r.push(d);
        }
        
        sendResponse({message: 'received', images: r});
    }
});