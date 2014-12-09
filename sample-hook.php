<?php

// get data from Gitlab hook
$push_data = json_decode( file_get_contents('php://input') );

// create log file
$logfile = 'hook.log';
file_put_contents($logfile, "POST:\n".print_r($_POST,true));
file_put_contents($logfile, "\nGET:\n".print_r($_GET, true),FILE_APPEND);
file_put_contents($logfile, "\nSERVER:\n".print_r($_SERVER, true), FILE_APPEND);
file_put_contents($logfile, "\nJSON POST:\n".print_r( $push_data, true), FILE_APPEND);

// some secret key
if ($_GET['key'] == 'my-secret-key') {
    // identifying the branch that was pushed
    // in this case -- only when branch 'production' was pushed
    if ($push_data->ref == 'refs/heads/production') {
        // get instances
        $trigger_obj = new ELBTrigger('REGION_SINGAPORE');
        $instances = $trigger_obj->get_instances( array( 'MY-ELB-101' ) );
        file_put_contents($logfile, "\nINSTANCES\n". print_r($instances, true) , FILE_APPEND);

        // get IP addresses
        $ipaddr = $trigger_obj->get_ip_addresses( $instances );
        file_put_contents($logfile, "\nIP ADDRESSES\n". print_r($ipaddr, true) , FILE_APPEND);
        
        // trigger the script at the instance servers
        $trigger = $trigger_obj->trigger_pull_script($ipaddr, '/apigitpull.php' );
        
        file_put_contents($logfile, "\nTRIGGER RESPONSE\n".  print_r($trigger, true), FILE_APPEND);
    } else {
        file_put_contents($logfile, "\nwrong branch - {$push_data->ref}", FILE_APPEND);
    }
} else {
	file_put_contents($logfile, "\nwrong key - $_GET[key]", FILE_APPEND);
}

// dumping to screen for debugging
echo file_get_contents($logfile);

