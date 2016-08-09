<?PHP

function show_summary() {
$config=$_SESSION['config'];
flush();
$status_array = array();
$dh = opendir($config['req_path']) or die('Unable to open  requests path '.$config['req_path']);
while (($file = readdir($dh)) !== false) {
  if ( ($file !== ".htaccess") && is_file($config['req_path'].$file) )  {
	$filename = base64_decode(substr($file, 0,strrpos($file,'.')));
	$name = base64_decode(substr($file, 0,strrpos($file,'.')));
	$ext = substr($file, strrpos($file,'.'));

	if (preg_match("((.*):(.*):(.*):(.*):(.*):(.*):(.*))",$filename,$matches)==1)
      $this_dn=array("cn"=>trim($matches[1]),"emailAddress"=>trim($matches[2]),"ou"=>trim($matches[3]),"o"=>trim($matches[4]),"l"=>trim($matches[5]),"st"=>trim($matches[6]),"c"=>trim($matches[7]));
    else if (preg_match("((.*):(.*):(.*):(.*):(.*):(.*))",$filename,$matches)==1)
      $this_dn=array("cn"=>trim($matches[1]),"ou"=>trim($matches[2]),"o"=>trim($matches[3]),"l"=>trim($matches[4]),"st"=>trim($matches[5]),"c"=>trim($matches[6]));
	$status_array[$this_dn['cn']]=array('dn'=>$this_dn,'has_csr'=>FALSE,'has_cert'=>FALSE,'has_pkcs12'=>FALSE,'has_pkey'=>FALSE,'status'=>FALSE);
	$status_array[$this_dn['cn']]['has_csr']=TRUE;
	$status_array[$this_dn['cn']]['csr_filename']=$file;
	$status_array[$this_dn['cn']]['unique']=$filename;
	}
  }
closedir($dh);

$dh = opendir($config['cert_path']) or die('Unable to open ' . $config['cert_path']);
while (($file = readdir($dh)) !== false) {
  if ( ($file !== ".htaccess") && is_file($config['cert_path'].$file) )  {
	$filename = base64_decode(substr($file, 0,strrpos($file,'.')));
	$ext = substr($file, strrpos($file,'.'));
	if (preg_match("((.*):(.*):(.*):(.*):(.*):(.*):(.*))",$filename,$matches)==1)
      $this_dn=array("cn"=>trim($matches[1]),"emailAddress"=>trim($matches[2]),"ou"=>trim($matches[3]),"o"=>trim($matches[4]),"l"=>trim($matches[5]),"st"=>trim($matches[6]),"c"=>trim($matches[7]));
    else if (preg_match("((.*):(.*):(.*):(.*):(.*):(.*))",$filename,$matches)==1)
      $this_dn=array("cn"=>trim($matches[1]),"ou"=>trim($matches[2]),"o"=>trim($matches[3]),"l"=>trim($matches[4]),"st"=>trim($matches[5]),"c"=>trim($matches[6]));
	if (!isset($status_array[$this_dn['cn']]))
	  $status_array[$this_dn['cn']]=array('dn'=>$this_dn,'has_csr'=>FALSE,'has_cert'=>FALSE,'has_pkcs12'=>FALSE,'has_pkey'=>FALSE,'status'=>FALSE);	
	if ($ext==".pem") {$status_array[$this_dn['cn']]['has_cert']=TRUE; $status_array[$this_dn['cn']]['cert_filename']=$file;}
	if ($ext==".p12") {$status_array[$this_dn['cn']]['has_pkcs12']=TRUE; $status_array[$this_dn['cn']]['pk12_filename']=$file;}
	$status_array[$this_dn['cn']]['unique']=$filename;
	}
  }
closedir($dh);

$dh = opendir($config['key_path']) or die('Unable to open ' . $config['key_path']);
while (($file = readdir($dh)) !== false) {
  if ( ($file !== ".htaccess") && is_file($config['key_path'].$file) )  {
	$filename = base64_decode(substr($file, 0,strrpos($file,'.')));
	$ext = substr($file, strrpos($file,'.'));
	if (preg_match("((.*):(.*):(.*):(.*):(.*):(.*):(.*))",$filename,$matches)==1)
      $this_dn=array("cn"=>trim($matches[1]),"emailAddress"=>trim($matches[2]),"ou"=>trim($matches[3]),"o"=>trim($matches[4]),"l"=>trim($matches[5]),"st"=>trim($matches[6]),"c"=>trim($matches[7]));
    else if (preg_match("((.*):(.*):(.*):(.*):(.*):(.*))",$filename,$matches)==1)
      $this_dn=array("cn"=>trim($matches[1]),"ou"=>trim($matches[2]),"o"=>trim($matches[3]),"l"=>trim($matches[4]),"st"=>trim($matches[5]),"c"=>trim($matches[6]));
	if (!isset($status_array[$this_dn['cn']]))
	  $status_array[$this_dn['cn']]=array('dn'=>$this_dn,'has_csr'=>FALSE,'has_cert'=>FALSE,'has_pkcs12'=>FALSE,'has_pkey'=>FALSE,'status'=>FALSE);
	$status_array[$this_dn['cn']]['has_pkey']=TRUE;
	$status_array[$this_dn['cn']]['pkey_filename']=$file;
	$status_array[$this_dn['cn']]['unique']=$filename;
	}
  }
closedir($dh);

$pattern = '/(\D)\t(\d+[Z])\t(\d+[Z])?\t([a-z0-9]+)\t(\D+)\t(.+)/';
$my_index_handle = fopen($config['index'], "r") or die('Unable to open Index file for reading');
while (!feof($my_index_handle)) {
  $this_line = rtrim(fgets($my_index_handle));
  $my_serial_number_real = -99;
  if (preg_match($pattern,$this_line,$matches)) {
	 $this_status=$matches[1];
	 $my_valid_to=$matches[2];
	 $my_revoke_date=$matches[3];
	 $my_serial_number_real=$matches[4];
	 $my_serial_number=base_convert($matches[4],16,10);
	 $my_index_name=$matches[6];
     $this_pattern2 = "(/C=(.*)/ST=(.*)/L=(.*)/O=(.*)/OU=(.*)/CN=(.*)/emailAddress=(.*))";
     preg_match($this_pattern2,$my_index_name,$matches);
	 if (preg_match($this_pattern2,$my_index_name,$matches)==1) {
	   $this_index_name=$matches[6].":".$matches[7].":".$matches[5].":".$matches[4].":".$matches[3].":".$matches[2].":".$matches[1];
	   $this_dn['cn']=$matches[6];
	   $this_dn['c']=$matches[1];
	   $this_dn['o']=$matches[4];
	   $this_dn['ou']=$matches[5];
	   $this_dn['l']=$matches[3];
	   $this_dn['st']=$matches[2];
	   $this_dn['emailAddress']=$matches[7];
	   $this_dn['serial_number']=$my_serial_number_real;
	   if (!isset($status_array[$this_dn['cn']]['unique']))
	     $status_array[$this_dn['cn']]['unique']=$this_dn['cn'].":".$this_dn['emailAddress'].":".$this_dn['ou'].":".$this_dn['o'].":".$this_dn['l'].":".$this_dn['st'].":".$this_dn['c'];
       if (!isset($status_array[$this_dn['cn']])) {
	     $status_array[$this_dn['cn']]=array('dn'=>$this_dn,'has_csr'=>FALSE,'has_cert'=>FALSE,'has_pkcs12'=>FALSE,'has_pkey'=>FALSE,'status'=>FALSE);
	     }
	   $status_array[$this_dn['cn']]['dn']=$this_dn;
	   $status_array[$this_dn['cn']]['status']=$this_status;
	   $status_array[$this_dn['cn']]['history'][] = array('status'=>$this_status,'valid_to'=>$my_valid_to,'revoke_date'=>$my_revoke_date,'serial_no'=>$my_serial_number);
	   }
     }
  }  
fclose($my_index_handle);
ksort($status_array);

$my_grey_title="rgb(170,170,170)";
$my_grey_body="rgb(215,215,215)";
/*
$my_ascii_yes="&#9745";
$my_ascii_no="&#9746";
*/
$my_ascii_yes="&#10004";
$my_ascii_no="&#10060";
print "<div class='contextMenu' id='all_menu'>
      <ul>
        <li id='no_actions'>No Actions Available</li>
        <li id='view_csr'>View CSR</li>
        <li id='download_csr'>Download CSR</li>
        <li id='sign_csr'>Sign CSR</li>
        <li id='view_cert'>View Certificate details</li>
        <li id='download_cert'>Download Cert</li>
        <li id='revoke_cert'>Revoke Cert</li>
        <li id='download_pkcs12'>Download PKCS12</li>
      </ul>
    </div>\n";
print "<TABLE width=100% border=1>\n";
print '<TR><TH>Common Name</TH><TH>Email Address</TH>';
print "<TH>Organisational unit</TH><TH>Organisation</TH><TH>Location</TH><TH>State</TH><TH>Country</TH>\n";
$this_coloured_width="5%";
print "<TH width=".$this_coloured_width." style=\"background-color:".$my_grey_title."\">Status</TH>";
print "<TH width=".$this_coloured_width." style=\"background-color:".$my_grey_title."\">Has<BR>CSR</TH>";
print "<TH width=".$this_coloured_width." style=\"background-color:".$my_grey_title."\">Has<BR>Certificate</TH>";
print "<TH width=".$this_coloured_width." style=\"background-color:".$my_grey_title."\">Has<BR>PKCS12</TH>";
print "<TH width=".$this_coloured_width." style=\"background-color:".$my_grey_title."\">Has<BR>Private Key</TH>";
print "</TR>\n";
$template_html = "";
foreach ($status_array as $filename=>$this_array) {
  $table_array=array();
  if ($this_array['status']=="R") {
    $table_array['status']['background']=$my_grey_body."\n";
	$table_array['status']['font_colour']="red\n";
	$table_array['status']['text']="Revoked\n";
	$table_array['status']['menu']="is_revoked";
	$table_array['status']['menu_id']=$this_array['unique'];
	$table_array['status']['font_weight']="bold";
	}
  elseif ($this_array['status']=="E") { 
    $table_array['status']['background']=$my_grey_body."\n";
	$table_array['status']['font_colour']="orange\n";
	$table_array['status']['text']="Expired\n";
	$table_array['status']['menu']="is_expired";
	$table_array['status']['menu_id']=$this_array['unique'];
	$table_array['status']['font_weight']="bold";
	}
  elseif ($this_array['status']=="V") {
    $table_array['status']['background']=$my_grey_body."\n";
    $table_array['status']['font_colour']="green\n";
    $table_array['status']['text']="Valid\n";
    $table_array['status']['menu']="is_valid";
    $table_array['status']['menu_id']=$this_array['unique'];
	$table_array['status']['font_weight']="bold";
    }
  else { 
    $table_array['status']['background']=$my_grey_body."\n";
    $table_array['status']['font_colour']="black\n";
    $table_array['status']['text']="Unknown\n";
    $table_array['status']['menu']="is_unknown";
    $table_array['status']['menu_id']=$this_array['unique'];
	$table_array['status']['font_weight']="normal";
    }
  
  if ($this_array['has_csr']==TRUE) {
    $table_array['csr']['background']=$my_grey_body."\n";
    $table_array['csr']['font_colour']="green\n";
    $table_array['csr']['text']=$my_ascii_yes;
    $table_array['csr']['menu']="has_csr";
    $table_array['csr']['menu_id']=$this_array['unique'];
	$this_array['has_csr_string']="TRUE";
	$table_array['csr']['font_weight']="bold";
	} 
  else { 
    $table_array['csr']['background']=$my_grey_body."\n";
    $table_array['csr']['font_colour']="red\n";
    $table_array['csr']['text']=$my_ascii_no;
    $table_array['csr']['menu']="has_not_csr";
    $table_array['csr']['menu_id']=$this_array['unique'];
	$this_array['has_csr_string']="FALSE";
	$table_array['csr']['font_weight']="bold";
	}

  if ($this_array['has_cert']==TRUE) {
    $table_array['cert']['background']=$my_grey_body."\n";
    $table_array['cert']['font_colour']="green\n";
    $table_array['cert']['text']=$my_ascii_yes;
    $table_array['cert']['menu']="has_cert";
    $table_array['cert']['menu_id']=$this_array['unique'];
	$this_array['has_cert_string']="TRUE";
	$table_array['cert']['font_weight']="bold";
	} 
  else { 
    $table_array['cert']['background']=$my_grey_body."\n";
    $table_array['cert']['font_colour']="red\n";
    $table_array['cert']['text']=$my_ascii_no;
    $table_array['cert']['menu']="has_not_cert";
    $table_array['cert']['menu_id']=$this_array['unique'];
	$this_array['has_cert_string']="FALSE";
	$table_array['cert']['font_weight']="bold";
	}
	
  if ($this_array['has_pkcs12']==TRUE) {
    $table_array['pkcs12']['background']=$my_grey_body."\n";
    $table_array['pkcs12']['font_colour']="green\n";
    $table_array['pkcs12']['text']=$my_ascii_yes;
    $table_array['pkcs12']['menu']="has_pkcs12";
    $table_array['pkcs12']['menu_id']=$this_array['unique'];
	$this_array['has_pkcs12_string']="TRUE";
	$table_array['pkcs12']['font_weight']="bold";
	} 
  else { 
    $table_array['pkcs12']['background']=$my_grey_body."\n";
    $table_array['pkcs12']['font_colour']="red\n";
    $table_array['pkcs12']['text']=$my_ascii_no;
    $table_array['pkcs12']['menu']="has_not_pkcs12";
    $table_array['pkcs12']['menu_id']=$this_array['unique'];
	$this_array['has_pkcs12_string']="FALSE";
	$table_array['pkcs12']['font_weight']="bold";
	}
  
  if ($this_array['has_pkey']==TRUE) {
    $table_array['pkey']['background']=$my_grey_body."\n";
    $table_array['pkey']['font_colour']="green\n";
    $table_array['pkey']['text']=$my_ascii_yes;
    $table_array['pkey']['menu']="has_pkey";
    $table_array['pkey']['menu_id']=$this_array['unique'];
	$this_array['has_pkey_string']="TRUE";
	$table_array['pkey']['font_weight']="bold";
	} 
  else { 
    $table_array['pkey']['background']=$my_grey_body."\n";
    $table_array['pkey']['font_colour']="red\n";
    $table_array['pkey']['text']=$my_ascii_no;
    $table_array['pkey']['menu']="has_not_pkey";
    $table_array['pkey']['menu_id']=$this_array['unique'];
	$this_array['has_pkey_string']="FALSE";
	$table_array['pkey']['font_weight']="bold";
	}
    $this_menu = "::::::".$table_array['status']['menu'].":::".$table_array['csr']['menu'].":::".$table_array['cert']['menu'].":::".$table_array['pkcs12']['menu'].":::".$table_array['pkey']['menu'].":::";
  
  print "<TR class=\"all_menu_class\" id=\"".$this_array['unique']."\"";
  print " data-serial_no=\"".$this_array['dn']['serial_number']."\"";
  print " data-has_csr=\"".$this_array['has_csr_string']."\"";
  print " data-has_cert=\"".$this_array['has_cert_string']."\"";
  print " data-has_pkcs12=\"".$this_array['has_pkcs12_string']."\"";
  print " data-has_pkey=\"".$this_array['has_pkey_string']."\"";
  print ">\n";
  print "<TD>".$this_array['dn']['cn']."</TD>\n";
  print "<TD>".preg_replace('[\s+]','<BR>',$this_array['dn']['emailAddress'])."</TD>\n";
  print "<TD>".$this_array['dn']['ou']."</TD>\n";
  print "<TD>".$this_array['dn']['o']."</TD>\n";
  print "<TD>".$this_array['dn']['l']."</TD>\n";
  print "<TD>".$this_array['dn']['st']."</TD>\n";
  print "<TD>".$this_array['dn']['c']."</TD>\n";
  foreach ($table_array as $this_cell=>$this_cell_array) {
  print '<TD width=20px align=center style="font-weight:'.$this_cell_array['font_weight'].';background-color:'.$this_cell_array['background'].';"><font color='.$this_cell_array['font_colour'].'>'.$this_cell_array['text'].'</font></TD>';
  print "\n";
  }
  print "</TR>\n\n";
  }
print "</TABLE>\n";
print "</SPAN>\n";
print "<!-- Template. This whole data will be added directly to working form above -->\n";
print "<div id=\"view_cert_tpl\" style=\"display:none\">\n";
print "<div class=\"view_cert\">\n";
print "</div>\n";
print "</div>\n";
}

?>
