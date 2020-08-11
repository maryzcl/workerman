<?php
swoole_timer_tick(1000,function($timer_id){
    echo "循环每秒执行:".$timer_id.PHP_EOL;

});
swoole_timer_after(3000,function($timer_id){
    echo "单次执行:".$timer_id.PHP_EOL;
});