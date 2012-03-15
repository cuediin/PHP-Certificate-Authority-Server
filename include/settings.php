<?PHP
session_start();
$config['certstore_path']="NOT_DEFINED";
if (isset($_SESSION['config']) and isset($_SESSION['my_ca']) )
  $config['ca_path'] = $config['certstore_path'].$_SESSION['my_ca']."/";
else
  $config['ca_path'] = $config['certstore_path'].'not_defined';

$config['req_path']=$config['ca_path'].'req/';
$config['key_path']=$config['ca_path'].'keys/';
$config['cert_path']=$config['ca_path'].'certs/';
$config['crl_path']=$config['ca_path'].'crl/';
$config['ssh_pubkey_path']=$config['ca_path'].'sshpub/';
$config['csr_upload_path']=$config['ca_path'].'csr_upload/';
$config['newcert_path']=$config['ca_path'].'newcerts/';
$config['config'] = $config['ca_path']."openssl.conf";
$config['cacert'] = $config['ca_path'] . "cacert.pem";
$config['cakey'] = $config['ca_path'] . "cacert.key";
$config['cacrl'] = $config['crl_path'] . "crl.pem";
$config['index'] = $config['ca_path'] . "index.txt";
$config['serial'] = $config['ca_path'] . "serial";
$config['blank_dn']=array(
'CN'=>"Common Name",
'emailAddress'=>"Email Address",
'OU'=>"Organizational Unit",
'O'=>"Organization",
'L'=>"Locality/ City",
'ST'=>"State",
'C'=>"Country",
);
if (is_file($config['cacert']) ) {
  $data = openssl_x509_parse(file_get_contents($config['cacert']));
  if (isset($data['subject']['CN'])) {$config['common'] = $data['subject']['CN'];}
  if (isset($data['subject']['OU'])) {$config['orgunit'] = $data['subject']['OU'];}
  if (isset($data['subject']['O'])) {$config['orgName'] = $data['subject']['O'];}
  if (isset($data['subject']['emailAddress'])) {$config['contact'] = $data['subject']['emailAddress'];}
  if (isset($data['subject']['L'])) {$config['city'] = $data['subject']['L'];}
  if (isset($data['subject']['ST'])) {$config['state'] = $data['subject']['ST'];}
  if (isset($data['subject']['C'])) {$config['country'] = $data['subject']['C'];}
}
/*
array for passing arguements for php and php extensions is as follows:
array('config' => $config['config'],
        'encrypt_key' => true,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
        'digest_alg' => 'sha1',
        'x509_extensions' => 'v3_ca',
        'private_key_bits' => (int)$someVariable // ---> good
        );
*/

?>
