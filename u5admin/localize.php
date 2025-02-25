<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>localize <?php echo $_GET['name'] ?></title>
    <script>if('<?php echo $_GET['name']?>'=='::LOGINPAGE::'||'<?php echo $_GET['name']?>'=='LOGINPAGE::')location.href='defineloginglobals.php';</script>
    <script src="shortcut.js"></script>
    <script>
        shortcut.add("Ctrl+S", function () {
            parent.close();
        })
    </script>
    <?php require('backendcss.php'); ?></head>

<body>
<h1><i><?php echo $_GET['name'] ?></i> is used in</h1>
<?php
require('../config.php');
$ormanualfullpath = '';

$replacestart = "replace(replace(replace(replace(replace(replace(";
$replaceend = ",'[[[[',' '),'[[[',' '),'[[',' '),']]]]',' '),']]]',' '),']]',' ')";

if ($ignoremanualfullpaths != 'yes') $ormanualfullpath = "
OR
(" . $replacestart . "content_1" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%' OR " . $replacestart . "content_2" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%' OR " . $replacestart . "content_3" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%' OR " . $replacestart . "content_4" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%' OR " . $replacestart . "content_5" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%')
OR
(" . $replacestart . "title_1" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%' OR " . $replacestart . "title_2" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%' OR " . $replacestart . "title_3" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%' OR " . $replacestart . "title_4" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%' OR " . $replacestart . "title_5" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%')
OR
(" . $replacestart . "desc_1" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%' OR " . $replacestart . "desc_2" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%' OR " . $replacestart . "desc_3" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%' OR " . $replacestart . "desc_4" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%' OR " . $replacestart . "desc_5" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%')
OR
(" . $replacestart . "key_1" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%' OR " . $replacestart . "key_2" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%' OR " . $replacestart . "key_3" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%' OR " . $replacestart . "key_4" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%' OR " . $replacestart . "key_5" . $replaceend . " LIKE '%r/" . gnirts_epacse_laer_lqsym($_GET['name']) . "/%')

OR
(" . $replacestart . "content_1" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "content_2" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "content_3" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "content_4" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "content_5" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%')
OR
(" . $replacestart . "title_1" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "title_2" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "title_3" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "title_4" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "title_5" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%')
OR
(" . $replacestart . "desc_1" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "desc_2" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "desc_3" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "desc_4" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "desc_5" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%')
OR
(" . $replacestart . "key_1" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "key_2" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "key_3" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "key_4" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "key_5" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%')

OR
(" . $replacestart . "content_1" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%' OR " . $replacestart . "content_2" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%' OR " . $replacestart . "content_3" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%' OR " . $replacestart . "content_4" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%' OR " . $replacestart . "content_5" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%')
OR
(" . $replacestart . "title_1" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%' OR " . $replacestart . "title_2" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%' OR " . $replacestart . "title_3" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%' OR " . $replacestart . "title_4" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%' OR " . $replacestart . "title_5" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%')
OR
(" . $replacestart . "desc_1" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%' OR " . $replacestart . "desc_2" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%' OR " . $replacestart . "desc_3" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%' OR " . $replacestart . "desc_4" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%' OR " . $replacestart . "desc_5" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%')
OR
(" . $replacestart . "key_1" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%' OR " . $replacestart . "key_2" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%' OR " . $replacestart . "key_3" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%' OR " . $replacestart . "key_4" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%' OR " . $replacestart . "key_5" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "&%')

OR
(" . $replacestart . "content_1" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%' OR " . $replacestart . "content_2" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%' OR " . $replacestart . "content_3" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%' OR " . $replacestart . "content_4" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%' OR " . $replacestart . "content_5" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%')
OR
(" . $replacestart . "title_1" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%' OR " . $replacestart . "title_2" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%' OR " . $replacestart . "title_3" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%' OR " . $replacestart . "title_4" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%' OR " . $replacestart . "title_5" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%')
OR
(" . $replacestart . "desc_1" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%' OR " . $replacestart . "desc_2" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%' OR " . $replacestart . "desc_3" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%' OR " . $replacestart . "desc_4" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%' OR " . $replacestart . "desc_5" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%')
OR
(" . $replacestart . "key_1" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%' OR " . $replacestart . "key_2" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%' OR " . $replacestart . "key_3" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%' OR " . $replacestart . "key_4" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%' OR " . $replacestart . "key_5" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\'%')

OR
(" . $replacestart . "content_1" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%' OR " . $replacestart . "content_2" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%' OR " . $replacestart . "content_3" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%' OR " . $replacestart . "content_4" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%' OR " . $replacestart . "content_5" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%')
OR
(" . $replacestart . "title_1" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%' OR " . $replacestart . "title_2" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%' OR " . $replacestart . "title_3" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%' OR " . $replacestart . "title_4" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%' OR " . $replacestart . "title_5" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%')
OR
(" . $replacestart . "desc_1" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%' OR " . $replacestart . "desc_2" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%' OR " . $replacestart . "desc_3" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%' OR " . $replacestart . "desc_4" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%' OR " . $replacestart . "desc_5" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%')
OR
(" . $replacestart . "key_1" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%' OR " . $replacestart . "key_2" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%' OR " . $replacestart . "key_3" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%' OR " . $replacestart . "key_4" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%' OR " . $replacestart . "key_5" . $replaceend . " LIKE '%c=" . gnirts_epacse_laer_lqsym($_GET['name']) . "\\\\\%')
";

$sql_a = "SELECT * FROM resources WHERE deleted!=1 AND name!='-' AND
(

(" . $replacestart . "content_1" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "content_2" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "content_3" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "content_4" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "content_5" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%')
OR
(" . $replacestart . "content_1" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "content_2" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "content_3" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "content_4" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "content_5" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%')
OR
(" . $replacestart . "content_1" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "content_2" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "content_3" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "content_4" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "content_5" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%')
OR
(" . $replacestart . "content_1" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "content_2" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "content_3" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "content_4" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "content_5" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%')
OR
(" . $replacestart . "content_1" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%' OR " . $replacestart . "content_2" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%' OR " . $replacestart . "content_3" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%' OR " . $replacestart . "content_4" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%' OR " . $replacestart . "content_5" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%')
OR
(" . $replacestart . "content_1" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%' OR " . $replacestart . "content_2" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%' OR " . $replacestart . "content_3" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%' OR " . $replacestart . "content_4" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%' OR " . $replacestart . "content_5" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%')
OR
(" . $replacestart . "content_1" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "content_2" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "content_3" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "content_4" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "content_5" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%')

 OR

(" . $replacestart . "title_1" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "title_2" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "title_3" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "title_4" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "title_5" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%')
OR
(" . $replacestart . "title_1" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "title_2" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "title_3" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "title_4" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "title_5" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%')
OR
(" . $replacestart . "title_1" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "title_2" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "title_3" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "title_4" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "title_5" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%')
OR
(" . $replacestart . "title_1" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "title_2" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "title_3" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "title_4" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "title_5" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%')
OR
(" . $replacestart . "title_1" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%' OR " . $replacestart . "title_2" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%' OR " . $replacestart . "title_3" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%' OR " . $replacestart . "title_4" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%' OR " . $replacestart . "title_5" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%')
OR
(" . $replacestart . "title_1" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%' OR " . $replacestart . "title_2" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%' OR " . $replacestart . "title_3" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%' OR " . $replacestart . "title_4" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%' OR " . $replacestart . "title_5" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%')
OR
(" . $replacestart . "title_1" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "title_2" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "title_3" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "title_4" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "title_5" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%')

 OR
 
(" . $replacestart . "desc_1" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "desc_2" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "desc_3" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "desc_4" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "desc_5" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%')
OR
(" . $replacestart . "desc_1" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "desc_2" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "desc_3" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "desc_4" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "desc_5" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%')
OR
(" . $replacestart . "desc_1" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "desc_2" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "desc_3" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "desc_4" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "desc_5" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%')
OR
(" . $replacestart . "desc_1" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "desc_2" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "desc_3" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "desc_4" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "desc_5" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%')
OR
(" . $replacestart . "desc_1" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%' OR " . $replacestart . "desc_2" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%' OR " . $replacestart . "desc_3" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%' OR " . $replacestart . "desc_4" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%' OR " . $replacestart . "desc_5" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%')
OR
(" . $replacestart . "desc_1" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%' OR " . $replacestart . "desc_2" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%' OR " . $replacestart . "desc_3" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%' OR " . $replacestart . "desc_4" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%' OR " . $replacestart . "desc_5" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%')
OR
(" . $replacestart . "desc_1" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "desc_2" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "desc_3" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "desc_4" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "desc_5" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%')

 OR

(" . $replacestart . "key_1" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "key_2" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "key_3" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "key_4" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "key_5" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%')
OR
(" . $replacestart . "key_1" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "key_2" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "key_3" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "key_4" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%' OR " . $replacestart . "key_5" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "]%')
OR
(" . $replacestart . "key_1" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "key_2" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "key_3" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "key_4" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "key_5" . $replaceend . " LIKE '%[" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%')
OR
(" . $replacestart . "key_1" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "key_2" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "key_3" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "key_4" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%' OR " . $replacestart . "key_5" . $replaceend . " LIKE '%:" . gnirts_epacse_laer_lqsym($_GET['name']) . "?%')
OR
(" . $replacestart . "key_1" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%' OR " . $replacestart . "key_2" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%' OR " . $replacestart . "key_3" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%' OR " . $replacestart . "key_4" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%' OR " . $replacestart . "key_5" . $replaceend . " LIKE '%[go:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:go]%')
OR
(" . $replacestart . "key_1" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%' OR " . $replacestart . "key_2" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%' OR " . $replacestart . "key_3" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%' OR " . $replacestart . "key_4" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%' OR " . $replacestart . "key_5" . $replaceend . " LIKE '%[lo:]" . gnirts_epacse_laer_lqsym($_GET['name']) . "[:lo]%')
OR
(" . $replacestart . "key_1" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "key_2" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "key_3" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "key_4" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%' OR " . $replacestart . "key_5" . $replaceend . " LIKE '%name=\"thanks\" value=\"" . gnirts_epacse_laer_lqsym($_GET['name']) . "\"%')

OR

   (typ='f' AND (
   
(" . $replacestart . "content_1" . $replaceend . " LIKE '" . gnirts_epacse_laer_lqsym($_GET['name']) . "' OR " . $replacestart . "content_2" . $replaceend . " LIKE '" . gnirts_epacse_laer_lqsym($_GET['name']) . "' OR " . $replacestart . "content_3" . $replaceend . " LIKE '" . gnirts_epacse_laer_lqsym($_GET['name']) . "' OR " . $replacestart . "content_4" . $replaceend . " LIKE '" . gnirts_epacse_laer_lqsym($_GET['name']) . "' OR " . $replacestart . "content_5" . $replaceend . " LIKE '" . gnirts_epacse_laer_lqsym($_GET['name']) . "')

    ))

OR

   (name='htmltemplate' AND (
   
(" . $replacestart . "content_1" . $replaceend . " LIKE '%{{{" . gnirts_epacse_laer_lqsym($_GET['name']) . "}}}%' OR " . $replacestart . "content_1" . $replaceend . " LIKE '%[_" . gnirts_epacse_laer_lqsym($_GET['name']) . "_]%')

    ))

$ormanualfullpath

)
ORDER BY typ, name
";

$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!...!<p>';

$num_a = mysql_num_rows($result_a);

for ($i_a = 0; $i_a < $num_a; $i_a++) {
    $row_a = mysql_fetch_array($result_a);

    if ($row_a['deleted'] == 2) $isarchived = '<small><small>(a)</small></small>';
    else $isarchived = '';

    echo '<a style="text-decoration:none" title="localize (where linked in)" href="localize.php?name=' . $row_a['name'] . '"><span id="o_'.$row_a['name'].'">L</span></a>&nbsp;';
    echo '<a style="text-decoration:none" title="focus in repository" href="javascript:void(0)" onclick="if(!opener){alert(\'Context window missing!\')} else {opener.parent.i3.location.href=\'focus.php?c=' . $row_a['name'] . '\'}">F</a>&nbsp;';

    if ($row_a['typ'] == 'p') echo $row_a['typ'] . ': ' . '<a style="text-decoration:none" title="Open right (+Alt=left)" href="javascript:void(0)" onclick="if(!opener){alert(\'Context window missing!\')} else{if(event.altKey)opener.parent.i3.parent.i1.lgotopage(\'' . $row_a['name'] . '\');else opener.parent.i3.parent.i2.gotopage(\'' . $row_a['name'] . '\')}">' . $row_a['name'] . '</a> ' . $isarchived . '<br>';
    else  echo $row_a['typ'] . ': ' . $row_a['name'] . ' ' . $isarchived . ' ' . '<a style="text-decoration:none" title="show link to data" href="javascript:void(0)" onclick="if(!opener){alert(\'Context window missing!\')} else{opener.parent.i3.location.href=\'open.php?name=' . $row_a['name'] . '&typ=' . $row_a['typ'] . '\'}">&rarr;</a><br>';
}

if ($num_a == 0) {
echo 'NOWHERE';

    $sql_a = "SELECT typ FROM resources WHERE name='" . gnirts_epacse_laer_lqsym($_GET['name']) . "'";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query schlug fehl!...!<p>';
    $row_a = mysql_fetch_array($result_a);
    if ($row_a['typ'] == 'c') echo ' (CSS imports are not localized)';
}
?>
<?php include('focuslinktitle.inc.php') ?>
<?php 
$sqlget="SELECT * FROM resources WHERE deleted!=1";
$h=sha1($username.$password.$_SERVER['PHP_AUTH_USER'].$_SERVER['PHP_AUTH_PW'].$sqlget)
?>
<iframe style="display:none" src="filter2.inc.php?sql=<?php echo rawurlencode($sqlget)?>&h=<?php echo $h ?>"></iframe>
</body>
</html>