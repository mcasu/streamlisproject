<?PHP

class DBActions
{
    var $error_message;

    /* Database variables*/
    var $connection;
    var $database;

    function DBActions($host, $uname, $pwd, $database)
    {
            $this->InitDB($host, $uname, $pwd, $database);
    }

    function InitDB($host,$uname,$pwd,$database)
    {
        $this->db_host  = $host;
        $this->username = $uname;
        $this->pwd  = $pwd;
        $this->database  = $database;
    }

    function DBLogin()
    {
        try 
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
        catch (Exception $e) 
        {
            $this->HandleDBError('ERROR - Database login failed! ' . $e->getMessage());
            return false;
        }
        
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
            $this->HandleError("ERRORE LOGIN - Il nome utente o la password inseriti non sono validi.");
            return false;
        }

        $row = mysql_fetch_assoc($result);
	
	$session_alive_time = time() - strtotime($row['last_update']);
	
	if ($row['user_logged'] == '1')
	{
		// Se un utente ha "abbandonato" la sessione chiudendo il browser per più di 5m = 300sec
		// allora distruggo la sessione e faccio logout
		if ($session_alive_time <= 300)
		{
			$this->HandleError("ERRORE LOGIN - L'utente inserito ha già effettuato login. \nUsare un nome utente diverso.");
			return false;
		}
		
		$this->UpdateUserLoginStatus($row['username'], false);
		//session_destroy();
	}
	
	$userdata = array();
	
	$userdata['user_id']  = $row['id_user'];
	$userdata['username']  = $row['username'];
        $userdata['user_fullname']  = $row['name'];
        $userdata['user_email'] = $row['email'];
        $userdata['user_group_id'] = $row['user_group_id'];
        $userdata['user_role_id'] = $row['user_role_id'];
	
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
        
	$userdata['user_group_name'] = $row_group['group_name'];

	$userdata['last_update'] = time();
	
        return $userdata;
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

        $qry = "update users set password='".md5($newpwd)."' Where  id_user=".$user_rec['id_user']."";

