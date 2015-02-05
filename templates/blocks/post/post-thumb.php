<?php

if ( !defined( 'ABSPATH' ) )
	exit( 'No direct script access allowed' ); // Exit if accessed directly

// setup vars
$author = get_field('post_thumb_author');

?>
<div class="featured-image">
	<?php
	get_the_image(array(
		'link_to_post' => true,
		'image_class' => array('img-responsive', 'img-rounded', 'img-full'),
		'meta_key' => false,
		'size' => 'thumbnail'
		)
	);
	?>
	<div class="image-author">
		<?php
		if ($author) 
			echo $author;
		?>
	</div>
</div>