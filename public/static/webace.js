//Set up some systems to allow dragging the pane
//up and down. We can't just use draggable() coz
//it goes all wonky with position:fixed divs :(
var webaceDragging = false;
var webaceDragY = -1;
var webaceTopZ = 5500;
var webacePaneHeight = 20000;
var webaceHandleHeight=38;
var webaceTextEnterHeight=25;
var webaceCSRF = "";
var webaceMaxCommentID = 0;
var webaceFirstPoll=true;
var webaceReplyUrl=null;
var webaceServerDomain = "webace.dalliance.net";
if(typeof(webaceServerDomainOverride)!='undefined'){
  var webaceServerDomain = webaceServerDomainOverride;
}

var webaceHelpText = "Commands:<br/><dl><dt>/help</dt><dd>Show this text</dd>"+
                                       "<dt>/nick X</dt><dd>Change nickname to X</li>"+
                                       "<dt>/email X@Y</dt><dd>Change your email to X@Y. "+
                                            "You will need to confirm it with an emailed link.<br/>"+
                                             "Users with email get avatars from "+
                                             "<a href=\"http://gravatar.com\">Gravatar.com</a>"+
                                             ", and can be notifed of non-contemporaneous replies "+
                                             "to their messages."+
                                             "</dd>"+
                                       "<dt>/mode X</dt><dd>Change display mode to X, X is:<br/>"+
                                             "0 = Show new comments to this single page.<br/>"+
                                             "1 = Show new comments to this whole site.<br/>"+
                                             "2 = Show all comments everywhere in the internet.</li>"+
                                       "<dt>/logout</dt><dd>Ditch your login info and start a new "+
                                             "fresh anonymous login. This will reset your nick, "+
                                             "email, mode and everything else.</li>"+
                                        "</dl>"+
                       "Visit <a href=\"http://"+webaceServerDomain+"/?page=help\">our help page</a> for more.";
                    

/**************************************************
* Some HTML chunks
*/
var webaceMainDiv = '<div id="webace"><div title="Click or drag handle" id="webaceHandle"><h1>&uarr;WebAce&uarr;</h1></div><div id="webaceContent"><br/><a href="http://'+webaceServerDomain+'/">WebAce - Chat in any webpage</a><br/>Type /help for help<hr/></div><div id="webaceTextInput"><input id="webaceTextInputField" onKeyPress="javascript:webaceCheckForEnterKey(event,\'webaceSendNewMessage()\')" size="40"/><input id="webaceTextInputSend" type="submit" onclick="javascript:webaceSendNewMessage()" value="Send" /></div></div>';



/********************************
* Random string for one-time IDs
*/
function webaceGetRandomString(){
  var ret = "";
  var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  for(var n=0;n<20;n++){
    ret+=chars.charAt([Math.random()*chars.length]);
  }
  return ret;
}



/********************************************************
* Animate the pane's position and height 
*/
function webaceAnimatePane(x,speed){
  if(speed==undefined){speed=500;}
  if(x!=undefined){webacePaneHeight=x;}
  var height = $(window).height()-webacePaneHeight-webaceHandleHeight-webaceTextEnterHeight;
  if(speed<=0){
    $("#webace").css({top:webacePaneHeight});
    $("#webaceContent").css({height:height});
  }else{
    $("#webace").animate({top:webacePaneHeight},speed);
    $("#webaceContent").animate({height:height},speed);
  }
}


/*****************************************
* Mouse dragging - mouse-moved function
*/
function webaceMouseMove(e){
  if(webaceDragging){
    //Dragging the pane up/down.
    var newPos = webaceDragStart-(webaceDragY-e.clientY);
    if(newPos<0){newPos=0;}
    if(newPos>$(window).height()-30){newPos=$(window).height()-30;}
    webaceAnimatePane(newPos,-1);
  }
  return;
}
function webaceMouseDown(e){
  webaceDragging=true;
  webaceDragY=e.clientY;
  webaceDragStart=webacePaneHeight;
}


