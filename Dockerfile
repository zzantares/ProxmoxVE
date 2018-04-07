FROM alpine

RUN apk update
RUN apk add php5 php5-openssl php5-json php5-phar php5-curl php5-dom wget
RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/1b137f8bf6db3e79a38a5bc45324414a6b1f9df2/web/installer -O - -q | php -- --quiet
RUN mv composer.phar /usr/local/bin/composer

ENTRYPOINT ["sh"]

WORKDIR /root/