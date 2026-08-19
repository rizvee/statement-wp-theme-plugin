<?php

namespace Statement\Integration\Fixtures;

use Statement\Collector\Core\Access\Schema as AccessSchema;
use Statement\Collector\Core\Access\ReminderService;
use WC_Order;

defined( 'ABSPATH' ) || exit;

/**
 * Hardened, deterministic, administrator-only QA cleanup service.
 *
 * Enforces strict safety rules:
 * - DRY RUN first capability with complete structured entity reporting
 * - Zero broad wildcard deletions
 * - Multi-signal QA entity verification (_statement_fixture = 1, TEST-* SKU, TEST —* title, manifests)
 * - Strict preservation of Drop 001, Product 213, Product 271, and genuine customer data
 * - Referential cleanup order for M10 Private Access operational tables (canonical Schema)
 * - HPOS-compatible QA order deletion (_statement_is_qa_order = 'yes')
 * - Scoped Action Scheduler reminder cancellation (matching QA grant IDs only)
 * - Zero PII or secrets in reports
 * - Fully idempotent and capability guarded
 */
class FinalCleanupService {

	public const TEST_DROP_SLUGS = array(
		'test-live-drop-01',
		'test-private-drop-01',
	);

	public const TEST_CAT_SLUGS = array(
		'test-outerwear',
	);

	public const TEST_TAG_SLUGS = array(
		'test-integration',
	);

	public const QA_OPTIONS = array(
		'statement_fixture_manifest',
		'statement_private_integration_fixture_manifest',
		'statement_qa_last_order_id',
		'statement_qa_last_test_run',
	);

	/**
	 * Perform a read-only dry-run audit of all QA entities without making any mutations.
	 *
	 * @return array<string, mixed>
	 */
	public static function dry_run(): array {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return array(
				'success'            => false,
				'is_safe_to_execute' => false,
				'message'            => 'Unauthorized: manage_woocommerce capability required.',
				'ambiguities'        => array( 'Unauthorized capability check failed.' ),
			);
		}

		global $wpdb;

		$products_to_delete   = array();
		$variations_to_delete = array();
		$terms_to_delete      = array();
		$orders_to_delete     = array();
		$options_to_delete    = array();
		$access_rows          = array();
		$actions_to_cancel    = array();
		$preserved_entities   = array();
		$ambiguities          = array();

		// 1. Discover QA Products & Variations
		$manifest_v1 = get_option( 'statement_fixture_manifest', false );
		$manifest_v2 = get_option( 'statement_private_integration_fixture_manifest', false );

		$known_qa_product_ids = array();
		if ( is_array( $manifest_v1 ) ) {
			if ( ! empty( $manifest_v1['product_ids'] ) ) {
				$known_qa_product_ids = array_merge( $known_qa_product_ids, (array) $manifest_v1['product_ids'] );
			}
			if ( ! empty( $manifest_v1['variation_ids'] ) ) {
				$known_qa_product_ids = array_merge( $known_qa_product_ids, (array) $manifest_v1['variation_ids'] );
			}
		}
		if ( is_array( $manifest_v2 ) && ! empty( $manifest_v2['product_id'] ) ) {
			$known_qa_product_ids[] = (int) $manifest_v2['product_id'];
		}

		// Query potential QA products by metadata or SKU pattern
		if ( isset( $wpdb ) ) {
			$meta_qa_ids = $wpdb->get_col(
				"SELECT DISTINCT post_id FROM {$wpdb->postmeta}
				WHERE (meta_key = '_statement_fixture' AND meta_value IN ('1', 'yes'))
				   OR (meta_key = '_sku' AND meta_value LIKE 'TEST-%')"
			);
			if ( ! empty( $meta_qa_ids ) ) {
				$known_qa_product_ids = array_unique( array_merge( $known_qa_product_ids, array_map( 'intval', $meta_qa_ids ) ) );
			}
		}

