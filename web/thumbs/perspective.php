<?php
require_once(dirname(__FILE__) . '/../../config/definitions.php');

set_time_limit(0);

if ( !isset( $_GET["p"] ) ) die();

$str = explode( "-", $_GET["p"] );

$size_w = (int) $str[0];
$size_h = (int) $str[1];
$pic_info = preg_replace('/\//iu', DS, $str[2]);
$perspective = ( isset($str[3]) ? $str[3] : '' );
$crop_image = ( isset($str[4]) ? $str[4] : 0 ); // To crop image from center
$glow = ( isset($str[5]) ? $str[5] : '' ); // To crop image from center
$glow_width =  (isset($str[6]) ? $str[6] : '' );
$border =  (isset($str[7]) ? $str[7] : '' );
$hover = ( isset($str[8]) ? $str[8] : '' ); // Hover - gallery
$text = ( isset($str[9]) ? $str[9] : '');
$text_angle = ( isset($str[10]) ? $str[10] : '');

$perspective_data = explode(";", $perspective);

$t_left  = isset($perspective_data[0]) ? (int) $perspective_data[0] : 0; //Perspective top left Pixels ('5' cuts 5 pixels from top left)
$t_right = isset($perspective_data[1]) ? (int) $perspective_data[1] : 0; //Perspective top right Pixels ('5' cuts 5 pixels from top right)
$b_left  = isset($perspective_data[2]) ? (int) $perspective_data[2] : 0; //Perspective bottom left Pixels ('5' cuts 5 pixels from bottom left)
$b_right = isset($perspective_data[3]) ? (int) $perspective_data[3] : 0; //Perspective bottom right Pixels ('5' cuts 5 pixels from bottom right)

if( empty($size_w) || empty($size_h) || empty($pic_info) || empty($perspective)) die();

if(preg_match('/^upload\/(.*)/iu', $pic_info)) $pic_info = '../' . $pic_info;
$folder_array = explode(DS, $pic_info);
$pic = array_pop($folder_array);
$folder = implode(DS, $folder_array);

$ext_arr = explode('.', $pic);
$ext = array_pop($ext_arr);
$fixedExt = mb_strtoupper(preg_replace('/jpg/iu', 'jpeg', $ext), 'utf-8');

//$pic_new = str_replace('.' . $ext, '.jpg', $pic);
$pic_new = preg_replace("/" . $ext . "/", "png", $pic);

$type = 'persp';

$upload_dir = THUMBS_PATH . $type. DS . $folder . DS . $size_w . 'x' . $size_h;

if ( $pic != "" && $size_w > 0 )
{
	$ourl = UPLOAD_PICS_PATH . $pic_info;
	$turl = $upload_dir . DS . $pic_new;
}
else die();

if(is_file( $turl ))
{
	$fp = fopen($turl, "rb");
	if($fp)
	{
		header("Content-Type: image/" . mb_strtolower($fixedExt, 'utf-8'));
		header("Content-Length: " . filesize($turl));
		header("Cache-Control: private, max-age=6000, pre-check=6000");
		header("Pragma: public");
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + 60 * 60 * 24 * 3). " GMT");
		fpassthru($fp);
		exit;
	}
	die();
}

//create dir
if(!is_dir($upload_dir)) mkdir( $upload_dir, 0777, true );

