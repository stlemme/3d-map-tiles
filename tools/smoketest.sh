#!/bin/sh

HOST=$1
PORT=$2
ENDPOINT=api/3d-map-tiles/osm

echo "Entering FIC2Lab smoke test sequence. Vendor's validation procedure of 3D-Map Tiles SE engaged. Target host: $HOST"

ITEM_RESULT=`curl -s -o /dev/null -w "%{http_code}" http://$HOST:$PORT/$ENDPOINT/0/0/0.xml`
if [ "$ITEM_RESULT" -ne "200" ]; then
    echo "Curl command for testing availability of endpoint failed. Validation procedure terminated."
    echo "Debug information: HTTP code $ITEM_RESULT instead of expected 200 from $HOST"
    exit 1;
else
    echo "Curl command for <your text> OK."
fi

echo "Smoke test completed. Vendor component validation procedure succeeded. Over."
