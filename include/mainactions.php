<?PHP
/*
    Registration/Login script from HTML Form Guide
    V1.0

    This program is free software published under the
    terms of the GNU Lesser General Public License.
    http://www.gnu.org/copyleft/lesser.html
    

This program is distributed in the hope that it will
be useful - WITHOUT ANY WARRANTY; without even the
implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.

*/
require_once("class.phpmailer.php");
require_once("utils.php");
require_once("dbactions.php");
require_once("fsactions.php");
require_once("ffmpeg-php-master/FFmpegAutoloader.php");

class MainActions
{
    var $admin_email;
    var $from_address;
    
    var $username;
    var $pwd;
    var $randomKey;
    var $utilsInstance;
    var $dbactionsInstance;
    var $fsactionsInstance;
 
    var $error_message;
    
    //-----Initialization -------
    function MainActions($host, $uname, $pwd, $database)
    {
        $this->sitename = 'YourWebsiteName.com';
        $this->randomKey = uniqid("usr_");

	$this->utilsInstance = new Utils();
        $this->dbactionsInstance = new DBActions($host, $uname, $pwd, $database);
        $this->fsactionsInstance = new FSActions();
    }

    function GetFSActionsInstance() 
    {
	return $this->fsactionsInstance;
    }

    function GetDBActionsInstance() 
    {
	return $this->dbactionsInstance;
    }

    function GetUtilsInstance() 
    {
	return $this->utilsInstance;
    }
    
/*** MEMBERS ***/
    function UserId()
    {
	return isset($_SESSION["userdata"]['user_id'])?$_SESSION["userdata"]['user_id']:'';
    }
    function UserName()
    {
	return isset($_SESSION["userdata"]['username'])?$_SESSION["userdata"]['username']:'';
    }
    function UserFullName()
    {
        return isset($_SESSION["userdata"]['user_fullname'])?$_SESSION["userdata"]['user_fullname']:'';
    }
    
    function UserEmail()
    {
        return isset($_SESSION["userdata"]['user_email'])?$_SESSION["userdata"]['user_email']:'';
    }
    
    function UserGroupId()
    {
        return isset($_SESSION["userdata"]['user_group_id'])?$_SESSION["userdata"]['user_group_id']:'';
    }
    
    function UserGroupName()
    {
        return isset($_SESSION["userdata"]['user_group_name'])?$_SESSION["userdata"]['user_group_name']:'';
    }
    
    function GetSessionUserRole()
    {
	    if(empty($_SESSION["userdata"]['user_role_id']))
	    {
		    $this->HandleError("User role for this session not found!");
		    return false;
	    }

	    return $_SESSION["userdata"]['user_role_id']; 
    }
/*** FINE MEMBERS ***/

    function SetAdminEmail($email)
    {
        $this->admin_email = $email;
    }
    
    function SetWebsiteName($sitename)
    {
        $this->sitename = $sitename;
    }
    
    //-------Main Operations ----------------------
    function CreateUser()
    {
        if(!isset($_POST['submitted']))
        {
           return false;
        }
        
        $uservars = array();
        
        $this->CollectRegistrationSubmission($uservars);
        
	$user_id = $this->SaveUserDataIntoDatabase($uservars);
	
        if(!$user_id)
        {
            return false;
        }
	
        $uservars['user_id'] = $this->utilsInstance->Sanitize($user_id);
	
        // Sent mail 
        $mailTo = array();
        $mailTo[] = array("email" => $this->admin_email, "name" => "admin");
        $mailTo[] = array("email" => $this->UserEmail(), "name" => $this->UserFullName());

        $mailSubject = $this->sitename . " - Creazione nuovo utente: ".$uservars['name'];

        $mailBody = "Ciao caro fratello <b>".$this->UserFullName()."</b>, \r\n\r\n".
            "Un nuovo utente è stato creato. ".
            "Di seguito puoi vedere le sue credenziali:\r\n".
            "\r\n".
            "Nome completo: ".$uservars['name']."\r\n".
            "Congregazione o gruppo: ".$uservars['group_name']."\r\n\r\n".
                
            "<b>Username:</b> ".$uservars['username']."\r\n".
            "<b>Password:</b> ".$uservars['password']."\r\n".
            "\r\n".
            "L'utente potrà fare login qui: https://www.streamlis.it/login.php\r\n".
            "\r\n".
            "\r\n".
            "Grazie per la collaborazione,\r\n".
            $this->sitename;

        if (!$this->SendMail($mailTo, $mailSubject, $mailBody))
        {
            error_log("\ERROR - CreateUser() SendMail() FAILED!");
        }
        
        return true;
    }