/*****************************************
* Mouse dragging - mouse-Up function
*/
function webaceMouseUp(e){
  if(webaceDragY==e.clientY){
    //Clicked not dragged.
    if(e.clientY>$(window).height()-30){
      //Open the pane
      webaceAnimatePane(100);
    }else{
      //Close it down, minimize everything!
      webaceAnimatePane($(window).height()-webaceHandleHeight);
    }
  }
  if(webaceDragging){
    webaceDragging=false;
    if(webacePaneHeight>$(window).height()-webaceHandleHeight-webaceTextEnterHeight){
      //Close it down, minimize everything!
      webaceAnimatePane($(window).height()-webaceHandleHeight);
    }
  }
}


/**************************************************
* Function that checks if a keypress was enter,
* and evals a string if so
*/
function webaceCheckForEnterKey(e,command){
  if(e.keyCode==13){
    eval(command);
  } 
}

/*****************************************
* Get the cookie. We need this coz IE is
* silly and won't send the cookie in a 
* X site ajax request.
*/
function webaceGetCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}



/*********************************************
* Output some text to the webAce console
*/
function webaceOutput(text,domid,inhibitScroll){
  var dom;
  if(domid==null){
    dom = $("#webaceContent");
  }else{
    dom=$('#'+domid)
  }
  dom.append(text+"<hr/>");
  if(inhibitScroll==null){
    dom.animate({scrollTop: dom.prop("scrollHeight")},500);
  }
}


/***************************************************
* Set the reply-to when the user clicks a reply
* radio button
*/
function webaceSetReply(url){
  webaceReplyUrl=url;
}


/*************************************************
* Format a reply read for printing. We add HTML
* markup and whatnot here.
*/
function webaceFormatReply(data){
  var reply = '<div id="webaceComment"'+data['id']+'">';
  if(data['url']){
    //Multi-mode! We need to point out this may not have been a comment to THIS page.
    reply+='<span class="webaceToPage" title="Not Posted To Your Current Page.">';
    reply+='<input type="radio" name="webaceReplyUrl" onclick="webaceSetReply(\''+data['url']+'\')" title="Click to reply to this comment/page" />';
    reply+='To: <a href="'+data['url']+'">'+data['url']+'</a></span>';
  }
  reply+='<div class="webaceAvatar">';
  if((data['email']!=null)&&(data['email']!="")){
    reply+=" <img class=\"webaceAvatarImg\" src=\"http://www.gravatar.com/avatar/"+data['emailmd5']+"\" width=\"80\" height=\"80\" alt=\"Avatar\" /><br/>";
  }
  reply += '<span class="webaceNick"><a href="http://'+webaceServerDomain+'/Comment/user?mid='+data['id']+'">'+data['nick']+'</a></span><br/>';
  reply+='<span class="webaceDate"><a href="http://'+webaceServerDomain+'/Comment/show?id='+data['id']+'">('+data['created']+')</a></span>';
//  reply+='<br/><span class="webaceDate">('+data['id']+')</span>';
  reply+="</div>";
  reply+=data['content'];
  reply+="</div>";
  return reply;
}



/******************************************************
* Fetch earlier comments, called when there's more
* than will fit.
*/
function webaceLoadEarlier(min,domid){
  dom = $('#'+domid);
  dom.replaceWith('<div id="'+domid+'"></div>');
  webaceSendMessage({min:min,domid:domid});
}



/************************************************
* Add some comments into the main chat pane. 
*/
function webaceAddComments(comments,domid){
  if((webaceFirstPoll)||(domid!=null)){
    webaceFirstPoll=false;
    if(comments.length>=50){
      rid = webaceGetRandomString();
      webaceOutput('<a class="webaceActionLink" href="javascript:webaceLoadEarlier('+comments[comments.length-1]['id']+',\''+rid+'\')" id="'+rid+'">[Load Earlier Comments]</a>',domid,true);
    }
  }
  for(var n=comments.length-1;n>=0;n--){
    var c = comments[n];
    webaceOutput(webaceFormatReply(c),domid,true);
    if(parseInt(c['id'])>webaceMaxCommentID){webaceMaxCommentID=parseInt(c['id']);}
  }
  if((comments.length>0)&&(domid==null)){
      var dom=$("#webaceContent");
      dom.animate({scrollTop: dom.prop("scrollHeight")},500);
  }
  
}

