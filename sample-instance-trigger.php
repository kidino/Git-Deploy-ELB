<?php
// change directory to project, virtual host
chdir('/vhosts/my-special-website');

// run git pull -- make sure you are set up with the right users
// and permissions for your server, or Deploy Keys
shell_exec('git pull origin production');
