<?PHP

// ==================================================================================================================
// =================== CHECK KEY PASSPHRASE =====================================================================================
// ==================================================================================================================

function check_key_passphrase_form(){
$config=$_SESSION['config'];
?>
<p>
<b>Check a Key Passphrase</b><br/>
<form action="index.php" method="post">
<input type="hidden" name="menuoption" value="check_key_passphrase"/>
<table  style="width: 400px;">
<tr><td width=100>Passphrase:<td><input type="password" name="pass"/>
<tr><td><td><input type="submit" value="Check Passphrase"/>
<tr><td width=100>Private Key:<td><select name="key_name" rows="6">
<option value="">--- Select a key
<?php
print "<option value=\"zzTHISzzCAzz\">This CA Key</option>\n";
$dh = opendir($config['key_path']) or die('Unable to open ' . $config['key_path']);
while (($file = readdir($dh)) !== false) {
	if ( ($file !== ".htaccess") && is_file($config['key_path'].$file) )  {
		$name = base64_decode(substr($file, 0,strrpos($file,'.')));
		$ext = substr($file, strrpos($file,'.'));
		print "<option value=\"$name$ext\">$name$ext</option>\n";
	}
}
?>
</table>
</form>
</p>
<?PHP
}

function check_key_passphrase($passPhrase,$this_key) {
$config=$_SESSION['config'];
if ($this_key == "zzTHISzzCAzz" )
  {
  $keyfile = $config['cakey'];
  }
else
  {
  $filename = $this_key;
  $keyfile = $filename;
  $keyfile = base64_encode(substr($keyfile, 0,strrpos($keyfile,'.')));
  $keyfile = $config['key_path']. $keyfile . substr($filename, strrpos($filename,'.'));
  }
  
print "<BR><b>Loading Private key...</b><br/>";
flush();
$fp = fopen($keyfile, "r") or die('Fatal: Unable to open Private Key: ' . $keyfile);
$myKey = fread($fp, filesize($keyfile)) or die('Fatal: Error whilst reading the Private Key: ' . $keyfile);
fclose($fp) or die('Fatal: Unable to close CA Key ' . $keyfile);
print "Done<br/><br/>\n";

print "<b>Decoding Private key...</b><br/>";
flush();
if ( $privkey = openssl_pkey_get_private($myKey, $passPhrase) ) {
  print "Done\n<br>Passphrase correct<br/>\n";
  }
else {
  die ('Error with passphrase for this key.');
  }
}
// ==================================================================================================================
// =================== GET PUBLIC SSH KEY =====================================================================================
// ==================================================================================================================

function get_public_ssh_key_form(){
$config=$_SESSION['config'];
?>
<p>
<b>Get Public SSH Key from a Private Key</b><br/>
<?php
//Get a Public SSH key from a private key
$valid_files=0;
$dh = opendir($config['key_path']) or die('Unable to open ' . $config['key_path']);
while (($file = readdir($dh)) !== false) {
	if ( ($file !== ".htaccess") && is_file($config['key_path'].$file) )
	  $valid_files++;
}
closedir($dh);

if ($valid_files) {
?>
<form action="index.php" method="post">
<input type="hidden" name="menuoption" value="get_public_ssh_key">
<table  style="width: 400px;">
<tr><td width=100>Key Passphrase:<td><input type="password" name="pass"/>
<tr><td width=100>Name:<td><select name="key_name" rows="6">
<option value="">--- Select a private key
<?php
$dh = opendir($config['key_path']) or die('Unable to open ' . $config['key_path']);
while (($file = readdir($dh)) !== false) {
	if ( ($file !== ".htaccess") && is_file($config['key_path'].$file) )  {
		$name = base64_decode(substr($file, 0,strrpos($file,'.')));
		$ext = substr($file, strrpos($file,'.'));
		print "<option value=\"$name$ext\">$name$ext</option>\n";
	}
}
?>
</select></td></tr>
<tr><td><td><input type="submit" value="Get Public Key">
</table>
</form>
<?php
}
else 
  print "<b> No Private Keys are available to convert into SSH Public Keys.</b>\n";
?>
</p>
<?PHP
}

function get_public_ssh_key($this_key_name,$my_passPhrase) {
$config=$_SESSION['config'];

if (!is_dir($config['ssh_pubkey_path']))
  mkdir($config['ssh_pubkey_path'],0777,true) or die('Fatal: Unable to create ssh public key folder');

$name = base64_encode(substr($this_key_name, 0,strrpos($this_key_name,'.')));
$ext = substr($this_key_name, strrpos($this_key_name,'.'));

$my_base64_keyfile=$name.$ext;
$my_key_filename=$config['key_path'].$name.$ext;
$fp = fopen($my_key_filename, "r") or die('Fatal: Error opening Private Key');
$my_key_x509 = fread($fp, filesize($my_key_filename)) or die('Fatal: Error reading Private Key');
fclose($fp) or die('Fatal: Error closing Private Key');
$my_private_key=openssl_pkey_get_private($my_key_x509, $my_passPhrase) or die('Fatal: Error decoding Private Key. Passphrase Incorrect');
$my_public_key = sshEncodePublicKey(openssl_pkey_get_details($my_private_key));
$application_type='application/octet-stream';
download_header_code($this_key_name.".ssh.pub",$my_public_key,$application_type);
}
// ==================================================================================================================
// =================== GET PRIVATE KEY =====================================================================================
// ==================================================================================================================

