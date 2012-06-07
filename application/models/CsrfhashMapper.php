<?php

class Application_Model_CsrfhashMapper
{
    protected $_dbTable;

    /***********************************************
    * Set which DB Table we're using.
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
            $this->setDbTable('Application_Model_DbTable_Csrfhash');
        }
        return $this->_dbTable;
    }

    /******************************************************
    * Save the data from the model into the database table
    */ 
    public function save(Application_Model_Csrfhash $hash) {
        $data = array(
            'cookie'       => $hash->getCookie(),
            'csrf'     => $hash->getCsrf(),
            'created'  => date('Y-m-d H:i:s'),
        );

        //Always insert, never update.
        $this->getDbTable()->insert($data);
    }


    /***************************************************
    * Delete a given hash, it's been used!
    */ 
    public function deleteByCsrf($csrf) {
      $delete = $this->getdbtable()->delete("csrf='$csrf'");
    }


    /***************************************************
    * Find a given hash, or if it's not there create it!
    */ 
    public function findOrCreate($cookie,$csrf) {
       $select = $this->getdbtable()->select()->where("cookie='$cookie' and csrf='$csrf'");
       $resultset = $this->getDbTable()->fetchAll($select);
       if(sizeof($resultset)>0){
          $csrfhash = $this->hydrateFromResult($resultset[0]);
       }else{
          $csrfhash = new Application_Model_Csrfhash(array("cookie"=>$cookie,"csrf"=>$csrf));
          $this->save($csrfhash);
       }
       return $csrfhash;
       
    }

    /***************************************************
    * Find all the csrfs for a given user.
    */ 
    public function findAllForUser($cookie) {
        $select = $this->getdbtable()->select()->where("cookie='$cookie' and created > DATE_SUB(now(),interval 1 minute)");
        $resultset = $this->getDbTable()->fetchAll($select);
        $entries   = array();
        foreach ($resultSet as $row) {
            $entries[] = $this->hydrateFromResult($row);
        }
        return $entries;
    }


    /***************************************************
    * Find all the csrfs for a given user and return   
    * just an array of the acceptable csrf strings.
    */ 
    public function findAllCsrfForUser($cookie) {
        $select = $this->getdbtable()->select()->where("cookie='$cookie' and created > DATE_SUB(now(),interval 1 minute)");  
        $resultset = $this->getDbTable()->fetchAll($select);
        $entries   = array();
        foreach ($resultset as $row) {
            $entries[] = $row->csrf;
        }
        return $entries;
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
    * Take a DB result and build an object
    */
    function hydrateFromResult($row){
       $entry = new Application_Model_Cookie();
       $entry->setCookie($row->cookie)
             ->setCsrf($row->csrf)
             ->setCreated($row->created);
        return $entry;
    } 
}

