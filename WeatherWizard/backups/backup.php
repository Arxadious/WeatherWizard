<?php
$secret_key = $_GET['key'] ?? '';

// Set your secret key here
if ($secret_key !== '7J#x@8vGH^kQ!2b3mnL*rsS$w') {
    die("Unauthorized access.");
}

// Database connection details
$host = 'sql103.infinityfree.com';
$db = 'if0_37324028_weatherwizard';
$user = 'if0_37324028';
$pass = 'q2h79ZWzexr';

// Define the directory and name for the backup file
$backup_dir = __DIR__ . '/backups';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true); // Create the backups directory if it doesn't exist
}

$backup_file = $backup_dir . '/daily_backup.sql'; // Use a fixed file name

// Create the mysqldump command for the backup
$command = "mysqldump --host=$host --user=$user --password=$pass $db > $backup_file";

// Execute the command and capture output
$output = [];
$return_var = null;
exec($command, $output, $return_var);

// Check if the backup was successful
if ($return_var === 0) {
    echo json_encode(['success' => true, 'message' => "Backup successfully saved to $backup_file"]);
} else {
    echo json_encode(['success' => false, 'message' => 'Backup failed']);
}
?>