    function CreateGroup($createPublisherLink = FALSE)
    {
        if(!isset($_POST['submitted']))
        {
           return false;
        }

        $groupvars = array();

        $this->CollectGroupRegistrationSubmission($groupvars);

        // Controllo se il gruppo esiste gia'
        if(!$this->dbactionsInstance->IsGroupFieldUnique($groupvars,'group_name'))
        {
            $this->HandleError("Il gruppo inserito esiste già. Per favore prova un altro nome.");
            return false;
        }            
        
        // Se il gruppo non esiste gia' lo creo nel database.
        if(!$this->dbactionsInstance->InsertGroupIntoDB($groupvars))
        {
            $this->HandleError("Creazione del gruppo nel database fallita!");
            return false;
        }
        
        if (!$createPublisherLink)
        {
            return true;
        }
        
        // Recupero il group id e se richiesto creo l'associazione con la congregazione dell'utente che esegue la richiesta.
        $viewerList = $this->dbactionsInstance->GetGroupIdByName($groupvars['group_name']);
        if(!$this->dbactionsInstance->AddViewersLink($viewerList, $this->UserGroupId()))
        {
            $this->HandleError("Associazione tra gruppo e congregazione fallita. Contattare l'amministratore per risolvere il problema.");
            return false;
        }

        return true;
    }

    function Login()
    {
        if(empty($_POST['username']))
        {
            $this->HandleError("Il campo username è vuoto!");
            return false;
        }
        
        if(empty($_POST['password']))
        {
            $this->HandleError("Il campo password è vuoto!");
            return false;
        }
        
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
	
	$sessionData = $this->dbactionsInstance->CheckLoginInDB($username,$password);
	
        if(!$sessionData)
        {
            return false;
        }

	$_SESSION["userdata"] = $sessionData;
        session_regenerate_id();
        
        // Set the cookie
        if (!isset($_COOKIE["userdata"]))
        {
            setcookie("userdata", json_encode($sessionData), 3600);
        }
	
	// Set the user logged flag and update last_update timestamp into the database.
        $_SESSION["userdata"]["last_update"] = time();
        $mysqlTime = date('Y-m-d H:i:s', $_SESSION["userdata"]["last_update"]);
        
        $this->dbactionsInstance->UpdateUserLoginStatus($username, true, $mysqlTime, true);
        
        return true;
    }
    
    function CheckLogin()
    {        
        if( empty($_SESSION["userdata"]) && empty($_COOKIE["userdata"]) )
        {
            //error_log("INFO - CheckLogin returned FALSE because of session has not found.");
            return false;
        }

        // Get session data from the memory or the cookie file.
        $sessionData = empty($_SESSION["userdata"]) ? (array) json_decode($_COOKIE["userdata"]) : $_SESSION["userdata"];
        
        // The users' session expire after 10800 sec = 3 hours
        if (time() - $sessionData['last_update'] > 10800)
        {
           $this->dbactionsInstance->UpdateUserLoginStatus($sessionData['username'], false);
           $_SESSION["userdata"] = NULL;
           unset($_SESSION["userdata"]);
           return false;
        }

        $_SESSION["userdata"]["last_update"] = time();
        $mysqlTime = date('Y-m-d H:i:s', $_SESSION["userdata"]["last_update"]);
        $this->dbactionsInstance->UpdateUserLoginStatus($sessionData['username'], true, $mysqlTime);
        
        return true;
    }
   
    function LogOut()
    {
	$userdata = $_SESSION["userdata"];
	$username = $userdata['username'];
	
	$this->dbactionsInstance->UpdateUserLoginStatus($username, false);
        
        $_SESSION["userdata"] = NULL;
        unset($_SESSION["userdata"]);
    }
    
    function EmailResetPasswordLink()
    {
        if(empty($_POST['username']))
        {
            $this->HandleError("Il campo username è vuoto!");
            return false;
        }
        $user_rec = array();
        if(!$this->dbactionsInstance->GetUserByUsername($_POST['username'], $user_rec))
        {
	    $this->HandleError("Impossibile recuperare le informazioni dell'utente!");
            return false;
        }
        if(!$this->SendResetPasswordLink($user_rec))
        {
            return false;
        }
        return true;
    }
    
