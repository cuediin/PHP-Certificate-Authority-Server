<?PHP


function pem2der($pem_data) {
   $begin = "CERTIFICATE-----";
   $end   = "-----END";
   $pem_data = substr($pem_data, strpos($pem_data, $begin)+strlen($begin));   
   $pem_data = substr($pem_data, 0, strpos($pem_data, $end));
   $der = base64_decode($pem_data);
   return $der;
}

function der2pem($der_data) {
   $pem = chunk_split(base64_encode($der_data), 64, "\n");
   $pem = "-----BEGIN CERTIFICATE-----\n".$pem."-----END CERTIFICATE-----\n";
   return $pem;
}

function does_cert_exist($my_name) {
$config=$_SESSION['config'];
$pattern = '/(\D)\t(\d+[Z])\t(\d+[Z])?\t(\d+)\t(\D+)\t(.+)/'; 
$my_valid_cert=0;
$my_index_handle = fopen($config['index'], "r") or die('Unable to open Index file for reading');
while (!feof($my_index_handle)) {
   $this_line = rtrim(fgets($my_index_handle));
   if (preg_match($pattern,$this_line,$matches))
     if ( ($matches[1] == 'V') && ($matches[6] == $my_name ) )
       $my_valid_cert=1;
}
fclose($my_index_handle);
return $my_valid_cert;
}


function checkError($result) {
	if (!$result) {
		while (($error = openssl_error_string()) !== false) {
			if ($error == "error:0E06D06C:configuration file routines:NCONF_get_string:no value") {
				if ($nokeyError++ == 0) {
					$errors .= "One or more configuration variables could not be found (possibly non-fatal)<br/>\n";
				}
			}
			else {
				$errorCount++;
				$errors .= "Error $errorCount: $error<br/>\n";
			}
		}
	}
	if ($errorCount or (!$result and $nokeyError)) {
		print "FATAL: An error occured in the script. Possibly due to a misconfiguration.<br/>\nThe following errors were reported during execution:<br/>\n$errors";
		exit();
	}
}


function download_header_code($my_filename,$my_file,$my_application_type) {
  header("Cache-Control: cache, must-revalidate");
  header("Pragma: public");
  header("Content-Type: ".$my_application_type);
  header("Content-Disposition: attachment; filename=\"".$my_filename."\"");
  print $my_file;
}




function sshEncodePublicKey($my_keyinfo)
{
    $buffer  = pack("N", 7) . "ssh-rsa" . 
               sshEncodeBuffer($my_keyinfo['rsa']['e']) . 
               sshEncodeBuffer($my_keyinfo['rsa']['n']);

    return "ssh-rsa " . base64_encode($buffer); 
}

function sshEncodeBuffer($buffer)
{
    $len = strlen($buffer);
    if (ord($buffer[0]) & 0x80) {
        $len++;
        $buffer = "\x00" . $buffer;
    }

    return pack("Na*", $len, $buffer);
}

	
function get_serial() {
$config=$_SESSION['config'];
	$fp = fopen($config['serial'], "r") or die('Unable to open read-only Serial Number file '.$config['serial']);
	list($serial) = fscanf($fp, "%d") or die('Unable to read contents of Serial Number file '.$config['serial']);
	fclose($fp) or die('Unable to close read-only Serial Number file '.$config['serial']);
	$fp = fopen($config['serial'], "w") or die('Unable to open write Serial Number file '.$config['serial']);
	fputs($fp, sprintf("%04d", $serial + 1) . chr(0) . chr(10) ) or die('Unable to write serial number to Serial Number file '.$config['serial']);
	fclose($fp)or die('Unable to close write Serial Number file '.$config['serial']);

	$fp = fopen($config['serial'].".old", "w") or die('Unable to open write Serial Number file '.$config['serial'].'.old');
	fputs($fp, sprintf("%04d", $serial) . chr(0) . chr(10) ) or die('Unable to write serial number to Serial Number file '.$config['serial'].'.old');
	fclose($fp)or die('Unable to close write Serial Number file '.$config['serial'].'.old');

	return $serial;
}




?>
