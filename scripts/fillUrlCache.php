<?php
  /****************************************************
  * The urlCache tells us what a given domain/path
  * points at. It tells us the title, and the
  * number of comments attached, and the dates of
  * the first and most-recent. But the main app
  * only creates these caches, it doesn't fill in
  * the titles, and it doesn't create any icons.
  * We do that here, every few minutes or so.
  * We find all the urlcache entries with null
  * title and go fetch that page so we can fill
  * in the title, and ideally create a png for the
  * page in thumbnail too but I suspect that last
  * bit may have to wait, it's complex, probably needs
  * some kinda X drivers or external API. For now
  * just grab the titles.
  *
  * I see this is likely to become a general
  * clean-up type cron. Adding something to delete
  * old unused CSRF hashes
  */

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
set_include_path(implode(PATH_SEPARATOR, array( APPLICATION_PATH . '/../library', get_include_path(),)));
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

// Define some CLI options
$getopt = new Zend_Console_Getopt(array(
    'env|e-s'    => 'Application environment (defaults to development)',
    'help|h'     => 'Help -- usage message',
));
try {
    $getopt->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    // Bad options passed: report usage
    echo $e->getUsageMessage();
    return false;
}
 
// If help requested, report usage message
if ($getopt->getOption('h')) {
    echo $getopt->getUsageMessage();
    return true;
}

$env      = $getopt->getOption('e');
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (null === $env) ? 'development' : $env);
 
// Initialize Zend_Application
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
 
// Initialize and retrieve DB resource
$bootstrap = $application->getBootstrap();
$bootstrap->bootstrap('db');
$dbAdapter = $bootstrap->getResource('db');

/************************** That's the boostrap over, here's the code ******************/

/********************************************
 * Get the title element from a URL
 */

function getTitle($url) {
    $fh = fopen($url, "r");
    $str = fread($fh, 4096);
    fclose($fh);
    $str2 = strtolower($str);
    $start = strpos($str2, "<title>")+7;
    $len   = strpos($str2, "</title>") - $start;
    $ret = substr($str, $start, $len);
    if(is_string($ret)){return $ret;}
    return null;
}

/*******************************************
* Delete unused CSRF hashes
*/
function deleteOldCsrf(){
  echo "  Deleting old stale CSRFs.";
  $mapper = new Application_Model_CsrfhashMapper();
  $delete = $mapper->getdbtable()->delete("created<date_sub(now(),interval 1 minute)");
}

function fillUrlCache(){
  echo "  Filling URL Cache...";
  $mapper = new Application_Model_UrlcacheMapper();
  foreach($mapper->findAllUntitled() as $urlcache){
    $url = $urlcache->getDomain().$urlcache->getPath();
    $title = getTitle($url);
    if($title==null){$title=$urlcache->getPath();}
    echo "Setting title of $url to $title...\n"; 
    $urlcache->setTitle($title);
    $mapper->save($urlcache);
  }
}

echo "checking ".time()."\n";
fillUrlCache();
deleteOldCsrf();



