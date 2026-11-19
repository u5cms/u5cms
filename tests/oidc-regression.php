<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
// Runs without a database, provider account, network, or persistent test keys.
require dirname(__DIR__).'/oidc/vendor/autoload.php';
require dirname(__DIR__).'/src/Oidc/Client.php';
require dirname(__DIR__).'/src/Oidc/Session.php';

use U5cms\Oidc\Client;
use U5cms\Oidc\Session;
use Firebase\JWT\JWT;

$checks = 0;
function check($condition, $label) {
    global $checks;
    if (!$condition) throw new RuntimeException('FAILED: '.$label);
    $checks++;
}
function rejects($callback, $label) {
    try { $callback(); }
    catch (Throwable $e) { check(true, $label); return; }
    check(false, $label);
}
set_error_handler(function ($level,$message) { throw new ErrorException($message,0,$level); });
$config = array('issuer'=>'https://issuer.example/tenant', 'client_id'=>'cms+client', 'client_secret'=>'test:secret',
    'redirect_uri'=>'https://cms.example/cms/oidc/callback.php', 'subjects'=>array('stable-subject'=>'member@example.org'));
$client = new Client($config);
check($client->basePath() === '/cms/', 'subdirectory base path');
check((new Client(array_replace($config,array('redirect_uri'=>'https://cms.example/oidc/callback.php'))))->basePath() === '/', 'root base path');
foreach (array('yes','no') as $frontend) foreach (array('','local-login') as $exception) {
    $policy = new Client(array_replace($config,array('frontend'=>$frontend,'frontend_enforced_unless_target_contains'=>$exception)));
    foreach (array('/cms/index.php?c=private','/cms/index.php?c=private&local-login=1') as $target) {
        $expected=$frontend === 'yes' || ($exception !== '' && strpos($target,$exception) === false);
        check($policy->requiresFederation($target)===$expected,'SAML-compatible frontend yes/no/substring matrix');
    }
    foreach (array('/cms/u5admin','/cms/u5admin/','/cms/u5admin/acc0.php?local-login=1','/cms/index.php?p=1&local-login=1','/cms/index.php?p=2&local-login=1') as $target) {
        check($policy->requiresFederation($target),'frontend policy cannot bypass backend or backend preview');
    }
    check($policy->requiresFederation('/cms/index.php?local-login=1',true),'explicit backend gate always federated');
}
$selective = new Client(array_replace($config,array('frontend'=>'no','frontend_enforced_unless_target_contains'=>'&local=1')));
check(!$selective->requiresFederation('https://cms.example/cms/index.php?c=group&local=1'),'original URL marker remains literal');
check($selective->requiresFederation('/cms/index.php?c=group&LOCAL=1'),'substring comparison remains case-sensitive like SAML');
foreach (array('issuer','client_id','redirect_uri') as $setting) rejects(function () use ($config,$setting) { new Client(array_replace($config,array($setting=>''))); }, 'required '.$setting);
foreach (array('http://idp.example','https://user:pass@idp.example','https://idp.example/#bad','https://idp.example/with space') as $url) {
    rejects(function () use ($config,$url) { new Client(array_replace($config,array('issuer'=>$url))); }, 'invalid issuer URL');
}
foreach (array('clock_skew'=>121,'session_ttl'=>0,'idle_timeout'=>'900','scopes'=>array('email'),
    'token_auth_method'=>'private_key_jwt','subjects'=>array('s'=>'?bad:member;'),'client_secret'=>'',
    'allowed_endpoint_origins'=>array('https://cdn.example/path')) as $key=>$value) {
    rejects(function () use ($config,$key,$value) { new Client(array_replace($config,array($key=>$value))); }, 'invalid configuration '.$key);
}
foreach (array('u5admin/'=>'/cms/u5admin/','https://cms.example/cms/index.php?c=protected#row'=>'/cms/index.php?c=protected#row',
    '/cms/index.php?c=_searchsi&searchstring=two%20words'=>'/cms/index.php?c=_searchsi&searchstring=two%20words', ''=>'/cms/index.php') as $target=>$expected) {
    check($client->returnUrl($target) === $expected, 'legitimate return target');
}
foreach (array('//evil.example/','https://evil.example/cms/','http://cms.example/cms/', 'https://cms.example:444/cms/',
    'https://u:p@cms.example/cms/', 'javascript:alert(1)', '/cms/../other', '/cms/%2e%2e/other', '/cms/a\\evil',
    '/cms2/private','/cms/%252e%252e/other','/cms/%0d%0aLocation:evil','%2f%2fevil.example',
    '/cms/%255c%255cevil.example', '/cms/index.php?x=%250d%250aInjected', str_repeat('x',2001), array()) as $target) {
    rejects(function () use ($client,$target) { $client->returnUrl($target); }, 'reject redirect attack');
}
$private = openssl_pkey_new(array('private_key_bits'=>2048,'private_key_type'=>OPENSSL_KEYTYPE_RSA));
$details = openssl_pkey_get_details($private);
$jwk = array('kty'=>'RSA','kid'=>'key-1','use'=>'sig','alg'=>'RS256','n'=>Client::base64url($details['rsa']['n']),'e'=>Client::base64url($details['rsa']['e']));
$jwks = array('keys'=>array($jwk));
$nonce = Client::random();
$claims = array('iss'=>$config['issuer'],'sub'=>'stable-subject','aud'=>$config['client_id'],
    'iat'=>time(),'exp'=>time()+600,'nonce'=>$nonce,'email'=>'attacker@example.org','email_verified'=>true);
