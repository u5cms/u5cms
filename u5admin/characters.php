<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);
require_once('../myfunctions.inc.php');
if($_GET['more']==1) header("Location: characters/punctuation.php");
$ucrs='';
if (isset($_GET['s'])) setcookie('mochr', htmlspecialchars($_GET['s']) , time()+3600*24*365*10,'/');
require('../config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo str_replace('1114111&ndash;NaN','u5CMS Commons&ndash;',htmlspecialchars($_GET['s']).'&ndash;'.htmlspecialchars($_GET['e'])) ?> characters</title>
<script>
function noopener() {
if (!opener) self.close();
setTimeout("noopener()",1111);
}
setTimeout("noopener()",1111);
</script>
<?php require('backendcss.php'); ?>
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="font-size:120%;background:#eee">
<div style="<?php echo $cssbackendspecialchars ?>">
<iframe width="0" height="0" frameborder="0" name="mychar" id="mychar"></iframe>

<script>
self.focus();
<?php 
if(!isset($_GET['s'])) {
	if (intval($_GET['w'])<100) $_GET['w']=640;
	if (intval($_GET['h'])<100) $_GET['h']=640;
	echo 'window.resizeTo('.floor(intval($_GET['w'])*0.42).','.intval($_GET['h']).');';
    echo 'window.moveTo(0,0);';
}
if (!isset($_GET['s']) && $_COOKIE['mochr']>0) echo "location.href='characters.php?s=".$_COOKIE['mochr']."';";
?>

function senddoins(sendthis) {
mychar.location.href=('mychar.php?c='+escape(decodeURIComponent(sendthis)));
(eval('opener.putdoins'+opener.lansel+'(decodeURIComponent(sendthis))'));
}
</script>
<a style="text-decoration:none" title="Home (u5CMS Commons)" href="characters.php?s=u5CMS Commons">&#8962;</a>
<select id="astart" name="astart" onchange="location.href='characters.php?s='+this.value">
<option>u5CMS Commons</option>
<option value="65792">AegeanNumbers</option>
<option value="128768">AlchemicalSymbols</option>
<option value="64256">AlphabeticPresentationForms</option>
<option value="119296">AncientGreekMusicalNotation</option>
<option value="65856">AncientGreekNumbers</option>
<option value="65936">AncientSymbols</option>
<option value="1536">Arabic</option>
<option value="64336">ArabicPresentationForms-A</option>
<option value="65136">ArabicPresentationForms-B</option>
<option value="1872">ArabicSupplement</option>
<option value="1328">Armenian</option>
<option value="8592">Arrows</option>
<option value="68352">Avestan</option>
<option value="6912">Balinese</option>
<option value="42656">Bamum</option>
<option value="92160">BamumSupplement</option>
<option value="7104">Batak</option>
<option value="2432">Bengali</option>
<option value="9600">BlockElements</option>
<option value="12544">Bopomofo</option>
<option value="12704">BopomofoExtended</option>
<option value="9472">BoxDrawing</option>
<option value="69632">Brahmi</option>
<option value="10240">BraillePatterns</option>
<option value="6656">Buginese</option>
<option value="5952">Buhid</option>
<option value="118784">ByzantineMusicalSymbols</option>
<option value="66208">Carian</option>
<option value="43520">Cham</option>
<option value="5024">Cherokee</option>
<option value="13056">CJKCompatibility</option>
<option value="65072">CJKCompatibilityForms</option>
<option value="63744">CJKCompatibilityIdeographs</option>
<option value="194560">CJKCompatibilityIdeographsSupplement</option>
<option value="11904">CJKRadicalsSupplement</option>
<option value="12736">CJKStrokes</option>
<option value="12288">CJKSymbols&amp;Punctuation</option>
<option value="19968">CJKUnifiedIdeographs</option>
<option value="13312">CJKUnifiedIdeographsExtensionA</option>
<option value="131072">CJKUnifiedIdeographsExtensionB</option>
<option value="173824">CJKUnifiedIdeographsExtensionC</option>
<option value="177984">CJKUnifiedIdeographsExtensionD</option>
<option value="768">CombiningDiacriticalMarks</option>
<option value="8400">CombiningDiacriticalMarksforSymbols</option>
<option value="7616">CombiningDiacriticalMarksSupplement</option>
<option value="65056">CombiningHalfMarks</option>
<option value="43056">CommonIndicNumberForms</option>
<option value="9216">ControlPictures</option>
<option value="11392">Coptic</option>
<option value="119648">CountingRodNumerals</option>
<option value="73728">Cuneiform</option>
<option value="74752">CuneiformNumbers&amp;Punctuation</option>
<option value="8352">CurrencySymbols</option>
<option value="67584">CypriotSyllabary</option>
<option value="1024">Cyrillic</option>
<option value="11744">CyrillicExtended-A</option>
<option value="42560">CyrillicExtended-B</option>
<option value="1280">CyrillicSupplement</option>
<option value="66560">Deseret</option>
<option value="2304">Devanagari</option>
<option value="43232">DevanagariExtended</option>
<option value="9984">Dingbats</option>
<option value="127024">DominoTiles</option>
<option value="77824">EgyptianHieroglyphs</option>
<option value="128512">Emoticons</option>
<option value="9312">EnclosedAlphanumerics</option>
<option value="127232">EnclosedAlphanumericSupplement</option>
<option value="12800">EnclosedCJKLetters&amp;Months</option>
<option value="127488">EnclosedIdeographicSupplement</option>
<option value="4608">Ethiopic</option>
<option value="11648">EthiopicExtended</option>
<option value="43776">EthiopicExtended-A</option>
<option value="4992">EthiopicSupplement</option>
<option value="8192">GeneralPunctuation</option>
<option value="9632">GeometricShapes</option>
<option value="4256">Georgian</option>
<option value="11520">GeorgianSupplement</option>
<option value="11264">Glagolitic</option>
<option value="66352">Gothic</option>
<option value="880">Greek&amp;Coptic</option>
<option value="7936">GreekExtended</option>
<option value="2688">Gujarati</option>
<option value="2560">Gurmukhi</option>
<option value="65280">Halfwidth&amp;FullwidthForms</option>
<option value="12592">HangulCompatibilityJamo</option>
<option value="4352">HangulJamo</option>
<option value="43360">HangulJamoExtended-A</option>
<option value="55216">HangulJamoExtended-B</option>
<option value="44032">HangulSyllables</option>
<option value="5920">Hanunoo</option>
<option value="1424">Hebrew</option>
<option value="56192">HighPrivateUseSurrogates</option>
<option value="55296">HighSurrogates</option>
<option value="12352">Hiragana</option>
<option value="12272">IdeographicDescriptionCharacters</option>
<option value="67648">ImperialAramaic</option>
<option value="68448">InscriptionalPahlavi</option>
<option value="68416">InscriptionalParthian</option>
<option value="592">IPAExtensions</option>
<option value="43392">Javanese</option>
<option value="69760">Kaithi</option>
<option value="110592">KanaSupplement</option>
<option value="12688">Kanbun</option>
<option value="12032">KangxiRadicals</option>
<option value="3200">Kannada</option>
<option value="12448">Katakana</option>
<option value="12784">KatakanaPhoneticExtensions</option>
<option value="43264">KayahLi</option>
<option value="68096">Kharoshthi</option>
<option value="6016">Khmer</option>
<option value="6624">KhmerSymbols</option>
<option value="3712">Lao</option>
<option value="33">Latin</option>
<option value="7680">LatinExtendedAdditional</option>
<option value="11360">LatinExtended-C</option>
<option value="42784">LatinExtended-D</option>
<option value="7168">Lepcha</option>
<option value="8448">LetterlikeSymbols</option>
<option value="6400">Limbu</option>
<option value="65664">LinearBIdeograms</option>
<option value="65536">LinearBSyllabary</option>
<option value="42192">Lisu</option>
<option value="56320">LowSurrogates</option>
<option value="66176">Lycian</option>
<option value="67872">Lydian</option>
<option value="126976">MahjongTiles</option>
<option value="3328">Malayalam</option>
<option value="2112">Mandaic</option>
<option value="119808">MathematicalAlphanumericSymbols</option>
<option value="8704">MathematicalOperators</option>
<option value="43968">MeeteiMayek</option>
<option value="10176">MiscellaneousMathematicalSymbols-A</option>
<option value="10624">MiscellaneousMathematicalSymbols-B</option>
<option value="9728">MiscellaneousSymbols</option>
<option value="11008">MiscellaneousSymbols&amp;Arrows</option>
<option value="127744">MiscellaneousSymbols&amp;Pictographs</option>
<option value="8960">MiscellaneousTechnical</option>
<option value="42752">ModifierToneLetters</option>
<option value="6144">Mongolian</option>
<option value="119040">MusicalSymbols</option>
<option value="4096">Myanmar</option>
<option value="43616">MyanmarExtended-A</option>
<option value="6528">NewTaiLue</option>
<option value="1984">NKo</option>
<option value="8528">NumberForms</option>
<option value="5760">Ogham</option>
<option value="7248">OlChiki</option>
<option value="66304">OldItalic</option>
<option value="66464">OldPersian</option>
<option value="68192">OldSouthArabian</option>
<option value="68608">OldTurkic</option>
<option value="9280">OpticalCharacterRecognition</option>
<option value="2816">Oriya</option>
<option value="66688">Osmanya</option>
<option value="43072">Phags-pa</option>
<option value="66000">PhaistosDisc</option>
<option value="67840">Phoenician</option>
<option value="7424">PhoneticExtensions</option>
<option value="7552">PhoneticExtensionsSupplement</option>
<option value="127136">PlayingCards</option>
<option value="57344">PrivateUseArea</option>
<option value="43312">Rejang</option>
<option value="69216">RumiNumeralSymbols</option>
<option value="5792">Runic</option>
<option value="2048">Samaritan</option>
<option value="43136">Saurashtra</option>
<option value="66640">Shavian</option>
<option value="3456">Sinhala</option>
<option value="65104">SmallFormVariants</option>
<option value="688">SpacingModifierLetters</option>
<option value="65520">Specials</option>
<option value="7040">Sundanese</option>
<option value="8304">Superscripts&amp;Subscripts</option>
<option value="10224">SupplementalArrows-A</option>
<option value="10496">SupplementalArrows-B</option>
<option value="10752">SupplementalMathematicalOperators</option>
<option value="11776">SupplementalPunctuation</option>
<option value="983040">SupplementaryPrivateUseArea-A</option>
<option value="1048576">SupplementaryPrivateUseArea-B</option>
<option value="43008">SylotiNagri</option>
<option value="1792">Syriac</option>
<option value="5888">Tagalog</option>
<option value="5984">Tagbanwa</option>
<option value="917504">Tags</option>
<option value="6480">TaiLe</option>
<option value="6688">TaiTham</option>
<option value="43648">TaiViet</option>
<option value="119552">TaiXuanJingSymbols</option>
<option value="2944">Tamil</option>
<option value="3072">Telugu</option>
<option value="1920">Thaana</option>
<option value="3584">Thai</option>
<option value="3840">Tibetan</option>
<option value="11568">Tifinagh</option>
<option value="128640">Transport&amp;MapSymbols</option>
<option value="66432">Ugaritic</option>
<option value="5120">UnifiedCanadianAboriginalSyllabics</option>
<option value="6320">UnifiedCanadianAboriginalSyllabicsExtended</option>
<option value="42240">Vai</option>
<option value="65024">VariationSelectors</option>
<option value="917760">VariationSelectorsSupplement</option>
<option value="7376">VedicExtensions</option>
<option value="65040">VerticalForms</option>
<option value="19904">YijingHexagramSymbols</option>
<option value="42128">YiRadicals</option>
<option value="40960">YiSyllables</option>
<option value="1114111">u5CMS Commons</option>
</select>


<select id="start" name="start" onchange="location.href='characters.php?s='+this.value">
<option>u5CMS Commons</option>
<option value="33">U+0000&ndash;U+024F:  Latin</option>
<option value="592">U+0250&ndash;U+02AF: IPA Extensions</option>
<option value="688">U+02B0&ndash;U+02FF: Spacing Modifier Letters</option>
<option value="768">U+0300&ndash;U+036F: Combining Diacritical Marks</option>
<option value="880">U+0370&ndash;U+03FF: Greek &amp; Coptic</option>
<option value="1024">U+0400&ndash;U+04FF: Cyrillic</option>
<option value="1280">U+0500&ndash;U+052F: Cyrillic Supplement</option>
<option value="1328">U+0530&ndash;U+058F: Armenian</option>
<option value="1424">U+0590&ndash;U+05FF: Hebrew</option>
<option value="1536">U+0600&ndash;U+06FF: Arabic</option>
<option value="1792">U+0700&ndash;U+074F: Syriac</option>
<option value="1872">U+0750&ndash;U+077F: Arabic Supplement</option>
<option value="1920">U+0780&ndash;U+07BF: Thaana</option>
<option value="1984">U+07C0&ndash;U+07FF: NKo</option>
<option value="2048">U+0800&ndash;U+083F: Samaritan</option>
<option value="2112">U+0840&ndash;U+085F: Mandaic</option>
<option value="2304">U+0900&ndash;U+097F: Devanagari</option>
<option value="2432">U+0980&ndash;U+09FF: Bengali</option>
<option value="2560">U+0A00&ndash;U+0A7F: Gurmukhi</option>
<option value="2688">U+0A80&ndash;U+0AFF: Gujarati</option>
<option value="2816">U+0B00&ndash;U+0B7F: Oriya</option>
<option value="2944">U+0B80&ndash;U+0BFF: Tamil</option>
<option value="3072">U+0C00&ndash;U+0C7F: Telugu</option>
<option value="3200">U+0C80&ndash;U+0CFF: Kannada</option>
<option value="3328">U+0D00&ndash;U+0D7F: Malayalam</option>
<option value="3456">U+0D80&ndash;U+0DFF: Sinhala</option>
<option value="3584">U+0E00&ndash;U+0E7F: Thai</option>
<option value="3712">U+0E80&ndash;U+0EFF: Lao</option>
<option value="3840">U+0F00&ndash;U+0FFF: Tibetan</option>
<option value="4096">U+1000&ndash;U+109F: Myanmar</option>
<option value="4256">U+10A0&ndash;U+10FF: Georgian</option>
<option value="4352">U+1100&ndash;U+11FF: Hangul Jamo</option>
<option value="4608">U+1200&ndash;U+137F: Ethiopic</option>
<option value="4992">U+1380&ndash;U+139F: Ethiopic Supplement</option>
<option value="5024">U+13A0&ndash;U+13FF: Cherokee</option>
<option value="5120">U+1400&ndash;U+167F: Unified Canadian Aboriginal Syllabics</option>
<option value="5760">U+1680&ndash;U+169F: Ogham</option>
<option value="5792">U+16A0&ndash;U+16FF: Runic</option>
<option value="5888">U+1700&ndash;U+171F: Tagalog</option>
<option value="5920">U+1720&ndash;U+173F: Hanunoo</option>
<option value="5952">U+1740&ndash;U+175F: Buhid</option>
<option value="5984">U+1760&ndash;U+177F: Tagbanwa</option>
<option value="6016">U+1780&ndash;U+17FF: Khmer</option>
<option value="6144">U+1800&ndash;U+18AF: Mongolian</option>
<option value="6320">U+18B0&ndash;U+18FF: Unified Canadian Aboriginal Syllabics Extended</option>
<option value="6400">U+1900&ndash;U+194F: Limbu</option>
<option value="6480">U+1950&ndash;U+197F: Tai Le</option>
<option value="6528">U+1980&ndash;U+19DF: New Tai Lue</option>
<option value="6624">U+19E0&ndash;U+19FF: Khmer Symbols</option>
<option value="6656">U+1A00&ndash;U+1A1F: Buginese</option>
<option value="6688">U+1A20&ndash;U+1AAF: Tai Tham</option>
<option value="6912">U+1B00&ndash;U+1B7F: Balinese</option>
<option value="7040">U+1B80&ndash;U+1BBF: Sundanese</option>
<option value="7104">U+1BC0&ndash;U+1BFF: Batak</option>
<option value="7168">U+1C00&ndash;U+1C4F: Lepcha</option>
<option value="7248">U+1C50&ndash;U+1C7F: Ol Chiki</option>
<option value="7376">U+1CD0&ndash;U+1CFF: Vedic Extensions</option>
<option value="7424">U+1D00&ndash;U+1D7F: Phonetic Extensions</option>
<option value="7552">U+1D80&ndash;U+1DBF: Phonetic Extensions Supplement</option>
<option value="7616">U+1DC0&ndash;U+1DFF: Combining Diacritical Marks Supplement</option>
<option value="7680">U+1E00&ndash;U+1EFF: Latin Extended Additional</option>
<option value="7936">U+1F00&ndash;U+1FFF: Greek Extended</option>
<option value="8192">U+2000&ndash;U+206F: General Punctuation</option>
<option value="8304">U+2070&ndash;U+209F: Superscripts &amp; Subscripts</option>
<option value="8352">U+20A0&ndash;U+20CF: Currency Symbols</option>
<option value="8400">U+20D0&ndash;U+20FF: Combining Diacritical Marks for Symbols</option>
<option value="8448">U+2100&ndash;U+214F: Letterlike Symbols</option>
<option value="8528">U+2150&ndash;U+218F: Number Forms</option>
<option value="8592">U+2190&ndash;U+21FF: Arrows</option>
<option value="8704">U+2200&ndash;U+22FF: Mathematical Operators</option>
<option value="8960">U+2300&ndash;U+23FF: Miscellaneous Technical</option>
<option value="9216">U+2400&ndash;U+243F: Control Pictures</option>
<option value="9280">U+2440&ndash;U+245F: Optical Character Recognition</option>
<option value="9312">U+2460&ndash;U+24FF: Enclosed Alphanumerics</option>
<option value="9472">U+2500&ndash;U+257F: Box Drawing</option>
<option value="9600">U+2580&ndash;U+259F: Block Elements</option>
<option value="9632">U+25A0&ndash;U+25FF: Geometric Shapes</option>
<option value="9728">U+2600&ndash;U+26FF: Miscellaneous Symbols</option>
<option value="9984">U+2700&ndash;U+27BF: Dingbats</option>
<option value="10176">U+27C0&ndash;U+27EF: Miscellaneous Mathematical Symbols-A</option>
<option value="10224">U+27F0&ndash;U+27FF: Supplemental Arrows-A</option>
<option value="10240">U+2800&ndash;U+28FF: Braille Patterns</option>
<option value="10496">U+2900&ndash;U+297F: Supplemental Arrows-B</option>
<option value="10624">U+2980&ndash;U+29FF: Miscellaneous Mathematical Symbols-B</option>
<option value="10752">U+2A00&ndash;U+2AFF: Supplemental Mathematical Operators</option>
<option value="11008">U+2B00&ndash;U+2BFF: Miscellaneous Symbols &amp; Arrows</option>
<option value="11264">U+2C00&ndash;U+2C5F: Glagolitic</option>
<option value="11360">U+2C60&ndash;U+2C7F: Latin Extended-C</option>
<option value="11392">U+2C80&ndash;U+2CFF: Coptic</option>
<option value="11520">U+2D00&ndash;U+2D2F: Georgian Supplement</option>
<option value="11568">U+2D30&ndash;U+2D7F: Tifinagh</option>
<option value="11648">U+2D80&ndash;U+2DDF: Ethiopic Extended</option>
<option value="11744">U+2DE0&ndash;U+2DFF: Cyrillic Extended-A</option>
<option value="11776">U+2E00&ndash;U+2E7F: Supplemental Punctuation</option>
<option value="11904">U+2E80&ndash;U+2EFF: CJK Radicals Supplement</option>
<option value="12032">U+2F00&ndash;U+2FDF: Kangxi Radicals</option>
<option value="12272">U+2FF0&ndash;U+2FFF: Ideographic Description Characters</option>
<option value="12288">U+3000&ndash;U+303F: CJK Symbols &amp; Punctuation</option>
<option value="12352">U+3040&ndash;U+309F: Hiragana</option>
<option value="12448">U+30A0&ndash;U+30FF: Katakana</option>
<option value="12544">U+3100&ndash;U+312F: Bopomofo</option>
<option value="12592">U+3130&ndash;U+318F: Hangul Compatibility Jamo</option>
<option value="12688">U+3190&ndash;U+319F: Kanbun</option>
<option value="12704">U+31A0&ndash;U+31BF: Bopomofo Extended</option>
<option value="12736">U+31C0&ndash;U+31EF: CJK Strokes</option>
<option value="12784">U+31F0&ndash;U+31FF: Katakana Phonetic Extensions</option>
<option value="12800">U+3200&ndash;U+32FF: Enclosed CJK Letters &amp; Months</option>
<option value="13056">U+3300&ndash;U+33FF: CJK Compatibility</option>
<option value="13312">U+3400&ndash;U+4DBF: CJK Unified Ideographs Extension A</option>
<option value="19904">U+4DC0&ndash;U+4DFF: Yijing Hexagram Symbols</option>
<option value="19968">U+4E00&ndash;U+9FFF: CJK Unified Ideographs</option>
<option value="40960">U+A000&ndash;U+A48F: Yi Syllables</option>
<option value="42128">U+A490&ndash;U+A4CF: Yi Radicals</option>
<option value="42192">U+A4D0&ndash;U+A4FF: Lisu</option>
<option value="42240">U+A500&ndash;U+A63F: Vai</option>
<option value="42560">U+A640&ndash;U+A69F: Cyrillic Extended-B</option>
<option value="42656">U+A6A0&ndash;U+A6FF: Bamum</option>
<option value="42752">U+A700&ndash;U+A71F: Modifier Tone Letters</option>
<option value="42784">U+A720&ndash;U+A7FF: Latin Extended-D</option>
<option value="43008">U+A800&ndash;U+A82F: Syloti Nagri</option>
<option value="43056">U+A830&ndash;U+A83F: Common Indic Number Forms</option>
<option value="43072">U+A840&ndash;U+A87F: Phags-pa</option>
<option value="43136">U+A880&ndash;U+A8DF: Saurashtra</option>
<option value="43232">U+A8E0&ndash;U+A8FF: Devanagari Extended</option>
<option value="43264">U+A900&ndash;U+A92F: Kayah Li</option>
<option value="43312">U+A930&ndash;U+A95F: Rejang</option>
<option value="43360">U+A960&ndash;U+A97F: Hangul Jamo Extended-A</option>
<option value="43392">U+A980&ndash;U+A9DF: Javanese</option>
<option value="43520">U+AA00&ndash;U+AA5F: Cham</option>
<option value="43616">U+AA60&ndash;U+AA7F: Myanmar Extended-A</option>
<option value="43648">U+AA80&ndash;U+AADF: Tai Viet</option>
<option value="43776">U+AB00&ndash;U+AB2F: Ethiopic Extended-A</option>
<option value="43968">U+ABC0&ndash;U+ABFF: Meetei Mayek</option>
<option value="44032">U+AC00&ndash;U+D7AF: Hangul Syllables</option>
<option value="55216">U+D7B0&ndash;U+D7FF: Hangul Jamo Extended-B</option>
<option value="55296">U+D800&ndash;U+DB7F: High Surrogates</option>
<option value="56192">U+DB80&ndash;U+DBFF: High Private Use Surrogates</option>
<option value="56320">U+DC00&ndash;U+DFFF: Low Surrogates</option>
<option value="57344">U+E000&ndash;U+F8FF: Private Use Area</option>
<option value="63744">U+F900&ndash;U+FAFF: CJK Compatibility Ideographs</option>
<option value="64256">U+FB00&ndash;U+FB4F: Alphabetic Presentation Forms</option>
<option value="64336">U+FB50&ndash;U+FDFF: Arabic Presentation Forms-A</option>
<option value="65024">U+FE00&ndash;U+FE0F: Variation Selectors</option>
<option value="65040">U+FE10&ndash;U+FE1F: Vertical Forms</option>
<option value="65056">U+FE20&ndash;U+FE2F: Combining Half Marks</option>
<option value="65072">U+FE30&ndash;U+FE4F: CJK Compatibility Forms</option>
<option value="65104">U+FE50&ndash;U+FE6F: Small Form Variants</option>
<option value="65136">U+FE70&ndash;U+FEFF: Arabic Presentation Forms-B</option>
<option value="65280">U+FF00&ndash;U+FFEF: Halfwidth &amp; Fullwidth Forms</option>
<option value="65520">U+FFF0&ndash;U+FFFF: Specials</option>
<option value="65536">U+10000&ndash;U+1007F: Linear B Syllabary</option>
<option value="65664">U+10080&ndash;U+100FF: Linear B Ideograms</option>
<option value="65792">U+10100&ndash;U+1013F: Aegean Numbers</option>
<option value="65856">U+10140&ndash;U+1018F: Ancient Greek Numbers</option>
<option value="65936">U+10190&ndash;U+101CF: Ancient Symbols</option>
<option value="66000">U+101D0&ndash;U+101FF: Phaistos Disc</option>
<option value="66176">U+10280&ndash;U+1029F: Lycian</option>
<option value="66208">U+102A0&ndash;U+102DF: Carian</option>
<option value="66304">U+10300&ndash;U+1032F: Old Italic</option>
<option value="66352">U+10330&ndash;U+1034F: Gothic</option>
<option value="66432">U+10380&ndash;U+1039F: Ugaritic</option>
<option value="66464">U+103A0&ndash;U+103DF: Old Persian</option>
<option value="66560">U+10400&ndash;U+1044F: Deseret</option>
<option value="66640">U+10450&ndash;U+1047F: Shavian</option>
<option value="66688">U+10480&ndash;U+104AF: Osmanya</option>
<option value="67584">U+10800&ndash;U+1083F: Cypriot Syllabary</option>
<option value="67648">U+10840&ndash;U+1085F: Imperial Aramaic</option>
<option value="67840">U+10900&ndash;U+1091F: Phoenician</option>
<option value="67872">U+10920&ndash;U+1093F: Lydian</option>
<option value="68096">U+10A00&ndash;U+10A5F: Kharoshthi</option>
<option value="68192">U+10A60&ndash;U+10A7F: Old South Arabian</option>
<option value="68352">U+10B00&ndash;U+10B3F: Avestan</option>
<option value="68416">U+10B40&ndash;U+10B5F: Inscriptional Parthian</option>
<option value="68448">U+10B60&ndash;U+10B7F: Inscriptional Pahlavi</option>
<option value="68608">U+10C00&ndash;U+10C4F: Old Turkic</option>
<option value="69216">U+10E60&ndash;U+10E7F: Rumi Numeral Symbols</option>
<option value="69632">U+11000&ndash;U+1107F: Brahmi</option>
<option value="69760">U+11080&ndash;U+110CF: Kaithi</option>
<option value="73728">U+12000&ndash;U+123FF: Cuneiform</option>
<option value="74752">U+12400&ndash;U+1247F: Cuneiform Numbers &amp; Punctuation</option>
<option value="77824">U+13000&ndash;U+1342F: Egyptian Hieroglyphs</option>
<option value="92160">U+16800&ndash;U+16A3F: Bamum Supplement</option>
<option value="110592">U+1B000&ndash;U+1B0FF: Kana Supplement</option>
<option value="118784">U+1D000&ndash;U+1D0FF: Byzantine Musical Symbols</option>
<option value="119040">U+1D100&ndash;U+1D1FF: Musical Symbols</option>
<option value="119296">U+1D200&ndash;U+1D24F: Ancient Greek Musical Notation</option>
<option value="119552">U+1D300&ndash;U+1D35F: Tai Xuan Jing Symbols</option>
<option value="119648">U+1D360&ndash;U+1D37F: Counting Rod Numerals</option>
<option value="119808">U+1D400&ndash;U+1D7FF: Mathematical Alphanumeric Symbols</option>
<option value="126976">U+1F000&ndash;U+1F02F: Mahjong Tiles</option>
<option value="127024">U+1F030&ndash;U+1F09F: Domino Tiles</option>
<option value="127136">U+1F0A0&ndash;U+1F0FF: Playing Cards</option>
<option value="127232">U+1F100&ndash;U+1F1FF: Enclosed Alphanumeric Supplement</option>
<option value="127488">U+1F200&ndash;U+1F2FF: Enclosed Ideographic Supplement</option>
<option value="127744">U+1F300&ndash;U+1F5FF: Miscellaneous Symbols &amp; Pictographs</option>
<option value="128512">U+1F600&ndash;U+1F64F: Emoticons</option>
<option value="128640">U+1F680&ndash;U+1F6FF: Transport &amp; Map Symbols</option>
<option value="128768">U+1F700&ndash;U+1F77F: Alchemical Symbols</option>
<option value="131072">U+20000&ndash;U+2A6DF: CJK Unified Ideographs Extension B</option>
<option value="173824">U+2A700&ndash;U+2B73F: CJK Unified Ideographs Extension C</option>
<option value="177984">U+2B740&ndash;U+2B81F: CJK Unified Ideographs Extension D</option>
<option value="194560">U+2F800&ndash;U+2FA1F: CJK Compatibility Ideographs Supplement</option>
<option value="917504">U+E0000&ndash;U+E007F: Tags</option>
<option value="917760">U+E0100&ndash;U+E01EF: Variation Selectors Supplement</option>
<option value="983040">U+F0000&ndash;U+FFFFF: Supplementary Private Use Area-A</option>
<option value="1048576">U+100000&ndash;U+10FFFF: Supplementary Private Use Area-B</option>
<option value="1114111">u5CMS Commons</option>
</select>
<hr>
<?php if ($_GET['s']>0) { ?>
<script>
inh=parseInt(document.getElementById('start').innerHTML.split('<option value="<?php echo $_GET['s']?>">')[1].split('</option>')[1].split('value="')[1]);
document.getElementById('start').value='<?php echo $_GET['s'] ?>';
document.getElementById('astart').value='<?php echo $_GET['s'] ?>';
<?php if (!$_GET['e']>0)  echo "location.href='characters.php?s=".$_GET['s']."&e='+inh";?>
//alert(escape(inh));
</script>
<?php } ?>


<?php 
if ($_GET['s']>0 && $_GET['e']>0) { 

for($i=$_GET['s'];$i<=$_GET['e'];$i++) {
$ucrs.=uchr($i).',';	
}

}

else {

$ucrs0=',<!><hr>,';

$ucrs1='¼,½,¾,⅐,⅑,⅒,⅓,⅔,⅕,⅖,⅗,⅘,⅙,⅚,⅛,⅜,⅝,⅞,⅟,Ⅰ,Ⅱ,Ⅲ,Ⅳ,Ⅴ,Ⅵ,Ⅶ,Ⅷ,Ⅸ,Ⅹ,Ⅺ,Ⅻ,Ⅼ,Ⅽ,Ⅾ,Ⅿ,ⅰ,ⅱ,ⅲ,ⅳ,ⅴ,ⅵ,ⅶ,ⅷ,ⅸ,ⅹ,ⅺ,ⅻ,ⅼ,ⅽ,ⅾ,ⅿ,ↀ,ↁ,ↂ,Ↄ,ↄ,ↅ,ↆ,ↇ,ↈ,↉,↊,↋,↌,↍,↎,↏,℀,℁,ℂ,℃,℄,℅,℆,ℇ,℈,℉,ℊ,ℋ,ℌ,ℍ,ℎ,ℏ,ℐ,ℑ,ℒ,ℓ,℔,ℕ,№,℗,℘,ℙ,ℚ,ℛ,ℜ,ℝ,℞,℟,℠,℡,™,℣,ℤ,℥,Ω,℧,ℨ,℩,K,Å,ℬ,ℭ,℮,ℯ,ℰ,ℱ,Ⅎ,ℳ,ℴ,ℵ,ℶ,ℷ,ℸ,ℹ,℺,℻,ℼ,ℽ,ℾ,ℿ,⅀,⅁,⅂,⅃,⅄,ⅅ,ⅆ,ⅇ,ⅈ,ⅉ,⅊,⅋,⅌,⅍,ⅎ';


$ucrs2='←,↑,→,↓,↔,↕,↖,↗,↘,↙,↚,↛,↜,↝,↞,↟,↠,↡,↢,↣,↤,↥,↦,↧,↨,↩,↪,↫,↬,↭,↮,↯,↰,↱,↲,↳,↴,↵,↶,↷,↸,↹,↺,↻,↼,↽,↾,↿,⇀,⇁,⇂,⇃,⇄,⇅,⇆,⇇,⇈,⇉,⇊,⇋,⇌,⇍,⇎,⇏,⇐,⇑,⇒,⇓,⇔,⇕,⇖,⇗,⇘,⇙,⇚,⇛,⇜,⇝,⇞,⇟,⇠,⇡,⇢,⇣,⇤,⇥,⇦,⇧,⇨,⇩,⇪,⇫,⇬,⇭,⇮,⇯,⇰,⇱,⇲,⇳,⇴,⇵,⇶,⇷,⇸,⇹,⇺,⇻,⇼,⇽,⇾,⇿,✀,✁,✂,✃,✄,✅,✆,✇,✈,✉,✊,✋,✌,✍,✎,✏,✐,✑,✒,✓,✔,✕,✖,✗,✘,✙,✚,✛,✜,✝,✞,✟,✠,✡,✢,✣,✤,✥,✦,✧,✨,✩,✪,✫,✬,✭,✮,✯,✰,✱,✲,✳,✴,✵,✶,✷,✸,✹,✺,✻,✼,✽,✾,✿,❀,❁,❂,❃,❄,❅,❆,❇,❈,❉,❊,❋,❌,❍,❎,❏,❐,❑,❒,❓,❔,❕,❖,❗,❘,❙,❚,❛,❜,❝,❞,❟,❠,❡,❢,❣,❤,❥,❦,❧,❨,❩,❪,❫,❬,❭,❮,❯,❰,❱,❲,❳,❴,❵,❶,❷,❸,❹,❺,❻,❼,❽,❾,❿,➀,➁,➂,➃,➄,➅,➆,➇,➈,➉,➊,➋,➌,➍,➎,➏,➐,➑,➒,➓,➔,➕,➖,➗,➘,➙,➚,➛,➜,➝,➞,➟,➠,➡,➢,➣,➤,➥,➦,➧,➨,➩,➪,➫,➬,➭,➮,➯,➰,➱,➲,➳,➴,➵,➶,➷,➸,➹,➺,➻,➼,➽,➾,➿,①,②,③,④,⑤,⑥,⑦,⑧,⑨,⑩,⑪,⑫,⑬,⑭,⑮,⑯,⑰,⑱,⑲,⑳,⑴,⑵,⑶,⑷,⑸,⑹,⑺,⑻,⑼,⑽,⑾,⑿,⒀,⒁,⒂,⒃,⒄,⒅,⒆,⒇,⒈,⒉,⒊,⒋,⒌,⒍,⒎,⒏,⒐,⒑,⒒,⒓,⒔,⒕,⒖,⒗,⒘,⒙,⒚,⒛,⒜,⒝,⒞,⒟,⒠,⒡,⒢,⒣,⒤,⒥,⒦,⒧,⒨,⒩,⒪,⒫,⒬,⒭,⒮,⒯,⒰,⒱,⒲,⒳,⒴,⒵,Ⓐ,Ⓑ,Ⓒ,Ⓓ,Ⓔ,Ⓕ,Ⓖ,Ⓗ,Ⓘ,Ⓙ,Ⓚ,Ⓛ,Ⓜ,Ⓝ,Ⓞ,Ⓟ,Ⓠ,Ⓡ,Ⓢ,Ⓣ,Ⓤ,Ⓥ,Ⓦ,Ⓧ,Ⓨ,Ⓩ,ⓐ,ⓑ,ⓒ,ⓓ,ⓔ,ⓕ,ⓖ,ⓗ,ⓘ,ⓙ,ⓚ,ⓛ,ⓜ,ⓝ,ⓞ,ⓟ,ⓠ,ⓡ,ⓢ,ⓣ,ⓤ,ⓥ,ⓦ,ⓧ,ⓨ,ⓩ,⓪,⓫,⓬,⓭,⓮,⓯,⓰,⓱,⓲,⓳,⓴,⓵,⓶,⓷,⓸,⓹,⓺,⓻,⓼,⓽,⓾,⓿,─,━,│,┃,┄,┅,┆,┇,┈,┉,┊,┋,┌,┍,┎,┏,┐,┑,┒,┓,└,┕,┖,┗,┘,┙,┚,┛,├,┝,┞,┟,┠,┡,┢,┣,┤,┥,┦,┧,┨,┩,┪,┫,┬,┭,┮,┯,┰,┱,┲,┳,┴,┵,┶,┷,┸,┹,┺,┻,┼,┽,┾,┿,╀,╁,╂,╃,╄,╅,╆,╇,╈,╉,╊,╋,╌,╍,╎,╏,═,║,╒,╓,╔,╕,╖,╗,╘,╙,╚,╛,╜,╝,╞,╟,╠,╡,╢,╣,╤,╥,╦,╧,╨,╩,╪,╫,╬,╭,╮,╯,╰,╱,╲,╳,╴,╵,╶,╷,╸,╹,╺,╻,╼,╽,╾,╿,▀,▁,▂,▃,▄,▅,▆,▇,█,▉,▊,▋,▌,▍,▎,▏,▐,░,▒,▓,▔,▕,▖,▗,▘,▙,▚,▛,▜,▝,▞,▟,■,□,▢,▣,▤,▥,▦,▧,▨,▩,▪,▫,▬,▭,▮,▯,▰,▱,▲,△,▴,▵,▶,▷,▸,▹,►,▻,▼,▽,▾,▿,◀,◁,◂,◃,◄,◅,◆,◇,◈,◉,◊,○,◌,◍,◎,●,◐,◑,◒,◓,◔,◕,◖,◗,◘,◙,◚,◛,◜,◝,◞,◟,◠,◡,◢,◣,◤,◥,◦,◧,◨,◩,◪,◫,◬,◭,◮,◯,◰,◱,◲,◳,◴,◵,◶,◷,◸,◹,◺,◻,◼,◽,◾,◿,☀,☁,☂,☃,☄,★,☆,☇,☈,☉,☊,☋,☌,☍,☎,☏,☐,☑,☒,☓,☔,☕,☖,☗,☘,☙,☚,☛,☜,☝,☞,☟,☠,☡,☢,☣,☤,☥,☦,☧,☨,☩,☪,☫,☬,☭,☮,♰,♱,♲,♳,♴,♵,♶,♷,♸,♹,♺,♻,♼,♽,♾,♿,⚀,⚁,⚂,⚃,⚄,⚅,⚆,⚇,⚈,⚉,⚊,⚋,⚌,⚍,⚎,⚏,⚐,⚑,⚒,⚓,⚔,⚕,⚖,⚗,⚘,⚙,⚚,⚛,⚜,⚝,⚞,⚟,⚠,⚡,⚢,⚣,⚤,⚥,⚦,⚧,⚨,⚩,⚪,⚫,⚬,⚭,⚮,⚯,⚰,⚱,⚲,⚳,⚴,⚵,⚶,⚷,⚸,⚹,⚺,⚻,⚼,⚽,⚾,⚿,⛀,⛁,⛂,⛃,⛄,⛅,⛆,⛇,⛈,⛉,⛊,⛋,⛌,⛍,⛎,⛏,⛐,⛑,⛒,⛓,⛔,⛕,⛖,⛗,⛘,⛙,⛚,⛛,⛜,⛝,⛞,⛟,⛠,⛡,⛢,⛣,⛤,⛥,⛦,⛧,⛨,⛩,⛪,⛫,⛬,⛭,⛮,⛯,⛰,⛱,⛲,⛳,⛴,⛵,⛶,⛷,⛸,⛹,⛺,⛻,⛼,⛽,⛾,⛿,☯,☰,☱,☲,☳,☴,☵,☶,☷,☸,☹,☺,☻,☼,☽,☾,☿,♀,♁,♂,♃,♄,♅,♆,♇,♈,♉,♊,♋,♌,♍,♎,♏,♐,♑,♒,♓,♔,♕,♖,♗,♘,♙,♚,♛,♜,♝,♞,♟,♠,♡,♢,♣,♤,♥,♦,♧,♨,♩,♪,♫,♬,♭,♮,♯';


$ucrs3='±,×,÷,∃,ƒ,∀,∁,∂,∃,∄,∅,∆,∇,∈,∉,∊,∋,∌,∍,∎,∏,∐,∑,−,∓,∔,∕,∖,∗,∘,∙,√,∛,∜,∝,∞,∟,∠,∡,∢,∣,∤,∥,∦,∧,∨,∩,∪,∫,∬,∭,∮,∯,∰,∱,∲,∳,∴,∵,∶,∷,∸,∹,∺,∻,∼,∽,∾,∿,≀,≁,≂,≃,≄,≅,≆,≇,≈,≉,≊,≋,≌,≍,≎,≏,≐,≑,≒,≓,≔,≕,≖,≗,≘,≙,≚,≛,≜,≝,≞,≟,≠,≡,≢,≣,≤,≥,≦,≧,≨,≩,≪,≫,≬,≭,≮,≯,≰,≱,≲,≳,≴,≵,≶,≷,≸,≹,≺,≻,≼,≽,≾,≿,⊀,⊁,⊂,⊃,⊄,⊅,⊆,⊇,⊈,⊉,⊊,⊋,⊌,⊍,⊎,⊏,⊐,⊑,⊒,⊓,⊔,⊕,⊖,⊗,⊘,⊙,⊚,⊛,⊜,⊝,⊞,⊟,⊠,⊡,⊢,⊣,⊤,⊥,⊦,⊧,⊨,⊩,⊪,⊫,⊬,⊭,⊮,⊯,⊰,⊱,⊲,⊳,⊴,⊵,⊶,⊷,⊸,⊹,⊺,⊻,⊼,⊽,⊾,⊿,⋀,⋁,⋂,⋃,⋄,⋅,⋆,⋇,⋈,⋉,⋊,⋋,⋌,⋍,⋎,⋏,⋐,⋑,⋒,⋓,⋔,⋕,⋖,⋗,⋘,⋙,⋚,⋛,⋜,⋝,⋞,⋟,⋠,⋡,⋢,⋣,⋤,⋥,⋦,⋧,⋨,⋩,⋪,⋫,⋬,⋭,⋮,⋯,⋰,⋱,⋲,⋳,⋴,⋵,⋶,⋷,⋸,⋹,⋺,⋻,⋼,⋽,⋾,⋿,⌀,⌁,⌂,⌃,⌄,⌅,⌆,⌇,⌈,⌉,⌊,⌋,⌌,⌍,⌎,⌏,⌐,⌑,⌒,⌓,⌔,⌕,⌖,⌗,⌘,⌙,⌚,⌛,⌜,⌝,⌞,⌟,⌠,⌡,⌢,⌣,⌤,⌥,⌦,⌧,⌨,〈,〉,⌫,⌬,⌭,⌮,⌯,⌰,⌱,⌲,⌳,⌴,⌵,⌶,⌷,⌸,⌹,⌺,⌻,⌼,⌽,⌾,⌿,⍀,⍁,⍂,⍃,⍄,⍅,⍆,⍇,⍈,⍉,⍊,⍋,⍌,⍍,⍎,⍏,⍐,⍑,⍒,⍓,⍔,⍕,⍖,⍗,⍘,⍙,⍚,⍛,⍜,⍝,⍞,⍟,⍠,⍡,⍢,⍣,⍤,⍥,⍦,⍧,⍨,⍩,⍪,⍫,⍬,⍭,⍮,⍯,⍰,⍱,⍲,⍳,⍴,⍵,⍶,⍷,⍸,⍹,⍺,⍻,⍼,⍽,⍾,⍿,⎀,⎁,⎂,⎃,⎄,⎅,⎆,⎇,⎈,⎉,⎊,⎋,⎌,⎍,⎎,⎏,⎐,⎑,⎒,⎓,⎔,⎕,⎖,⎗,⎘,⎙,⎚,⎛,⎜,⎝,⎞,⎟,⎠,⎡,⎢,⎣,⎤,⎥,⎦,⎧,⎨,⎩,⎪,⎫,⎬,⎭,⎮,⎯,⎰,⎱,⎲,⎳,⎴,⎵,⎶,⎷,⎸,⎹,⎺,⎻,⎼,⎽,⎾,⎿,⏀,⏁,⏂,⏃,⏄,⏅,⏆,⏇,⏈,⏉,⏊,⏋,⏌,⏍,⏎,⏏,⏐,⏑,⏒,⏓,⏔,⏕,⏖,⏗,⏘,⏙,⏚,⏛,⏜,⏝,⏞,⏟,⏠,⏡,⏢,⏣,⏤,⏥,⏦,⏧,⏨,⏩,⏪,⏫,⏬,⏭,⏮,⏯,⏰,⏱,⏲,⏳,⏴,⏵,⏶,⏷,⏸,⏹,⏺,⏻,⏼,⏽,⏾,⏿,␀,␁,␂,␃,␄,␅,␆,␇,␈,␉,␊,␋,␌,␍,␎,␏,␐,␑,␒,␓,␔,␕,␖,␗,␘,␙,␚,␛,␜,␝,␞,␟,␠,␡,␢,␣,␤,␥,␦,␧,␨,␩,␪,␫,␬,␭,␮,␯,␰,␱,␲,␳,␴,␵,␶,␷,␸,␹,␺,␻,␼,␽,␾,␿';


$ucrs4='⁰,¹,²,³,ⁱ,⁴,⁵,⁶,⁷,⁸,⁹,⁺,⁻,⁼,⁽,⁾,ⁿ,₀,₁,₂,₃,₄,₅,₆,₇,₈,₉,₊,₋,₌,₍,₎,ᵃ,ᵇ,ᶜ,ᵈ,ᵉ,ᶠ,ᵍ,ʰ,ⁱ,ʲ,ᵏ,ˡ,ᵐ,ⁿ,ᵒ,ᵖ,ʳ,ˢ,ᵗ,ᵘ,ᵛ,ʷ,ˣ,ʸ,ᶻ,ᴬ,ᴮ,ᴰ,ᴱ,ᴳ,ᴴ,ᴵ,ᴶ,ᴷ,ᴸ,ᴹ,ᴺ,ᴼ,ᴾ,ᴿ,ᵀ,ᵁ,ⱽ,ᵂ,ₐ,ₑ,ₕ,ᵢ,ⱼ,ₖ,ₗ,ₘ,ₙ,ₒ,ₚ,ᵣ,ₛ,ₜ,ᵤ,ᵥ,ₓ,ᵅ,ᵝ,ᵞ,ᵟ,ᵋ,ᶿ,ᶥ,ᶲ,ᵠ,ᵡ,ᵦ,ᵧ,ᵨ,ᵩ,ᵪ,α,β,γ,δ,ε,ζ,η,θ,ι,κ,λ,μ,ν,ξ,ο,π,ρ,σ,ς,τ,υ,φ,χ,ψ,ω,Α,Β,Γ,Δ,Ε,Ζ,Η,Θ,Ι,Κ,Λ,Μ,Ν,Ξ,Ο,Π,Ρ,Σ,Τ,Υ,Φ,Χ,Ψ,Ω';


$ucrs5='!,",#,$,%,&,\',(,),*,+,-,.,/,0,1,2,3,4,5,6,7,8,9,:,;,<,=,>,?,@,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,[,\,],^,_,`,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,{,|,},~,¡,¢,£,¤,¥,¦,§,¨,©,ª,«,¬,®,¯,°,±,²,³,´,µ,¶,·,¸,¹,º,»,¼,½,¾,¿,À,Á,Â,Ã,Ä,Å,Æ,Ç,È,É,Ê,Ë,Ì,Í,Î,Ï,Ð,Ñ,Ò,Ó,Ô,Õ,Ö,×,Ø,Ù,Ú,Û,Ü,Ý,Þ,ß,à,á,â,ã,ä,å,æ,ç,è,é,ê,ë,ì,í,î,ï,ð,ñ,ò,ó,ô,õ,ö,÷,ø,ù,ú,û,ü,ý,þ,ÿ,Ā,ā,Ă,ă,Ą,ą,Ć,ć,Ĉ,ĉ,Ċ,ċ,Č,č,Ď,ď,Đ,đ,Ē,ē,Ĕ,ĕ,Ė,ė,Ę,ę,Ě,ě,Ĝ,ĝ,Ğ,ğ,Ġ,ġ,Ģ,ģ,Ĥ,ĥ,Ħ,ħ,Ĩ,ĩ,Ī,ī,Ĭ,ĭ,Į,į,İ,ı,Ĳ,ĳ,Ĵ,ĵ,Ķ,ķ,ĸ,Ĺ,ĺ,Ļ,ļ,Ľ,ľ,Ŀ,ŀ,Ł,ł,Ń,ń,Ņ,ņ,Ň,ň,ŉ,Ŋ,ŋ,Ō,ō,Ŏ,ŏ,Ő,ő,Œ,œ,Ŕ,ŕ,Ŗ,ŗ,Ř,ř,Ś,ś,Ŝ,ŝ,Ş,ş,Š,š,Ţ,ţ,Ť,ť,Ŧ,ŧ,Ũ,ũ,Ū,ū,Ŭ,ŭ,Ů,ů,Ű,ű,Ų,ų,Ŵ,ŵ,Ŷ,ŷ,Ÿ,Ź,ź,Ż,ż,Ž,ž,ſ,ƀ,Ɓ,Ƃ,ƃ,Ƅ,ƅ,Ɔ,Ƈ,ƈ,Ɖ,Ɗ,Ƌ,ƌ,ƍ,Ǝ,Ə,Ɛ,Ƒ,ƒ,Ɠ,Ɣ,ƕ,Ɩ,Ɨ,Ƙ,ƙ,ƚ,ƛ,Ɯ,Ɲ,ƞ,Ɵ,Ơ,ơ,Ƣ,ƣ,Ƥ,ƥ,Ʀ,Ƨ,ƨ,Ʃ,ƪ,ƫ,Ƭ,ƭ,Ʈ,Ư,ư,Ʊ,Ʋ,Ƴ,ƴ,Ƶ,ƶ,Ʒ,Ƹ,ƹ,ƺ,ƻ,Ƽ,ƽ,ƾ,ƿ,ǀ,ǁ,ǂ,ǃ,Ǆ,ǅ,ǆ,Ǉ,ǈ,ǉ,Ǌ,ǋ,ǌ,Ǎ,ǎ,Ǐ,ǐ,Ǒ,ǒ,Ǔ,ǔ,Ǖ,ǖ,Ǘ,ǘ,Ǚ,ǚ,Ǜ,ǜ,ǝ,Ǟ,ǟ,Ǡ,ǡ,Ǣ,ǣ,Ǥ,ǥ,Ǧ,ǧ,Ǩ,ǩ,Ǫ,ǫ,Ǭ,ǭ,Ǯ,ǯ,ǰ,Ǳ,ǲ,ǳ,Ǵ,ǵ,Ƕ,Ƿ,Ǹ,ǹ,Ǻ,ǻ,Ǽ,ǽ,Ǿ,ǿ,Ȁ,ȁ,Ȃ,ȃ,Ȅ,ȅ,Ȇ,ȇ,Ȉ,ȉ,Ȋ,ȋ,Ȍ,ȍ,Ȏ,ȏ,Ȑ,ȑ,Ȓ,ȓ,Ȕ,ȕ,Ȗ,ȗ,Ș,ș,Ț,ț,Ȝ,ȝ,Ȟ,ȟ,Ƞ,ȡ,Ȣ,ȣ,Ȥ,ȥ,Ȧ,ȧ,Ȩ,ȩ,Ȫ,ȫ,Ȭ,ȭ,Ȯ,ȯ,Ȱ,ȱ,Ȳ,ȳ,ȴ,ȵ,ȶ,ȷ,ȸ,ȹ,Ⱥ,Ȼ,ȼ,Ƚ,Ⱦ,ȿ,ɀ,Ɂ,ɂ,Ƀ,Ʉ,Ʌ,Ɇ,ɇ,Ɉ,ɉ,Ɋ,ɋ,Ɍ,ɍ,Ɏ,ɏ,ɐ,ɑ,ɒ,ɓ,ɔ,ɕ,ɖ,ɗ,ɘ,ə,ɚ,ɛ,ɜ,ɝ,ɞ,ɟ,ɠ,ɡ,ɢ,ɣ,ɤ,ɥ,ɦ,ɧ,ɨ,ɩ,ɪ,ɫ,ɬ,ɭ,ɮ,ɯ,ɰ,ɱ,ɲ,ɳ,ɴ,ɵ,ɶ,ɷ,ɸ,ɹ,ɺ,ɻ,ɼ,ɽ,ɾ,ɿ,ʀ,ʁ,ʂ,ʃ,ʄ,ʅ,ʆ,ʇ,ʈ,ʉ,ʊ,ʋ,ʌ,ʍ,ʎ,ʏ,ʐ,ʑ,ʒ,ʓ,ʔ,ʕ,ʖ,ʗ,ʘ,ʙ,ʚ,ʛ,ʜ,ʝ,ʞ,ʟ,ʠ,ʡ,ʢ,ʣ,ʤ,ʥ,ʦ,ʧ,ʨ,ʩ,ʪ,ʫ,ʬ,ʭ,ʮ,ʯ,ʰ,ʱ,ʲ,ʳ,ʴ,ʵ,ʶ,ʷ,ʸ,ʹ,ʺ,ʻ,ʼ,ʽ,ʾ,ʿ,ˀ,ˁ,˂,˃,˄,˅,ˆ,ˇ,ˈ,ˉ,ˊ,ˋ,ˌ,ˍ,ˎ,ˏ,ː,ˑ,˒,˓,˔,˕,˖,˗,˘,˙,˚,˛,˜,˝,˞,˟,ˠ,ˡ,ˢ,ˣ,ˤ,˥,˦,˧,˨,˩,˪,˫,ˬ,˭,ˮ,˯,˰,˱,˲,˳,˴,˵,˶,˷,˸,˹,˺,˻,˼,˽,˾,˿';


$ucrs6='<!><span style="font-size:60%">minus</span>,−,<!><span style="font-size:60%">hyphen</span>,‐,<!><span style="font-size:60%">non&nbsp;breaking&nbsp;hyphen</span>,‑,<!><span style="font-size:60%">figure&nbsp;dash</span>,‒,<!><span style="font-size:60%">en&nbsp;dash</span>,–,<!><span style="font-size:60%">em&nbsp;dash</span>,—,<!><span style="font-size:60%">horizontal&nbsp;bar</span>,―,<!><span style="font-size:60%">double&nbsp;hyphen</span>,⸗,‖,‗,‘,’,‚,‛,“,”,„,‟,†,‡,•,‣,․,‥,…,‧,‰,‱,′,″,‴,‵,‶,‷,‸,‹,›,※,‼,‽,‾,‿,⁀,⁁,⁂,⁃,⁄,⁅,⁆,⁇,⁈,⁉,⁊,⁋,⁌,⁍,⁎,⁏,⁐,⁑,⁒,⁓,⁔,⁕,⁖,⁗,⁘,⁙,⁚,⁛,⁜,⁝,⁞,⁠,⁡,⁢,⁣,⁤,⁥,⁦,⁧,⁨,⁩,︰,﹅,﹆,﹉,﹊,﹋,﹌,﹐,﹑,﹒,﹔,﹕,﹖,﹗,﹟,﹠,﹡,﹨,﹪,﹫,！,＂,＃,％,＆,＇,＊,，,．,／,：,；,？,＠,＼,｡,､,･';


$ucrs7='<!><span style="font-size:60%">Space</span><span style="background:yellow;font-size:150%">,&nbsp;,<!><span style="font-size:60%">Tab</span><span style="background:yellow;font-size:150%">,	,<!><span style="font-size:60%">No‑Break&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Ogham&nbsp;Space&nbsp;Mark</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">En&nbsp;Space&nbsp;or&nbsp;Nut</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Em&nbsp;Space&nbsp;or&nbsp;Mutton</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Three‑Per‑Em&nbsp;Space&nbsp;or&nbsp;Thick&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Four‑Per‑Em&nbsp;Space&nbsp;or&nbsp;Mid&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Six‑Per‑Em&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Figure&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Punctuation&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Thin&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Hair&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Zero‑Width&nbsp;Space&nbsp;(remove&nbsp;<span style="font-size:200%">⌜</span>+<span style="font-size:200%">⌟</span>)</span><span style="background:yellow;font-size:150%">,⌜​⌟,<!><span style="font-size:60%">Narrow&nbsp;No‑Break&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Medium&nbsp;Mathematical&nbsp;Space</span><span style="background:yellow;font-size:150%">, ,<!><span style="font-size:60%">Word&nbsp;Joiner&nbsp;(remove&nbsp;<span style="font-size:200%">⌜</span>+<span style="font-size:200%">⌟</span>)</span></span><span style="background:yellow;font-size:150%">,⌜⁠⌟,<!><span style="font-size:60%">Ideographic&nbsp;Space</span><span style="background:yellow;font-size:150%">,　';

$ucrs=$ucrs6.$ucrs0.$ucrs4.$ucrs0.$ucrs1.$ucrs0.$ucrs2.$ucrs0.$ucrs3.$ucrs0.$ucrs5.$ucrs0.$ucrs7;
}

function uchr ($codes) {
    if (is_scalar($codes)) $codes= func_get_args();
    $str= '';
    foreach ($codes as $code) $str.= html_entity_decode('&#'.$code.';',ENT_NOQUOTES,'UTF-8');
    return $str;
}
?>

<?php 
$xc=explode(',',$ucrs);
for ($xi=0;$xi<tnuoc($xc);$xi++) {
if (strpos($xc[$xi],'!')==1) echo $xc[$xi];
else {
$xc[$xi]=rawurlencode($xc[$xi]);
echo '<a style="cursor:pointer;color:blue" title="'.rawurldecode($xc[$xi]).' '.mymb_ord(str_replace('⌜','',rawurldecode($xc[$xi]))).' ('.dechex(mymb_ord(str_replace('⌜','',rawurldecode($xc[$xi])))).')" onclick="senddoins(\''.$xc[$xi].'\')"><script>document.write(decodeURIComponent(\''.$xc[$xi].'\'))</script></a>';
echo '</span>';
echo '<span style="font-size:90%"> </span><span style="color:white;font-weight:bold">|</span><span style="font-size:90%"> </span>'; 
}}


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
</div>
</body>
</html>
