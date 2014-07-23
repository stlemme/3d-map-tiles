FIcontent 3D-Map Tiles SE
=====================

An openstreetmap-like xml3d tiles provider

Prereqisites
=====================

* Apache webserver
* PHP5
* mod_rewrite activated
* mod_headers activated

Installation
=====================

- cd {WEB_ROOT}/api/3d-map-tiles
- git clone https://github.com/stlemme/3d-map-tiles.git
- cd 3d-map-tiles
- cp config.json.sample config.json
- vim config.json
- adapt line "RewriteBase /api/3d-map-tiles" in .htaccess if necessary

Usage
=====================

- http://HOST/api/3d-map-tiles/filab/0/0/0.xml
- http://HOST/api/3d-map-tiles/filab/0/0/0-asset.xml
