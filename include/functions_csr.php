<?PHP
// ==================================================================================================================
// =================== CREATE CSR =====================================================================================
// ==================================================================================================================

function createCSR_form(){
$config=$_SESSION['config'];
$my_x509_parse = openssl_x509_parse(file_get_contents($config['cacert']));
?>
<p>
<b>Create a new CSR</b><br/>
<form action="index.php" method="post">
<input type="hidden" name="menuoption" value="createCSR"/>
<table  style="width: 90%;">
<tr><th width=100>Common Name (eg www.golf.local)</th><td><input type="text" name="cert_dn[commonName]" value="ABC Widgets Certificate Authority" size="40"></td></tr>
<tr><th>Contact Email Address</th><td><input type="text" name="cert_dn[emailAddress]" value=<?PHP if (array_key_exists('emailAddress',$my_x509_parse['subject'])) print $my_x509_parse['subject']['emailAddress']; else print '""';?> size="30"></td></tr>
<tr><th>Organizational Unit Name</th><td><input type="text" name="cert_dn[organizationalUnitName]" value=<?PHP if (array_key_exists('OU',$my_x509_parse['subject'])) print $my_x509_parse['subject']['OU']; else print '""';?> size="30"></td></tr>
<tr><th>Organization Name</th><td><input type="text" name="cert_dn[organizationName]" value=<?PHP if (array_key_exists('O',$my_x509_parse['subject'])) print $my_x509_parse['subject']['O']; else print '""';?> size="25"></td></tr>
<tr><th>City</th><td><input type="text" name="cert_dn[localityName]" value=<?PHP if (array_key_exists('L',$my_x509_parse['subject'])) print $my_x509_parse['subject']['L']; else print '""';?> size="25"></td></tr>
<tr><th>State</th><td><input type="text" name="cert_dn[stateOrProvinceName]" value=<?PHP if (array_key_exists('ST',$my_x509_parse['subject'])) print $my_x509_parse['subject']['ST']; else print '""';?>  size="25"></td></tr>
<tr><th>Country</th><td><input type="text" name="cert_dn[countryName]" value=<?PHP if (array_key_exists('C',$my_x509_parse['subject'])) print $my_x509_parse['subject']['C']; else print '""';?>  size="2"></td></tr>
<tr><th>Key Size</th><td><input type="radio" name="cert_dn[keySize]" value="1024" /> 1024bits <input type="radio" name="cert_dn[keySize]" value="2048bits" /> 2048bits<input type="radio" name="cert_dn[keySize]" value="4096bits" checked /> 4096bits</td></tr>
<tr><th>Device Type</th><td><input type="radio" name="device_type" value="client_cert" /> Client <input type="radio" name="device_type" value="server_cert" checked /> Server<input type="radio" name="device_type" value="msdc_cert"/> Microsoft Domain Controller<input type="radio" name="device_type" value="subca_cert" /> Sub_CA <input type="radio" name="device_type" value="8021x_client_cert" /> 802.1x Client<input type="radio" name="device_type" value="8021x_server_cert" /> 802.1x Server</td></tr>
<tr><th>Certificate Passphrase</th><td><input type="password" name="passphrase"/></td></tr>
<tr><td><td><input type="submit" value="Create CSR"/>
</table>
</form> 
</p>
<?PHP
}

