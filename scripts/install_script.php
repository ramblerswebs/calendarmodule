<?php
// No direct access to this file
defined('_JEXEC') or die;

/**
 * Script file of HelloWorld module
 */
class mod_ra_calendar_downloadInstallerScript
{
	/**
	 * Method to install the extension
	 * $parent is the class calling this method
	 *
	 * @return void
	 */
	function install($parent)
	{
            echo '<p>Please wait while we download/update the current Ramblers Area & Group Information</p>';
            // Get a db connection.
            $db = JFactory::getDbo();


            // Now we need to get the Group information
            $ra_feed = new RJsongroupsFeed("http://www.ramblers.org.uk/api/lbs/groups/");
            $groups = $ra_feed->getGroups()->allGroups();
            foreach ($groups as $group)
            {
                // Insert values.
                if ($group->groupScope == 'G' || $group->groupScope == 'S')
                {
                    // Create a new query object.
                    $query = $db->getQuery(true);
                    // Insert columns.
                     $columns = array('code', 'description');
                    $values = array($db->quote($group->groupCode), $db->quote($group->groupCode . ':' . $group->groupName));

                    // Prepare the insert query.
                    $query
                        ->insert($db->quoteName('#__ra_groups'))
                        ->columns($db->quoteName($columns))
                        ->values(implode(',', $values));

                    // Set the query using our newly populated query object and execute it.
                    $db->setQuery($query);
                    $db->execute();

                    unset($columns);
                    unset($values);
                    unset($query);
                }
            }
            echo '<p>Ramblers Group Codes have been updated.</p>';
	}

	/**
	 * Method to uninstall the extension
	 * $parent is the class calling this method
	 *
	 * @return void
	 */
	function uninstall($parent)
	{
		echo '<p>The module has been uninstalled</p>';
	}

	/**
	 * Method to update the extension
	 * $parent is the class calling this method
	 *
	 * @return void
	 */
	function update($parent)
	{
            $db = JFactory::getDbo();
            
            // First Delete all the records
            $query = $db->getQuery(true);
            $query->delete($db->quoteName('#__ra_groups'));
            $db->setQuery($query);
            $result = $db->execute();
            unset($result);
            unset($query);
            unset($db);
            
            // Re-install the groups
            $this->install($parent);
        }

	/**
	 * Method to run before an install/update/uninstall method
	 * $parent is the class calling this method
	 * $type is the type of change (install, update or discover_install)
	 *
	 * @return void
	 */
	function preflight($type, $parent)
	{
		// echo '<p>Anything here happens before the installation/update/uninstallation of the module</p>';
	}

	/**
	 * Method to run after an install/update/uninstall method
	 * $parent is the class calling this method
	 * $type is the type of change (install, update or discover_install)
	 *
	 * @return void
	 */
	function postflight($type, $parent)
	{
		//echo '<p>Anything here happens after the installation/update/uninstallation of the module</p>';
	}
}

class RJsongroupsFeed {

    private $groups;
    private $rafeedurl;
    private $feederror;
    private $feednotfound;
    private $displayLimit = 0;

    public function __construct($rafeedurl) {
        $this->rafeedurl = strtolower($rafeedurl);
        $this->feederror = "Invalid groups feed (invalid json format): " . $this->rafeedurl;
        $this->feednotfound = "Groups feed error(url not found): " . $this->rafeedurl;
        $this->groups = new RJsonwalksGroups(NULL);
        $this->readFeed($rafeedurl);
    }

    private function readFeed($rafeedurl) {
        $CacheTime = 60; // minutes
        $cacheLocation = $this->CacheLocation();
        $srfr = new RFeedhelper($cacheLocation, $CacheTime);
        $contents = $srfr->getFeed($rafeedurl);
        switch ($contents) {
            case NULL:
                echo '<b>Groups feed: Unable to read feed: ' . $rafeedurl . '</b>';
                break;
            case "":
                echo '<b>Groups feed: No groups found</b>';
                break;
            case "[]":
                echo '<b>Groups feed empty: No groups found</b>';
                break;
            default:
                $json = json_decode($contents);
                unset($contents);
                $error = 0;
                if (json_last_error() == JSON_ERROR_NONE) {
                    foreach ($json as $value) {
                        $ok = $this->checkJsonProperties($value);
                        $error+=$ok;
                    }
                    if ($error > 0) {
                        echo '<br/><b>Groups feed: Json file format not supported</b>';
                    } else {
                        $this->groups = new RJsonwalksGroups($json);
                    }
                    unset($json);
                    break;
                } else {
                    echo '<br/><b>Groups feed: feed is not in Json format</b>';
                }
        }
    }

    public function getGroups() {
        return $this->groups;
    }

