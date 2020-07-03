<?php
/* A class for encapsulating the /proc/.../stat data structure */

class ProcStat {
    protected $data;

    public function __construct($pid) {
        $this->data = self::update($pid);
    }

    public function __get($name) {
        if(array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
    }

    protected static function nextTok($string, &$start, $delim) {
        $right = strpos($string, $delim, $start);
        
        $strstart = $start;
        $length = $right - $strstart;

        if(false !== $right) {
            $start = $right + strlen($delim);
        }

        return substr($string, $strstart, $length);
    }

    static protected function update($pid) {
        // I tried fopen(), but I ran into some kind of race condition when accessing 'stat' for nohup'd processes.
        exec( "cat /proc/$pid/stat 2>/dev/null", $output, $cat_ret );
        if(0 !== $cat_ret) {
            throw new ProcStatException("'cat /proc/$pid/stat' returned $cat_ret");
        }

        $string = $output[0];

        # With the exception of "comm", everything is " " delimited. But "comm" throws a monkey wrench in.
        $start = 0;
        $data['pid'] = self::nextTok($string, $start, ' (');
        $data['comm'] = self::nextTok($string, $start, ') ');
        $data['state'] = self::nextTok($string, $start, ' ');
        $data['ppid'] = self::nextTok($string, $start, ' ');
        $data['pgrp'] = self::nextTok($string, $start, ' ');
        $data['session'] = self::nextTok($string, $start, ' ');
        $data['tty_nr'] = self::nextTok($string, $start, ' ');
        $data['tpgid'] = self::nextTok($string, $start, ' ');
        $data['flags'] = self::nextTok($string, $start, ' ');
        $data['minflt'] = self::nextTok($string, $start, ' ');
        $data['cminflt'] = self::nextTok($string, $start, ' ');
        $data['majflt'] = self::nextTok($string, $start, ' ');
        $data['cmajflt'] = self::nextTok($string, $start, ' ');
        $data['utime'] = self::nextTok($string, $start, ' ');
        $data['stime'] = self::nextTok($string, $start, ' ');
        $data['cutime'] = self::nextTok($string, $start, ' ');
        $data['cstime'] = self::nextTok($string, $start, ' ');
        $data['priority'] = self::nextTok($string, $start, ' ');
        $data['nice'] = self::nextTok($string, $start, ' ');
        $data['num_threads'] = self::nextTok($string, $start, ' ');
        $data['itrealvalue'] = self::nextTok($string, $start, ' ');
        $data['starttime'] = self::nextTok($string, $start, ' ');
        $data['vside'] = self::nextTok($string, $start, ' ');
        $data['rss'] = self::nextTok($string, $start, ' ');
        $data['rsslim'] = self::nextTok($string, $start, ' ');
        $data['startcode'] = self::nextTok($string, $start, ' ');
        $data['endcode'] = self::nextTok($string, $start, ' ');
        $data['startstack'] = self::nextTok($string, $start, ' ');
        $data['kstkesp'] = self::nextTok($string, $start, ' ');
        $data['kstkeip'] = self::nextTok($string, $start, ' ');
        $data['signal'] = self::nextTok($string, $start, ' ');
        $data['blocked'] = self::nextTok($string, $start, ' ');
        $data['sigignore'] = self::nextTok($string, $start, ' ');
        $data['sigcatch'] = self::nextTok($string, $start, ' ');
        $data['wchan'] = self::nextTok($string, $start, ' ');
        $data['nswap'] = self::nextTok($string, $start, ' ');
        $data['cnswap'] = self::nextTok($string, $start, ' ');
        $data['exit_signal'] = self::nextTok($string, $start, ' ');
        $data['processor'] = self::nextTok($string, $start, ' ');
        $data['rt_priority'] = self::nextTok($string, $start, ' ');
        $data['rt_policy'] = self::nextTok($string, $start, ' ');
        $data['policy'] = self::nextTok($string, $start, ' ');
        $data['delayacct_blkio_ticks'] = self::nextTok($string, $start, ' ');
        $data['guest_time'] = self::nextTok($string, $start, ' ');
        $data['cguest_time'] = self::nextTok($string, $start, ' ');
        $data['start_data'] = self::nextTok($string, $start, ' ');
        $data['end_data'] = self::nextTok($string, $start, ' ');
        $data['start_brk'] = self::nextTok($string, $start, ' ');
        $data['arg_start'] = self::nextTok($string, $start, ' ');
        $data['arg_end'] = self::nextTok($string, $start, ' ');
        $data['env_start'] = self::nextTok($string, $start, ' ');
        $data['env_end'] = self::nextTok($string, $start, ' ');
        $data['exit_code'] = self::nextTok($string, $start, ' ');
        return $data;
    }

    public function refresh() {
        try {
            $newInstance = new ProcStat($this->pid);
        } catch (ProcStatException $e) {
            // process is almost certainly gone.
            return false;
        }

        // Is this the same process?
        if($newInstance->starttime !== $this->starttime) {
            // No, not the same process.
            return false;
        }

        // Yes, unless the world's clock wrapped. Unlikely, that.
        // Also, yes, this is a hacky way around not being able to reassign $this. Let me know if it breaks something.
        $this->data = $newInstance->data;

        return true;
    }

    public function status() {
        return $this->refresh();
    }

}