function create_csr($my_cert_dn,$my_keysize,$my_passphrase,$my_device_type) {
$config=$_SESSION['config'];
$cert_dn=array();

print "<h1>Creating Certificate Key</h1>";
print "PASSWORD:".$my_passphrase."<BR>";
if (get_magic_quotes_gpc()) {
	while (list($key, $val) = each($cert_dn)) {
		$cert_dn[$key] = stripslashes($val);
	}
}

while (list($key, $val) = each($my_cert_dn)) {
		if ( array_key_exists($key,$my_cert_dn) )
		  if (strlen($my_cert_dn[$key]) > 0)
		    $cert_dn[$key]=$my_cert_dn[$key];
		 }

$filestr = $cert_dn['commonName'].":".$cert_dn['organizationalUnitName'].":".$cert_dn['organizationName'].":".$cert_dn['localityName'].":".$cert_dn['stateOrProvinceName'].":".$cert_dn['countryName'];
$filename=base64_encode($filestr);
print $filestr;
if ($my_device_type=='ca_cert') {
  $client_keyFile = $config['cakey'];
  $client_reqFile = $config['req_path'].$filename.".pem";  
}
else {
  $client_keyFile = $config['key_path'].$filename.".pem";
  $client_reqFile = $config['req_path'].$filename.".pem";
}
	
print "<h1>Creating Client CSR and Client Key</h1>";

print "<b>Checking your DN (Distinguished Name)...</b><br/>";
print "<pre>DN = ".var_export($cert_dn,1)."</pre>";
print "<b>Generating new key...</b><br/>";
print $my_keysize;
//$my_new_config=array('config'=>$config['config'],'private_key_bits'=>$config['private_key_bits'],'x509_extensions'=>$config['x509_extensions']);
$my_new_config=array('config'=>$config['config'],'private_key_bits'=>(int)$my_keysize);
//print_r($my_new_config);
//,'private_key_bits'=>$config['private_key_bits'],'x509_extensions'=>$config['x509_extensions']);
$privkey = openssl_pkey_new($my_new_config) or die('Fatal: Error creating Certificate Key');
print "Done<br/><br/>\n";

if ($my_device_type=='ca_cert') {
print "<b>Exporting encoded private key to CA Key file...</b><br/>";
}
else {
print "<b>Exporting encoded private key to file...</b><br/>";
}
openssl_pkey_export_to_file($privkey, $client_keyFile, $my_passphrase) or die ('Fatal: Error exporting Certificate Key to file');
print "Done<br/><br/>\n";

print "<b>Creating CSR...</b><br/>";
$my_csr = openssl_csr_new($cert_dn, $privkey,$config) or die('Fatal: Error creating CSR');
print "Done<br/><br/>\n";

print "<b>Exporting CSR to file...</b><br/>";
openssl_csr_export_to_file($my_csr, $client_reqFile) or die ('Fatal: Error exporting CSR to file');
print "Done<br/><br/>\n";

$my_details=openssl_csr_get_subject($my_csr);
$my_public_key_details=openssl_pkey_get_details(openssl_csr_get_public_key($my_csr));
?>
<table  style="width: 90%;">
<tr><th width=100>Common Name (eg www.golf.local)</th><td><?PHP print $my_details['CN'];?></td></tr>
<tr><th>Contact Email Address</th><td><?PHP print $my_details['emailAddress'];?></td></tr>
<tr><th>Organizational Unit Name</th><td><?PHP print $my_details['OU'];?></td></tr>
<tr><th>Organization Name</th><td><?PHP print $my_details['O'];?></td></tr>
<tr><th>City</th><td><?PHP print $my_details['L'];?></td></tr>
<tr><th>State</th><td><?PHP print $my_details['ST'];?></td></tr>
<tr><th>Country</th><td><?PHP print $my_details['C'];?></td></tr>
<tr><th>Key Size</th><td><?PHP print $my_public_key_details['bits'];?></td></tr>
</table>
<?PHP
print "<h1>Client CSR and Key - Generated successfully</h1>";
return $filestr.'.pem';
}
// ==================================================================================================================
// =================== DOWNLOAD CSR =====================================================================================
// ==================================================================================================================

