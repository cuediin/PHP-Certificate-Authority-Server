<?PHP
//
// The PHP file which stores all the functions referenced by index.php
//

// ==================================================================================================================
// =================== DOWNLOAD CERT =====================================================================================
// ==================================================================================================================

function download_cert_form(){
$config=$_SESSION['config'];
?>
<p>
<b>Download a Certificate</b><br/>
<form action="index.php" method="post">
<input type="hidden" name="menuoption" value="download_cert">
<table  style="width: 400px;">

<tr><th>Rename Extension</th><td><input type="radio" name="rename_ext" value="FALSE" checked />Do not Rename<br><input type="radio" name="rename_ext" value="cer" /> Rename to cer<br><input type="radio" name="rename_ext" value="pfx" /> Rename to pfx<br></td></tr>
<?PHP
/*

<input type="radio" name="cer_ext" value="FALSE" checked /> No <input type="radio" name="cer_ext" value="CER" /> Yes</td></tr>
<tr><th>Rename Extension to .pfx</th><td><input type="radio" name="pfx_ext" value="FALSE" checked /> No <input type="radio" name="cer_ext" value="PFX" /> Yes</td></tr>
*/
?>
<tr><td width=100>Name:<td><select name="cert_name" rows="6">
<option value="">--- Select a certificate
<?php
print "<option value=\"zzTHISzzCAzz\">This CA Certificate</option>\n";
$dh = opendir($config['cert_path']) or die('Unable to open ' . $config['cert_path']);
while (($file = readdir($dh)) !== false) {
	if ( ($file !== ".htaccess") && is_file($config['cert_path'].$file) )  {
		$name = base64_decode(substr($file, 0,strrpos($file,'.')));
		$ext = substr($file, strrpos($file,'.'));
		print "<option value=\"$name$ext\">$name$ext</option>\n";
	}
}
?>
</select></td></tr>
<tr><td><td><input type="submit" value="Download Certificate">
</table>
</form>
</p>
<?PHP
}

function download_cert($this_cert,$cer_ext) {
$config=$_SESSION['config'];
if (!isset($cer_ext)) 
  $cer_ext='FALSE';

if ($this_cert == "zzTHISzzCAzz" )
  {
  $my_x509_parse = openssl_x509_parse(file_get_contents($config['cacert']));
  $filename = $my_x509_parse['subject']['CN'].":".$my_x509_parse['subject']['OU'].":".$my_x509_parse['subject']['O'].":".$my_x509_parse['subject']['L'].":".$my_x509_parse['subject']['ST'].":".$my_x509_parse['subject']['C'];
  $download_certfile = $config['cacert'];
  $ext=".pem";
  //$application_type="application/x-x509-ca-cert";
  $application_type='application/octet-stream';
  }
else
  {
  $filename = substr($this_cert, 0,strrpos($this_cert,'.'));
  $ext=substr($this_cert, strrpos($this_cert,'.'));
  $download_certfile = base64_encode($filename);
  $download_certfile = $config['cert_path']. $download_certfile.$ext;
  $application_type='application/octet-stream';
  }

if ($cer_ext != 'FALSE') 
  $ext='.'.$cer_ext;
  
if (file_exists($download_certfile)) {
  $myCert = join("", file($download_certfile));
  download_header_code($filename.$ext,$myCert,$application_type);
  }
else {
  printHeader("Certificate Retrieval");
  print "<h1> $filename - X509 CA certificate not found</h1>\n";
  printFooter();
  }

}
// ==================================================================================================================
// =================== CONVERT CERT PKCS12 =====================================================================================
// ==================================================================================================================

