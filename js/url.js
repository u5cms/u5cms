var decodeEntities=(function(){var e=document.createElement('div');function d(s){if(s&&typeof s==='string'){s=s.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi,'');s=s.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi,'');e.innerHTML=s;s=e.textContent||e.innerText||'';e.textContent='';}return s;}return d;})();

(function(){
  if(typeof s!=='string')return;

  s=decodeEntities(s);
  var xahellip='';

  if(s.length<4||s.charAt(3)!=='!')s='033!,!'+s;

  var parts=s.split('!,!'),lenStr=parts[0],s3=parts[1]||'';
  var visibleLen=parseInt(lenStr,10);if(!isFinite(visibleLen)||visibleLen<0)visibleLen=33;
  var s2=s.substr(6,visibleLen);

  if(s3!==s2)xahellip='&hellip;';

  var stripLabelPrefixes=[
    'mailto:','http://','https://','tel:','sms:','smsto:','sip:'
  ];

  for(var i=0;i<stripLabelPrefixes.length;i++){
    var p=stripLabelPrefixes[i];
    if(s2.indexOf(p)===0){s2=s2.substr(p.length);break;}
  }

  function unmask(prefix){
    var mask=prefix.replace(':','!'),cutLen=mask.length;
    if(s2.indexOf(mask)===0){
      s2=prefix+s2.substr(cutLen);
      s3=prefix+s3.substr(cutLen);
    }
  }

  unmask('mailto:');
  unmask('http://');
  unmask('https://');
  unmask('tel:');
  unmask('sms:');
  unmask('smsto:');
  unmask('mms:');
  unmask('mmsto:');
  unmask('sip:');
  unmask('callto:');
  unmask('skype:');
  unmask('whatsapp:');
  unmask('tg:');
  unmask('viber:');
  unmask('zoommtg:');
  unmask('geo:');
  unmask('maps:');
  unmask('comgooglemaps:');
  unmask('waze:');

  function getScheme(u){
    var m=String(u).match(/^([a-zA-Z0-9+.-]+):/);
    return m?m[1].toLowerCase():'';
  }

  var scheme=getScheme(s3);

  var noTargetSchemes={
    'mailto':1,'tel':1,'sms':1,'smsto':1,'mms':1,'mmsto':1,'sip':1,'callto':1,'skype':1,'whatsapp':1,'tg':1,'viber':1,'zoommtg':1,'geo':1,'maps':1,'comgooglemaps':1,'waze':1
  };

  var forbiddenSchemes={
    'javascript':1,'data':1,'vbscript':1,'about':1,'chrome':1,'edge':1,'moz-extension':1,'file':1
  };

  function escAttr(t){
    return String(t).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;');
  }

  function escText(t){
    return String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  if(forbiddenSchemes[scheme]){
    document.write(escText(s2)+xahellip);
    return;
  }

  var href=s3,title=s3;
  var html='<a href="'+escAttr(href)+'" title="'+escAttr(title)+'"';
  if(!noTargetSchemes[scheme])html+=' target="_blank"';
  html+='>'+escText(s2)+xahellip+'</a>';

  document.write(html);
})();
