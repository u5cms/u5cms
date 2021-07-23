<span onclick="pubctrlonoff()" style="cursor:pointer;font-size:11px">
<span title="hidden (offline)" id="hic_h" style="display:none;background:black;color:white;">&nbsp;H&nbsp;</span>
<span title="indexing off (search engines)" id="hic_i"
      style="display:none;background:orange;color:white;">&nbsp;i&nbsp;</span>
<span title="closed user group (logins)" id="hic_c"
      style="display:none;background:green;color:white;">&nbsp;c&nbsp;</span>
<span title="hidden (htaccess forcer only)" id="hic_ha"
      style="display:none;background:black;color:white;">&nbsp;h&nbsp;</span>
</span>
<script>
    function fnhic() {

        if (document.getElementById('idlogins').value.replace(/ /, '') != '') document.getElementById('hic_c').style.display = 'inline';
        else document.getElementById('hic_c').style.display = 'none';

        if (document.getElementById('idhidden').value == '1') document.getElementById('hic_h').style.display = 'inline';
        else document.getElementById('hic_h').style.display = 'none';

        if (document.getElementById('idhidden').value == '-1') document.getElementById('hic_i').style.display = 'inline';
        else document.getElementById('hic_i').style.display = 'none';

        if (document.getElementById('idhidden').value == '2') document.getElementById('hic_ha').style.display = 'inline';
        else document.getElementById('hic_ha').style.display = 'none';


    }
</script>