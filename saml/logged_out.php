<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);

require ('../simplesaml/_include.php');

$state = \SimpleSAML\Auth\State::loadState((string)$_REQUEST['LogoutState'], 'MyLogoutState');
$ls = $state['saml:sp:LogoutStatus'];

if ($ls['Code'] === 'urn:oasis:names:tc:SAML:2.0:status:Success' && !isset($ls['SubCode'])) {
    /* Successful logout. */
    echo("You have been logged out.");
} else {
    /* Logout failed. Tell the user to close the browser. */
    echo("We were unable to log you out of all your sessions. To be completely sure that you are logged out, you need to close your web browser.");
}

echo "<br /><pre>";
var_dump($state);
