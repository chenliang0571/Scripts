// Listen for the content script to send a message to the background page.
chrome.extension.onRequest.addListener(
	function (request, sender, sendResponse) {
		chrome.pageAction.show(sender.tab.id);
		sendResponse({});
	}
);

chrome.webRequest.onErrorOccurred.addListener(
	function(details) {
		console.log(details);
		var reset = "net::ERR_CONNECTION_RESET";
		var url = null;
		if (details.error == reset){
			//if (details.type == "main_frame" && details.tabId == -1)
            if (details.type == "main_frame")
			{
				console.log("main_frame search error, redirecting...");
                var oldUrl = details.url;
				url = oldUrl.replace("http://www.google","https://www.google")
				chrome.tabs.update(details.tabid, {url: url});
			}
			else
			{
				console.log("this is others RESET");
			}
		}
		else {
			console.log('other Error: ' + details.url + ',\n' + details.error );
		}
	}, 
	{urls: ["http://www.google.com/*","http://www.google.com.hk/*"]}
);
