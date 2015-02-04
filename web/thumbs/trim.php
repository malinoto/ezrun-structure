<?php
require_once(dirname(__FILE__) . '/../../config/definitions.php');

set_time_limit(0);

if ( !isset( $_GET["p"] ) ) die();

$coef = 1.3333333333333333333333333333333;

$str = explode( "-", $_GET["p"] );

$size = (int) $str[0];
$pic_info = $str[1];
$stat = ( isset( $str[2] ) ? (int) $str[2] : 0 );
$create = ( isset( $str[3] ) ? (int) $str[3] : 0 );

$folder_array = explode('/', $pic_info);
$pic = $folder_array[3];
$folder = $folder_array[0] . '/' . $folder_array[1] . '/' . $folder_array[2] . '/';

$ext_arr = explode('.', $pic);
$ext = $ext_arr[(count($ext_arr) - 1)];
$fixedExt = mb_strtoupper(preg_replace('/jpg/iu', 'jpeg', $ext), 'utf-8');

//$pic_new = str_replace('.' . $ext, '.jpg', $pic);
$pic_new = $pic;

$type = 'common/';

if ( !is_dir( THUMBS_PATH . $type ) ) mkdir( THUMBS_PATH . $type );
if ( !is_dir( THUMBS_PATH . $type . $folder_array[0] ) ) mkdir( THUMBS_PATH . $type . $folder_array[0] );
if ( !is_dir( THUMBS_PATH . $type . $folder_array[0] . "/" . $folder_array[1] ) ) mkdir( THUMBS_PATH . $type . $folder_array[0] . "/" . $folder_array[1] );
if ( !is_dir( THUMBS_PATH . $type . $folder ) ) mkdir( THUMBS_PATH . $type . $folder );
if ( !is_dir( THUMBS_PATH . $type . $folder . $size ) ) mkdir( THUMBS_PATH . $type . $folder . $size, 0777 );
if ( !is_dir( THUMBS_PATH . $type . $folder . $size . "/" . $stat ) ) mkdir( THUMBS_PATH . $type . $folder . $size . "/" . $stat, 0777 );

if ( $pic != "" && $size > 0 )
{
	if($create != 0) $ourl = rtrim(UPLOAD_PICS_PATH, 'pics/original') . '/' . $pic_info;
	else $ourl = UPLOAD_PICS_PATH . $pic_info;
	
	$turl = THUMBS_PATH . $type . $folder . $size . "/" . $stat . "/" . $pic_new;
}
else die();

if(is_file( $turl ))
{
	//Header("Location:" . THUMBS_WEBPATH . $type . $folder . $size . "/" . $stat . "/" . $pic_new;);
	$fp = fopen($turl, "rb");
	if($fp)
	{
		header("Content-Type: image/" . mb_strtolower($fixedExt, 'utf-8'));
		fpassthru($fp);
		exit;
	}
	die();
}

if ( $size > 0 && !is_file( $turl ) )
{
	$getimagesize = getimagesize($ourl);
	if($getimagesize['mime'] == 'image/jpeg') $src_img = ImageCreateFromJpeg( $ourl );
	else if($getimagesize['mime'] == 'image/gif') $src_img = ImageCreateFromGIF( $ourl );
	else if($getimagesize['mime'] == 'image/png') $src_img = ImageCreateFromPNG( $ourl );

	if ( $stat == 1 )
	{
		$new_w = $size * ImageSX( $src_img ) / ImageSY( $src_img );
		$new_h = $size;
	}
	else
	{
		if ( ImageSX( $src_img ) != $size && (ImageSX( $src_img ) > $size || ImageSX( $src_img ) < 155) )
		{
			if( ImageSX( $src_img ) < 155 && $size > 155 )
			{
				$size = 155;
				$new_w = 155;
			}
			else $new_w = $size;
			$new_h = $size * ImageSY( $src_img ) / ImageSX( $src_img );
		}
		else
		{
			$new_w = ImageSX( $src_img );
			$new_h = ImageSY( $src_img );
		}
	}
	
	if(ImageSX( $src_img ) / ImageSY( $src_img ) == $coef || ImageSY( $src_img ) / ImageSX( $src_img ) == $coef)
	{		
		$dst_img = ImageCreateTrueColor( ($stat == 1 ? ($new_h * $coef) : $new_w), $new_h );
		
		ImageCopyResampled( $dst_img, $src_img, (($stat == 1 ? ($new_h * $coef) : $new_w)-$new_w)/2, 0, 0, 0, $new_w, $new_h, ImageSX( $src_img ), ImageSY( $src_img ) );
	}
	else if(ImageSX( $src_img ) / ImageSY( $src_img ) != $coef && ImageSY( $src_img ) / ImageSX( $src_img ) != $coef)
	{
		if($stat == 1)
		{
			$nw = $new_h * $coef;
			$nh = ( ImageSX( $src_img ) > ImageSY( $src_img ) ) ? ($nw / ($nw / $new_h)) : $new_h;
			$dst_img = ImageCreateTrueColor( $nw, $new_h );
			
			if($new_w > $nw)
			{
				$new_h = ( ImageSX( $src_img ) > ImageSY( $src_img ) ) ? $nw / ($new_w / $new_h) : $nw * ($new_w / $new_h);
				$new_w = $nw;
			}
			
			ImageCopyResampled( $dst_img, $src_img, abs(($nw - $new_w)/2), abs(($nh - $new_h)/2), 0, 0, $new_w, $new_h, ImageSX( $src_img ), ImageSY( $src_img ) );
		}
		else
		{
			$nh = $new_w / $coef;
			$nw = ( ImageSX( $src_img ) > ImageSY( $src_img ) ) ? $new_w : $nh / $coef;
			$dst_img = ImageCreateTrueColor( $new_w, $nh );
			
			if($new_h > $nh)
			{
				$new_w = ( ImageSX( $src_img ) > ImageSY( $src_img ) ) ? $nh * ($new_w / $new_h) : $nh / ($new_w / $new_h);
				$new_h = $nh;
			}
			
			ImageCopyResampled( $dst_img, $src_img, abs(($nw - $new_w)/2), abs(($nh - $new_h)/2), 0, 0, $nw, $new_h, ImageSX( $src_img ), ImageSY( $src_img ) );
		}
	}
	
	eval("Image{$fixedExt}( \$dst_img, null );");
	
	if($getimagesize['mime'] == 'image/jpeg') ImageJPEG( $dst_img, $turl, 100 );
	else if($getimagesize['mime'] == 'image/gif') $src_img = ImageGIF( $dst_img, $turl );
	else if($getimagesize['mime'] == 'image/png') ImagePNG( $dst_img, $turl, 0 );
	
	imagedestroy( $src_img );
	imagedestroy( $dst_img );
	
	header("Content-Type: image/" . mb_strtolower($fixedExt, 'utf-8'));
	header("Content-Length: " . filesize($turl));
	header("Cache-Control: maxage=186400 , must-revalidate"); //In seconds
	header("Pragma: public");
}
die();
?>