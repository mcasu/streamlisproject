<?PHP

include_once('mysql-fix.php');

class DBActions
{
    var $error_message;

    var $connection;
    var $pdoConn;
    var $db_host;
    var $username;
    var $pwd;
    var $database;

    function DBActions($host, $uname, $pwd, $database)
    {
        $this->db_host  = $host;
        $this->database  = $database;
        $this->username = $uname;
        $this->pwd  = $pwd;
    
        // open database connection
        $connectionString = sprintf("mysql:host=%s;dbname=%s",$this->db_host,$this->database);
        
        try 
        {
            $this->connection = mysql_connect($host,$uname,$pwd);
            mysql_select_db($database, $this->connection);
            mysql_query("SET NAMES 'UTF8'", $this->connection);

            $this->pdoConn = new PDO($connectionString,$uname,$pwd);
        } 
        catch (PDOException $pe) 
        {
            error_log("ERROR - " . $pe->getMessage());
            die($pe->getMessage());
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
/*      if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        } */
        
        $field_val = $this->SanitizeForSQL($groupvars[$fieldname]);
        //$query = "select group_name from groups where $fieldname='".$field_val."'";
        $queryCount = "select COUNT(*) from groups where $fieldname='".$field_val."'";

        $results = $this->pdoConn->query($queryCount);
        if ($results && $results->fetchColumn() > 0)
        {
            return false;
        }

        return true;
    }

    function CheckLoginInDB($username,$password)
    {
/*         if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        } */

        $username = $this->SanitizeForSQL($username);
        $pwdmd5 = md5($password);
        $query = "select * from users where username='$username' and password='$pwdmd5'";
        $queryCount = "select COUNT(*) from users where username='$username' and password='$pwdmd5'";

        $results = $this->pdoConn->query($queryCount);
        if (!$results || $results->fetchColumn() <= 0)
        {
            $this->HandleError("ERRORE LOGIN - Il nome utente o la password inseriti non sono validi.");
            return false;
        }
        $stm = $this->pdoConn->query($query);
        $stm->setFetchMode(PDO::FETCH_ASSOC);
        $row = $stm->fetch();
/* 
        $result = mysql_query($qry,$this->connection);

        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("ERRORE LOGIN - Il nome utente o la password inseriti non sono validi.");
            return false;
        } */

        //$row = mysql_fetch_assoc($result);
       
	
//	$session_alive_time = time() - strtotime($row['last_update']);
//	
//	if ($row['user_logged'] == '1')
//	{
//		// Se un utente ha "abbandonato" la sessione chiudendo il browser per più di 5m = 300sec
//		// allora distruggo la sessione e faccio logout
//                error_log("INFO - User [" . $row['username'] . "] session alive time -> [" . $session_alive_time . "]");
//                
////		if ($session_alive_time <= 120)
////		{
////			$this->HandleError("ERRORE LOGIN - L'utente inserito ha già effettuato login. \nUsare un nome utente diverso.");
////			return false;
////		}
//		
//		$this->UpdateUserLoginStatus($row['username'], false);
//		//session_destroy();
//	}
	
        $userdata = array();
        $userdata['user_id']  = $row['id_user'];
        $userdata['username']  = $row['username'];
        $userdata['user_fullname']  = $row['name'];
        $userdata['user_email'] = $row['email'];
        $userdata['user_group_id'] = $row['user_group_id'];
        $userdata['user_role_id'] = $row['user_role_id'];
        $userdata['users_viewall'] = $row['users_viewall'];
        
        $query = "select * from groups where group_id='".$row['user_group_id']. "'";
        $stm = $this->pdoConn->query($query);
        $stm->setFetchMode(PDO::FETCH_ASSOC);
        $row_group = $stm->fetch();

        if (!$row_group)
        {
                $this->HandleError("Error getting group data.");
                return false;
        }
        
	    $userdata['user_group_name'] = $row_group['group_name'];

        return $userdata;
    }

    function ChangePasswordInDB($user_rec, $newpwd)
    {
/* 	    if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        } */

        $newpwd = $this->SanitizeForSQL($newpwd);

        $query = "update users set password='".md5($newpwd)."' Where  id_user=".$user_rec['id_user']."";

        if(!$this->pdoConn->query($query))
        {
            $this->HandleDBError("Error updating the password \nquery:$query");
            return false;
        }
        return true;
    }
    
    function UpdateUserLoginStatus($username, $status, $mysqlTime = NULL, $islogin = false)
    {
/* 	    if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        } */

        $query = 'update users set user_logged = "'.$status.'", last_update = "' . $mysqlTime . '" where username = "'.$username.'"';
        if ($islogin)
        {
            $query = 'update users set user_logged = "'.$status.'", last_login = "' . $mysqlTime . '", last_update = "' . $mysqlTime . '" where username = "'.$username.'"';	
        }
        
        if(!$this->pdoConn->query($query))
        {
            $this->HandleDBError("Error updating the user login status \nquery:$query");
            return false;
        }
        
        return true;
    }
    
    function CleanLoginOlderThan($seconds)
    {
/* 	    if(!$this->DBLogin())
        {
            return false;
        } */
	
        $query = 'UPDATE users SET user_logged = "0" WHERE user_logged = "1" and TIMESTAMPDIFF(SECOND,last_update,now()) > \''.$seconds.'\'';
        
        try 
        {
            $affectedRows = $this->pdoConn->exec($query);
            //$affectedRows = mysql_affected_rows($this->connection);
	    } 
        catch (Exception $e) 
        {
            $this->HandleDBError("Error updating the user login status \nQuery: " .$query. "\n". $e->getMessage());
            return false;
        }
        
        return $affectedRows;
    }
    
    function GetGroupById($groupId)
    {
/* 	    if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        } */

	    $query = 'select * from groups where group_id = ' . $groupId;

        $stm = $this->pdoConn->query($query);
        if(!$stm)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$query");
            return false;
        }

        $row = $stm->fetch();
	    return $row;
    }

