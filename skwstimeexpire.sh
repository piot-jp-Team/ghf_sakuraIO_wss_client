#!/bin/bash

FILETIME=`date +"%y%m%d%H%M" -r /xxxx/yyy/sakura_wss/log_Continuously.txt`
EXPTIME=`date +"%y%m%d%H%M" -d '10 minutes ago'`
if [ $FILETIME -lt $EXPTIME ]; then
    export LC_CTYPE=ja_JP.UTF-8;echo "ｓａｋｕｒａーｉｏのデータが１０分以上遅れてます" | mail -s "ｓａｋｕｒａーｉｏ遅延" test@test.com    
    echo "sent mail ${FILETIME} ${EXPTIME}"
    ps ax | grep -v grep | grep -q skws_Continuously || echo "wss stopped"
    isAlive=`ps -ef | grep "skws_Continuously" | grep -v grep | wc -l`
    if [ $isAlive = 0 ]; then
        echo "Server is dead, restarting..."
        /xxxx/yyy/skws_Contin.sh
    fi
fi

exit 0

