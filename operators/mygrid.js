
XML3D.options.setValue("renderer-faceculling", "back");



/**
 * Grid Generation
 */
Xflow.registerOperator("xflow.mygrid", {
    outputs: [	{type: 'float3', name: 'position', customAlloc: true},
				{type: 'float3', name: 'normal', customAlloc: true},
				{type: 'float2', name: 'texcoord', customAlloc: true},
				{type: 'int', name: 'index', customAlloc: true}],
    params:  [{type: 'int', source: 'size', array: true}],
    alloc: function(sizes, size)
    {
        var s = size[0];
        var t = (size.length > 1) ? size[1] : s;
        sizes['position'] = s* t;
        sizes['normal'] = s* t;
        sizes['texcoord'] = s* t;
		// TODO: use triangle strips
        sizes['index'] = (s-1) * (t-1) * 6;
        // sizes['index'] = (s*t) + (s-1)*(t-2);
    },
    evaluate: function(position, normal, texcoord, index, size) {
		var s = size[0];
        var t = (size.length > 1) ? size[1] : s;
		var l = s*t;
		
        // Create Positions
		for(var i = 0; i < l; i++) {
			var offset = i*3;
			position[offset] =  (((i % s) / (s-1))-0.5)*2;
			position[offset+1] = 0;
			position[offset+2] = ((Math.floor(i/t) / (t-1))-0.5)*2;
			// position[offset+2] = ((Math.floor(i/s) / (s-1))-0.5)*2;
		}

        // Create Normals
		for(var i = 0; i < l; i++) {
			var offset = i*3;
			normal[offset] =  0;
			normal[offset+1] = 1;
			normal[offset+2] = 0;
		}
        // Create Texture Coordinates
		for(var i = 0; i < l; i++) {
			var offset = i*2;
			// tx in range [0..1] not [0..1)
            texcoord[offset] = (i%s) / s;
            texcoord[offset+1] = 1.0 - (Math.floor(i/t) / t);
            // texcoord[offset] = (i%s) / (s-1);
            // texcoord[offset+1] = 1.0 - (Math.floor(i/t) / (t-1));
            // texcoord[offset+1] = Math.floor(i/s) / (s-1);
		}

        // Create Indices for triangles
		var tl = (s-1) * (t-1);
		var offset = 0;
		for(var i = 0; i < tl; i++) {
			var base = i + Math.floor(i / (s-1));
			index[offset++] = base + 1;
			index[offset++] = base;
			index[offset++] = base + s;
			index[offset++] = base + s;
			index[offset++] = base + s + 1;
			index[offset++] = base + 1;
		}
		// var tl = (s-1) * (t-1);
		// var offset = 0;
		// for(var i = 0; i < t-1; i++) {
			// for(var j = 0; j < s-1; j++) {
				// var base = i*s + j;
				// index[offset++] = base + s;
				// index[offset++] = base;
				// index[offset++] = base + 1;
				// index[offset++] = base + 1;
				// index[offset++] = base + s + 1;
				// index[offset++] = base + s;
			// }
		// }
		// console.log(offset);
		// console.log(tl);
		// Create Indices for trianglestrips
		// var i = 0
		// for (var row=0; row<t-1; row++) {
			// if ( (row%2)==0 ) { // even rows
				// for (var col=0; col<s; col++) {
					// index[i++] = col + row * s;
					// index[i++] = col + (row+1) * s;
				// }
			// } else { // odd rows
				// for (var col=s-1; col>0; col--) {
					// index[i++] = col + (row+1) * s;
					// index[i++] = col - 1 + + row * s;
				// }
			// }
		// }
	}
});

