<?php
/***********************************************
* Data Mapper for the UrlCache table. Map the
* model to the data source.
*/ 

class Application_Model_UrlcacheMapper{
    protected $_dbTable;

    /***********************************************
    * Set which DB Table we're using. This ought
    * to always be "urlcache" I would think so not
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
            $this->setDbTable('Application_Model_DbTable_Urlcache');
        }
        return $this->_dbTable;
    }

    /******************************************************
    * Save the data from the model into the database table
    */ 
    public function save(Application_Model_Urlcache $urlcache) {
        $data = array(
            'domain'   => $urlcache->getDomain(),
            'path'     => $urlcache->getPath(),
            'title'    => $urlcache->getTitle(),
            'postcount'=> $urlcache->getPostCount(),
            'hotness'=> $urlcache->getHotness(),
            'hottime'=> $urlcache->getHotTime(),
            'created' => date('Y-m-d H:i:s'),
            'updated' => date('Y-m-d H:i:s'),
        );
 
        if (null === ($id = $urlcache->getId())) {
            unset($data['id']);
            $this->getDbTable()->insert($data);
        } else {
            unset($data['created']);    //Added by Pre: Don't want to change Created date when updating.
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }


    /*************************************************
    * Find the hottest few urls. 
    */
    public function findHottest($num){
      $ret = array();
      $where = 'title is not null';
      $select=$this->getDbTable()->select()->where($where)->order("hotness*exp(-".Application_Model_Urlcache::HOTNESS_COOLRATE."*(UNIX_TIMESTAMP(now())-hottime)) desc")->limit($num);
      $hottest = $this->getDbTable()->fetchAll($select);
      foreach($hottest as $row){
        $urlcache = new Application_Model_Urlcache();
        $urlcache->setId($row->id)
                 ->setDomain($row->domain)
                 ->setPath($row->path)
                 ->setTitle($row->title)
                 ->setPostCount($row->postcount)
                 ->setHotness($row->hotness)
                 ->setHotTime($row->hottime)
                 ->setCreated($row->created)
                 ->setUpdated($row->updated);
        $ret[]=$urlcache;
      }
      return $ret;
    }




    /*************************************************
    * Find all the entries which have no title. These
    * will be the ones we need to update in the cron
    */
    public function findAllUntitled(){
      $ret = array();
      $where = 'title is null';
      $select=$this->getDbTable()->select()->where($where);
      $untitled = $this->getDbTable()->fetchAll($select);
      foreach($untitled as $row){
        $urlcache = new Application_Model_Urlcache();
        $urlcache->setId($row->id)
                 ->setDomain($row->domain)
                 ->setPath($row->path)
                 ->setTitle($row->title)
                 ->setPostCount($row->postcount)
                 ->setHotness($row->hotness)
                 ->setHotTime($row->hottime)
                 ->setCreated($row->created)
                 ->setUpdated($row->updated);
        $ret[]=$urlcache;
      }
      return $ret;
    }



    /*************************************************
    * Find a single rows based on it's domain and path
    */
    public function findOneWhere($domain,$path){
      $urlcache = new Application_Model_Urlcache();
      $where = 'domain="'.$domain.'" and path="'.$path.'"';
      $select=$this->getDbTable()->select()->where($where);
      $id = $this->getDbTable()->fetchAll($select);
      if(sizeof($id)>=1){
        $id = $id[0]->id;
        $this->find($id,$urlcache);
      }else{
        $urlcache->setDomain($domain);
        $urlcache->setPath($path);
        $urlcache->setPostCount(0);
        $urlcache->setHotness(Application_Model_Urlcache::HOTNESS_INITIAL);
        $urlcache->setHotTime(time());
      }
      return $urlcache;
    }


    /*********************************************************
    * Find a database table's row and hydrate it into a
    * model object. Surprisingly we seem to be setting
    * data on an object already created and passed as a
    * param rather than just creating the object and
    * returning it. Wonder why that is? 
    */ 
    public function find($id, Application_Model_Urlcache $urlcache) {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $urlcache->setId($row->id)
                 ->setDomain($row->domain)
                 ->setPath($row->path)
                 ->setTitle($row->title)
                 ->setPostCount($row->postcount)
                 ->setHotness($row->hotness)
                 ->setHotTime($row->hottime)
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
            $entry = new Application_Model_Urlcache();
            $entry->setId($row->id)
                  ->setDomain($row->domain)
                  ->setPath($row->path)
                  ->setTitle($row->title)
                  ->setPostCount($row->postcount)
                  ->setHotness($row->hotness)
                  ->setHotTime($row->hottime)
                  ->setCreated($row->created)
                  ->setUpdated($row->updated);
            $entries[] = $entry;
        }
        return $entries;
    }

}
