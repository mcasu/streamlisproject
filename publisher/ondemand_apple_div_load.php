<?php include("/check_login.php"); ?>

<?PHP

$url = $_GET['url'];

//error_log('INFO - CERCO URL:  ' + $url);

// check if $url contains a valid URL
if (filter_var($url, FILTER_VALIDATE_URL) !== false)
{
    $headers = get_headers($url, 1);
    //$response = http_get($url, array("timeout"=>2), $info);
    
    if ($headers[0] == 'HTTP/1.1 200 OK')
    {
        echo '<a class="play-button" href="'.$url.'" target="_blank">'.
        '<img src="../images/os_apple.png"/></a>';
        echo '<br/>';
        echo "<label>Guarda il video con Apple Iphone</label>";
    }
    else
    {
        echo '<div id="block_1" class="barlittle"></div>
        <div id="block_2" class="barlittle"></div>
        <div id="block_3" class="barlittle"></div>
        <div id="block_4" class="barlittle"></div>
        <div id="block_5" class="barlittle"></div>';
        echo '<br/>';
        echo "<label>Creazione video per Apple in corso...</label>";
    }
}
