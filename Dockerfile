FROM eboraas/apache-php

RUN uname -a

RUN apt-get update && apt-get -y install chef subversion


ENV RECIPE_PATH /var/chef/cookbooks/3DMapTiles

WORKDIR /opt/


# currently FIWARE hosts an SVN repo for Chef recipes for GE/SE
RUN svn export --non-interactive --trust-server-cert  https://forge.fiware.org/scmrepos/svn/testbed/trunk/cookbooks/SESoftware/3DMapTiles/ $RECIPE_PATH  

# write Chef solo install script on the fly
RUN echo "{ \"run_list\" : \"recipe[3DMapTiles::4.1.3_install]\" }" > $RECIPE_PATH/recipes/install.js

RUN pwd
RUN ls $RECIPE_PATH

# will clone git repo with SE and configure Apache
RUN chef-solo -j $RECIPE_PATH/install.js


EXPOSE 80 
