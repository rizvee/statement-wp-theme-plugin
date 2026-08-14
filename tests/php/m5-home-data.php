<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );
define( 'OBJECT', 'OBJECT' );

$statement_assertions = 0;
$statement_query_args = array();
$statement_products   = array();
$statement_terms      = array();
$statement_archive    = true;
$statement_page_content = array();

function wc_get_products( array $arguments ): array {
	global $statement_query_args, $statement_products;
	$statement_query_args = $arguments;
	return array_values( $statement_products );
}

function wc_get_product( int $product_id ) {
	global $statement_products;
	return $statement_products[ $product_id ] ?? false;
}

function get_the_terms( int $product_id, string $taxonomy ) {
	global $statement_terms;
	return 'statement_drop' === $taxonomy ? ( $statement_terms[ $product_id ] ?? false ) : false;
}

function is_wp_error( $value ): bool {
	return false;
}

function get_page_by_path( string $path, string $output = OBJECT, $post_type = 'page' ) {
	global $statement_archive;
	return $statement_archive && 'archive' === $path ? (object) array( 'ID' => 900 ) : null;
}

function get_post_status( int $post_id ): string {
	return 900 === $post_id ? 'publish' : 'draft';
}

function get_permalink( int $post_id ) {
	return 900 === $post_id ? 'https://example.test/archive/' : false;
}

function get_post_field( string $field, int $post_id ) {
	global $statement_page_content;
	return 'post_content' === $field ? ( $statement_page_content[ $post_id ] ?? '' ) : '';
}

function statement_assert_same( $expected, $actual, string $message ): void {
	global $statement_assertions;
	++$statement_assertions;

	if ( $expected !== $actual ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		fwrite( STDERR, 'Expected: ' . var_export( $expected, true ) . "\n" );
		fwrite( STDERR, 'Actual: ' . var_export( $actual, true ) . "\n" );
		exit( 1 );
	}
}

final class Statement_Home_Data_Test_Product {
	private $id;
	private $release_state;

	public function __construct( int $id, string $release_state ) {
		$this->id            = $id;
		$this->release_state = $release_state;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_type(): string {
		return 'simple';
	}

	public function get_meta( string $key, bool $single = true ): string {
		return '_statement_release_state' === $key ? $this->release_state : '';
	}
}

$root = dirname( __DIR__, 2 );

require $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Drop/Taxonomy.php';
require $root . '/wp-content/plugins/statement-collector-core/src/PublicApi.php';
require $root . '/wp-content/themes/statement-collector-theme/inc/home.php';

use Statement\Collector\Core\Release\ReleaseState;

$fixture = array(
	1 => array( ReleaseState::PRIVATE_ACCESS, 10 ),
	2 => array( ReleaseState::LIVE, 20 ),
	3 => array( ReleaseState::LIVE, 20 ),
	4 => array( ReleaseState::LIVE, 30 ),
	5 => array( ReleaseState::SOLD_OUT, 20 ),
	6 => array( ReleaseState::LIVE, 20 ),
	7 => array( ReleaseState::LIVE, 20 ),
	8 => array( ReleaseState::LIVE, 20 ),
);

foreach ( $fixture as $id => $data ) {
	list( $state, $drop_id ) = $data;
	$statement_products[ $id ] = new Statement_Home_Data_Test_Product( $id, $state );
	$statement_terms[ $id ]    = array(
		(object) array(
			'term_id'  => $drop_id,
			'taxonomy' => 'statement_drop',
			'name'     => "Release {$drop_id}",
		),
	);
}

$data = \Statement\Collector\Theme\get_home_release_data();

statement_assert_same( 'publish', $statement_query_args['status'] ?? null, 'Homepage query must require published products.' );
statement_assert_same( 'visible', $statement_query_args['visibility'] ?? null, 'Homepage query must require catalog-visible products.' );
statement_assert_same( 24, $statement_query_args['limit'] ?? null, 'Homepage candidate query must remain bounded.' );
statement_assert_same( 'objects', $statement_query_args['return'] ?? null, 'Homepage query must return WooCommerce objects.' );
statement_assert_same( 20, $data['drop']->term_id ?? null, 'First eligible LIVE product must determine the active Drop.' );
statement_assert_same( array( 2, 3, 6, 7 ), array_map( static fn( $product ) => $product->get_id(), $data['products'] ), 'Homepage must return at most four LIVE products from the selected Drop.' );
statement_assert_same( false, in_array( 1, array_map( static fn( $product ) => $product->get_id(), $data['products'] ), true ), 'PRIVATE_ACCESS product must not enter homepage presentation data.' );
statement_assert_same( false, in_array( 4, array_map( static fn( $product ) => $product->get_id(), $data['products'] ), true ), 'Unrelated LIVE Drop product must not enter the selected showcase.' );
statement_assert_same( false, in_array( 5, array_map( static fn( $product ) => $product->get_id(), $data['products'] ), true ), 'SOLD_OUT product must not enter homepage presentation data.' );
statement_assert_same( 'https://example.test/archive/', \Statement\Collector\Theme\get_home_archive_url(), 'Published Archive page must resolve through native page APIs.' );

$statement_archive = false;
statement_assert_same( null, \Statement\Collector\Theme\get_home_archive_url(), 'Missing Archive page must omit the gateway.' );

$statement_page_content = array(
	100 => "<!-- wp:paragraph -->\n<p> </p>\n<!-- /wp:paragraph -->",
	101 => '<!-- wp:image --><figure><img src="image.jpg" alt=""></figure><!-- /wp:image -->',
	102 => '<p>Editorial copy.</p>',
);
statement_assert_same( false, \Statement\Collector\Theme\has_home_editorial_content( 100 ), 'Effectively empty block markup must omit the editorial zone.' );
statement_assert_same( true, \Statement\Collector\Theme\has_home_editorial_content( 101 ), 'Native media-only content must retain the editorial zone.' );
statement_assert_same( true, \Statement\Collector\Theme\has_home_editorial_content( 102 ), 'Native text content must retain the editorial zone.' );

$statement_products = array(
	1 => new Statement_Home_Data_Test_Product( 1, ReleaseState::PRIVATE_ACCESS ),
	2 => new Statement_Home_Data_Test_Product( 2, ReleaseState::SOLD_OUT ),
);
$statement_terms    = array(
	1 => array( (object) array( 'term_id' => 10, 'taxonomy' => 'statement_drop', 'name' => 'Release 10' ) ),
	2 => array( (object) array( 'term_id' => 20, 'taxonomy' => 'statement_drop', 'name' => 'Release 20' ) ),
);
$empty_data         = \Statement\Collector\Theme\get_home_release_data();
statement_assert_same( null, $empty_data['drop'], 'No LIVE products must omit the active Drop.' );
statement_assert_same( array(), $empty_data['products'], 'No LIVE products must omit the product showcase.' );

fwrite( STDOUT, "PASS: M5 homepage data passed ({$statement_assertions} assertions).\n" );
