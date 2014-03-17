<?PHP

class DBActions
{

	var $error_message;

	/* Database variables*/
	var $connection;
	var $database;
	var $tablename;

    function InitDB($host,$uname,$pwd,$database,$tablename)
    {
        $this->db_host  = $host;
        $this->username = $uname;
        $this->pwd  = $pwd;
        $this->database  = $database;
        $this->tablename = $tablename;
    }

    function DBLogin()
    {
        $this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

        if(!$this->connection)
        {
            $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
            return false;
        }
        if(!mysql_select_db($this->database, $this->connection))
        {
            $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
            return false;
        }
        if(!mysql_query("SET NAMES 'UTF8'",$this->connection))
        {
            $this->HandleDBError('Error setting utf8 encoding');
            return false;
        }
        return true;
    }

    function IsFieldUnique($uservars,$fieldname)
    {
        $field_val = $this->SanitizeForSQL($uservars[$fieldname]);
        $qry = "select username from users where $fieldname='".$field_val."'";
        $result = mysql_query($qry,$this->connection);
        if($result && mysql_num_rows($result) > 0)
        {
            return false;
        }
        return true;
    }
    
    function IsGroupFieldUnique($groupvars,$fieldname)
    {
        $field_val = $this->SanitizeForSQL($groupvars[$fieldname]);
        $qry = "select group_name from groups where $fieldname='".$field_val."'";
        $result = mysql_query($qry,$this->connection);
        if($result && mysql_num_rows($result) > 0)
        {
            return false;
        }
        return true;
    }

    function EnsureUsersTable()
    {
        $result = mysql_query("SHOW COLUMNS FROM users");
        if(!$result || mysql_num_rows($result) <= 0)
        {
            return $this->CreateUsersTable();
        }
        return true;
    }

    function CheckLoginInDB($username,$password)
    {
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }
        $username = $this->SanitizeForSQL($username);
        $pwdmd5 = md5($password);
        $qry = "Select * from users where username='$username' and password='$pwdmd5' and confirmcode='y'";

