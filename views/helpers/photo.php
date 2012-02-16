<?php
class PhotoHelper extends AppHelper {
	
	var $helpers = array('Html', 'Javascript');
	
	function display($param1 = array(), $param2 = array(), $param3 = array(), $param4 = array()) {
		if(is_array($param1)) {
			$path = $param1['path'];
			$filename = $param1['filename'];
			$options =& $param2;
			$htmlAttributes = $param3;
		}
		else {
			$path = $param1;
			$filename = $param2;
			$options = $param3;
			$htmlAttributes = $param4;
		}
	
		$html = null;
		if(!array_key_exists('alt', $htmlAttributes)) {
			$htmlAttributes['alt'] = '';
		}
		foreach($htmlAttributes as $k => $h) {
			$html = $html . ' '.$k.'="'.$h.'"';
		}
		return '<img src="'.$this->path($path, $filename, $options).'"'.$html.' />';
	}
	
	function getPath($path, $filename, $dims, $sizingMethod, $time) {
		return $this->path($path, $filename, array('size' => $dims, 'method' => $sizingMethod, 'time' => $time));
	}
	
	function path($param1 = array(), $param2 = array(), $param3 = array()) {
		if(is_array($param1)) {
			$path = $param1['path'];
			$filename = $param1['filename'];
			$options =& $param2;
		}
		else {
			$path = $param1;
			$filename = $param2;
			$options = $param3;
		}
	
		if($path == '' || $filename == '') return false;
		
		// Set default values
		if(!isset($options['full'])) $options['full'] = false;
		if(!isset($options['dims'])) $options['dims'] = 'original';
		if(isset($options['size'])) $options['dims'] = $options['size'];
		if($options['dims'] != 'original' && !isset($options['method'])) $options['method'] = 'crop';
		if(!isset($options['saveas'])) $options['saveas'] = null;
		if(!isset($options['time'])) $options['time'] = null;
		if(!isset($options['watermark'])) $options['watermark'] = null;
		
		// Path must not begin with slash but must end with slash
		if($path[0] == '/') $path = substr($path, 1);
		if(substr($path, -1) != '/') $path .= '/';
		
		// Absolute path to directory
		$absolutePath = WWW_ROOT . $path;
		// Absolute path to file
		$absoluteFilePath = $absolutePath . $filename;
		
		if(file_exists($absoluteFilePath)) {
			$fileSize = filesize($absoluteFilePath);
			$fileExt = strtolower(strrchr($filename, '.'));
			if($fileExt == '.jpeg') {
				$fileExt = '.jpg';
			}
			
			if(!in_array($fileExt, array('.jpg','.jpeg','.png','.gif','.f4v','.flv'))) {
				return false;
			}
			
			$fileNameNoExt = substr($filename, 0, -strlen($fileExt));
			
			if($options['saveas'] == null) {
				if($fileExt == '.flv' || $fileExt == '.f4v') {
					$options['saveas'] = '.jpg';
				}
				$options['saveas'] = $fileExt;
			}
			else {
				$options['saveas'] = strtolower($options['saveas']);
				if($options['saveas'] == '.jpeg') {
					$options['saveas'] == '.jpg';
				}
				if($options['saveas'][0] != '.') {
					$options['saveas'] = '.'.$options['saveas'];
				}
			}

			if($options['dims'] == 'original' && $options['saveas'] == $fileExt && $fileExt != '.flv' && $fileExt != '.f4v' && $options['watermark'] == null) {
				// original file returned
				return $this->url('/'.$path.$filename, $options['full']);
			}
			else {
				$cachedFile = $this->_cachePath($path, $fileNameNoExt, $fileExt, $fileSize, $options);
				
				if($cachedFile['exists']) {
					return $this->url($cachedFile['path'].$cachedFile['filename'], $options['full']);
				}
				else {
					// create file
					if($fileExt == '.flv' || $fileExt == '.f4v') {
						// if video, check if a screenshot already exists
						$optionsOriginal = $options;
						$optionsOriginal['dims'] = 'original';
						$optionsOriginal['watermark'] = null;
						$videoScreenshot = $this->_cachePath($path, $fileNameNoExt, $fileExt, $fileSize, $optionsOriginal);
						
						if($videoScreenshot['exists']) {
							// Screenshot already exists, use it
							$img = $this->_loadImage($videoScreenshot['path'], $videoScreenshot['filename']);
						}
						else {
							// No screenshot available, take one
							if(class_exists('ffmpeg_movie')) {
								$ffmpegInstance = new ffmpeg_movie($absoluteFilePath);
								
								if($options['time'] != null) {
									// take screenshot at specified time
									$framerate = $ffmpegInstance->getFrameRate();
									$framecount = $ffmpegInstance->getFrameCount();
									$frame_number = $framerate * $options['time'];
									if($frame_number > $framecount - 1) {
										// invalid time
										$frame = $ffmpegInstance->getFrame((int)(($ffmpegInstance->getFrameCount())/2));
									}
									else {
										$frame = $ffmpegInstance->getFrame($frame_number);
									}
								}
								else {
									// take screenshot at middle of video
									$frame = $ffmpegInstance->getFrame((int)(($ffmpegInstance->getFrameCount())/2));
								}
								
								$img = $frame->toGDImage();
								
								// Save screenshot for future use
								$this->_saveImage($img, $videoScreenshot['path'], $videoScreenshot['filename'], $options['saveas']);
							}
							else {
								return false;
							}
						}
					}
					else {
						// this is not a video
						// load image
						$img = $this->_loadImage($path, $filename, $fileExt);
					}
						
					// Add watermark if needed
					if($options['watermark'] != null) {
						if($options['watermark'][0] == '/') {
							$options['watermark'] = substr($options['watermark'], 1);
						}
						$absoluteWatermarkFile = WWW_ROOT . $options['watermark'];
						
						if(file_exists($absoluteWatermarkFile)) {
							$watermarkFileName = basename($options['watermark']);
							$watermark = $this->_loadImage(dirname($options['watermark']).'/', $watermarkFileName, strtolower(strrchr($watermarkFileName, '.')));
							//var_dump($watermark);
							//die();
							$watermark_width = imagesx($watermark);  
							$watermark_height = imagesy($watermark);
							$img_height = imagesy($img);
							$img_width = imagesx($img);
							
							for($i = 0; $i < $img_width; $i = $i+(1.5*$watermark_width)) {
								for($j = 0; $j < $img_height; $j=$j+(1.5*$watermark_height)) {
									imagecopy($img, $watermark,  $i, $j, 0, 0, $watermark_width, $watermark_height);
								}
							}
						}
					}
					
					// crop/resize if needed
					if($options['dims'] != 'original') {
						if($options['method'] == 'crop') {
							$img = $this->_crop($img, $options['dims'], ($fileExt == '.png' && $options['saveas'] == '.png'));
						}
						elseif($options['method'] == 'resize') {
							$img = $this->_resize($img, $options['dims'], ($fileExt == '.png' && $options['saveas'] == '.png'));
						}
					}
					
					// Save image
					if($this->_saveImage($img, $cachedFile['path'], $cachedFile['filename'], $options['saveas'])) {
						imagedestroy($img);
						return $this->url($cachedFile['path'].$cachedFile['filename'], $options['full']);
					}
					return false;

				}
			}
		}
		return false;
	}
	