function download_csr_form(){
$config=$_SESSION['config'];
?>
<p>
<b>Download a CSR</b><br/>
<form action="index.php" method="post">
<input type="hidden" name="menuoption" value="download_csr">
<table  style="width: 90%;">

<tr><th>Rename Extension</th><td><input type="radio" name="rename_ext" value="FALSE" checked />Do not Rename<br><input type="radio" name="rename_ext" value="cer" /> Rename to cer<br><input type="radio" name="rename_ext" value="csr" /> Rename to csr<br></td></tr>
<?PHP
/*

<input type="radio" name="cer_ext" value="FALSE" checked /> No <input type="radio" name="cer_ext" value="CER" /> Yes</td></tr>
<tr><th>Rename Extension to .pfx</th><td><input type="radio" name="pfx_ext" value="FALSE" checked /> No <input type="radio" name="cer_ext" value="PFX" /> Yes</td></tr>
*/
?>
<tr><td width=100>Name:<td><select name="cert_name" rows="6">
<option value="">--- Select a CSR
<?php
$dh = opendir($config['req_path']) or die('Unable to open ' . $config['req_path']);
while (($file = readdir($dh)) !== false) {
	if ( ($file !== ".htaccess") && is_file($config['req_path'].$file) )  {
		$name = base64_decode(substr($file, 0,strrpos($file,'.')));
		$ext = substr($file, strrpos($file,'.'));
		print "<option value=\"$name$ext\">$name$ext</option>\n";
	}
}
?>
</select></td></tr>
<tr><td><td><input type="submit" value="Download CSR">
</table>
</form>
</p>
<?PHP
}

