<?php

/**
 * PHP Sprites v0.1
 *
 * Terms of Use - PHP Sprites
 * under the MIT (http://www.opensource.org/licenses/mit-license.php) License.
 *
 * Copyright 2013 Steve Palmer All rights reserved.
 * (https://github.com/spalmer/PHP-Sprites)
 *
 */
 
class Spritesheet { 

	protected $options;
	
	protected $spritePNG;
	protected $spriteCSS;
	
	protected $images = array();
	protected $image_sizes = array();
	protected $retina_images = array();
	protected $retina_image_sizes = array();
	protected $total_width = 0;
	protected $total_height = 0;

	protected $css_rules = array();
	protected $retina_css_rules = array();


	public function __construct( $args = array() ) 
	{
		$defaults = array(
			'dir'			=> 'images/',
			'class'		=> 'sprite',
			'gutter'	=> 10,
		);
		$this->options = array_merge( $defaults, $args );
		
		$this->spritePNG = $this->options['class'] . '.png';
		$this->spriteCSS = $this->options['class'] . '.css';
		
		$images = glob( $this->options['dir'] . '*.{png,gif,jpg,jpeg}', GLOB_BRACE );
		foreach( $images as $image )
		{
			$parts = pathinfo( $image );
			if ( $image != $this->spritePNG ) 
			{
				if ( substr( $parts['filename'], -3 ) == '@2x' )
				{
					$this->retina_images[] = $image;
				}
				else
				{
					$this->images[] = $image;
				}
			}
		}

	}	

	protected function buildSpritePNG() 
	{
		$gutter = $this->options['gutter'];
		$row_width = 0;
		$row_height = 0;
		$first_row_height = 0;
		
		// delete old file
		if ( file_exists( $this->spritePNG ) )
		{
			unlink( $this->spritePNG );
		}
		
		if ( sizeof( $this->images ) == 0 && sizeof( $this->retina_images ) == 0 ) return;
		
		// regular images
		foreach( $this->images as $image )
		{
			$size 							= getimagesize( $image );
			$this->image_sizes[]= $size;
			$row_width					+= $size[0];
			$row_height					= max( $row_height, $size[1] );
		}

		$this->total_width 		= $row_width + $gutter * sizeof( $this->images );
		$this->total_height 	= $row_height + $gutter;
		$row_width						= 0;
		$row_height						= 0;
		$first_row_height			= $this->total_height;

		// retina images
		foreach( $this->retina_images as $image )
		{
			$size 							= getimagesize( $image );
			$this->retina_image_sizes[]	= $size;
			$row_width					+= $size[0];
			$row_height					= max( $row_height, $size[1] );
		}

		$this->total_width 		= max( $this->total_width, $row_width + $gutter * sizeof( $this->retina_images ) );
		$this->total_height 	+= $row_height + $gutter;
		
		// create png image
		$png = imagecreatetruecolor( $this->total_width, $this->total_height );
		imagealphablending( $png, FALSE );
		$fill = imagecolorallocatealpha( $png, 255, 255, 255, 127 );
		imagefilledrectangle( $png, 0, 0, $this->total_width, $this->total_height, $fill );
		imagesavealpha( $png, TRUE );							
		
		// set positions
		$x = 0;
		$y = 0;
		
		// place images
		foreach( $this->images as $index => $image )
		{
			$size = $this->image_sizes[$index];
			switch( $size[2] )
			{
				case IMAGETYPE_PNG:
					$temp_image = imagecreatefrompng( $image );
					break;
				case IMAGETYPE_GIF:
					$temp_image = imagecreatefromgif( $image );
					break;
				case IMAGETYPE_JPG:
					$temp_image = imagecreatefromjpeg( $image );
					break;
			}
			imagecopy( $png, $temp_image, $x, $y, 0, 0, $size[0], $size[1] );
			imagealphablending( $png, TRUE );
			imagedestroy( $temp_image );
			
			$x += ceil( $size[0] / 2 ) * 2 + $gutter;
				
		}
		
		// reset positions
		$x = 0;
		$y += $first_row_height;

 		foreach( $this->retina_images as $index => $image )
		{
			$size = $this->retina_image_sizes[$index];
			switch( $size[2] )
			{
				case IMAGETYPE_PNG:
					$temp_image = imagecreatefrompng( $image );
					break;
				case IMAGETYPE_GIF:
					$temp_image = imagecreatefromgif( $image );
					break;
				case IMAGETYPE_JPG:
					$temp_image = imagecreatefromjpeg( $image );
					break;
			}
			imagecopy( $png, $temp_image, $x, $y, 0, 0, $size[0], $size[1] );
			imagealphablending( $png, TRUE );
			imagedestroy( $temp_image );
			
			$x += ceil( $size[0] / 2 ) * 2 + $gutter;
				
		}
		
		// save the big image
		imagealphablending( $png, FALSE );
		imagesavealpha( $png, TRUE );			
		imagepng( $png, $this->spritePNG, 9 );
		
	}
	
