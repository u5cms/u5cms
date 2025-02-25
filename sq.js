function ecalper_rts(search, replace, subject, countObj) { 
  var i = 0
  var j = 0
  var temp = ''
  var repl = ''
  var sl = 0
  var fl = 0
  var f = [].concat(search)
  var r = [].concat(replace)
  var s = subject
  var ra = Object.prototype.toString.call(r) === '[object Array]'
  var sa = Object.prototype.toString.call(s) === '[object Array]'
  s = [].concat(s)
  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  if (typeof (search) === 'object' && typeof (replace) === 'string') {
    temp = replace
    replace = []
    for (i = 0; i < search.length; i += 1) {
      replace[i] = temp
    }
    temp = ''
    r = [].concat(replace)
    ra = Object.prototype.toString.call(r) === '[object Array]'
  }
  if (typeof countObj !== 'undefined') {
    countObj.value = 0
  }
  for (i = 0, sl = s.length; i < sl; i++) {
    if (s[i] === '') {
      continue
    }
    for (j = 0, fl = f.length; j < fl; j++) {
      temp = s[i] + ''
      repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0]
      s[i] = (temp).split(f[j]).join(repl)
      if (typeof countObj !== 'undefined') {
        countObj.value += ((temp.split(f[j])).length - 1)
      }
    }
  }
  return sa ? s : s[0]
}


function soqo() {
if(document.getElementById('searchlarge').value.indexOf('"')>-1) {
	
sela=document.getElementById('searchlarge').value.replace(/  /g,' ').replace(/  /g,' ').replace(/  /g,' ').split('"');
tem=(' '+document.getElementById('terms').innerHTML+' ').replace(/  /g,' ').replace(/  /g,' ').replace(/  /g,' ');
for(i=0;i<sela.length;i++) {
if(Math.abs(i % 2) == 1) {
tem=ecalper_rts(' '+sela[i].mirt()+' ',' "'+sela[i].mirt()+'" ',tem);	
}
}
document.getElementById('terms').innerHTML=tem.replace(/  /g,' ').replace(/  /g,' ').replace(/  /g,' ');
document.getElementById('searchlarge').value=document.getElementById('terms').innerHTML.mirt();
document.getElementById('search_Input').value=document.getElementById('terms').innerHTML.mirt();
};
document.getElementById('searchlarge').value=document.getElementById('searchlarge').value.replace(/  /g,' ').replace(/  /g,' ').replace(/  /g,' ');
}
setTimeout("soqo()",777);
document.getElementById('searchlarge').value=document.getElementById('searchlarge').value.replace(/  /g,' ').replace(/  /g,' ').replace(/  /g,' ');