$sign = function ($payload,$kid='key-1',$header=null) use ($private) { return JWT::encode($payload,$private,'RS256',$kid,$header); };
$token = $sign($claims);
check($client->validate($token,$jwks,$nonce)['username'] === 'member@example.org','explicit subject mapping ignores email claim');
check($client->validate($token,$jwks,$nonce)['claims']===array(),'claims are not retained by default');
check($client->validate($sign(array_replace($claims,array('exp'=>time()+60))),$jwks,$nonce)['expires']>time()+3500,'short-lived ID token does not silently shorten the configured RP session');
$attributeClient=new Client(array_replace($config,array('claims_to_keep'=>array('email','email_verified'))));
check($attributeClient->validate($token,$jwks,$nonce)['claims']===array('email'=>'attacker@example.org','email_verified'=>true),'only configured verified claims are retained');
check($attributeClient->validate($token,$jwks,$nonce)['username']==='member@example.org','retained attributes do not change identity mapping');
$emailClient=new Client(array_replace($config,array('identity_mode'=>'verified_email')));
check($emailClient->validate($token,$jwks,$nonce)['username']==='member@example.org','explicit mapping wins over verified email');
$emailClaims=array_replace($claims,array('sub'=>'new-email-sub','email'=>'new@example.org','email_verified'=>true));
$candidate=$emailClient->validate($sign($emailClaims),$jwks,$nonce);
check($candidate['username']===null && $candidate['mapping']==='verified_email' && $candidate['email']==='new@example.org','verified email requires subsequent durable identity binding');
foreach (array(false,'true',1,null) as $verified) {
    rejects(function () use ($emailClient,$sign,$emailClaims,$jwks,$nonce,$verified) {
        $emailClient->validate($sign(array_replace($emailClaims,array('email_verified'=>$verified))),$jwks,$nonce);
    },'verified-email profile requires boolean true from the signed token');
}
rejects(function () use ($config) { new Client(array_replace($config,array('claims_to_keep'=>array('nonce')))); }, 'protocol secrets cannot be retained as application claims');
check($client->validate($sign(array_replace($claims,array('aud'=>array($config['client_id'])))),$jwks,$nonce)['sub'] === 'stable-subject','single audience array');
check($client->validate($sign($claims,null),array('keys'=>array(array_diff_key($jwk,array('kid'=>true)))),$nonce)['sub'] === 'stable-subject','one unambiguous key without kid');
foreach (array('iss'=>'https://issuer.example/other','sub'=>'unknown','aud'=>'other-client','nonce'=>'wrong',
    'exp'=>time()-60,'iat'=>time()+61,'nbf'=>time()+61,'azp'=>'other-client') as $key=>$value) {
    rejects(function () use ($client,$sign,$claims,$jwks,$nonce,$key,$value) { $client->validate($sign(array_replace($claims,array($key=>$value))),$jwks,$nonce); }, 'claim rejection '.$key);
}
foreach (array('iss','sub','aud','iat','exp','nonce') as $key) {
    rejects(function () use ($client,$sign,$claims,$jwks,$nonce,$key) { $c=$claims;unset($c[$key]);$client->validate($sign($c),$jwks,$nonce); }, 'missing '.$key);
}
foreach (array('exp'=>'9999999999','iat'=>'123','nonce'=>array(),'sub'=>array(),'aud'=>array($config['client_id'],'untrusted'),'nbf'=>'0') as $key=>$value) {
    rejects(function () use ($client,$sign,$claims,$jwks,$nonce,$key,$value) { $client->validate($sign(array_replace($claims,array($key=>$value))),$jwks,$nonce); }, 'wrong claim type '.$key);
}
rejects(function () use ($client,$sign,$claims,$jwks,$nonce) { $client->validate($sign(array_replace($claims,array('iat'=>time()-700))),$jwks,$nonce); }, 'stale issued-at');
rejects(function () use ($client,$sign,$claims,$jwks,$nonce) { $client->validate($sign($claims,'unknown-key'),$jwks,$nonce); }, 'unknown signing key');
rejects(function () use ($client,$token,$jwks,$nonce) { $parts=explode('.',$token);$parts[2][0]=$parts[2][0]==='A'?'B':'A';$client->validate(implode('.',$parts),$jwks,$nonce); }, 'tampered signature');
rejects(function () use ($client,$claims,$jwks,$nonce) { $client->validate(JWT::encode($claims,str_repeat('x',64),'HS256','key-1'),$jwks,$nonce); }, 'HMAC algorithm confusion');
rejects(function () use ($client,$claims,$jwks,$nonce) { $client->validate(Client::base64url('{"alg":"none"}').'.'.Client::base64url(json_encode($claims)).'.',$jwks,$nonce); }, 'unsigned token');
foreach (array('crit'=>array('x'),'jku'=>'https://evil.example/keys','x5u'=>'https://evil.example/cert','jwk'=>$jwk) as $key=>$value) {
    rejects(function () use ($client,$sign,$claims,$jwks,$nonce,$key,$value) { $client->validate($sign($claims,'key-1',array($key=>$value)),$jwks,$nonce); }, 'untrusted JOSE header '.$key);
}
rejects(function () use ($client,$token,$jwk,$nonce) { $client->validate($token,array('keys'=>array($jwk,$jwk)),$nonce); }, 'duplicate kid');
rejects(function () use ($client,$token,$jwk,$nonce) { $client->validate($token,array('keys'=>array(array_replace($jwk,array('use'=>'enc')))),$nonce); }, 'encryption-only key');
$access='access-test';$withHash=array_replace($claims,array('at_hash'=>Client::base64url(substr(hash('sha256',$access,true),0,16))));
check($client->validate($sign($withHash),$jwks,$nonce,$access)['sub']==='stable-subject','access token hash accepted');
rejects(function () use ($client,$sign,$withHash,$jwks,$nonce) { $client->validate($sign($withHash),$jwks,$nonce,'wrong'); }, 'access token hash mismatch');
$metadata = array('issuer'=>$config['issuer'],'authorization_endpoint'=>$config['issuer'].'/auth','token_endpoint'=>$config['issuer'].'/token',
    'jwks_uri'=>$config['issuer'].'/keys','response_types_supported'=>array('code'),'id_token_signing_alg_values_supported'=>array('RS256'),
    'code_challenge_methods_supported'=>array('S256'),'token_endpoint_auth_methods_supported'=>array('client_secret_basic','client_secret_post','none'));
