<?php
// ===========================================================================================
//
// PShowStyle.php
//
// An implementation of a PHP pagecontroller for a web-site.
//
// Shows a directory listning and change the stylesheet dynamic.
//

// -------------------------------------------------------------------------------------------
//
// Settings for this pagecontroller.
//

// Separator between directories and files, change between Unix/Windows
$SEPARATOR = '/'; 	// Unix, Linux, MacOS, Solaris
//$SEPARATOR = '\\'; 	// Windows (have not verified it on Windows yet) 

// Show the content of files named config.php, except the rows containing DB_USER, DB_PASSWORD
//$HIDE_DB_USER_PASSWORD = FALSE; 
$HIDE_DB_USER_PASSWORD = TRUE; 

// Which directory to use as basedir
$BASEDIR = TP_PAGESPATH . 'app_syw' . $SEPARATOR . 'stylesheets' . $SEPARATOR;

// The link to this page
$HREF = '?p=style';


// -------------------------------------------------------------------------------------------
//
// Page specific code
//

$html = <<<EOD
<h2>En stylesheet i delar</h2>
<p>
Nedanstående stylesheet finns, testa dem genom att klicka på dem. Orginal är den som vi brukar använda.
De andra innehåller endast de delar som namnet anger.
</p>
EOD;


//
// Verify the input variable _GET, no tampering with it
//
$currentdir	= isset($_GET['dir']) ? $_GET['dir'] : '';

$fullpath1 	= realpath($BASEDIR);
$fullpath2 	= realpath($BASEDIR . $currentdir);
$len = strlen($fullpath1);
if(	strncmp($fullpath1, $fullpath2, $len) !== 0 ||
	strcmp($currentdir, substr($fullpath2, $len+1)) !== 0 ) {
	die('Tampering with directory?');
}
$fullpath = $fullpath2;
$currpath = substr($fullpath2, $len+1);


// -------------------------------------------------------------------------------------------
//
// Show the name of the current directory
//
$start		= basename($fullpath1);
$dirname 	= basename($fullpath);
$html .= <<<EOD
<p>
> <a href='{$HREF}&amp;dir='>{$start}</a>{$SEPARATOR}{$currpath}
</p>
EOD;



// -------------------------------------------------------------------------------------------
//
// Open and read a directory, show its content
//
$dir 	= $fullpath;
$curdir1 = empty($currpath) ? "" : "{$currpath}{$SEPARATOR}";
$curdir2 = empty($currpath) ? "" : "{$currpath}";

$list = Array();
if(is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
        	if($file != '.' && $file != '..' && $file != '.svn') {
        		$curfile = $fullpath . $SEPARATOR . $file;
        		if(is_dir($curfile)) {
          	  		$list[$file] = "<a href='{$HREF}&amp;dir={$curdir1}{$file}'>{$file}{$SEPARATOR}</a>";
          	  	} else if(is_file($curfile)) {
          	  	   	$list[$file] = "<a href='{$HREF}&amp;dir={$curdir2}&amp;file={$file}'>{$file}</a>";
          	  	}
          	 }
        }
        closedir($dh);
    }
}

ksort($list);

$html .= '<p>';
foreach($list as $val => $key) {
	$html .= "{$key}<br />";
}
$html .= '</p>';


// -------------------------------------------------------------------------------------------
//
// Show the content of a file, is set
//
$dir 	= $fullpath;
$file	= "";

if(isset($_GET['file'])) {
	$file = basename($_GET['file']);

	$content = htmlspecialchars(file_get_contents($dir . $SEPARATOR . $file, 'FILE_TEXT'));

	// Remove password and user from config.php, if enabled
	if($HIDE_DB_USER_PASSWORD == TRUE && $file == 'config.php') {

		$pattern[0] 	= '/(DB_PASSWORD|DB_USER)(.+)/';
		$replace[0] 	= '/* <em>\1,  is removed and hidden for security reasons </em> */ );';
		
		$content = preg_replace($pattern, $replace, $content);
	}
	
	$html .= <<<EOD
<fieldset class=code>
<legend><a href='{$HREF}'>{$file}</a></legend>
<pre>
{$content}
</pre>
</fieldset>
EOD;
}


// -------------------------------------------------------------------------------------------
//
// Create and print out the resulting page
//
require_once(TP_SOURCEPATH . 'CHTMLPage.php');

$style = empty($file) ? 'original.css' : $file;
$style = "pages/app_syw/stylesheets/{$style}";

$page = new CHTMLPage($style);

$page->printHTMLHeader('Pröva olika stylesheets');
$page->printPageHeader();
$page->printPageBody($html);
$page->printPageFooter();


?>
