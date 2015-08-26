<?php
$target_path = "./";
$target_path = basename( $_FILES['file']['name']); 

if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
    if(isset($_REQUEST['num']) && is_numeric($_REQUEST['num'])){
        $num = (int)$_REQUEST['num'];
    }
    else {
        $num = 1; 
    }
    
	split_pdf("$target_path", 'split/', $num);
    $url_home = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/";
	echo "<a href='".$url_home."'>Click to go back</a>";
} else{
    echo "There was an error uploading the file, please try again!";
}

function split_pdf($filename, $end_directory = false, $num = 1)
{

	require_once('fpdf/fpdf.php');
	require_once('fpdi/fpdi.php');
	
	$end_directory = $end_directory ? $end_directory.date("d-m-Y__H-i-s")."/" : './';
    
	$new_path = preg_replace('/[\/]+/', '/', $end_directory.'/'.substr($filename, 0, strrpos($filename, '/')));
	
	if (!is_dir($new_path))
	{
		// Will make directories under end directory that don't exist
		// Provided that end directory exists and has the right permissions
		mkdir($new_path, 0777, true);
	}
	
	$pdf = new FPDI();
	$pagecount = $pdf->setSourceFile($filename); // How many pages?
	
    $j = 0;
	// Split each page into a new PDF
    $new_pdf = new FPDI();
	for ($i = 1; $i <= $pagecount; $i++) {
     
		$new_pdf->AddPage();
		$new_pdf->setSourceFile($filename);
		$new_pdf->useTemplate($new_pdf->importPage($i));
		
        if( ($i != 1) && ($i % $num == 0) ||  ($num == 1) ) {
            try {
                $new_filename = $end_directory.str_replace('.pdf', '', $filename).'_'.$i.".pdf";
                $new_pdf->Output($new_filename, "F");
                $url = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/";
                echo "File: <a href='".$url.$new_filename."'>".$url.$new_filename."</a><br />\n";
            } catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
            
            unset($new_pdf);
            $new_pdf = new FPDI();
            
            $j = 0;
        }
        $j++;
        
	}

    if($j != 0) {
        try {
            $new_filename = $end_directory.str_replace('.pdf', '', $filename).'_'.($i-1).".pdf";
            $new_pdf->Output($new_filename, "F");
            $url = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/";
            echo "File: <a href='".$url.$new_filename."'>".$url.$new_filename."</a><br />\n";
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        } 
    }

}

?>
