<?php
/***************************************************
* A model for the blogs.
*/

class Application_Model_Blog {

  /************************************************
  * Get a list of all the blogs, basically by 
  * ls in the directory
  */
  public static function getBlogList(){
       $blogs = array();
       $handler = opendir("../application/views/scripts/blogs");
       while ($file = readdir($handler)) {
         if(preg_match("/^(\d\d\d\d)(\d\d)(\d\d)\-(.*)\.phtml$/",$file,&$reg)){
            $blog = array();
            $blog['fulldate']=$reg[1].$reg[2].$reg[3];
            $blog['year']=$reg[1];
            $blog['month']=$reg[2];
            $blog['day']=$reg[3];
            $blog['title']=$reg[4];
            $blog['file']=$file;
            $blogs[] = $blog;
         }
       }
       closedir($handler);
       usort($blogs,function($b1,$b2){return -strcmp($b1['fulldate'],$b2['fulldate']);});
       return $blogs;
     }
}

