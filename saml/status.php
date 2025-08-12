<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);

require('../config.php');

/*
********************** SAML LOGOUT INTEGRATION ***************************3
*/

// As the SimpleSAMLphp component can be put anywhere, the path
// to it is taken from the configuration.
putenv('SIMPLESAMLPHP_CONFIG_DIR=' . $u5samlsspconfigdir);
require('../simplesaml/_include.php');

// Initialize SimpleSAMLphp, the only parameter is the key of the SP
// resource as set in the authnsources.php in the SimpleSAMLphp
// configuration.
$simpleSaml = new \SimpleSAML\Auth\Simple($u5samlsspauthsourcekey);

if ($simpleSaml->isAuthenticated()) {
    echo "You are currently logged in as user " . $_COOKIE['u5samlusername'] . "</h3>";
    echo "<br/><button onClick=\"window.location.href='logout.php';\">Logout (Standard)</button>";
    echo " <button onClick=\"window.location.href='logout_debug.php';\">Logout (Debug)</button>";
    // We are authenticated, now fetch attributes from response and fill in
    $samlattributes = $simpleSaml->getAttributes();

    /*
    * The attributes coming from the SAML response are store in a nested arrazy,
    * the keys are the URN from the schema:
    * Array
    * (
    *     'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress' => Array
    *     (
    *         array
    *         (
    *             [0] => 'max.mustermann@test.com'
    *         )
    *     )
    * )
    */

    // Print out attributes as found in SAML-Response
    echo "<h3>Attributes read from SAML-Response and processed for application</h3>";
    echo "<table>";
    foreach ($attributes as $xkey => $xval) {
        $iz = strrpos($xkey, "/") + 1;
        $key = substr($xkey, $iz, 9999);
        $proc_attribs[$key] = $xval[0];
        echo "<tr><td>u5saml" . $key . "</td><td>" . $xval[0] . "</td></tr>";
    }
    echo "</table>";

    // Print our original response attributes with their URNs/structure
    echo "<h3>Original attributes from SAML-Response:</h3>";
    echo "<pre>";
    print_r($samlattributes);
    echo "</pre>";
} else {
    echo "No SAML-Session found. You are currently not logged in.";
    echo "<br/><button onClick=\"window.location.href='login.php?u=" . rawurlencode('saml/status.php') . "';\">Login</button>";
}
