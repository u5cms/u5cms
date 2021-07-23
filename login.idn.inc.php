<?php
            if ($usesessioninsteadofbasicauth != 'no') {
                $concatlogins = '';
                $row['logins']=str_replace('&#140;','&oelig;',$row['logins']);
                $row['logins']=str_replace('&#156;','&oelig;',$row['logins']);
				$row['logins']=u5toutf8($row['logins']);
				$logins1 = explode(';', $row['logins']);

                for ($ii = 0; $ii < count($logins1); $ii++) {
					$logins1[$ii]=u5allnument(trim($logins1[$ii]));
                    $logins1[$ii]=html_entity_decode(html_entity_decode(($logins1[$ii]), ENT_COMPAT,'ISO-8859-1'), ENT_COMPAT,'ISO-8859-1');
					$logins2 = explode(':', $logins1[$ii]);
					$pwhashed = pwdcookie($logins2[1]);
					if(trim(u5flatidnlower($logins2[0]))!='')$concatlogins .= u5flatidnlower($logins2[0]) . ':' . $pwhashed . ';';
                }
                $row['logins'] = $concatlogins;
            }
?>