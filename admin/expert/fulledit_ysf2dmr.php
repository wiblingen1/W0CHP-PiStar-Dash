<?php

if (!isset($_SESSION) || !is_array($_SESSION)) {
    session_id('pistardashsess');
    session_start();
    
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code
    checkSessionValidity();
}

$editorname = 'YSF2DMR';
$configfile = '/etc/ysf2dmr';
$tempfile = '/tmp/MNhQn9HUvpNPgp.tmp';
$servicenames = array('mmdvmhost.service', 'ysfgateway.service', 'ysf2dmr.service');

require_once('fulledit_template.php');

?>
