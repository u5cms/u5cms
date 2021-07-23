pages='formdata.php,formdata_h.php,formdata_n.php,formdata2.php,formdatamail.php,formdata2csv.php,chart.php,import.php,backup.php,intranetmembers.php,acc.php,trxlist.php,pwd.php';

//////////////////////////////////

gotopage=false;
pages=pages.split(',');
for(i=0;i<pages.length;i++) {
if(location.href.indexOf('/'+pages[i])>0)gotopage=true;	
}

