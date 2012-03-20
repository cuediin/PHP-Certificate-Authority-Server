<?PHP
session_start();
include("./include/settings.php");
include("./include/functions_setup.php");
include("./include/functions_layout.php");
include("./include/functions_csr.php");
include("./include/functions_cert.php");
include("./include/functions_key.php");
include("./include/functions_ca.php");
include("./include/functions_misc.php");
$_SESSION['cwd'] = dirname(__FILE__);
// Various IF statements to check current status of the PHP CA
if ( ($_REQUEST['menuoption']=='menu') && ($_SESSION['my_ca']=='create_ca_2' )) {
  $menuoption='create_ca_1';
  }
else
if ( ($config['certstore_path']=='NOT_DEFINED') && ($_POST['menuoption'] != 'setup_certstore') ) {
  $menuoption='setup_certstore_form';
}
else
if ( ($_POST['menuoption'] == 'setup_certstore') ) {
  $menuoption='setup_certstore';
}
else
// Checks for creating a CA
if ( ($_REQUEST['menuoption']=='menu') && isset($_POST['ca_name'])   ) {
  if ($_POST['ca_name']=='zzCREATEZZnewZZ') {
    $menuoption='create_ca_1';
  }
  // If not creating a CA set current CA to requested CA
  else {
    $menuoption='menu';
    $_SESSION['my_ca'] = $_POST['ca_name'];
  }
}
else
//Covers First Time Page accessed or No parameters for my_ca
if ( (!isset($_REQUEST['menuoption']) && !isset($_SESSION['my_ca']) ) || !isset($_SESSION['my_ca']) ) {
  $menuoption='switchca';
}
else
// Checks to see if there is an existing session CA configured, even if the menuoption parameter is empty
if (!isset($_REQUEST['menuoption']) && isset($_SESSION['my_ca']) ) {
  $menuoption='menu';
}
else
  // Covers off any valid options
if (isset($_REQUEST['menuoption']) ) {
  $menuoption=$_REQUEST['menuoption'];
  }
// if the session isnt configured for the config area, create a blank config array inside the session before importing the session variables into the
// config array
if (!isset($_SESSION['config'])) 
  $_SESSION['config']=array();

include("./include/settings.php");
if (isset($_POST['device_type']))
  $config['x509_extensions'] = $_POST['device_type'];
if (isset($_POST['cert_dn']['keySize']))
  $config['private_key_bits'] = $_POST['cert_dn']['keySize'];
$_SESSION['config']=$config;
		switch ($menuoption) {
			case "menu":
			    printHeader('CA Administration');
				printFooter();
		    break;

    		case "switchca":
				switch_ca();
				//printFooter();
			break;

			case "download_crl_form":
				printHeader('Download CRL');
				download_crl_form();
				printFooter();
			break;

			case "download_crl":
				download_crl($_POST['crl_name'],$_POST['rename_ext'],$_POST['rename_filename']);
			break;				

			case "download_csr_form":
				printHeader('Download CSR');
				download_csr_form();
				printFooter();
			break;

			case "download_csr":
				download_csr($_POST['cert_name'],$_POST['rename_ext']);
				break;				

			case "download_cert_form":
				printHeader('Download Certificate');
				download_cert_form();
				printFooter();
			break;
			
			case "download_cert":
				download_cert($_POST['cert_name'],$_POST['rename_ext']);
				break;			

			case "get_public_ssh_key_form":
				printHeader('Get Public SSH Key');
				get_public_ssh_key_form();
				printFooter();
			break;

			case "get_public_ssh_key":
				get_public_ssh_key($_POST['key_name'],$_POST['pass']);
				break;
				
			case "get_mod_private_form":
				printHeader('Get Private Key');
				get_mod_private_form();
				printFooter();
			break;

			case "get_mod_private":
				get_private_key($_POST['key_name'],$_POST['pass'],$_POST['strip_passphrase'],$_POST['puttygen'],$_POST['rename_ext']);
			break;
							
			case "view_csr_details_form":
				printHeader('View CSR Details');
				view_csr_details_form();
				printFooter();
			break;

			case "view_csr_details":
				printHeader('View CSR Details');
				view_csr($_POST['csr_name']);
				printFooter();
			break;

			case "check_key_passphrase_form":
				printHeader('Check CA Passphrase');
				check_key_passphrase_form();
				printFooter();
			break;

			case "check_key_passphrase":
				printHeader('Check CA Passphrase');
				check_key_passphrase($_POST['pass'],$_POST['key_name']);
				printFooter();
			break;
			
			case "revoke_cert_form":
			  printHeader('Revoke a Certificate');
			  revoke_cert_form();
			break;

			case "revoke_cert":
			  printHeader('Revoke a Certificate');
			  revoke_cert($_POST['cert_serial'],$_POST['pass']);
			break;

			case "convert_cert_pkcs12_form":
				printHeader('Convert Certificate to PKCS#12');
				convert_cert_pkcs12_form();
				printFooter();
			break;

			case "convert_cert_pkcs12":
				printHeader('Convert Certificate to PKCS#12');
				convert_cert_pkcs12($_POST['cert_name'],$_POST['pkey_pass'],$_POST['pkcs12_pass']);
				printFooter();
			break;

            case "createCSR_form":
                printHeader('Creating the CSR');
				createCSR_form();
                printFooter();
            break;

            case "createCSR":
                printHeader('Creating the CSR');
                create_csr($_POST['cert_dn'],$_POST['cert_dn']['keySize'],$_POST['passphrase'],$_POST['device_type']);
                printFooter();
            break;

            case "import_CSR_form":
                printHeader('Import a CSR');
                import_csr_form();
                printFooter();
            break;

            case "import_CSR":
                printHeader('Import a CSR');
                import_csr($_POST['request']);
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
				printHeader('Signing CSR');
				sign_csr_form();
				printFooter();
			break;

			case "sign_csr":
				printHeader('Signing CSR');
				sign_csr($_POST['pass'],$_POST['csr_name'],$_POST['days'],$_POST['device_type']);
				printFooter();
			break;

			case "setup_certstore_form":
			printHeader('Setup CA Certificate Store');
			setup_certstore_form();
			break;
			
			case "setup_certstore":
			printHeader('Setup CA Certificate Store');
			setup_certstore($_POST['certstore_path']);
			break;			
			
			case "create_ca_1":
			  printHeader('Creating new Root CA - Part 1');
			  create_new_CA_get_options();
			break;

			case "create_ca_2":
			  $_SESSION['my_ca'] = $_POST['cert_dn']['commonName'];
			  include("./include/settings.php");
			  $_SESSION['config']=$config;
			  printHeader('Creating new Root CA - Part 2');
			  create_ca_post($config['certstore_path'], $_POST['device_type'],$_POST['cert_dn'],$_POST['passphrase']);  
			break;
			
			default:
				printHeader("Unknown area");
				print "Unknown area: " . htmlspecialchars($_REQUEST['area']);
				printFooter();
				break;
		}
?>