$calls = array();
$transport = function ($url,$form,$headers) use (&$calls,&$metadata,$token,$jwks) {
    $calls[]=array($url,$form,$headers);
    if (substr($url,-33)==='/.well-known/openid-configuration') return $metadata;
    if (substr($url,-6)==='/token') return array('id_token'=>$token,'access_token'=>'access-test','token_type'=>'Bearer');
    return $jwks;
};
$transportClient = new Client($config,$transport);
check($transportClient->discovery()===$metadata,'discovery validation');
$tx = array('state'=>Client::random(),'nonce'=>$nonce,'verifier'=>Client::random());
parse_str(parse_url($transportClient->authorisation($metadata,$tx),PHP_URL_QUERY),$parameters);
check($parameters['response_type']==='code' && $parameters['response_mode']==='query','code flow only');
check($parameters['code_challenge_method']==='S256' && $parameters['code_challenge']===Client::base64url(hash('sha256',$tx['verifier'],true)),'PKCE S256');
check($parameters['state']===$tx['state'] && $parameters['nonce']===$nonce,'transaction-specific state/nonce');
check(!isset($parameters['client_secret']) && !isset($parameters['code_verifier']),'no secrets in authorisation URL');
foreach (array('client_secret_basic','client_secret_post','none') as $method) {
    $methodClient=new Client(array_replace($config,array('token_auth_method'=>$method)),$transport);$calls=array();
    $methodClient->exchange($metadata,'one-code',$tx);
    $request=$calls[0];
    check($request[1]['code_verifier']===$tx['verifier'] && $request[1]['redirect_uri']===$config['redirect_uri'],'exact token request binding');
    check(($method==='client_secret_basic') === isset($request[2][0]),'client auth header selected');
    check(($method==='client_secret_post') === isset($request[1]['client_secret']),'client secret post selected');
    check(!isset($calls[1][2][0]),'no token credentials forwarded to JWKS');
}
foreach (array('issuer'=>'https://evil.example','jwks_uri'=>'https://evil.example/keys','code_challenge_methods_supported'=>array('plain'),
    'response_types_supported'=>array('token'),'token_endpoint_auth_methods_supported'=>array('none')) as $key=>$value) {
    $saved=$metadata;$metadata[$key]=$value;
    rejects(function () use ($transportClient) { $transportClient->discovery(); }, 'reject discovery '.$key);
    $metadata=$saved;
}
$session = new Session($client,'test-installation-secret');
check($session->identity() === null, 'anonymous lookup starts no session');
$a=$session->begin('/cms/u5admin/',$metadata);$b=$session->begin('/cms/index.php?c=!intranet',$metadata);
check($a['state']!==$b['state'] && $a['nonce']!==$b['nonce'] && $a['verifier']!==$b['verifier'],'independent parallel-tab transactions');
rejects(function () use ($session) { $session->consume(array('state'=>Client::random(),'code'=>'x')); }, 'unbound state');
$used=$session->consume(array('state'=>$a['state'],'code'=>'one-code'));
rejects(function () use ($session,$a) { $session->consume(array('state'=>$a['state'],'code'=>'one-code')); }, 'callback replay rejected');
$session->establish($client->validate($token,$jwks,$nonce),$used);
check($session->identity()['username']==='member@example.org','established server-side identity');
$session->consume(array('state'=>$b['state'],'code'=>'other-code'));
check($session->identity()!==null,'other tab survives session ID rotation');
$cancel=$session->begin('/cms/',$metadata);
rejects(function () use ($session,$cancel) { $session->consume(array('state'=>$cancel['state'],'error'=>'access_denied')); }, 'cancelled login');
rejects(function () use ($session,$cancel) { $session->consume(array('state'=>$cancel['state'],'code'=>'x')); }, 'cancellation consumes transaction');
$race=$session->begin('/cms/',$metadata);$consumed=$session->consume(array('state'=>$race['state'],'code'=>'x'));
$session->logout();
check($session->identity()===null,'logout clears identity');
rejects(function () use ($session,$client,$token,$jwks,$nonce,$consumed) { $session->establish($client->validate($token,$jwks,$nonce),$consumed); }, 'logout cancels in-flight token exchange');
$tx=$session->begin('/cms/',$metadata);$tx=$session->consume(array('state'=>$tx['state'],'code'=>'x'));
$session->establish($client->validate($token,$jwks,$nonce),$tx);
check((new Session($client,'rotated-secret'))->identity()===null,'installation secret rotation revokes sessions');
$session->logout();
function editOidcTestSession($session, $change) {
    session_name($session->cookieName());session_id($_COOKIE[$session->cookieName()]);
    session_start();$change();session_write_close();
}
$timed=$session->begin('/cms/',$metadata);$timed=$session->consume(array('state'=>$timed['state'],'code'=>'x'));
$oldId=$_COOKIE[$session->cookieName()];$session->establish($client->validate($token,$jwks,$nonce),$timed);
check($_COOKIE[$session->cookieName()]!==$oldId,'successful authentication rotates the actual session ID');
editOidcTestSession($session,function () {$_SESSION['identity']['expires']=time()-1;});
check($session->identity()===null,'absolute session expiry removes authenticated identity');
$timed=$session->begin('/cms/',$metadata);$timed=$session->consume(array('state'=>$timed['state'],'code'=>'x'));
$session->establish($client->validate($token,$jwks,$nonce),$timed);
editOidcTestSession($session,function () {$_SESSION['identity']['last_seen']=time()-901;});
check($session->identity()===null,'idle timeout removes authenticated identity');
$expired=$session->begin('/cms/',$metadata);
editOidcTestSession($session,function () use ($expired) {$_SESSION['pending'][$expired['state']]['created']=time()-301;});
rejects(function () use ($session,$expired) {$session->consume(array('state'=>$expired['state'],'code'=>'x'));},'expired transaction rejected without exchange');
$session->logout();
$_COOKIE[$session->cookieName()]=str_repeat('a',32);
$fixed=$session->begin('/cms/',$metadata);
check($_COOKIE[$session->cookieName()]!==str_repeat('a',32),'strict sessions reject attacker-selected unknown ID');
$session->logout();
check($client->logoutResult()==='/cms/index.php?c=loggedout','default logout destination');
check((new Client(array_replace($config,array('logout_result_page'=>'index.php?c=farewell'))))->logoutResult()==='/cms/index.php?c=farewell','configured logout destination');
check((new Client(array_replace($config,array('logout_result_page'=>'https://federal.example/farewell#done'))))->logoutResult()==='https://federal.example/farewell#done','explicit administrator logout destination may be external like SAML');
rejects(function () use ($config) {new Client(array_replace($config,array('logout_result_page'=>'javascript:alert(1)')));},'unsafe logout scheme rejected');
$logout=$session->beginLogout();$session->finishLogout($logout);
rejects(function () use ($session,$logout) {$session->finishLogout($logout);},'logout response is single-use');
echo "OIDC core: $checks checks passed\n";
