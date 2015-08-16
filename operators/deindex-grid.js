
var Xflow = Xflow || {};
var XML3D = XML3D || {};
	
(function() {


Xflow.registerOperator("xflow.deindex", {
    outputs: [
		{type: 'float3', name: 'out_position', customAlloc: true},
		{type: 'float3', name: 'out_normal', customAlloc: true},
		{type: 'float3', name: 'out_texcoord', customAlloc: true},
		{type: 'float3', name: 'barycentric', customAlloc: true}
	],
    params:  [
        { type: 'float3', source: 'position' },
		{ type: 'float3', source: 'normal' },
		{ type: 'float2', source: 'texcoord' },
        { type: 'int', source: 'index'}
    ],
    alloc: function(sizes, position, normal, texcoord, index)
    {
        sizes['out_position'] = index.length;
		sizes['out_normal'] = index.length;
		sizes['out_texcoord'] = index.length;
		sizes['barycentric'] = index.length;

    },
	
    evaluate: function(out_position, out_normal, out_texcoord, barycentric, position, normal, texcoord, index, info)
	{
        for (var i = 0; i < index.length; i+=3)
		{
			var idx = index[i];
			var idx2 = index[i+1];
			var idx3 = index[i+2];
			
			//positions
            out_position[3*i  ] = position[3*idx  ];
            out_position[3*i+1] = position[3*idx+1];
            out_position[3*i+2] = position[3*idx+2];
			
			out_position[3*i+3] = position[3*idx2  ];
            out_position[3*i+4] = position[3*idx2+1];
            out_position[3*i+5] = position[3*idx2+2];
			
			out_position[3*i+6] = position[3*idx3  ];
            out_position[3*i+7] = position[3*idx3+1];
            out_position[3*i+8] = position[3*idx3+2];
			
			//normals
			out_normal[3*i  ] = normal[3*idx  ];
            out_normal[3*i+1] = normal[3*idx+1];
            out_normal[3*i+2] = normal[3*idx+2];
			
			out_normal[3*i+3] = normal[3*idx2  ];
            out_normal[3*i+4] = normal[3*idx2+1];
            out_normal[3*i+5] = normal[3*idx2+2];
			
			out_normal[3*i+6] = normal[3*idx3  ];
            out_normal[3*i+7] = normal[3*idx3+1];
            out_normal[3*i+8] = normal[3*idx3+2];
			
			//texcoords
			out_texcoord[2*i  ] = out_texcoord[2*idx  ];
			out_texcoord[2*i+1] = out_texcoord[2*idx+1];
			
			out_texcoord[2*i+2] = out_texcoord[2*idx2  ];
			out_texcoord[2*i+3] = out_texcoord[2*idx2+1];
			
			out_texcoord[2*i+4] = out_texcoord[2*idx3  ];
			out_texcoord[2*i+5] = out_texcoord[2*idx3+1];
			
			//barycentrics
			barycentric[3*i  ] = 0;
			barycentric[3*i+1] = 0;
			barycentric[3*i+2] = 1;
			
			barycentric[3*i+3] = 0;
			barycentric[3*i+4] = 1;
			barycentric[3*i+5] = 0;
			
			barycentric[3*i+6] = 1;
			barycentric[3*i+7] = 0;
			barycentric[3*i+8] = 0;
			
        }


        return true;
    }
});


})();
