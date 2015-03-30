FROM eboraas/apache-php

RUN apt-get update && apt-get -y install chef-solo svn

WORKDIR /opt/

RUN svn export https://forge.fiware.org/scmrepos/svn/testbed/trunk/cookbooks/SESoftware/3DMapTiles/ 

RUN pwd
RUN ls

RUN chef-solo 3DMapTiles/recipes/4.1.3_install.rb

RUN git clone https://github.com/stlemme/3d-map-tiles 

EXPOSE 80 
