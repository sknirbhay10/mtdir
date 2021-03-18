<?php

	add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

	function my_theme_enqueue_styles() {

		wp_enqueue_style( 'listingpr-parent-style', get_template_directory_uri() . '/style.css' );

	}






function getpost_shortcode() {
    // get dynamic blogs from WordPress
    $html="";
	$json = file_get_contents('https://mediatimes.com.au/wp-json/wp/v2/posts?per_page=3&_embed&categories=359');
    $posts = json_decode($json); // Convert the JSON to an array of posts
   //  echo '<pre>';
   // print_r($posts );
    foreach ($posts as $p) {
	$html.='<div class="col-md-4 col-sm-4 lp-blog-grid-box"><div class="lp-blog-grid-box-container lp-border lp-border-radius-8">
	<div class="lp-blog-grid-box-thumb">
				<a href="'.$p->link.'"><img src="'.$p->_embedded->{'wp:featuredmedia'}[0]->source_url.'" alt="blog-grid-1-410x308"></a>
			</div>
			<div class="lp-blog-grid-box-description text-center">
					<div class="lp-blog-user-thumb margin-top-subtract-25">
						<img class="avatar" src="https://secure.gravatar.com/avatar/3a3c418f908e1222dbe2971373f05483?s=51&amp;d=mm&amp;r=g" alt="">
					</div>
					<div class="lp-blog-grid-category"><a href="#">'.$p->category.'</a></div>
					<div class="lp-blog-grid-title">
						<h4 class="lp-h4"><a href="'.$p->link.'">'.$p->title->rendered.'</a></h4>
					</div>
					<ul class="lp-blog-grid-author">
						<li><i class="fa fa-calendar"></i>
							<span>'.date('M d, Y', strtotime($p->date)).'</span>
						</li>
					</ul>
			</div>';
	$html.='</div></div>';
									
	}
		return $html;

}
add_shortcode('getMTPost', 'getpost_shortcode'); 



?>

