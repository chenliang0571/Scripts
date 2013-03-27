   var request = null;
   try {
     request = new XMLHttpRequest();
   } catch (trymicrosoft) {
     try {
       request = new ActiveXObject("Msxml2.XMLHTTP");
     } catch (othermicrosoft) {
       try {
         request = new ActiveXObject("Microsoft.XMLHTTP");
       } catch (failed) {
         request = null;
       }
     }
   }

   if (request == null)
     alert("Error creating request object!");

   function getFriendIndex() {
   	//document.getElemntById("friBtn").disabled=true;
   	document.getElementById("status").innerHTML = "<img src='web_loading.gif'/>";
     var url = "process.php?type=friend";
     request.open("GET", url, true);
     request.onreadystatechange = updatePage;
     request.send(null);
   }
   
   function getFollowerIndex() {
   	//document.getElemntById("folBtn").disabled=true;
   	document.getElementById("status").innerHTML = "<img src='web_loading.gif'/>";
     var url = "process.php?type=follower";
     request.open("GET", url, true);
     request.onreadystatechange = updatePage;
     request.send(null);
   }

	function postTweetFir(){
		var tweet = document.getElementById("twt").value;
		var url = "process.php";
		request.open("POST",url,true);
		request.onreadystatechange = updatePage;
		request.setRequestHeader("Content-Type","application/x-www-form-urlencoded;charset=UTF-8");
		//request.setRequestHeader("Content-Type","text/plain;charset=UTF-8");
		request.send("type=postTweetFriend" + "&tweet=" + tweet);
	}
	function postTweetFoll(){
		var tweet = document.getElementById("twt").value;
		var url = "process.php";
		request.open("POST",url,true);
		request.onreadystatechange = updatePage;
		request.setRequestHeader("Content-Type","application/x-www-form-urlencoded;charset=UTF-8");
		//request.setRequestHeader("Content-Type","text/plain;charset=UTF-8");
		request.send("type=postTweetFollow" + "&tweet=" + tweet);
	}
   function updatePage() {
     if (request.readyState == 4) {
		if (request.status == 200)
		{
			//if(document.getElemntById("friBtn").disabled)
			//	document.getElemntById("friBtn").disabled=false;
			//if(document.getElemntById("folBtn").disabled)
			//	document.getElemntById("folBtn").disabled=false;	
	       /* Get the response from the server */
	       var respon = request.responseText;
	
	       /* Update the HTML web form */
	       document.getElementById("status").innerHTML = respon;
		}
		else
		{
			document.getElementById("status").innerHTML = "Error " + request.status;
		}
     }
   }