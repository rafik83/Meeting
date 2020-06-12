FROM node:14.4-alpine

WORKDIR /srv

RUN apk --no-cache --update --virtual build-dependencies add \
    python \
    make \
    g++ \
    && yarn install --pure-lockfile \
    && apk del build-dependencies