        $result = mysql_query($qry,$this->connection);

        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("Error logging in. The username or password does not match");
            return false;
        }

        $row = mysql_fetch_assoc($result);

        $_SESSION['user_id']  = $row['id_user'];
        $_SESSION['name_of_user']  = $row['name'];
        $_SESSION['email_of_user'] = $row['email'];
        $_SESSION['user_group_id'] = $row['user_group_id'];
        $_SESSION['user_role_id'] = $row['user_role_id'];

        $select_query = "select * from groups where group_id='".$row['user_group_id']. "'";

        $result = mysql_query($select_query ,$this->connection);
        if(!$result)
        {
                $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
                return false;
        }

        $row_group = mysql_fetch_assoc($result);
        if (!$row_group)
        {
                $this->HandleError("Error getting group data.");
                return false;
        }
        $_SESSION['user_group_name'] = $row_group['group_name'];

        return true;
    }

    function UpdateDBRecForConfirmation(&$user_rec)
    {
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }
        $confirmcode = $this->SanitizeForSQL($_GET['code']);

        $result = mysql_query("Select name, email from users where confirmcode='$confirmcode'",$this->connection);
        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("Wrong confirm code.");
            return false;
        }
        $row = mysql_fetch_assoc($result);
        $user_rec['name'] = $row['name'];
        $user_rec['email']= $row['email'];

        $qry = "Update users Set confirmcode='y' Where  confirmcode='$confirmcode'";

        if(!mysql_query( $qry ,$this->connection))
        {
            $this->HandleDBError("Error inserting data to the table\nquery:$qry");
            return false;
        }
        return true;
    }

    function ChangePasswordInDB($user_rec, $newpwd)
    {
	if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }

        $newpwd = $this->SanitizeForSQL($newpwd);

        $qry = "Update users Set password='".md5($newpwd)."' Where  id_user=".$user_rec['id_user']."";

        if(!mysql_query( $qry ,$this->connection))
        {
            $this->HandleDBError("Error updating the password \nquery:$qry");
            return false;
        }
        return true;
    }

    function GetGroupInfoByName($group_name)
    {
	if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }
	$select_query = 'select group_id from groups where group_name =\'' . $group_name . '\'';

        $result = mysql_query($select_query ,$this->connection);
        if(!$result)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
            return false;
        }
        $row = mysql_fetch_array($result);

	return $row['group_id'];
    }

    function GetUserFromEmail($email,&$user_rec)
    {
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }
        $email = $this->SanitizeForSQL($email);

        $result = mysql_query("Select * from users where email='$email'",$this->connection);

        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("There is no user with email: $email");
            return false;
        }
        $user_rec = mysql_fetch_assoc($result);


        return true;
    }

   function InsertIntoDB(&$uservars,$rand_key)
    {

        $confirmcode = $this->MakeConfirmationMd5($uservars['email'],$rand_key);

        $uservars['confirmcode'] = $confirmcode;

                $select_query_group = 'select * from groups where group_name =\'' . $uservars['group_name'] . '\'';

                $result = mysql_query($select_query_group ,$this->connection);
                if(!$result)
                {
                    $this->HandleDBError("Error selecting data from the table\nquery:$select_query_group");
                    return false;
                }
                $row_group = mysql_fetch_assoc($result);

                $select_query_role = 'select * from user_roles where role_name =\'' . $uservars['user_role_name'] . '\'';

                $result = mysql_query($select_query_role ,$this->connection);
                if(!$result)
                {
                    $this->HandleDBError("Error selecting data from the table\nquery:$select_query_role");
                    return false;
                }
                $row_role = mysql_fetch_assoc($result);

        $insert_query = 'insert into users (
                name,
                email,
                username,
                password,
                user_group_id,
                confirmcode,
                user_role_id
                )
                values
                (
                "' . $this->SanitizeForSQL($uservars['name']) . '",
                "' . $this->SanitizeForSQL($uservars['email']) . '",
                "' . $this->SanitizeForSQL($uservars['username']) . '",
                "' . md5($uservars['password']) . '",
                "' . $this->SanitizeForSQL($row_group['group_id']) . '",
                "' . $confirmcode . '",
                "' . $this->SanitizeForSQL($row_role['role_id']) . '"
                )';
        if(!mysql_query( $insert_query ,$this->connection))
        {
            $this->HandleDBError("Error inserting data to the table\nquery:$insert_query");
            return false;
        }
        return true;
    }

	function InsertGroupIntoDB(&$groupvars)
	{
		$select_query_role = 'select * from group_roles where role_name =\'' . $groupvars['group_role_name'] . '\'';

                $result = mysql_query($select_query_role ,$this->connection);
                if(!$result)
                {
                    $this->HandleDBError("Error selecting data from the table\nquery:$select_query_role");
                    return false;
                }
                $row_role = mysql_fetch_assoc($result);

		$publish_code = $this->ParseGroupName($groupvars['group_name']);

		$insert_query = 'insert into groups (
                group_name,
                group_type,
                group_role,
		publish_code
                )
                values
                (
                "' . $this->SanitizeForSQL($groupvars['group_name']) . '",
                "' . $this->SanitizeForSQL($groupvars['group_type']) . '",
                "' . $this->SanitizeForSQL($row_role['role_id']) . '",
                "' . $this->SanitizeForSQL($publish_code) . '"
                )';

	        if(!mysql_query( $insert_query ,$this->connection))
	        {
        	    $this->HandleDBError("Error inserting data to the table\nquery:$insert_query");
	            return false;
	        }
	        return true;
	}

	function ParseGroupName($publish_code)
	{
		return strtolower(str_replace(' ', '_', $publish_code));
	}

    function OnPublish($nginx_id,$app_name,$stream_name,$client_addr,$publish_code,$mysqldate,$mysqltime)
        {

                $this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

                if(!$this->connection)
                {
                    $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
                    return false;
                }
                if(!mysql_select_db($this->database, $this->connection))
                {
                    $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
                    return false;
                }

                $insert_query = 'insert into live (
                nginx_id,
		live_date,
		live_time,
                app_name,
                stream_name,
                client_addr,
                publish_code)
                values
                (
                "' . $this->SanitizeForSQL($nginx_id) . '",
                "' . $this->SanitizeForSQL($mysqldate) . '",
                "' . $this->SanitizeForSQL($mysqltime) . '",
                "' . $this->SanitizeForSQL($app_name) . '",
                "' . $this->SanitizeForSQL($stream_name) . '",
                "' . $this->SanitizeForSQL($client_addr) . '",
                "' . $this->SanitizeForSQL($publish_code) . '"
                )';

                if(!mysql_query( $insert_query ,$this->connection))
                {
                    $this->HandleDBError("Error inserting data to the table\nquery:$insert_query");
                    return false;
                }
                return true;
        }

	function OnPublishDone($nginx_id,$app_name,$stream_name,$client_addr)
        {
                $this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

                if(!$this->connection)
                {
                    $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
                    return false;
                }
                if(!mysql_select_db($this->database, $this->connection))
                {
                    $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
                    return false;
                }

                $delete_query = 'delete from live where nginx_id = "' . $this->SanitizeForSQL($nginx_id) . '"';

                if(!mysql_query( $delete_query ,$this->connection))
                {
                    $this->HandleDBError("Error deleting data from the table\nquery:$delete_query");
                    return false;
                }
                return true;
        }

	function OnRecordDone($app_name,$stream_name,$ondemand_path,$ondemand_filename,$movie)
        {

                $this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

                if(!$this->connection)
                {
                    $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
                    return false;
                }
                if(!mysql_select_db($this->database, $this->connection))
                {
                    $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
                    return false;
                }

		$video_duration=$movie->getDuration();
		$video_bitrate=$movie->getVideoBitRate();
		$video_codec=$movie->getVideoCodec();

                $insert_query = 'insert into ondemand (
                ondemand_publish_code,
                ondemand_path,
                ondemand_app_name,
                ondemand_filename,
		ondemand_movie_duration,
		ondemand_movie_bitrate,
		ondemand_movie_codec)
                values
                (
                "' . $this->SanitizeForSQL($stream_name) . '",
                "' . $this->SanitizeForSQL($ondemand_path) . '",
                "' . $this->SanitizeForSQL($app_name) . '",
                "' . $this->SanitizeForSQL($ondemand_filename) . '",
                "' . $this->SanitizeForSQL($video_duration) . '",
                "' . $this->SanitizeForSQL($video_bitrate) . '",
                "' . $this->SanitizeForSQL($video_codec) . '"
                )';

                if(!mysql_query( $insert_query ,$this->connection))
                {
                    $this->HandleDBError("Error inserting data to the table\nquery:$insert_query");
                    return false;
                }
                return true;
        }

	function GetGroups()
        {
                $this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

                if(!$this->connection)
                {
                    $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
                    return false;
                }
                if(!mysql_select_db($this->database, $this->connection))
                {
                    $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
                    return false;
                }

                $select_query = 'select group_id,group_name,group_type,role_id as group_role_id,publish_code,role_name as group_role_name from groups INNER JOIN group_roles ON groups.group_role = group_roles.role_id order by group_name';      

                $result = mysql_query($select_query ,$this->connection);
                if(!$result)
                {
                    $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
                    return false;
                }
                return $result;
        }

	function GetUserRoles()
        {
                $this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

                if(!$this->connection)
                {
                    $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
                    return false;
                }
                if(!mysql_select_db($this->database, $this->connection))
                {
                    $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
                    return false;
                }

                $select_query = 'select role_id,role_name as user_role_name from user_roles order by role_name';   

                $result = mysql_query($select_query ,$this->connection);
                if(!$result)
                {
                    $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
                    return false;
                }
                return $result;
        }

	function DeleteUser($user_id)
        {
                $this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

                if(!$this->connection)
                {
                    $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
                    return false;
                }
                if(!mysql_select_db($this->database, $this->connection))
                {
                    $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
                    return false;
                }

                $delete_query = 'delete from users where id_user = \''.$user_id.'\'';

                $result = mysql_query($delete_query ,$this->connection);
                if(!$result)
                {
                    $this->HandleDBError("Error deleting data from the table\nquery:$delete_query");
                    return false;
                }
                return $result;
        }

	function DeleteGroup($group_id)
        {
                $this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

                if(!$this->connection)
                {
                    $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
                    return false;
                }
                if(!mysql_select_db($this->database, $this->connection))
                {
                    $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
                    return false;
                }

                $delete_query = 'delete from groups where group_id = \''.$group_id.'\'';

                $result = mysql_query($delete_query ,$this->connection);
                if(!$result)
                {
                    $this->HandleDBError("Error deleting data from the table\nquery:$delete_query");
                    return false;
                }
		
		$delete_query = 'delete from group_links where viewer_id = \''.$group_id.'\' or publisher_id = \''.$group_id.'\'';
		$result = mysql_query($delete_query ,$this->connection);
		if(!$result)
                {
                    $this->HandleDBError("Error deleting data from the table\nquery:$delete_query");
                    return false;
                }
		
                return $result;
        }
	
	function DeleteEventOnDemand($ondemand_id)
	{
		$this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

                if(!$this->connection)
                {
                    $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
                    return false;
                }
                if(!mysql_select_db($this->database, $this->connection))
                {
                    $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
                    return false;
                }

                $delete_query = 'delete from ondemand where ondemand_id = \''.$ondemand_id.'\'';

                $result = mysql_query($delete_query ,$this->connection);
                if(!$result)
                {
                    $this->HandleDBError("Error deleting data from the table\nquery:$delete_query");
                    return false;
                }
                return $result;
	}

	function AddViewersLink($viewer_list, $publisher_id)
	{
		$this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

                if(!$this->connection)
                {
                    $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
                    return false;
                }
                if(!mysql_select_db($this->database, $this->connection))
                {
                    $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
                    return false;
                }
		
		$viewers = explode("|", $viewer_list);
		
		foreach ($viewers as $viewtoadd)
		{
			if ($viewtoadd != "")
			{
				$insert_query = 'insert into group_links (
				publisher_id,
				viewer_id)
				values
				(
				"' . $this->SanitizeForSQL($publisher_id) . '",
				"' . $this->SanitizeForSQL($viewtoadd) . '"
				)';
			
				if(!mysql_query( $insert_query ,$this->connection))
				{
				    $this->HandleDBError("Error inserting data to the table\nquery:$insert_query");
				    return false;
				}		
			}
		}
		
                return true;	
	}
	
	function DelViewersLink($viewer_list, $publisher_id)
	{
		$this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

                if(!$this->connection)
                {
                    $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
                    return false;
                }
                if(!mysql_select_db($this->database, $this->connection))
                {
                    $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
                    return false;
                }
		
		$viewers = explode("|", $viewer_list);

		$str = implode(",", $viewers);
		$viewerstodel = substr_replace($str, "",-1);
		
		$delete_query = 'delete from group_links where publisher_id in ('.
		$this->SanitizeForSQL($publisher_id).
		') and viewer_id in ('.
		$viewerstodel.')';
		
		if(!mysql_query( $delete_query ,$this->connection))
		{
		    $this->HandleDBError("Error deleting data from the table\nquery:$delete_query");
		    return false;
		}
		
                return true;	
	}
	
	function GetViewersByPublisher($publisher_id)
        {
                $this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

                if(!$this->connection)
                {
                    $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
                    return false;
                }
                if(!mysql_select_db($this->database, $this->connection))
                {
                    $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
                    return false;
                }

                $select_query = 'select group_links.viewer_id, groups.group_name as viewer_name from group_links INNER JOIN groups ON group_links.viewer_id = groups.group_id where group_links.publisher_id = \''.$publisher_id.'\' order by viewer_name';

                $result = mysql_query($select_query ,$this->connection);
                if(!$result)
                {
                    $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
                    return false;
                }
                return $result;
        }

	function GetViewersAvailable($publisher_id)
        {
                $this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

                if(!$this->connection)
                {
                    $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
                    return false;
                }
                if(!mysql_select_db($this->database, $this->connection))
                {
                    $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
                    return false;
                }

                $select_query = 'select groups.group_name,groups.group_id from groups where group_role = \'2\' and group_id not in (select viewer_id from group_links INNER JOIN groups ON group_links.viewer_id = groups.group_id where publisher_id = \''.$publisher_id.'\') order by group_name;';
                /*$select_query = 'select group_links.viewer_id, groups.group_name as viewer_name from group_links INNER JOIN groups ON group_links.viewer_id = groups.group_id where group_links.publisher_id != \''.$publisher_id.'\' order by viewer_name';*/

                $result = mysql_query($select_query ,$this->connection);
                if(!$result)
                {
                    $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
                    return false;
                }
                return $result;
        }

	function GetPublishersByViewer($viewer_id)
        {
                $this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

                if(!$this->connection)
                {
                    $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
                    return false;
                }
                if(!mysql_select_db($this->database, $this->connection))
                {
                    $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
                    return false;
                }
		
		$select_query = 'select group_links.publisher_id, groups.group_name as publisher_name, groups.publish_code as publisher_code '.
				'from group_links '.
				'INNER JOIN groups ON group_links.publisher_id = groups.group_id '.
				'where group_links.viewer_id = \''.$viewer_id.'\';';

                $result = mysql_query($select_query ,$this->connection);
                if(!$result)
                {
                    $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
                    return false;
                }
                return $result;
        }

	function GetGroupRoles()
        {
                $this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

                if(!$this->connection)
                {
                    $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
                    return false;
                }
                if(!mysql_select_db($this->database, $this->connection))
                {
                    $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
                    return false;
                }

                $select_query = 'select role_id,role_name as group_role_name from group_roles order by role_name'; 

                $result = mysql_query($select_query ,$this->connection);
                if(!$result)
                {
                    $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
                    return false;
                }
                return $result;
        }

	function GetLiveEventsByPublisher($publish_code)
	{	
		$this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

                if(!$this->connection)
                {
                    $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
                    return false;
                }
                if(!mysql_select_db($this->database, $this->connection))
                {
                    $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
                    return false;
                }

                $select_query = 'select * from live where stream_name = \''.$publish_code.'\' order by app_name,live_date';

                $result = mysql_query($select_query ,$this->connection);
                if(!$result)
                {
                    $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
                    return false;
                }
                return $result;
	}

	function GetOndemandEventsByPublisher($publish_code)
        {
                $this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

                if(!$this->connection)
                {
                    $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
                    return false;
                }
                if(!mysql_select_db($this->database, $this->connection))
                {
                    $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
                    return false;
                }

                $select_query = 'select * from ondemand where ondemand_publish_code = \''.$publish_code.'\' order by ondemand_filename';

                $result = mysql_query($select_query ,$this->connection);
                if(!$result)
                {
                    $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
                    return false;
                }
                return $result;
        }

	function GetUsers()
        {
                $this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

                if(!$this->connection)
                {
                    $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
                    return false;
                }
                if(!mysql_select_db($this->database, $this->connection))
                {
                    $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
                    return false;
                }

                $select_query = 'select id_user as user_id,name as user_name,email as user_mail,phone_number,username,password,confirmcode,user_group_id,group_name as user_group_name,user_role_id,role_name as user_role_name from users '.
                'INNER JOIN user_roles ON users.user_role_id = user_roles.role_id '.
                'INNER JOIN groups ON users.user_group_id = groups.group_id '.
                'order by name';

                $result = mysql_query($select_query ,$this->connection);
                if(!$result)
                {
                    $this->HandleDBError("Error deleting data from the table\nquery:$select_query");
                    return false;
                }
                return $result;
        }

    function CreateUsersTable()
    {
        $qry = "Create Table users (".
                "id_user INT NOT NULL AUTO_INCREMENT ,".
                "name VARCHAR( 128 ) NOT NULL ,".
                "email VARCHAR( 64 ) NOT NULL ,".
                "phone_number VARCHAR( 16 ) NOT NULL ,".
                "username VARCHAR( 16 ) NOT NULL ,".
                "password VARCHAR( 32 ) NOT NULL ,".
                "user_group_id INT ( 11 ) NOT NULL,".
                "confirmcode VARCHAR(32) ,".
                "user_role_id INT ( 11 ) NOT NULL,".
                "PRIMARY KEY ( id_user )".
                ")";

        if(!mysql_query($qry,$this->connection))
        {
            $this->HandleDBError("Error creating the table \nquery was\n $qry");
            return false;
        }
        return true;
    }

    function SanitizeForSQL($str)
    {
        if( function_exists( "mysql_real_escape_string" ) )
        {
              $ret_str = mysql_real_escape_string( $str );
        }
        else
        {
              $ret_str = addslashes( $str );
        }
        return $ret_str;
    }

    function MakeConfirmationMd5($email,$rand_key)
    {
        $randno1 = rand();
        $randno2 = rand();
        return md5($email.$rand_key.$randno1.''.$randno2);
    }

    /*  ERROR HANDLE FUNCTIONS*/
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

    function HandleDBError($err)
    {
        $this->HandleError($err."\r\n mysqlerror:".mysql_error());
    }


}

?>
