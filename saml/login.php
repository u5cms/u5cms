<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);
ignore_user_abort(true);set_time_limit(36000);

$debug = (isset($_COOKIE['u5debug']) && $_COOKIE['u5debug']) == 'yes' ? true : false;
$debug = (isset($_GET['u5debug'])) ? true : false;
require('../config.php');

/*
********************** SAML INTEGRATION ***************************3
*/
if ($u5samlmockauth != 'yes') {
    // As the SimpleSAMLphp component can be put anywhere, the path
    // to it is taken from the configuration.
    putenv('SIMPLESAMLPHP_CONFIG_DIR=' . $u5samlsspconfigdir);
    require ('../simplesaml/_include.php');

    // Initialize SimpleSAMLphp, the only parameter is the key of the SP
    // resource as set in the authnsources.php in the SimpleSAMLphp
    // configuration.
    $simpleSaml = new \SimpleSAML\Auth\Simple($u5samlsspauthsourcekey);

    // Require authenthication, redirects to IdP if not yet authenticated
    $simpleSaml->requireAuth();

    // We are authenticated, now fetch attributes from response and fill in
    $attributes = $simpleSaml->getAttributes();

    /*
     * The attributes coming from the SAML response are store in a nested array,
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
    $samlattribs = array();
    foreach($attributes as $xkey => $xval){
        $iz = strrpos($xkey, "/") + 1;
        $key = substr($xkey, $iz, 9999);
        $samlattribs[$key] = $xval[0];
    }
} else {
    $samlattribs = array(
        'adminEmployeeNumber' => '1234567',
        'adminOrganizationName' => 'Mock',
        'emailaddress' => $u5samlmockauthusername,
    );
}

// Save email attribute as username and also store all attributes in
// cookie for later use anywhere in the CMS
setcookie('u5samlusername', strtolower(trim($samlattribs['emailaddress'])), 0, '/', '', $httpsisinuse, true);
setcookie('u5samlnonce', $u5samlnonce, 0, '/', '', $httpsisinuse, true);
foreach ($samlattribs as $attrib => $value) {
    setcookie('u5saml' . $attrib, $value, 0, '/', '', $httpsisinuse, true);
}

// on CMS instance www.flyssi.ch we want an autoenrollment
// of user as intranet members
if ($u5samlautointranetenrollment == 'yes') {
$urlhash=sha1(date('Ymd').$password.$sessioncookiehashsalt.base64_encode($samlattribs['emailaddress']));
echo'<html><body><center style="margin-top:111px"><img src="../upload/spinner.gif" /><iframe frameborder="0" src="autointranetenroll.php?k='.$urlhash.'&e='.rawurlencode(base64_encode($samlattribs['emailaddress'])).'"></iframe></center></body></html>';
}

if ($debug) {
    // Print out processed attributes as saved in cookie
    echo "<h3>Attributes saved for the application:</h3>";
    echo "<table>";
    foreach($samlattribs as $key => $value){
        echo "<tr><td>" . $key . "</td><td>" . $value . "</td></tr>";
    }
    echo "</table>";

    // Print our original response attributes with their URNs/structure
    echo "<h3>Attributes (original array from AuthnResponse) :</h3>";
    echo "<pre>";
    print_r($attributes);
    echo "</pre>";
    // Exit here to be able to inspect the printed out information
    exit;
}

// Finally redirect back to the CMS
if ($u5samlautointranetenrollment == 'yes') echo '<script>
function loginsave() {
location.href="../loginsave.php?u='.rawurlencode($_GET['u']).'"
}
setTimeout("loginsave()",11111);
</script>';

else echo '<script>location.href="../loginsave.php?u='.rawurlencode($_GET['u']).'"</script>';
?>