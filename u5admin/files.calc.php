<?php
require_once('connect.inc.php');
if (file_exists('../r/' . $_GET['name'])) {

    $path = '../r/' . $_GET['name'];
    if ($handle = @opendir($path)) {
        while (false !== ($file = readdir($handle))) {

            if (str_replace('.', '', $file) != '' && (str_replace($thislan, '', $file) != $file || ($thislan == '' &&
                        str_replace('_'.$lan1na, '', $file) == $file &&
                        str_replace('_'.$lan2na, '', $file) == $file &&
                        str_replace('_'.$lan3na, '', $file) == $file
                    ))
            ) {
                $i++;

                $thefilesize = filesize($path . '/' . $file);
                $totalsizes += $thefilesize;
                $filesize = round($thefilesize / 1024);
                $mass = 'kB';
                if ($filesize > 1023) {
                    $mass = 'MB';
                    $filesize = round(($filesize / 1024 * 10)) / 10;
                }

                $showimage = '';
                if ($_GET['typ'] == 'i') {

////////////////////////////////////////////////////////
                    include('../config.php');
                    if (($limitjpgsizetobytes > 0 && filesize($path . '/' . $file) > $limitjpgsizetobytes) && (strpos($path . '/' . $file, '.jpg') > 0 || strpos($path . '/' . $file, '.JPG') > 0 || strpos($path . '/' . $file, '.jpeg') > 0 || strpos($path . '/' . $file, '.JPEG') > 0)) {

include('resizedlongedgepx.inc.php');

// Creating the Canvas 
                        $tn = imagecreatetruecolor($modwidth, $modheight);
                        $source = imagecreatefromjpeg($path . '/' . $file);

// Resizing our image to fit the canvas 
                        imagecopyresampled($tn, $source, 0, 0, 0, 0, $modwidth, $modheight, $width, $height);

// Outputs a jpg image, you could change this to gif or png if needed
                        unlink($path . '/' . $file);
                        if (!is_numeric($resamplingquality)) $resamplingquality = 70;
                        imagejpeg($tn, $path . '/' . $file, $resamplingquality);
                    }

////////////////////////////////////////////////////////

                    $showimage = '<a target="_blank" href="' . str_replace('r/../r/','r/',('../f.php?f=r/' . $path)) . '/' . $file . '?t=' . filemtime('../r/' . $path . '/' . $file) . '"><img style="max-width:30px;max-height:30px" src="' . str_replace('r/../r/','r/','../f.php?f=r/' . ($path . '/' . $file)) . '?t=' . @filemtime($path . '/' . $file) . '" /></a>';
                }

                if ($_GET['typ'] == 'f') $showimage = '<a target="_blank" href="' . str_replace('r/../r/','r/',('../f.php?f=r/' . $path)) . '/' . $file . '?t=' . filemtime('../r/' . $path . '/' . $file) . '"><img src="' . str_replace('r/../r/','r/',('../f.php?f=r/' . $path)) . '/' . $file . '?t=' . @filemtime($path . '/' . $file) . '" style="max-width:30px;max-height:30px" /></a>';

                if ($file[0] != '.') echo '<tr bgcolor="#eeeeee"><td>
	
	' . $showimage . '
	
	</td><td><a target="_blank" href="' . str_replace('r/../r/','r/',('../f.php?f=r/' . $path)) . '/' . $file . '?t=' . filemtime('../r/' . $path . '/' . $file) . '">' . $file . '</a></td><td>' . date('Y M d H:i:s', filemtime($path . '/' . $file)) . '</td><td>&nbsp;&nbsp;&nbsp;' . $filesize . $mass . '&nbsp;&nbsp;&nbsp;</td><td><a href="javascript:void(0)" onclick="cf=confirm(\'Do you really want to delete ' . $file . '\');if (cf) location.replace(\'deletefile.php?typ=' . $_GET['typ'] . '&name=' . $_GET['name'] . '&f=' . $path . '/' . $file . '\')">delete</a></td></tr>';
            }
        }
    }

}

?>