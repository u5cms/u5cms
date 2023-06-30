<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);

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
$simpleSaml->logout($u5samllogoutresultpage);
