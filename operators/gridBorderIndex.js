
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


Xflow.registerOperator("xflow.gridBorderIndex", {
    outputs: [	{type: 'int', name: 'index', customAlloc: true}],
    params:  [  {type: 'int', source: 'lod', array: true},
				{type: 'int', source: 'stitching', array: true},],
    alloc: function(sizes, lod , stitching)
    {
		//warning! code will only work for lod>=2!!
		var dimensions=Math.pow(2,lod[0])+1;
		
		//size if no stitching, otherwise: smaller and filled with degenerate triangles	
        sizes['index'] = (dimensions-2)*24;
    },
    evaluate: function(index, lod, stitching) {
		var dimensions=Math.pow(2,lod[0])+1;
		
		
        // Create Indices for triangles
		var offset = 0;
		
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
				offset=create_patch (0,y,dimensions,offset,index);
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
				offset=create_patch (x,dimensions-2,dimensions,offset,index);
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
				offset=create_patch (dimensions-2,y,dimensions,offset,index);
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
				offset=create_patch (x,0,dimensions,offset,index);
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
		
		var buffersize=(dimensions-2)*24;
		//fill remaining buffer size with degenerate triangles
		while(offset<buffersize){
			index[offset++]=0;
		}
	}
});


})();