		// Evaluate each candidate product safely
		foreach ( $known_qa_product_ids as $pid ) {
			$post = get_post( $pid );
			if ( ! $post ) {
				continue;
			}

			$sku         = (string) get_post_meta( $pid, '_sku', true );
			$is_fixture  = get_post_meta( $pid, '_statement_fixture', true );
			$is_demo     = get_post_meta( $pid, '_statement_client_demo', true );
			$title       = $post->post_title;
			$post_type   = $post->post_type;

			// Strict Product 213 / 271 / Client Demo Protection:
			// If it has STMT-CD-* SKU or _statement_client_demo = 1 AND lacks explicit _statement_fixture = 1, PRESERVE.
			if ( ( '1' === (string) $is_demo || str_starts_with( $sku, 'STMT-CD-' ) ) && '1' !== (string) $is_fixture ) {
				$preserved_entities[] = array(
					'id'     => $pid,
					'title'  => $title,
					'sku'    => $sku,
					'reason' => 'Client Demo / Production entity (Must be preserved).',
				);
				continue;
			}

			// Confirm exact QA identity markers
			$is_confirmed_qa = ( '1' === (string) $is_fixture || 'yes' === (string) $is_fixture || str_starts_with( $sku, 'TEST-' ) || str_starts_with( $title, 'TEST —' ) );

			if ( $is_confirmed_qa ) {
				if ( 'product_variation' === $post_type ) {
					$variations_to_delete[] = array(
						'id'        => $pid,
						'title'     => $title,
						'sku'       => $sku,
						'parent_id' => $post->post_parent,
					);
				} else {
					$products_to_delete[] = array(
						'id'    => $pid,
						'title' => $title,
						'sku'   => $sku,
					);
				}
			} else {
				$ambiguities[] = "Product ID {$pid} ('{$title}', SKU: '{$sku}') lacks conclusive QA signals. Skipped for safety.";
			}
		}

		// Always verify Drop 001 production products are marked preserved
		$prod_213 = get_post( 213 );
		if ( $prod_213 && ! in_array( 213, array_column( $preserved_entities, 'id' ), true ) ) {
			$preserved_entities[] = array(
				'id'     => 213,
				'title'  => $prod_213->post_title,
				'sku'    => (string) get_post_meta( 213, '_sku', true ),
				'reason' => 'Monogram Jacquard Jacket (Production presentation).',
			);
		}
		$prod_271 = get_post( 271 );
		if ( $prod_271 && ! in_array( 271, array_column( $preserved_entities, 'id' ), true ) ) {
			$preserved_entities[] = array(
				'id'     => 271,
				'title'  => $prod_271->post_title,
				'sku'    => (string) get_post_meta( 271, '_sku', true ),
				'reason' => 'Panelled Hood Jacket (Production presentation).',
			);
		}

