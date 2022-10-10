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

if (isset($_SESSION['CSSConfigs']['Text']['TextColor'])) {
    $textContent = $_SESSION['CSSConfigs']['Text']['TextColor'];
}

function system_information() {
    @list($system, $host, $kernel) = preg_split('/[\s,]+/', php_uname('a'), 5);
    $meminfo = false;
    if (@is_readable('/proc/meminfo')) {
        $data = explode("\n", file_get_contents("/proc/meminfo"));
        $meminfo = array();
        foreach ($data as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $val) = explode(":", $line);
                $meminfo[$key] = 1024 * floatval( trim( str_replace( ' kB', '', $val ) ) );
            }
        }
    }
    return array('date' => date('Y-m-d H:i:s T'),
                 'mem_info' => $meminfo
    );
}

// Retrieve server information
$system = system_information();

function formatSize( $bytes ) {
    $types = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' );
    for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
    return( round( $bytes, 2 ) . " " . $types[$i] );
}

// root fs info
$diskUsed = @exec("df --block-size=1 / | tail -1 | awk {'print $3'}");
$diskTotal = @exec("df --block-size=1 / | tail -1 | awk {'print $2'}");
$diskPercent = sprintf('%.2f',($diskUsed / $diskTotal) * 100);
$rootfs_used = formatSize($diskUsed). " of " .formatSize($diskTotal). " ($diskPercent% used)" ;

// Get the CPU temp and colour the box accordingly...
// Values/thresholds gathered from: 
// <https://www.rs-online.com/designspark/how-does-raspberry-pi-deal-with-overheating>
$cpuTempCRaw = exec('cat /sys/class/thermal/thermal_zone0/temp');
if ($cpuTempCRaw > 1000) { $cpuTempC = sprintf('%.0f',round($cpuTempCRaw / 1000, 1)); } else { $cpuTempC = sprintf('%.0f',round($cpuTempCRaw, 1)); }
$cpuTempF = sprintf('%.0f',round(+$cpuTempC * 9 / 5 + 32, 1));
if ($cpuTempC <= 59) { $cpuTempHTML = "<div class=\"divTableCell cell_content\" style=\"background: inherit\">".$cpuTempF."&deg;F / ".$cpuTempC."&deg;C</div>\n"; }
if ($cpuTempC >= 60) { $cpuTempHTML = "<div class=\"divTableCell cell_content\" style=\"background: #fa0;color:black;\">".$cpuTempF."&deg;F / ".$cpuTempC."&deg;C</div>\n"; }
if ($cpuTempC >= 80) { $cpuTempHTML = "<div class=\"divTableCell cell_content\" style=\"background: #f00;color:black;font-weight:bold;\">".$cpuTempF."&deg;F / ".$cpuTempC."&deg;C</div>\n"; }

$loads = sys_getloadavg();
$core_nums = trim(shell_exec("grep -c '^processor' /proc/cpuinfo"));
$load = round($loads[0]/($core_nums + 1)*100, 2);

// get ram
$sysRamUsed = $system['mem_info']['MemTotal'] - $system['mem_info']['MemFree'] - $system['mem_info']['Buffers'] - $system['mem_info']['Cached'];
$sysRamPercent = sprintf('%.2f',($sysRamUsed / $system['mem_info']['MemTotal']) * 100); 
$ramDeetz = formatSize($sysRamUsed). " of ".formatSize($system['mem_info']['MemTotal']). " ($sysRamPercent% used)";

// inet traffic
$iface = $_SESSION['PiStarRelease']['Pi-Star']['iface'];
$VNStatGetData = exec("vnstat -i $iface | grep today | sed 's/today//g' | awk '{print $1\" \"$2\" \"$4\" \"$5\" \"$7\" \"$8\" \"$10\" \"$11}'"); // fields: rx[0] unit[1] tx[2] unit[3] total[4] unit[5] rate[6] unit[7]
if (empty($VNStatGetData) == false) {
    $Data = explode(" ", $VNStatGetData);
    $NetworkTraffic = "$Data[0] $Data[1] &darr; / $Data[2] $Data[3] &uarr; <small>($Data[6] $Data[7] avg. rate)</small>";
    $NetTrafficTotal = "$Data[4] $Data[5]";
} else {
    $NetworkTraffic = "(Collecting data, please wait.)";
    $NetTrafficTotal = "(Collecting data, please wait.)";
}
?>
<div class="divTable" id="hwInfoTable">
  <div class="divTableBody">
    <div class="divTableRow">
      <div class="divTableHeadCell"><a class="tooltip" href="#"><?php echo $lang['cpu_load'];?><span><strong>Plaform Info</strong><br/>Platform: <?php echo $_SESSION['PiStarRelease']['Pi-Star']['Platform'];?><br />Linux Kernel: <?php echo php_uname('r');?></a></span></div>
      <div class="divTableHeadCell"><a class="tooltip" href="#"><?php echo $lang['cpu_temp'];?><span><strong>CPU Temp</strong></a><span></div>
      <div class="divTableHeadCell"><a class="tooltip" href="#">Memory Usage<span><strong>Memory Usage</strong></a></span></div>
      <div class="divTableHeadCell"><a class="tooltip" href="#">Disk Usage<span><strong>Disk Usage</strong></a></span></div>
      <div class="divTableHeadCell"><a class="tooltip" href="#">Network Traffic<span><strong>Total Network Traffic Today</strong><br />(Interface: <?php echo($iface); ?>)</a></span></div>
    </div>
    <div class="divTableRow">
      <div class="divTableCell cell_content middle"><?php echo $load; ?>%</div>
      <?php echo $cpuTempHTML; ?>
      <div class="divTableCell cell_content middle"><?php echo $ramDeetz;?></div>
      <div class="divTableCell cell_content middle;"><?php echo $rootfs_used;?></div>
      <div class="divTableCell cell_content middle;"><a class="tooltip" href="#" style="border-bottom:1px dotted;color: <?php echo $textContent; ?>;"><?php echo $NetworkTraffic;?><span><strong>Total Combined Network Traffic Today</strong><br /><?php echo $NetTrafficTotal;?></a></span></div>
    </div>
  </div>
</div>
