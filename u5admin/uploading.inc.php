<input type="hidden" id="uploading" value="0" />
<script>
function upstart() {
document.getElementById('uploading').value++;	
}
function upend() {
document.getElementById('uploading').value--;		
}
window.onbeforeunload = function() {
if (document.getElementById('uploading').value>0){self.focus();return('There is still a fileupload in progress in that window you tried to close. Closing that window will void your fileupload!');}
}
</script>