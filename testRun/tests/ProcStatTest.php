<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ProcStatTest extends TestCase
{
    protected $instance;

    public function setUp() : void {
        $this->instance = new ProcStat(getmypid());
    }

    public function testNumericAttributes() {
        $attributes = [
            'pid',
            'ppid',
            'pgrp',
            'session',
            'tty_nr',
            'tpgid',
            'flags',
            'minflt',
            'cminflt',
            'majflt',
            'cmajflt',
            'utime',
            'stime',
            'cutime',
            'cstime',
            'priority',
            'nice',
            'num_threads',
            'itrealvalue',
            'starttime',
            'vside',
            'rss',
            'rsslim',
            'startcode',
            'endcode',
            'startstack',
            'kstkesp',
            'kstkeip',
            'signal',
            'blocked',
            'sigignore',
            'sigcatch',
            'wchan',
            'nswap',
            'cnswap',
            'exit_signal',
            'processor',
            'rt_priority',
            'rt_policy',
            'policy',
            'delayacct_blkio_ticks',
            'guest_time',
            'cguest_time',
            'start_data',
            'end_data',
            'start_brk',
            'arg_start',
            'arg_end',
            'env_start',
            'env_end',
            'exit_code'
        ];

        foreach ($attributes as $attr) {
            $val = $this->instance->$attr;
            if(false !== $val) {
                $this->assertIsNumeric($val, "$attr should be numeric");
            }
        }
    }

    public function testpid() {
        $this->assertEquals($this->instance->pid, getmypid(), "should be our pid");
    }

    public function testcomm() {
        $this->assertTrue(is_string($this->instance->comm), "comm should be a string");
        $this->assertEquals($this->instance->comm, 'php', "comm should be 'php'");
    }

    public function teststate() {
        $this->assertTrue(is_string($this->instance->state), "state should be a string");
        $this->assertContains($this->instance->state, ['R', 'S'], "we should be Running or Sleeping");
    }

    public function testDeadStatus() {
        exec( "nohup cat /dev/zero > /dev/null 2>&1 & printf '%s' \"$!\"", $output, $cat_ret);

        $this->assertEquals(0, $cat_ret, "backgrounded process setup should have succeeded");
        
        $dead_proc = new ProcStat($output[0]);

        $this->assertTrue($dead_proc->status(), "process should still be executing\n");

        exec( "kill -9 " . $dead_proc->pid );

        $this->assertFalse($dead_proc->status(), "$output[0] should be dead");
    }

    public function testrefresh() {
        // We can expect to have spent some user time between when the thing was initialized and now. So the metrics will read different.
        $old_cutime = $this->instance->cutime;
        
        // Run a "timeout 2 nohup cat /dev/zero > /dev/null" to let a child process rack up some cputime.
        exec( "timeout 2 cat /dev/zero > /dev/null");

        // And then check against cutime, not utime.
        $this->instance->refresh();
        $this->assertNotEquals($old_cutime, $this->instance->cutime, "we should see a change in child user CPU time");
    }

    public function testOurstatus() {
        // We're still running, so this had better be working.
        $this->assertTrue($this->instance->status(), "we should see that we're still a viable process");
    }

}

?>