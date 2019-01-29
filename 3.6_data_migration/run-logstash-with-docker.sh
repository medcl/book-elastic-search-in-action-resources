#https://www.elastic.co/guide/en/logstash/current/docker-config.html
docker run  --rm -it --network="host" -v "$PWD"/config/:/config docker.elastic.co/logstash/logstash:6.5.4  -f /config/logstash.conf
