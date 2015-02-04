<?php
require_once(dirname(__FILE__) . '/../../config/definitions.php');

set_time_limit(0);

if ( !isset( $_GET["p"] ) ) die();

$str = explode( "-", $_GET["p"] );

$size_h = (int) $str[0];
$pic_info = $str[1];
$stat = ( isset( $str[2] ) ? (int) $str[2] : 0 );
$wm = ( isset( $str[3] ) ? (int) $str[3] : 0 );

if(preg_match('/^upload\/(.*)/iu', $pic_info)) $pic_info = '../' . $pic_info;
$folder_array = explode('/', $pic_info);
$pic = $folder_array[3];
$folder = $folder_array[0] . '/' . $folder_array[1] . '/' . $folder_array[2] . '/';

$ext_arr = explode('.', $pic);
$ext = $ext_arr[(count($ext_arr) - 1)];
$fixedExt = mb_strtoupper(preg_replace('/jpg/iu', 'jpeg', $ext), 'utf-8');

//$pic_new = str_replace('.' . $ext, '.jpg', $pic);
$pic_new = $pic;

$type = 'asp/';

if ($pic != "" && $size_h)
{
	$ourl = UPLOAD_PICS_PATH . $pic_info;
	$turl = THUMBS_PATH . $type . $folder . $size_h . "/" . $stat . "/" . ($wm == 1 ? "/wm/" : "") . $pic_new;
}
else die();

if(is_file( $turl ))
{
	//Header("Location: " . THUMBS_WEBPATH . $type . $folder . $size_h . '/' . $stat . '/' . ($wm == 1 ? "/wm/" : "") . $pic_new);
	$fp = fopen($turl, "rb");
	if($fp)
	{
		header("Content-type: image/" . mb_strtolower($fixedExt, 'utf-8'));
		fpassthru($fp);
		exit;
	}
	die();
}

if ( !is_dir( THUMBS_PATH . $type ) ) mkdir( THUMBS_PATH . $type );
if ( !is_dir( THUMBS_PATH . $type . $folder_array[0] ) ) mkdir( THUMBS_PATH . $type . $folder_array[0] );
if ( !is_dir( THUMBS_PATH . $type . $folder_array[0] . "/" . $folder_array[1] ) ) mkdir( THUMBS_PATH . $type . $folder_array[0] . "/" . $folder_array[1] );
if ( !is_dir( THUMBS_PATH . $type . $folder ) ) mkdir( THUMBS_PATH . $type . $folder );
if ( !is_dir( THUMBS_PATH . $type . $folder . $size_h ) ) mkdir( THUMBS_PATH . $type . $folder . $size_h, 0777 );
if ( !is_dir( THUMBS_PATH . $type . $folder . "/" . $size_h . "/" . $stat ) ) mkdir( THUMBS_PATH . $type . $folder . "/" . $size_h . "/" . $stat, 0777 );
if ( !is_dir( THUMBS_PATH . $type . $folder . "/" . $size_h . "/" . $stat . "/wm" ) && $wm == 1 ) mkdir( THUMBS_PATH . $type . $folder . "/" . $size_h . "/" . $stat . "/wm", 0777 );

if ( $size_h > 0 && !is_file( $turl ) )
{
	$getimagesize = getimagesize($ourl);
	if($getimagesize['mime'] == 'image/jpeg') $src_img = ImageCreateFromJpeg( $ourl );
	else if($getimagesize['mime'] == 'image/gif') $src_img = ImageCreateFromGIF( $ourl );
	else if($getimagesize['mime'] == 'image/png') $src_img = ImageCreateFromPNG( $ourl );
	
	$ImageSX = ImageSX( $src_img );
	$ImageSY = ImageSY( $src_img );
	
	if($ImageSX > $size_h) {
		if($stat == 0)
		{
			if($ImageSX >= $ImageSY)
			{
				$coef = $ImageSX / $ImageSY;
				$size_w = $size_h * $coef;
			}
			else
			{
				$coef = $ImageSY / $ImageSX;
				$size_w = $size_h / $coef;
			}
		}
		else
		{
			if($ImageSX >= $ImageSY)
			{
				$coef = $ImageSX / $ImageSY;
				$size_w = $size_h;
				$size_h = $size_w / $coef;
			}
			else
			{
				$coef = $ImageSY / $ImageSX;
				$size_w = $size_h;
				$size_h = $size_w * $coef;
			}
		}
	}
	else
	{
		$size_w = $ImageSX;
		$size_h = $ImageSY;
	}
	
	$dst_img = ImageCreateTrueColor( $size_w, $size_h );
	ImageCopyResampled( $dst_img, $src_img, 0, 0, 0, 0, $size_w, $size_h, $ImageSX, $ImageSY );
	
	//put wattermark
	if($wm == 1)
	{
		$wmpic = ImageCreateFromPNG(IMGPATHREL . "sf_watermark.png");
		
		$wmpic_w = round(($size_w * 30) / 100);
		$wmpic_h = round($wmpic_w / (ImageSX($wmpic) / ImageSY($wmpic)));
		
		$wmn = ImageCreateTrueColor( $wmpic_w, $wmpic_h );
		ImageAlphaBlending($wmn, false); // turn off the alpha blending to keep the alpha channel
  	ImageSaveAlpha($wmn, true);
		ImageCopyResampled( $wmn, $wmpic, 0, 0, 0, 0, $wmpic_w, $wmpic_h, ImageSX($wmpic), ImageSY($wmpic) );
		
		ImageCopy($dst_img, $wmn, ImageSX( $dst_img ) - ($wmpic_w + 7), ImageSY( $dst_img ) - ($wmpic_h + 7), 0, 0, ImageSX($wmn), ImageSY($wmn) );
	}
	
	eval("Image{$fixedExt}( \$dst_img, null );");
	
	if($getimagesize['mime'] == 'image/jpeg') ImageJPEG( $dst_img, $turl, 100 );
	else if($getimagesize['mime'] == 'image/gif') $src_img = ImageGIF( $dst_img, $turl );
	else if($getimagesize['mime'] == 'image/png') ImagePNG( $dst_img, $turl, 0 );
	
	imagedestroy( $src_img );
	imagedestroy( $dst_img );
	
	header("Content-Type: image/" . mb_strtolower($fixedExt, 'utf-8'));
}
die();
?>