function convert_cert_pkcs12_form(){
$config=$_SESSION['config'];
?>
<p>
<b>Convert a Certificate to PKCS#12</b><br/>
<?php
//Convert an existing certificate to PKCS#12 format, however, you can only do this if you have a private keyfile used to generate the CSR with.
$valid_files=0;
$dh = opendir($config['cert_path']) or die('Unable to open cert path');
while (($file = readdir($dh)) !== false) {
	if ( ($file !== ".htaccess") && is_file($config['cert_path'].$file)  && (substr($file, strrpos($file,'.')) != '.p12') && !is_file($config['cert_path'].substr($file, 0,strrpos($file,'.')).'.p12') )  {
	  if (is_file($config['key_path'].$file) ) {
	    $valid_files++;
	  }
	}
}
closedir($dh);

if ($valid_files) {
?>
<form action="index.php?menuoption=download_cert" method="post">
<input type="hidden" name="menuoption" value="convert_cert_pkcs12">
<table  style="width: 400px;">
<tr><td width=100>Private Key Passphrase:<td><input type="password" name="pkey_pass"/>
<tr><td width=100>PKCS#12 Passphrase:<td><input type="password" name="pkcs12_pass"/>
<tr><td width=100>Name:<td><select name="cert_name" rows="6">
<option value="">--- Select a certificate
<?php
$dh = opendir($config['cert_path']) or die('Unable to open cert path');
while (($file = readdir($dh)) !== false) {
	if ( ($file !== ".htaccess") && is_file($config['cert_path'].$file)  && (substr($file, strrpos($file,'.')) != '.p12') && !is_file($config['cert_path'].substr($file, 0,strrpos($file,'.')).'.p12') )  {
	  if (is_file($config['key_path'].$file)  ) {
		$name = base64_decode(substr($file, 0,strrpos($file,'.')));
		$ext = substr($file, strrpos($file,'.'));
		print "<option value=\"$name$ext\">$name$ext</option>\n";
	  }
	}
}
closedir($dh);
?>
</select></td></tr>
<tr><td><td><input type="submit" value="Convert Certificate">
</table>
</form>
<?php
}
else 
  print "<b> No Valid Certificates are available to convert.</b>\n";
?>
</p>
<?PHP
}

function convert_cert_pkcs12($this_cert_name,$my_pkey_pass,$my_pkcs12_pass){
$config=$_SESSION['config'];
$this_filename=substr($this_cert_name, 0,strrpos($this_cert_name,'.'));
$name = base64_encode(substr($this_cert_name, 0,strrpos($this_cert_name,'.')));
$ext = substr($this_cert_name, strrpos($this_cert_name,'.'));
$my_base64_certfile=$name.$ext;
$my_key_filename=$config['key_path'].$name.$ext;

print "<b>Loading key...</b><br/>";
$fp = fopen($my_key_filename, "r") or die('Fatal: Error opening Private Key');
$my_privkey_x509 = fread($fp, filesize($my_key_filename)) or die('Fatal: Error reading Private Key');
fclose($fp) or die('Fatal: Error closing Private Key');
print "Done<br/><br/>\n";

print "<b>Decoding Private key...</b><br/>";
$my_privkey = openssl_pkey_get_private($my_privkey_x509, $my_pkey_pass) or die('Fatal: Error decoding Private Key. Passphrase Incorrect');
print "Done<br/><br/>\n";

print "<b>Loading Certificate...</b><br/>";
$fp = fopen($config['cert_path'].$my_base64_certfile, "r") or die('Fatal: Error opening Certificate');
$my_cert = fread($fp, filesize($config['cert_path'].$my_base64_certfile)) or die('Fatal: Error reading Certificate');
fclose($fp) or die('Fatal: Error closing Certificate');
print "Done<br/><br/>\n";

$my_pkcs12_filename=$config['cert_path'].$name.'.p12';
$my_key_filename=$config['key_path'].$name.'.p12';
print "<b>Convert Certificate to PKCS#12...</b><br>";
openssl_pkcs12_export_to_file($my_cert,$my_pkcs12_filename,$my_privkey,$my_pkcs12_pass) or die('Fatal: Error converting Certificate to PKCS#12 '.$my_pkcs12_filename);
print "Done\n<br>\n";

print "<b>Download PKCS#12 Certificate:</b>\n<br>\n<br>\n";

?>
<form action="index.php" method="post">
<input type="hidden" name="menuoption" value="download_cert">
<input type="hidden" name="cert_name" value="<?PHP print $this_filename.'.p12';?>">
<input type="submit" value="Download PKCS#12 Certificate">
</form>
<BR><BR>
<?PHP
}
// ==================================================================================================================
// =================== REVOKE CERT =====================================================================================
// ==================================================================================================================

