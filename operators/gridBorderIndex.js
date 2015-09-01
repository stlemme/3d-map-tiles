
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
			
			var d=Math.pow(2,stitching[0]-1);
			
			//first triangles
			
			//large triangle
			index[offset++]=0;
			index[offset++]=2*d*dimensions;
			index[offset++]=d*dimensions+1;
			
			//small triangle(s)
			for(var i=0;i<d;i++){
				index[offset++]=2*d*dimensions;
				index[offset++]=(d+i+1)*dimensions+1;
				index[offset++]=(d+i)*dimensions+1;
			}
			//additional triangle(s)
			for(var i=1;i<d;i++){
				index[offset++]=0;
				index[offset++]=(i+1)*dimensions+1;
				index[offset++]=(i)*dimensions+1;
			}
			//last triangles
			
			//large triangle
			index[offset++]=dimensions*(dimensions-1-2*d);
			index[offset++]=dimensions*(dimensions-1);
			index[offset++]=dimensions*(dimensions-1-d)+1;
			
			//small triangle(s)
			for(var i=0;i<d;i++){
				index[offset++]=dimensions*(dimensions-1-2*d);
				index[offset++]=dimensions*(dimensions-1-d-i)+1;
				index[offset++]=dimensions*(dimensions-1-d-i-1)+1;
			}
			
			//additional triangle(s)
			for(var i=1;i<d;i++){
				index[offset++]=dimensions*(dimensions-1);
				index[offset++]=dimensions*(dimensions-1-i)+1;
				index[offset++]=dimensions*(dimensions-1-i-1)+1;
			}
			
			//everything in-between
			for(var y=2*d;y<=dimensions-4*d-1;y+=2*d){
			
				//large triangle
				index[offset++]=y*dimensions;
				index[offset++]=(y+2*d)*dimensions;
				index[offset++]=(y+d)*dimensions+1;
			
				//small triangle(s)
				for(var i=0;i<d;i++){
					index[offset++]=y*dimensions;
					index[offset++]=(y+1+i)*dimensions+1;
					index[offset++]=(y+i)*dimensions+1;
				}
			
				//small triangle(s)
				for(var i=0;i<d;i++){
					index[offset++]=(y+d+i)*dimensions+1;
					index[offset++]=(y+2*d)*dimensions;
					index[offset++]=(y+d+1+i)*dimensions+1;
				}
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
			
			var d=Math.pow(2,stitching[1]-1);
			
			//first triangles
			
			//large triangle
			index[offset++]=dimensions*(dimensions-1);
			index[offset++]=dimensions*(dimensions-1)+2*d;
			index[offset++]=dimensions*(dimensions-2)+d;
			
			//small triangle(s)
			for(var i=0;i<d;i++){
				index[offset++]=dimensions*(dimensions-2)+d+i;
				index[offset++]=dimensions*(dimensions-1)+2*d;
				index[offset++]=dimensions*(dimensions-2)+d+i+1;
			}
			
			//additional triangle(s)
			for(var i=1;i<d;i++){
				index[offset++]=dimensions*(dimensions-2)+i;
				index[offset++]=dimensions*(dimensions-1);
				index[offset++]=dimensions*(dimensions-2)+i+1;
			}
			//last triangles
			
			//large triangle
			index[offset++]=dimensions*dimensions-1;
			index[offset++]=dimensions*(dimensions-1)-1-d;
			index[offset++]=dimensions*dimensions-1-2*d;
			
			//small triangle(s)
			for(var i=0;i<d;i++){
				index[offset++]=dimensions*dimensions-1-2*d;
				index[offset++]=dimensions*(dimensions-1)-1-2*d+i+1;
				index[offset++]=dimensions*(dimensions-1)-1-2*d+i;
			}
			
			//additional triangle(s)
			for(var i=0;i<d-1;i++){
				index[offset++]=dimensions*dimensions-1;
				index[offset++]=dimensions*(dimensions-1)-1-d+i+1;
				index[offset++]=dimensions*(dimensions-1)-1-d+i;
			}
			
			//everything in-between
			for(var x=2*d;x<=dimensions-4*d-1;x+=2*d){
				
				//large triangle
				index[offset++]=dimensions*(dimensions-1)+x;
				index[offset++]=dimensions*(dimensions-1)+2*d+x;
				index[offset++]=dimensions*(dimensions-2)+d+x;
			
				//small triangle(s)
				for(var i=0;i<d;i++){
					index[offset++]=dimensions*(dimensions-1)+x;
					index[offset++]=dimensions*(dimensions-2)+1+x+i;
					index[offset++]=dimensions*(dimensions-2)+x+i;
				}
				
				//small triangle(s)
				for(var i=0;i<d;i++){
					index[offset++]=dimensions*(dimensions-2)+d+x+i;
					index[offset++]=dimensions*(dimensions-1)+2*d+x;
					index[offset++]=dimensions*(dimensions-2)+d+1+x+i;
				}
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
			
			var d=Math.pow(2,stitching[2]-1);
			
			//first triangles
			
			//large triangle
			index[offset++]=dimensions*dimensions-1;
			index[offset++]=dimensions*(dimensions-2*d)-1;
			index[offset++]=dimensions*(dimensions-d)-2;
			
			//small triangle(s)
			for(var i=0;i<d;i++){
				index[offset++]=dimensions*(dimensions-2*d+i+1)-2;
				index[offset++]=dimensions*(dimensions-2*d)-1;
				index[offset++]=dimensions*(dimensions-2*d+i)-2;
			}
			
			//additional triangle(s)
			for(var i=1;i<d;i++){
				index[offset++]=dimensions*(dimensions-i)-2;
				index[offset++]=dimensions*(dimensions)-1;
				index[offset++]=dimensions*(dimensions-i-1)-2;
			}
			
			//last triangles
			
			//large triangle
			index[offset++]=dimensions-1;
			index[offset++]=(1+d)*dimensions-2;
			index[offset++]=(1+2*d)*dimensions-1;
			
			//small triangle(s)
			for(var i=0;i<d;i++){
				index[offset++]=(1+2*d)*dimensions-1;
				index[offset++]=(1+d+i)*dimensions-2;
				index[offset++]=(1+d+i+1)*dimensions-2;
			}
			
			//additional triangle(s)
			for(var i=1;i<d;i++){
				index[offset++]=dimensions-1;
				index[offset++]=(1+i)*dimensions-2;
				index[offset++]=(1+i+1)*dimensions-2;
			}
			
			//everything in-between
			for(var y=2*d;y<=dimensions-4*d-1;y+=2*d){
			
				//large triangle
				index[offset++]=(y+1)*dimensions-1;
				index[offset++]=(y+1+d)*dimensions-2;
				index[offset++]=(y+1+2*d)*dimensions-1;
				
				//small triangle(s)
				for(var i=0;i<d;i++){
					index[offset++]=(y+1+i+1)*dimensions-2;
					index[offset++]=(y+1)*dimensions-1;
					index[offset++]=(y+1+i)*dimensions-2;
				}
				//small triangle
				for(var i=0;i<d;i++){
					index[offset++]=(y+1+2*d)*dimensions-1;
					index[offset++]=(y+1+d+i)*dimensions-2;
					index[offset++]=(y+1+d+i+1)*dimensions-2;
				}
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
			
			var d=Math.pow(2,stitching[3]-1);
			
			//first triangles
			
			//large triangle
			index[offset++]=0;
			index[offset++]=dimensions+d;
			index[offset++]=2*d;
			
			//small triangle(s)
			for(var i=0;i<d;i++){
				index[offset++]=2*d;
				index[offset++]=dimensions+d+i;
				index[offset++]=dimensions+d+i+1;
			}
			
			//additional triangle(s)
			for(var i=1;i<d;i++){
				index[offset++]=0;
				index[offset++]=dimensions+i;
				index[offset++]=dimensions+i+1;
			}
			
			//last triangles
			
			//large triangle
			index[offset++]=dimensions-1;
			index[offset++]=dimensions-1-2*d;
			index[offset++]=2*dimensions-1-d;
			
			//small triangle(s)
			for(var i=0;i<d;i++){
				index[offset++]=2*dimensions-1-2*d+i+1;
				index[offset++]=dimensions-1-2*d;
				index[offset++]=2*dimensions-1-2*d+i;
			}
			
			//additional triangle(s)
			for(var i=0;i<d-1;i++){
				index[offset++]=2*dimensions-1-d+i+1;
				index[offset++]=dimensions-1;
				index[offset++]=2*dimensions-1-d+i;
			}
			
			//everything in-between
			for(var x=2*d;x<=dimensions-4*d-1;x+=2*d){
			
				//large triangle
				index[offset++]=x;
				index[offset++]=dimensions+d+x;
				index[offset++]=2*d+x;
			
				//small triangle(s)
				for(var i=0;i<d;i++){
					index[offset++]=2*d+x;
					index[offset++]=dimensions+d+x+i;
					index[offset++]=dimensions+d+1+x+i;
				}
				//small triangle(s)
				for(var i=0;i<d;i++){
					index[offset++]=dimensions+1+x+i;
					index[offset++]=x;
					index[offset++]=dimensions+x+i;
				}
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