    public function clearCache() {
        $cacheFolderPath = $this->CacheLocation();
// Check if the cache folder exists
        if (file_exists($cacheFolderPath) && is_dir($cacheFolderPath)) {
// clear files from folder
            $files = glob($cacheFolderPath . '/*'); // get all file names
            echo "<h2>Feed cache has been cleared</h2>";
            foreach ($files as $file) { // iterate files
                if (is_file($file)) {
                    unlink($file); // delete file}
                }
            }
        }
// reread feed
        $this->readFeed($this->rafeedurl);
    }

    private function checkJsonProperties($item) {
        $properties = array("groupCode", "description", "name", "scope", "url");

        foreach ($properties as $value) {
            if (!$this->checkJsonProperty($item, $value)) {
                return 1;
            }
        }

        return 0;
    }

    private function checkJsonProperty($item, $property) {
        if (property_exists($item, $property)) {
            return true;
        }
        return false;
    }

    private function CacheLocation() {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }
        return 'cache' . DS . 'ra_groupsfeed';
    }
}

class RJsonwalksGroups {

    private $arrayofgroups;
    private $sortorder1;
    private $sortorder2;
    private $sortorder3;

    public function __construct($json) {
        $this->arrayofgroups = array();
        if ($json != NULL) {
            foreach ($json as $value) {
                $walk = new RJsonwalksGroup($value);
                $this->arrayofgroups[] = $walk;
            }
        }
    }

    public function addGroup($group) {
        $this->arrayofgroups[] = $group;
    }

    private function notInItems($items, $text) {
        if ($items == NULL) {
            return true;
        }
        foreach ($items->getItems() as $item) {
            if ($this->contains(strtolower($text), strtolower($item->getName()))) {
                return false;
            }
        }
        return true;
    }

    private function contains($needle, $haystack) {
        return strpos($haystack, $needle) !== false;
    }

    public function sort($sortorder1, $sortorder2, $sortorder3) {
        $this->sortorder1 = $sortorder1;
        $this->sortorder2 = $sortorder2;
        $this->sortorder3 = $sortorder3;
        usort($this->arrayofwalks, array($this, "sortData1"));
    }

    private function sortData1($a, $b) {
        $val1 = $a->getValue($this->sortorder1);
        $val2 = $b->getValue($this->sortorder1);
        if ($val1 == $val2) {
            return $this->sortData2($a, $b);
        }
        return ($val1 < $val2 ) ? -1 : 1;
    }

    private function sortData2($a, $b) {
        $val1 = $a->getValue($this->sortorder2);
        $val2 = $b->getValue($this->sortorder2);
        if ($val1 == $val2) {
            return $this->sortData3($a, $b);
        }
        return ($val1 < $val2 ) ? -1 : 1;
    }

    private function sortData3($a, $b) {
        $val1 = $a->getValue($this->sortorder3);
        $val2 = $b->getValue($this->sortorder3);
        if ($val1 == $val2) {
            return 0;
        }
        return ($val1 < $val2 ) ? -1 : 1;
    }

    public function allGroups() {
        return $this->arrayofgroups;
    }

    public function totalGroups() {
        $no = count($this->arrayofgroups);
        return $no;
    }
}

class RJsonwalksGroup {

	  // administration items
    public $groupCode;              // group code e.g. SR01
    public $groupName;              // the group name e.g. Derby & South Derbyshire
		public $groupScope;							// Scope of the group - A = Area, G = Group
    public $description = "";       // description of walk with html tags removed
		public $url;										// URL of the group website

    public function __construct($item) {

        try {
            $this->groupCode = $item->groupCode;
            $this->groupName = $item->name;
						$this->groupScope = $item->scope;
					  $this->url = $item->url;
            $this->description = $item->description;
            $this->description = str_replace("\r", "", $this->description);
            $this->description = str_replace("\n", "", $this->description);
            $this->description = str_replace("&nbsp;", " ", $this->description);
            $this->description = strip_tags($this->description);
            $this->description = trim($this->description);
        } catch (Exception $ex)
				{
            $this->errorFound = 2;
        }
    }
}

class RFeedhelper {

    private $cacheFolderPath;
    private $cacheTime;
    private $status = self::OK;

    const OK = 0;
    const READFAILED = 1;
    const READEMPTY = 2;

    function __construct($cacheLocation, $cacheTime) {
        if (isset($cacheLocation)) {
            $this->cacheTime = $cacheTime * 60; // convert to seconds
            $this->cacheFolderPath = JPATH_SITE . DS . $cacheLocation;
        } else {
            die("Invalid call to RJsonwalksFeedhelper");
        }
    }

    function getFeed($feedfilename) {
        // Returns
        //   NULL is cannot read feed
        //   blank if feed empty
        //   contents if all ok
        $this->status = self::OK;
        $url = trim($feedfilename);
        $content = '';
        ini_set('max_execution_time', 120);
        $cachedFile = $this->createCachedFileFromUrl($feedfilename);
        if ($cachedFile <> '') {
            $content = file_get_contents($cachedFile);
            if ($content === false) {
                $content = '';
            }
        }
        switch ($this->status) {
            case self::OK:
                return $content;
                break;
            case self::READEMPTY:
                return '';
                break;
            case self::READFAILED:
                return NULL;
                break;
            default:
                return NULL;
                break;
        }
        return NULL;
    }

