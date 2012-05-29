//Set up some systems to allow dragging the pane
//up and down. We can't just use draggable() coz
//it goes all wonky with position:fixed divs :(
var webaceDragging = false;  //Is the pane being moved?
var webaceDragY = -1;
var webaceTopZ = 5500;
var webacePaneHeight = 20000;       //WAY off bottom
var webaceHandleHeight=38;
var webaceTextEnterHeight=25;

var webaceHelpText = "Commands:<br/><dl><dt>/help</dt><dd>Show this text</dd>"+
                                       "<dt>/nick X</dt><dd>Change nickname to X</li>"+
                                       "<dt>/email X@Y</dt><dd>Change your email to X@Y. "+
                                                      "You will need to confirm it, your "+
                                                       "avatar will be from gravatar.com</dd>"+
                                        "</ul>";

/**************************************************
* Some HTML chunks
*/
var webaceMainDiv = '<div id="webace"><div id="webaceHandle"><h1>&uarr; webAce &uarr;</h1></div><div id="webaceContent"><br/>Type /help for help<hr/></div><div id="webaceTextInput"><input id="webaceTextInputField" onKeyPress="javascript:webaceCheckForEnterKey(event,\'webaceSendNewMessage()\')" size="40"/><input id="webaceTextInputSend" type="submit" onclick="javascript:webaceSendNewMessage()" value="Send" /></div></div>';



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



/*********************************************
* Output some text to the webAce console
*/
function webaceOutput(text){
  $("#webaceContent").append(text+"<hr/>");
}


/*************************************************
* Post To Server - Send a string to the server,
* assuming it's a chat message, and put the
* message up as others will see it when they
* get it
*/
function webacePostToServer(text){
  webaceOutput("You: "+text);
}


/***************************************************
* Execute a command - change nick, show help, 
* anything else that ends up here.
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
      default:
        webaceOutput("Unknown command: "+command);
        
    }
  }
}


/***********************************************
* Post off an AJAX request to send a message
* out
*/
function webaceSendNewMessage(){
  var dom=$("#webaceTextInputField");
  var text = dom.val();

  if(text[0]=="/"){
    webaceDoCommand(text.substr(1));
  }else{
    webacePostToServer(text);
  }

  dom.val("");
  dom.focus();
}





/*********************************************
* Success of the poll brings us here. We update
* the CSRF and pop out any new chat messages
* and stuff like that
*/
function webacePollDone(c){
}


/*******************************************
* If the window is resized, we have to
* check if they resized us off of the
* screen and move back on.
*/
function webaceWindowResized() {
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



/***************************************************
* Function to poll the server and check for new messages
*/
function webaceSendPoll(){
  webaceTicksSincePoll=0;
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
    webaceSendPoll();
  }
}



/**************************************************************
* Function to start the web ace, get the main div into the
* document and start the polling etc.
*/
function webaceStart() {
  //Include the CSS
  $("body").prepend('<link type="text/css" rel="stylesheet" media="all" href="http://webace.dalliance.net/static/webace.css" />');

  $("html").prepend(webaceMainDiv);                   //Add the main webace div.
  webaceAnimatePane(100);

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
  var v = "1.3.2"; // Jquery Version
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
