FROM nginx:latest

EXPOSE 80

RUN apt-get update \
    && apt-get install iputils-ping -y \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN rm -f /var/log/nginx/access.log \
    && touch /var/log/nginx/access.log

RUN rm -f /var/log/nginx/error.log \
    && touch /var/log/nginx/error.log

COPY ./nginx.conf /etc/nginx/nginx.conf
