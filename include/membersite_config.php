<?PHP

require_once($_SERVER['DOCUMENT_ROOT'] . "/include/fg_membersite.php");

$ip_public="54.213.120.163";
$ip_private="192.168.5.128";

$myhostname = gethostname();

$ondemand_hls_path="/var/stream/hls";
$ondemand_flash_path="/var/stream/flv";

$fgmembersite = new FGMembersite();
$dbactions = $fgmembersite->GetDBActionsInstance();

//Provide your site name here
$fgmembersite->SetWebsiteName('JW LIS Streaming');

//Provide the email address where you want to get notifications
$fgmembersite->SetAdminEmail('marco.casu@gmail.com');

//Provide your database login details here:
//hostname, user name, password, database name and table name
//note that the script will create the table (for example, fgusers in this case)
//by itself on submitting register.php for the first time
$dbactions->InitDB(/*hostname*/'localhost',
                      /*username*/'root',
                      /*password*/'Filippesi4:8',
                      /*database name*/'streamlisdb',
                      /*table name*/'users');

//For better security. Get a random string from this link: http://tinyurl.com/randstr
// and put it here
$fgmembersite->SetRandomKey('ykroeSaT1Ma53hP');

?>
