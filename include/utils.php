<?PHP
/*
    This program is free software published under the
    terms of the GNU Lesser General Public License.
    http://www.gnu.org/copyleft/lesser.html


This program is distributed in the hope that it will
be useful - WITHOUT ANY WARRANTY; without even the
implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.

*/

class Utils
{

/*
    Sanitize() function removes any potential threat from the
    data submitted. Prevents email injections or any other hacker attempts.
    if $remove_nl is true, newline chracters are removed from the input.
    */
    function Sanitize($str,$remove_nl=true)
    {
        $str = StripSlashes($str);

        if($remove_nl)
        {
            $injections = array('/(\n+)/i',
                '/(\r+)/i',
                '/(\t+)/i',
                '/(%0A+)/i',
                '/(%0D+)/i',
                '/(%08+)/i',
                '/(%09+)/i'
                );
            $str = preg_replace($injections,'',$str);
        }

        return $str;
    }
    
    function StripSlashes($str)
    {
        if(get_magic_quotes_gpc())
        {
            $str = stripslashes($str);
        }
        return $str;
    }

	//-------Public Helper functions -------------
    function GetSelfScript()
    {
        return htmlentities($_SERVER['PHP_SELF']);
    }

    function SafeDisplay($value_name)
    {
        if(empty($_POST[$value_name]))
        {
            return'';
        }
        return htmlentities($_POST[$value_name]);
    }

    function RedirectToURL($url)
    {
        header("Location: $url");
        exit;
    }

    function GetSpamTrapInputName($rand_key)
    {
        return 'sp'.md5('KHGdnbvsgst'.$rand_key);
    }
    
    /**
     * Convert number of seconds into hours, minutes and seconds
     * and return an array containing those values
     *
     * @param integer $seconds Number of seconds to parse
     * @return array
     */
    public function SecondsToTime($seconds)
    {
        // extract hours
        $hours = floor((float)$seconds / (60 * 60));
     
        // extract minutes
        $divisor_for_minutes = (float)$seconds % (60 * 60);
        $minutes = floor($divisor_for_minutes / 60);
     
        // extract the remaining seconds
        $divisor_for_seconds = $divisor_for_minutes % 60;
        $seconds = ceil($divisor_for_seconds);
     
        // return the final array
        $obj = array(
            "h" => (int) $hours,
            "m" => (int) $minutes,
            "s" => (int) $seconds,
        );
        return $obj;
    }
    
    public function RenameFileToLowerCase($fullPath)
    {
        try
        {
            $filename = basename($fullPath);
            $dir = dirname($fullPath);

            $oldName = $filename;
            $newName = strtolower($filename);

            rename("$dir/$oldName","$dir/$newName");

            return "$dir/$newName";
        }
        catch(Exception $e)
        {
            error_log("ERROR - RenameFileToLowerCase() - fullPath->[" . $fullPath . "] - " . $e->getMessage());
            return FALSE;
        }
    }
}