function download_csr($this_cert,$cer_ext) {
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
  $download_certfile = $config['req_path']. $download_certfile.$ext;
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
// =================== IMPORT CSR =====================================================================================
// ==================================================================================================================


function import_CSR_form(){
$config=$_SESSION['config'];
?>
<p>
<b>Import a CSR</b><br/>
<form action="index.php" method="post">
<input type="hidden" name="menuoption" value="import_CSR"/>
<table  style="width: 90%;">
<tr><td colspan=2>Request:<br/>
<textarea name="request" cols="60" rows="6"></textarea><br/>
<tr><td><td><input type="submit" value="Import CSR"/>
</table>
</form>
</p>
<?PHP
}


function import_csr($my_csr) {
$config=$_SESSION['config'];

//CN:Email:OU:O:L:ST:GB 
$cert_dn=openssl_csr_get_subject($my_csr);
$my_csrfile="";
while (list($key, $val) = each($config['blank_dn'])) {
		if ( isset($cert_dn[$key]) )
		 $my_csrfile = $my_csrfile.$cert_dn[$key].":";
		 else
		 $my_csrfile = $my_csrfile." :";
 }
print_r($cert_dn);
$my_csrfile=substr($my_csrfile,0,strrpos($my_csrfile,':'));
$my_csrfile = $config['req_path'].base64_encode($my_csrfile).".pem";
print "<b>Saving your CSR...</b><br/>";
if ($fp = fopen($my_csrfile, 'w') or die('Fatal: Error open write $my_csrfile') ) {
	fputs($fp, $my_csr)  or die('Fatal: Error writing to $my_csrfile') ;
	fclose($fp)  or die('Fatal: Error closing write $my_csrfile') ;
}
print "CSR Filename:".$my_csrfile;
print "<b>Done";
}


// ==================================================================================================================
// =================== UPLOAD CSR =====================================================================================
// ==================================================================================================================



function upload_CSR_form(){
$config=$_SESSION['config'];
?>
<p>
<b>Upload a CSR</b><br/>
<form enctype="multipart/form-data" action="index.php" method="POST">
<input type="hidden" name="menuoption" value="upload_CSR"/>
<input type="hidden" name="MAX_FILE_SIZE" value="100000" />
<table  style="width: 90%;">
<tr><th>Choose a CSR to upload: </th></tr>
<tr><td><input name="uploadedfile" type="file" id="uploaded_csr" />
<tr><td><input type="submit" value="Upload CSR" />
</table>
</form>
</p>
<?PHP
}


function upload_csr($uploaded_file) {
$config=$_SESSION['config'];

if (!is_dir($config['csr_upload_path']))
  mkdir($config['csr_upload_path'],0777,true) or die('Fatal: Unable to create upload folder');

if ($uploaded_file["error"] > 0)
    die('Uploaded File Error: ' . $uploaded_file["error"]);
else
  if ($uploaded_file["size"] > 20000)
    die('Fatal: CSR file is too large.');
  else
    {
    $my_uploaded_file=$config['csr_upload_path'] . $uploaded_file["name"];
    if (file_exists($my_uploaded_file)) {
	  unlink($my_uploaded_file);
//      echo $uploaded_file["name"] . " already exists. ";
      }
      move_uploaded_file($uploaded_file["tmp_name"],$my_uploaded_file) or die('Fatal: Error moving uploaded file');
  	  print "<b>Reading Uploaded CSR file...</b><br/>";
      $fp = fopen($my_uploaded_file, "r") or die('Fatal: Error opening uploaded file');
      $my_csr = fread($fp, filesize($my_uploaded_file)) or die('Fatal: Error reading CSR file');
      fclose($fp) or die('Fatal: Error closing CSR file ');
      print "Done<br/><br/>\n";
      $cert_dn=openssl_csr_get_subject($my_csr) or die('Invalid CSR Format.');	  
	  print "<table  style=\"width: 90%;\">";
      print "<tr><th width=100>Certificate Details</th><td></td></tr>";
	  $my_index_name='';
	  while (list($key, $val) = each($config['blank_dn'])) {
		if ( isset($cert_dn[$key]) ) {
  		  $my_csrfile = $my_csrfile.$cert_dn[$key].":";
		  print "<tr><th>".$config['blank_dn'][$key]."</th><td>".$cert_dn[$key]."</td></tr>\n";
		  $my_index_name="/".$key."=".$cert_dn[$key].$my_index_name;
		  }
		 else
		 $my_csrfile = $my_csrfile." :";
		}
      $my_csrfile=substr($my_csrfile,0,strrpos($my_csrfile,':'));
	  print "</table>\n";
	  if (does_cert_exist($my_index_name))
	    die('Fatal: A certificate already exists for uploaded CSR.');
	  else {
	    $filename=base64_encode($my_csrfile).".pem";
	    $client_reqFile = $config['req_path'].$filename;
        print "<b>Saving your CSR...</b><br/>";
        if ($fp = fopen($client_reqFile, 'w') or die('Fatal: Error open write $my_csrfile') ) {
	      fputs($fp, $my_csr)  or die('Fatal: Error writing to $my_csrfile') ;
	      fclose($fp)  or die('Fatal: Error closing write $my_csrfile') ;
          }
        print "CSR file saved as $filename\n<br>\n";
        print "<b>Done";
		}
    }
	
}


// ==================================================================================================================
// =================== VIEW CSR =====================================================================================
// ==================================================================================================================


function view_csr_details_form(){
$config=$_SESSION['config'];
?>

<p>
<b>View a CSR's details</b><br>
<?php
//View an existing CSR code form. Uses some PHP code first to ensure there are some valid CSRs available.
$valid_files=0;
$dh = opendir($config['req_path']) or die('Unable to open  requests path');
while (($file = readdir($dh)) !== false) {
	if ( ($file !== ".htaccess") && is_file($config['req_path'].$file) )  {
	  if (!is_file($config['cert_path'].$file) ) {
	    $valid_files++;
	  }
	}
}
closedir($dh);

if ($valid_files) {
?>
<form action="index.php" method="post">
<input type="hidden" name="menuoption" value="view_csr_details"/>
<table  style="width: 90%;">
<tr><td>Name:<td><select name="csr_name" rows="6">
<option value="">--- Select a CSR
<?php
$dh = opendir($config['req_path']) or die('Unable to open  requests path');
while (($file = readdir($dh)) !== false) {
	if ( ($file !== ".htaccess") && is_file($config['req_path'].$file) )  {
	  if (!is_file($config['cert_path'].$file) ) {
		$name = base64_decode(substr($file, 0,strrpos($file,'.')));
		$ext = substr($file, strrpos($file,'.'));
		print "<option value=\"$name$ext\">$name$ext</option>\n";
	  }
	}
}
closedir($dh);
?>
</select></td></tr>
<tr><td><td><input type="submit" value="View CSR">
</table>
</form>
<?php
}
else 
  print "<b> No Valid CSRs are available to view.</b>\n";
?>
</p>
<?PHP
}


function view_csr($my_csrfile) {
$config=$_SESSION['config'];
$name = base64_encode(substr($my_csrfile, 0,strrpos($my_csrfile,'.')));
$ext = substr($my_csrfile, strrpos($my_csrfile,'.'));
$my_base64_csrfile=$name.$ext;
?>
<h1>Viewing certificate request</h1>

<?php
print "<b>Loading CSR from file...</b><br/>";
$fp = fopen($config['req_path'].$my_base64_csrfile, "r") or die('Fatal: Error opening CSR file'.$my_base64_csrfile);
$my_csr = fread($fp, filesize($config['req_path'].$my_base64_csrfile)) or die('Fatal: Error reading CSR file'.$my_base64_csrfile);
fclose($fp) or die('Fatal: Error closing CSR file '.$my_base64_csrfile);
print "Done<br/><br/>\n";
print $my_csr;
print "<BR><BR><BR>\n\n\n";
$my_details=openssl_csr_get_subject($my_csr);
$my_public_key_details=openssl_pkey_get_details(openssl_csr_get_public_key($my_csr));
?>
<table  style="width: 90%;">
<tr><th width=100>Common Name (eg www.golf.local)</th><td><?PHP print $my_details[CN];?></td></tr>
<tr><th>Contact Email Address</th><td><?PHP print $my_details[emailAddress];?></td></tr>
<tr><th>Organizational Unit Name</th><td><?PHP print $my_details[OU];?></td></tr>
<tr><th>Organization Name</th><td><?PHP print $my_details[O];?></td></tr>
<tr><th>City</th><td><?PHP print $my_details[L];?></td></tr>
<tr><th>State</th><td><?PHP print $my_details[ST];?></td></tr>
<tr><th>Country</th><td><?PHP print $my_details[C];?></td></tr>
<tr><th>Key Size</th><td><?PHP print $my_public_key_details[bits];?></td></tr>
</table>
<?PHP
print "\n\n<br><br><b>Completed.</b><br/>";
}


// ==================================================================================================================
// =================== SIGN CSR =====================================================================================
// ==================================================================================================================


function sign_csr_form(){
$config=$_SESSION['config'];
?>
<p>
<b>Sign a CSR - Generate a Certificate</b><br>
<?php
//Sign an existing CSR code form. Uses some PHP code first to ensure there are some valid CSRs available.
$valid_files=0;
$dh = opendir($config['req_path']) or die('Unable to open  requests path');
while (($file = readdir($dh)) !== false) {
	if ( ($file !== ".htaccess") && is_file($config['req_path'].$file) )  {
	  if (!is_file($config['cert_path'].$file) ) {
	    $valid_files++;
	  }
	}
}
closedir($dh);

if ($valid_files) {
?>
<form action="index.php" method="post">
<input type="hidden" name="menuoption" value="sign_csr"/>
<table  style="width: 90%;">
<tr><td width=100>CA Passphrase:<td><input type="password" name="pass"/>
<tr><td width=100>Number of days Certificate is to be valid for:<td><input type="text" name="days" value="730"/>
<tr><th>Device Type</th><td><input type="radio" name="device_type" value="client_cert" /> Client <input type="radio" name="device_type" value="server_cert" checked /> Server<input type="radio" name="device_type" value="msdc_cert"/> Microsoft Domain Controller<input type="radio" name="device_type" value="subca_cert" /> Sub_CA <input type="radio" name="device_type" value="8021x_client_cert" /> 802.1x Client<input type="radio" name="device_type" value="8021x_server_cert" /> 802.1x Server</td></tr>
<tr><td>Name:<td><select name="csr_name" rows="6">
<option value="">--- Select a CSR
<?php
$dh = opendir($config['req_path']) or die('Unable to open  requests path');
while (($file = readdir($dh)) !== false) {
	if ( ($file !== ".htaccess") && is_file($config['req_path'].$file) )  {
	  if (!is_file($config['cert_path'].$file) ) {
		$name = base64_decode(substr($file, 0,strrpos($file,'.')));
		$ext = substr($file, strrpos($file,'.'));
		print "<option value=\"$name$ext\">$name$ext</option>\n";
	  }
	}
}
closedir($dh);
?>
</select></td></tr>
<tr><td><td><input type="submit" value="Sign CSR">
</table>
</form>
<?php
}
else 
  print "<b> No Valid CSRs are available to sign.</b>\n";
?>
</p>
<?PHP
}


function sign_csr($passPhrase,$my_csrfile,$my_days,$my_device_type) {
$config=$_SESSION['config'];
$name = base64_encode(substr($my_csrfile, 0,strrpos($my_csrfile,'.')));
$ext = substr($my_csrfile, strrpos($my_csrfile,'.'));
$my_base64_csrfile=$name.$ext;
?>
<h1>Signing certificate request</h1>

<p>
<?PHP print "We will sign the requested CSR with this CA's key.";?>
</p>

<p>
Now signing certificate... Please wait...
</p>
<?php
print "<b>Loading CA key...</b><br/>";
$fp = fopen($config['cakey'], "r") or die('Fatal: Error opening CA Key'.$config['cakey']);
$my_key = fread($fp, filesize($config['cakey'])) or die('Fatal: Error reading CA Key'.$config['cakey']);
fclose($fp) or die('Fatal: Error closing CA Key'.$config['cakey']);
print "Done<br/><br/>\n";

print "<b>Decoding CA key...</b><br/>";
$my_ca_privkey = openssl_pkey_get_private($my_key, $passPhrase) or die('Fatal: Error decoding CA Key. Passphrase Incorrect');
print "Done<br/><br/>\n";

if (!($my_device_type=='ca_cert')) {
  print "<b>Loading CA Certificate...</b><br/>";
  $fp = fopen($config['cacert'], "r") or die('Fatal: Error opening CA Certificate'.$config['cacert']);
  $my_ca_cert = fread($fp, filesize($config['cacert'])) or die('Fatal: Error reading CA Certificate'.$config['cacert']);
  fclose($fp) or die('Fatal: Error closing CA Certificate'.$config['cacert']);
  print "Done<br/><br/>\n";
}
else 
  $my_ca_cert = NULL;
  
print "<b>Loading CSR from file...</b><br/>";
$fp = fopen($config['req_path'].$my_base64_csrfile, "r") or die('Fatal: Error opening CSR file'.$my_base64_csrfile);
$my_csr = fread($fp, filesize($config['req_path'].$my_base64_csrfile)) or die('Fatal: Error reading CSR file'.$my_base64_csrfile);
fclose($fp) or die('Fatal: Error closing CSR file '.$my_base64_csrfile);
print "Done<br/><br/>\n";

if ($my_device_type=='ca_cert') {
  print "<b>Deleting CSR file from Cert Store...</b><br/>";
  unlink($config['req_path'].$my_base64_csrfile) or die('Fatal: Error deleting CSR file'.$my_base64_csrfile);
  print "Done<br/><br/>\n";
}

print "<b>Signing CSR...</b><br/>";
$my_serial=sprintf("%04d",get_serial());
$my_scert = openssl_csr_sign($my_csr, $my_ca_cert, $my_ca_privkey, $my_days, $config, $my_serial) or die('Fatal: Error signing CSR.');
print "Done<br/><br/>\n";

print "<b>Exporting X509 Certificate...</b><br/>";
openssl_x509_export($my_scert, $my_x509_scert);
print "Done<br/><br/>\n";

$my_x509_parse=openssl_x509_parse($my_x509_scert);
$my_hex_serial=dechex($my_serial);
if (is_int((strlen($my_hex_serial)+1)/2))
 $my_hex_serial="0".$my_hex_serial;
//$index_line="V\t".$my_x509_parse['validTo']."\t\t".$my_serial."\tunknown\t".$my_x509_parse['name'];
$my_index_name="/C=".$my_x509_parse['subject']['C']."/ST=".$my_x509_parse['subject']['ST']."/L=".$my_x509_parse['subject']['L']."/O=".$my_x509_parse['subject']['O']."/OU=".$my_x509_parse['subject']['OU']."/CN=".$my_x509_parse['subject']['CN']."/emailAddress=".$my_x509_parse['subject']['emailAddress'];
$index_line="V\t".$my_x509_parse['validTo']."\t\t".$my_hex_serial."\tunknown\t".$my_index_name;

//Patern to match the index lines
$pattern = '/(\D)\t(\d+[Z])\t(\d+[Z])?\t(\d+)\t(\D+)\t(.+)/'; 

//Check to see if the certificate already exists in the Index file (ie. If someone clicks refresh on the webpage after creating a cert)
$my_valid_cert=does_cert_exist($my_index_name);

if ($my_valid_cert==0) {
  print "<b>Saving X509 Certificate...</b><br/>";
  if ($my_device_type=='ca_cert') 
    $my_scertfile = $config['cacert'];
  else
    $my_scertfile = $config['cert_path'].$my_base64_csrfile;
  if ($fp = fopen($my_scertfile, 'w') or die('Fatal: Error opening Signed Cert X509 file $my_scertfile') ) {
	fputs($fp, $my_x509_scert)  or die('Fatal: Error writing Signed Cert X509 file $my_scertfile') ;
	fclose($fp)  or die('Fatal: Error closing Signed Cert X509 file $my_scertfile') ;
  }
  if ( !($my_device_type=='ca_cert') ) {
    $my_scertfile = $config['newcert_path'].$my_serial.".pem";
    if ($fp = fopen($my_scertfile, 'w') or die('Fatal: Error opening Signed Cert X509 file $my_scertfile') ) {
	  fputs($fp, $my_x509_scert)  or die('Fatal: Error writing Signed Cert X509 file $my_scertfile') ;
	  fclose($fp)  or die('Fatal: Error closing Signed Cert X509 file $my_scertfile') ;
    }
    print "Done\n<br>\n";
    print "<b>Updating Index File...</b><br>";
    $my_index_handle = fopen($config['index'], "a") or die('Fatal: Unable to open Index file for appending');
    fwrite($my_index_handle,$index_line."\n") or die('Fatal: Unable to append data to end of Index file');
    fclose($my_index_handle) or die('Fatal: Unable to close Index file');
  }
  print "Done";
  print "<br><br>";
  print "<b>Download Certificate:</b>\n<br>\n<br>\n";

?>
<form action="index.php" method="post">
<input type="hidden" name="menuoption" value="download_cert">
<input type="hidden" name="cert_name" value="<?PHP if ($my_device_type=='ca_cert') print 'zzTHISzzCAzz'; else print $my_csrfile;?>">
<input type="submit" value="Download Signed Certificate">
</form>
<BR>
<form action="index.php" method="post">
<input type="hidden" name="menuoption" value="download_cert">
<input type="hidden" name="cert_name" value="<?PHP print 'zzTHISzzCAzz';?>">
<input type="submit" value="Download CA Trusted Root Certificate">
</form>
<BR><BR>
<?PHP
  print "<b>Your certificate:</b>\n<pre>$my_x509_scert</pre>\n";
?>
<h1>Successfully signed certificate request with CA key.</h1>
<?PHP

if ($my_device_type=='subca_cert') {
  print "Creating Sub-CA certificate Store...\n<br>";
  $my_cert_dn=openssl_csr_get_subject($my_csr) or die('Fatal: Getting Subject details from CSR');
  create_cert_store($config['certstore_path'], $my_cert_dn['CN']);
  print "Copying Sub CA Certificate over...\n<br>";
  copy($config['cert_path'].$my_base64_csrfile,$config['certstore_path'].$my_cert_dn['CN'].'/cacert.pem') or die('Fatal: Unable to copy sub-ca cacert.pem from Existing CA to Sub-CA Certificate Store');
  print "Done\n<br>";
  print "Copying Sub CA Certificate over...\n<br>";
  copy($config['key_path'].$my_base64_csrfile,$config['certstore_path'].$my_cert_dn['CN'].'/cacert.key') or die('Fatal: Unable to copy sub-ca cakey.pem from Existing CA to Sub-CA Certificate Store');
  print "Done\n<br>";
}

  }
else 
  print "<h1>".$my_x509_parse['name']." already exists in the Index file and is Valid.</h1>";

} //end of function sign_cert()

?>