function revoke_cert_form(){
$config=$_SESSION['config'];
?>
<p>
<b>Revoke a Certificate</b><br/>
<form action="index.php" method="post">
<input type="hidden" name="menuoption" value="revoke_cert">
<table  style="width: 400px;">
<tr><td width=100>CA Passphrase:<td><input type="password" name="pass"/>
<tr><td width=100>Name:<td><select name="cert_serial">
<option value="">--- Select a certificate
<?php
$config=$_SESSION['config'];
$my_index_handle = fopen($config['index'], "r") or die('Unable to open Index file for reading');
$pattern = '/(\D)\t(\d+[Z])\t(\d+[Z])?\t([a-z0-9]+)\t(\D+)\t(.+)/'; 
while (!feof($my_index_handle)) {
   $this_line = rtrim(fgets($my_index_handle));
   if (preg_match($pattern,$this_line,$matches))
     if ($matches[1] == 'V') {
       print "<option value=\"".$matches[4]."\">".$matches[4]."<BR>".$matches[6]."</option>\n";
       }
}
fclose($my_index_handle);
?>
</select></td></tr>
<tr><td><td><input type="submit" value="Revoke Certificate">
</table>
</form>
</p>
<?PHP
}

function revoke_cert($my_serial,$my_passPhrase) {
$config=$_SESSION['config'];
print "<BR><b>Loading CA key...</b><br/>";
flush();
$fp = fopen($config['cakey'], "r") or die('Fatal: Unable to open CA Private Key: ' . $keyfile);
$myKey = fread($fp, filesize($config['cakey'])) or die('Fatal: Error whilst reading the CA Private Key: ' . $keyfile);
fclose($fp) or die('Fatal: Unable to close CA Key ');
print "Done<br/><br/>\n";

print "<b>Decoding CA key...</b><br/>";
flush();
if ( $privkey = openssl_pkey_get_private($myKey, $my_passPhrase) or die ('Error with passphrase for CA Key.') ) {
  print "Done\n<br>Passphrase correct<br/>\n";
  }
print "Revoking ".$my_serial."<BR>\n";
$pattern = '/(\D)\t(\d+[Z])\t(\d+[Z])?\t([a-z0-9]+)\t(\D+)\t(.+)/';
$my_index_handle = fopen($config['index'], "r") or die('Unable to open Index file for reading');
while (!feof($my_index_handle)) {
   $this_line = rtrim(fgets($my_index_handle));
   if (preg_match($pattern,$this_line,$matches))
     if ( ($matches[1] == 'V') && ($matches[4] == $my_serial ) )
       {
	   $my_valid_to=$matches[2];
	   $my_index_name=$matches[6];
	   print "Found ".$my_serial." ".$my_index_name."<BR>\n";
	   }
}
fclose($my_index_handle);
$orig_index_line="V\t".$my_valid_to."\t\t".$my_serial."\tunknown\t".$my_index_name;
$new_index_line="R\t".$my_valid_to."\t".gmDate("ymdHis\Z")."\t".$my_serial."\tunknown\t".$my_index_name;
$my_index = file_get_contents($config['index']) or die('Fatal: Unable to open Index File');
$my_index = str_replace($orig_index_line,$new_index_line,$my_index) or die('Unable to update Status of Cert in Index string'); 
file_put_contents($config['index'],$my_index);
//openssl ca -revoke $config['cert_path'].$filename -keyfile $config['cakey'] -cert $config['cacert'] -config $config['config']
//openssl ca -gencrl -keyfile $config['cakey'] -cert $config['cacert'] -out $config['cacrl'] -crldays 365 -config $config['config']
$cmd="openssl ca -gencrl -passin pass:".$my_passPhrase." -crldays 365"." -keyfile \"".$config['cakey']."\" -cert \"".$config['cacert']."\" -out \"".$config['cacrl']."\" -config \"".$config['config']."\"";
exec($cmd,$output_array,$retval);
if ($retval) {
 print $cmd."\n<BR>";
 print_r($output_array);
 print "\n<BR>";
 die('Fatal: Error processing GENCRL command');
 }
else
  print "CRL published to ".$config[cacrl]."\n<br>";
}