/*************************************************
* Post To Server - Send a string to the server,
* assuming it's a chat message, and put the
* message up as others will see it when they
* get it
*/
function webacePostToServer(text){
  webaceSendMessage({url:"http://"+webaceServerDomain+"/Comment/submit",data:'&content='+text});
}


/***************************************************
* Execute a command - change nick, show help, 
* anything else that ends up here. Return true
* if this was completed here, false will send
* it to the server for completing there.
*/
function webaceDoCommand(wholeCommand){
  var command = wholeCommand;
  if(wholeCommand.indexOf(" ")>=0){
    command = wholeCommand.substr(0,wholeCommand.indexOf(" ")).toLowerCase();
  }
  if(command){
    switch(command){
      case "help":
        webaceOutput(webaceHelpText);
        break;
      case "csrf":
        webaceOutput("Current CSRF: "+webaceCSRF);
        break;
      case "cookie":
        webaceOutput("Current Auth Cookie: "+webaceGetCookie('cookieKey'));
        break;
      case "sessid":
        webaceOutput("Current SessID Cookie: "+webaceGetCookie('PHPSESSID'));
        break;
      case "maxid":
        webaceOutput("Current Top Post ID: "+webaceMaxCommentID);
        break;
      case "replyurl":
          webaceOutput("Current Reply-Url: "+webaceGetReplyUrl());
        break;
      default:
        return false;
    }
  }
  return true;
}


/***********************************************
* Post off an AJAX request to send a message
* out
*/
function webaceSendNewMessage(){
  var dom=$("#webaceTextInputField");
  var text = dom.val();
  var done=false;

  if(text==""){return;}		//Never send empty string, pointless.

  if(text[0]=="/"){
    done = webaceDoCommand(text.substr(1));
  }
  if(!done){
    webacePostToServer(text);
  }
  dom.val("");
  dom.focus();
}







/*******************************************
* If the window is resized, we have to
* check if they resized us off of the
* screen and move back on.
*/
function webaceWindowResized() {
  webaceAnimatePane(webacePaneHeight,2);
  webaceMoveOffBottom();
}

/******************************************
* If the comms pane is off the bottom of the
* window, move it on up now, yeah, out of
* the darkness.
*/
function webaceMoveOffBottom(){
  if(webacePaneHeight>$(window).height()-webaceHandleHeight){
     webacePaneHeight=$(window).height()-webaceHandleHeight
     webaceAnimatePane();
  }
}


/******************************************
* Find which page to reply to, should work
* with bookmarklet, with /mode replies
* with the launch-in-page and everywhere
*/
function webaceGetReplyUrl(){
  var myurl = webaceReplyUrl;
  if(myurl==null){
    //Default to the current page.
    var externalDom = $("#webaceLaunchContent");
    if(externalDom.length>0){
      //Current page is the iframe the launcher pushed us to.
      myurl=externalDom.attr("src");
    }else{
      //Current page is just this page
      myurl=$(location).attr('href');
    }
  }else{
    myurl=myurl;
  }
  return myurl;
}

function webacePollResult(params,json){
  webaceTicksSincePoll=0;
  if(json['csrf']){ webaceCSRF=json['csrf'] };
  if(json['comments']){webaceAddComments(json['comments'],params['domid']); }
  if(json['command']){
     webaceOutput("<i>System</i>: "+json['content']);
  }
}