    function ResetPassword()
    {
        if(empty($_GET['user_id']))
        {
            $this->HandleError("UserId is empty!");
            return false;
        }
	if(empty($_GET['email']))
        {
            $this->HandleError("Email is empty!");
            return false;
        }
        if(empty($_GET['code']))
        {
            $this->HandleError("Reset code is empty!");
            return false;
        }
	
	$email = trim($_GET['email']);
        $user_id = trim($_GET['user_id']);
        $code = trim($_GET['code']);
        
        $user_rec = array();
        if(!$this->dbactionsInstance->GetUserById($user_id,$user_rec))
        {
            return false;
        }
        
        if($this->GetResetPasswordCode($email, $user_rec['username']) != $code)
        {
            $this->HandleError("Bad reset code!");
            return false;
        }
        
        $new_password = $this->ResetUserPasswordInDB($user_rec);
        if(false === $new_password || empty($new_password))
        {
            $this->HandleError("Error updating new password");
            return false;
        }
        
        if(false == $this->SendNewPassword($user_rec,$new_password))
        {
            $this->HandleError("Error sending new password");
            return false;
        }
        return true;
    }
    
    function ChangePassword()
    {
        if(!$this->CheckLogin())
        {
            $this->HandleError("Not logged in!");
            return false;
        }
        
        if(empty($_POST['oldpassword']))
        {
            $this->HandleError("Il campo password attuale non può essere vuoto!");
            return false;
        }
        if(empty($_POST['newpassword']))
        {
            $this->HandleError("Il campo nuova password non può essere vuoto!");
            return false;
        }
        
        $user_rec = array();
        if(!$this->dbactionsInstance->GetUserById($this->UserId(),$user_rec))
        {
            return false;
        }
        
        $pwd = trim($_POST['oldpassword']);
        
        if($user_rec['password'] != md5($pwd))
        {
            $this->HandleError("The old password [".md5($pwd)."] does not match! [".$user_rec['password']."]");
            return false;
        }
        $newpwd = trim($_POST['newpassword']);
        
        if(!$this->dbactionsInstance->ChangePasswordInDB($user_rec, $newpwd))
        {
            return false;
        }
        return true;
    }
    
    function GetErrorMessage()
    {
        if(empty($this->error_message))
        {
            return '';
        }
        $errormsg = nl2br(htmlentities($this->error_message));
        return $errormsg;
    }    
    
    function HandleError($err)
    {
        $this->error_message .= $err."\r\n";
    }
    
    function GetFromAddress()
    {
        if(!empty($this->from_address))
        {
            return $this->from_address;
        }

        $from ="noreply@streamlis.it";
        
        return $from;
    } 
    
    function GetSessionVarName()
    {
        //$retvar = md5($this->rand_key);
        return substr($this->randomKey,0,15);
    }
    
    function GenerateRandomPassword($length)
    {
        if (empty($length))
        {
            return FALSE;
        }
        
        // Characters to use for the password
        //$str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.-+=_,!@$#*%<>[]{}";
        $str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.-+=;:!@$#*%";

        // Desired length of the password
        $pwlen = (int)$length;

        // Length of the string to take characters from
        $len = strlen($str);

        // RANDOM.ORG - We are pulling our list of random numbers as a 
        // single request, instead of iterating over each character individually
        $uri = "http://www.random.org/integers/?";
        $random = file_get_contents(
            $uri ."num=$pwlen&min=0&max=".($len-1)."&col=1&base=10&format=plain&rnd=new"
        );
        $indexes = explode("\n", $random);
        array_pop($indexes);

        // We now have an array of random indexes which we will use to build our password
        $pw = '';
        foreach ($indexes as $int){
            $pw .= substr($str, $int, 1);
        }

        // Password is stored in `$pw`
        return $pw;
    }
    
    function ResetUserPasswordInDB($user_rec)
    {
        $new_password = substr(md5(uniqid()),0,10);
        
        if(false == $this->dbactionsInstance->ChangePasswordInDB($user_rec,$new_password))
        {
            return false;
        }
        return $new_password;
    }
    
    function GetResetPasswordCode($email, $username)
    {
        $code = substr(md5($email.$username."vz749yEAm7"),0,10);
        //error_log("INFO - RESET PASSWORD CODE->[" . $code . "]");
        return $code;
    }
    
