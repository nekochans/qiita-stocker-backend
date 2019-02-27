FROM nginx:1.15.5-alpine

ADD ./docker/nginx/config/default.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /var/www/html/public
ADD ./public/ /var/www/html/public
