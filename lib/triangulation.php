<?php

//ported from 3d-map-tiles.js

class Triangulate{
	private $contour=array();
	private $points;
	private $epsilon=0.0000000001;
	
 
	public function __construct($contour){
		$this->contour=$contour;
		$this->points=(count($contour)/3)-1;
	}
	
	public function GetX($idx){
		return $this->contour[3*$idx];
	}
	public function GetY($idx){
		return $this->contour[3*$idx+2];
	}

	public function Area($V){
		$n=count($V);
		$A=0.0;
		
		for( $p=$n-1,  $q=0; $q<$n; $p=$q++){
			$A+= $this->GetX($V[$p])*$this->GetY($V[$q])- $this->GetX($V[$q])*$this->GetY($V[$p]);
		}
		return 0.5*$A;
	}

	public function InsideTriangle($Ax,$Ay,$Bx,$By,$Cx,$Cy,$Px,$Py){
	
		$ax= $Cx-$Bx;
		$ay= $Cy-$By;
		$bx= $Ax-$Cx;
		$by= $Ay-$Cy;
		$cx= $Bx-$Ax;
		$cy= $By-$Ay;
	
		$apx=$Px-$Ax;
		$apy=$Py-$Ay;
		$bpx=$Px-$Bx;
		$bpy=$Py-$By;
		$cpx=$Px-$Cx;
		$cpy=$Py-$Cy;
	
		$aCROSSbp = $ax*$bpy - $ay*$bpx;
		$cCROSSap = $cx*$apy - $cy*$apx;
		$bCROSScp = $bx*$cpy - $by*$cpx;
	
		return(($aCROSSbp>=0.0)&&($cCROSSap>=0.0)&&($bCROSScp>=0.0));
	}

	public function Snip($u,$v,$w,$n,$V){
		$p;
		$Px;
		$Py;
		
		$Ax=$this->GetX($V[$u]);
		$Ay=$this->GetY($V[$u]);
		
		$Bx=$this->GetX($V[$v]);
		$By=$this->GetY($V[$v]);
		
		$Cx=$this->GetX($V[$w]);
		$Cy=$this->GetY($V[$w]);
	
		if($this->epsilon > ((($Bx-$Ax)*($Cy-$Ay)) - (($By-$Ay)*($Cx-$Ax)))){
			return false;
		}
	
		//is it possible to cut out the triangle without any point lying inside of it?
		for($p=0; $p<$n; $p++){
			if(($p==$u) || ($p==$v) || ($p==$w)){
				continue;
			}
			
			$Px= $this->GetX($V[$p]);
			$Py= $this->GetY($V[$p]);
			
			if($this->InsideTriangle($Ax,$Ay,$Bx,$By,$Cx,$Cy,$Px,$Py)){
				return false;
			}
		}
		return true;
	}

	public function Process($U){
		$result=array();
		$n=$this->points;
		if($n<3){
			return $result;
		}
		
		if(is_null($U)){
			$U= array();
			for( $v=0;$v<$n;$n++){
			$U[$v]=$v;
			}
		}
		else{
			$this->points = $n = count($U);
		}
		$V = array();
		
		if(0.0< $this->Area($U)){
			for( $v=0;$v<$n;$v++){
				$V[$v]=$U[$v];
			}
		}
		else{
			for( $v=0;$v<$n;$v++){
				$V[$v]=$U[($n-1)-$v];
			}
		}
		
		$nv=$n;
		// remove nv-2 Vertices, creating 1 triangle every time
		
		$count = 2*$nv; // error detection
	
		for( $m=0,  $v=$nv-1;$nv>2;){
			// if we loop, it is probably a non-simple polygon
			if(0>=($count--)){
				// Triangulate: ERROR - probable bad polygon!
				$test=array();
				return $test;
			}
			// three consecutive vertices in current polygon, <u,v,w>
			$u=$v; if($nv<=$u) $u=0; //previous vertex
			$v=$u+1; if($nv<=$v) $v=0;   //new v
			$w=$v+1;if($nv<=$w) $w=0;//next
			
			if($this->Snip($u,$v,$w,$nv,$V)){
				
				$a=$V[$u];
				$b=$V[$v];
				$c=$V[$w];
				
				$s;
				$t;
				
				// why do i have to change order here???
				// if i do not change order, normals are 0,-1,0! why is that so?

				
				$result[]= $a;
				$result[]= $c;
				$result[]= $b;
				
				$m++;
				
				for($s=$v,$t=$v+1; $t<$nv; $s++,$t++){
					$V[$s]=$V[$t];
				}
				$nv--;
				
				$count=2*$nv;
			
			}
			
		}
		
		return $result;
	}

	//x-flow Operatoren as static functions!
	
	//triangulate polygon not ported!

