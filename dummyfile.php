<?php 


  
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/timespent_form.php');
require_once(dirname(__FILE__) . '/testform.php');
require_once($CFG->libdir . '/adminlib.php');
//require_once($CFG->libdir.'/pdflib.php');
require_once ($CFG->libdir . '/tcpdf/tcpdf.php');

 
    $filename='mypdffile.pdf';
    $loc=$CFG->dataroot.'/'.$filename;
  	$homepage = $newtext;
  	$pdf = new TCPDF('L');     // ‘L’ for Landscape mode, P for Portrait mode
	$pdf->AddPage();
  	$pdf->SetFont("freeserif", '', 11); // Font settings
  	$pdf->writeHTML('<p>This is an example</p>', true, false, true, false, ''); 
  	$pdf->Output($loc, "D");
    






?>