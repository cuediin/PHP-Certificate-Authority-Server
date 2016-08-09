<?PHP

/*
http://onehackoranother.com/projects/jquery/droppy/
http://archive.plugins.jquery.com/project/droppy
http://docs.jquery.com/UI/Dialog
http://stackoverflow.com/a/1328731/5738
*/


function printHeader($my_title='PHP CA Server',$SHOW_CA_NAME=TRUE) {
print "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
print "<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\" xml:lang=\"en\">\n";
print "<head>\n";
print "<script type=\"text/javascript\" src=\"layout/jquery-1.6.4.min.js\"></script>\n";
print "<script type=\"text/javascript\" src=\"layout/jquery.droppy.js\"></script>\n";
print "<script type='text/javascript' src='layout/jquery.contextmenu.r2.js'></script>\n";
print "<link rel=\"stylesheet\" href=\"layout/droppy.css\" type=\"text/css\">\n";
print "<title>".$my_title."</title>\n";
print "</head>\n";
display_menu();
if ($SHOW_CA_NAME==TRUE) print "<H2>".strtoupper($_SESSION['my_ca'])."</H2>\n";
}

function printFooter($SHOW_CA_NAME=TRUE) {
if ($SHOW_CA_NAME==TRUE) print "<H2>".strtoupper($_SESSION['my_ca'])."</H2>";
print "</div> <!-- end .mainContent -->\n";
print "</div><!-- /page -->\n";
print "</body>\n";
print "</html>\n";
}

function display_menu() {
print "<script type='text/javascript'>\n
$(function() {\n
$('tr.all_menu_class').contextMenu('all_menu', {
        menuStyle: {
          border: '2px solid #000',
		  width: '200px'
          },
        itemStyle: {
          fontFamily : 'verdana',
          backgroundColor : '#666',
          color: 'white',
          border: '2px solid #000',
          padding: '2px'
          },
        itemHoverStyle: {
          color: '#fff',
          backgroundColor: '#0f0',
          border: '2px solid #000',
          },
		  onShowMenu: function(e, menu) {
            if ( ($(e.target).parent().data('has_csr') == 'TRUE')  || ($(e.target).parent().data('has_cert') == 'TRUE')  || ($(e.target).parent().data('has_pkcs12') == 'TRUE')  ) $('#no_actions', menu).remove();
            if ($(e.target).parent().data('has_csr') == 'FALSE') $('#view_csr, #download_csr, #sign_csr', menu).remove();
            if ($(e.target).parent().data('has_cert') == 'FALSE') $('#view_cert, #download_cert, #revoke_cert', menu).remove();
            if ($(e.target).parent().data('has_pkcs12') == 'FALSE') $('#download_pkcs12', menu).remove();
            return menu;
        },
        bindings: {
          'view_csr': function(t) {
            $.post( 'index.php',{menuoption:'view_csr_details',csr_name:t.id+'.pem',print_content_only:'TRUE'},function(data){var win = window.open('','View CSR','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,top=50,left=50,width=800,height=600');win.document.write(data);});
          },
          'sign_csr': function(t) {
            $.post( 'index.php',{menuoption:'sign_csr_form',csr_name:t.id+'.pem',print_content_only:'TRUE'},function(data){var win = window.open('','Sign CSR','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,top=50,left=50,width=800,height=600');win.document.write(data);});
          },
          'download_csr': function(t) {
            var url = '/phpca/index.php?menuoption=download_csr&csr_name=' + t.id + '.pem&rename_ext=csr';
            window.location = url;
          },
          'download_pkcs12': function(t) {
            var url = '/phpca/index.php?menuoption=download_cert&cert_name=' + t.id + '.p12&rename_ext=pfx';
            window.location = url;
          },
          'view_cert': function(t) {
            $.post( 'index.php',{menuoption:'view_cert_details',cert_name:t.id+'.pem',print_content_only:'TRUE'},function(data){var win = window.open('','View Certificate','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,top=50,left=50,width=800,height=600');win.document.write(data);});
          },
          'revoke_cert': function(t) {
            $.post( 'index.php',{menuoption:'revoke_cert_form',cert_serial:$(t).data('serial_no'),cert_id:t.id,print_content_only:'TRUE'},function(data){var win = window.open('','Revoke Certificate','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,top=50,left=50,width=800,height=600');win.document.write(data);});
          },
          'check_key_passphrase': function(t) {
            var url = '/phpca/index.php?menuoption=check_key_passphrase_form&key_name=' + t.id + '.pem';
            window.location = url;
          }
        }
      });
	  
$('ul.nav').droppy({trigger: 'hover'},{speed: 500});\n
});\n
</script>\n";
print "<body>\n";
print "<ul class='nav'>\n";
print "<li><a href=\"#\">CSR Functions</a>\n";
print "<ul>\n";
print "<li><a href=\"index.php?menuoption=createCSR_form\">Create a CSR</a></li>\n";
print "<li><a href=\"index.php?menuoption=import_CSR_form\">Import a CSR</a></li>\n";
print "<li><a href=\"index.php?menuoption=upload_CSR_form\">Upload a CSR</a></li>\n";
print "<li><a href=\"index.php?menuoption=download_csr_form\">Download a CSR</a></li>\n";
print "<li><a href=\"index.php?menuoption=view_csr_details_form\">View a CSR's Details</a></li>\n";
print "<li><a href=\"index.php?menuoption=sign_csr_form\">Sign a CSR</a></li>\n";
print "</ul>\n</li>\n";
print "<li><a href=\"#\">Certificate Functions</a>\n";
print "<ul>\n";
print "<li><a href=\"index.php?menuoption=download_cert_form\">Download a Cert/ PKCS#12</a></li>\n";
print "<li><a href=\"index.php?menuoption=revoke_cert_form\">Revoke a Certificate</a></li>\n";
print "<li><a href=\"index.php?menuoption=convert_cert_pkcs12_form\">Convert a Certificate to PKCS#12</a></li>\n";
print "<li><a href=\"index.php?menuoption=view_cert_details_form\">View a Certificate's Details</a></li>\n";
print "</ul>\n</li>\n";
print "<li><a href=\"#\">Key Functions</a>\n";
print "<ul>\n";
print "<li><a href=\"index.php?menuoption=get_public_ssh_key_form\">Get Public SSH Key</a></li>\n";
print "<li><a href=\"index.php?menuoption=get_mod_private_form\">Get Private Key</a></li>\n";
print "<li><a href=\"index.php?menuoption=check_key_passphrase_form\">Check a private key's passphrase</a></li>\n";
print "</ul>\n</li>\n";
print "<li><a href=\"#\">CA Functions</a>\n";
print "<ul>\n";
print "<li><a href=\"index.php?menuoption=switchca_form\">Switch to a different CA</a></li>\n";
print "<li><a href=\"index.php?menuoption=download_crl_form\">Download CRL</a></li>\n";
print "<li><a href=\"index.php?menuoption=delete_ca_form\">Delete CA</a></li>\n";
print "</ul>\n</li>\n";
print "<li><a href=\"index.php?menuoption=show_summary\">Show Summary</a></li>\n";
print "</ul>\n";
print "<div id=\"mainContent\">\n";
}

