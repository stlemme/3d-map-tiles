
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


Xflow.registerOperator("xflow.vertexGrid", {
    outputs: [	{type: 'float3', name: 'position', customAlloc: true},
				{type: 'float3', name: 'normal', customAlloc: true},
				{type: 'float2', name: 'texcoord', customAlloc: true}],
    params:  [  {type: 'int', source: 'lod', array: true}],
	
    alloc: function(sizes, lod)
    {
		//warning! code will only work for lod>=2!!
		var dimensions=Math.pow(2,lod[0])+1;
		
        sizes['position'] = dimensions*dimensions;
        sizes['normal'] = dimensions*dimensions;
        sizes['texcoord'] = dimensions*dimensions;
    },
    evaluate: function(position, normal, texcoord, lod) {
		var dimensions=Math.pow(2,lod[0])+1;
		
		var l = dimensions*dimensions;
		
        // Create Positions
		for(var i = 0; i < l; i++) {
			var off3 = i*3;
			var off2 = i*2;
			
			var x = (i % dimensions) / (dimensions - 1);
			var z = Math.floor(i / dimensions) / (dimensions - 1);

			position[off3  ] = x;
			position[off3+1] = 0;
			position[off3+2] = z;

			normal[off3    ] = 0;
			normal[off3+1  ] = 1;
			normal[off3+2  ] = 0;

            texcoord[off2  ] = x;
            texcoord[off2+1] = 1.0 - z;
		}
	}
});


})();
