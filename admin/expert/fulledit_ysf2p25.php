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

$editorname = 'YSF2P25';
$configfile = '/etc/ysf2p25';
$tempfile = '/tmp/7LyKicNWVPUAV2.tmp';
$servicenames = array('mmdvmhost.service', 'p25gateway.service', 'ysf2p25.service');

require_once('fulledit_template.php');

?>
