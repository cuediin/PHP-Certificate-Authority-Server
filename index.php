<?PHP
if (session_id() === '') {
    session_start();
}
include("./include/settings.php");
include("./include/functions_setup.php");
include("./include/functions_layout.php");
include("./include/functions_csr.php");
include("./include/functions_cert.php");
include("./include/functions_key.php");
include("./include/functions_ca.php");
include("./include/functions_show_summary.php");
include("./include/functions_misc.php");
$config=update_config();
$_SESSION['cwd'] = dirname(__FILE__);
$page_variables=array();
if (count($_POST) or count($_GET)) {
  $page_variables = array_merge($_POST,$_GET);
  }
if (!isset($page_variables['menuoption']))
    $page_variables['menuoption'] = FALSE;
if (!isset($page_variables['ca_name']))
    $page_variables['ca_name'] = FALSE;
if (!isset($page_variables['print_content_only']))
    $page_variables['print_content_only'] = FALSE;

// Various IF statements to check current status of the PHP CA
//Initial page when nothing is defined and we need to create the certificate store
if (get_KeyValue($config, 'certstore_path') == 'NOT_DEFINED' && get_KeyValue($page_variables, 'menuoption') != 'setup_certstore') {
  $page_variables['menuoption'] = 'setup_certstore_form';
  $menuoption='setup_certstore_form';
  }
elseif (get_KeyValue($page_variables, 'menuoption') =='switchca' && get_KeyValue($page_variables, 'ca_name') !== FALSE) {
  // Checks for creating a CA
  if ($page_variables['ca_name'] == 'zzCREATEZZnewZZ')
    $menuoption='create_ca_form';
  else {
    // If not creating a CA set current CA to requested CA
    $menuoption = 'switchca';
    $_SESSION['my_ca'] = $page_variables['ca_name'];
	$config=update_config();
    }
  }
elseif ((get_KeyValue($page_variables, 'menuoption') === FALSE && !isset($_SESSION['my_ca'])) || 
        (!isset($_SESSION['my_ca']) && get_KeyValue($page_variables, 'menuoption') != 'setup_certstore' 
		&& get_KeyValue($page_variables, 'menuoption') != 'create_ca_form' 
		&& get_KeyValue($page_variables, 'menuoption') != 'delete_ca_form' 
		&& get_KeyValue($page_variables, 'menuoption') != 'switchca' 
		&& get_KeyValue($page_variables, 'menuoption') != 'delete_ca') ) {
  //Covers First Time Page accessed or No parameters for my_ca
  $menuoption = 'switchca_form';
  }
elseif (get_KeyValue($page_variables, 'menuoption') === FALSE && isset($_SESSION['my_ca']) ) {
  // Checks to see if there is an existing session CA configured, even if the menuoption parameter is empty
  $menuoption = 'menu';
  }
elseif (get_KeyValue($page_variables, 'menuoption') !== FALSE) {
  // Covers off any other valid options
  $menuoption=$page_variables['menuoption'];
  }
  
// if the session isnt configured for the config area, create a blank config array inside the session before importing the session variables into the
// config array
if (!isset($_SESSION['config'])) {
  $_SESSION['config']=array();
  }

if (isset($page_variables['device_type'])) {
  $config['x509_extensions'] = $page_variables['device_type'];
  }
if (isset($page_variables['cert_dn']['keySize'])) {
  $config['private_key_bits'] = $page_variables['cert_dn']['keySize'];
  }

// =================================================================================================================================================================
// =================================================================================================================================================================

$_SESSION['config']=$config;

