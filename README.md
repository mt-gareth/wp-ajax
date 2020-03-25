# PHP Library: WP-AJAX
[![Latest Stable Version](https://poser.pugx.org/motiontactic/wp-ajax/v/stable)](https://packagist.org/packages/motiontactic/wp-ajax)[![Total Downloads](https://poser.pugx.org/motiontactic/wp-ajax/downloads)](https://packagist.org/packages/motiontactic/wp-ajax)[![License](https://poser.pugx.org/motiontactic/wp-ajax/license)](https://packagist.org/packages/motiontactic/wp-ajax)

A class to add boilerplate code to quickly setup WordPress AJAX

## Install

Recommended installation to WP MU Plugin is through composer:
```
$ composer require motiontactic/wp-ajax
```

## Usage
### Setup
This class motiontactic\WP_AJAX is made to be extended to give you control over the query. Once extended, construct your new Class with the desired settings

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use motiontactic\WP_AJAX;

class BlogFeed extends WP_AJAX
{
	protected function getQueryArgs()
	{
		return [
			'post_status'    => 'publish',
			'post_type'      => 'post',
			'posts_per_page' => 1,
			'paged'          => $_REQUEST[ 'page' ],
		];

	}
}

new BlogFeed( [
	'action'                   => 'get_posts',
	'output_template'          => 'partials.blog-posts',
	'pagination_template'      => 'partials.blog-pagination',
	'pagination_pages_to_show' => 9,
	'include_nopriv'           => true,
] );
```

####Default Args
```php
$args = [
	'action'                   => 'get_posts',  //ajax action name used by JS to address this query
	'output_template'          => false,        //blade template location for the output html
	'pagination_template'      => false,        //blade template location for the pagination html
	'pagination_pages_to_show' => 9,            //Used in the pagination array creation
	'include_nopriv'           => true,         //Whether or not to include wp_ajax_nopriv
];
```


### Additional Functionality
If you just need access to the pagination function you can access it through the static method arrayOfPages
```php
motiontactic\WP_AJAX::arrayOfPages( 6, 100, 9 );
```

### Blade Starting Points
Output
```blade
//coming soon has access to $posts which is an array of WP Post objects
```

Pagination
```blade
@if( $current_page !== 1)
  <a href="?paged={{ $current_page - 1 }}" class="prev-arrow nav-arrow"
     data-paged="{{ $current_page - 1 }}">
    <div class="pagination-arrow prev">
      <
    </div>
  </a>
@endif
@foreach($pages as $page)
  @if($page === 'E')
    <p class="page width-auto pagination-spacing">
      ...
    </p>
  @else
    <a href="?paged={{ $page }}"
       class="page width-auto {{ $page === $current_page ? 'current' : '' }}"
       data-paged="{{ $page }}">{{ $page }}</a>
  @endif
@endforeach
@if( $current_page !== $max_pages )
  <a href="?paged={{ $current_page + 1 }}" class="next-arrow nav-arrow"
     data-paged="{{ $current_page + 1 }}">
    <div class="pagination-arrow next">
      >
    </div>
  </a>
@endif
```