	function _loadImage($path = '', $fileName = '', $fileExt = '') {
		if($path[0] == '/') {
			$path = substr($path, 1);
		}
		$absolutePath = WWW_ROOT . $path . $fileName;
	
		if(strtolower($fileExt) == '.jpg') {
			$image = imagecreatefromjpeg($absolutePath);
		}
		elseif(strtolower($fileExt) == '.gif') {
			$image = imagecreatefromgif($absolutePath);
		}
		elseif(strtolower($fileExt) == '.png') {
			$image = imagecreatefrompng($absolutePath);
			imagealphablending($image, false); // setting alpha blending on
			imagesavealpha($image, true); // save alphablending setting (important)
		}
		
		if($image) {
			return $image;
		}
		return false;
	}
	
	function _saveImage($img = null, $path = '', $filename = '', $saveas = '') {
		if(substr($path, 0, 6) == 'files/') {
			$path = substr($path, 5);
		}
		if($path[0] == '/') {
			$path = substr($path, 1);
		}
		$destinationPath = WWW_ROOT . $path;
	
		if(!is_dir($destinationPath)) {
			mkdir($destinationPath, 0777, true);
		}
		
		if(strtolower($saveas) == '.jpg') {
			return imagejpeg($img,$destinationPath . $filename,100);
		}
		elseif(strtolower($saveas) == '.gif') {
			return imagegif($img,$destinationPath . $filename);
		}
		elseif(strtolower($saveas) == '.png') {
			//imagealphablending($tmpPic, false); // setting alpha blending on
			//imagesavealpha($tmpPic, true); // save alphablending setting (important)
			return imagepng($img,$destinationPath . $filename,0);
		}
	}
	