function get_mod_private_form(){
$config=$_SESSION['config'];
?>
<p>
<b>Get a Private Key</b><br/>
<?php
//Download a private key
$valid_files=0;
$dh = opendir($config['key_path']) or die('Unable to open ' . $config['key_path']);
while (($file = readdir($dh)) !== false) {
	if ( ($file !== ".htaccess") && is_file($config['key_path'].$file) )
	    $valid_files++;
}
closedir($dh);

if ($valid_files) {
?>
<form action="index.php" method="post">
<input type="hidden" name="menuoption" value="get_mod_private">
<table  style="width: 400px;">
<tr><td width=100>Key Passphrase:<td><input type="password" name="pass"/>
<tr><th>Rename Extension</th><td><input type="radio" name="rename_ext" value="FALSE" checked />Do not Rename<br><input type="radio" name="rename_ext" value="key" /> Rename to key</td></tr>
<tr><th>Strip Passphrase</th><td><input type="radio" name="strip_passphrase" value="FALSE" checked /> No <input type="radio" name="strip_passphrase" value="TRUE" /> Yes</td></tr>
<tr><th>Puttygen Compatable</th><td><input type="radio" name="puttygen" value="FALSE" checked /> No <input type="radio" name="puttygen" value="TRUE" /> Yes</td></tr>
<tr><td width=100>Name:<td><select name="key_name" rows="6">

<option value="">--- Select a private key
<?php
$dh = opendir($config['key_path']) or die('Unable to open ' . $config['key_path']);
while (($file = readdir($dh)) !== false) {
	if ( ($file !== ".htaccess") && is_file($config['key_path'].$file) )  {
		$name = base64_decode(substr($file, 0,strrpos($file,'.')));
		$ext = substr($file, strrpos($file,'.'));
		print "<option value=\"$name$ext\">$name$ext</option>\n";
	}
}
?>
</select></td></tr>
<tr><td><td><input type="submit" value="Get Private Key">
</table>
</form>
<?php
}
else 
  print "<b> No Valid Private Keys are available to download.</b>\n";
?>
</p>
<?PHP
}

function get_private_key($this_key_name,$my_passPhrase,$my_strip_passphrase,$my_puttygen,$cer_ext) {
$config=$_SESSION['config'];
if (!isset($cer_ext)) 
  $cer_ext='FALSE';
$this_name=substr($this_key_name, 0,strrpos($this_key_name,'.'));
$this_ext=substr($this_key_name, strrpos($this_key_name,'.'));
$down_ext=$this_ext;
$base64_name = base64_encode($this_name);
$my_base64_keyfile=$base64_name.$this_ext;
$my_key_filename=$config['key_path'].$my_base64_keyfile;
$fp = fopen($my_key_filename, "r") or die('Fatal: Error opening Private Key');
$my_privkey_x509 = fread($fp, filesize($my_key_filename)) or die('Fatal: Error reading Private Key');
fclose($fp) or die('Fatal: Error closing Private Key');
if ($my_privkey=openssl_pkey_get_private($my_privkey_x509, $my_passPhrase) or die('Fatal: Error decoding Private Key. Passphrase Incorrect') ) {
  $application_type='application/octet-stream';
  if ($my_strip_passphrase=='TRUE') 
    openssl_pkey_export($my_privkey,$my_privkey_x509);
  else
    $my_privkey_x509 = join("", file($my_key_filename));
  if ($cer_ext != 'FALSE') 
    $down_ext='.'.$cer_ext;
  if ($my_puttygen=='TRUE') {
	$this_session=session_id();
	file_put_contents('/tmp/'.$this_session.'.pem',$my_privkey_x509);
	$cmd='puttygen /tmp/'.$this_session.'.pem -O private -o /tmp/'.$this_session.'.ppk';
	exec($cmd,$retval);
	$my_privkey_x509=file_get_contents('/tmp/'.$this_session.'.ppk');
	$down_ext='.ppk';
	}
  header("Cache-Control: cache, must-revalidate");
  header("Pragma: public");
  header("Content-Type: ".$application_type);
  header("Content-Disposition: filename=\"".$this_name.$down_ext."\"");
  print $my_privkey_x509;
  }
}

?>
