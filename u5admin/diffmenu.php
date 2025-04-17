<?php header('Content-Type: text/html; charset=ISO-8859-1'); ?><?php require_once('connect.inc.php') ?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>...</title>
<?php require('backendcss.php'); ?></head>
<body>
<script>
parent.document.title='Diff of '+parent.opener.document.getElementsByTagName('select')[0].value;
document.write('<h1>Diff of <i>'+parent.opener.document.getElementsByTagName('select')[0].value+'</i></h1>');

function vpick() {

AL=document.getElementById('VL').value-0;
BL=document.getElementById('LL').value-1;
that=11 + 15 * AL - 1 + BL;
document.getElementById('TL').value=parent.opener.document.getElementsByTagName('textarea')[that].value;	

AR=document.getElementById('VR').value-0;
BR=document.getElementById('LR').value-1;
that=11 + 15 * AR - 1 + BR;
document.getElementById('TR').value=parent.opener.document.getElementsByTagName('textarea')[that].value;	

document.getElementById('diff').submit();
}

</script>

<form id="diff" method="post" action="diffresult.php" target="result">

<center style="margin-top:-55px">

<select id="VL" onchange="vpick()">
<script>
e=parent.opener.document.getElementsByTagName('h2');
for(i=0;i<e.length;i++) {
document.write('<option value="'+i+'">'+i+': '+e[i].innerHTML+'</option>');	
}
</script>
</select>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<select id="VR" onchange="vpick()">
<script>
e=parent.opener.document.getElementsByTagName('h2');
for(i=0;i<e.length;i++) {
document.write('<option value="'+i+'">'+i+': '+e[i].innerHTML+'</option>');	
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
if (document.cookie.indexOf('LL=')>-1){ 
document.getElementById('LL').value=('; '+document.cookie).split('; LL=')[1].split(';')[0];
vpick();
}
if (document.cookie.indexOf('LR=')>-1){ 
document.getElementById('LR').value=('; '+document.cookie).split('; LR=')[1].split(';')[0];
document.getElementById('LR').onchange;
vpick();
}
</script>

<script>
let isWaitingForResult = false;

(function checkLoop() {
    setTimeout(() => {
        if (isWaitingForResult) {
            // Don't do anything until the result frame finishes loading
            return checkLoop();
        }

        try {
            const resultFrame = parent.frames['result'];
            let resultDoc;

            try {
                resultDoc = resultFrame.document;
            } catch (err) {
                console.warn("Unable to access result frame document. Retrying...");
                return checkLoop();
            }

            if (resultDoc.readyState !== "complete") {
                console.log("Result frame still loading...");
                return checkLoop();
            }

            const tds = resultDoc.getElementsByTagName("td");
            let hasErrorHighlight = false;

            for (let i = 0; i < tds.length; i++) {
                const bg = resultFrame.getComputedStyle(tds[i]).backgroundColor;
                if (bg === "rgb(255, 236, 236)") {
                    hasErrorHighlight = true;
                    break;
                }
            }

            if (hasErrorHighlight) {
                console.log("Detected #ffecec cell. Stopping.");
                return;
            }

            const select = document.getElementById('VL');
            if (!select) {
                console.warn("Select menu with ID 'VL' not found.");
                return;
            }

            if (select.selectedIndex < select.options.length - 1) {
                // Set the waiting flag *immediately*, before doing anything else
                isWaitingForResult = true;

                select.selectedIndex++;
                console.log("No #ffecec cell found. Advancing to next option...");
                vpick();

                // Now we poll until the new page is ready
                (function waitForResult() {
                    setTimeout(() => {
                        try {
                            const doc = resultFrame.document;
                            if (doc.readyState === "complete") {
                                console.log("Result frame finished loading.");
                                isWaitingForResult = false;
                            } else {
                                waitForResult();
                            }
                        } catch (e) {
                            waitForResult();
                        }
                    }, 100); // Retry loading check every 100ms
                })();
            } else {
                console.log("Reached end of select menu. No #ffecec cell found.");
            }

            checkLoop(); // Continue looping regardless

        } catch (e) {
            console.error("Unexpected error:", e);
            checkLoop();
        }
    }, 111); // Now safe even at very small intervals
})();
</script>
</body>
</html>