<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);

$debug = (isset($_COOKIE['u5debug']) && $_COOKIE['u5debug']) == 'yes' ? true : false;
$debug = (isset($_GET['u5debug'])) ? true : false;
require('../config.php');

/*
********************** SAML LOGOUT INTEGRATION ***************************3
*/

// As the SimpleSAMLphp component can be put anywhere, the path
// to it is taken from the configuration.
putenv('SIMPLESAMLPHP_CONFIG_DIR=' . $u5samlsspconfigdir);
require ('../simplesaml/_include.php');

// Initialize SimpleSAMLphp, the only parameter is the key of the SP
// resource as set in the authnsources.php in the SimpleSAMLphp
// configuration.
$simpleSaml = new \SimpleSAML\Auth\Simple($u5samlsspauthsourcekey);

if ($debug) {
    if ($simpleSaml->isAuthenticated()) {
        echo "<h3>You are currently logged in as " . $_COOKIE['u5samlusername'] . "</h3>";
    } else {
        echo "<h3>You are currently not logged in";
    }

    echo "</br><button onClick=\"window.location.href='?dologout=1';\">Logout</button>";
}

if (!$debug || isset($_GET['dologout'])) {
    $simpleSaml->logout($u5samllogoutresultpage);
}
