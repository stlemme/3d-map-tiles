
var Xflow = Xflow || {};
var XML3D = XML3D || {};
	
(function() {




Xflow.registerOperator("xflow.simpleStitching", {
    outputs: [	{type: 'float', name: 'out_elevation', customAlloc: true},
				{type: 'float3', name: 'out_normal', customAlloc: true}],
    params:  [  {type: 'int', source: 'lod', array: true},
				{type: 'float3', source: 'normal', array: true},
				{type: 'float', source: 'elevation', array: true},
				{type: 'int', source: 'stitching', array: true}],
    alloc: function(sizes,lod,normal,elevation,stitching)
    {
        sizes['out_elevation'] = elevation.length;
		sizes['out_normal'] = elevation.length;
    },
    evaluate: function(out_elevation,out_normal,lod,normal,elevation,stitching) {
		var dimensions=Math.pow(2,lod[0])+1;
		
		for(var i=0;i<elevation.length;i++){
			out_elevation[i]=elevation[i];
			out_normal[3*i]=normal[3*i];
			out_normal[3*i+1]=normal[3*i+1];
			out_normal[3*i+2]=normal[3*i+2];
		}
		
        //west
		if(stitching[0]>0){
			var d=Math.pow(2,stitching[0]);
			
			for(var y=0;y<dimensions-1;y+=d){
				for(var i=1;i<d;i++){
					var t=i/d;
					out_elevation[(y+i)*dimensions]=(1-t)*elevation[y*dimensions]+t*elevation[(y+d)*dimensions];
					
					var n1=(1-t)*normal[y*dimensions*3]+t*normal[(y+d)*dimensions*3];
					var n2=(1-t)*normal[y*dimensions*3+1]+t*normal[(y+d)*dimensions*3+1];
					var n3=(1-t)*normal[y*dimensions*3+2]+t*normal[(y+d)*dimensions*3+2];
					
					var l=Math.sqrt(Math.pow(n1,2)+Math.pow(n2,2)+Math.pow(n3,2));
					
					out_normal[(y+i)*dimensions*3]=n1/l;
					out_normal[(y+i)*dimensions*3+1]=n2/l;
					out_normal[(y+i)*dimensions*3+2]=n3/l;	
				}
			}
		
		}
		
		//south
		if(stitching[1]>0){
		
			var d=Math.pow(2,stitching[1]);
			
			for(var x=0;x<dimensions-1;x+=d){
				for(var i=1;i<d;i++){
					var t=i/d;
					out_elevation[(dimensions-1)*dimensions+x+i]=(1-t)*elevation[(dimensions-1)*dimensions+x]+t*elevation[(dimensions-1)*dimensions+x+d];
					
					var n1=(1-t)*normal[((dimensions-1)*dimensions+x)*3]+t*normal[((dimensions-1)*dimensions+x+d)*3];
					var n2=(1-t)*normal[((dimensions-1)*dimensions+x)*3+1]+t*normal[((dimensions-1)*dimensions+x+d)*3+1];
					var n3=(1-t)*normal[((dimensions-1)*dimensions+x)*3+2]+t*normal[((dimensions-1)*dimensions+x+d)*3+2];
					
					var l=Math.sqrt(Math.pow(n1,2)+Math.pow(n2,2)+Math.pow(n3,2));
					
					out_normal[((dimensions-1)*dimensions+x+i)*3]=n1/l;
					out_normal[((dimensions-1)*dimensions+x+i)*3+1]=n2/l;
					out_normal[((dimensions-1)*dimensions+x+i)*3+2]=n3/l;
				}
			}
		
		}
		
		//east
		if(stitching[2]>0){
		
			var d=Math.pow(2,stitching[2]);
		
			for(var y=0;y<dimensions-1;y+=d){
				for(var i=1;i<d;i++){
					var t=i/d;
					out_elevation[(y+i)*dimensions+dimensions-1]=(1-t)*elevation[y*dimensions+dimensions-1]+t*elevation[(y+d)*dimensions+dimensions-1];
					
					var n1=(1-t)*normal[(y*dimensions+dimensions-1)*3]+t*normal[((y+d)*dimensions+dimensions-1)*3];
					var n2=(1-t)*normal[(y*dimensions+dimensions-1)*3+1]+t*normal[((y+d)*dimensions+dimensions-1)*3+1];
					var n3=(1-t)*normal[(y*dimensions+dimensions-1)*3+2]+t*normal[((y+d)*dimensions+dimensions-1)*3+2];	
					
					var l=Math.sqrt(Math.pow(n1,2)+Math.pow(n2,2)+Math.pow(n3,2));
					
					out_normal[((y+i)*dimensions+dimensions-1)*3]=n1/l;
					out_normal[((y+i)*dimensions+dimensions-1)*3+1]=n2/l;
					out_normal[((y+i)*dimensions+dimensions-1)*3+2]=n3/l;
				}
			}
		}
		
		//north
		if(stitching[3]>0){
		
		var d=Math.pow(2,stitching[3]);
			
			for(var x=0;x<dimensions-1;x+=d){
				for(var i=1;i<d;i++){
					var t=i/d;
					out_elevation[x+i]=(1-t)*elevation[x]+t*elevation[x+d];
					
					var n1=(1-t)*normal[x*3]+t*normal[(x+d)*3];
					var n2=(1-t)*normal[x*3+1]+t*normal[(x+d)*3+1];
					var n3=(1-t)*normal[x*3+2]+t*normal[(x+d)*3+2];
					
					var l=Math.sqrt(Math.pow(n1,2)+Math.pow(n2,2)+Math.pow(n3,2));
					
					out_normal[(x+i)*3]=n1/l;
					out_normal[(x+i)*3+1]=n2/l;
					out_normal[(x+i)*3+2]=n3/l;
				}
			}
		
		}
	}
});


})();
