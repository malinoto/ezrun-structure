<?php
if($_SESSION["verify"] != "FileManager4TinyMCE") die('forbidden');


//------------------------------------------------------------------------------
// DON'T COPY THIS VARIABLES IN .config FILES
//------------------------------------------------------------------------------

require_once(dirname(__FILE__) . '/../../../../../common/config.php');
$root = PATH; // don't touch this configuration

//**********************
//Path configuration
//**********************
// In this configuration the folder tree is
// root
//   |- responsivefm
//   |    |- source <- upload folder
//   |    |- js
//   |    |   |- tinymce
//   |    |   |    |- plugins
//   |    |   |    |-   |- filemanager
//   |    |   |    |-   |-      |- thumbs <- thumbnail folder [must have write permission (755)]



$domain_dir = preg_replace('#/(admin|modules|ajax|capture|system|js).*$#i', '', str_replace(strrchr($_SERVER['PHP_SELF'], '/'), '', $_SERVER['PHP_SELF']));
$DOMAIN = $_SERVER['HTTP_HOST'] . $domain_dir;
$WEBPATH = 'http://' . $DOMAIN . '/';

//if(!isset($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST'] = 'localhost';

//$domain_dir = preg_replace('#/(admin|modules|ajax|capture).*$#i', '', str_replace(strrchr($_SERVER['PHP_SELF'], '/'), '', $_SERVER['PHP_SELF']));
//$DOMAIN = $_SERVER['HTTP_HOST'];
//$WEBPATH = 'http://' . $DOMAIN . '';

$base_url = $WEBPATH;  // base url of site. If you prefer relative urls leave empty
$upload_dir = 'pics/upload/'; // path from base_url to base of upload folder
$current_path = '../../../../pics/upload/'; // relative path from filemanager folder to upload folder

$config['mk_dir'] = $root . '/pics/upload/' . date('Y') . '/' . date('m') . '/' . date('d') . '/'; // Relative to domain name
if(!is_dir($config['mk_dir'])) mkdir( $config['mk_dir'], 0777, true );



//------------------------------------------------------------------------------
// YOU CAN COPY AND CHANGE THESE VARIABLES IN .config FILES
//------------------------------------------------------------------------------

$MaxSizeUpload=100; //Mb

$show_folder_size=true;
$show_sorting_bar=true;

//**********************
//Image config
//**********************

//parameter passed on editor
$image_dimension_passing=1; // if 1 filemanager passes the the pixel dimension to TinyMCE, else 0

// set maximum pixel width and/or maximum pixel height for all images
// If you set a maximum width or height, oversized images are converted to those limits. Images smaller than the limit(s) are unaffected
// if you don't need a limit set both to 0
$image_max_width=0;
$image_max_height=0;

//Automatic resizing //
// If you set $image_resizing to true the script converts all uploaded images to image_width x image_height 
// If you set width or height to 0 the script automatically calculates the other dimension
$image_resizing=false;
$image_width=600;
$image_height=0;

//******************
//Default layout setting
//
// 0 => boxes
// 1 => list (1 column)
// 2 => columns list (multiple columns depending on the width of the page)
//
// YOU CAN ALSO PASS THIS PARAMETERS USING SESSION VAR => $_SESSION["VIEW"]=
//
//******************
$default_view=0;

//******************
//Permissions config
//******************
$delete_files=true;
$create_folders=true;
$delete_folders=true;
$upload_files=true;
$rename_files=true;
$rename_folders=true;

//**********************
//Allowed extensions
//**********************
$ext_img = array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'svg', 'svgz'); //Images
$ext_file = array('doc', 'docx', 'pdf', 'xls', 'xlsx', 'txt', 'csv','html','psd','sql','log','fla','xml','ade','adp','ppt','pptx'); //Files
$ext_video = array('mov', 'mpeg', 'mp4', 'avi', 'mpg','wma'); //Videos
$ext_music = array('mp3', 'm4a', 'ac3', 'aiff', 'mid'); //Music
$ext_misc = array('zip', 'rar','gzip'); //Archives
$ext=array_merge($ext_img, $ext_file, $ext_misc, $ext_video,$ext_music); //allowed extensions

//**********************
// Hidden files and folders
//**********************
// set the names of any folders you want hidden (eg "hidden_folder1", "hidden_folder2" ) Remember all folders with these names will be hidden (you can set any exceptions in .config file)
$hidden_folders = array();
// set the names of any files you want hidden. Remember these names will be hidden in all folders (eg "this_document.pdf", "that_image.jpg" )
$hidden_files = array();

/*******************
 * JAVA upload 
 *******************/
$java_upload=true;
$MaxJAVASizeUpload=200; //Gb
?>