        if(!mysql_query( $qry ,$this->connection))
        {
            $this->HandleDBError("Error updating the password \nquery:$qry");
            return false;
        }
        return true;
    }
    

    function UpdateUserLoginStatus($username, $status, $islogin = false)
    {
	if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }

	$query = 'update users set user_logged = "'.$status.'", last_update = now() where username = "'.$username.'"';
	if ($islogin)
	{
		$query = 'update users set user_logged = "'.$status.'", last_login = now(), last_update = now() where username = "'.$username.'"';	
	}

        if(!mysql_query( $query ,$this->connection))
        {
            $this->HandleDBError("Error updating the user login status \nquery:$query");
            return false;
        }
        return true;
    }
    
    function CleanLoginOlderThan($seconds)
    {
	if(!$this->DBLogin())
        {
            return false;
        }
	
        $query = 'UPDATE users SET user_logged = "0" WHERE user_logged = "1" and TIMESTAMPDIFF(SECOND,last_update,now()) > \''.$seconds.'\'';

        try 
        {
            $result = mysql_query($query ,$this->connection);
            $affectedRows = mysql_affected_rows($this->connection);
            
            if(!$result)
            {
                $this->HandleDBError("Error updating the user login status \nQuery: " .$query. "\n");
                return false;
            }
	} 
        catch (Exception $e) 
        {
            $this->HandleDBError("Error updating the user login status \nQuery: " .$query. "\n". $e->getMessage());
            return false;
        }
        
        return $affectedRows;
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
    
    function GetPublishCodeByGroupId($group_id)
    {
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }
	$select_query = 'select publish_code from groups where group_id =\'' . $group_id . '\'';

        $result = mysql_query($select_query ,$this->connection);
        if(!$result)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
            return false;
        }
        $row = mysql_fetch_array($result);

	return $row['publish_code'];
    }
    
    function GetGroupNameByPublishCode($publish_code)
    {
	if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }
	$select_query = 'select group_name from groups where publish_code =\'' . $publish_code . '\'';

        $result = mysql_query($select_query ,$this->connection);
        if(!$result)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
            return false;
        }
        $row = mysql_fetch_array($result);

	return $row['group_name'];
    }

    function GetUserById($user_id,&$user_rec)
    {
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }
        $id = $this->SanitizeForSQL($user_id);

        $result = mysql_query("Select * from users where id_user='$id'",$this->connection);

        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("There is no user with ID: $id");
            return false;
        }
        $user_rec = mysql_fetch_assoc($result);

        return true;
    }
    
    function GetUserByUsername($username,&$user_rec)
    {
	if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }
        $name = $this->SanitizeForSQL($username);

        $result = mysql_query("Select * from users where username='$name'",$this->connection);

        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("Non esistono utenti con username: $name");
            return false;
        }
        $user_rec = mysql_fetch_assoc($result);

        return true;
    }

   function InsertIntoDB(&$uservars)
    {

        //$confirmcode = $this->MakeConfirmationMd5($uservars['email'],$rand_key);

        //$uservars['confirmcode'] = $confirmcode;

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
                "y",
                "' . $this->SanitizeForSQL($row_role['role_id']) . '"
                )';
        if(!mysql_query( $insert_query ,$this->connection))
        {
            $this->HandleDBError("Error inserting data to the table\nquery:$insert_query");
            return false;
        }
        return mysql_insert_id();
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
            "' . $this->SanitizeForSQL(strtolower($publish_code)) . '"
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

    function PublishNameAlreadyExists($app_name,$stream_name)
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

            $select_query = 'select * from live where app_name=\''.$app_name.'\' and stream_name=\''.$stream_name.'\'';      

            $result = mysql_query($select_query ,$this->connection);
            if(!$result)
            {
                $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
                return false;
            }

            $num_rows = mysql_num_rows($result);

            return $num_rows;
    }

    function DeletePublishNameDuplicated($app_name,$stream_name)
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

            $delete_query = 'delete from live where app_name = "' . $this->SanitizeForSQL($app_name) . '" and stream_name = "' . $this->SanitizeForSQL($stream_name) . '"';

            if(!mysql_query( $delete_query ,$this->connection))
            {
                $this->HandleDBError("Error deleting data from the table\nquery:$delete_query");
                return false;
            }
            return true;
    }


    function SaveEventoDb($nginx_id,$mysqldate,$mysqltime,$event_call,$app_name,$stream_name,$client_addr,$flash_ver,$page_url,$username = null)
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

            $insert_query = 'insert into events (
            nginx_id,
            event_date,
            event_time,
            event_call,
            app_name,
            stream_name,
            client_addr,
            flash_ver,
            page_url,
            username)
            values
            (
            "' . $this->SanitizeForSQL($nginx_id) . '",
            "' . $this->SanitizeForSQL($mysqldate) . '",
            "' . $this->SanitizeForSQL($mysqltime) . '",
            "' . $this->SanitizeForSQL($event_call) . '",
            "' . $this->SanitizeForSQL($app_name) . '",
            "' . $this->SanitizeForSQL(strtolower($stream_name)) . '",
            "' . $this->SanitizeForSQL($client_addr) . '",
            "' . $this->SanitizeForSQL($flash_ver) . '",
            "' . $this->SanitizeForSQL($page_url) . '",
            "' . $this->SanitizeForSQL($username) . '"
            )';

            if(!mysql_query( $insert_query ,$this->connection))
            {
                $this->HandleDBError("Error inserting data to the table\nquery:$insert_query");
                return false;
            }
            return true;
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

    function OnRecordDone($app_name,$stream_name,$ondemand_path,$ondemand_filename,$movie, $mysqldate = null)
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
            ondemand_movie_codec,
            ondemand_date)
            values
            (
            "' . $this->SanitizeForSQL($stream_name) . '",
            "' . $this->SanitizeForSQL($ondemand_path) . '",
            "' . $this->SanitizeForSQL($app_name) . '",
            "' . $this->SanitizeForSQL($ondemand_filename) . '",
            "' . $this->SanitizeForSQL($video_duration) . '",
            "' . $this->SanitizeForSQL($video_bitrate) . '",
            "' . $this->SanitizeForSQL($video_codec) . '",
            "' . $this->SanitizeForSQL($mysqldate) . '"
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

    function GetUserLoggedByLoginTime($publisher_id = NULL)
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

            $select_query = 'SELECT users.id_user as user_id, '.
                            'users.name, '.
                            'users.email, '.
                            'users.username, '.
                            'users.confirmcode, '.
                            'groups.group_name, '.
                            'user_roles.role_name as role_name, '.
                            'users.last_login, '.
                            'users.last_update, '.
                            'users.user_logged '.
            'FROM users INNER JOIN user_roles ON users.user_role_id = user_roles.role_id '.
            'INNER JOIN groups ON users.user_group_id = groups.group_id ';

            $select_where = 'WHERE users.user_logged = 1 ';
            if (!empty($publisher_id))
            {
                $where_add = ' AND (users.user_group_id in('.
                        'select group_links.viewer_id from group_links INNER JOIN groups ON group_links.viewer_id = groups.group_id '.
                        'where group_links.publisher_id = \''.$publisher_id.'\' order by viewer_id) or users.user_group_id = \''.$publisher_id.'\') ';

                $select_where .= $where_add;
            }


            $select_orderby = 'ORDER BY users.last_login';


            $select_total = $select_query . $select_where . $select_orderby;

            $result = mysql_query($select_total ,$this->connection);
            if(!$result)
            {
                $this->HandleDBError("Error selecting data from the table\nquery:$select_total");
                return false;
            }
            return $result;
    }

    function GetUsersByPublisher($publisher_id)
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

            $select_query = 'SELECT users.id_user as user_id, '.
                            'users.name, '.
                            'users.email, '.
                            'users.username, '.
                            'users.confirmcode, '.
                            'groups.group_name, '.
                            'user_roles.role_name as role_name, '.
                            'users.last_login, '.
                            'users.last_update, '.
                            'users.user_logged '.
            'FROM users INNER JOIN user_roles ON users.user_role_id = user_roles.role_id '.
            'INNER JOIN groups ON users.user_group_id = groups.group_id ';

            $select_where = ' WHERE users.user_group_id in('.
                        'select group_links.viewer_id from group_links INNER JOIN groups ON group_links.viewer_id = groups.group_id '.
                        'where group_links.publisher_id = \''.$publisher_id.'\' order by viewer_id) or users.user_group_id = \''.$publisher_id.'\' ';

            $select_orderby = 'ORDER BY users.name';

            $select_total = $select_query . $select_where . $select_orderby;

            $result = mysql_query($select_total ,$this->connection);
            if(!$result)
            {
                $this->HandleDBError("Error selecting data from the table\nquery:$select_total");
                return false;
            }
            return $result;
    }

    function GetUserNumbersByRole($publisher_id = NULL)
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

            $select_query = 'SELECT user_role_id, '.
                            'user_roles.role_name as role_name, '.
                            'count(*) as user_number '.
                    'FROM users INNER JOIN user_roles ON users.user_role_id = user_roles.role_id ';   

            $select_where = '';
            if (!empty($publisher_id))
            {
                $where_add = 'WHERE users.user_group_id in('.
                        'select group_links.viewer_id from group_links INNER JOIN groups ON group_links.viewer_id = groups.group_id '.
                        'where group_links.publisher_id = \''.$publisher_id.'\' order by viewer_id) or users.user_group_id = \''.$publisher_id.'\'';

                $select_where .= $where_add;
            }

            $select_groupby = 'GROUP BY user_role_id';

            $select_total = $select_query . $select_where . $select_groupby;

            $result = mysql_query($select_total ,$this->connection);
            if(!$result)
            {
                $this->HandleDBError("Error selecting data from the table\nquery:$select_total");
                return false;
            }
            return $result;
    }

    function GetEventOndemandNumberByPublisher()
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

            $select_query = 'SELECT groups.group_name as publisher_name, groups.publish_code, count(*) as event_number FROM ondemand INNER JOIN groups ON ondemand.ondemand_publish_code = groups.publish_code '.
            'group by groups.publish_code order by groups.group_name';

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

            $delete_query = 'delete from users where user_group_id = \''.$group_id.'\'';
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
    
    function DeleteAllEventsLive()
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
        
        $delete_query = 'DELETE FROM live';

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

            $select_query = 'SELECT group_links.viewer_id, '.
                    'groups.group_name as viewer_name, '.
                    'groups.group_type, '.
                    'group_roles.role_name, '.
                    'groups.publish_code '.
                    'FROM group_links INNER JOIN groups ON group_links.viewer_id = groups.group_id '.
                    'INNER JOIN group_roles ON groups.group_role = group_roles.role_id ';

            $select_where = 'WHERE group_links.publisher_id = \''.$publisher_id.'\' ';

            $select_orderby = 'ORDER BY viewer_name';

            $select_total = $select_query . $select_where . $select_orderby;

            $result = mysql_query($select_total ,$this->connection);
            if(!$result)
            {
                $this->HandleDBError("Error selecting data from the table\nquery:$select_total");
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

    function GetTodayLastLivePlayersNumber($stream_name)
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

        $date_now = date('Y-m-d');

        $select_query = 'select e1.* from events e1 '.
        'where e1.stream_name = \''.$stream_name.'\' and '.
        'e1.event_date = \''.$date_now.'\' and '.
        'e1.event_call like \'%play%\' and '.
        'e1.app_name not like \'%vod%\' and '.
        'e1.client_addr not like \'%unix%\' and '.
        'e1.client_addr not like \'%127.0.0.1%\' '.
        /*
        'and e1.event_time = '.
        '(select max(e2.event_time) from events e2 '.
        'where e2.stream_name = e1.stream_name and e2.event_date = e1.event_date and e2.event_call = e1.event_call and e2.app_name = e1.app_name and e2.client_addr = e1.client_addr) '.
         */

        'order by client_addr, nginx_id';

        /*
        $select_query = 'select event_id, nginx_id, event_date, max(event_time) as event_time, event_call, app_name, stream_name, client_addr from events '.
                'where stream_name = \''.$stream_name.'\' and '.
                'event_date = \''.$date_now.'\' and '.
                'event_call like \'%play%\' and '.
                'client_addr not like \'%unix%\' and '.
                'client_addr not like \'%127.0.0.1%\' '.
                'group by client_addr, event_call '.
                'order by event_date, client_addr, nginx_id, event_call';
        */

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

    function GetOndemandEventsById($eventId)
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

            $select_query = 'select * from ondemand where ondemand_id = \''.$eventId.'\';';

            $result = mysql_query($select_query ,$this->connection);
            if(!$result)
            {
                $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
                return false;
            }
            return $result;
    }

    function GetUsers($onlyLogged = false)
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

            $query_select = 'select id_user as user_id,name as user_name,email as user_mail,phone_number,username,password,confirmcode,user_group_id,group_name as user_group_name,user_role_id,role_name as user_role_name,user_logged,last_login,last_update from users ';

            $query_where = '';
            if ($onlyLogged)
            {
                    $query_where = 'where users.user_logged = \'1\' ';	
            }

            $query_join = 'INNER JOIN user_roles ON users.user_role_id = user_roles.role_id '.
            'INNER JOIN groups ON users.user_group_id = groups.group_id ';

            $query_orderby = 'order by name';


            $query_total = $query_select . $query_join . $query_where . $query_orderby;

            $result = mysql_query($query_total ,$this->connection);
            if(!$result)
            {
                $this->HandleDBError("Error deleting data from the table\nquery:$query_total");
                return false;
            }
            return $result;
    }

    function CheckIfOndemandVideoIsMarked($ondemandId)
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
        
        $query_select = 'SELECT * FROM ondemand WHERE ondemand_id = ' . $ondemandId . ' and ondemand_join_id is not NULL';
        
        $result = mysql_query($query_select ,$this->connection);
        if(!$result)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$query_select");
            return false;
        }
        
        return $result;
    }
    
    function MarkOndemandVideoToJoin($ondemandIdList)
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
       
        // Genero un id univoco di 12 caratteri
        $ondemandJoinId = substr(uniqid("join_"), 0, 15);
        
        $query_total = 'UPDATE ondemand SET ondemand_join_id = "'. $ondemandJoinId . '" WHERE ondemand_id in ('. $ondemandIdList . ')';
        
        $result_update = mysql_query($query_total ,$this->connection);
        if(!$result_update)
        {
            $this->HandleDBError("Error updating data from the table\nquery:$query_total");
            return false;
        }
        
        $query_total = 'INSERT INTO ondemand_actions_join(ondemand_actions_join_id, ondemand_actions_join_list, ondemand_actions_join_date) '.
                'VALUES ("' . $ondemandJoinId . '","' . $ondemandIdList . '",CURRENT_TIMESTAMP)';
        
        $result_insert = mysql_query($query_total ,$this->connection);
        if(!$result_insert)
        {
            $this->HandleDBError("Error inserting data from the table\nquery:$query_total");
            return false;
        }
        
        return $result_insert;
    }
    
    function GetAllOnDemandActionsJoin()
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
        $query_select = 'SELECT * FROM ondemand_actions_join ORDER BY ondemand_actions_join_date';
        
        $result_select = mysql_query($query_select ,$this->connection);
        if(!$result_select)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$query_select");
            return false;
        }
        
        return $result_select;
    }

    function DeleteOnDemandActionsJoin($joinIds)
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
        
        $count = 0;
        $joinIdsToString = null;
        foreach ($joinIds as $id) 
        {
            $joinIdsToString .= '"' . $id . '"';
            $count++;
            
            if (isset($joinIds[$count]))
            {
                $joinIdsToString .= ',';
            }
        }
        
        error_log("INFO - join ids for db: " . $joinIdsToString);
        
        $query_select = 'DELETE FROM ondemand_actions_join WHERE ondemand_actions_join_id in (' . $joinIdsToString . ')';
        
        $result_select = mysql_query($query_select ,$this->connection);
        if(!$result_select)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$query_select");
            return false;
        }
        
        return $result_select;
    }
    
    function ResetOndemandVideoActionsJoin($joinIds)
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

        $count = 0;
        $joinIdsToString = null;
        foreach ($joinIds as $id) 
        {
            $joinIdsToString += '"' . $id . '"';
            $count++;
            
            if (isset($joinIds[$count]))
            {
                $joinIdsToString += ',';
            }
        }
        
        $query_update = 'UPDATE ondemand SET ondemand_join_id = NULL WHERE ondemand_join_id in ('. $joinIdsToString . ')';
        
        $result_update = mysql_query($query_update ,$this->connection);
        if(!$result_update)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$query_update");
            return false;
        }
        
        return $result_update;        
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

