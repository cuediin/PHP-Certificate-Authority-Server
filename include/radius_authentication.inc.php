<?php
    //
    // $Id: radius_authentication.inc,v 1.3 2002/01/23 23:21:20 mavetju Exp $
    //
    // radius authentication v1.0 by Edwin Groothuis (hide@address.com)
    //
    // If you didn't get this file via http://www.mavetju.org, please
    // check for the availability of newer versions.
    //
    // See LICENSE for distribution issues. If this file isn't in
    // the distribution, please inform me about it.
    //
    // If you want to use this script, fill in the configuration in
    // radius_authentication.conf and call the function
    // RADIUS_AUTHENTICATION() with the username and password
    // provided by the user. If it returns a 2, the authentication
    // was successfull!

    // If you want to use this, make sure that you have raw sockets
    // enabled during compile-time: "./configure --enable-sockets".

    function init_radiusconfig(&$server,&$port,&$sharedsecret,&$suffix) {
	$file=fopen("radius_authentication.conf","r");
	if ($file==0) {
	    echo "Couldn't open radius_authentication.conf, exiting";
	    exit(0);
	}
	while (!feof($file)) {
	    $s=fgets($file,1024);
	    $s=chop($s);
	    if ($s[0]=="#") continue;
	    if (strlen($s)==0) continue;
	    if (preg_match("/^([a-zA-Z]+) (.*)$/",$s,$a)) {
		if ($a[1]=="port")   { $port=$a[2];continue; }
		if ($a[1]=="server") { $server=$a[2];continue; }
		if ($a[1]=="secret") { $sharedsecret=$a[2];continue; }
		if ($a[1]=="suffix") { 
		    $suffix=$a[2];
		    if ($suffix=="\"\"") { 
			$suffix="";
		    }
		    continue;
		}
	    }
	    echo "Unknown config-file option: $a[1] ($s)\n";
	    exit(0);
	}
	fclose($file);
    }

    function RADIUS_AUTHENTICATION($username,$password) {
	global $debug;
	global $SERVER_ADDR;
	$radiushost="";
	$sharedsecret="";
	$suffix="";

	init_radiusconfig(&$radiushost,&$radiusport,&$sharedsecret,&$suffix);

	// check your /etc/services. Some radius servers 
	// listen on port 1812, some on 1645.
	if ($radiusport==0)
	    $radiusport=getservbyname("radius","udp");

	$nasIP=explode(".",$SERVER_ADDR);
	$ip=gethostbyname($radiushost);

	// 17 is UDP, formerly known as PROTO_UDP
	$sock=socket_create(AF_INET,SOCK_DGRAM,17);
	$retval=socket_connect($sock,$ip,$radiusport);

	if (!preg_match("/@/",$username))
	    $username.=$suffix;

	if ($debug)
	    echo "<br>radius-port: $radiusport<br>radius-host: $radiushost<br>username: $username<br>suffix: $suffix<hr>\n";

	$RA=pack("CCCCCCCCCCCCCCCC",				// auth code
	    1+rand()%255, 1+rand()%255, 1+rand()%255, 1+rand()%255,
	    1+rand()%255, 1+rand()%255, 1+rand()%255, 1+rand()%255,
	    1+rand()%255, 1+rand()%255, 1+rand()%255, 1+rand()%255,
	    1+rand()%255, 1+rand()%255, 1+rand()%255, 1+rand()%255);

	$encryptedpassword=Encrypt($password,$sharedsecret,$RA);

	$length=4+				// header
		16+				// auth code
		6+				// service type
		2+strlen($username)+		// username
		2+strlen($encryptedpassword)+	// userpassword
		6+				// nasIP
		6;				// nasPort

	$thisidentifier=rand()%256;
	//          v   v v     v   v   v     v     v
	$data=pack("CCCCa*CCCCCCCCa*CCa*CCCCCCCCCCCC",
	    1,$thisidentifier,$length/256,$length%256,		// header
	    $RA,						// authcode
	    6,6,0,0,0,1,					// service type
	    1,2+strlen($username),$username,			// username
	    2,2+strlen($encryptedpassword),$encryptedpassword,	// userpassword
	    4,6,$nasIP[0],$nasIP[1],$nasIP[2],$nasIP[3],	// nasIP
	    5,3,0,0,0,0						// nasPort
	    );

	socket_write($sock,$data,$length);

	if ($debug)
	    echo "<br>writing $length bytes<hr>\n";

	//
	// Wait at most five seconds for the answer. Thanks to
	// Michael Long <hide@address.com> for his remark about this.
	//
	$set=socket_fd_alloc();
	socket_fd_zero($set);
	socket_fd_set($set,$sock);
	socket_select($set,null,null,5);
	if (!socket_fd_isset($set,$sock)) {
	    echo "No answer from radius server, aborting\n";
	    exit(0);
	}
	socket_fd_free($set);


	$readdata=socket_read($sock,1);
	socket_close($sock);

	return ord($readdata);
	// 2 -> Access-Accept
	// 3 -> Access-Reject
	// See RFC2138 for this.
    }

    function Encrypt($password,$key,$RA) {
	global $debug;

	$keyRA=$key.$RA;

	if ($debug)
	    echo "<br>key: $key<br>password: $password<hr>\n";

	$md5checksum=md5($keyRA);
	$output="";

	for ($i=0;$i<=15;$i++) {
	    if (2*$i>strlen($md5checksum)) $m=0; else $m=hexdec(substr($md5checksum,2*$i,2));
	    if ($i>strlen($keyRA)) $k=0; else $k=ord(substr($keyRA,$i,1));
	    if ($i>strlen($password)) $p=0; else $p=ord(substr($password,$i,1));
	    $c=$m^$p;
	    $output.=chr($c);
	}
	return $output;
    }
?>
