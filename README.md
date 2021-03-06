FIcontent 3D-Map Tiles SE
=====================

An openstreetmap-like xml3d tiles provider

Prerequisites
=====================

* Apache webserver
* PHP5
* mod_rewrite activated
* mod_headers activated

Installation
=====================

- cd {WEB_ROOT}/api
- git clone https://github.com/stlemme/3d-map-tiles.git
- cd 3d-map-tiles
- cp config.json.sample config.json
- vim config.json
- adapt line "RewriteBase /api/3d-map-tiles" in .htaccess if necessary

Docker
=====================

To ease the setup we provide pre-configured Docker images:
  * stlemme/3d-map-tiles
  * fic2/3d-map-tiles
  
```
sudo docker run -p 80:80 --name=my-3d fic2/3d-map-tiles
```

Configuration
=====================
 - update config.json accordingly to utilize different backends
 - an endpoint `osm` is configured by default using the OSM Textures backend
 - you can configure several endpoints using different property names in a single config.json

## OSM Textures
 - generates a single quad per tile textured with the respective image tile
 - you can use any OSM tile rendering server with this backend
 - the configuration requires the base URL of the tile rendering endpoint
```
{
  "ground" : {
    "backend" : "osm-texture",
    "config" : {
      "url" : "http://a.tile.openstreetmap.org"
    }
  }
}
```

## OSM Geometry
 - generates a single quad per tile textured with the respective image tile as ground plane
 - in addition, extruded building from their footprints are delivered
 - you can use any OSM tile rendering server for the ground plane
 - you can use any Overpass API endpoint for the buildings
 - the configuration requires the *base URL* of the tile rendering service as well as the endpoint of the *Overpass API*
```
{
  "buildings" : {
    "backend" : "osm-geometry",
    "config" : {
      "endpoint" : "http://overpass-api.de/api/interpreter",
      "params" : {
      },
      "osm-url" : "http://a.tile.openstreetmap.org"
    }
  }
}
```

## GIS Data Provider
 - generates a single quad per tile textured with a respective image tile from the Web Map Service (WMS)
 - in addition, extruded building from their footprints using the Web Feature Service (WFS) are delivered
 - you can use any WMS endpoint for the texture of the ground plane
 - you can use any WFS endpoint for the building footprints
 - the FIWARE GIS Data Provider GE (Geoserver) supports both services
 - the configuration requires the URLs of the two endpoint for the WMS as well as the WFS
 - each service is capable of additional parameters, which are passed to the respective API call
   - Web Map Service - [GetMap](http://docs.geoserver.org/stable/en/user/services/wms/reference.html#getmap)
   - Web Feature Service - [GetFeature](http://docs.geoserver.org/latest/en/user/services/wfs/reference.html#getfeature)
```
{
  "gis" : {
    "backend" : "gis-data-provider",
    "config" : {
      "wms" : {
        "endpoint" : "http://HOST/geoserver/fiware/wms",
        "params" : {
          "layers" : "fiware:terrain_texture_orto",
          "styles" : "",
          "bgcolor" : "0xFF8000",
          "transparent" : false
        }
      },
      "wfs" : {
        "endpoint" : "http://HOST/geoserver/fiware/ows",
        "params" : {
          "layers" : "fiware:building_polygons"
        }
      }
    }
  }
}
```

Usage
=====================

- http://HOST/api/3d-map-tiles/NAME-IN-CONFIG/0/0/0.xml
- http://HOST/api/3d-map-tiles/NAME-IN-CONFIG/0/0/0-asset.xml
