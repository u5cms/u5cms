<?php
 $sql = "SELECT email, pw FROM accounts WHERE accadmin=1";
        $result = mysql_query($sql);

        if ($result == false) die('SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>');

        $num = @mysql_num_rows($result);

        for ($i = 0; $i < $num; $i++) {
            $row = mysql_fetch_array($result);

            if ($usesessioninsteadofbasicauth != 'no') {
                if (u5flatidnlower($_SERVER['PHP_AUTH_USER']) == u5flatidnlower($row['email']) && $_SERVER['PHP_AUTH_PW'] == pwdcookieget($row['pw'])) $knownamdin = 'ok';
            } else {
                if (u5flatidnlower($row['email'] == u5flatidnlower($_SERVER['PHP_AUTH_USER'])) && $row['pw'] == pwdhsh($_SERVER['PHP_AUTH_PW'])) $knownamdin = 'ok';
            }
        }
?>