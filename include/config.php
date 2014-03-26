<?PHP

require_once($_SERVER['DOCUMENT_ROOT'] . "/include/mainactions.php");

/*** NETWORK CONFIG ***/
/*$ip_public="54.186.64.204";*/
/*$ip_private="192.168.5.128";*/
$ip_public="www.jwstream.org";
$ip_private="www.jwstream.org";

$myhostname = gethostname();

/*** MYSQL DATABASE CONFIG ***/
$host = 'localhost';
$uname = 'root';
$pwd = 'Filippesi4:8';
$database = 'streamlisdb';

/*** FILE SYSTEM CONFIG ***/
$live_tmp_flash_path = "/tmp/stream/flash/";
$live_tmp_hls_path = "/tmp/stream/hls/";

// Do not forget the '/' char at the end of path
// These values must be the same as you can see into the nginx config file
$ondemand_hls_record_filepath = '/var/stream/hls/';
$ondemand_flash_record_filepath = '/var/stream/flash/';

/*** SITE CONFIG ***/

$mainactions = new MainActions($host, $uname, $pwd, $database);

//Provide your site name here
$mainactions->SetWebsiteName('JW LIS Streaming');

//Provide the email address where you want to get notifications
$mainactions->SetAdminEmail('marco.casu@gmail.com');

//For better security. Get a random string from this link: http://tinyurl.com/randstr
// and put it here
$mainactions->SetRandomKey('ykroeSaT1Ma53hP');

?>
