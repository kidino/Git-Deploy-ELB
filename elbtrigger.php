<?php
/**
 * Git Deploy ELB
 *
 * PHP Script for Triggering Git Pull on Instances Under Elastic Load Balancer
 * in AWS (useful with autoscaling)
 *
 * The MIT License (MIT)
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author     Iszuddin Ismail <author@example.com>
 * @license    http://opensource.org/licenses/MIT
 */
    
class ELBTrigger {

    var $region = '';
    
    /**
     * Constructor and sets the REGION for AWS SDK
     */     
    function __construct( $region = 'REGION_SINGAPORE'){
        require_once 'sdk.class.php';
        $this->region = $region;
    }
    
    /**
     * Get the instances under a specific ELB
     *
     * @param  array    $load_balancer_names The ELB names in array
     * @return bool     false or an array of instance IDs
     */     
    function get_instances( $load_balancer_names = array() ) {
    
        $elb = new AmazonELB();
        $elb->set_region(constant('AmazonELB::'.$this->region));
        $elb_info = $elb->describe_load_balancers( array( 'LoadBalancerNames' => $load_balancer_names ) );

        print_r($elb_info);
        
        if ($elb_info->isOK()) {

            $fullObj = $elb_info->body->to_array()->getArrayCopy();
            $ins = $fullObj['DescribeLoadBalancersResult']['LoadBalancerDescriptions']['member']['Instances']['member'];
            
            $instances = (array_key_exists(0,$ins)) ? $ins : array( $ins );

            $all_instances = array();
            foreach($instances as $i) {
                $all_instances[] = $i['InstanceId'];
            }
            
            if (count($all_instances) > 0) 
                return $all_instances;
        } 
        
        return false;
    }
    
    /**
     * Get IP addresses of instances
     *
     * @param  array    $load_balancer_names The ELB names in array
     * @return bool     false or an array of instance IDs
     */     
    function get_ip_addresses( $instance_ids = array() ) {
        $ec2 = new AmazonEC2();
        $ec2->set_region(constant('AmazonEC2::'.$this->region));
        $ins_info = $ec2->describe_instances( array( 'InstanceId' => $instance_ids ) );

        if ($ins_info->isOK()) {
            $insObj = $ins_info->body->to_array()->getArrayCopy();

            $foundInst = (array_key_exists(0, $insObj['reservationSet']['item'])) ? 
                            $insObj['reservationSet']['item'] : 
                            array($insObj['reservationSet']['item']);

            $ipaddr = array();
            $trigger_urls = array();

            foreach( $foundInst as $inst){
                $ipaddr[] = $inst['instancesSet']['item']['ipAddress'];
            }
            
            if (count($ipaddr) > 0)
                return $ipaddr;
        }
        
        return false;
    }
    
    /**
     * Trigger scripts based on an array of IP addresses
     *
     * @param  array    $ipaddr an array of IP addresses
     * @param  string   $script_uri the URI of the script, with beginning slash
     * @return array    array of strings, with IP addresses as key, responses from respective servers
     */     
    function trigger_pull_script($ipaddr = array(), $script_uri = '/' /* with beginning slash */ ) {
        
        $trigger_response = array();
        
        foreach( $ipaddr as $ip ) {
            $resp = file_get_contents( 'http://'.$ip.$script_uri );
            $trigger_response[$ip] = $resp;
        }
        
        return $trigger_response;
    }

}

