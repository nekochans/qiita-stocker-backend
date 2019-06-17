FROM nginx:1.15.5-alpine

ENV PHP_HOST=127.0.0.1

ADD ./docker/nginx/config/default.conf.template /etc/nginx/conf.d/default.conf.template
ADD ./docker/nginx/config/nginx.conf /etc/nginx/nginx.conf

RUN mkdir -p /var/www/html/public
ADD ./public/ /var/www/html/public

CMD /bin/sh -c 'sed "s/\${PHP_HOST}/$PHP_HOST/" /etc/nginx/conf.d/default.conf.template  > /etc/nginx/conf.d/default.conf && nginx -g "daemon off;"'
