#! /bin/sh
# chkconfig: 2345 55 25
# Description: Startup script for service on CentOS 7

PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

#服务名称
SERVICE_NAME='example'

#服务入口文件 绝对路径
SERVICE_INDEX_FILE='/usr/local/matrix/s/monitor/www/web/index.php'

#每个子进程运行的最大次数
MAX_REQUEST=1024

#RUN_PARAMS
RUN_PARAMS='main/index=2 main/test=1'

#每次运行任务的时间间隔
TIME_TRICK=2

#服务日志ID
SERVICE_LOG_ID='service_'$SERVICE_NAME

#服务BIN文件
BIN_FILE='/usr/local/php7/bin/php'

#PID 文件名
PID_FILENAME='service_'$SERVICE_NAME


DAEMON_CMD_LINE="$BIN_FILE $SERVICE_INDEX_FILE --daemon  -pid=$PID_FILENAME -l=$SERVICE_LOG_ID -t=$TIME_TRICK -max_request=$MAX_REQUEST $RUN_PARAMS"


do_start() {
	$DAEMON_CMD_LINE start
    if [ $? -eq 0 ];then
		echo -e "Staring $SERVICE_NAME      \033[49;32;1m [  OK  ]  \033[0m"
    fi 
}

do_stop() {
	$DAEMON_CMD_LINE stop
    if [ $? -eq 0 ];then
		echo -e "Stopping $SERVICE_NAME    \033[49;32;1m [  OK  ]  \033[0m"
	fi       
}

case "$1" in
        start)
                echo -n "Starting service_$SERVICE_NAME"
                do_start
        ;;
        stop)
                echo -n "Stopping service_$SERVICE_NAME"
                do_stop
		;;
        restart)
                echo -n "Restarting service_$SERVICE_NAME"
                do_stop
                sleep 3
                do_start
        ;;
        *)
                $DAEMON_CMD_LINE -h
                exit 3
        ;;

esac
exit 0