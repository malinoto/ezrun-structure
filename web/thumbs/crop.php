<?php
require_once(dirname(__FILE__) . '/../../config/definitions.php');

set_time_limit(0);

if ( !isset( $_GET["p"] ) ) die();

$str = explode( "-", $_GET["p"] );

$size_w = (int) $str[0];
$size_h = (int) $str[1];
$pic_info = $str[2];

$folder_array = explode('/', $pic_info);
$pic = $folder_array[3];
$folder = $folder_array[0] . '/' . $folder_array[1] . '/' . $folder_array[2] . '/';

$ext_arr = explode('.', $pic);
$ext = $ext_arr[(count($ext_arr) - 1)];
$fixedExt = mb_strtoupper(preg_replace('/jpg/iu', 'jpeg', $ext), 'utf-8');

//$pic_new = str_replace('.' . $ext, '.jpg', $pic);
$pic_new = $pic;

$type = 'cut/';

if ( $pic != "" && $size_w > 0 && $size_h > 0 )
{
	$ourl = UPLOAD_PICS_PATH . $pic_info;
	$turl = THUMBS_PATH . $type . $folder . $size_w . "x" . $size_h . "/" . $pic_new;
}
else die();

if(is_file( $turl ))
{
	//Header("Location: " . THUMBS_WEBPATH . $type . $folder . $size_w . "x" . $size_h . '/' . $pic_new);
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
if ( !is_dir( THUMBS_PATH . $type . $folder . $size_w . "x" . $size_h ) ) mkdir( THUMBS_PATH . $type . $folder . $size_w . "x" . $size_h, 0777 );

if ( $size_w > 0 && $size_h > 0 && !is_file( $turl ) )
{
	$getimagesize = getimagesize($ourl);
	if($getimagesize['mime'] == 'image/jpeg') $src_img = ImageCreateFromJpeg( $ourl );
	else if($getimagesize['mime'] == 'image/gif') $src_img = ImageCreateFromGIF( $ourl );
	else if($getimagesize['mime'] == 'image/png') $src_img = ImageCreateFromPNG( $ourl );
	
	$dst_img = ImageCreateTrueColor( $size_w, $size_h );
	
	$ImageSX = ImageSX( $src_img );
	$ImageSY = ImageSY( $src_img );
	
	if($ImageSX >= $ImageSY)
	{
		$coef = $ImageSX / $ImageSY;
		
		//smaller
		if($ImageSX > $size_w)
		{
			$nw = $size_w;
			$nh = $size_w / $coef;
			
			if($nh < $size_h)
			{
				$nh = $size_h;
				$nw = $nh * $coef;
				
				if($nw < $size_w)
				{
					$nh = $size_h + ($size_h - ($size_w / $coef));
					$nw = $nh * $coef;
				}
			}
		}
		else if($ImageSY > $size_h)
		{
			$nh = $size_h;
			$nw = $size_h * $coef;
			
			if($nw < $size_w)
			{
				$nw = $size_w;
				$nh = $nw / $coef;
			}
		}
		//no resize
		else
		{
			$nw = $size_w;
			$nh = $size_w / $coef;
			
			if($nh < $size_h)
			{
				$nh = $size_h;
				$nw = $nh * $coef;
			}
		}
	}
	else
	{
		$coef = $ImageSY / $ImageSX;
		
		//smaller
		if($ImageSX > $size_w)
		{
			$nw = $size_w;
			$nh = $size_w * $coef;
			
			if($nh < $size_h)
			{
				$nh = $size_h;
				$nw = $size_h / $coef;
			}
		}
		else if($ImageSY > $size_h)
		{
			$nh = $size_h;
			$nw = $size_h / $coef;
			
			if($nw < $size_w)
			{
				$nw = $size_w;
				$nh = $size_w * $coef;
			}
		}
		//no resize
		else
		{
			$nw = $size_w;
			$nh = $nw * $coef;
			
			if($nh < $size_h)
			{
				$nh = $size_h;
				$nw = $size_h / $coef;
			}
		}
	}

	//transperancy
	imagealphablending($dst_img, false);
  imagesavealpha($dst_img, true);
  $transparent = imagecolorallocatealpha($dst_img, 255, 255, 255, 127);
  imagefilledrectangle($dst_img, 0, 0, $size_w, $size_h, $transparent);
  
  //image
  ImageCopyResampled( $dst_img, $src_img, 0, 0, round(($nw - $size_w) / 2), round(($nh - $size_h) / 2), round($nw), round($nh), ImageSX( $src_img ), ImageSY( $src_img ));
	
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