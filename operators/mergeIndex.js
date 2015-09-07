
var Xflow = Xflow || {};
var XML3D = XML3D || {};
	
(function() {



Xflow.registerOperator("xflow.mergeIndex", {
    outputs: [  {type: 'int', name: 'index', customAlloc: true}],
    params:  [  {type: 'int', source: 'index1', array: true},
				{type: 'int', source: 'index2', array: true}],
    alloc: function(sizes,index1,index2)
    {
        sizes['index'] = index1.length+index2.length;
    },
    evaluate: function(index,index1,index2) {
	
		//memcopy/copy
	
		for(var i=0;i<index1.length;i++){
			index[i]=index1[i];
		}
		
		for(var i=0;i<index2.length;i++){
			index[index1.length+i]=index2[i];
		}
		
	}
});


})();
