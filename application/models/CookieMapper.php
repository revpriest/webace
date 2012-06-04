<?php
/***********************************************
* Data Mapper for the Coookie table. Map the
* model to the data source.
*/ 

class Application_Model_CookieMapper{
    protected $_dbTable;

    /***********************************************
    * Set which DB Table we're using. This ought
    * to always be "Cookie" I would think so not
    * sure why we're taking a parameter for it.
    * Boilerplate code from the tutorials.
    */
    public function setDbTable($dbTable){
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    /****************************************
    * Get DB Table
    */ 
    public function getDbTable() {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_Cookie');
        }
        return $this->_dbTable;
    }

    /******************************************************
    * Save the data from the model into the database table
    */ 
    public function save(Application_Model_Cookie $cookie) {
        $data = array(
            'id'       => $cookie->getId(),
            'nick'     => $cookie->getNick(),
            'displaymode' => $cookie->getDisplayMode(),
            'savename' => $cookie->getSaveName(),
            'email'    => $cookie->getEmail(),
            'password' => $cookie->getPassword(),
            'twitter'  => $cookie->getTwitter(),
            'facebook' => $cookie->getFacebook(),
            'ccemail'  => $cookie->getCCEmail(),
            'created'  => date('Y-m-d H:i:s'),
            'updated'  => date('Y-m-d H:i:s'),
        );

        if ((!isset($data['id']))||($data['id'] === 0)||($data['id']===null)||($data['id']=="")) {
            $data['id'] = Application_Model_DbTable_Cookie::generateRandomKey();
            $cookie->setId($data['id']);
            $this->getDbTable()->insert($data);
        } else {
            unset($data['created']);    //Added by Pre: Don't want to change Created date when updating.
            $this->getDbTable()->update($data, array('id = ?' => $data['id']));
        }
    }

    /*********************************************************
    * Find a database table's row and hydrate it into a
    * model object. Surprisingly we seem to be setting
    * data on an object already created and passed as a
    * param rather than just creating the object and
    * returning it. Wonder why that is? 
    */ 
    public function find($id) {
        $result = $this->getDbTable()->find($id);
        if (count($result) == 0) {
            return null;
        }
        $row = $result->current();
        $cookie = new Application_Model_Cookie();
        $cookie->setId($row->id)
               ->setNick($row->nick)
               ->setEmail($row->email)
               ->setDisplayMode($row->displaymode)
               ->setSaveName($row->savename)
               ->setPassword($row->password)
               ->setTwitter($row->twitter)
               ->setFacebook($row->facebook)
               ->setCCEmail($row->ccemail)
               ->setCreated($row->created)
               ->setUpdated($row->updated);
        return $cookie;
    }



    /***************************************************
    * Find a cookie based on a patial, IE the user-exposed
    * half of the login cookie.
    */ 
    public function findFromPartial($id) {
        $select = $this->getdbtable()->select()->where("SUBSTR(id,1,30)='$id'");
        $resultset = $this->getDbTable()->fetchAll($select);
        if(sizeof($resultset)<1){return null;}
        return($this->hydrateFromResult($resultset[0]));
    }


    /***************************************************
    * Grab everything from the DB table and put it into
    * an array of model objects. This has a lot of
    * ->setX($row->X) lines which are duplicated above.
    * Maybe they sould be taken into a function of their
    * own?
    */ 
    public function fetchAll() {
        $resultset = $this->getdbtable()->fetchall();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entries[] = $this->hydrateFromResult($row);
        }
        return $entries;
    }

   
    /*************************************
    * Take a DB result and build a cookie object
    */
    function hydrateFromResult($row){
       $entry = new Application_Model_Cookie();
       $entry->setId($row->id)
             ->setNick($row->nick)
             ->setEmail($row->email)
             ->setPassword($row->password)
             ->setDisplayMode($row->displaymode)
             ->setSaveName($row->savename)
             ->setTwitter($row->twitter)
             ->setFacebook($row->facebook)
             ->setCCEmail($row->ccemail)
             ->setCreated($row->created)
             ->setUpdated($row->updated);
        return $entry;
    } 
}

