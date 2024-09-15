<?php
function writeecho($that) {
file_put_contents('../fileversions/KILLhtaccessLOG.txt',$that,FILE_APPEND);	
}

// Path to the file to be checked
$file = '../fileversions/KILLhtaccessPID.txt';

// Check if the PID file exists
if (!file_exists($file)) {
    writeecho(date('Y-m-d H:i:s').' '.$pid." PID file not found. The process may not be running.\n");
}

// Read the PID from the file
else {
	
$pid = file_get_contents($file);

// Check if the posix extension is available
if (function_exists('posix_kill')) {
    // Try to stop the process using posix_kill
    if (posix_kill($pid, SIGKILL)) {
        writeecho(date('Y-m-d H:i:s').' '.$pid." Process successfully stopped using posix_kill.\n");
    } else {
        writeecho(date('Y-m-d H:i:s').' '.$pid." Error stopping the process using posix_kill.\n");
    }
} else {
    // If posix_kill is not available, use exec to stop the process
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows-specific command
        exec("taskkill /F /PID $pid", $output, $result);
    } else {
        // UNIX/Linux-specific command
        exec("kill -9 $pid", $output, $result);
    }

    if ($result === 0) {
        writeecho(date('Y-m-d H:i:s').' '.$pid." Process successfully stopped using exec.\n");
    } else {
        writeecho(date('Y-m-d H:i:s').' '.$pid." Error stopping the process using exec.\n");
    }
}
}
// Define the maximum file size (6 MB in bytes)
$maxFileSize = 6 * 1024 * 1024; // 6 MB in bytes

// Check if the file exists
if (file_exists($file)) {
    // Get the file size
    $fileSize = filesize($file);

    // Check if the file size is greater than 6 MB
    if ($fileSize > $maxFileSize) {
        // Delete the file
        if (unlink($file)) {
            writeecho(date('Y-m-d H:i:s').' '.$pid." KILLhtaccess.txt was successfully deleted because it exceeded 6 MB.\n");
        } else {
            writeecho(date('Y-m-d H:i:s').' '.$pid." Error deleting KILLhtaccess.txt.");
        }
    } else {
        writeecho(date('Y-m-d H:i:s').' '.$pid." KILLhtaccess.txt is 6 MB or smaller and was not deleted.\n");
    }
} else {
    writeecho(date('Y-m-d H:i:s').' '.$pid." KILLhtaccess.txt does not exist.\n");
}

// Determine the PID
$pid = getmypid();
// Save the PID in a file
file_put_contents($file, $pid);
?>