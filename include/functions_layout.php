<?PHP

/*
http://onehackoranother.com/projects/jquery/droppy/
http://archive.plugins.jquery.com/project/droppy
http://docs.jquery.com/UI/Dialog
http://stackoverflow.com/a/1328731/5738
*/


function printHeader($my_title='PHP CA Server') {
print "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
print "<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\" xml:lang=\"en\">\n";
print "<head>\n";
print "<script type=\"text/javascript\" src=\"layout/jquery-1.6.4.min.js\"></script>\n";
print "<script type=\"text/javascript\" src=\"layout/jquery.droppy.js\"></script>\n";
print "<link rel=\"stylesheet\" href=\"layout/droppy.css\" type=\"text/css\">\n";
print "<title>".$my_title."</title>\n";
print "</head>\n";
display_menu();
}

function printFooter() {
print "</div> <!-- end .mainContent -->\n";
print "</div><!-- /page -->\n";
print "</body>\n";
print "</html>\n";
}

function display_menu() {
print "<body>\n";
print "<ul class='nav'>\n";
print "<li><a href=\"#\">CSR Functions</a> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;\n";
print "<ul>\n";
print "<li><a href=\"?menuoption=createCSR_form\">Create a CSR</a></li>\n";
print "<li><a href=\"?menuoption=import_CSR_form\">Import a CSR</a></li>\n";
print "<li><a href=\"?menuoption=upload_CSR_form\">Upload a CSR</a></li>\n";
print "<li><a href=\"?menuoption=download_csr_form\">Download a CSR</a></li>\n";
print "<li><a href=\"?menuoption=view_csr_details_form\">View a CSR's Details</a></li>\n";
print "<li><a href=\"?menuoption=sign_csr_form\">Sign a CSR</a></li>\n";
print "</ul>\n</li>\n";
print "<li><a href=\"#\">Certificate Functions</a>\n";
print "<ul>\n";
print "<li><a href=\"?menuoption=download_cert_form\">Download a Cert/ PKCS#12</a></li>\n";
print "<li><a href=\"?menuoption=revoke_cert_form\">Revoke a Certificate</a></li>\n";
print "<li><a href=\"?menuoption=convert_cert_pkcs12_form\">Convert a Certificate to PKCS#12</a></li>\n";
print "<li><a href=\"?menuoption=view_cert_details_form\">View a Certificate's Details</a></li>\n";
print "</ul>\n</li>\n";
print "<li><a href=\"#\">Key Functions</a>\n";
print "<ul>\n";
print "<li><a href=\"?menuoption=get_public_ssh_key_form\">Get Public SSH Key</a></li>\n";
print "<li><a href=\"?menuoption=get_mod_private_form\">Get Private Key</a></li>\n";
print "<li><a href=\"?menuoption=check_key_passphrase_form\">Check a private key's passphrase</a></li>\n";
print "</ul>\n</li>\n";
print "<li><a href=\"#\">CA Functions</a>\n";
print "<ul>\n";
print "<li><a href=\"?menuoption=switchca\">Switch to a different CA</a></li>\n";
print "<li><a href=\"?menuoption=download_crl_form\">Download CRL</a></li>\n";
print "<li><a href=\"?menuoption=delete_ca_form\">Delete CA</a></li>\n";
print "</ul>\n</li>\n";
print "</ul>\n";
print "<script type='text/javascript'>\n$(function() {\n$('.nav').droppy({trigger: 'hover'},{speed: 500});});\n</script>\n";

print "\n";

print "<div id=\"mainContent\">\n";
}


?>