	public static function extrudePolygon($contour,$height){
		$position=array();
		$index=array();
	
		$points= (count($contour)/3)-1;
		$nv=2*$points;
		
		// clone contour points
		for ( $i=0; $i<$points; $i++){
			$position[] = $contour[3*$i  ];
            $position[] = $contour[3*$i+1];
            $position[] = $contour[3*$i+2];

            $position[] = $contour[3*$i  ];
            $position[] = $contour[3*$i+1] + $height;
            $position[] = $contour[3*$i+2];
		
		}
		
		// generate indices for the walls
        for ( $i = 0; $i < $points; $i++)
		{
			$tp =  2* $i;
			$np = (2*($i+1)) % $nv;
			
			// TODO: check order in terms of cracks caused by interpolation issues
            $index[6*$i  ] = $tp+1;
            $index[6*$i+1] = $np;
            $index[6*$i+2] = $tp;
			
            $index[6*$i+3] = $np;
            $index[6*$i+4] = $tp+1;
            $index[6*$i+5] = $np+1;
		}
		
		// generate indices for the roof
		
		$tri = new Triangulate($position);
		// use every odd point from position, which is the upper contour
		$V = array();
		for ( $i = 1; $i < $nv; $i+=2){
			$V[]=$i;
		}
		$result=$tri->Process($V);
		$offset = 6*$points;
		for ( $i = 0; $i < count($result); $i++){
			$index[$offset+$i] = $result[$i];
		}
		
		$ret=array();
		$ret[]=$position;
		$ret[]=$index;
        return $ret;
	}

	public static function deindex($position,$index){
		$out_position=array();
		
		for ( $i=0; $i<count($index);$i++){
			 $idx=$index[$i];
			$out_position[3*$i  ] = $position[3*$idx  ];
            $out_position[3*$i+1] = $position[3*$idx+1];
            $out_position[3*$i+2] = $position[3*$idx+2];
		}
	
		return $out_position;
	}

	public static function planeXZ($in_position){
		$out_position=array();
	
		for ( $i = 0; $i < count($in_position); $i+=2)
		{
            $out_position[] = $in_position[$i  ];
            $out_position[] = 0.0;
            $out_position[] = $in_position[$i+1];
        }
		return $out_position;
	}
	
	public static function generateFaceNormal($position){
		$iteratecount=count($position)/3; //number of points
		$normal=array();
		
		for ( $i = 0; $i < $iteratecount; $i+=3){
			
			$A = 3* $i;
			$B = 3*($i+1);
			$C = 3*($i+2);
				
			$Ax = $position[$A  ];
			$Ay = $position[$A+1];
			$Az = $position[$A+2];

			$Bx = $position[$B  ];
			$By = $position[$B+1];
			$Bz = $position[$B+2];

			$Cx = $position[$C  ];
			$Cy = $position[$C+1];
			$Cz = $position[$C+2];
			
			$Ux = $Bx - $Ax;
			$Uy = $By - $Ay;
			$Uz = $Bz - $Az;
			
			$Vx = $Cx - $Ax;
			$Vy = $Cy - $Ay;
			$Vz = $Cz - $Az;
		
			$N = array();
		
			$N[]= $Uy*$Vz - $Uz*$Vy;
			$N[]= $Uz*$Vx - $Ux*$Vz;
			$N[]= $Ux*$Vy - $Uy*$Vx;
			// normalize
			$l = sqrt($N[0]*$N[0]+$N[1]*$N[1]+$N[2]*$N[2]);
			for ( $j=0; $j<3; $j++){
				$N[$j] = $N[$j] / $l;
			}
			/*
			for ( $j=0; $j<3; $j++) {
				$normal[$A+$j] = $N[$j];
				$normal[$B+$j] = $N[$j];
				$normal[$C+$j] = $N[$j];
			}
			*/
			
			for ( $j=0; $j<9; $j++) {
				$normal[] = $N[$j%3];
			}
			
			
			
			
		}
		return $normal;
	}

	public static function ensureCCWContour ($in_contour){
		$tn = count($in_contour);
		$n = $tn/2;
		$A = 0.0;
	
		for ( $p=$n-1, $q=0; $q<$n; $p=$q++){
			$A += $in_contour[2*$p]*$in_contour[2*$q+1] - $in_contour[2*$q]*$in_contour[2*$p+1];
		}
		
		if (0.5*$A < 0.0) {
			$out_contour=array();
			// reverse order of vertices
			for ( $i = 0; $i < $tn; $i+=2) {
				$out_contour[$i  ] = $in_contour[$tn-$i-2];
				$out_contour[$i+1] = $in_contour[$tn-$i-1];
			}
			return $out_contour;
		}
		
		else{
			return $in_contour;
		}
	
	}
	
	
}
?>