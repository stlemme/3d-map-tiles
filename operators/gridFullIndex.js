
var Xflow = Xflow || {};
var XML3D = XML3D || {};
	
(function() {



function create_patch (x,y,dimensions,offset,index){
	var base = x+y*dimensions;
	index[offset++] = base + 1;
	index[offset++] = base;
	index[offset++] = base + dimensions;
	index[offset++] = base + dimensions;
	index[offset++] = base + dimensions + 1;
	index[offset++] = base + 1;
	return offset;
}


Xflow.registerOperator("xflow.gridFullIndex", {
    outputs: [{type: 'int', name: 'index', customAlloc: true}],
    params:  [  {type: 'int', source: 'lod', array: true}],
    alloc: function(sizes, lod)
    {
		//warning! code will only work for lod>=2!!
		var dimensions=Math.pow(2,lod[0])+1;
        sizes['index'] = (dimensions-1)*(dimensions-1)*6;
    },
    evaluate: function(index, lod) {
		var dimensions=Math.pow(2,lod[0])+1;
		
        // Create Indices for triangles
		var offset = 0;
		
		// Create Indices for the tile
		for(var y = 0; y <= dimensions-2 ; y++) {
			for(var x = 0; x <= dimensions-2 ; x++) {
				offset=create_patch (x,y,dimensions,offset,index);
			}
		}
	}
});


})();
