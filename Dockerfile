FROM ubuntu:latest

RUN apt-get update
RUN apt-get -y install chef subversion


ENV RECIPE_PATH /var/chef/cookbooks/3DMapTiles

# currently FIWARE hosts an SVN repo for Chef recipes for GE/SE
RUN svn export --non-interactive --trust-server-cert  https://forge.fiware.org/scmrepos/svn/testbed/trunk/cookbooks/SESoftware/3DMapTiles/ $RECIPE_PATH  

# write Chef solo install script on the fly
RUN echo '{"run_list" : "recipe[3DMapTiles::4.1.3_install]" }' > $RECIPE_PATH/install.js

# will clone git repo with SE and configure Apache
RUN chef-solo -j $RECIPE_PATH/install.js

# quieten error logging
# RUN sed -i "s/error_reporting = .*$/error_reporting = E_ERROR | E_WARNING | E_PARSE/" /etc/php5/apache2/php.ini


ENV APACHE_RUN_USER   www-data
ENV APACHE_RUN_GROUP  www-data
ENV APACHE_PID_FILE   /var/run/apache2.pid
ENV APACHE_RUN_DIR    /var/run/apache2
ENV APACHE_LOCK_DIR   /var/lock/apache2
ENV APACHE_LOG_DIR    /var/log/apache2


EXPOSE 80 

CMD /usr/sbin/apache2ctl -D FOREGROUND

