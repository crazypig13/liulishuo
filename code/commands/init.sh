#!/bin/sh
PATH=/usr/local/nginx/sbin:/usr/local/php/bin:/usr/local/mysql/bin:$PATH

host=$1
db=$2
user=$3
password=$4
sql_tpl=$5

exec_user=root
exec_pwd=123456
mysql_exe=`which mysql`
init_cmd=`${mysql_exe} -u${exec_user} -h${host} -p${exec_pwd} -e "create database ${db} default charset 'utf8';grant all on ${db}.* to ${user}@'%' identified by '${password}';" 2>&1 | grep -v 'insecure' ; echo inited`
if [ "$init_cmd" != "inited" ];
then
    >&2 echo $init_cmd
    exit 1
fi

import_cmd=`${mysql_exe} -u${user} -p${password} -h${host} ${db} < ${sql_tpl} 2>&1 | grep -v 'insecure'; echo imported`
if [ "$import_cmd" != "imported" ];
then
    >&2 echo $import_cmd
    exit 1
fi

echo ok

