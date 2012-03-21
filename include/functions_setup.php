<?PHP
//
// The PHP file which stores all the functions referenced by index.php
//

function setup_certstore_form() {
$this_certstore = dirname($_SESSION['cwd'])."/certstore";
?>
<h1>PHP-CA Configure Certificate Store</h1>
<p>
<b>Certificate Store Location</b><br/>
<form action="index.php" method="post">
<input type="hidden" name="menuoption" value="setup_certstore"/>
<table  style="width: 400px;">
<tr><th>Please Enter Absolute Path for Cert Store: (eg. /var/www/certstore)</th></tr>
<tr><td><input type="text" name="certstore_path" value="<?PHP print $this_certstore;?>" size="30"/>
<tr><td><input type="submit" value="Submit Certificate Store"/>
</table>
</form>
</p>
<?PHP
}

function setup_certstore($my_certstore_path) {
if (!is_writable(dirname($my_certstore_path)) ) {
  $is_writable = FALSE;
  print "You do not have write permissions to the file $my_certstore_path<BR>One way around this is in Linux is to use<BR>chown -R www-data:www-data ".dirname($my_certstore_path)."<BR>\n<BR>\n";
  exit();
  }
if (!is_dir($my_certstore_path) && $is_writable)
  mkdir($my_certstore_path,0700,true) or die('Fatal: Unable to create Certificate Store folder'.$my_certstore_path);

$is_writable = TRUE;
if (!is_writable('./include/settings.php') ){
  $is_writable = FALSE;
  print "You do not have write permissions to the file ".$_SESSION['cwd']."/include/settings.php<BR>One way around this in Linux is to use<BR>chown -R www-data:www-data ".$_SESSION['cwd']."/include<BR>\n<BR>\n";
  exit();
  } 
if ($is_writable) {
  $my_settings=file_get_contents('./include/settings.php') or die('Fatal: Unable to open ./include/settings.php');
  if (substr($my_certstore_path,-1) != '/') $my_certstore_path = $my_certstore_path.'/';
  $my_settings = str_replace('NOT_DEFINED',$my_certstore_path,$my_settings) or die('Unable to update variable holding settings string'); 
  file_put_contents('./include/settings.php',$my_settings) or die('Fatal: Unable to write ./include/settings.php');
  }
?>
<h1>Initial Setup Complete</h1>
<h1>Now Create a Certificate Authority</h1>
<form action="index.php" method="post">
<input type="hidden" name="menuoption" value="create_ca_form"/>
<input type="hidden" name="ca_name" value="zzCREATEZZnewZZ"/>
<input type="submit" value="Create a Certificate Authority"/>
</form>
</p>
<?PHP
}


function create_cert_store($my_certstore_path, $my_common_name) {
$_SESSION['my_ca']=$my_common_name;
include('settings.php');
print "Creating Directories...\n<br>";
if (!is_dir($config['ca_path']))
  mkdir($config['ca_path'],0700,true) or die('Fatal: Unable to create CA folder');
if (!is_dir($config['req_path']))
  mkdir($config['req_path'],0700,true) or die('Fatal: Unable to create REQ folder');
if (!is_dir($config['key_path']))
  mkdir($config['key_path'],0700,true) or die('Fatal: Unable to create KEY folder');
if (!is_dir($config['cert_path']))
  mkdir($config['cert_path'],0700,true) or die('Fatal: Unable to create CERT folder');
if (!is_dir($config['newcert_path']))
  mkdir($config['newcert_path'],0700,true) or die('Fatal: Unable to create NEWCERT folder');
if (!is_dir($config['ssh_pubkey_path']))
  mkdir($config['ssh_pubkey_path'],0700,true) or die('Fatal: Unable to create SSH_PUBKEY folder');
if (!is_dir($config['csr_upload_path']))
  mkdir($config['csr_upload_path'],0700,true) or die('Fatal: Unable to create CSR_UPLOAD folder');
if (!is_dir($config['crl_path']))
  mkdir($config['crl_path'],0700,true) or die('Fatal: Unable to create CRL folder');
print "Done\n<br><br>";

print "Creating Serial File...\n<br>";
$fp = fopen($config['serial'], "w") or die('Unable to open write Serial Number file '.$config['serial']);
fputs($fp, sprintf("%04d", "1") . chr(0) . chr(10) ) or die('Unable to write serial number to Serial Number file '.$config['serial']);
fclose($fp)or die('Unable to close write Serial Number file '.$config['serial']);
print "Done.\n<br><br>";

print "Creating blank index file...\n<br>";
$fp = fopen($config['index'], "w") or die('Unable to open write Index file '.$config['index']);
fclose($fp)or die('Unable to close write Index file '.$config['index']);
print "Done.\n<br><br>";

print "Creating openssl.conf file for this CA\n<br>";
$orig_fp = fopen('./include/openssl.conf',"r") or die('Unable to open OPENSSL.CONF template');
$new_fp = fopen($config['config'],"w") or die('Unable to open OPENSSL.CONF new file');

while (!feof($orig_fp)) {
   $this_line = rtrim(fgets($orig_fp));
   if ( strpos($this_line,'ZZ_REPLACE_ME_ZZ') )
     $this_line = str_replace('ZZ_REPLACE_ME_ZZ',substr($config['ca_path'],0,-1),$this_line);
   fwrite($new_fp,$this_line."\n");
}
fclose($orig_fp);
fclose($new_fp);
print "Done\n<br><br>";
print "Certstore files and folders created successfully.<br><br>\n";
}


?>