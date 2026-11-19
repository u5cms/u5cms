<?php
/* Nullable database fields must remain valid transport input on newer PHP. */
$source=file_get_contents(dirname(__DIR__).'/u5admin/formdata.intro.php');
$start=strpos($source,'function u5_transport_text_escaped(');
$end=strpos($source,'function mkltgt(',$start);
if ($start===false || $end===false) throw new RuntimeException('Transport helpers not found.');
set_error_handler(function($severity,$message,$file,$line){throw new ErrorException($message,0,$severity,$file,$line);});
eval(substr($source,$start,$end-$start));
$checks=0;
foreach (array(null,'',0,'0') as $input) foreach (array('u5_transport_text_escaped','u5_transport_text_raw') as $function) {
    $expected=$input===null ? '' : (string)$input;
    if ($function($input)!==$expected) throw new RuntimeException($function.' changed a valid empty/zero field.');
    $checks++;
}
$input="\xDCber \x80</div>\r\n\t<&\"";
$expected=array('u5_transport_text_escaped'=>"\xDCber \x80&lt;/div&gt;&#13;&#10;&#9;<&\"",
    'u5_transport_text_raw'=>"\xDCber \x80&lt;/div&gt;&#13;&#10;&#9;&lt;&amp;&quot;");
foreach ($expected as $function=>$value) {
    if ($function($input)!==$value) throw new RuntimeException($function.' changed encoding, quoting or line breaks.');
    $checks++;
}
restore_error_handler();echo 'Form-data text transport: '.$checks." checks passed.\n";
