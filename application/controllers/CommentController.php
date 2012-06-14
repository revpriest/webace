<?php
/*****************************************************************
* EWWWW! NASTY, NASTY! When ever we call the zf add controller
* action function, it CLEARS COMMENTS FROM THIS CODE.
* My normal before-the-function description of 
* the function gets erased. We need to put all that
* stuff INSIDE the function. At least it's not
* deleting extra functions I guess but that's nasty.
* Nasty. Nasty. Nasty.
*/
class CommentController extends Zend_Controller_Action
{

    public function init()
    {
    }

    public function indexAction()
    {
        /*******************************************************
        * Index just shows some recent messages to all pages.
        */
        $this->view->title="Latest Messages";
        $cookie = Application_Model_DbTable_Cookie::getUserCookie();
        $mapper = new Application_Model_CommentMapper();
        $rows = $mapper->findWhere("true");
        $this->view->comments = array();
        foreach($rows as $r){
          $this->view->comments[]=$mapper->convertRowToArray($r,$cookie);
        } 
    }

    public function hotconversationsAction()
    {
        /*******************************************************
        * Hottest dozen or so conversations, each with five most
        * recent comments.
        */
        $this->view->title="Hot Conversations";
        $cookie = Application_Model_DbTable_Cookie::getUserCookie();
        $commentMapper = new Application_Model_CommentMapper();
        $mapper = new Application_Model_UrlcacheMapper();
        $this->view->urls = $mapper->findHottest(12);
        $this->view->comments = array();
        foreach($this->view->urls as $url){
          $commentRow = $commentMapper->findWhere("domain='".$url->getDomain()."' and path='".$url->getPath()."'",5,0,"id desc");
          $this->view->comments[$url->getId()] = array();
          for($n=sizeof($commentRow);$n--;$n>=0){
            $this->view->comments[$url->getId()][]=$commentMapper->convertRowToArray($commentRow[$n],$cookie);
          }
          foreach($commentRow as $r){
          } 
        } 
    }

    public function convertUrlToDP($url)
    {
      /***************************************************************
      * Split a URL into it's domain and path. Actually we don't
      * really care to do it properly. We just need to separate
      * "On this website" from "On another website" so we'll just
      * split into two at the third slash. If there aren't any
      * slasshes, it's invalid.
      */
      $firstslash = strpos($url,"/");
      if($firstslash===false){return "Not a valid URL";}
      $secondslash = strpos($url,"/",$firstslash+1);
      if($secondslash===false){return "Not a valid URL";}
      $thirdslash = strpos($url,"/",$secondslash+1);
      if($thirdslash===false){return "Not a valid URL";}
      if($thirdslash===false){
        return "Comment can't be attached to this invalid URL: ".$url;
      }
      $ret = array();
      $ret['domain']=substr($url,0,$thirdslash);
      $ret['path']=substr($url,$thirdslash);
      return $ret;
    }

    public function formDataToObjectData($vals)
    {
      /*****************************************************************
      * A form has a single 'url' field, and a comment object
      * has separate 'domain' and 'path' fields. There are doubtless
      * other differences too. Here we convert that form data into
      * something that'll instantiate a comment model object.
      */
      $dp = $this->convertUrlToDP($vals['url']);
      if(is_string($dp)){return $dp;}
      $vals['domain']=$dp['domain'];
      $vals['path']=$dp['path'];
      $cookie = Application_Model_DbTable_Cookie::getUserCookie();
      $vals['cookie']=$cookie->getId();
      $vals['cookieObject']=$cookie;
      $vals['nick']=$cookie->getNick();
      $vals['email']=$cookie->getEmail();
      $vals['ip']=$_SERVER['REMOTE_ADDR'];
      $content = $vals['content'];

      #Purify the HTML with a lovely library...
      require '../library/htmlpurifier-4.4.0-lite/library/HTMLPurifier.auto.php';
      $config = HTMLPurifier_Config::createDefault();
      $purifier = new HTMLPurifier($config);
      $config->set('HTML.Allowed', "a[href],b,i,strike,br,img[src|width|height]");
      $config->set('HTML.MaxImgLength' , 150);
      $config->set('CSS.MaxImgLength',"150px");
      $content = $purifier->purify( $content );

      $vals['content']=$content;

      return $vals;
    }

