
var Xflow = Xflow || {};
var XML3D = XML3D || {};
	
(function() {

function create_patch (x,y,dimensions){
	var base = x+y*dimensions;
	var offset=0;
	var index=new Array(6);
	index[offset++] = base + 1;
	index[offset++] = base;
	index[offset++] = base + dimensions;
	index[offset++] = base + dimensions;
	index[offset++] = base + dimensions + 1;
	index[offset++] = base + 1;
	return index;
}





Xflow.registerOperator("xflow.stichedGrid", {
    outputs: [	{type: 'float3', name: 'position', customAlloc: true},
				{type: 'float3', name: 'normal', customAlloc: true},
				{type: 'float2', name: 'texcoord', customAlloc: true},
				{type: 'int', name: 'index', customAlloc: true}],
    params:  [  {type: 'int', source: 'lod', array: false},
				{type: 'int', source: 'stitching', array: true}],
    alloc: function(sizes, lod , stitching)
    {
		//warning! code will only work for lod>=2!!
		var dimensions=Math.pow(2,lod[0])+1;
		
        sizes['position'] = dimensions*dimensions;
        sizes['normal'] = dimensions*dimensions;
        sizes['texcoord'] = dimensions*dimensions;
		// TODO: use triangle strips
		
		var indexsize = 0;
		
		//center triangles
		indexsize+= (dimensions-3)*(dimensions-3)*2;
		
		//border triangles
		for(var i=0;i<4;i++){
			if(stitching[i]==0){
			// no stitching
			indexsize+=(dimensions-2)*2;
			}
			else{
			// stitching
			indexsize+=((dimensions-5)*3)/2 + 4;
			}
		}
        sizes['index'] = indexsize*3;
    },
    evaluate: function(position, normal, texcoord, index, lod, stitching) {
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

        // Create Indices for triangles
		var offset = 0;
		
		// Create Indices for the center of the tile
		for(var y = 1; y <= dimensions-3 ; y++) {
			for(var x = 1; x <= dimensions-3 ; x++) {
				var tris=create_patch (x,y,dimensions);
				for(var i=0;i<tris.length;i++){
					index[offset++]=tris[i];
				}
				
			}
		}
		
		//west
		if(stitching[0]==0){
			//no stitching
			
			//first triangle
			index[offset++]=0;
			index[offset++]=dimensions;
			index[offset++]=dimensions+1;
			
			//last triangle
			index[offset++]=dimensions*(dimensions-2);
			index[offset++]=dimensions*(dimensions-1);
			index[offset++]=dimensions*(dimensions-2)+1;
		
			//everything in-between
			for(var y=1;y<=dimensions-3;y++){
				var tris=create_patch (0,y,dimensions);
				for(var i=0;i<tris.length;i++){
					index[offset++]=tris[i];
				}
			}
		}
		else{
			//stitching
			
			//first triangles
			
			//large triangle
			index[offset++]=0;
			index[offset++]=2*dimensions;
			index[offset++]=dimensions+1;
			
			//small triangle
			index[offset++]=2*dimensions;
			index[offset++]=2*dimensions+1;
			index[offset++]=dimensions+1;
			
			//last triangles
			
			//large triangle
			index[offset++]=dimensions*(dimensions-3);
			index[offset++]=dimensions*(dimensions-1);
			index[offset++]=dimensions*(dimensions-2)+1;
			
			//small triangle
			index[offset++]=dimensions*(dimensions-3);
			index[offset++]=dimensions*(dimensions-2)+1;
			index[offset++]=dimensions*(dimensions-3)+1;
			
			//everything in-between
			for(var y=2;y<=dimensions-5;y+=2){
			
				//large triangle
				index[offset++]=y*dimensions;
				index[offset++]=(y+2)*dimensions;
				index[offset++]=(y+1)*dimensions+1;
			
				//small triangle
				index[offset++]=y*dimensions;
				index[offset++]=(y+1)*dimensions+1;
				index[offset++]=y*dimensions+1;
			
				//small triangle
				index[offset++]=(y+1)*dimensions+1;
				index[offset++]=(y+2)*dimensions;
				index[offset++]=(y+2)*dimensions+1;
			}
		}

		//south
		if(stitching[1]==0){
			//no stitching
			
			//first triangle
			index[offset++]=dimensions*(dimensions-1);
			index[offset++]=dimensions*(dimensions-1)+1;
			index[offset++]=dimensions*(dimensions-2)+1;
			
			//last triangle
			index[offset++]=dimensions*dimensions-1;
			index[offset++]=dimensions*(dimensions-1)-2;
			index[offset++]=dimensions*dimensions-2;
			
			//everything in-between
			for(var x=1;x<=dimensions-3;x++){
				var tris=create_patch (x,dimensions-2,dimensions);
				for(var i=0;i<tris.length;i++){
					index[offset++]=tris[i];
				}
			}
			
		}
		else{
			//stitching
			
			//first triangles
			
			//large triangle
			index[offset++]=dimensions*(dimensions-1);
			index[offset++]=dimensions*(dimensions-1)+2;
			index[offset++]=dimensions*(dimensions-2)+1;
			
			//small triangle
			index[offset++]=dimensions*(dimensions-2)+1;
			index[offset++]=dimensions*(dimensions-1)+2;
			index[offset++]=dimensions*(dimensions-2)+2;
			
			//last triangles
			
			//large triangle
			index[offset++]=dimensions*dimensions-1;
			index[offset++]=dimensions*(dimensions-1)-2;
			index[offset++]=dimensions*dimensions-3;
			//small triangle
			index[offset++]=dimensions*dimensions-3;
			index[offset++]=dimensions*(dimensions-1)-2;
			index[offset++]=dimensions*(dimensions-1)-3;
			
			//everything in-between
			for(var x=2;x<=dimensions-5;x+=2){
				
				//large triangle
				index[offset++]=dimensions*(dimensions-1)+x;
				index[offset++]=dimensions*(dimensions-1)+2+x;
				index[offset++]=dimensions*(dimensions-2)+1+x;
			
				//small triangle
				index[offset++]=dimensions*(dimensions-1)+x;
				index[offset++]=dimensions*(dimensions-2)+1+x;
				index[offset++]=dimensions*(dimensions-2)+x;
			
				//small triangle
				index[offset++]=dimensions*(dimensions-2)+1+x;
				index[offset++]=dimensions*(dimensions-1)+2+x;
				index[offset++]=dimensions*(dimensions-2)+2+x;
			}
		}
		
		//east
		if(stitching[2]==0){
			//no stitching
			
			//first triangle
			index[offset++]=dimensions*dimensions-1;
			index[offset++]=dimensions*(dimensions-1)-1;
			index[offset++]=dimensions*(dimensions-1)-2;
			
			//last triangle
			index[offset++]=dimensions-1;
			index[offset++]=2*dimensions-2;
			index[offset++]=2*dimensions-1;
			
			//everything in-between
			for(var y=1;y<=dimensions-3;y++){
				var tris=create_patch (dimensions-2,y,dimensions);
				for(var i=0;i<tris.length;i++){
					index[offset++]=tris[i];
				}
			}
		}
		else{
			//stitching
			
			//first triangles
			
			//large triangle
			index[offset++]=dimensions*dimensions-1;
			index[offset++]=dimensions*(dimensions-2)-1;
			index[offset++]=dimensions*(dimensions-1)-2;
			
			//small triangle
			index[offset++]=dimensions*(dimensions-1)-2;
			index[offset++]=dimensions*(dimensions-2)-1;
			index[offset++]=dimensions*(dimensions-2)-2;
			
			//last triangles
			
			//large triangle
			index[offset++]=dimensions-1;
			index[offset++]=2*dimensions-2;
			index[offset++]=3*dimensions-1;
			
			//small triangle
			index[offset++]=3*dimensions-1;
			index[offset++]=2*dimensions-2;
			index[offset++]=3*dimensions-2;
			
			//everything in-between
			for(var y=2;y<=dimensions-5;y+=2){
			
				//large triangle
				index[offset++]=(y+1)*dimensions-1;
				index[offset++]=(y+2)*dimensions-2;
				index[offset++]=(y+3)*dimensions-1;
				
				//small triangle
				index[offset++]=(y+2)*dimensions-2;
				index[offset++]=(y+1)*dimensions-1;
				index[offset++]=(y+1)*dimensions-2;
				
				//small triangle
				index[offset++]=(y+3)*dimensions-1;
				index[offset++]=(y+2)*dimensions-2;
				index[offset++]=(y+3)*dimensions-2;
			}
		}
		
		
		//north
		if(stitching[3]==0){
			//no stitching
			
			//first triangle
			index[offset++]=0;
			index[offset++]=dimensions+1;
			index[offset++]=1;
			
			//last triangle
			index[offset++]=dimensions-1;
			index[offset++]=dimensions-2;
			index[offset++]=2*dimensions-2;
			
			//everything in-between
			for(var x=1;x<=dimensions-3;x++){
				var tris=create_patch (x,0,dimensions);
				for(var i=0;i<tris.length;i++){
					index[offset++]=tris[i];
				}
			}
			
		}
		else{
			//stitching
			
			//first triangles
			
			//large triangle
			index[offset++]=0;
			index[offset++]=dimensions+1;
			index[offset++]=2;
			
			//small triangle
			index[offset++]=2;
			index[offset++]=dimensions+1;
			index[offset++]=dimensions+2;
			
			//last triangles
			
			//large triangle
			index[offset++]=dimensions-1;
			index[offset++]=dimensions-3;
			index[offset++]=2*dimensions-2;
			
			//small triangle
			index[offset++]=2*dimensions-2;
			index[offset++]=dimensions-3;
			index[offset++]=2*dimensions-3;
			
			//everything in-between
			for(var x=2;x<=dimensions-5;x+=2){
			
				//large triangle
				index[offset++]=x;
				index[offset++]=dimensions+1+x;
				index[offset++]=2+x;
			
				//small triangle
				index[offset++]=2+x;
				index[offset++]=dimensions+1+x;
				index[offset++]=dimensions+2+x;

				//small triangle
				index[offset++]=dimensions+1+x;
				index[offset++]=x;
				index[offset++]=dimensions+x;
			}
		}
	}
});


})();