		// 2. Discover QA Taxonomy Terms
		$test_drop_ids = array();
		foreach ( self::TEST_DROP_SLUGS as $slug ) {
			$term = get_term_by( 'slug', $slug, 'statement_drop' );
			if ( $term && is_object( $term ) && ! is_wp_error( $term ) ) {
				$terms_to_delete[] = array(
					'id'       => (int) $term->term_id,
					'taxonomy' => 'statement_drop',
					'slug'     => $term->slug,
					'name'     => $term->name,
				);
				$test_drop_ids[]   = (int) $term->term_id;
			}
		}
		foreach ( self::TEST_CAT_SLUGS as $slug ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );
			if ( $term && is_object( $term ) && ! is_wp_error( $term ) ) {
				$terms_to_delete[] = array(
					'id'       => (int) $term->term_id,
					'taxonomy' => 'product_cat',
					'slug'     => $term->slug,
					'name'     => $term->name,
				);
			}
		}
		foreach ( self::TEST_TAG_SLUGS as $slug ) {
			$term = get_term_by( 'slug', $slug, 'product_tag' );
			if ( $term && is_object( $term ) && ! is_wp_error( $term ) ) {
				$terms_to_delete[] = array(
					'id'       => (int) $term->term_id,
					'taxonomy' => 'product_tag',
					'slug'     => $term->slug,
					'name'     => $term->name,
				);
			}
		}

		// Verify Drop 001 is preserved
		$drop_001 = get_term_by( 'slug', 'drop-001-monogram-study', 'statement_drop' );
		if ( $drop_001 && is_object( $drop_001 ) && ! is_wp_error( $drop_001 ) ) {
			$preserved_entities[] = array(
				'id'     => (int) $drop_001->term_id,
				'title'  => $drop_001->name,
				'sku'    => 'TAXONOMY_TERM',
				'reason' => 'Drop 001: Monogram Study (Production Drop).',
			);
		}

		// 3. Discover QA Orders (HPOS & Legacy Safe)
		if ( function_exists( 'wc_get_orders' ) ) {
			$qa_orders = wc_get_orders(
				array(
					'limit'        => -1,
					'meta_key'     => '_statement_is_qa_order',
					'meta_value'   => 'yes',
					'meta_compare' => '=',
					'return'       => 'objects',
				)
			);
			foreach ( $qa_orders as $order ) {
				if ( is_object( $order ) && method_exists( $order, 'get_id' ) ) {
					$orders_to_delete[] = array(
						'id'     => (int) $order->get_id(),
						'number' => method_exists( $order, 'get_order_number' ) ? $order->get_order_number() : (string) $order->get_id(),
						'total'  => method_exists( $order, 'get_total' ) ? $order->get_total() : '',
					);
				}
			}
		}

		// 4. Discover QA Database Rows in M10 Operational Tables (Canonical Schema)
		$qa_grant_ids = array();
		if ( isset( $wpdb ) && class_exists( AccessSchema::class ) ) {
			$tables = AccessSchema::get_table_names( $wpdb->prefix );

			// Check table existence first
			$grants_table    = $tables['grants'];
			$sessions_table  = $tables['sessions'];
			$tokens_table    = $tables['tokens'];
			$limits_table    = $tables['rate_limits'];
			$consent_table   = $tables['consent'];

			$has_grants = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $grants_table ) ) === $grants_table );

			if ( $has_grants ) {
				// Find grant IDs associated with test drops or source 'qa_test'
				$drop_placeholders = ! empty( $test_drop_ids ) ? implode( ',', array_map( 'intval', $test_drop_ids ) ) : '0';
				$grant_rows        = $wpdb->get_col(
					"SELECT id FROM {$grants_table} WHERE drop_term_id IN ({$drop_placeholders}) OR source = 'qa_test'"
				);
				if ( ! empty( $grant_rows ) ) {
					$qa_grant_ids = array_map( 'intval', $grant_rows );
				}

				$access_rows['statement_access_grants'] = count( $qa_grant_ids );

				// Sessions
				if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $sessions_table ) ) === $sessions_table ) {
					$grant_in = ! empty( $qa_grant_ids ) ? implode( ',', $qa_grant_ids ) : '0';
					$sess_cnt = (int) $wpdb->get_var(
						"SELECT COUNT(*) FROM {$sessions_table} WHERE drop_term_id IN ({$drop_placeholders}) OR grant_id IN ({$grant_in})"
					);
					$access_rows['statement_access_sessions'] = $sess_cnt;
				}

				// Tokens
				if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tokens_table ) ) === $tokens_table ) {
					$grant_in = ! empty( $qa_grant_ids ) ? implode( ',', $qa_grant_ids ) : '0';
					$tok_cnt  = (int) $wpdb->get_var(
						"SELECT COUNT(*) FROM {$tokens_table} WHERE grant_id IN ({$grant_in})"
					);
					$access_rows['statement_access_tokens'] = $tok_cnt;
				}

				// Rate limits
				if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $limits_table ) ) === $limits_table ) {
					$limit_cnt = (int) $wpdb->get_var(
						"SELECT COUNT(*) FROM {$limits_table} WHERE drop_term_id IN ({$drop_placeholders})"
					);
					$access_rows['statement_access_rate_limits'] = $limit_cnt;
				}

				// Consent events
				if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $consent_table ) ) === $consent_table ) {
					$grant_in    = ! empty( $qa_grant_ids ) ? implode( ',', $qa_grant_ids ) : '0';
					$consent_cnt = (int) $wpdb->get_var(
						"SELECT COUNT(*) FROM {$consent_table} WHERE source = 'qa_test' OR drop_term_id IN ({$drop_placeholders}) OR grant_id IN ({$grant_in})"
					);
					$access_rows['statement_consent_events'] = $consent_cnt;
				}
			}
		}

		// 5. Discover Action Scheduler Reminders for QA Grants
		if ( ! empty( $qa_grant_ids ) && function_exists( 'as_get_scheduled_actions' ) ) {
			$hook = class_exists( ReminderService::class ) ? ReminderService::ACTION_HOOK : 'statement_private_access_reminder_action';
			foreach ( $qa_grant_ids as $qa_gid ) {
				$actions = as_get_scheduled_actions(
					array(
						'hook'     => $hook,
						'args'     => array( 'grant_id' => (int) $qa_gid ),
						'status'   => \ActionScheduler_Store::STATUS_PENDING,
						'per_page' => 10,
					),
					'ids'
				);
				if ( ! empty( $actions ) ) {
					foreach ( $actions as $aid ) {
						$actions_to_cancel[] = (int) $aid;
					}
				}
			}
		}

		// 6. Discover QA Options
		foreach ( self::QA_OPTIONS as $opt ) {
			if ( false !== get_option( $opt, false ) ) {
				$options_to_delete[] = $opt;
			}
		}

		$is_safe = empty( $ambiguities );

		return array(
			'success'                           => true,
			'is_safe_to_execute'                => $is_safe,
			'products_to_delete'                => $products_to_delete,
			'variations_to_delete'              => $variations_to_delete,
			'terms_to_delete'                   => $terms_to_delete,
			'orders_to_delete'                  => $orders_to_delete,
			'options_to_delete'                 => $options_to_delete,
			'access_rows_to_delete'             => $access_rows,
			'action_scheduler_action_ids'       => $actions_to_cancel,
			'preserved_entities'                => $preserved_entities,
			'ambiguities'                       => $ambiguities,
			'total_entities_to_clean'           => count( $products_to_delete ) + count( $variations_to_delete ) + count( $terms_to_delete ) + count( $orders_to_delete ) + count( $options_to_delete ),
		);
	}

	/**
	 * Execute final production cleanup safely based on verified dry-run.
	 *
	 * @return array<string, mixed>
	 */
	public static function execute_cleanup(): array {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return array(
				'success' => false,
				'message' => 'Unauthorized: manage_woocommerce capability required.',
			);
		}

		$dry_run = self::dry_run();
		if ( ! $dry_run['is_safe_to_execute'] || ! empty( $dry_run['ambiguities'] ) ) {
			return array(
				'success' => false,
				'message' => 'Cleanup aborted: Safety checks failed due to entity ambiguity.',
				'details' => $dry_run['ambiguities'],
			);
		}

		global $wpdb;
		$deleted_log = array();

		// 1. Discover QA grant IDs and clean M10 Private Access DB Rows in Referential Order
		$qa_grant_ids = array();
		if ( isset( $wpdb ) && class_exists( AccessSchema::class ) ) {
			$tables            = AccessSchema::get_table_names( $wpdb->prefix );
			$test_drop_ids     = array();
			foreach ( $dry_run['terms_to_delete'] as $t ) {
				if ( 'statement_drop' === $t['taxonomy'] ) {
					$test_drop_ids[] = (int) $t['id'];
				}
			}
			$drop_placeholders = ! empty( $test_drop_ids ) ? implode( ',', $test_drop_ids ) : '0';

			$grants_table   = $tables['grants'];
			$sessions_table = $tables['sessions'];
			$tokens_table   = $tables['tokens'];
			$limits_table   = $tables['rate_limits'];
			$consent_table  = $tables['consent'];

			// Identify QA grant IDs
			$grant_rows = $wpdb->get_col(
				"SELECT id FROM {$grants_table} WHERE drop_term_id IN ({$drop_placeholders}) OR source = 'qa_test'"
			);
			$qa_grant_ids = ! empty( $grant_rows ) ? array_map( 'intval', $grant_rows ) : array();
			$grant_in     = ! empty( $qa_grant_ids ) ? implode( ',', $qa_grant_ids ) : '0';

			// 2. Unschedule Action Scheduler QA reminder actions for QA grants ONLY
			if ( ! empty( $qa_grant_ids ) && function_exists( 'as_unschedule_action' ) ) {
				$hook = class_exists( ReminderService::class ) ? ReminderService::ACTION_HOOK : 'statement_private_access_reminder_action';
				foreach ( $qa_grant_ids as $qa_gid ) {
					as_unschedule_action( $hook, array( 'grant_id' => (int) $qa_gid ), 'statement' );
				}
			}
			if ( ! empty( $dry_run['action_scheduler_action_ids'] ) ) {
				foreach ( $dry_run['action_scheduler_action_ids'] as $aid ) {
					$deleted_log[] = "Unscheduled Action Scheduler reminder action #{$aid}";
				}
			}

			// 3. Delete from operational tables in referential order (Sessions -> Tokens -> Rate Limits -> Consent -> Grants)
			$sess_del = $wpdb->query(
				"DELETE FROM {$sessions_table} WHERE drop_term_id IN ({$drop_placeholders}) OR grant_id IN ({$grant_in})"
			);
			if ( $sess_del ) {
				$deleted_log[] = "Deleted {$sess_del} QA session row(s) from statement_access_sessions";
			}

			$tok_del = $wpdb->query(
				"DELETE FROM {$tokens_table} WHERE grant_id IN ({$grant_in})"
			);
			if ( $tok_del ) {
				$deleted_log[] = "Deleted {$tok_del} QA token row(s) from statement_access_tokens";
			}

			$lim_del = $wpdb->query(
				"DELETE FROM {$limits_table} WHERE drop_term_id IN ({$drop_placeholders})"
			);
			if ( $lim_del ) {
				$deleted_log[] = "Deleted {$lim_del} QA rate limit row(s) from statement_access_rate_limits";
			}

			$con_del = $wpdb->query(
				"DELETE FROM {$consent_table} WHERE source = 'qa_test' OR drop_term_id IN ({$drop_placeholders}) OR grant_id IN ({$grant_in})"
			);
			if ( $con_del ) {
				$deleted_log[] = "Deleted {$con_del} QA consent event row(s) from statement_consent_events";
			}

			$grt_del = $wpdb->query(
				"DELETE FROM {$grants_table} WHERE drop_term_id IN ({$drop_placeholders}) OR source = 'qa_test'"
			);
			if ( $grt_del ) {
				$deleted_log[] = "Deleted {$grt_del} QA grant row(s) from statement_access_grants";
			}
		}

		// 3. Delete QA Orders (HPOS & Legacy Safe)
		foreach ( $dry_run['orders_to_delete'] as $ord ) {
			$order_id = (int) $ord['id'];
			if ( function_exists( 'wc_get_order' ) ) {
				$order = wc_get_order( $order_id );
				if ( is_object( $order ) && method_exists( $order, 'delete' ) ) {
					$order->delete( true );
					$deleted_log[] = "Permanently deleted QA Order #{$ord['number']} (ID: {$order_id})";
				}
			} elseif ( function_exists( 'wp_delete_post' ) ) {
				wp_delete_post( $order_id, true );
				$deleted_log[] = "Permanently deleted QA Order ID {$order_id}";
			}
		}

		// 4. Delete QA Product Variations
		foreach ( $dry_run['variations_to_delete'] as $v ) {
			$vid = (int) $v['id'];
			if ( function_exists( 'wc_get_product' ) ) {
				$prod = wc_get_product( $vid );
				if ( is_object( $prod ) ) {
					$prod->delete( true );
					$deleted_log[] = "Deleted QA Product Variation '{$v['title']}' (ID: {$vid}, SKU: {$v['sku']})";
				}
			} else {
				wp_delete_post( $vid, true );
				$deleted_log[] = "Deleted QA Product Variation ID {$vid}";
			}
		}

		// 5. Delete QA Products
		foreach ( $dry_run['products_to_delete'] as $p ) {
			$pid = (int) $p['id'];
			if ( function_exists( 'wc_get_product' ) ) {
				$prod = wc_get_product( $pid );
				if ( is_object( $prod ) ) {
					$prod->delete( true );
					$deleted_log[] = "Deleted QA Product '{$p['title']}' (ID: {$pid}, SKU: {$p['sku']})";
				}
			} else {
				wp_delete_post( $pid, true );
				$deleted_log[] = "Deleted QA Product ID {$pid}";
			}
		}

		// 6. Delete QA Taxonomy Terms
		foreach ( $dry_run['terms_to_delete'] as $t ) {
			$tid = (int) $t['id'];
			$tax = (string) $t['taxonomy'];
			wp_delete_term( $tid, $tax );
			$deleted_log[] = "Deleted QA Term '{$t['name']}' (Taxonomy: {$tax}, ID: {$tid}, Slug: {$t['slug']})";
		}

		// 7. Delete QA Options
		foreach ( $dry_run['options_to_delete'] as $opt ) {
			delete_option( $opt );
			$deleted_log[] = "Deleted QA Option '{$opt}'";
		}

		// 8. Restore Currency if needed
		CleanupService::restore_currency();

		return array(
			'success'      => true,
			'message'      => 'Final QA cleanup executed successfully. ' . count( $deleted_log ) . ' item(s) cleaned.',
			'deleted_log'  => $deleted_log,
			'preserved'    => $dry_run['preserved_entities'],
		);
	}
}
