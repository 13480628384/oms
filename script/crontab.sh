#!/bin/bash
function checkprocess(){
    if (ps aux|grep -v grep|grep "$1" )
    then
        echo "active"
    else
        echo "miss"
        #echo $1
        /usr/local/php/bin/php $1 &
    fi
}

cd /data/httpd/stable.tg.taoex.com/script/
checkprocess "/data/httpd/stable.tg.taoex.com/script/activeHostQueue.php"
checkprocess "/data/httpd/stable.tg.taoex.com/crontab/thread_queue.php"
