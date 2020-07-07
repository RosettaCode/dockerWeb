<?php

/* An abstraction around a PHP-FPM daemon process
 * Allows us to test interaction with PHP-FPM, by governing process lifetime, and providing connection details.
 */

class PHPFPM extends ProcStat {
    const FCGI_PORT = 9000;
    
    public function __construct() {
        parent::__construct($this->run());
    }

    protected function run() {
        $output = [];
        $pid = getmypid();
        exec( "nohup sh -c \"php-fpm > /usr/local/var/log/error_log 2>&1 \" & printf '%s' \"$!\"", $output, $cat_ret);

        if(0 != $cat_ret) {
            throw new PHPFPMException(sprintf("php-fpm failed to launch with exception %d", $cat_ret));
        }

        return $output[0];
    }

    public function stop($max_wait = 60, $wait_interval = 0.5) {
        // Tell the process to end.
        exec("kill $this->pid");

        $waited = 0;
        // Wait for it to end
        while((($waited + $wait_interval) < $max_wait) && $this->status()) {
            sleep($wait_interval);
        }
    }
}

?>