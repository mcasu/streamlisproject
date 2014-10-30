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
require_once("formvalidator.php");
require_once("utils.php");
require_once("dbactions.php");
require_once("fsactions.php");

class MainActions
{
    var $admin_email;
    var $from_address;
    
    var $username;
    var $pwd;
    var $rand_key;
    var $utilsInstance;
    var $dbactionsInstance;
    var $fsactionsInstance;
 
    var $error_message;
    
    //-----Initialization -------
    function MainActions($host, $uname, $pwd, $database)
    {
        $this->sitename = 'YourWebsiteName.com'
	;
        $this->rand_key = '0iQx5oBk66oVZep';

	$this->utilsInstance = new Utils();
        $this->dbactionsInstance = new DBActions($host, $uname, $pwd, $database);
        $this->fsactionsInstance = new FSActions();
    }
    
/*** MEMBERS ***/
    function UserId()
    {
	return isset($_SESSION[$this->GetLoginSessionVar()]['user_id'])?$_SESSION[$this->GetLoginSessionVar()]['user_id']:'';
    }
    function UserName()
    {
	return isset($_SESSION[$this->GetLoginSessionVar()]['username'])?$_SESSION[$this->GetLoginSessionVar()]['username']:'';
    }
    function UserFullName()
    {
        return isset($_SESSION[$this->GetLoginSessionVar()]['user_fullname'])?$_SESSION[$this->GetLoginSessionVar()]['user_fullname']:'';
    }
    
    function UserEmail()
    {
        return isset($_SESSION[$this->GetLoginSessionVar()]['user_email'])?$_SESSION[$this->GetLoginSessionVar()]['user_email']:'';
    }
    
    function UserGroupId()
    {
        return isset($_SESSION[$this->GetLoginSessionVar()]['user_group_id'])?$_SESSION[$this->GetLoginSessionVar()]['user_group_id']:'';
    }
    