	protected function buildSpriteCSS()
	{
		$gutter = $this->options['gutter'];
		$row_width = 0;
		$row_height = 0;
		
		// delete old file
		if ( file_exists( $this->spriteCSS ) )
		{
			unlink( $this->spriteCSS );
		}	
	
		$this->css_rules[ '.' . $this->options['class'] ] = array(
			'display' 						=> 'inline-block',
			'background-image' 		=> 'url("' . $this->spritePNG . '")',
			'background-repeat'		=> 'no-repeat',
			'background-position' => 'top left',
		);
		
		$x = 0;
		$y = 0;
		
		if ( sizeof( $this->images ) == 0 && sizeof( $this->retina_images ) == 0 ) return;
		
		foreach( $this->images as $index => $image )
		{
			$size = $this->image_sizes[$index];
			$row_height = max( $row_height, $size[1] );
			$parts = pathinfo( $image );
			
			$this->css_rules[ '.' . str_replace( array( '.', ' ', '#', '>', '+' ), '-', $parts['filename'] ) ] = array(
				'width' 							=> $size[0] . 'px',
				'height' 							=> $size[1] . 'px',
				'background-position'	=> '-' . ( $x + $index * $gutter ) . 'px -' . $y . 'px',
			);
			
			$x += ceil( $size[0] / 2 ) * 2; // multiple of 2
		}
		
		$x = 0;
		$y += ceil( $row_height / 2 ) * 2;

		foreach( $this->retina_images as $index => $image )
		{
			$size = $this->retina_image_sizes[$index];
			$parts = pathinfo( $image );
			
			$this->retina_css_rules[ '.' . str_replace( array( '.', ' ', '#', '>', '+' ), '-', substr( $parts['filename'], 0, -3 ) ) ] = array(
				'width' 							=> ceil( $size[0] / 2 ) . 'px',
				'height' 							=> ceil( $size[1] / 2 ) . 'px',
				'background-position'	=> '-' . ( ( $x + $index * $gutter ) / 2 ) . 'px -' . ( ( $y + $gutter ) / 2 ) . 'px',
				'background-size'			=> floor( $this->total_width / 2 ) . 'px ' . floor( $this->total_height / 2 ) . 'px',
			);
			
			$x += ceil( $size[0] / 2 ) * 2; // multiple of 2
		}
		
		$css = '';
		
		// regular rules
		foreach( $this->css_rules as $selector => $rules )
		{
			$css .= $selector . ' {';
			
			foreach( $rules as $property => $value )
			{
				$css .= $property . ': ' . $value . ';';
			}

			$css .= '}';
		}
		
		// retina rules
		if ( sizeof( $this->retina_css_rules ))
		{

			$css .= '@media only screen and (-webkit-min-device-pixel-ratio: 1.5),	
				only screen and ( 	min--moz-device-pixel-ratio: 1.5),	
				only screen and ( 		-o-min-device-pixel-ratio: 3/2),	
				only screen and (				min-device-pixel-ratio: 1.5),	
				only screen and (min-resolution: 192dpi) { 
			';
	
			foreach( $this->retina_css_rules as $selector => $rules )
			{
				$css .= $selector . ' {';
				
				foreach( $rules as $property => $value )
				{
					$css .= $property . ': ' . $value . ';';
				}
	
				$css .= '}';
			}
			
			$css .= '}'; // close retina media query
		
		}
		
		file_put_contents( $this->spriteCSS, '/* Generated by PHP sprites http://github.com/spalmer/PHP-Sprites */ ' . $this->compress( $css ) );		
		
	} 
	
	protected function compress( $buffer )
	{
		/* remove comments */
		$buffer = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", "", $buffer);
		/* remove tabs, spaces, newlines, etc. */
		$buffer = str_replace(array("\r\n","\r","\t","\n",'	','		',' 		'), '', $buffer);
		/* remove other spaces before/after ) */
		$buffer = preg_replace(array('(( )+\))','(\)( )+)'), ')', $buffer);

		return $buffer;
	}
	
	public function output()
	{
		header('Content-Type: text/css');
		
		if ( sizeof( $this->images ) == 0 && sizeof( $this->retina_images ) == 0 ) 
		{
			echo '/* There are no images in this folder! */';
			return;
		}	
		
		$images_mtime = 0;
		$retina_images_mtime = 0;
	
		$files = @array_combine( @$this->images, array_map( "filemtime", @$this->images ) );
		if ( is_array( $files ) && sizeof( $files ) )
		{
			arsort($files);	
			$images_mtime = current( $files );
		}
		
		$files = @array_combine( @$this->retina_images, array_map( "filemtime", @$this->retina_images ) );
		if ( is_array( $files ) && sizeof( $files ) )
		{
			arsort($files);	
			$retina_images_mtime = current( $files );
		}
		
		$latest = max( $images_mtime, $retina_images_mtime );
		
		// rebuild if necessary
		if ( 
			! file_exists( $this->spritePNG ) || 
			filemtime( $this->spritePNG ) < $latest || 
			! file_exists( $this->spriteCSS ) || 
			filemtime( $this->spriteCSS ) < $latest
		)
		{
			$this->buildSpritePNG();
			$this->buildSpriteCSS();
		}

		echo file_get_contents( $this->spriteCSS );
		
	}

} // end class


$sprites = new Spritesheet( $_GET );
$sprites->output();