// ==================================================================================================================
// =================== VIEW Certificate =====================================================================================
// ==================================================================================================================


function view_cert_details_form(){
$config=$_SESSION['config'];
?>

<p>
<b>View a Certificate's details</b><br>
<?php
//Sign an existing CSR code form. Uses some PHP code first to ensure there are some valid CSRs available.
$valid_files=0;
$dh = opendir($config['cert_path']) or die('Unable to open certificate path');
while (($file = readdir($dh)) !== false) {
	if ( ($file !== ".htaccess") && is_file($config['cert_path'].$file) )  {
	  if (is_file($config['cert_path'].$file) ) {
	    $valid_files++;
	  }
	}
}
closedir($dh);

if ($valid_files) {
?>
<form action="index.php" method="post">
<input type="hidden" name="menuoption" value="view_cert_details"/>
<table  style="width: 400px;">
<tr><td>Name:<td><select name="cert_name" rows="6">
<option value="">--- Select a Certificate
<?php
print "<option value=\"zzTHISzzCAzz\">This CA Certificate</option>\n";
$dh = opendir($config['cert_path']) or die('Unable to open ' . $config['cert_path']);
while (($file = readdir($dh)) !== false) {
	if ( ($file !== ".htaccess") && is_file($config['cert_path'].$file) )  {
		$name = base64_decode(substr($file, 0,strrpos($file,'.')));
		$ext = substr($file, strrpos($file,'.'));
		print "<option value=\"$name$ext\">$name$ext</option>\n";
	}
}
?>
</select></td></tr>
<tr><td><td><input type="submit" value="View Certificate">
</table>
</form>
<?php
}
else 
  print "<b> No Valid Certificates are available to view.</b>\n";
?>
</p>
<?PHP
}


function view_cert($my_certfile) {
$config=$_SESSION['config'];
?>
<h1>Viewing certificate request</h1>

<?php
print "<b>Loading Certificate from file...</b><br/>";
if ($my_certfile == "zzTHISzzCAzz" )
  {
  $my_cert = openssl_x509_parse(file_get_contents($config['cacert']));
  }
else
  {
  $name = base64_encode(substr($my_certfile, 0,strrpos($my_certfile,'.')));
  $ext = substr($my_certfile, strrpos($my_certfile,'.'));
  $my_base64_certfile=$name.$ext;
  $my_cert = openssl_x509_parse(file_get_contents($config['cert_path'].$my_base64_certfile));
  }

$my_details = $my_cert['subject'];
//print_r($my_cert);

print "Done<br/><br/>\n";
print "<BR><BR><BR>\n\n\n";
?>
<table  style="width:500px;" border=1>
<tr><th width=200>Common Name<BR>(eg www.golf.local)</th><td><?PHP print $my_details['CN'];?></td></tr>
<tr><th>Serial Number</th><td><?PHP print $my_cert['serialNumber'];?></td></tr>
<tr><th>Contact Email Address</th><td><?PHP print $my_details['emailAddress'];?></td></tr>
<tr><th>Organizational Unit Name</th><td><?PHP print $my_details['OU'];?></td></tr>
<tr><th>Organization Name</th><td><?PHP print $my_details['O'];?></td></tr>
<tr><th>City</th><td><?PHP print $my_details['L'];?></td></tr>
<tr><th>State</th><td><?PHP print $my_details['ST'];?></td></tr>
<tr><th>Country</th><td><?PHP print $my_details['C'];?></td></tr>
</table>
<?PHP
print "\n\n<br><br><b>Completed.</b><br/>";
}



?>