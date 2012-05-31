<?php
/*****************************************************************
* A controller. This was auto-generated but again, with
* basically no content at all. Every action needs to
* be added by hand, individually, at least it was in
* this tutorial. Perhaps having Doctrine around would
* make it easier?
*
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
        $comment = new Application_Model_CommentMapper();
        $this->view->comments = $comment->fetchAll();
    }


    public function convertUrlToDP($url){
      if(substr($url,0,7)!="http://"){
        return "Comment can't be attached to invalid URL: ".$url;
      }
      $firstslash=strpos($url,'/',7);
      if($firstslash===false){
        return "Comment can't be attached to this invalid URL: ".$url;
      }
      $ret = array();
      $ret['domain']=substr($url,7,$firstslash-7);
      $ret['path']=substr($url,$firstslash+1);
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
      $config->set('HTML.MaxImgLength' , 250);
      $config->set('CSS.MaxImgLength',"250px");
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
        $request = $this->getRequest();
        $form    = new Application_Form_Comment();
 
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {
                $initVals = $form->getValues();
                $initVals = $this->formDataToObjectData($initVals);
                if(!is_array($initVals)){
                    print "Error: $initVals";
                    exit;
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
      switch($command){
         case "nick":
           $cookie=$vals['cookieObject'];
           $x=(implode(" ",$params));
 	   $x=preg_replace("/[^A-Za-z0-9\ \_\-]/","",$x);
           $cookie->setNick($x);
           $mapper  = new Application_Model_CookieMapper();
           $mapper->save($cookie);
           return "Changed nick to $x";
      }
      return "Unknown Command $command";
    }



    public function doPollingStuffAndOutputJSON($jsonArray=array())
    {
       /***************************************************************
       * Every actions wants to return the polling data I reckon,
       * say if there's any new posts, update the CSRF etc.
       * so they all call this. Even the pollAction, which does
       * very little lese.
       */
       $this->pollForm    = new Application_Form_Comment();
       $e = $this->pollForm->getElement('csrf');
       foreach($this->pollForm as $n=>$v){        //Only seems to give us the value when we inspect it first.
         $a = "$n $v\n";
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
         $rows = $mapper->findWhere("domain='".$dom."' and path='".$path."' and ".$minmax);
         foreach($rows as $r){
           $this->comments[]=$mapper->convertRowToArray($r);
         }
       }
       $this->getHelper('json')->sendJSON(array_merge($jsonArray,array("success"=>"true",
                                          "comments"=>$this->comments,
                                          "success"=>"true",
                                          "csrf"=>$this->pollForm->getValue('csrf'))));

    }

    public function pollAction()
    {
       /************************************************************
       * The poll action. We return a JSON object with data like
       * a lovely CSRF token and any new messages that popped up.
       */
       $this->doPollingStuffAndOutputJSON();
    }


}




