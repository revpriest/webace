<?php
/*************************************************************
* We make a new version of the CSRF which needs to allow
* the user to operate in more than one tab, and to NOT
* use the php session (which won't work if we scale out
* to many Apache servers) and to work with IE when it
* fails to send cookies (or indeed POST data) to the
* cross site request.
*/
class Webace_Form_Element_WebaceCSRF extends Zend_Form_Element_Hash
{

   /********************************************
   * The current cookie-key for the current logged
   * in user. Try the cookie, or the params.
   */
   private function getCookieKey(){
      if(isset($_COOKIE['cookieKey'])){
          return $_COOKIE['cookieKey'];
      }else if(isset($_POST['cookieKey'])){
          return $_POST['cookieKey'];
      }else if(isset($_GET['cookieKey'])){
          return $_GET['cookieKey'];
      }
      return null;
   }


    /***********************************
     * Initialize CSRF validator
     * We fetch $rightHash from the database, in fact we
     * fetch like a dozen of 'em ideally.
     *
     * @return Zend_Form_Element_Hash
     */
    public function initCsrfValidator()
    {
        $cookieKey = $this->getCookieKey();
        if($cookieKey==null){
            print "No cookie provided";
            exit;
        }
        $mapper = new Application_Model_CsrfhashMapper();
        $rightHashes = $mapper->findAllCsrfForUser($cookieKey);
        $validator = new Application_Form_WebaceCSRFValidator($rightHashes);
        $this->addValidator($validator,true);
        return $this;
    }



    /***************************************************
     * Save a token into the csrf database.
     *
     * @return void
     */
    public function initCsrfToken()
    {
        $mapper = new Application_Model_CsrfhashMapper();
        $csrfhash = $mapper->findOrCreate($this->getCookieKey(),$this->getHash());
        $this->hash = $csrfhash->getCsrf();
    }


    /***********************************************
    * Set the caches of the hashs to a new value,
    */
    protected function _generateHash()
    {
        $this->_hash = Application_Model_Cookie::generateRandomKey();
        $this->setValue($this->_hash);
        return $this->_hash;
    }
}
