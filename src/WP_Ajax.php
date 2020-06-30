<?php

namespace motiontactic;

class WP_AJAX
{
	private $action;
	private $output_template;
	private $no_results_template;
	private $pagination_template;
	private $pagination_pages_to_show;
	private $include_nopriv;

	function __construct( array $args )
	{
		$defaults = [
			'action'                   => 'get_posts',
			'output_template'          => false,
			'no_results_template'      => false,
			'pagination_template'      => false,
			'pagination_pages_to_show' => 9,
			'include_nopriv'           => true,
		];

		$args = wp_parse_args( $args, $defaults );

		$this->action = $args[ 'action' ];
		$this->output_template = $args[ 'output_template' ];
		$this->no_results_template = $args[ 'no_results_template' ];
		$this->pagination_template = $args[ 'pagination_template' ];
		$this->pagination_pages_to_show = $args[ 'pagination_pages_to_show' ];
		$this->include_nopriv = $args[ 'include_nopriv' ];
		$this->setup_ajax_handlers();
	}

	public function setup_ajax_handlers()
	{
		add_action( 'wp_ajax_' . $this->action, array( $this, 'ajax_response' ) );
		if ( $this->include_nopriv )
			add_action( 'wp_ajax_nopriv_' . $this->action, array( $this, 'ajax_response' ) );
	}

	public function ajax_response()
	{
		[ $posts, $current_page, $max_pages ] = $this->getPostsAndPages();

		[ $html, $pagination ] = $this->get_html( $posts, $current_page, $max_pages );


		wp_send_json( [
			'html'        => $html,
			'pagination'  => $pagination,
			'currentPage' => $current_page,
			'maxPages'    => $max_pages,
			'posts'       => $posts,
		] );
	}

	public function html_response()
	{
		[ $posts, $current_page, $max_pages ] = $this->getPostsAndPages();

		[ $html, $pagination ] = $this->get_html( $posts, $current_page, $max_pages );

		return [ $html, $pagination ];
	}

	protected function get_html( $posts, $current_page, $max_pages )
	{
		$html = '';
		if ( !count( $posts ) ) {
			if ( $this->no_results_template !== false ) {
				$html = \App\Template( $this->no_results_template );
			} else {
				$html = '<div class="no-results">No Results Found</div>';
			}
		}

		if ( count( $posts ) && $this->output_template !== false )
			$html = \App\Template( $this->output_template, [ 'posts' => $posts ] );

		$pagination = '';
		if ( $max_pages > 1 && $this->pagination_template !== false ) {
			$pages_array = self::arrayOfPages( $current_page, $max_pages, $this->pagination_pages_to_show );
			$pagination = \App\Template( $this->pagination_template, [ 'pages' => $pages_array, 'current_page' => $current_page, 'max_pages' => $max_pages ] );
		}

		return [ $html, $pagination ];
	}

	protected function getQueryArgs()
	{
		return [
			'post_status' => 'publish',
			'post_type'   => 'post',
		];
	}

	protected function getPostsAndPages()
	{
		$args = $this->getQueryArgs();
		$loop = new \WP_Query( $args );
		$posts = $loop->posts;
		$current_page = (int)$loop->query_vars[ 'paged' ];
		$current_page = $current_page ? $current_page : 1;
		$max_pages = (int)$loop->max_num_pages;

		return [ $posts, $current_page, $max_pages ];
	}

	public static function arrayOfPages( $current_page, $max_pages, $pages_to_show = 9, $ellipsis_char = '...' )
	{
		if ( !( $pages_to_show % 2 ) ) $pages_to_show--; // if $pages_to_show not odd subtract 1 to make it so
		$pages_on_either_side = ( $pages_to_show - 1 ) / 2 - 2;
		$page_array = range( 1, $max_pages );
		if ( $max_pages <= $pages_to_show ) return $page_array; //if you can show all the pages, do
		$page_array = array_values( array_filter( $page_array, function ( $page ) use ( $max_pages, $current_page, $pages_to_show, $pages_on_either_side ) {
			if ( $page === 1 ) return true; //always show first page
			if ( $page === $max_pages ) return true; //always show last page
			if ( $page >= $current_page - $pages_on_either_side && $page <= $current_page + $pages_on_either_side ) return true; //show the proper count on either side of the current page
			if ( $page === 2 && $page === $current_page - $pages_on_either_side - 1 ) return true; //if we are just missing 2 show it instead of the E
			if ( $page === ( $max_pages - 1 ) && $page === $current_page + $pages_on_either_side + 1 ) return true; //if we are just missing the second from the last just show it instead of E
			$page_to_show_all = $pages_to_show - 2 - $pages_on_either_side;
			if ( $current_page <= $page_to_show_all && $page <= $page_to_show_all + $pages_on_either_side ) return true;//if we are within $pages_to_show - 2 of the start just show the first $pages_to_show - 2
			if ( $current_page > $max_pages - $page_to_show_all && $page > $max_pages - $page_to_show_all - $pages_on_either_side ) return true;//if we are within $pages_to_show - 2 of the end just show the last $pages_to_show - 2
			return false;
		} ) );
		if ( $page_array[ 1 ] !== 2 ) array_splice( $page_array, 1, 0, [ $ellipsis_char ] );
		if ( $page_array[ count( $page_array ) - 2 ] !== $max_pages - 1 ) array_splice( $page_array, count( $page_array ) - 1, 0, [ $ellipsis_char ] );
		return $page_array;
	}
}