    public function submitAction()
    {
       /*****************************************************
       * Add a new comment to the database from a form,
       * or if there's none supplied return the form.
       * We then do a poll and send back the whole lot
       * as JSON.
       */
        //IE doesn't sent a content-type header with X site requests,
        //And PHP doesn't fill in $_POST if that happens. Fix it:
        $request_body = urldecode(file_get_contents("php://input"));
        parse_str($request_body, $_POST);

        $this->view->title="Submit Conversation";
        $this->allowAccessControl();
        $request = $this->getRequest();
        $form    = new Application_Form_Comment();

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $initVals = $form->getValues();
                $initVals = $this->formDataToObjectData($initVals);
                if(!is_array($initVals)){
                    throw new Exception("Can't initialize form data");
                }

                if(substr($initVals['content'],0,1)=="/"){
                  //Oh, special command!
                  $reply = $this->processCommand($initVals);
                  $this->doPollingStuffAndOutputJSON(array("command"=>$initVals['content'],
						           "content"=>$reply));
                }else{
                  //Just submit the comment.
                  $comment = new Application_Model_Comment($initVals);
                  $mapper  = new Application_Model_CommentMapper();
                  $mapper->save($comment);
                  $urlcachemapper = new Application_Model_UrlcacheMapper();
                  $urlcache = $urlcachemapper->findOneWhere($comment->getDomain(),$comment->getPath());
                  $urlcache->incPostcount();
                  $urlcachemapper->save($urlcache);
                }
                $this->doPollingStuffAndOutputJSON(array("content"=>$comment->getContent(),
                                                         "nick"=>$comment->getNick(),
                                                         "email"=>$comment->getEmail(),
                                                        ));
            }
        }
        $this->view->form = $form;
    }

    public function processCommand($vals)
    {
      /*******************************************************
      * Do things like allow the user to set a /nick.
      * and other special commands.
      *
      * $vals['content'] is the command itself.
      * $vals['cookieObject'] is the cookie.
      */
      $commandStr = substr($vals['content'],1);
      $params = explode(" ",$commandStr);
      $command = array_shift($params);
      $cookie=$vals['cookieObject'];
      switch($command){

         /**************************************************
         * change Nickname command.
         */
         case "nick":
           if(sizeof($params)<=0){
             return "Your nick is currently ".$cookie->getNick();
           }
           $x=(implode(" ",$params));
           $x=preg_replace("/[^A-Za-z0-9\ \_\-]/","",$x);
           if(strlen($x)<2){
             return "Nicks must be 2 chars or more";
           }
           $cookie->setNick($x);
           $mapper = new Application_Model_CookieMapper();
           $mapper->save($cookie);
           return "Changed nick to $x";


         /**************************************************
         * Save your session to resume it later! Oooh!
         */
         case "password":
         case "pass":
         case "save":
           if(($cookie->getEmail()==null)||($cookie->getEmail()=="")){
             return "You must first set an email address before you can save";
           }
           if(sizeof($params)<=0){
             return "You must provide a password to save with";
           }
           $password = $params[0];
           if(isset($params[1])){
             if($password!=$params[1]){
               return "Password and confirm don't match";
             }
           }
           $oldPassword = $cookie->getPassword();
           $mapper  = new Application_Model_CookieMapper();
           $cookie=$mapper->duplicate($cookie);        //Save session as backup!

           $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
           $opts = $bootstrap->getOptions();
           $salt = $opts['webace']['saveSessionPasswordSalt'];
    
           $encPassword = md5($salt.$password);
           $cookie->setPassword($encPassword);
           $mapper->save($cookie); 
           if($oldPassword){
             return "Changed session password, use new password in future";
           }
           return "Session saved, resume with /load [email@address.com] [password]";


         /***************************************************
         * Resuming your session
         */
         case "resume":
         case "load":
         case "login":
           if(sizeof($params)<2){
             return "To load a session you need to provide an email address and password";
           }
           $email = $params[0];
           $password = $params[1];
           $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
           $opts = $bootstrap->getOptions();
           $salt = $opts['webace']['saveSessionPasswordSalt'];
           $encPassword = md5($salt.$password);
           $mapper= new Application_Model_CookieMapper();
           $cookie = $mapper->findFromPassword($email,$encPassword);
           if($cookie==null){
             return "Can't find session with that email/password";
           }else{
             $newcookie = $mapper->duplicate($cookie);
             $mapper->save($newcookie);
             setcookie('cookieKey',$newcookie->getId(),time()+(7*24*60*60),"/");
             $_POST['cookieKey']=$newcookie->getId();
             return "Restored session, welcome back ".$cookie->getNick();
           }


         /**************************************************
         * Log out
         */
         case "logout":
           $cookie = Application_Model_Cookie::makeNewCookie();
           return "Logged out. You now have a new anonymous ID.";

         /**************************************************
         * Attach email address command.
         */ 
         case "email":
            if(sizeof($params)<=0){
              $cookie->setEmail("");
              $mapper  = new Application_Model_CookieMapper();
              $mapper->save($cookie);
              return "Reset your email attachment, no longer attached to email.";
            }
            $x=$params[0];
            $validator = new Zend_Validate_EmailAddress();
            if ($validator->isValid($x)) {
              // email appears to be valid
              $nick=$cookie->getNick();

              //Create the confirmation hash
              $hash = new Application_Model_EmailHash();
              $hash->setCookie($cookie->getId());
              $hash->setEmail($x);
              $mapper = new Application_Model_EmailHashMapper();
              $mapper->save($hash);

              //What's the email look like?
              $emailBody = "Hi there!\n\nYou (or someone pretending to be you) asked webace to confirm your email. Click here to confirm this is really you:\nhttp://webace.dalliance.net/Email/confirm?hash=".$hash->getHash()."\n\nIf it was't you, sorry. Ignore this.";

              //Send off the confirmation
              $mail = new Zend_Mail();
              $mail->setBodyText($emailBody)
                   ->setFrom('pre@dalliance.net', 'WebAce')
                   ->addTo($x, $nick)
                   ->setSubject("Confirm your email address for webace $nick");
              $mail->send();
              return "Sent confirmation email to ".htmlentities($x)." -> It'll probably be in your <b>spam folder</b> soon.";
             }else{
               return htmlentities($x)." isn't a valid email address.";
         	 }


         /**************************************************
         * Set display mode command.
         */
         case "mode":
           if(sizeof($params)==0){
             return "Current displaymode is ".$cookie->getDisplayMode()."(".$cookie->getDisplayModeName().")";
           }
           //Some names for the modes:
           if(strcasecmp($params[0],"page")==0){$params[0]=0;}
           if(strcasecmp($params[0],"single-page")==0){$params[0]=0;}
           if(strcasecmp($params[0],"domain")==0){$params[0]=1;}
           if(strcasecmp($params[0],"whole-domain")==0){$params[0]=1;}
           if(strcasecmp($params[0],"net")==0){$params[0]=2;}
           if(strcasecmp($params[0],"internet")==0){$params[0]=2;}
           if(strcasecmp($params[0],"whole-internet")==0){$params[0]=2;}
           if(strcasecmp($params[0],"whole-net")==0){$params[0]=2;}
           $x=(int)($params[0]);
           $cookie->setDisplayMode($x);
           $mapper  = new Application_Model_CookieMapper();
           $mapper->save($cookie);
           return "Changed displaymode changed to $x (".$cookie->getDisplayModeName().")";

      }/*endSwitch*/
      return "Unknown Command $command";
    }

    private function allowAccessControl()
    {
      /*******************************************************************
      * Check if this is from a non-local source, and add the
      * headers to say we're okay with that if it is.
      */
       $origin = $this->getRequest()->getHeader('Origin');
       //$logWriter = new Zend_Log_Writer_Stream('../log.txt');
       //$logger = new Zend_Log($logWriter);
       //$logger->log("Checking AccessControl from $origin",Zend_Log::INFO);
       if(($origin!=null)&&($origin!="")){
          //$logger->log("From $origin, allowing access",Zend_Log::INFO);
          $this->getResponse()->setHeader("Access-Control-Max-Age","1728000",true)
                              ->setHeader("Access-Control-Allow-Origin",$origin,true)
                              ->setHeader("Access-Control-Allow-Credentials", "true",true)
                              ->setHeader("Access-Control-Allow-Methods","POST, GET, OPTIONS",true)
                              ->setHeader("Access-Control-Allow-Headers", "Authorization, Origin, Accept, Content-Type, X-Requested-With, X-HTTP-Method-Override,Set-Cookie,Cookie",true);
        }
       
    }

    public function doPollingStuffAndOutputJSON($jsonArray = array ())
    {
       /***************************************************************
       * Every actions wants to return the polling data I reckon,
       * say if there's any new posts, update the CSRF etc.
       * so they all call this. Even the pollAction, which does
       * very little lese.
       */
       $cookie = Application_Model_DbTable_Cookie::getUserCookie();

       //Generate a new CSRF if this one is too old and tired.
       $csrf = $this->getRequest()->getParam('csrf');
       $csrfmapper = new Application_Model_CsrfhashMapper();
       $age = $csrfmapper->findAge($cookie->getId(),$csrf);
       if(($age==null)||($age>30)){
         //Either no or old CSR, give a new one.
         $csrf = Application_Model_Cookie::generateRandomKey();
         $csrfObj=$csrfmapper->findOrCreate($cookie->getId(),$csrf);
       }
       $url = addslashes($this->getRequest()->getParam('url'));
       $max = (int)($this->getRequest()->getParam('maxCommentId'));
       $min = (int)($this->getRequest()->getParam('minCommentId'));

       //Get all the comments for this URL that are higher in ID than $max.
       $this->comments = array();
       $dp = $this->convertUrlToDP($url);
       if(is_array($dp)){
         $mapper = new Application_Model_CommentMapper();
         $dom = addslashes($dp['domain']);
         $path = addslashes($dp['path']);
         if($min==null){
           $minmax="id > $max";
         }else{
           $minmax="id < $min";
         }
         if($cookie->getDisplayMode()==2){
           //All posts from the entire internet!? Are ou CRAZY!
           $rows = $mapper->findWhere($minmax);
         }else if($cookie->getDisplayMode()==1){
           //All posts to any page on this domain. Sorted.
           $rows = $mapper->findWhere("domain='".$dom."' and ".$minmax);
         }else{
           $rows = $mapper->findWhere("domain='".$dom."' and path='".$path."' and ".$minmax);
         }
         foreach($rows as $r){
           $this->comments[]=$mapper->convertRowToArray($r,$cookie);
         }
       }
       $sendArray = array_merge($jsonArray,array(
                                          "comments"=>$this->comments,
                                          "success"=>"true",
                                          "setCookie"=>$cookie->getId(),
                                          "url"=>$url,
                                          "csrf"=>$csrf));
       $this->getHelper('json')->sendJSON($sendArray);
    }

    public function pollAction()
    {
       /************************************************************
       * The poll action. We return a JSON object with data like
       * a lovely CSRF token and any new messages that popped up.
       */
       //IE doesn't sent a content-type header with X site requests,
       //And PHP doesn't fill in $_POST if that happens. Fix it:
       $request_body = urldecode(file_get_contents("php://input"));
       parse_str($request_body, $_POST);

       $this->view->title="Poll";
       $this->allowAccessControl();
       $this->doPollingStuffAndOutputJSON();
    }

    public function userAction()
    {
        /****************************************************************
        * List everything by a user. Either passed a message or 
        * a half-a-cookie. Certainly want to paginate this.
        */
        $page = $this->getRequest()->getParam('page');
        if(!$page){$page=0;}
        $cookie = Application_Model_DbTable_Cookie::getUserCookie();
        $cookieMapper = new Application_Model_CookieMapper();
        $mapper = new Application_Model_CommentMapper();

        //Need to grab a cookie and nick. We can do this two ways,
        if($this->getRequest()->getParam('id')){
          //One: Passed a partial cookie and nickname directly:
          $partialCookie = addslashes($this->getRequest()->getParam("id"));
          $viewUserCookie = $cookieMapper->findFromPartial($partialCookie);
          $nick = addslashes($this->getRequest()->getParam("nick"));
        }else if($this->getRequest()->getParam('mid')){
          //Two: Passed a message and told "The guy who wrote this"
          $messageId = $this->getRequest()->getParam('mid');
          $message = $mapper->find($messageId);
          if($message==null){
            throw new Exception("Can't find that message");
          }
          $viewUserCookie=$message->getCookieObject();
          $nick = $message->getNick();
          if($this->getRequest()->getParam("nick")){
            $nick = addslashes($this->getRequest()->getParam("nick"));
          }
          if($nick=="-1"){
             $nick=null;
          }
        }else{
          throw new Exception("User not specified");
        }

        //All the nicks this user ever used!
        $this->view->userNicks = $viewUserCookie->getAllNicks();

        //All the comments they ever posted!
        if($viewUserCookie->getEmail()){
          $orEmail=" or email='".$viewUserCookie->getEmail()."'";
        }else{
          $orEmail="";
        }
        if($nick!=null){
          $andNick=" and nick='".$nick."'";
        }
        $rows = $mapper->findWhere("(cookie='".$viewUserCookie->getId()."' ".$orEmail.")".$andNick,50,$page*50);
        $this->view->comments = array();
        foreach($rows as $r){
          $viewUserFullCookieId = $r->cookie;
          if($messageId==null){$messageId=$r->id;}
          $this->view->comments[]=$mapper->convertRowToArray($r,$cookie);
        } 
        $this->view->askedNick=$nick;
        $this->view->userDetails=$viewUserCookie;
        $this->view->mid=$messageId;
        $this->view->title="Messages from $nick";
        $this->view->page=$page;
    }

    public function showAction()
    {
        /****************************************************************
        * Show a single message, and maybe eventually replies to that
        * message I guess (Maybe posts to this actual page, in a meta
        * sort of way).
        */
        $this->view->title="Single Message";
        $cookie = Application_Model_DbTable_Cookie::getUserCookie();
        $cookieMapper = new Application_Model_CookieMapper();
        $mapper = new Application_Model_CommentMapper();
        $messageId = $this->getRequest()->getParam('id');
        if($messageId==null){
          throw new Exception("No Message Specified");
        }
        $message = $mapper->find($messageId);
        $viewUserCookie=$message->getCookieObject();
        $nick = $message->getNick();
        $this->view->userDetails=$viewUserCookie;
        $this->view->message = $message;
    }


}










