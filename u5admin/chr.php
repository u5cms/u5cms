<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);
if($_GET['more']==1) header("Location: characters/punctuation.php");
require('../config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php require('backendcss.php'); ?>
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="<?php echo $cssbackendspecialchars ?>">
<iframe width="0" height="0" frameborder="0" name="mychar" id="mychar"></iframe>

<script>
function senddoins(sendthis) {
mychar.location.href=('mychar.php?c='+escape(decodeURIComponent(sendthis)));
parent.putdoins<?php echo $_GET['l']?>(decodeURIComponent(sendthis));
}
</script>
<?php 
if (strpos(rawurlencode($_GET['c']),'BD')>0) {
$ucrs='¼,½,¾,⅐,⅑,⅒,⅓,⅔,⅕,⅖,⅗,⅘,⅙,⅚,⅛,⅜,⅝,⅞,⅟,Ⅰ,Ⅱ,Ⅲ,Ⅳ,Ⅴ,Ⅵ,Ⅶ,Ⅷ,Ⅸ,Ⅹ,Ⅺ,Ⅻ,Ⅼ,Ⅽ,Ⅾ,Ⅿ,ⅰ,ⅱ,ⅲ,ⅳ,ⅴ,ⅵ,ⅶ,ⅷ,ⅸ,ⅹ,ⅺ,ⅻ,ⅼ,ⅽ,ⅾ,ⅿ,ↀ,ↁ,ↂ,Ↄ,ↄ,ↅ,ↆ,ↇ,ↈ,↉,↊,↋,↌,↍,↎,↏,℀,℁,ℂ,℃,℄,℅,℆,ℇ,℈,℉,ℊ,ℋ,ℌ,ℍ,ℎ,ℏ,ℐ,ℑ,ℒ,ℓ,℔,ℕ,№,℗,℘,ℙ,ℚ,ℛ,ℜ,ℝ,℞,℟,℠,℡,™,℣,ℤ,℥,Ω,℧,ℨ,℩,K,Å,ℬ,ℭ,℮,ℯ,ℰ,ℱ,Ⅎ,ℳ,ℴ,ℵ,ℶ,ℷ,ℸ,ℹ,℺,℻,ℼ,ℽ,ℾ,ℿ,⅀,⅁,⅂,⅃,⅄,ⅅ,ⅆ,ⅇ,ⅈ,ⅉ,⅊,⅋,⅌,⅍,ⅎ';
}

if (strpos(rawurlencode($_GET['c']),'u2192')>0) {
$ucrs='←,↑,→,↓,↔,↕,↖,↗,↘,↙,↚,↛,↜,↝,↞,↟,↠,↡,↢,↣,↤,↥,↦,↧,↨,↩,↪,↫,↬,↭,↮,↯,↰,↱,↲,↳,↴,↵,↶,↷,↸,↹,↺,↻,↼,↽,↾,↿,⇀,⇁,⇂,⇃,⇄,⇅,⇆,⇇,⇈,⇉,⇊,⇋,⇌,⇍,⇎,⇏,⇐,⇑,⇒,⇓,⇔,⇕,⇖,⇗,⇘,⇙,⇚,⇛,⇜,⇝,⇞,⇟,⇠,⇡,⇢,⇣,⇤,⇥,⇦,⇧,⇨,⇩,⇪,⇫,⇬,⇭,⇮,⇯,⇰,⇱,⇲,⇳,⇴,⇵,⇶,⇷,⇸,⇹,⇺,⇻,⇼,⇽,⇾,⇿,✀,✁,✂,✃,✄,✅,✆,✇,✈,✉,✊,✋,✌,✍,✎,✏,✐,✑,✒,✓,✔,✕,✖,✗,✘,✙,✚,✛,✜,✝,✞,✟,✠,✡,✢,✣,✤,✥,✦,✧,✨,✩,✪,✫,✬,✭,✮,✯,✰,✱,✲,✳,✴,✵,✶,✷,✸,✹,✺,✻,✼,✽,✾,✿,❀,❁,❂,❃,❄,❅,❆,❇,❈,❉,❊,❋,❌,❍,❎,❏,❐,❑,❒,❓,❔,❕,❖,❗,❘,❙,❚,❛,❜,❝,❞,❟,❠,❡,❢,❣,❤,❥,❦,❧,❨,❩,❪,❫,❬,❭,❮,❯,❰,❱,❲,❳,❴,❵,❶,❷,❸,❹,❺,❻,❼,❽,❾,❿,➀,➁,➂,➃,➄,➅,➆,➇,➈,➉,➊,➋,➌,➍,➎,➏,➐,➑,➒,➓,➔,➕,➖,➗,➘,➙,➚,➛,➜,➝,➞,➟,➠,➡,➢,➣,➤,➥,➦,➧,➨,➩,➪,➫,➬,➭,➮,➯,➰,➱,➲,➳,➴,➵,➶,➷,➸,➹,➺,➻,➼,➽,➾,➿,①,②,③,④,⑤,⑥,⑦,⑧,⑨,⑩,⑪,⑫,⑬,⑭,⑮,⑯,⑰,⑱,⑲,⑳,⑴,⑵,⑶,⑷,⑸,⑹,⑺,⑻,⑼,⑽,⑾,⑿,⒀,⒁,⒂,⒃,⒄,⒅,⒆,⒇,⒈,⒉,⒊,⒋,⒌,⒍,⒎,⒏,⒐,⒑,⒒,⒓,⒔,⒕,⒖,⒗,⒘,⒙,⒚,⒛,⒜,⒝,⒞,⒟,⒠,⒡,⒢,⒣,⒤,⒥,⒦,⒧,⒨,⒩,⒪,⒫,⒬,⒭,⒮,⒯,⒰,⒱,⒲,⒳,⒴,⒵,Ⓐ,Ⓑ,Ⓒ,Ⓓ,Ⓔ,Ⓕ,Ⓖ,Ⓗ,Ⓘ,Ⓙ,Ⓚ,Ⓛ,Ⓜ,Ⓝ,Ⓞ,Ⓟ,Ⓠ,Ⓡ,Ⓢ,Ⓣ,Ⓤ,Ⓥ,Ⓦ,Ⓧ,Ⓨ,Ⓩ,ⓐ,ⓑ,ⓒ,ⓓ,ⓔ,ⓕ,ⓖ,ⓗ,ⓘ,ⓙ,ⓚ,ⓛ,ⓜ,ⓝ,ⓞ,ⓟ,ⓠ,ⓡ,ⓢ,ⓣ,ⓤ,ⓥ,ⓦ,ⓧ,ⓨ,ⓩ,⓪,⓫,⓬,⓭,⓮,⓯,⓰,⓱,⓲,⓳,⓴,⓵,⓶,⓷,⓸,⓹,⓺,⓻,⓼,⓽,⓾,⓿,─,━,│,┃,┄,┅,┆,┇,┈,┉,┊,┋,┌,┍,┎,┏,┐,┑,┒,┓,└,┕,┖,┗,┘,┙,┚,┛,├,┝,┞,┟,┠,┡,┢,┣,┤,┥,┦,┧,┨,┩,┪,┫,┬,┭,┮,┯,┰,┱,┲,┳,┴,┵,┶,┷,┸,┹,┺,┻,┼,┽,┾,┿,╀,╁,╂,╃,╄,╅,╆,╇,╈,╉,╊,╋,╌,╍,╎,╏,═,║,╒,╓,╔,╕,╖,╗,╘,╙,╚,╛,╜,╝,╞,╟,╠,╡,╢,╣,╤,╥,╦,╧,╨,╩,╪,╫,╬,╭,╮,╯,╰,╱,╲,╳,╴,╵,╶,╷,╸,╹,╺,╻,╼,╽,╾,╿,▀,▁,▂,▃,▄,▅,▆,▇,█,▉,▊,▋,▌,▍,▎,▏,▐,░,▒,▓,▔,▕,▖,▗,▘,▙,▚,▛,▜,▝,▞,▟,■,□,▢,▣,▤,▥,▦,▧,▨,▩,▪,▫,▬,▭,▮,▯,▰,▱,▲,△,▴,▵,▶,▷,▸,▹,►,▻,▼,▽,▾,▿,◀,◁,◂,◃,◄,◅,◆,◇,◈,◉,◊,○,◌,◍,◎,●,◐,◑,◒,◓,◔,◕,◖,◗,◘,◙,◚,◛,◜,◝,◞,◟,◠,◡,◢,◣,◤,◥,◦,◧,◨,◩,◪,◫,◬,◭,◮,◯,◰,◱,◲,◳,◴,◵,◶,◷,◸,◹,◺,◻,◼,◽,◾,◿,☀,☁,☂,☃,☄,★,☆,☇,☈,☉,☊,☋,☌,☍,☎,☏,☐,☑,☒,☓,☔,☕,☖,☗,☘,☙,☚,☛,☜,☝,☞,☟,☠,☡,☢,☣,☤,☥,☦,☧,☨,☩,☪,☫,☬,☭,☮,♰,♱,♲,♳,♴,♵,♶,♷,♸,♹,♺,♻,♼,♽,♾,♿,⚀,⚁,⚂,⚃,⚄,⚅,⚆,⚇,⚈,⚉,⚊,⚋,⚌,⚍,⚎,⚏,⚐,⚑,⚒,⚓,⚔,⚕,⚖,⚗,⚘,⚙,⚚,⚛,⚜,⚝,⚞,⚟,⚠,⚡,⚢,⚣,⚤,⚥,⚦,⚧,⚨,⚩,⚪,⚫,⚬,⚭,⚮,⚯,⚰,⚱,⚲,⚳,⚴,⚵,⚶,⚷,⚸,⚹,⚺,⚻,⚼,⚽,⚾,⚿,⛀,⛁,⛂,⛃,⛄,⛅,⛆,⛇,⛈,⛉,⛊,⛋,⛌,⛍,⛎,⛏,⛐,⛑,⛒,⛓,⛔,⛕,⛖,⛗,⛘,⛙,⛚,⛛,⛜,⛝,⛞,⛟,⛠,⛡,⛢,⛣,⛤,⛥,⛦,⛧,⛨,⛩,⛪,⛫,⛬,⛭,⛮,⛯,⛰,⛱,⛲,⛳,⛴,⛵,⛶,⛷,⛸,⛹,⛺,⛻,⛼,⛽,⛾,⛿,☯,☰,☱,☲,☳,☴,☵,☶,☷,☸,☹,☺,☻,☼,☽,☾,☿,♀,♁,♂,♃,♄,♅,♆,♇,♈,♉,♊,♋,♌,♍,♎,♏,♐,♑,♒,♓,♔,♕,♖,♗,♘,♙,♚,♛,♜,♝,♞,♟,♠,♡,♢,♣,♤,♥,♦,♧,♨,♩,♪,♫,♬,♭,♮,♯';
}

if (strpos(rawurlencode($_GET['c']),'B1')>0) {
$ucrs='±,×,÷,∃,ƒ,∀,∁,∂,∃,∄,∅,∆,∇,∈,∉,∊,∋,∌,∍,∎,∏,∐,∑,−,∓,∔,∕,∖,∗,∘,∙,√,∛,∜,∝,∞,∟,∠,∡,∢,∣,∤,∥,∦,∧,∨,∩,∪,∫,∬,∭,∮,∯,∰,∱,∲,∳,∴,∵,∶,∷,∸,∹,∺,∻,∼,∽,∾,∿,≀,≁,≂,≃,≄,≅,≆,≇,≈,≉,≊,≋,≌,≍,≎,≏,≐,≑,≒,≓,≔,≕,≖,≗,≘,≙,≚,≛,≜,≝,≞,≟,≠,≡,≢,≣,≤,≥,≦,≧,≨,≩,≪,≫,≬,≭,≮,≯,≰,≱,≲,≳,≴,≵,≶,≷,≸,≹,≺,≻,≼,≽,≾,≿,⊀,⊁,⊂,⊃,⊄,⊅,⊆,⊇,⊈,⊉,⊊,⊋,⊌,⊍,⊎,⊏,⊐,⊑,⊒,⊓,⊔,⊕,⊖,⊗,⊘,⊙,⊚,⊛,⊜,⊝,⊞,⊟,⊠,⊡,⊢,⊣,⊤,⊥,⊦,⊧,⊨,⊩,⊪,⊫,⊬,⊭,⊮,⊯,⊰,⊱,⊲,⊳,⊴,⊵,⊶,⊷,⊸,⊹,⊺,⊻,⊼,⊽,⊾,⊿,⋀,⋁,⋂,⋃,⋄,⋅,⋆,⋇,⋈,⋉,⋊,⋋,⋌,⋍,⋎,⋏,⋐,⋑,⋒,⋓,⋔,⋕,⋖,⋗,⋘,⋙,⋚,⋛,⋜,⋝,⋞,⋟,⋠,⋡,⋢,⋣,⋤,⋥,⋦,⋧,⋨,⋩,⋪,⋫,⋬,⋭,⋮,⋯,⋰,⋱,⋲,⋳,⋴,⋵,⋶,⋷,⋸,⋹,⋺,⋻,⋼,⋽,⋾,⋿,⌀,⌁,⌂,⌃,⌄,⌅,⌆,⌇,⌈,⌉,⌊,⌋,⌌,⌍,⌎,⌏,⌐,⌑,⌒,⌓,⌔,⌕,⌖,⌗,⌘,⌙,⌚,⌛,⌜,⌝,⌞,⌟,⌠,⌡,⌢,⌣,⌤,⌥,⌦,⌧,⌨,〈,〉,⌫,⌬,⌭,⌮,⌯,⌰,⌱,⌲,⌳,⌴,⌵,⌶,⌷,⌸,⌹,⌺,⌻,⌼,⌽,⌾,⌿,⍀,⍁,⍂,⍃,⍄,⍅,⍆,⍇,⍈,⍉,⍊,⍋,⍌,⍍,⍎,⍏,⍐,⍑,⍒,⍓,⍔,⍕,⍖,⍗,⍘,⍙,⍚,⍛,⍜,⍝,⍞,⍟,⍠,⍡,⍢,⍣,⍤,⍥,⍦,⍧,⍨,⍩,⍪,⍫,⍬,⍭,⍮,⍯,⍰,⍱,⍲,⍳,⍴,⍵,⍶,⍷,⍸,⍹,⍺,⍻,⍼,⍽,⍾,⍿,⎀,⎁,⎂,⎃,⎄,⎅,⎆,⎇,⎈,⎉,⎊,⎋,⎌,⎍,⎎,⎏,⎐,⎑,⎒,⎓,⎔,⎕,⎖,⎗,⎘,⎙,⎚,⎛,⎜,⎝,⎞,⎟,⎠,⎡,⎢,⎣,⎤,⎥,⎦,⎧,⎨,⎩,⎪,⎫,⎬,⎭,⎮,⎯,⎰,⎱,⎲,⎳,⎴,⎵,⎶,⎷,⎸,⎹,⎺,⎻,⎼,⎽,⎾,⎿,⏀,⏁,⏂,⏃,⏄,⏅,⏆,⏇,⏈,⏉,⏊,⏋,⏌,⏍,⏎,⏏,⏐,⏑,⏒,⏓,⏔,⏕,⏖,⏗,⏘,⏙,⏚,⏛,⏜,⏝,⏞,⏟,⏠,⏡,⏢,⏣,⏤,⏥,⏦,⏧,⏨,⏩,⏪,⏫,⏬,⏭,⏮,⏯,⏰,⏱,⏲,⏳,⏴,⏵,⏶,⏷,⏸,⏹,⏺,⏻,⏼,⏽,⏾,⏿,␀,␁,␂,␃,␄,␅,␆,␇,␈,␉,␊,␋,␌,␍,␎,␏,␐,␑,␒,␓,␔,␕,␖,␗,␘,␙,␚,␛,␜,␝,␞,␟,␠,␡,␢,␣,␤,␥,␦,␧,␨,␩,␪,␫,␬,␭,␮,␯,␰,␱,␲,␳,␴,␵,␶,␷,␸,␹,␺,␻,␼,␽,␾,␿';
}

if (strpos(rawurlencode($_GET['c']),'B2')>0) {
$ucrs='⁰,¹,²,³,ⁱ,⁴,⁵,⁶,⁷,⁸,⁹,⁺,⁻,⁼,⁽,⁾,ⁿ,₀,₁,₂,₃,₄,₅,₆,₇,₈,₉,₊,₋,₌,₍,₎,ᵃ,ᵇ,ᶜ,ᵈ,ᵉ,ᶠ,ᵍ,ʰ,ⁱ,ʲ,ᵏ,ˡ,ᵐ,ⁿ,ᵒ,ᵖ,ʳ,ˢ,ᵗ,ᵘ,ᵛ,ʷ,ˣ,ʸ,ᶻ,ᴬ,ᴮ,ᴰ,ᴱ,ᴳ,ᴴ,ᴵ,ᴶ,ᴷ,ᴸ,ᴹ,ᴺ,ᴼ,ᴾ,ᴿ,ᵀ,ᵁ,ⱽ,ᵂ,ₐ,ₑ,ₕ,ᵢ,ⱼ,ₖ,ₗ,ₘ,ₙ,ₒ,ₚ,ᵣ,ₛ,ₜ,ᵤ,ᵥ,ₓ,ᵅ,ᵝ,ᵞ,ᵟ,ᵋ,ᶿ,ᶥ,ᶲ,ᵠ,ᵡ,ᵦ,ᵧ,ᵨ,ᵩ,ᵪ,α,β,γ,δ,ε,ζ,η,θ,ι,κ,λ,μ,ν,ξ,ο,π,ρ,σ,ς,τ,υ,φ,χ,ψ,ω,Α,Β,Γ,Δ,Ε,Ζ,Η,Θ,Ι,Κ,Λ,Μ,Ν,Ξ,Ο,Π,Ρ,Σ,Τ,Υ,Φ,Χ,Ψ,Ω';
}

if (strpos(rawurlencode($_GET['c']),'u0153')>0) {
$ucrs='!,",#,$,%,&,\',(,),*,+,-,.,/,0,1,2,3,4,5,6,7,8,9,:,;,<,=,>,?,@,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,[,\,],^,_,`,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,{,|,},~,¡,¢,£,¤,¥,¦,§,¨,©,ª,«,¬,®,¯,°,±,²,³,´,µ,¶,·,¸,¹,º,»,¼,½,¾,¿,À,Á,Â,Ã,Ä,Å,Æ,Ç,È,É,Ê,Ë,Ì,Í,Î,Ï,Ð,Ñ,Ò,Ó,Ô,Õ,Ö,×,Ø,Ù,Ú,Û,Ü,Ý,Þ,ß,à,á,â,ã,ä,å,æ,ç,è,é,ê,ë,ì,í,î,ï,ð,ñ,ò,ó,ô,õ,ö,÷,ø,ù,ú,û,ü,ý,þ,ÿ,Ā,ā,Ă,ă,Ą,ą,Ć,ć,Ĉ,ĉ,Ċ,ċ,Č,č,Ď,ď,Đ,đ,Ē,ē,Ĕ,ĕ,Ė,ė,Ę,ę,Ě,ě,Ĝ,ĝ,Ğ,ğ,Ġ,ġ,Ģ,ģ,Ĥ,ĥ,Ħ,ħ,Ĩ,ĩ,Ī,ī,Ĭ,ĭ,Į,į,İ,ı,Ĳ,ĳ,Ĵ,ĵ,Ķ,ķ,ĸ,Ĺ,ĺ,Ļ,ļ,Ľ,ľ,Ŀ,ŀ,Ł,ł,Ń,ń,Ņ,ņ,Ň,ň,ŉ,Ŋ,ŋ,Ō,ō,Ŏ,ŏ,Ő,ő,Œ,œ,Ŕ,ŕ,Ŗ,ŗ,Ř,ř,Ś,ś,Ŝ,ŝ,Ş,ş,Š,š,Ţ,ţ,Ť,ť,Ŧ,ŧ,Ũ,ũ,Ū,ū,Ŭ,ŭ,Ů,ů,Ű,ű,Ų,ų,Ŵ,ŵ,Ŷ,ŷ,Ÿ,Ź,ź,Ż,ż,Ž,ž,ſ,ƀ,Ɓ,Ƃ,ƃ,Ƅ,ƅ,Ɔ,Ƈ,ƈ,Ɖ,Ɗ,Ƌ,ƌ,ƍ,Ǝ,Ə,Ɛ,Ƒ,ƒ,Ɠ,Ɣ,ƕ,Ɩ,Ɨ,Ƙ,ƙ,ƚ,ƛ,Ɯ,Ɲ,ƞ,Ɵ,Ơ,ơ,Ƣ,ƣ,Ƥ,ƥ,Ʀ,Ƨ,ƨ,Ʃ,ƪ,ƫ,Ƭ,ƭ,Ʈ,Ư,ư,Ʊ,Ʋ,Ƴ,ƴ,Ƶ,ƶ,Ʒ,Ƹ,ƹ,ƺ,ƻ,Ƽ,ƽ,ƾ,ƿ,ǀ,ǁ,ǂ,ǃ,Ǆ,ǅ,ǆ,Ǉ,ǈ,ǉ,Ǌ,ǋ,ǌ,Ǎ,ǎ,Ǐ,ǐ,Ǒ,ǒ,Ǔ,ǔ,Ǖ,ǖ,Ǘ,ǘ,Ǚ,ǚ,Ǜ,ǜ,ǝ,Ǟ,ǟ,Ǡ,ǡ,Ǣ,ǣ,Ǥ,ǥ,Ǧ,ǧ,Ǩ,ǩ,Ǫ,ǫ,Ǭ,ǭ,Ǯ,ǯ,ǰ,Ǳ,ǲ,ǳ,Ǵ,ǵ,Ƕ,Ƿ,Ǹ,ǹ,Ǻ,ǻ,Ǽ,ǽ,Ǿ,ǿ,Ȁ,ȁ,Ȃ,ȃ,Ȅ,ȅ,Ȇ,ȇ,Ȉ,ȉ,Ȋ,ȋ,Ȍ,ȍ,Ȏ,ȏ,Ȑ,ȑ,Ȓ,ȓ,Ȕ,ȕ,Ȗ,ȗ,Ș,ș,Ț,ț,Ȝ,ȝ,Ȟ,ȟ,Ƞ,ȡ,Ȣ,ȣ,Ȥ,ȥ,Ȧ,ȧ,Ȩ,ȩ,Ȫ,ȫ,Ȭ,ȭ,Ȯ,ȯ,Ȱ,ȱ,Ȳ,ȳ,ȴ,ȵ,ȶ,ȷ,ȸ,ȹ,Ⱥ,Ȼ,ȼ,Ƚ,Ⱦ,ȿ,ɀ,Ɂ,ɂ,Ƀ,Ʉ,Ʌ,Ɇ,ɇ,Ɉ,ɉ,Ɋ,ɋ,Ɍ,ɍ,Ɏ,ɏ,ɐ,ɑ,ɒ,ɓ,ɔ,ɕ,ɖ,ɗ,ɘ,ə,ɚ,ɛ,ɜ,ɝ,ɞ,ɟ,ɠ,ɡ,ɢ,ɣ,ɤ,ɥ,ɦ,ɧ,ɨ,ɩ,ɪ,ɫ,ɬ,ɭ,ɮ,ɯ,ɰ,ɱ,ɲ,ɳ,ɴ,ɵ,ɶ,ɷ,ɸ,ɹ,ɺ,ɻ,ɼ,ɽ,ɾ,ɿ,ʀ,ʁ,ʂ,ʃ,ʄ,ʅ,ʆ,ʇ,ʈ,ʉ,ʊ,ʋ,ʌ,ʍ,ʎ,ʏ,ʐ,ʑ,ʒ,ʓ,ʔ,ʕ,ʖ,ʗ,ʘ,ʙ,ʚ,ʛ,ʜ,ʝ,ʞ,ʟ,ʠ,ʡ,ʢ,ʣ,ʤ,ʥ,ʦ,ʧ,ʨ,ʩ,ʪ,ʫ,ʬ,ʭ,ʮ,ʯ,ʰ,ʱ,ʲ,ʳ,ʴ,ʵ,ʶ,ʷ,ʸ,ʹ,ʺ,ʻ,ʼ,ʽ,ʾ,ʿ,ˀ,ˁ,˂,˃,˄,˅,ˆ,ˇ,ˈ,ˉ,ˊ,ˋ,ˌ,ˍ,ˎ,ˏ,ː,ˑ,˒,˓,˔,˕,˖,˗,˘,˙,˚,˛,˜,˝,˞,˟,ˠ,ˡ,ˢ,ˣ,ˤ,˥,˦,˧,˨,˩,˪,˫,ˬ,˭,ˮ,˯,˰,˱,˲,˳,˴,˵,˶,˷,˸,˹,˺,˻,˼,˽,˾,˿';
}

if (strpos(rawurlencode($_GET['c']),'u2013')>0) {
$ucrs='<!><span style="font-size:60%">minus</span>,−,<!><span style="font-size:60%">hyphen</span>,‐,<!><span style="font-size:60%">non&nbsp;breaking&nbsp;hyphen</span>,‑,<!><span style="font-size:60%">figure&nbsp;dash</span>,‒,<!><span style="font-size:60%">en&nbsp;dash</span>,–,<!><span style="font-size:60%">em&nbsp;dash</span>,—,<!><span style="font-size:60%">horizontal&nbsp;bar</span>,―,<!><span style="font-size:60%">double&nbsp;hyphen</span>,⸗,‖,‗,‘,’,‚,‛,“,”,„,‟,†,‡,•,‣,․,‥,…,‧,‰,‱,′,″,‴,‵,‶,‷,‸,‹,›,※,‼,‽,‾,‿,⁀,⁁,⁂,⁃,⁄,⁅,⁆,⁇,⁈,⁉,⁊,⁋,⁌,⁍,⁎,⁏,⁐,⁑,⁒,⁓,⁔,⁕,⁖,⁗,⁘,⁙,⁚,⁛,⁜,⁝,⁞,⁠,⁡,⁢,⁣,⁤,⁥,⁦,⁧,⁨,⁩,︰,﹅,﹆,﹉,﹊,﹋,﹌,﹐,﹑,﹒,﹔,﹕,﹖,﹗,﹟,﹠,﹡,﹨,﹪,﹫,！,＂,＃,％,＆,＇,＊,，,．,／,：,；,？,＠,＼,｡,､,･';
}

if (strpos(rawurlencode($_GET['c']),'u202F')>0) {
$ucrs='<!><span style="font-size:60%">Space</span><span style="background:yellow;font-size:150%">,&nbsp;,<!><span style="font-size:60%">Tab</span><span style="background:yellow;font-size:150%">,	,<!><span style="font-size:60%">No‑Break&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Ogham&nbsp;Space&nbsp;Mark</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">En&nbsp;Space&nbsp;or&nbsp;Nut</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Em&nbsp;Space&nbsp;or&nbsp;Mutton</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Three‑Per‑Em&nbsp;Space&nbsp;or&nbsp;Thick&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Four‑Per‑Em&nbsp;Space&nbsp;or&nbsp;Mid&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Six‑Per‑Em&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Figure&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Punctuation&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Thin&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Hair&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Zero‑Width&nbsp;Space&nbsp;(remove&nbsp;<span style="font-size:200%">⌜</span>+<span style="font-size:200%">⌟</span>)</span><span style="background:yellow;font-size:150%">,⌜​⌟,<!><span style="font-size:60%">Narrow&nbsp;No‑Break&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Medium&nbsp;Mathematical&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Word&nbsp;Joiner&nbsp;(remove&nbsp;<span style="font-size:200%">⌜</span>+<span style="font-size:200%">⌟</span>)</span><span style="background:yellow;font-size:150%">,⌜⁠⌟,<!><span style="font-size:60%">Ideographic&nbsp;Space</span><span style="background:yellow;font-size:150%">,　';
}

$xc=explode(',',$ucrs);
for ($xi=0;$xi<count($xc);$xi++) {
if (strpos($xc[$xi],'!')==1) echo $xc[$xi];
else {
$xc[$xi]=rawurlencode($xc[$xi]);
echo '<a style="cursor:pointer;color:blue" title="'.rawurldecode($xc[$xi]).' '.mymb_ord(rawurldecode($xc[$xi])).' ('.dechex(mymb_ord(rawurldecode($xc[$xi]))).')" onclick="senddoins(\''.$xc[$xi].'\')"><script>document.write(decodeURIComponent(\''.$xc[$xi].'\'))</script></a>';
if (strpos(rawurlencode($_GET['c']),'u202F')>0) echo '</span>';
echo '<span style="font-size:90%"> </span><span style="color:white;font-weight:bold">|</span><span style="font-size:90%"> </span>'; 
}}
echo '<!--<span style="font-size:50%;color:#fff">'.rawurlencode($_GET['c']).'</span>-->';


function mymb_html_entity_decode($string)
{
    if (extension_loaded('mbstring') === true)
    {
    	mb_language('Neutral');
    	mb_internal_encoding('UTF-8');
    	mb_detect_order(array('UTF-8', 'ISO-8859-15', 'ISO-8859-1', 'ASCII'));

    	return mb_convert_encoding($string, 'UTF-8', 'HTML-ENTITIES');
    }

    return html_entity_decode($string, ENT_COMPAT, 'UTF-8');
}

function mymb_ord($string)
{
    if (extension_loaded('mbstring') === true)
    {
    	mb_language('Neutral');
    	mb_internal_encoding('UTF-8');
    	mb_detect_order(array('UTF-8', 'ISO-8859-15', 'ISO-8859-1', 'ASCII'));

    	$result = unpack('N', mb_convert_encoding($string, 'UCS-4BE', 'UTF-8'));

    	if (is_array($result) === true)
    	{
    		return $result[1];
    	}
    }

    return ord($string);
}

function mymb_chr($string)
{
    return mymb_html_entity_decode('&#' . intval($string) . ';');
}
?>
</body>
</html>