#!/bin/bash

COOKIE="weibocn.txt"
USER_AGENT="Mozilla/5.0 (Windows NT 6.1; WOW64; rv:10.0.2) Gecko/20100101 Firefox/10.0.2"
DATA=""
REFERER=""

PUB_URL="http://weibo.cn/pub"
MAIN_URL="http://weibo.cn/u/2698105273"
LOGIN_URL=""
POSTMSG_URL=""
SUBMIT_URL="http://3g.sina.com.cn/prog/wapsite/sso/login_submit.php"
HZQX_URL="http://www.hzqx.com/gzhfw/dqyb.asp"
PUB_HTML=""
MAIN_HTML=""
LOGIN_HTML=""
HZQX_HTML=""
CONTENT=""
HEADER1=""
HEADER2=""
B_SEND=true
SEND_RESULT=""

function getLoginURL
{
    #http://3g.sina.com.cn/prog/wapsite/sso/login.php
    local html=$PUB_HTML
    local url=${html/*class=\"ut\"><a href=\'/}
    url=${url//\'>登录*/}
    local length=${#url}
    if [ $length -lt 200 ]
    then
        LOGIN_URL=${url//&amp;/&}
    else
        LOGIN_URL=""
		echo "Error:"
		echo "$url"
    fi
    echo -e "==============LOGIN_URL================"
    echo -e "$LOGIN_URL"
    echo -e "======================================="
}

function getPostData
{
    #--data 
    local mobile="mobile=gmail.com&"
    local password="&"
    local remember="remember=on&"
    local backURL="backURL=http%3A%2F%2Fweibo.cn%2Fhzsb&"
    local backTitle="backTitle=%E6%96%B0%E6%B5%AA%E5%BE%AE%E5%8D%9A&"
    local submit="submit=%E7%99%BB%E5%BD%95"
    local html=$LOGIN_HTML
    local pwdname=${html/*<input type=\"password\" name=\"/}
    local vk=${html/*<input type=\"hidden\" name=\"vk\" value=\"/}
    local posturl=${html/*login_submit.php/}
    backURL="backURL=http%3A%2F%2Fweibo.cn%2Fhzsb&"
    #"vk=5352_1980_1868987161&"
    pwdname=${pwdname/\" size=\"30\" value=\"\"*/}
    password=$pwdname$password
    vk=${vk/?????<input type=\"submit\"*/}
    vk="vk=$vk&"
    #http://3g.sina.com.cn/prog/wapsite/sso/login_submit.php
    posturl=${posturl/\" method=\"post\"*/}
    posturl=${posturl//&amp;/&}
    SUBMIT_URL=$SUBMIT_URL$posturl
    #DATA=$mobile$password"=DJ%3DF6dWUCxGKK1%3B&remember=on&"$backURL$backTitle"&vk="$vk"&"$submit
    DATA="$mobile$password$remember$backURL$backTitle$backURL$vk$submit"
    echo -e "===========SUBMIT_URL==DATA============"
    echo -e "$SUBMIT_URL"
    echo -e "$DATA"
    echo -e "======================================="
}

function login
{
    echo "Begin login..."
	echo "Pub URL: $PUB_URL"
    echo "===========<-GET_PUB_HTML==============="
	echo "curl --cookie $COOKIE --cookie-jar $COOKIE --user-agent \"$USER_AGENT\" $PUB_URL"
    PUB_HTML=`curl --cookie $COOKIE --cookie-jar $COOKIE --user-agent "$USER_AGENT" $PUB_URL`
    echo "============GET_PUB_HTML->=============="
    echo "Get Login URL..."
    getLoginURL
    if [ -n "$LOGIN_URL" ] #if non zero
    then
        #get login page
        REFERER=$PUB_URL
        echo "===========<-GET_LOG_HTML==============="
		echo "curl --cookie $COOKIE --cookie-jar $COOKIE --user-agent \"$USER_AGENT\" --referer $REFERER $LOGIN_URL"
        LOGIN_HTML=`curl --cookie $COOKIE --cookie-jar $COOKIE --user-agent "$USER_AGENT" --referer $REFERER $LOGIN_URL`
        echo "===========GET_LOG_HTML->==============="
        REFERER=$LOGIN_URL
        echo "Try to Log on..."
        getPostData
        #submit request to login
        echo "============<-POST_LOGIN==============="
		echo "curl --cookie $COOKIE --cookie-jar $COOKIE --user-agent \"$USER_AGENT\" --data $DATA --referer $REFERER $SUBMIT_URL"
        curl --cookie $COOKIE --cookie-jar $COOKIE --user-agent "$USER_AGENT" --data $DATA --referer $REFERER $SUBMIT_URL
        echo "============POST_LOGIN->==============="
        sed -i 's/sina.com.cn/weibo.cn/' $COOKIE
        cat $COOKIE
    fi
}

function checkLogin
{
    echo "Test if logon..."
    echo "===========<-GET_MAIN_HTML==============="
	echo "curl --cookie $COOKIE --cookie-jar $COOKIE --user-agent \"$USER_AGENT\" $MAIN_URL"
    MAIN_HTML=`curl --cookie $COOKIE --cookie-jar $COOKIE --user-agent "$USER_AGENT" $MAIN_URL`
    echo "===========<-GET_MAIN_HTML==============="
    
    #http://weibo.cn/mblog/sendmblog?st=ea62&st=ea62
    local html=$MAIN_HTML
    local url=${html}
    local url=${html/*<form action=\"\/mblog\/sendmblog/} 
    #echo -e "\n\n$url\n\n"
    local url=${url/\" accept-charset=\"UTF-8\" method=\"post\"*/}
    echo -e "\n\n$url\n\n"
    local length=${#url}
    if [ $length -lt 40 ]
    then
        POSTMSG_URL=${url//&amp;/&}
    else
        POSTMSG_URL=""
    fi
    #POSTMSG_URL="http://weibo.cn$POSTMSG_URL"
    echo -e "==============POST_MSG_URL================"
    echo -e "$POSTMSG_URL"
    echo -e "=========================================="   
}


function getWeatherRpt
{
    echo -e "\n\nStarting getWeatherRpt"
    echo "===========<-GET_WEATHER_RPT==============="
	echo "curl $HZQX_URL"
    HZQX_HTML=`curl $HZQX_URL`
    echo "===========GET_WEATHER_RPT->==============="
    local html=${HZQX_HTML/*<td width=\"100%\"> <p align=\"left\">/}
    html=$(echo $html | iconv -f gb2312 -t utf-8)
    CONTENT=${html/更新更快的天气信息，请拨96121。*/}
    CONTENT=${CONTENT//&nbsp;/}
    CONTENT=${CONTENT/杭州市气象台/}
    HEADER2=${CONTENT/发布的市区天气预报*/}
    CONTENT=${CONTENT/发布的市区天气预报/}
    echo "header1=$HEADER1"
    echo "header2=$HEADER2"
    if [ "$HEADER1" != "$HEADER2" ]
    then
        HEADER1=$HEADER2
        B_SEND=true
        echo "i am going to send msg..."
    else
        B_SEND=false
    fi
    #local msgLength=${#CONTENT}-36
    #echo $msgLength
    #CONTENT=${CONTENT:0:$msgLength}
    #echo $CONTENT
}

function sendMsg
{
    checkLogin
    if [ ! -n "$POSTMSG_URL" ]
    then
        echo "Cookie login failed. Logging on..."
        login
        checkLogin
    fi
    
    if [ -n "$POSTMSG_URL" ]
    then
        echo "Cookie login successfully. Sending Message..."
        POSTMSG_URL="http://weibo.cn/mblog/sendmblog$POSTMSG_URL"
        REFERER=$MAIN_URL
        local dt=`date`
        #getWeatherRpt
        DATA="rl=0&content=$CONTENT @陈小明Beta "
        #DATA="rl=0&split=确定&content=$CONTENT @陈小明Beta "
        #DATA=${DATA/\#/%23}
        #DATA=${DATA/\&/%26}
        echo "$DATA @ $dt"
		echo "curl --cookie $COOKIE --cookie-jar $COOKIE --user-agent \"$USER_AGENT\" --data \"$DATA\" --referer $REFERER $POSTMSG_URL"
        SEND_RESULT=`curl --cookie $COOKIE --cookie-jar $COOKIE --user-agent "$USER_AGENT" --data "$DATA" --referer $REFERER $POSTMSG_URL`
        echo $SEND_RESULT
        spt=`grep 将自动分为 $SEND_RESULT`
        if [ ${#spt} -gt 0 ]
        then
            DATA="split=确定&content=$CONTENT @陈小明Beta "
            echo "curl --cookie $COOKIE --cookie-jar $COOKIE --user-agent \"$USER_AGENT\" --data \"$DATA\" --referer $REFERER $POSTMSG_URL"
            curl --cookie $COOKIE --cookie-jar $COOKIE --user-agent \"$USER_AGENT\" --data \"$DATA\" --referer $REFERER $POSTMSG_URL
        fi
    else
        echo "Error: login failed."
    fi
}

echo "Process start..."
while true
do
    getWeatherRpt
    if $B_SEND
    then
        sendMsg
    fi
    sleep 3600
done

#getWeatherRpt
#checkLogin