/*

            var win = window.open();win.document.write(data);


            $.post( 'index.php',{menuoption:'download_cert',cert_name:t.id+'.pem',rename_ext:'cer'},function(data){\$('#mainContent').html(data);});

,
		onShowMenu: function(e, menu) {
		if ($(e.target).attr('id').indexOf(':::has_csr:::') !== -1) {
          $('#view_csr', menu).remove();
          }		
        return menu;
      }

,
		onShowMenu: function(e, menu) {
		if ($(e.target).attr('id').indexOf(':::has_csr:::') !== -1) {
          $('#view_csr', menu).remove();
          }
		if ($(e.target).attr('id').indexOf(':::has_cert:::') !== -1) {
          $('#view_cert', menu).remove();
          }
        return menu;
      }
	  
	  $('span.demo3').contextMenu('myMenu3', {
        onContextMenu: function(e) {
          if ($(e.target).attr('id') == 'dontShow') return false;
          else return true;
        },

        onShowMenu: function(e, menu) {
          if ($(e.target).attr('id') == 'showOne') {
            $('#item_2, #item_3', menu).remove();
          }
          return menu;
        }
      });
	  
	  
,
        menuStyle: {
          border: '2px solid #000'
        },
        bindings: {
          'view_csr': function(t) {
            $.post( 'index.php',{menuoption:'view_csr_details',csr_name:t.id+'.pem',printcontentonly:'TRUE'},function(data){\$('#mainContent').html(data);});
          },
          'view_pkcs12': function(t) {
            $.post( 'index.php',{menuoption:'view_cert_details',cert_name:t.id+'.p12',printcontentonly:'TRUE'},function(data){\$('#mainContent').html(data);});
          },
          'view_cert': function(t) {
            $.post( 'index.php',{menuoption:'view_cert_details',cert_name:t.id+'.pem',printcontentonly:'TRUE'},function(data){\$('#mainContent').html(data);});
          }
        }
		
		

*/

?>