if ( $size_w > 0 && !is_file( $turl ) )
{
	/* Create new object */
	$im = new Imagick( $ourl );
	$im->setImageBackgroundColor( new ImagickPixel( '#121050' ) );
	if($crop_image == 1) $im->cropThumbnailImage($size_w, $size_h); 
	else $im->resizeImage($size_w, $size_h, imagick::FILTER_LANCZOS, 0.9, false);

	/* Create new checkerboard pattern */
	//$im->newPseudoImage($size_w_w, $size_w, "pattern:checkerboard");
	
	/* Set the image format to png */
	$im->setImageFormat('png');
	
	/* Fill new visible areas with transparent */
	$im->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);

	/* Activate matte */
	$im->setImageMatte(true);
	
	//border
	if(!empty($border))
		$im->borderImage( new ImagickPixel( '#' . $border ), 1, 1 );
	
	//glow
	if(!empty($glow) && empty($hover)) {
		
		$nwidth = $size_w + ($glow_width * 2) + (!empty($border) ? 2 : 0);
		$nheight = $size_h + ($glow_width * 2) + (!empty($border) ? 2 : 0);
		
		$glow_img = new Imagick();
		$glow_img->newImage($nwidth, $nheight, 'none');
		$glow_img->setImageFormat('png');
		
		$draw = new ImagickDraw();    //Create a new drawing class (?)
		$draw->setFillColor(new ImagickPixel('#' . $glow));    // Set up some colors to use for fill and outline
		$draw->rectangle( $glow_width, $glow_width, $nwidth - $glow_width, $nheight - $glow_width );
		
		$glow_img->drawImage( $draw );
		$glow_img->blurImage($glow_width - 4, $glow_width);
		
		$glow_img->resizeImage($nwidth, $nheight, imagick::FILTER_LANCZOS, 0.9, false);
		$glow_img->compositeImage( $im, Imagick::COMPOSITE_OVER, $glow_width, $glow_width, Imagick::CHANNEL_ALPHA);
		
		$im = $glow_img;
		
	}
	
	$controlPoints = array( 0, 0, 0, 0,
										$im->getImageWidth(), $t_left, // Top left
										$im->getImageWidth(), $t_right, // Top right
										$im->getImageWidth(), $im->getImageHeight() - $b_left, // Bottom left
										$im->getImageWidth(), $im->getImageHeight() - $b_right, // Bottom right
										//STATIC
										0, $im->getImageHeight(),
										0, $im->getImageHeight()
									);
	
	/* Perform the distortion */                       
	$im->distortImage(Imagick::DISTORTION_PERSPECTIVE, $controlPoints, true);
	
	//hover
	if(!empty($hover)) {
		
		$bgr_color = !empty($glow) ? $glow : '000000';
		
		$bgr = new Imagick();
		$bgr->newImage($size_w, $size_h, new ImagickPixel('#' . $bgr_color));
		
		if($crop_image == 1) $im->cropThumbnailImage($size_w, $size_h); 
		else $im->resizeImage($size_w, $size_h, imagick::FILTER_LANCZOS, 0.9, false);
		
		$bgr->setImageFormat('png');
		$bgr->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
		$bgr->setImageMatte(true);
		
		$controlPoints = array( 0, 0, 0, 0,
							$bgr->getImageWidth(), $t_left, // Top left
							$bgr->getImageWidth(), $t_right, // Top right
							$bgr->getImageWidth(), $bgr->getImageHeight() - $b_left, // Bottom left
							$bgr->getImageWidth(), $bgr->getImageHeight() - $b_right, // Bottom right
							//STATIC
							0, $bgr->getImageHeight(),
							0, $bgr->getImageHeight());


		$bgr->setImageOpacity(0.7);

		$bgr->distortImage(Imagick::DISTORTION_PERSPECTIVE, $controlPoints, true);
		$bgr_dir = UPLOAD_PICS_PATH . ".."	. DS ."thumb" . DS . $type . DS . 'hover'
									. DS . $hover . DS . $size_w . 'x' . $size_h . DS;
		
		$bgr->resizeImage($size_w, $size_h, imagick::FILTER_LANCZOS, 0.9, false);

		//create dir
		if(!is_dir($bgr_dir)) mkdir( $bgr_dir, 0777, true );

		$bgr->writeImage($bgr_dir . $t_left . "_" .  $t_right . "_" .  $b_left . "_" .  $b_right . ".png");
	}
	
	//text
	if(!empty($text)) {
		
		$draw = new ImagickDraw();
		$draw->setFillColor('#ffffff');
		$draw->setFont(LIBRARY_PATH . 'fonts/futura/futuraltcnbtlight-webfont.ttf');
		$draw->setFontSize(22);
		$draw->setFontWeight(100);
		$draw->setTextUnderColor('none');
		
		$im->annotateImage($draw, 21, $im->getimageheight() - 21, -$text_angle, strtoupper($text));
	}
	
	$im->writeImage($turl);
	header("Content-Type: image/png");
	echo $im;
}
die();
?>