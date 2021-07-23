var decodeEntities = (function() {
  // this prevents any overhead from creating the object each time
  var element = document.createElement('div');

  function decodeHTMLEntities (str) {
    if(str && typeof str === 'string') {
      // strip script/html tags
      str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
      str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
      element.innerHTML = str;
      str = element.textContent;
      element.textContent = '';
    }

    return str;
  }

  return decodeHTMLEntities;
})();
s=decodeEntities(s);
s2=s;
s3=s;
s5=s;
xahellip='';
if (s.substr(3,1)!='!') {s='033!,!'+s;}s3=s.split('!,!')[1];
s2=s.substr(6,s.split('!,!')[0]);
s4=s.substr(s.split('!,!')[0]-1+7,111);
if (s3!=s2) xahellip='&hellip;';

if (s2.indexOf('mailto:')==0)  s2=s2.substr(7);
if (s2.indexOf('http://')==0)  s2=s2.substr(7);
if (s2.indexOf('https://')==0) s2=s2.substr(8);

if (s2.indexOf('mailto!')==0)  {s2='mailto:'+s2.substr(7); s3='mailto:'+s3.substr(7);}
if (s2.indexOf('http!//')==0)  {s2='http://'+s2.substr(7); s3='http://'+s3.substr(7);}
if (s2.indexOf('https!//')==0) {s2='https://'+s2.substr(8);s3='https://'+s3.substr(8);}

if (s3.indexOf('mailto:')==0) document.write('<a href="'+s3+'" title="'+s3+'">'+s2+xahellip+'</a>');
else document.write('<a href="'+s3+'" title="'+s3+'" target="_blank">'+s2+xahellip+'</a>');