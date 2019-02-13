FROM php:7.1-apache-jessie as ignore
WORKDIR /tmp
COPY . /tmp
RUN rm -rfv .git/ .idea/ bower_components package.json Dockerfile .gitlab-ci.yml bitbucket-pipelines.yml
RUN ls -lah

FROM php:7.1-apache-jessie
COPY --from=ignore /tmp /var/www/html/
COPY cicd/apache/000-default.conf /etc/apache2/sites-enabled/000-default.conf
RUN chown www-data:www-data /var/www/* -Rfv
