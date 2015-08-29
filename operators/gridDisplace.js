
var Xflow = Xflow || {};
var XML3D = XML3D || {};
	
(function() {


Xflow.registerOperator("xflow.gridDisplace", {
    outputs: [	{type: 'float3', name: 'position', customAlloc: true}],
    params:  [  {type: 'float3', source: 'in_position', array: true},
				{type: 'float', source: 'elevation', array: true}],
	
    alloc: function(sizes, in_position,elevation)
    {
        sizes['position'] = elevation.length;
    },
    evaluate: function(position, in_position, elevation) {
		for (var i=0;i<elevation.length;i++){
			var offset=3*i;
			position[offset]=in_position[offset];
			position[offset+1]=elevation[i];
			position[offset+2]=in_position[offset+2];
		}
	}
});


})();
