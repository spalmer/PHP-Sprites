<?php

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
  	);
  	$this->options = array_merge( $defaults, $args );
  	
  	$this->spritePNG = $this->options['dir'] . $this->options['class'] . '.png';
  	$this->spriteCSS = $this->options['dir'] . $this->options['class'] . '.css';
		
		$images = glob( $this->options['dir'] . '*.{png,gif,jpg,jpeg}', GLOB_BRACE );
		foreach( $images as $image )
		{
			$parts = pathinfo( $image );
			if( $image != $this->spritePNG ) 
			{
				if( substr( $parts['filename'], -3 ) == '@2x' )
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
  	// delete old file
  	if( file_exists( $this->spritePNG ) )
  	{
  		unlink( $this->spritePNG );
  	}
  	
  	if( sizeof( $this->images ) == 0 && sizeof( $this->retina_images ) == 0 ) return;
  	
  	foreach( $this->images as $image )
  	{
  		$size 								= getimagesize( $image );
  		$this->image_sizes[]	= $size;
  		$this->total_width 		+= $size[0];
  		$this->total_height 	= max( $total_height, $size[1] );
  	}

  	foreach( $this->retina_images as $image )
  	{
  		$size 								= getimagesize( $image );
  		$this->retina_image_sizes[]	= $size;
  		$this->total_width 		+= $size[0];
  		$this->total_height 	= max( $total_height, $size[1] );
  	}
  	
  	$png = imagecreatetruecolor( $this->total_width, $this->total_height );
		imagealphablending( $png, FALSE );
		imagesavealpha( $png, TRUE );  				  	
  	
  	$x = 0;
  	
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
      imagecopy( $png, $temp_image, $x, 0, 0, 0, $size[0], $size[1] );
      imagedestroy( $temp_image );
      
      $x += $size[0];
      	
  	}

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
      imagecopy( $png, $temp_image, $x, 0, 0, 0, $size[0], $size[1] );
      imagedestroy( $temp_image );
      
      $x += $size[0];
      	
  	}
  	
		// save the big image
  	imagepng( $png, $this->spritePNG );
  	
  }
  
  protected function buildSpriteCSS()
  {
  	// delete old file
  	if( file_exists( $this->spriteCSS ) )
  	{
  		unlink( $this->spriteCSS );
  	}  
  
  	$this->css_rules[ '.' . $this->options['class'] ] = array(
  		'display' 					=> 'inline-block',
  		'background-image' 	=> 'url("' . $this->spritePNG . '")',
  	);
  	
  	$x = 0;
  	
  	if( sizeof( $this->images ) == 0 && sizeof( $this->retina_images ) == 0 ) return;
  	
  	foreach( $this->images as $index => $image )
  	{
  		$size = $this->image_sizes[$index];
  		$parts = pathinfo( $image );
  		
  		$this->css_rules[ '.' . str_replace( array( '.', ' ', '#', '>', '+' ), '-', $parts['filename'] ) ] = array(
  			'width' 							=> $size[0] . 'px',
  			'height' 							=> $size[1] . 'px',
  			'background-position'	=> '-' . $x . 'px 0px',
  		);
  		
  		$x += $size[0];
  	}

  	foreach( $this->retina_images as $index => $image )
  	{
  		$size = $this->retina_image_sizes[$index];
  		$parts = pathinfo( $image );
  		
  		$this->retina_css_rules[ '.' . str_replace( array( '.', ' ', '#', '>', '+' ), '-', substr( $parts['filename'], 0, -3 ) ) ] = array(
  			'width' 							=> floor( $size[0] / 2 ) . 'px',
  			'height' 							=> floor( $size[1] / 2 ) . 'px',
  			'background-position'	=> '-' . ( $x / 2 ) . 'px 0px',
  			'background-size'			=> floor( $this->total_width / 2 ) . 'px ' . floor( $this->total_height / 2 ) . 'px',
  		);
  		
  		$x += $size[0];
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
  	if( sizeof( $this->retina_css_rules ))
  	{

	  	$css .= '@media only screen and (-webkit-min-device-pixel-ratio: 1.5),  
        only screen and (   min--moz-device-pixel-ratio: 1.5),  
        only screen and (     -o-min-device-pixel-ratio: 3/2),  
        only screen and (        min-device-pixel-ratio: 1.5),  
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
    $buffer = str_replace(array("\r\n","\r","\t","\n",'  ','    ','     '), '', $buffer);
    /* remove other spaces before/after ) */
    $buffer = preg_replace(array('(( )+\))','(\)( )+)'), ')', $buffer);

    return $buffer;
  }
  
  public function output()
  {
  	header('Content-Type: text/css');
  	
		if( sizeof( $this->images ) == 0 && sizeof( $this->retina_images ) == 0 ) return;  
  
		$files = array_combine( $this->images, array_map( "filemtime", $this->images ) );
		arsort($files);	
		$images_mtime = current( $files );
  	
		$files = array_combine( $this->retina_images, array_map( "filemtime", $this->retina_images ) );
		arsort($files);	
		$retina_images_mtime = current( $files );
  	
  	$latest = max( $images_mtime, $retina_images_mtime );
  	
  	// rebuild if necessary
  	if( 
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