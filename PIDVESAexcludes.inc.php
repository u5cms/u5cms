<?php
$PIDVESAexcludes='';
if(strpos('x'.$excludedobjecttypesinsearchresults,'p')>0) $PIDVESAexcludes.="AND typ!='p'";
if(strpos('x'.$excludedobjecttypesinsearchresults,'i')>0) $PIDVESAexcludes.="AND typ!='i'";
if(strpos('x'.$excludedobjecttypesinsearchresults,'f')>0) $PIDVESAexcludes.="AND typ!='f'";
if(strpos('x'.$excludedobjecttypesinsearchresults,'a')>0) $PIDVESAexcludes.="AND typ!='a'";
if(strpos('x'.$excludedobjecttypesinsearchresults,'d')>0) $PIDVESAexcludes.="AND typ!='d'";
if(strpos('x'.$excludedobjecttypesinsearchresults,'v')>0) $PIDVESAexcludes.="AND typ!='v'";
if(strpos('x'.$excludedobjecttypesinsearchresults,'y')>0) $PIDVESAexcludes.="AND typ!='y'";
if(strpos('x'.$excludedobjecttypesinsearchresults,'e')>0) $PIDVESAexcludes.="AND typ!='e'";
define(PIDVESAexcludes,$PIDVESAexcludes);
?>