    // Get remote file
    private function createCachedFileFromUrl($feedfilename) {
        // Check if the cache folder exists
        if (file_exists($this->cacheFolderPath) && is_dir($this->cacheFolderPath)) {
            // all OK
        } else {
            mkdir($this->cacheFolderPath);
        }

        jimport('joomla.filesystem.file');

        $url = trim($feedfilename);
        $tmpFile = $this->getCacheName($url);

        // Check if a cached copy exists otherwise create it
        if (file_exists($tmpFile) && is_readable($tmpFile) && (filemtime($tmpFile) + $this->cacheTime) > time()) {
            $result = $tmpFile;
        } else {
            // Get file
            if (substr($url, 0, 4) == "http") {
                // remote file
                if (ini_get('allow_url_fopen')) {
                    // file_get_contents
                    if ($this->urlExists($url)) {
                        $fgcOutput = file_get_contents($url);
                        if ($fgcOutput === false) {
                            $status = self::READFAILED;
                            return '';
                        } else {
                            JFile::write($tmpFile, $fgcOutput);
                        }
                    } else {
                        $status = self::READFAILED;
                        return '';
                    }
                } elseif (in_array('curl', get_loaded_extensions())) {
                    // cURL
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $chOutput = curl_exec($ch);
                    curl_close($ch);
                    JFile::write($tmpFile, $chOutput);
                } else {
                    // fsockopen
                    $readURL = parse_url($url);
                    $relativePath = (isset($readURL['query'])) ? $readURL['path'] . "?" . $readURL['query'] : $readURL['path'];
                    $fp = fsockopen($readURL['host'], 80, $errno, $errstr, 5);
                    if (!$fp) {
                        $status = self::READFAILED;
                    } else {
                        $out = "GET " . $relativePath . " HTTP/1.1\r\n";
                        $out .= "Host: " . $readURL['host'] . "\r\n";
                        $out .= "Connection: Close\r\n\r\n";
                        fwrite($fp, $out);
                        $header = '';
                        $body = '';
                        do {
                            $header .= fgets($fp, 128);
                        } while (strpos($header, "\r\n\r\n") === false); // get the header data
                        while (!feof($fp))
                            $body .= fgets($fp, 128); // get the actual content
                        fclose($fp);
                        JFile::write($tmpFile, $body);
                    }
                }

                $result = $tmpFile;
            } else {

                // local file
                $result = $url;
            }
        }

        return $result;
    }

    private function urlExists($url) {
        $exists = true;
        $file_headers = @get_headers($url);
        if ($file_headers == false) {
            return false;
        }
        $InvalidHeaders = array('404', '403', '500');
        foreach ($InvalidHeaders as $HeaderVal) {
            if (strstr($file_headers[0], $HeaderVal)) {
                $exists = false;
                break;
            }
        }
        return $exists;
    }

    public function clearCache() {

        // Check if the cache folder exists
        if (file_exists($this->cacheFolderPath) && is_dir($this->cacheFolderPath)) {
            // clear files from folder
            $files = glob($this->cacheFolderPath . '/*'); // get all file names
            echo "<h2 class='feedrefresh'>Feed cache will be refreshed</h2>";
            foreach ($files as $file) { // iterate files
                if (is_file($file)) {
                    unlink($file); // delete file}
                }
            }
        }
    }

    private function getCacheName($feedfilename) {
        $url = trim($feedfilename);
        if (substr($url, 0, 4) == "http") {
            $turl = explode("?", $url);
            $matchComponents = array("#(http|https)\:\/\/#s", "#www\.#s");
            $replaceComponents = array("", "");
            $turl = preg_replace($matchComponents, $replaceComponents, $turl[0]);
            $turl = str_replace(array("/", "-", "."), array("_", "_", "_"), $turl);
            $tmpFile = $this->cacheFolderPath . DS . urlencode($turl) . '.cache';
            /* $turl = explode("?", $url); CEV replacement code to handle walksfeed options */
            $turl = $url;
            $matchComponents = array("#(http|https)\:\/\/#s", "#www\.#s");
            $replaceComponents = array("", "");
            $turl = preg_replace($matchComponents, $replaceComponents, $turl);
            $turl = str_replace(array("/", "-", "."), array("_", "_", "_"), $turl);
            $turl = str_replace(array("?", "&", ",", "="), array("_", "", "", ""), $turl);
            $tmpFile = $this->cacheFolderPath . DS . urlencode($turl) . '.cache';
            /* end of change */
        } else {
            $tmpFile = $this->cacheFolderPath . DS . 'cached_' . md5($url);
        }
        return $tmpFile;
    }
}
?>