    function SendResetPasswordLink($user_rec)
    {
        $email = $user_rec['email'];
        $user_id = $user_rec['id_user'];
	
        $mailer = new PHPMailer();
        
        $mailer->CharSet = 'utf-8';
        
        $mailer->AddAddress($email,$user_rec['name']);
        
        $mailer->Subject = "Reimposta la tua password in ".$this->sitename;

        $mailer->From = $this->GetFromAddress();
        
        $link = $this->GetAbsoluteURLFolder().
                'resetpwd.php?user_id='. urlencode($user_id).
                '&email='. urlencode($email).
                '&code='. $this->GetResetPasswordCode($email,$user_rec['username']);

        $mailer->Body ="Caro fratello ".$user_rec['name']."\r\n\r\n".
        "Abbiamo ricevuto la tua richiesta per reimpostare la tua password in ".$this->sitename."\r\n\r\n".
        "Per favore clicca sul link qui sotto per completare la richiesta: \r\n".$link."\r\n\r\n".
        "Un abbraccio fraterno,\r\n".
        $this->sitename;
        
        if(!$mailer->Send())
        {
            return false;
        }
        return true;
    }
    
    function SendNewPassword($user_rec, $new_password)
    {
        $email = $user_rec['email'];
        
        $mailer = new PHPMailer();
        
        $mailer->CharSet = 'utf-8';
        
        $mailer->AddAddress($email,$user_rec['name']);
        
        $mailer->Subject = "Your new password for ".$this->sitename;

        $mailer->From = $this->GetFromAddress();
        
        $mailer->Body ="Hello ".$user_rec['name']."\r\n\r\n".
        "Your password is reset successfully. ".
        "Here is your updated login:\r\n".
        "username:".$user_rec['username']."\r\n".
        "password:$new_password\r\n".
        "\r\n".
        "Login here: ".$this->GetAbsoluteURLFolder()."/login.php\r\n".
        "\r\n".
        "Regards,\r\n".
        "Webmaster\r\n".
        $this->sitename;
        
        if(!$mailer->Send())
        {
            return false;
        }
        return true;
    }    
    
    function SendMail($mailTo, $mailSubject, $mailBody, $isHtml = FALSE)
    {
        $mailer = new PHPMailer();
        $mailer->CharSet = 'utf-8';
        $mailer->IsHTML($isHtml);
        
        foreach ($mailTo as $address) 
        {
            if ($address['name'] == "admin")
            {
                $mailer->addCC($address['email']);
            }
            else
            {
                $mailer->AddAddress($address['email'], $address['name']);
            }
        }
        
        $mailer->Subject = $mailSubject;
        $mailer->From = $this->GetFromAddress();
        $mailer->Body = $mailBody;
        
        if(!$mailer->Send())
        {
            return false;
        }
        return true;
    }
    
    function CollectRegistrationSubmission(&$uservars)
    {
        $uservars['name'] = $this->utilsInstance->Sanitize($_POST['name']);
        if (array_key_exists('email',$uservars)) 
        {
            $uservars['email'] = $_POST['email'] ? $this->utilsInstance->Sanitize($_POST['email']) : NULL;
        }
        $uservars['username'] = $this->utilsInstance->Sanitize($_POST['username']);
        $uservars['password'] = $this->utilsInstance->Sanitize($_POST['password']);
        
        if(!isset($_POST['group_name']) || empty($_POST['group_name']))
        {
            $uservars['group_name'] = $this->UserGroupName();
        }
        else
        {
            $uservars['group_name'] = $this->utilsInstance->Sanitize($_POST['group_name']);
        }
        
        if(!isset($_POST['user_role_name']) || empty($_POST['user_role_name']))
        {
            $uservars['user_role_name'] = "viewer";
        }
        else
        {
            $uservars['user_role_name'] = $this->utilsInstance->Sanitize($_POST['user_role_name']);
        }
    }
    
    function CollectGroupRegistrationSubmission(&$groupvars)
    {
        $groupvars['group_name'] = $this->utilsInstance->Sanitize($_POST['group_name']);
        
        // Salvo la variabile del POST 'group_type'
        if (!isset($_POST['group_type']) || empty($_POST['group_type']))
        {
            $groupvars['group_type'] = "Gruppo";
        }
        else
        {
            $groupvars['group_type'] = $this->utilsInstance->Sanitize($_POST['group_type']);
        }
        
        // Salvo la variabile del POST 'group_role_name'
        if (!isset($_POST['group_role_name']) || empty($_POST['group_role_name']) || $_POST['group_role_name'] == "Nessuno")
        {
            $groupvars['group_role_name'] = "viewer";
        }
        else
        {
            $groupvars['group_role_name'] = $this->utilsInstance->Sanitize($_POST['group_role_name']);
        }
    }
    
