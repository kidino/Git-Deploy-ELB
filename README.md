# Git Deploy ELB

**NOTE:** This library is to be used with PHP AWS SDK version 1. The last version is 1.6.2

I created this library to help update the codes in instances under a specific Elastic Load Balancer in AWS. While you can easily create a hook in your Git repo (GitHub or GitLab), the problem starts when you are using Auto-Scaling. With Auto-Scaling, you don't know that IP addresses of your instances.

This this library, what you can do is create a hook to trigger a script in a dedicated machine. And then, have that script trigger a script in the instances. 

To use this, your instances must have public IP addresses.

## How To Use

Put this library in the same folder as your sdk.class.php from your PHP AWS SDK. 

Make sure you have created and update config.inc.php with your AWS credentials that has access to EC2 and ELB.

The sample-hook.php shows how you can use the library. This is the script that you use in your Hook in Github or Gitlab.

The sample-instance-trigger.php is a sample script that you use to execute 'git pull' in the EC2 instance. Since this is going to be triggered via IP address, I put this in the default virtual host, which is normally in /var/www/html (in Apache 2.4). You also need to make sure that you have a user created for your machine or use the Deploy Key set up.