	function _cachePath($path = '', $fileNameNoExt = '', $fileExt = '', $fileSize = 0, $options = array()) {
		if(substr($path, 0, 6) == 'files/') {
			$path = substr($path, 5);
		}
		
		$timePath = null;
		$watermarkPath = null;
		if(($fileExt == '.flv' || $fileExt == '.f4v') && $options['time'] != null) {
			$timePath = $options['time'].'/';
		}
		if($options['watermark'] != null) {
			$watermarkPath = basename($options['watermark']).'/';
		}
		
		if($options['dims'] == 'original') {
			$cachePath = 'cache/original/'.$path.$watermarkPath.$timePath;
			$cacheFilename = $fileSize.'_'.$fileNameNoExt.$options['saveas'];
		}
		else {
			$cachePath = 'cache/'.$options['method'].'/'.$options['dims'].'/'.$path.$watermarkPath.$timePath;
			$cacheFilename = $fileSize.'_'.$fileNameNoExt.$options['saveas'];
		}
		
		return array('exists' => file_exists(WWW_ROOT . 'files/' . $cachePath.$cacheFilename), 'path' => '/files/'.$cachePath, 'filename' => $cacheFilename);
	}
	
	function _resize(&$img, $dims, $png = false) {
		list($max_width, $max_height) = explode('x', $dims);
		
		$width = imagesx($img);
		$height = imagesy($img);
		
		$x_ratio = $max_width / $width;
		$y_ratio = $max_height / $height;

		if(($width <= $max_width) && ($height <= $max_height)){
			$tn_width = $width;
			$tn_height = $height;
		}
		elseif (($x_ratio * $height) < $max_height) {
			$tn_height = ceil($x_ratio * $height);
			$tn_width = $max_width;
		}
		else {
			$tn_width = ceil($y_ratio * $width);
			$tn_height = $max_height;
		}
		
		$tmpPic = imagecreatetruecolor($tn_width,$tn_height);
		if($png) {
			imagealphablending($tmpPic, false); // setting alpha blending on
			imagesavealpha($tmpPic, true); // save alphablending setting (important)
		}
		imagecopyresampled($tmpPic,$img,0,0,0,0,$tn_width, $tn_height,$width,$height);

		imagedestroy($img);
		
		return $tmpPic;
	}
	
	function _crop(&$img, $dims, $png = false) {
	
		list($nw, $nh) = explode('x', $dims);
		
		$w = imagesx($img);
		$h = imagesy($img);
		
		$dimg = imagecreatetruecolor($nw, $nh);
		if($png) {
			imagealphablending($dimg, false); // setting alpha blending on
			imagesavealpha($dimg, true); // save alphablending setting (important)
		}
		
		$wm = $w/$nw;
		$hm = $h/$nh;
		
		$h_height = $nh/2;
		$w_height = $nw/2;
		
		if($wm > $hm) {
			$adjusted_width = $w / $hm;
			$half_width = $adjusted_width / 2;
			$int_width = $half_width - $w_height;
			imagecopyresampled($dimg,$img,-$int_width,0,0,0,$adjusted_width,$nh,$w,$h);
		} elseif(($wm < $hm) || ($wm == $hm)) {
			$adjusted_height = $h / $wm;
			$half_height = $adjusted_height / 2;
			$int_height = $half_height - $h_height;
			imagecopyresampled($dimg,$img,0,-$int_height,0,0,$nw,$adjusted_height,$w,$h);
		} else {
			imagecopyresampled($dimg,$img,0,0,0,0,$nw,$nh,$w,$h);
		}
		
		imagedestroy($img);
		
		return $dimg;
	}
}
?>