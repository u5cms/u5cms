function MAX(inpname) {
eval('document.u5form.'+inpname+'.value=validated(document.u5form.'+inpname+'.value)');
inimax();
}

function validated(string) {
    for (var i=0, output='', valid="1234567890"; i<string.length; i++)
           if (valid.indexOf(string.charAt(i)) != -1)
                     output += string.charAt(i)
                         return output;
                         }


function inimax() {
if(location.href.indexOf('c=')>0)c=escape(location.href.split('c=')[1]);
else if(location.href.indexOf('n=')>0)c=escape(location.href.split('n=')[1]);
for(m=0;m<document.getElementsByTagName('input').length;m++) {
if(document.getElementsByTagName('input')[m].name.indexOf('_MAX_')>0) {
document.getElementsByTagName('input')[m].nextSibling.innerHTML='<iframe scrolling="no" frameborder="0" height="18" style="margin-bottom:-4px" width="100" src="max.php?c='+c+'&m='+document.getElementsByTagName('input')[m].name+'"></iframe>';
}
}
}
inimax();