/***************************************************
* Function to send a message to the server, we
* always include a poll to check for new messages
* coz, why not? And we also always update the CSRF
* for the posting form..
*/
function webaceSendMessage(params){ 
  jQuery.support.cors = true; // force cross-site scripting (as of jQuery 1.5)
  if(params==null){params={};}
  if(params['url']==null){params['url']="http://"+webaceServerDomain+"/Comment/poll";}
  myurl = encodeURIComponent(webaceGetReplyUrl());
  var data="url="+myurl+"&csrf="+webaceCSRF;
  if(params['data']!=null){
    data+=params['data'];
  }

  //Always add data for a poll to a message and parse any replies.
  if(params['min']!=null){
     data += "&minCommentId="+params['min'];
  }else{
     data += "&maxCommentId="+webaceMaxCommentID;
  }

  //Right. *sigh*
  //IE deliberately refuses to send any cookie data, and 
  //as far as I can tell accidentally fails to send any
  //POST data either. It's ALL gotta go in the GET part.
  if ($.browser.msie && window.XDomainRequest) {
      // Use Microsoft XDR
      var xdr = new XDomainRequest();
      xdr.open("POST", params['url']+"?"+data+'&cookie='+webaceGetCookie('cookieKey')+"&phpsessid="+webaceGetCookie('PHPSESSID'));
      xdr.onload = function() {
	  try{
            json = jQuery.parseJSON(xdr.responseText);
	    webacePollResult(params,json);
	  }catch(e){
	    webaceOutput("Reply:"+xdr.responseText);
	  }
      };
      xdr.send(data);
  } else {
    //Normal sensible way to do pre-fight and x domain requests.
    $.ajax({
      type: "POST",
      url: params['url'],
      xhrFields: {
         withCredentials: true
      },
      crossDomain: true,
      dataType: "json",
      cache: false,
      data: data,
      error:function(a,b,c){
              webaceTicksSincePoll=0;
              var error = "";
              for(i in a){
                  try{ error+="<b>"+i+"</b> => "+a[i]+"<br/>\n"; }catch(e){ }
              }
              webaceOutput("Server communication error:<br/><b>"+b+"</b><br/>"+error+"\n"+params['url']+":"+data);
      },
      success: function(json) {
        //Got the submit form, need to update our CSRF
        webaceTicksSincePoll=0;
        if(json['success']=='true'){
	  webacePollResult(params,json);
        }else{ 
          webaceOutput("ERROR:"+json);
        }
      }
    });
  }
}


/******************************************************
* The interval, called every second, should check there's
* no new messages every now and then and ensure
* everything is tickety-boo.
*/
var webaceTicksSincePoll=10000;  //Poll ASAP!
function commsInterval(){
  //Poll the server? Once every 5 seconds for now. Something more dynamic soon.
  if(webaceTicksSincePoll++>5){
    webaceSendMessage();
  }
}



/**************************************************************
* Function to start the web ace, get the main div into the
* document and start the polling etc.
*/
function webaceStart() {
  //Include the CSS
  $("body").prepend('<link type="text/css" rel="stylesheet" media="all" href="http://'+webaceServerDomain+'/static/webace.css" />');

  //Include the google web font...
  WebFontConfig = { google: { families: [ 'Oleo+Script::latin' ] } };
  var wf = document.createElement('script');
  wf.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
  wf.type = 'text/javascript';
  wf.async = 'true';
  var s = document.getElementsByTagName('script')[0];
  s.parentNode.insertBefore(wf, s);

  $("html").prepend(webaceMainDiv);                   //Add the main webace div.
  if(typeof(webaceStartMinimized)==="undefined"){
    webaceAnimatePane(100);
  }else{
    webaceAnimatePane($(window).height()-webaceHandleHeight);
  }

  //Set up our callback functions so we know what's going on.
  $(document).mousemove(webaceMouseMove);
  $(window).resize(webaceWindowResized);
  $("#webaceHandle").mousedown(webaceMouseDown);
  $(document).mouseup(webaceMouseUp);
  $("#webaceTextInputField").focus();

  //And start the ticker
  setInterval("commsInterval()",1000);
}




/******************************************************************
* Function to include jquery if it's not already here, and then
* start the whole thing by adding the main chat div and starting
* the polling system etc.
*/
function webaceIncludeJquery() {
  //Check for and include the lovely jQuery....
  var v = "1.7.2"; // Jquery Version
  if (window.jQuery === undefined || window.jQuery.fn.jquery < v) {
    var done = false;
    var script = document.createElement("script");
    script.src = "http://ajax.googleapis.com/ajax/libs/jquery/" + v + "/jquery.min.js";
    script.onload = script.onreadystatechange = function(){
      if (!done && (!this.readyState || this.readyState == "loaded" || this.readyState == "complete")) {
        done = true;
        webaceStart();
      }
    };
    document.getElementsByTagName("head")[0].appendChild(script);
  } else {
    //Already have jquery!
    webaceStart();
  }
}

webaceIncludeJquery();
