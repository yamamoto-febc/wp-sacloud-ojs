FROM wordpress:latest
MAINTAINER Kazumichi Yamamoto <yamamoto.febc@gmail.com>

RUN apt-get update && apt-get install -y git
