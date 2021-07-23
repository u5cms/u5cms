<?php 
//Für sture Hoster
if(!function_exists('get_magic_quotes_gpc'))
{
    function get_magic_quotes_gpc()
    {
        return 0;
    }
}

if (get_magic_quotes_gpc()==1) {

   foreach ($_GET as $key=>$value) {
      $_GET[$key]=stripslashes($value);
      }

   foreach ($_POST as $key=>$value) {
      $_POST[$key]=stripslashes($value);
      }

   }
//Fertig sture Hoster

?>