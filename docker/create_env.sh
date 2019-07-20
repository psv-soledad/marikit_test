#!/bin/bash
os="$(uname -s)"
if [[ -n "$os" ]]
then
	case "$os" in
	Darwin*)
		host=host.docker.internal
		remote_connect_back=0
		gid=1100
		;;
	Linux*)
		host=$(docker network inspect bridge | awk '/"Gateway"/ {gsub ("\"","") ;print $2}')
		remote_connect_back=1
		gid=$(id -g)
		;;
	*)
		exit;;
	esac
	#Выводим инфу на экран
	echo "Your remote_connect_back = $remote_connect_back"
	echo "Your OS = $os"
	echo "Your machine's host = $host"
	echo "Your GID = $gid"

	#Создаем фаил .env
	echo -n > ./.env
	echo "USER_UID=$(id -u)" >> ./.env
	echo "USER_GID=$gid" >> ./.env
	echo "XDEBUG_REMOTE_HOST=${host}" >> ./.env
	echo "REMOTE_CONNECT_BACK=${remote_connect_back}" >> ./.env
else
	echo "Only Darwin or Linux"
fi