    function UserGroupName()
    {
        return isset($_SESSION[$this->GetLoginSessionVar()]['user_group_name'])?$_SESSION[$this->GetLoginSessionVar()]['user_group_name']:'';
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
    
    function SetRandomKey($key)
    {
        $this->rand_key = $key;
    }
    
    //-------Main Operations ----------------------
    function CreateUser()
    {
        if(!isset($_POST['submitted']))
        {
           return false;
        }
        
        $uservars = array();
        
        /*
        if(!$this->ValidateRegistrationSubmission())
        {
            return false;
        }
        */
        
        $this->CollectRegistrationSubmission($uservars);
        
	$user_id = $this->SaveToDatabase($uservars);
	
        if(!$user_id)
        {
            return false;
        }
	
        $uservars['user_id'] = $this->utilsInstance->Sanitize($user_id);
	
        if(!$this->SendUserConfirmationEmail($uservars))
        {
	    $this->dbactionsInstance->DeleteUser($uservars['user_id']);
            return false;
        }

        $this->SendAdminIntimationEmail($uservars);
        
        return true;
    }

    function CreateGroup()
    {
        if(!isset($_POST['submitted']))
        {
           return false;
        }

        $groupvars = array();

        $this->CollectGroupRegistrationSubmission($groupvars);

        if(!$this->SaveGroupToDatabase($groupvars))

        {
            return false;
        }

        return true;
    }

    function ConfirmUser()
    {
        if(empty($_GET['code'])||strlen($_GET['code'])<=10)
        {
            $this->HandleError("Please provide the confirm code");
            return false;
        }
        $user_rec = array();
        if(!$this->dbactionsInstance->UpdateDBRecForConfirmation($user_rec))
        {
            return false;
        }
        
        $this->SendUserWelcomeEmail($user_rec);
        
        $this->SendAdminIntimationOnRegComplete($user_rec);
        
        return true;
    }    
    
    function Login()
    {
        if(empty($_POST['username']))
        {
            $this->HandleError("UserName is empty!");
            return false;
        }
        
        if(empty($_POST['password']))
        {
            $this->HandleError("Password is empty!");
            return false;
        }
        
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
	
	$userdata = $this->dbactionsInstance->CheckLoginInDB($username,$password);
	
        if(!$userdata)
        {
            return false;
        }

	session_start();
	$sessionvar = $this->GetLoginSessionVar();
	$_SESSION[$sessionvar] = $userdata;
	
	// Set the user logged flag into the database.
        $this->dbactionsInstance->UpdateUserLoginStatus($username, true, true);
      
        return true;
    }
    
    function CheckLogin()
    {
         session_start();
         $sessionvar = $this->GetLoginSessionVar();
         
         if(empty($_SESSION[$sessionvar]))
         {
            return false;
         }
	 
	 $userdata = $_SESSION[$sessionvar];
	 // The users' session expire after 10800 sec = 3 hours
	 if (time() - $userdata['last_update'] > 10800)
	 {
	    $this->dbactionsInstance->UpdateUserLoginStatus($userdata['username'], false);
	    $_SESSION[$sessionvar]=NULL;
	    unset($_SESSION[$sessionvar]);
	    return false;
	 }
	 
	 $this->dbactionsInstance->UpdateUserLoginStatus($userdata['username'], true);
         return true;
    }
   
    function GetSessionUserRole()
    {
	    if(empty($_SESSION[$this->GetLoginSessionVar()]['user_role_id']))
	    {
		    $this->HandleError("User role for this session not found!");
		    return false;
	    }

	    return $_SESSION[$this->GetLoginSessionVar()]['user_role_id']; 
	    
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

    function LogOut()
    {
	session_start();
	$sessionvar = $this->GetLoginSessionVar();
	
	$userdata = $_SESSION[$sessionvar];
	$username = $userdata['username'];
	
	$this->dbactionsInstance->UpdateUserLoginStatus($username, false);
        
        $_SESSION[$sessionvar]=NULL;
        unset($_SESSION[$sessionvar]);
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
        
        if($this->GetResetPasswordCode($email) != $code)
        {
            $this->HandleError("Bad reset code!");
            return false;
        }
        
        $user_rec = array();
        if(!$this->dbactionsInstance->GetUserById($user_id,$user_rec))
        {
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

        $host = $_SERVER['SERVER_NAME'];

        $from ="lnxstreamserver@jwstream.org";
        return $from;
    } 
    
    function GetLoginSessionVar()
    {
        $retvar = md5($this->rand_key);
        $retvar = 'usr_'.substr($retvar,0,10);
        return $retvar;
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
    
    function SendUserWelcomeEmail(&$user_rec)
    {
        $mailer = new PHPMailer();
        
        $mailer->CharSet = 'utf-8';
        
        $mailer->AddAddress($user_rec['email'],$user_rec['name']);
        
        $mailer->Subject = "Benvenuto su ".$this->sitename;

        $mailer->From = $this->GetFromAddress();        
        
        $mailer->Body ="Ciao caro fratello ".$user_rec['name']."\r\n\r\n".
        "Benvenuto! La tua registrazione su ".$this->sitename." e' completata!.\r\n".
        "\r\n".
        "Saluti,\r\n".
        "Webmaster\r\n".
        $this->sitename;

        if(!$mailer->Send())
        {
            $this->HandleError("Failed sending user welcome email.");
            return false;
        }
        return true;
    }
    
    function SendAdminIntimationOnRegComplete(&$user_rec)
    {
        if(empty($this->admin_email))
        {
            return false;
        }
        $mailer = new PHPMailer();
        
        $mailer->CharSet = 'utf-8';
        
        $mailer->AddAddress($this->admin_email);
        
        $mailer->Subject = "Registration Completed: ".$user_rec['name'];

        $mailer->From = $this->GetFromAddress();         
        
        $mailer->Body ="A new user registered at ".$this->sitename."\r\n".
        "Name: ".$user_rec['name']."\r\n".
        "Email address: ".$user_rec['email']."\r\n";
        
        if(!$mailer->Send())
        {
            return false;
        }
        return true;
    }
    
    function GetResetPasswordCode($email)
    {
       return substr(md5($email.$this->sitename.$this->rand_key),0,10);
    }
    
    function SendResetPasswordLink($user_rec)
    {
        $email = $user_rec['email'];
        $user_id = $user_rec['id_user'];
	
        $mailer = new PHPMailer();
        
        $mailer->CharSet = 'utf-8';
        
        $mailer->AddAddress($email,$user_rec['name']);
        
        $mailer->Subject = "Your reset password request at ".$this->sitename;

        $mailer->From = $this->GetFromAddress();
        
        $link = $this->GetAbsoluteURLFolder().
                '/resetpwd.php?user_id='.
                urlencode($user_id).'&email='.
		urlencode($email).'&code='.
                urlencode($this->GetResetPasswordCode($email));

        $mailer->Body ="Hello ".$user_rec['name']."\r\n\r\n".
        "There was a request to reset your password at ".$this->sitename."\r\n".
        "Please click the link below to complete the request: \r\n".$link."\r\n".
        "Regards,\r\n".
        "Webmaster\r\n".
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
    
    function ValidateRegistrationSubmission()
    {
        //This is a hidden input field. Humans won't fill this field.
        if(!empty($_POST[$this->utilsInstance->GetSpamTrapInputName($this->rand_key)]) )
        {
            //The proper error is not given intentionally
            $this->HandleError("Automated submission prevention: case 2 failed");
            return false;
        }
        
        $validator = new FormValidator();
        $validator->addValidation("name","req","Please fill in Name");
        $validator->addValidation("email","email","The input for Email should be a valid email value");
        $validator->addValidation("email","req","Please fill in Email");
        $validator->addValidation("username","req","Please fill in UserName");
        $validator->addValidation("password","req","Please fill in Password");

        
        if(!$validator->ValidateForm())
        {
            $error='';
            $error_hash = $validator->GetErrors();
            foreach($error_hash as $inpname => $inp_err)
            {
                $error .= $inpname.':'.$inp_err."\n";
            }
            $this->HandleError($error);
            return false;
        }        
        return true;
    }
    
    function CollectRegistrationSubmission(&$uservars)
    {
        $uservars['name'] = $this->utilsInstance->Sanitize($_POST['name']);
        $uservars['email'] = $this->utilsInstance->Sanitize($_POST['email']);
        $uservars['username'] = $this->utilsInstance->Sanitize($_POST['username']);
        $uservars['password'] = $this->utilsInstance->Sanitize($_POST['password']);
        $uservars['group_name'] = $this->utilsInstance->Sanitize($_POST['group_name']);
        $uservars['user_role_name'] = $this->utilsInstance->Sanitize($_POST['user_role_name']);
    }
    
    function CollectGroupRegistrationSubmission(&$groupvars)
    {
        $groupvars['group_name'] = $this->utilsInstance->Sanitize($_POST['group_name']);
        $groupvars['group_type'] = $this->utilsInstance->Sanitize($_POST['group_type']);
        $groupvars['group_role_name'] = $this->utilsInstance->Sanitize($_POST['group_role_name']);
    }
    
    
    function SendUserConfirmationEmail(&$uservars)
    {
        $mailer = new PHPMailer();
        
        $mailer->CharSet = 'utf-8';
       	
	$mailer->IsHTML(true);
 
        $mailer->AddAddress($uservars['email'],$uservars['name']);
        
        $mailer->Subject = "Registrazione su ".$this->sitename;

        $mailer->From = $this->GetFromAddress();        
        
        $confirmcode = $uservars['confirmcode'];
        
        $confirm_url = $this->GetAbsoluteURLFolder().'/confirmreg.php?code='.$confirmcode;
       
	$group_name = $this->dbactionsInstance->GetGroupInfoByName($uservars['group_name']);
	
        $mailer->Body ="<html><body>Caro fratello <b>".$uservars['name']."</b>".
	" della congregazione ". $uservars['group_name'].",<br/><br/>". 
        'Grazie per la tua regsitrazione su '.$this->sitename."<br/><br/>".
        'Per favore clicca sul seguente link per confermare la tua registrazione: '.
        '<a href="'.$confirm_url.'">Conferma la tua registrazione.</a>'."<br/><br/>".
	"Queste sono le tue credenziali di accesso:<br/>".
	"Username:  ".$uservars['username']."<br/>".
	"Password:  ".$uservars['password']."<br/>".
        "<br/>".
        "Saluti,<br/>".
        "Webmaster<br/></body></html>".
        $this->sitename;

        if(!$mailer->Send())
        {
            $this->HandleError("Failed sending registration confirmation email.");
            return false;
        }
        return true;
    }
    function GetAbsoluteURLFolder()
    {
        $scriptFolder = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? 'https://' : 'http://';
        $scriptFolder .= $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
        return $scriptFolder;
    }
    
    function SendAdminIntimationEmail(&$uservars)
    {
        if(empty($this->admin_email))
        {
            return false;
        }
        $mailer = new PHPMailer();
        
        $mailer->CharSet = 'utf-8';
        
        $mailer->AddAddress($this->admin_email);
        
        $mailer->Subject = "New registration: ".$uservars['name'];

        $mailer->From = $this->GetFromAddress();         
        
        $mailer->Body ="A new user registered at ".$this->sitename."\r\n".
        "Name: ".$uservars['name']."\r\n".
        "Email address: ".$uservars['email']."\r\n".
        "UserName: ".$uservars['username'];
        
        if(!$mailer->Send())
        {
            return false;
        }
        return true;
    }
    
    function SaveToDatabase(&$uservars)
    {
        if(!$this->dbactionsInstance->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }
        if(!$this->dbactionsInstance->IsFieldUnique($uservars,'username'))
        {
            $this->HandleError("This UserName is already used. Please try another username");
            return false;
        }
	
	$user_id = $this->dbactionsInstance->InsertIntoDB($uservars,$this->rand_key);
        if(!$user_id)
        {
            $this->HandleError("Inserting to Database failed!");
            return false;
        }
        return $user_id;
    }
    
    function SaveGroupToDatabase(&$groupvars)
    {
        if(!$this->dbactionsInstance->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }
        if(!$this->dbactionsInstance->IsGroupFieldUnique($groupvars,'group_name'))
        {
            $this->HandleError("This group name is already used. Please try another group name");
            return false;
        }        
        if(!$this->dbactionsInstance->InsertGroupIntoDB($groupvars))
        {
            $this->HandleError("Inserting to Database failed!");
            return false;
        }
        return true;
    }
	
}
?>
