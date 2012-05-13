<?php

class CrempaImagick_Module extends Core_ModuleBase
{


	/**
	 * Creates the module information object
	 * @return Core_ModuleInfo
	 */
	protected function createModuleInfo()
	{
		return new Core_ModuleInfo(
			"Imagick extension support",
			"Adds support of Imagick PHP extension",
			"Crempa");
	}


	public function subscribeEvents()
	{
		Backend::$events->addEvent('core:onProcessImage', $this, 'process_image');
	}


	public function process_image($file_obj, $width, $height, $returnJpeg, $params)
	{

		// Generate the thumbnail file name and check whether it does not exist yet 
		$ext            = $returnJpeg ? 'jpg' : 'png';
		$thumbnail_path = '/uploaded/thumbnails/' . implode('.', array_slice(explode('.', $file_obj->name), 0, -1)) . '_' . $file_obj->id . '_' . $width . 'x' . $height . '.' . $ext;

		// Return the thumbnail path if it does exist
		if(file_exists($thumbnail_path)){
			return $thumbnail_path;
		}

		// Create new Imagick object
		$image = new Imagick(PATH_APP . $file_obj->getPath());

		//Sizes
		$width  = $width == 'auto' ? 0 : $width;
		$height = $height == 'auto' ? 0 : $height;

		//Sharpen
		$sharpen = isset($params['sharpen']) ? $params['sharpen'] : 1;

		//Resize
		$image->resizeImage($width, $height, Imagick::FILTER_LANCZOS, $sharpen);

		//Gamma reduction?
		if(isset($params['gamma'])){
			$image->gammaImage($params['gamma']);
		}

		//Jpeg?
		if($returnJpeg){
			// Set to use jpeg compression
			$image->setImageCompression(Imagick::COMPRESSION_JPEG);

			// Set compression level (1 lowest quality, 100 highest quality)
			$image->setImageCompressionQuality(Phpr::$config->get('IMAGE_JPEG_QUALITY', 94));
		}

		// Strip out unneeded meta data
		$image->stripImage();

		// Writes resultant image to output directory
		$image->writeImage(PATH_APP . $thumbnail_path);

		// Destroys Imagick object, freeing allocated resources in the process
		$image->destroy();


		// Return the relative path to the thumbnail
		return $thumbnail_path;
	}
}

?>