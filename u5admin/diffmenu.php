<?php header('Content-Type: text/html; charset=ISO-8859-1'); ?><?php require_once('connect.inc.php') ?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>...</title>
<?php require('backendcss.php'); ?></head>
<body><a style="text-decoration:none;position:absolute;top:0;right:0;font-size:60%" href="javascript:location.reload()">Click here to auto-select the nearest difference (e.g. after changing languages)</a>
<script>
parent.document.title='Diff of '+parent.opener.document.getElementsByTagName('select')[0].value;
document.write('<h1>Diff of <i>'+parent.opener.document.getElementsByTagName('select')[0].value+'</i></h1>');
let nolang = parent.opener.document.getElementsByTagName('select')[0].options[parent.opener.document.getElementsByTagName('select')[0].selectedIndex].text.includes("c:") ? 1 : 0;
</script>

<script>
function vpick() {
AL=document.getElementById('VL').value-0;
BL=document.getElementById('LL').value-1;
that=11 + 15 * AL - 1 + BL;
document.getElementById('TL').value=parent.opener.document.getElementsByTagName('textarea')[that].value;	

AR=document.getElementById('VR').value-0;
BR=document.getElementById('LR').value-1;
that=11 + 15 * AR - 1 + BR;
document.getElementById('TR').value=parent.opener.document.getElementsByTagName('textarea')[that].value;	

for(i=1;i<=5;i++) {
document.querySelectorAll('.cL'+i).forEach(function(el) {
  el.hidden=false;
});
document.querySelectorAll('.cR'+i).forEach(function(el) {
  el.hidden=false;
});
}

document.querySelectorAll('.cL'+BL).forEach(function(el) {
  el.hidden=true;
});

document.querySelectorAll('.cR'+BR).forEach(function(el) {
  el.hidden=true;
});

document.getElementById('diff').submit();
}
</script>

<form id="diff" method="post" action="diffresult.php" target="result">
<center style="margin-top:-55px">

<select id="VL" onchange="vpick()">
<script>
e=parent.opener.document.getElementsByTagName('h2');
for(i=0;i<e.length;i++) {
document.write('<option value="'+i+'" id="L'+i+'">'+i+': '+e[i].innerHTML+'</option>');	
}
</script>
</select>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<select id="VR" onchange="vpick()">
<script>
e=parent.opener.document.getElementsByTagName('h2');
for(i=0;i<e.length;i++) {
document.write('<option value="'+i+'" id="R'+i+'">'+i+': '+e[i].innerHTML+'</option>');	
}
</script>
</select>

<br>

<select id="LL" onchange="vpick();document.cookie='LL='+escape(this.value)+'; expires=Thu, 31 Dec 2037 12:00:00 GMT';">
<option value="1"><?php echo str_replace('0','',$lan1na); ?></option>
<option value="2"><?php echo str_replace('0','',$lan2na); ?></option>
<option value="3"><?php echo str_replace('0','',$lan3na); ?></option>
<option value="4"><?php echo str_replace('0','',$lan4na); ?></option>
<option value="5"><?php echo str_replace('0','',$lan5na); ?></option>
</select>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<select id="LR" onchange="vpick();document.cookie='LR='+escape(this.value)+'; expires=Thu, 31 Dec 2037 12:00:00 GMT';">
<option value="1"><?php echo str_replace('0','',$lan1na); ?></option>
<option value="2"><?php echo str_replace('0','',$lan2na); ?></option>
<option value="3"><?php echo str_replace('0','',$lan3na); ?></option>
<option value="4"><?php echo str_replace('0','',$lan4na); ?></option>
<option value="5"><?php echo str_replace('0','',$lan5na); ?></option>
</select>

</center>

<center style="display:none">
<textarea name="TL" id="TL" style="height:333px;width:555px"></textarea>
<textarea name="TR" id="TR" style="height:333px;width:555px"></textarea>
</center>
</form>

<script>
vpick();
if(nolang==0) {
if (document.cookie.indexOf('LL=')>-1){ 
document.getElementById('LL').value=('; '+document.cookie).split('; LL=')[1].split(';')[0];
vpick();
}
if (document.cookie.indexOf('LR=')>-1){ 
document.getElementById('LR').value=('; '+document.cookie).split('; LR=')[1].split(';')[0];
vpick();
}
}
else {
document.getElementById('LL').style.display='none';	
document.getElementById('LR').style.display='none';	
}
</script>

<script>
let isWaitingForResult = false;

function diffDone() {
    console.log("Diff rendering completed (called from result frame).");
    isWaitingForResult = false;
}

(function checkLoop() {
    setTimeout(() => {
        if (isWaitingForResult) return checkLoop();

        try {
            const resultFrame = parent.frames['result'];
            const resultDoc = resultFrame.document;

            const tds = resultDoc.getElementsByTagName("td");
            let hasErrorHighlight = false;

            for (let i = 0; i < tds.length; i++) {
                const bg = resultFrame.getComputedStyle(tds[i]).backgroundColor;
                if (bg === "rgb(255, 236, 236)" || bg === "rgb(234, 255, 234)") {
                    hasErrorHighlight = true;
                    tds[i].scrollIntoView({ behavior: "smooth", block: "start" });

                    for (let i = 0; i < document.getElementById('VR').options.length; i++) {
                        if (!document.getElementById('VR').options[i].hidden) {
                            document.getElementById('VR').selectedIndex = i;
                            break;
                        }
                    }
                    break;
                }
            }

            if (hasErrorHighlight) {
                console.log("Detected changed cell. Stopping.");
                return;
            }

            const select = document.getElementById('VL');
            if (!select) return;

            if (select.selectedIndex < select.options.length - 1) {
                isWaitingForResult = true;
                select.selectedIndex++;
                console.log("No changed cell found. Advancing to next option...");
                vpick();
            } else {
                console.log("Reached end of select menu. No #ffecec cell found.");
            }

            checkLoop();
        } catch (e) {
            console.error("Unexpected error:", e);
            checkLoop();
        }
    }, 333); 
})();
</script>

<script>
e=parent.opener.document.getElementsByTagName('h2');
for(ii=0;ii<5;ii++) {
oldtextarea='';
for(i=e.length-1;i>=0;i--) {
that=11 + 15 * i - 1 + ii;
textarea=parent.opener.document.getElementsByTagName('textarea')[that].value;
if(textarea==oldtextarea){document.getElementById('L'+i).classList.add('cL'+ii);document.getElementById('R'+i).classList.add('cR'+ii);}
oldtextarea=textarea;
}
}
</script>

<?php
$sql_a="SELECT name FROM resources WHERE name='modify!diff!php' AND deleted!=1";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';
$num_a = mysql_num_rows($result_a);
if ($num_a>0) {
$_GET['c']='modify!diff!php';
include('../u5sys.content.php');
}
?>
</body>
</html>
