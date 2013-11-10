<?php
require('fpdf.php');

class PDF extends FPDF {
	var $info_array;
	
	function PDF($orientation='P',$unit='mm',$format='A4', $info_array){
		$this->FPDF($orientation,$unit,$format);
		$this->empty_cells = array(3,2,3,1,3,2,3,0);
		$this->pointer_empty_cells = 0;
		$this->info_array = $info_array;
	}

	// Page header
	function Header() {

	    // Title
		if ( $this->PageNo() == 1 ){
	  		// Logo
	    	$this->Image('pdf_conv_cards/ssc_logo.png',20,20,40);
	   	 	// Arial bold 15
	   	 	$this->SetFont('Arial','B',18);
	    	// Move to the right
	    	$this->Cell(55);
	  	  	$this->Cell(10,35,"Savoy Swing Club",0,2,'L');
	   		$this->SetFont('Arial','',12);
	  	  	$this->Cell(0,-20,"PO Box 12114",0,2,'L');
	  	  	$this->Cell(0,30,"Seattle, WA 98102",0,2,'L');
	  	  	$this->Cell(0,-20,"1 (877) 37-SAVOY",0,2,'L');
	    	$this->Cell(82);
			$current_date = date("F j, Y");
	  	  	$this->Cell(10,55,$current_date,0,2,'L');
		}
	    // Line break
	    $this->Ln(40);
	}
	
	function WriteHTML($html) {
	    // HTML parser
	    $html = str_replace("\n",' ',$html);
	    $a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
	    foreach($a as $i=>$e)
	    {
	        if($i%2==0)
	        {
	            // Text
	            if($this->HREF)
	                $this->PutLink($this->HREF,$e);
	            else
	                $this->Write(5,$e);
	        }
	        else
	        {
	            // Tag
	            if($e[0]=='/')
	                $this->CloseTag(strtoupper(substr($e,1)));
	            else
	            {
	                // Extract attributes
	                $a2 = explode(' ',$e);
	                $tag = strtoupper(array_shift($a2));
	                $attr = array();
	                foreach($a2 as $v)
	                {
	                    if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
	                        $attr[strtoupper($a3[1])] = $a3[2];
	                }
	                $this->OpenTag($tag,$attr);
	            }
	        }
	    }
	}

	function OpenTag($tag, $attr)
	{
	    // Opening tag
	    if($tag=='B' || $tag=='I' || $tag=='U')
	        $this->SetStyle($tag,true);
	    if($tag=='A')
	        $this->HREF = $attr['HREF'];
	    if($tag=='BR')
	        $this->Ln(5);
	}
	
	function CloseTag($tag)
	{
	    // Closing tag
	    if($tag=='B' || $tag=='I' || $tag=='U')
	        $this->SetStyle($tag,false);
	    if($tag=='A')
	        $this->HREF = '';
	}
	
	function SetStyle($tag, $enable)
	{
	    // Modify style and select corresponding font
	    $this->$tag += ($enable ? 1 : -1);
	    $style = '';
	    foreach(array('B', 'I', 'U') as $s)
	    {
	        if($this->$s>0)
	            $style .= $s;
	    }
	    $this->SetFont('',$style);
	}

	function PutLink($URL, $txt)
	{
	    // Put a hyperlink
	    $this->SetTextColor(0,0,255);
	    $this->SetStyle('U',true);
	    $this->Write(5,$txt,$URL);
	    $this->SetStyle('U',false);
	    $this->SetTextColor(0);
	}

