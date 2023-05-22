<?
class PDF extends FPDF{
	var $areaUtil;
	var $orientacion = "P";
	var $widths;
	var $aligns;
	var $fills;
	var $borders;
	var $rowHeight=5;
	
	function Header(){
		$this->areaUtil=$this->w - $this->rMargin - $this->lMargin;

	}

	function Footer(){
	}

	function texto_centrado($x,$y,$width,$height,$interlineado,$texto,$borde=0){
		if($borde==1) $this->Rect($x,$y,$width,$height);
		$lineas=$this->NbLines($width,$texto);
		$alto_necesario=$lineas*$interlineado;
		$this->SetXY($x,$y+($height-$alto_necesario)/2);
		$this->MultiCell($width,$interlineado,$texto,0,'C');
		$this->SetXY($x+$width,$y);
	}


	function SetWidths($w)
	{
		//Set the array of column widths
		$this->widths=$w;
	}

	function SetAligns($a)
	{
		//Set the array of column alignments
		$this->aligns=$a;
	}

	function SetFills($a)
	{
		//Set the array of column alignments
		$this->fills=$a;
	}

	function SetBorders($a)
	{
		//Set the array of column alignments
		$this->borders=$a;
	}

	function SetFontBolds($a)
	{
		//Si alguna columna en particular va en negrilla
		$this->fontBolds=$a;
	}

	function Row($data)
	{
		//Calculate the height of the row
		$x=$this->getX();
//		preguntar($x);
		$nb=0;
		for($i=0;$i<count($data);$i++)
			$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
		$h=$this->rowHeight*$nb;
		//Issue a page break first if needed
		$this->CheckPageBreak($h);
		$y_inicial=$this->getY();
		$this->setXY($x,$this->getY());
		//Draw the cells of the row
		for($i=0;$i<count($data);$i++)
		{
			$w=$this->widths[$i];
			$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
			$f=isset($this->fills[$i]) ? $this->fills[$i] : 0;
			//Save the current position
			$x=$this->GetX();
			$y=$this->GetY();
			//Print the text
			$cellHeight=$this->NbLines($this->widths[$i],$data[$i]) * $this->rowHeight;
			if($cellHeight<$h){
				$diff=($h-$cellHeight)/2;
				$this->SetXY($x,$y+$diff);
			}
			if(isset($this->fontBolds[$i])) $this->SetFont('',$this->fontBolds[$i]);
			$this->MultiCell($w,$this->rowHeight,$data[$i],0,$a,$f);
			//Draw the border
			if(!isset($this->borders[$i]) || $this->borders[$i]==1) $this->Rect($x,$y,$w,$h);
			else{
				if(preg_match("/u/i",$this->borders[$i])) $this->line($x,$y,$x+$w,$y);
				if(preg_match("/l/i",$this->borders[$i])) $this->line($x,$y,$x,$y+$h);
				if(preg_match("/r/i",$this->borders[$i])) $this->line($x+$w,$y,$x+$w,$y+$h);
				if(preg_match("/b/i",$this->borders[$i])) $this->line($x,$y+$h,$x+$w,$y+$h);
			}
			//Put the position to the right of the cell
			$this->SetXY($x+$w,$y);
		}
		//Go to the next line
		$this->Ln($h);
		return($y_inicial);
	}

	function CheckPageBreak($h)
	{
		//If the height h would cause an overflow, add a new page immediately
		if($this->GetY()+$h>$this->PageBreakTrigger)
			$this->AddPage($this->CurOrientation);
	}

	function NbLines($w,$txt)
	{
		//Computes the number of lines a MultiCell of width w will take
		$cw=&$this->CurrentFont['cw'];
		if($w==0)
			$w=$this->w-$this->rMargin-$this->x;
		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
		if($nb>0 and $s[$nb-1]=="\n")
			$nb--;
		$sep=-1;
		$i=0;
		$j=0;
		$l=0;
		$nl=1;
		while($i<$nb)
		{
			$c=$s[$i];
			if($c=="\n")
			{
				$i++;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
				continue;
			}
			if($c==' ')
				$sep=$i;
			$l+=$cw[$c];
			if($l>$wmax)
			{
				if($sep==-1)
				{
					if($i==$j)
						$i++;
				}
				else
					$i=$sep+1;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
			}
			else
				$i++;
		}
		return $nl;
	}

	var $angle=0;

	function Rotate($angle,$x=-1,$y=-1)
	{
		if($x==-1)
			$x=$this->x;
		if($y==-1)
			$y=$this->y;
		if($this->angle!=0)
			$this->_out('Q');
		$this->angle=$angle;
		if($angle!=0)
		{
			$angle*=M_PI/180;
			$c=cos($angle);
			$s=sin($angle);
			$cx=$x*$this->k;
			$cy=($this->h-$y)*$this->k;
			$this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
		}
	}

	function RotatedText($x,$y,$txt,$angle)
	{
		//Text rotated around its origin
		$this->Rotate($angle,$x,$y);
		$this->Text($x,$y,$txt);
		$this->Rotate(0);
	}

