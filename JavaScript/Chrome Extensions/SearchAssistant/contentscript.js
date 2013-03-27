//onmousedown="return rwt(this,'','','','6','AFQjCNG-IufYYOPuPvs4YCQm-A9zsbgJ1Q','','0CIUBEBYwBQ',null,event)"
//var s = document.getElementsByClassName('r');
var tag = document.getElementsByTagName('li');
chrome.extension.sendRequest({}, function(response) {});
if( tag ){
    for(var d = 0 , l = tag.length ; d < l ; d ++ ){ 
        tag[d].innerHTML = tag[d].innerHTML.replace(/onmousedown="return rwt\(this,[^\)]*,event\)"/g,"");	
    }
}