	// Colored table
	function FancyTable() {
		$this->SetFillColor(224,235,255);
		$length = 54;
		$w = array($length, $length, $length, $length, $length);
		$h = array(82,41,20.5,10.25,5.125);
	    $fill = false;
	    $current_date = date("F j, Y");
		$this->Cell(10);
		$this->Cell(20,-50,$this->info_array[0],0,2,'L');
		$this->Cell(20,60,$this->info_array[1],0,2,'L');
		$this->Cell(20,-50,$this->info_array[2],0,2,'L');
		$this->Cell(20,60,$this->info_array[3],0,2,'L');

	  	$this->Cell(20,-30,"Dear ".$this->info_array[0].",",0,2,'L');
	    // Closing line
		$this->setY($this->getY()+25);
	    $this->Cell(10);
		$text = "Welcome to Savoy Swing Club!  The Savoy Swing Club team would like to welcome you to all the exciting events and discounts we offer.  If you have yet to read about all our benefits, you can visit us online at savoyswing.org.";
		$text2 = "Everyday we are providing new and exciting events and benefits to our users, and we thank you for your recent registration.  You can view your account details at savoyswing.org/members, where you will be able to update your information and view other membership related information.  Attached is your Savoy Swing Club Passport Card, this will be your passport to all our exciting events and partners.";
	$text3 = "If you have any questions, don't hesitate to email us at membership@savoyswing.org or call Toll-free 1(877)-37-SAVOY.";
		$this->MultiCell(168,5,$text);
		$this->setY($this->getY()+5);
	    $this->Cell(10);
		$this->MultiCell(168,5,$text2);
		$this->setY($this->getY()+5);
	    $this->Cell(10);
		$this->MultiCell(168,5,$text3);

		$this->setY($this->getY()+5);
	    $this->Cell(10);
	    $this->SetFont('Arial','B',12);
		$this->Cell(20,10,"Name:",0,0,'L');
		$this->Cell(5,10,"",0,0,'L');
	   	$this->SetFont('Arial','',12);
		$this->Cell(25,10,$this->info_array[0],0,2,'L');
	   	$this->SetFont('Arial','',12);
		$this->setY($this->getY()-5);
	    $this->Cell(10);
	    $this->SetFont('Arial','B',12);
		$this->Cell(20,10,"ID:",0,0,'L');
		$this->Cell(5,10,"",0,0,'L');
	   	$this->SetFont('Arial','',12);
		$this->Cell(25,10,$this->info_array[5],0,2,'L');
		$this->setY($this->getY()-5);
	    $this->Cell(10);
	    $this->SetFont('Arial','B',12);
		$this->Cell(20,10,"Exp Date:",0,0,'L');
		$this->Cell(5,10,"",0,0,'L');
	   	$this->SetFont('Arial','',12);
		$this->Cell(20,10,$this->info_array[6],0,2,'L');


	    $this->Image('pdf_conv_cards/border.jpg',90,192,100);

		$this->setY($this->getY()+55);
	    $this->Cell(10);
		$this->Cell(20,10,"Thank You,",0,2,'L');
		$this->Cell(20,10,"The Savoy Swing Club Team",0,2,'L');
		$this->setY($this->getY()+55);
	    $this->Cell(10);
		$this->MultiCell(168,5,"This page is for Staff Use Only, please ensure that all information is correct and keep for administrative purposes.");


		$this->setY($this->getY()+10);
	    $this->Cell(10);
	    $this->SetFont('Arial','B',12);
		$this->Cell(168,10,"Savoy Swing Club Receipt",1,2,'C');
		$this->setY($this->getY()-10);
	    $this->Cell(10);
		$month_year = date("M j, Y");
		$this->Cell(168,10,$month_year,0,2,'R');
		$this->setY($this->getY());
	    $this->Cell(10);
	    $this->SetFont('Arial','',10);
		$this->Cell(168,10,"Card Holder Name: ".$this->info_array[0],1,2,'L');
		$this->Cell(56,10,"Phone: ".$this->info_array[3],1,0,'L');
		$this->Cell(56,10,"ID: ".$this->info_array[5],1,0,'L');
		$this->Cell(56,10,"Exp Date: ".$this->info_array[6],1,2,'L');
		$this->setY($this->getY());
	    $this->Cell(10);
		$this->MultiCell(168,5,"Address Sent to: \n\t\t\t\t\t\t\t\t\t\t".$this->info_array[1]." \n\t\t\t\t\t\t\t\t\t\t".$this->info_array[2]."\n\n",1,2,'C');
	    $this->Cell(10);
		$this->MultiCell(168,5,"Any Details: \n\n\n\n\n\n\n",1,2,'C');
	}
	
	// Page footer
	function Footer() {
	}
}
?>