switch ($menuoption) {
    case "menu":
        printHeader('CA Administration');
        printFooter();
    break;

    case "switchca_form":
        printHeader("Switch CA",FALSE);
        switch_ca_form();
        printFooter(FALSE);
    break;

    case "switchca":
        printHeader("Switch CA");
		$_SESSION['config']=array();
		$_SESSION['my_ca'] = $page_variables['ca_name'];
		$_SESSION['config']=update_config();
		show_summary();
        printFooter();
    break;

    case "download_crl_form":
        printHeader('Download CRL');
        download_crl_form();
        printFooter();
    break;

    case "download_crl":
        download_crl($page_variables['crl_name'],$page_variables['rename_ext'],$page_variables['rename_filename']);
    break;				

    case "download_csr_form":
        printHeader('Download CSR');
        download_csr_form();
        printFooter();
    break;

    case "download_csr":
        download_csr($page_variables['csr_name'],$page_variables['rename_ext']);
    break;				

    case "download_cert_form":
        printHeader('Download Certificate');
        download_cert_form();
        printFooter();
    break;

    case "download_cert":
        download_cert($page_variables['cert_name'],$page_variables['rename_ext']);
    break;			

    case "get_public_ssh_key_form":
        printHeader('Get Public SSH Key');
        get_public_ssh_key_form();
        printFooter();
    break;

    case "get_public_ssh_key":
        get_public_ssh_key($page_variables['key_name'],$page_variables['pass']);
    break;

    case "get_mod_private_form":
        if ($page_variables['print_content_only'] == FALSE)
          printHeader('Get Private Key');
		if (isset($page_variables['key_name']))
          get_mod_private_form(array('key_name'=>$page_variables['key_name']));
		else
          get_mod_private_form();
        if ($page_variables['print_content_only'] == FALSE)
          printFooter();
    break;

    case "get_mod_private":
        get_private_key($page_variables['key_name'],$page_variables['pass'],$page_variables['strip_passphrase'],$page_variables['puttygen'],$page_variables['rename_ext']);
    break;

    case "view_csr_details_form":
        printHeader('View CSR Details');
        view_csr_details_form();
        printFooter();
    break;

    case "view_csr_details":
        if ($page_variables['print_content_only'] == FALSE)
          printHeader('View CSR Details');
        view_csr($page_variables['csr_name']);
        if ($page_variables['print_content_only'] == FALSE)
          printFooter();
    break;

    case "check_key_passphrase_form":
        if ($page_variables['print_content_only'] == FALSE)
          printHeader('Check CA Passphrase');
		if (isset($page_variables['key_name']))
          check_key_passphrase_form(array('key_name'=>$page_variables['key_name']));
		else
          check_key_passphrase_form();
        if ($page_variables['print_content_only'] == FALSE)
          printFooter();
    break;

    case "check_key_passphrase":
        printHeader('Check CA Passphrase');
        check_key_passphrase($page_variables['pass'],$page_variables['key_name']);
        printFooter();
    break;

    case "revoke_cert_form":
	    if ($page_variables['print_content_only'] == FALSE)
		  printHeader('Revoke a Certificate');
		if (isset($page_variables['cert_serial']))
          revoke_cert_form(array('cert_serial'=>$page_variables['cert_serial']));
		else
          revoke_cert_form();
        if ($page_variables['print_content_only'] == FALSE)
		  printFooter();
    break;

    case "revoke_cert":
        printHeader('Revoke a Certificate');
        revoke_cert($page_variables['cert_serial'],$page_variables['pass']);
        printFooter();
    break;

    case "convert_cert_pkcs12_form":
	    if ($page_variables['print_content_only'] == FALSE)
          printHeader('Convert Certificate to PKCS#12');
		if (isset($page_variables['cert_name']))
          convert_cert_pkcs12_form(array('cert_name'=>$page_variables['cert_name']));
		else
          convert_cert_pkcs12_form();
	    if ($page_variables['print_content_only'] == FALSE)
          printFooter();
    break;

    case "convert_cert_pkcs12":
        printHeader('Convert Certificate to PKCS#12');
        convert_cert_pkcs12($page_variables['cert_name'],$page_variables['pkey_pass'],$page_variables['pkcs12_pass']);
        printFooter();
    break;

    case "createCSR_form":
        printHeader('Creating the CSR');
        createCSR_form();
        printFooter();
    break;

    case "createCSR":
        printHeader('Creating the CSR');
        create_csr($page_variables['cert_dn'],$page_variables['cert_dn']['keySize'],$page_variables['passphrase'],$page_variables['device_type']);
        printFooter();
    break;

    case "import_CSR_form":
        printHeader('Import a CSR');
        import_csr_form();
        printFooter();
    break;

    case "import_CSR":
        printHeader('Import a CSR');
        import_csr($page_variables['request']);
        printFooter();
    break;

    case "upload_CSR_form":
        printHeader('Upload a CSR');
        upload_csr_form();
        printFooter();
    break;

    case "upload_CSR":
        printHeader('Upload a CSR');
        upload_csr($_FILES['uploadedfile']);
        printFooter();
    break;

    case "sign_csr_form":
        if ($page_variables['print_content_only']== FALSE) printHeader('Signing CSR');
		if (isset($page_variables['csr_name']))
          sign_csr_form(array('csr_name'=>$page_variables['csr_name']));
		else
          sign_csr_form();
        if ($page_variables['print_content_only']== FALSE) printFooter();
    break;

    case "sign_csr":
        printHeader('Signing CSR');
        sign_csr($page_variables['pass'],$page_variables['csr_name'],$page_variables['days'],$page_variables['device_type']);
        printFooter();
    break;

    case "setup_certstore_form":
        printHeader('Setup CA Certificate Store');
        setup_certstore_form();
    break;

    case "setup_certstore":
        printHeader('Setup CA Certificate Store');
        setup_certstore($page_variables['certstore_path']);
    break;			

    case "create_ca_form":
        printHeader('Creating new Root CA - Part 1');
        create_ca_form();
    break;

    case "create_ca":
        $_SESSION['my_ca'] = $page_variables['cert_dn']['commonName'];
        $config=update_config();
        $_SESSION['config']=$config;
        printHeader('Creating new Root CA - Part 2');
        create_ca($config['certstore_path'], $page_variables['device_type'],$page_variables['cert_dn'],$page_variables['passphrase']);  
    break;

	case "delete_ca_form":
		printHeader('Delete a CA');
		delete_ca_form();
	break;

	case "delete_ca":
		$delete_check['errors']=FALSE;
		$delete_check['valid_text']=TRUE;
  		$delete_check['valid_ca_name']=TRUE;
		if (!($page_variables['confirm_text'] === 'DELETEME')) {
    	  $delete_check['errors']=TRUE;
		  $delete_check['valid_text']=FALSE;
		  }
		if ($page_variables['ca_name'] === 'zzzDELETECAzzz') {
    	  $delete_check['errors']=TRUE;
		  $delete_check['valid_ca_name']=FALSE;
		  }
		if ($delete_check['errors']) {
		  delete_ca_form($delete_check);
		  exit();
		  }
 	    $config=update_config();
		$_SESSION['config']=$config;
		printHeader('Delete a CA');
		delete_ca($config['certstore_path'],$page_variables['ca_name']);  
	break;

    case "view_cert_details_form":
        printHeader('View Certificate Details');
        view_cert_details_form();
        printFooter();
    break;

    case "view_cert_details":
        if ($page_variables['print_content_only']== FALSE)
          printHeader('View Certificate Details');
        view_cert($page_variables['cert_name']);
        if ($page_variables['print_content_only']== FALSE)
          printFooter();	
    break;

    case "show_summary":
        printHeader('Show Certificate Authority Summary');
        show_summary();
        printFooter();
    break;

	default:
        printHeader("Unknown area");
        print "Unknown menuoption.";
        printFooter();
    break;
}

?>