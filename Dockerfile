FROM wordpress:latest
#FROM wordpress:4.5.3
#FROM wordpress:4.6.0

MAINTAINER Kazumichi Yamamoto <yamamoto.febc@gmail.com>

RUN apt-get update && apt-get install -y git
