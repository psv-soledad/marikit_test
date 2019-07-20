FROM nginx:1.15.3

#Пользователь
ARG USER_UID
ARG USER_GID
RUN export uid=$USER_UID gid=$USER_GID && \
    groupadd -r job -g ${gid} && \
    useradd -u ${uid} -r -g job -m -d /home/job -s /sbin/nologin -c "Docker image user" job

RUN mkdir -p /var/www/job && \
    chown -R job:job /var/www/job && \
    chown -R job:job /home/job

RUN usermod -a -G www-data job