    function GetAbsoluteURLFolder()
    {
        $scriptFolder = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? 'https://' : 'http://';
        $scriptFolder .= $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
        return $scriptFolder;
    }
        
    function SaveUserDataIntoDatabase(&$uservars)
    {
        if(!$this->dbactionsInstance->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }
        if(!$this->dbactionsInstance->IsFieldUnique($uservars,'username'))
        {
            $this->HandleError("Lo username scelto è già utilizzato. Per favore cambia il tuo uername.");
            return false;
        }
	
	$user_id = $this->dbactionsInstance->InsertIntoDB($uservars);
        if(!$user_id)
        {
            $this->HandleError("Inserting to Database failed!");
            return false;
        }
        return $user_id;
    }
    
    function ConvertOnDemandVideo($streamName, $onDemandFlvFilename, $onDemandMp4RecordFilepath, $onDemandFlvRecordFilepath)
    {
        $videoBasename = basename($onDemandFlvFilename, '.flv');
        $videoMp4Filename = $videoBasename . ".mp4";
        $videoMp4Dir = $onDemandMp4RecordFilepath.strtolower($streamName);
        $videoFlvDir = $onDemandFlvRecordFilepath.strtolower($streamName);

        // SE LA CARTELLA DEL VIDEO ONDEMAND MP4 NON ESISTE, LA CREO
        if (!file_exists($videoMp4Dir))
        {
            mkdir($videoMp4Dir, 0755, true);
            error_log("INFO - ConvertOnDemandVideo() Created folder [".$videoMp4Dir."]");
        }

        $docRoot = getenv("DOCUMENT_ROOT");

        if (file_exists($videoMp4Dir.'/'.$videoMp4Filename))
        {
            error_log("WARNING - ConvertOnDemandVideo() Il file [".$onDemandMp4RecordFilepath.$videoMp4Filename."] esiste gia'.");
        }
        else
        {
            // ESEGUO LA CONVERSIONE DAL .FLV A .MP4 TRAMITE LO SCRIPT BASH
            $output = shell_exec($docRoot.'/scripts/convert_video.bash '.$videoFlvDir."/".$onDemandFlvFilename.' '.$videoMp4Dir.'/'.$videoMp4Filename.' '.$onDemandFlvFilename);   
        }

        // CREO IL LINK SIMBOLICO AL FILE MP4
        if (is_link($onDemandMp4RecordFilepath.$videoMp4Filename))
        {
            unlink($onDemandMp4RecordFilepath.$videoMp4Filename);
        }
        if (!symlink($videoMp4Dir."/".$videoMp4Filename, $onDemandMp4RecordFilepath.$videoMp4Filename))
        {
            error_log("ERROR - ConvertOnDemandVideo() Creazione del link simbolico [".$onDemandMp4RecordFilepath.$videoMp4Filename."] fallita!");
            return 1;
        }
        
        error_log("INFO - ConvertOnDemandVideo() Creazione del link simbolico [".$onDemandMp4RecordFilepath.$videoMp4Filename."] riuscita!");
        
        return 0;
    }
    
    function ConvertOnDemandVideos($ondemandVideoList, $onDemandMp4RecordFilepath, $onDemandFlvRecordFilepath)
    {
        $ondemandVideoInfos = $this->dbactionsInstance->GetOndemandEventsByIds($ondemandVideoList);

        if (!$ondemandVideoInfos)
        {
            throw new Exception("GetOndemandEventsByIds() FAILED! - " . $this->dbactionsInstance->GetErrorMessage());
        }        
        
        $videoToConvertNumber = mysql_num_rows($ondemandVideoInfos);

        if ($videoToConvertNumber < 1)
        {
            throw new Exception("GetOndemandEventsByIds() ritorna 0 record (forse i video selezionati sono stati cancellati??)");
        }
        
        $errorsCount = 0;
        while($ondemandVideo = mysql_fetch_array($ondemandVideoInfos))
        {
            $result = $this->ConvertOnDemandVideo($ondemandVideo['ondemand_publish_code'], $ondemandVideo['ondemand_filename'], $onDemandMp4RecordFilepath, $onDemandFlvRecordFilepath);
            
            if ($result !== 0)
            {
                error_log("Conversione del video [". $ondemandVideo['ondemand_filename'] ."] in mp4 fallita! :( ");
                ++$errorsCount;
            }
            else
            {
                error_log("Conversione del video [". $ondemandVideo['ondemand_filename'] ."] in mp4 riuscita! :) ");
                
            }
        }
        
        return $errorsCount;
    }
    
}
