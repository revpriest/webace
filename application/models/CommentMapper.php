<?php
/***********************************************
* Data Mapper for the Comment table. Map the
* model to the data source.
*/ 

class Application_Model_CommentMapper{
    protected $_dbTable;

    /***********************************************
    * Set which DB Table we're using. This ought
    * to always be "comment" I would think so not
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
            $this->setDbTable('Application_Model_DbTable_Comment');
        }
        return $this->_dbTable;
    }

    /******************************************************
    * Save the data from the model into the database table
    */ 
    public function save(Application_Model_Comment $comment) {
        $data = array(
            'domain'   => $comment->getDomain(),
            'path'     => $comment->getPath(),
            'cookie'   => $comment->getCookie(),
            'nick'     => $comment->getNick(),
            'email'    => $comment->getEmail(),
            'ip'       => $comment->getIp(),
            'content'  => $comment->getContent(),
            'reply_to' => $comment->getReplyTo(),
            'created' => date('Y-m-d H:i:s'),
            'updated' => date('Y-m-d H:i:s'),
        );
 
        if (null === ($id = $comment->getId())) {
            unset($data['id']);
            $this->getDbTable()->insert($data);
        } else {
            unset($data['created']);    //Added by Pre: Don't want to change Created date when updating.
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }

    /*********************************************************
    * Find a database table's row and hydrate it into a
    * model object. Surprisingly we seem to be setting
    * data on an object already created and passed as a
    * param rather than just creating the object and
    * returning it. Wonder why that is? 
    */ 
    public function find($id, Application_Model_Comment $comment) {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $comment->setId($row->id)
                ->setDomain($row->domain)
                ->setPath($row->path)
                ->setCookie($row->cookie)
                ->setNick($row->nick)
                ->setEmail($row->email)
                ->setIp($row->ip)
                ->setContent($row->content)
                ->setReplyTo($row->reply_to)
                ->setCreated($row->created)
                ->setUpdated($row->updated);
    }

    /***************************************************
    * Grab everything from the DB table and put it into
    * an array of model objects. This has a lot of
    * ->setX($row->X) lines which are duplicated above.
    * Maybe they sould be taken into a function of their
    * own?
    */ 
    public function fetchAll() {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Comment();
            $entry->setId($row->id)
                  ->setDomain($row->domain)
                  ->setPath($row->path)
                  ->setCookie($row->cookie)
                  ->setNick($row->nick)
                  ->setEmail($row->email)
                  ->setIp($row->ip)
                  ->setContent($row->content)
                  ->setReplyTo($row->reply_to)
                  ->setCreated($row->created)
                  ->setUpdated($row->updated);
            $entries[] = $entry;
        }
        return $entries;
    }

}

