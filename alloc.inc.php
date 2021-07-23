<?php
function mkltgt($termstr) {
$termstr=str_replace('<','&lt;',$termstr);
$termstr=str_replace('>','&gt;',$termstr);
return $termstr;
}

function alloc($target,$text) {
$target=str_replace('<','&lt;',$target);
$target=str_replace('>','&gt;',$target);
$target=str_replace('"',' ',$target);
$target=str_replace('  ',' ',$target);
$target=str_replace('  ',' ',$target);
$target=str_replace('  ',' ',$target);
$output='&hellip;';

$words=@explode(' ',trim($target));

for ($i=0;$i<count($words);$i++) {
//echo $words[$i].'<hr>';
$text = highlight(preg_quote(str_replace('_',' ',$words[$i])),$text);
}

//echo $text.'<hr>';

// PEND: Remove Delimiter if in entity --> where not LIKE?

$text=explode('_._!_:_',' '.$text.' ');

//die('<pre>'.var_dump($text).'</pre>');

$ii=0;

for ($i=0;$i<15;$i++){
$ii++;
if (str_replace('{[}','',$text[$i])!=$text[$i]) $output.= html_substr($text[$i-1],-55).$text[$i].html_substr($text[$i+1],0,55).'&hellip;';

}

return str_replace('</span></span> &hellip; <span class="hitshilite">',' ',str_replace('-&hellip;-','-',str_replace('{[}','<span class="hitshilite">',str_replace('{]}','</span></span>',$output))));
//return $output;
} 

function prepare_search_term($str,$delim='#') {
$search = preg_quote($str,$delim);
$search = preg_replace('/[aàáâãåäæAÀÁÂÃÄÅÆ]/i', '[aàáâãåäæAÀÁÂÃÄÅÆ]', $search);
$search = preg_replace('/[eèéêëEÈÉÊË]/i', '[eèéêëEÈÉÊË]', $search);
$search = preg_replace('/[iìíîïIÌÍÎÏ]/i', '[iìíîïIÌÍÎÏ]', $search);
$search = preg_replace('/[oòóôõöøœOÒÓÔÕÖØŒ]/i', '[oòóôõöøœOÒÓÔÕÖØŒ]', $search);
$search = preg_replace('/[uùúûüUÙÚÛÜ]/i', '[uùúûüUÙÚÛÜ]', $search); 
$search = preg_replace('/[nñNÑ]/i', '[nñNÑ]', $search);
$search = preg_replace('/[cçCÇ]/i', '[cçCÇ]', $search);

    // add more characters...
    
    return $search;
}

function highlight($searchtext, $text) {
//echo $searchtext.'<hr>';
    $search = prepare_search_term($searchtext);
    $text = str_replace('&#','&\\#',$text);
	return str_replace('&\#','&#',preg_replace('#' . $search . '#i', '_._!_:_{[}$0{]}_._!_:_', $text));
}

function html_strlen($str) {
  $chars = preg_split('/(&[^;\s]+;)|/', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
  return count($chars);
}

function html_substr($str, $start, $length = NULL) {
  if ($length === 0) return ""; //stop wasting our time ;)

  //check if we can simply use the built-in functions
  if (strpos($str, '&') === false) { //No entities. Use built-in functions
    if ($length === NULL)
      return substr($str, $start);
    else
      return substr($str, $start, $length);
  }

  // create our array of characters and html entities
  $chars = preg_split('/(&[^;\s]+;)|/', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);
  $html_length = count($chars);

  // check if we can predict the return value and save some processing time
  if (
       ($html_length === 0) /* input string was empty */ or
       ($start >= $html_length) /* $start is longer than the input string */ or
       (isset($length) and ($length <= -$html_length)) /* all characters would be omitted */
     )
    return "";

  //calculate start position
  if ($start >= 0) {
    $real_start = $chars[$start][1];
  } else { //start'th character from the end of string
    $start = max($start,-$html_length);
    $real_start = $chars[$html_length+$start][1];
  }

  if (!isset($length)) // no $length argument passed, return all remaining characters
    return substr($str, $real_start);
  else if ($length > 0) { // copy $length chars
    if ($start+$length >= $html_length) { // return all remaining characters
      return substr($str, $real_start);
    } else { //return $length characters
      return substr($str, $real_start, $chars[max($start,0)+$length][1] - $real_start);
    }
  } else { //negative $length. Omit $length characters from end
      return substr($str, $real_start, $chars[$html_length+$length][1] - $real_start);
  }

}

?>