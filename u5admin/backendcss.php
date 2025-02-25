<?php
if(!isset($cssremoteurlsifnecessary)) $cssremoteurlsifnecessary="@import url(//fonts.googleapis.com/css?family=Arimo:400,700,400italic,700italic&subset=latin,cyrillic,latin-ext,vietnamese,greek,greek-ext,cyrillic-ext);";
if(!isset($cssbackendtextarea)) $cssbackendtextarea="font-family:Consolas,Menlo,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New,monospace,sans-serif;line-height:20px;font-size:12px;";
if(!isset($cssbackendinput)) $cssbackendinput="font-family:'Arimo', sans-serif";
if(!isset($cssbackendspecialchars)) $cssbackendspecialchars="font-family:'Arimo', sans-serif;";
if(!isset($cssbackendnormaltext)) $cssbackendnormaltext="font-family:'Segoe UI', 'Arimo', sans-serif;";
?>
<style>
<?php echo $cssremoteurlsifnecessary;?>
body {<?php echo $cssbackendnormaltext ?>;}
textarea {<?php echo $cssbackendtextarea ?>}
input, select, button {<?php echo $cssbackendinput ?>}
h1 {font-weight:100}
h2 {font-weight:100}
h3 {font-weight:100}
h4 {font-weight:100;margin-bottom:3px}
th {font-weight:100;text-align:left}
.blink_me {
animation: blinker 1s linear infinite;
}
@keyframes blinker {
  50% {
    opacity: 0;
  }
}
.sblink {
color:black;
background:yellow;
animation: blinker 1s linear infinite;
}
@keyframes blinker {
  50% {
    opacity: 0;
  }
}
#selfclose{position:fixed !important}
</style>