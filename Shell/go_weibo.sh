#!/bin/bash
if [ -f 'weibo.log' ] 
then
    gzip weibo.log
    if [ -d './logs/weibo' ] 
    then
        mv weibo.log.gz `date +%Y%m%d%H%M%S`_weibo.log
    else
        mkdir -p './logs/weibo'
        mv weibo.log.gz `date +%Y%m%d%H%M%S`_weibo.log
    fi
fi
./weibo2.sh > weibo.log 2>&1 &