	function _endpage()
	{
		if($this->angle!=0)
		{
			$this->angle=0;
			$this->_out('Q');
		}
		parent::_endpage();
	}


	function MemImage($data, $x, $y, $w=0, $h=0, $link='')
	{
		//Put the PNG image stored in $data
		$id = md5($data);
		if(!isset($this->images[$id]))
		{
			$info = $this->_parsemempng( $data );
			$info['i'] = count($this->images)+1;
			$this->images[$id]=$info;
		}
		else
			$info=$this->images[$id];

		//Automatic width and height calculation if needed
		if($w==0 and $h==0)
		{
			//Put image at 72 dpi
			$w=$info['w']/$this->k;
			$h=$info['h']/$this->k;
		}
		if($w==0)
			$w=$h*$info['w']/$info['h'];
		if($h==0)
			$h=$w*$info['h']/$info['w'];
		$this->_out(sprintf('q %.2f 0 0 %.2f %.2f %.2f cm /I%d Do Q',$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$info['i']));
		if($link)
			$this->Link($x,$y,$w,$h,$link);
	}

	function GDImage($im, $x, $y, $w=0, $h=0, $link='')
	{
		//Put the GD image $im
		ob_start();
		imagepng($im);
		$data = ob_get_contents();
		ob_end_clean();
		$this->MemImage($data, $x, $y, $w, $h, $link);
	}

	// PRIVATE FUNCTIONS
	//
	function _readstr($var, &$pos, $n)
	{
		//Read some bytes from string
		$string = substr($var, $pos, $n);
		$pos += $n;
		return $string;
	}

	function _readstr_int($var, &$pos)
	{
		//Read a 4-byte integer from string
		$i =ord($this->_readstr($var, $pos, 1))<<24;
		$i+=ord($this->_readstr($var, $pos, 1))<<16;
		$i+=ord($this->_readstr($var, $pos, 1))<<8;
		$i+=ord($this->_readstr($var, $pos, 1));
		return $i;
	}


	
	function _parsemempng($var)
	{
		$pos=0;
		//Check signature
		$sig = $this->_readstr($var,$pos, 8);
		if($sig != chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10))
			$this->Error('Not a PNG image');
		//Read header chunk
		$this->_readstr($var,$pos,4);
		$ihdr = $this->_readstr($var,$pos,4);
		if( $ihdr != 'IHDR')
			$this->Error('Incorrect PNG Image');
		$w=$this->_readstr_int($var,$pos);
		$h=$this->_readstr_int($var,$pos);
		$bpc=ord($this->_readstr($var,$pos,1));
		if($bpc>8)
			$this->Error('16-bit depth not supported: '.$file);
		$ct=ord($this->_readstr($var,$pos,1));
		if($ct==0)
			$colspace='DeviceGray';
		elseif($ct==2)
			$colspace='DeviceRGB';
		elseif($ct==3)
			$colspace='Indexed';
		else
			$this->Error('Alpha channel not supported: '.$file);
		if(ord($this->_readstr($var,$pos,1))!=0)
			$this->Error('Unknown compression method: '.$file);
		if(ord($this->_readstr($var,$pos,1))!=0)
			$this->Error('Unknown filter method: '.$file);
		if(ord($this->_readstr($var,$pos,1))!=0)
			$this->Error('Interlacing not supported: '.$file);
		$this->_readstr($var,$pos,4);
		$parms='/DecodeParms <</Predictor 15 /Colors '.($ct==2 ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w.'>>';
		//Scan chunks looking for palette, transparency and image data
		$pal='';
		$trns='';
		$data='';
		do
		{
			$n=$this->_readstr_int($var,$pos);
			$type=$this->_readstr($var,$pos,4);
			if($type=='PLTE')
			{
				//Read palette
				$pal=$this->_readstr($var,$pos,$n);
				$this->_readstr($var,$pos,4);
			}
			elseif($type=='tRNS')
			{
				//Read transparency info
				$t=$this->_readstr($var,$pos,$n);
				if($ct==0)
					$trns=array(ord(substr($t,1,1)));
				elseif($ct==2)
					$trns=array(ord(substr($t,1,1)),ord(substr($t,3,1)),ord(substr($t,5,1)));
				else
				{
					$pos=strpos($t,chr(0));
					if(is_int($pos))
						$trns=array($pos);
				}
				$this->_readstr($var,$pos,4);
			}
			elseif($type=='IDAT')
			{
				//Read image data block
				$data.=$this->_readstr($var,$pos,$n);
				$this->_readstr($var,$pos,4);
			}
			elseif($type=='IEND')
				break;
			else
				$this->_readstr($var,$pos,$n+4);
		}
		while($n);
		if($colspace=='Indexed' and empty($pal))
			$this->Error('Missing palette in '.$file);
		return array('w'=>$w,
				'h'=>$h,
				'cs'=>$colspace,
				'bpc'=>$bpc,
				'f'=>'FlateDecode',
				'parms'=>$parms,
				'pal'=>$pal,
				'trns'=>$trns,
				'data'=>$data);
	}





	

}

?>
