<?php
$t=time();
$beforets=array('.css"'       ,  '.css)'       ,  '.jpg)'       ,  '.jpe)'       ,  '.jpeg)'       ,  '.gif)'       ,  '.htm)'       ,  '.html)'       ,  '.svg)'       ,  '.ttf)'       ,  '.woff)'           );
$afterts= array('.css?'.$t.'"',  '.css?'.$t.')',  '.jpg?'.$t.')',  '.jpe?'.$t.')',  '.jpeg?'.$t.')',  '.gif?'.$t.')',  '.htm?'.$t.')',  '.html?'.$t.')',  '.svg?'.$t.')',  '.ttf?'.$t.')',  '.woff?'.$t.')'    );


$sql_a="SELECT * FROM resources WHERE typ='c' AND deleted!=1";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}

$num_a = mysql_num_rows($result_a);

for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);

if (file_put_contents('../r/'.$row_a['name'].'.css',str_replace($beforets,$afterts,$row_a['content_1'].' '))) {
echo '<!--w ../r/ ok -->';
}
else echo '<script>alert("PROBLEM: The server can store your data but not write the consequent output file '.$row_a['name'].'.\n\nEFFECTS: The data you typed is stored but you won\'t see any effects in the layout.\n\nSOLUTION: CHMOD the folder named \'r\' RECURSIVELY (incl. all its files, subfolders a.s.o.) e. g. to 777 e. g. with FileZilla.");</script>';
}

?>
<script>
if (parent) if (parent.opener) if (parent.opener.parent) if (parent.opener.parent.i1) if (parent.opener.parent.i1.pframe2) parent.opener.parent.i1.pframe2.location.reload();
if (parent) if (parent.opener) if (parent.opener.parent) if (parent.opener.parent.i2) if (parent.opener.parent.i2.pframe2) parent.opener.parent.i2.pframe2.location.reload();
</script>