    function GetGroupByToken($groupToken)
    {
/* 	    if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        } */

	    $query = 'select * from groups where group_token = \'' . $groupToken . '\'';

        $stm = $this->pdoConn->query($query);
        if(!$stm)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$query");
            return false;
        }

        $row = $stm->fetch();
	    return $row;
    }    
    
    function GetGroupIdByName($group_name)
    {
/* 	    if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        } */
        
        $query = 'select group_id from groups where group_name =\'' . $group_name . '\'';

        $stm = $this->pdoConn->query($query);
        if(!$stm)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$query");
            return false;
        }

        $row = $stm->fetch();
	    return $row['group_id'];
    }
    
    function UpdateGroupLiveToken($groupId)
    {
/*         if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        } */

        $token = md5(uniqid());
	    $updateQuery = 'UPDATE `groups` SET `group_token`=\''. $token .'\' WHERE group_id =\''. $groupId .'\'';

        //$result = mysql_query($updateQuery ,$this->connection);
        if(!$this->pdoConn->query($query))
        {
            $this->HandleDBError("Error updating data from the table\nquery:$updateQuery");
            return false;
        }
	    return true;
    }
    
    function GetPublishCodeByGroupId($group_id)
    {
/*         if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        } */

	    $query = 'select publish_code from groups where group_id =\'' . $group_id . '\'';
        $stm = $this->pdoConn->query($query);
        if(!$stm)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$query");
            return false;
        }

        $row = $stm->fetch();
	    return $row['publish_code'];
    }
    
    function GetGroupNameByPublishCode($publish_code)
    {
/* 	    if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        } */

	    $query = 'select group_name from groups where publish_code =\'' . $publish_code . '\'';

        $stm = $this->pdoConn->query($query);
        if(!$stm)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$query");
            return false;
        }

        $row = $stm->fetch();
	    return $row['group_name'];
    }

    function GetUserById($user_id,&$user_rec)
    {
/*         if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        } */

        $id = $this->SanitizeForSQL($user_id);
        $query = "select * from users where id_user='$id'";
        $queryCount = "select COUNT(*) from users where id_user='$id'";

        $results = $this->pdoConn->query($queryCount);
        if(!$results || $results->fetchColumn() <= 0)
        {
            $this->HandleError("There is no user with ID: $id");
            return false;
        }
        $stm = $this->pdoConn->prepare($query);
        $stm->execute();
        $user_rec = $stm->fetch(PDO::FETCH_ASSOC);

        return true;
    }
    
    function GetUserByUsername($username,&$user_rec)
    {
/* 	    if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        } */

        $name = $this->SanitizeForSQL($username);
        $query = "select * from users where username='$name'";
        $queryCount = "select COUNT(*) from users where username='$name'";

        $results = $this->pdoConn->query($queryCount);
        if(!$results || $results->fetchColumn() <= 0)
        {
            $this->HandleError("Non esistono utenti con username: $name");
            return false;
        }
        $stm = $this->pdoConn->prepare($query);
        $stm->execute();
        $user_rec = $stm->fetch(PDO::FETCH_ASSOC);

        return true;
    }

    function InsertIntoDB($uservars)
    {
        error_log("\n INFO - " . print_r($uservars,true));

        $select_query_group = 'select * from groups where group_name =\'' . $uservars['group_name'] . '\'';

        $result = mysql_query($select_query_group ,$this->connection);
        if(!$result)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$select_query_group");
            return false;
        }
        $row_group = mysql_fetch_assoc($result);
        error_log("\n INFO - " . print_r($row_group,true));

        $select_query_role = 'select * from user_roles where role_name =\'' . $uservars['user_role_name'] . '\'';

        $result = mysql_query($select_query_role ,$this->connection);
        if(!$result)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$select_query_role");
            return false;
        }
        $row_role = mysql_fetch_assoc($result);
        error_log("\n INFO - " . print_r($row_role,true));

        if (array_key_exists('email',$uservars))
        {
            $insert_query = 'insert into users (
                name,
                email,
                username,
                password,
                user_group_id,
                user_role_id
                )
                values
                (
                "' . $this->SanitizeForSQL($uservars['name']) . '",
                "' . $this->SanitizeForSQL($uservars['email']) . '",
                "' . $this->SanitizeForSQL($uservars['username']) . '",
                "' . md5($uservars['password']) . '",
                "' . $this->SanitizeForSQL($row_group['group_id']) . '",
                "' . $this->SanitizeForSQL($row_role['role_id']) . '"
                )';
        }
        else
        {
            $insert_query = 'insert into users (
                name,
                username,
                password,
                user_group_id,
                user_role_id
                )
                values
                (
                "' . $this->SanitizeForSQL($uservars['name']) . '",
                "' . $this->SanitizeForSQL($uservars['username']) . '",
                "' . md5($uservars['password']) . '",
                "' . $this->SanitizeForSQL($row_group['group_id']) . '",
                "' . $this->SanitizeForSQL($row_role['role_id']) . '"
                )';
        }

        error_log("\n INSERT QUERY: [ " . $insert_query . "]\n");
        if(!mysql_query( $insert_query ,$this->connection))
        {
            $this->HandleDBError("Error inserting data to the table\nquery:$insert_query");
            return false;
        }
        return mysql_insert_id();
    }

    function InsertGroupIntoDB(&$groupvars)
    {
/*         if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        } */
        
        $select_query_role = 'select * from group_roles where role_name =\'' . $groupvars['group_role_name'] . '\'';
        $row_role = null;
        try
        {
            $stm = $this->pdoConn->prepare($select_query_role);
            $stm->execute();
            $row_role = $stm->fetch(PDO::FETCH_ASSOC);
        }
        catch(Exception $e)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$select_query_role". "\n". $e->getMessage());
            return false;
        }

        $publish_code = $this->ParseGroupName($groupvars['group_name']);

        $insert_query = 'insert into groups (
        group_name,
        group_type,
        group_role,
        publish_code,
        group_token
        )
        values
        (
        "' . $this->SanitizeForSQL($groupvars['group_name']) . '",
        "' . $this->SanitizeForSQL($groupvars['group_type']) . '",
        "' . $this->SanitizeForSQL($row_role['role_id']) . '",
        "' . $this->SanitizeForSQL(strtolower($publish_code)) . '",
        "' . md5(uniqid()) . '"
        )';

        if(!$this->pdoConn->query($insert_query))
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
                $insert_query = 'insert into live (
                nginx_id,
		live_date,
		live_time,
                app_name,
                stream_name,
                client_addr,
                publish_code,
                live_token)
                values
                (
                "' . $this->SanitizeForSQL($nginx_id) . '",
                "' . $this->SanitizeForSQL($mysqldate) . '",
                "' . $this->SanitizeForSQL($mysqltime) . '",
                "' . $this->SanitizeForSQL($app_name) . '",
                "' . $this->SanitizeForSQL($stream_name) . '",
                "' . $this->SanitizeForSQL($client_addr) . '",
                "' . $this->SanitizeForSQL($publish_code) . '",
                "' . md5(uniqid()) . '"
                )';

                if(!mysql_query( $insert_query ,$this->connection))
                {
                    $this->HandleDBError("Error inserting data to the table\nquery:$insert_query");
                    return false;
                }
                return true;
        }

    function InsertEventsLiveToken($eventsLiveId, $token)
    {
        $update_query = 'UPDATE `live` SET `live_token`="' . $token . '" '
                . 'WHERE live_id = ' . $eventsLiveId;
        
        if(!mysql_query($update_query ,$this->connection))
        {
            $this->HandleDBError("Error inserting data to the table\nquery:$update_query");
            return false;
        }
        return true;
    }
    
    function GetEventsLiveData($token)
    {
        $selectQuery = 'SELECT * FROM `live` WHERE live_token = "' . $token . '"';
        
        $result = mysql_query($selectQuery ,$this->connection);
        
        if(!$result)
        {
            $this->HandleDBError("Error inserting data to the table\nquery:$selectQuery");
            return false;
        }
        return $result;
    }
                
    function OnPublishDone($nginx_id,$app_name,$stream_name,$client_addr)
    {
            $delete_query = 'delete from live where nginx_id = "' . $this->SanitizeForSQL($nginx_id) . '"';

            if(!mysql_query( $delete_query ,$this->connection))
            {
                $this->HandleDBError("Error deleting data from the table\nquery:$delete_query");
                return false;
            }
            return true;
    }

    function UpdateOndemandEvent($ondemandId, $ondemandEventInfos)
    {
        if (!isset($ondemandId))
        {
            $this->HandleDBError("UpdateOndemandEvent() - Parametro \$ondemandId non valido!");
            return false;            
        }
        
        if (!isset($ondemandEventInfos) || !is_array($ondemandEventInfos))
        {
            $this->HandleDBError("UpdateOndemandEvent() - Parametro \$ondemandEventInfos non valido!");
            return false;             
        }
        
        $query_update = 'UPDATE ondemand SET ';
        
        $count = 0;
        foreach ($ondemandEventInfos as $eventInfo) 
        {
            $query_update .= $eventInfo[0] . ' = \'' . $eventInfo[1] . '\' ';
            
            $count++;
            
            if (isset($ondemandEventInfos[$count]))
            {
                $query_update .= ',';
            }
        }
        
        $query_where = 'WHERE ondemand_id = ' . $ondemandId . ' ';

        $query_total = $query_update . $query_where;
        
        if(!mysql_query($query_total ,$this->connection))
        {
            $this->HandleDBError("Error inserting data to the table\nquery:$query_total");
            return false;
        }
        return true;
    }
    
    function OnRecordDone(
            $app_name,
            $stream_name,
            $ondemand_path,
            $ondemand_filename,
            $ondemandFileSize,
            $video_duration, 
            $video_bitrate,
            $videoFrameRate,
            $videoRes,
            $video_codec,
            $mysqldate = null)
    {

            $insert_query = 'insert into ondemand (
            ondemand_publish_code,
            ondemand_path,
            ondemand_app_name,
            ondemand_filename,
            ondemand_filesize,
            ondemand_movie_duration,
            ondemand_movie_bitrate,
            ondemand_movie_framerate,
            ondemand_movie_res,
            ondemand_movie_codec,
            ondemand_date)
            values
            (
            "' . $this->SanitizeForSQL($stream_name) . '",
            "' . $this->SanitizeForSQL($ondemand_path) . '",
            "' . $this->SanitizeForSQL($app_name) . '",
            "' . $this->SanitizeForSQL($ondemand_filename) . '",
            "' . $ondemandFileSize . '",                
            "' . $this->SanitizeForSQL($video_duration) . '",
            "' . $this->SanitizeForSQL($video_bitrate) . '",
            "' . $this->SanitizeForSQL($videoFrameRate) . '",
            "' . $this->SanitizeForSQL($videoRes) . '",
            "' . $this->SanitizeForSQL($video_codec) . '",
            "' . $this->SanitizeForSQL($mysqldate) . '"
            )';

            if(!mysql_query( $insert_query ,$this->connection))
            {
                $this->HandleDBError("Error inserting data to the table\nquery:$insert_query");
                return false;
            }
            return mysql_insert_id();
    }

    function GetGroups()
    {
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
            $select_query = 'SELECT users.id_user as user_id, '.
                            'users.name, '.
                            'users.email, '.
                            'users.username, '.
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
            $select_query = 'SELECT users.id_user as user_id, '.
                            'users.name, '.
                            'users.email, '.
                            'users.username, '.
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
            $select_query = 'select role_id,role_name as user_role_name from user_roles order by role_name';   

            $result = mysql_query($select_query ,$this->connection);
            if(!$result)
            {
                $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
                return false;
            }
            return $result;
    }

    function DeleteUsers($userIds)
    {
/*         if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        } */

        $delete_query = 'delete from users where id_user in ('.$userIds.')';
        $stm = $this->pdoConn->query($delete_query);
        if(!$stm)
        {
            $this->HandleDBError("Error deleting data from the table\nquery:$delete_query");
            return false;
        }
        return $stm;
    }    
    
    function DeleteUser($user_id)
    {
            $delete_query = 'delete from users where id_user = \''.$user_id.'\'';

            $results = $this->pdoConn->query($delete_query);
            if(!$results)
            {
                $this->HandleDBError("Error deleting data from the table\nquery:$delete_query");
                return false;
            }
            return $results;
    }
    
    function UpdateUser($userId, $fullName, $email, $username, $groupName, $roleName, $viewall)
    {
/*         if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        } */
        
        $query = 'update users set name = "'.$fullName.'", email = "' . $email . '", username = "' . $username . '",'.
                ' user_group_id = (select groups.group_id from groups where LOWER(groups.group_name) = "'. strtolower($groupName) .'" LIMIT 1),'.
                ' user_role_id = (select user_roles.role_id from user_roles where LOWER(user_roles.role_name) = "'. strtolower($roleName) .'" LIMIT 1), '.
                ' users_viewall = \''.$viewall.'\''.
                ' where id_user = \''.$userId.'\'';
        
        $result = $this->pdoConn->query($query);
        if(!$result)
        {
            $this->HandleDBError("Error updating data from the table\nquery:$query");
            return false;
        }
        
        return $result;
    }

    function DeleteGroup($group_id)
    {
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

    function DeleteGroups($groupIds)
    {
/*         if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        } */

        $delete_query = 'delete from groups where group_id in ('.$groupIds.')';

        $results = $this->pdoConn->query($delete_query);
        if(!$results)
        {
            $this->HandleDBError("Error deleting data from the table\nquery:$delete_query");
            return false;
        }
        return $results;
    }     
    
    
    function DeleteEventOnDemand($ondemand_id)
    {
            $delete_query = 'delete from ondemand where ondemand_id = \''.$ondemand_id.'\'';

            $result = $this->pdoConn->query($delete_query);
            if(!$result)
            {
                $this->HandleDBError("Error deleting data from the table\nquery:$delete_query");
                return false;
            }
            return $result;
    }
    
    function DeleteAllEventsLive()
    {
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
            $select_query = 'SELECT group_links.viewer_id, '.
                    'groups.group_name as viewer_name, '.
                    'groups.group_type, '.
                    'group_roles.role_id, '.
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

    function GetGroupsToLinkAvailable($publisher_id)
    {
            $select_query = 'select groups.group_name,groups.group_id,groups.group_role from groups where group_id not in (select viewer_id from group_links INNER JOIN groups ON group_links.viewer_id = groups.group_id where publisher_id = \''.$publisher_id.'\') AND group_id != ' .$publisher_id. ' order by group_name;';

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
            $select_query = 'select group_links.publisher_id, groups.group_name as publisher_name, groups.publish_code as publisher_code '.
                            'from group_links '.
                            'INNER JOIN groups ON group_links.publisher_id = groups.group_id '.
                            'where group_links.viewer_id = \''.$viewer_id.'\' '.
                            'UNION '. 
                            'select groups.group_id as publisher_id, groups.group_name as publisher_name, groups.publish_code as publisher_code '.
                            'from groups '.
                            'where groups.group_type = \'Congregazione\' and groups.group_id = \''.$viewer_id.'\' ORDER BY publisher_name;';

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
            $select_query = 'select role_id,role_name as group_role_name from group_roles order by role_name'; 

            $result = mysql_query($select_query ,$this->connection);
            if(!$result)
            {
                $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
                return false;
            }
            return $result;
    }

    function GetLiveEventsById($eventsLiveId)
    {	
            $select_query = 'select * from live where live_id = '.$eventsLiveId;

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
            $select_query = 'select * from ondemand where ondemand_publish_code = \''.$publish_code.'\' order by ondemand_filename';

            $result = mysql_query($select_query ,$this->connection);
            if(!$result)
            {
                $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
                return false;
            }
            return $result;
    }

    function GetOndemandEventById($eventId)
    {
            $select_query = 'select * from ondemand where ondemand_id = \''.$eventId.'\';';

            $result = mysql_query($select_query ,$this->connection);
            if(!$result)
            {
                $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
                return false;
            }
            return $result;
    }
    
    function GetOndemandEventsByIds($eventIdArray)
    {
            $ondemandIdsToString = join(",", $eventIdArray);
            $select_query = 'SELECT * FROM ondemand WHERE ondemand_id in ( '.$ondemandIdsToString.' ) ORDER BY ondemand_date, ondemand_time';

            $result = mysql_query($select_query ,$this->connection);
            if(!$result)
            {
                $this->HandleDBError("Error selecting data from the table\nquery:$select_query");
                return false;
            }
            return $result;
    }

    function GetUserTotalNumber()
    {
/*         $this->DBLogin(); */
        
        $queryCount = 'select count(*) as user_total from users';
        
        $stm = $this->pdoConn->query($queryCount);
        if(!$stm)
        {
            $this->HandleDBError("Error selecting data from the table\nquery: $queryCount");
            return false;
        }
        
        $row = $stm->fetch();
        return $row['user_total'];
    }
    
    function GetUserLoggedNumber()
    {
        //$this->DBLogin();
        
        $queryCount = 'select count(*) as user_logged from users where users.user_logged = \'1\'';
        
        $stm = $this->pdoConn->query($queryCount);
        if(!$stm)
        {
            $this->HandleDBError("Error selecting data from the table\nquery: $queryCount");
            return false;
        }
        
        $row = $stm->fetch();
        return $row['user_logged'];
    }
    
    function GetCongregationTotalNumber()
    {
        //$this->DBLogin();
        
        $queryCount = 'select count(*) as congregation_total from groups where group_type = \'Congregazione\'';
        
        $stm = $this->pdoConn->query($queryCount);
        if(!$stm)
        {
            $this->HandleDBError("Error selecting data from the table\nquery: $queryCount");
            return false;
        }
        
        $row = $stm->fetch();
        return $row['congregation_total'];
    }    
    
    function GetGroupTotalNumber()
    {
        //$this->DBLogin();
        
        $queryCount = 'select count(*) as group_total from groups where group_type = \'Gruppo\'';
        
        $stm = $this->pdoConn->query($queryCount);
        if(!$stm)
        {
            $this->HandleDBError("Error selecting data from the table\nquery: $queryCount");
            return false;
        }
        
        $row = $stm->fetch();
        return $row['group_total'];
    }     
    
    function GetUsers($onlyLogged = false)
    {
            $query_select = 'select id_user as user_id,name as user_name,email as user_mail,username,password,user_group_id,group_name as user_group_name,user_role_id,role_name as user_role_name,user_logged,last_login,last_update from users ';

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

    function CheckIfOndemandVideoIsMarkedToConvert($ondemandId)
    {
        $query_select = 'SELECT * FROM ondemand WHERE ondemand_id = ' . $ondemandId . ' and ondemand_convert_id is not NULL';
        
        $result = mysql_query($query_select ,$this->connection);
        if(!$result)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$query_select");
            return false;
        }
        
        return $result;
    }
    
    function CheckIfOndemandVideoIsMarkedToJoin($ondemandId)
    {
        $query_select = 'SELECT * FROM ondemand WHERE ondemand_id = ' . $ondemandId . ' and ondemand_join_id is not NULL';
        
        $result = mysql_query($query_select ,$this->connection);
        if(!$result)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$query_select");
            return false;
        }
        
        return $result;
    }
    
    function MarkOndemandVideoToJoin($ondemandIdList, $userId)
    {
        // Genero un id univoco di 12 caratteri
        $ondemandJoinId = substr(uniqid("join_"), 0, 15);
        
        $query_total = 'UPDATE ondemand SET ondemand_join_id = "'. $ondemandJoinId . '" WHERE ondemand_id in ('. $ondemandIdList . ')';
        
        $result_update = mysql_query($query_total ,$this->connection);
        if(!$result_update)
        {
            $this->HandleDBError("Error updating data from the table\nquery:$query_total");
            return false;
        }
        
        $query_total = 'INSERT INTO ondemand_actions_join(ondemand_actions_join_id, ondemand_actions_join_list, ondemand_actions_join_date,ondemand_actions_user_id) '.
                'VALUES ("' . $ondemandJoinId . '","' . $ondemandIdList . '",CURRENT_TIMESTAMP,' . $userId . ')';
        
        $result_insert = mysql_query($query_total ,$this->connection);
        if(!$result_insert)
        {
            $this->HandleDBError("Error inserting data from the table\nquery:$query_total");
            return false;
        }
        
        return $result_insert;
    }
    
    function MarkOndemandVideoToConvert($ondemandIdList, $userId = -1)
    {
        // Genero un id univoco di 12 caratteri
        $ondemandConvertId = substr(uniqid("conv_"), 0, 15);
        
        $query_total = 'UPDATE ondemand SET ondemand_convert_id = "'. $ondemandConvertId . '" WHERE ondemand_id in ('. $ondemandIdList . ')';
        
        $result_update = mysql_query($query_total ,$this->connection);
        if(!$result_update)
        {
            $this->HandleDBError("Error updating data from the table\nquery:$query_total");
            return false;
        }
        
        $query_total = 'INSERT INTO ondemand_actions_convert(ondemand_actions_convert_id, ondemand_actions_convert_list, ondemand_actions_convert_date,ondemand_actions_user_id) '.
                'VALUES ("' . $ondemandConvertId . '","' . $ondemandIdList . '",CURRENT_TIMESTAMP,' . $userId . ')';
        
        $result_insert = mysql_query($query_total ,$this->connection);
        if(!$result_insert)
        {
            $this->HandleDBError("Error inserting data from the table\nquery:$query_total");
            return false;
        }
        
        return $result_insert;
    }
    
        function UnMarkOndemandVideoToConvert($ondemandIdList)
    {
        $query_total = 'UPDATE ondemand SET ondemand_convert_id = null WHERE ondemand_id in ('. $ondemandIdList . ')';
        
        $result_update = mysql_query($query_total ,$this->connection);
        if(!$result_update)
        {
            $this->HandleDBError("Error updating data from the table\nquery:$query_total");
            return false;
        }
        
        $query_total = 'DELETE ondemand_actions_convert WHERE ondemand_actions_convert_list in ('. $ondemandIdList . ')';
        
        $result_insert = mysql_query($query_total ,$this->connection);
        if(!$result_insert)
        {
            $this->HandleDBError("Error deleting data from the table\nquery:$query_total");
            return false;
        }
        
        return $result_insert;
    }
    
    function SetOndemandActionsJoinStatus($actionsJoinId, $actionsJoinStatus = 0)
    {
        $query_update = 'UPDATE ondemand_actions_join SET ondemand_actions_join_status = ' . $actionsJoinStatus . ' WHERE ondemand_actions_join_id = "'. $actionsJoinId . '"';
        
        $result_update = mysql_query($query_update ,$this->connection);
        if(!$result_update)
        {
            $this->HandleDBError("Error updating data from the table\nquery:$query_update");
            return false;
        }
        
        return $result_update; 
    }
    
    function SetOndemandActionsConvertStatus($actionsConvertId, $actionsConvertStatus = 0)
    {
        try
        {
            $this->pdoConn->beginTransaction();
            
            $queryUpdate = 'UPDATE ondemand_actions_convert SET ondemand_actions_convert_status = ' . $actionsConvertStatus . ' WHERE ondemand_actions_convert_id = "'. $actionsConvertId . '"';
            $this->pdoConn->exec($queryUpdate);
            $this->pdoConn->commit();
            return TRUE;
        } 
        catch (Exception $e) 
        {
            $this->HandleDBError("ERROR  - Rollback transaction in CheckAndUpdateActionsConvertStatus() - " . $e->getMessage());
            $this->pdoConn->rollBack();
            return FALSE;
        }        
    }
    
    function GetAllOnDemandActionsJoin()
    {
        $query_select = 'SELECT * FROM ondemand_actions_join WHERE ondemand_actions_join_status = 0 ORDER BY ondemand_actions_join_date';
        
        $result_select = mysql_query($query_select ,$this->connection);
        if(!$result_select)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$query_select");
            return false;
        }
        
        return $result_select;
    }

    function CheckAndUpdateActionsConvertStatus($actionsConvertId)
    {
        try
        {
            $this->pdoConn->beginTransaction();
            
            $this->pdoConn->exec('LOCK TABLES ondemand_actions_convert WRITE');
            
            $querySelect = 'SELECT ondemand_actions_convert_status FROM ondemand_actions_convert '.
                    'WHERE ondemand_actions_convert_status = 0 AND ondemand_actions_convert_id = "'. $actionsConvertId . '" ';
            
            $sthSelect = $this->pdoConn->prepare($querySelect);
            $sthSelect->execute();
            
            $status = $sthSelect->fetchColumn();
            
            if ($status == 0)
            {
                $queryUpdate = 'UPDATE ondemand_actions_convert SET ondemand_actions_convert_status = 1 WHERE ondemand_actions_convert_id = "'. $actionsConvertId . '"';
                $sthUpdate = $this->pdoConn->prepare($queryUpdate);
                $sthUpdate->execute();
                
                $this->pdoConn->commit();
                $this->pdoConn->exec('UNLOCK TABLES');
                return 0;
            }
            
            $this->pdoConn->commit();
            $this->pdoConn->exec('UNLOCK TABLES');
            return 1;
        } 
        catch (Exception $e) 
        {
            $this->HandleDBError("ERROR - Rollback transaction in CheckAndUpdateActionsConvertStatus() - " . $e->getMessage());
            $this->pdoConn->rollBack();
            $this->pdoConn->exec('UNLOCK TABLES');
            return 2;
        }
    }
    
    function GetAllOnDemandActionsConvert()
    {
        $query_select = 'SELECT * FROM ondemand_actions_convert WHERE ondemand_actions_convert_status = 0 ORDER BY ondemand_actions_convert_date';
        
        $result_select = mysql_query($query_select ,$this->connection);
        if(!$result_select)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$query_select");
            return false;
        }
        
        return $result_select;
    }    
    
    function GetActionsConvertIdByOnDemandId($ondemandId)
    {
        $query_select = 'SELECT ondemand_convert_id FROM ondemand WHERE ondemand_id = ' . $ondemandId;
        
        $result_select = mysql_query($query_select ,$this->connection);
        if(!$result_select)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$query_select");
            return false;
        }
        
        return $result_select;
    }     
    function GetOnDemandActionsConvertById($actionsConvertId)
    {
        $query_select = 'SELECT * FROM ondemand_actions_convert WHERE ondemand_actions_convert_status = 0 AND ondemand_actions_convert_id = "' . $actionsConvertId . '" ORDER BY ondemand_actions_convert_date';
        
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
        
        $query_select = 'DELETE FROM ondemand_actions_join WHERE ondemand_actions_join_id in (' . $joinIdsToString . ')';
        
        $result_select = mysql_query($query_select ,$this->connection);
        if(!$result_select)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$query_select");
            return false;
        }
        
        return $result_select;
    }
    
    function DeleteOnDemandActionsConvert($convertIds)
    {
        $count = 0;
        $convertIdsToString = null;
        foreach ($convertIds as $id) 
        {
            $convertIdsToString .= '"' . $id . '"';
            $count++;
            
            if (isset($convertIds[$count]))
            {
                $convertIdsToString .= ',';
            }
        }
        
        $query_select = 'DELETE FROM ondemand_actions_convert WHERE ondemand_actions_convert_id in (' . $convertIdsToString . ')';
        
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
        
        $query_update = 'UPDATE ondemand SET ondemand_join_id = NULL WHERE ondemand_join_id in ('. $joinIdsToString . ')';
        
        $result_update = mysql_query($query_update ,$this->connection);
        if(!$result_update)
        {
            $this->HandleDBError("Error updating data from the table\nquery:$query_update");
            return false;
        }
        
        return $result_update;        
    }
    
    function ResetOndemandVideoActionsConvert($convertIds)
    {
        $count = 0;
        $convertIdsToString = null;
        foreach ($convertIds as $id) 
        {
            $convertIdsToString .= '"' . $id . '"';
            $count++;
            
            if (isset($convertIds[$count]))
            {
                $convertIdsToString .= ',';
            }
        }
        
        $query_update = 'UPDATE ondemand SET ondemand_convert_id = NULL WHERE ondemand_convert_id in ('. $convertIdsToString . ')';
        
        $result_update = mysql_query($query_update ,$this->connection);
        if(!$result_update)
        {
            $this->HandleDBError("Error updating data from the table\nquery:$query_update");
            return false;
        }
        
        return $result_update;        
    }
    
    function GetLiveVideoFileName($streamName)
    {
        $query_select = 'SELECT * FROM live WHERE stream_name = \''.$streamName.'\' ORDER BY live_date,live_id DESC LIMIT 1';
        
        $result_select = mysql_query($query_select ,$this->connection);
        if(!$result_select)
        {
            $this->HandleDBError("Error selecting data from the table\nquery:$query_select");
            return false;
        }
        
        return $result_select;
        
    }
    
    function PublishCodeExists($publishCode)
    {
        $querySelect = 'SELECT count(*) FROM `groups` WHERE publish_code like \''. $publishCode . '\' GROUP BY publish_code';
        
        $resultSelect = mysql_query($querySelect ,$this->connection);
        if(!$resultSelect)
        {
            $this->HandleDBError("Error selecting data from the table\nquery: $querySelect");
            return false;
        }
        
        $row = mysql_fetch_array($resultSelect);
        
        return $row[0] > 0 ? true : false;
        
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

