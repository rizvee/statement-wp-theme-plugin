<?php

namespace Statement\Collector\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Coordinates core integrations without owning feature implementations.
 */
final class Plugin {
	/** @var bool */
	private static $booted = false;

	/**
	 * Register the bootstrap once.
	 */
	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;
		add_action( 'plugins_loaded', array( self::class, 'register_integrations' ) );
	}

	/**
	 * Register domain integrations after WordPress plugins load.
	 */
	public static function register_integrations(): void {
		Access\Schema::maybe_upgrade();
		Drop\Taxonomy::boot();
		Access\PrivateAccessGate::boot();
		Access\UnsubscribeService::boot();
		Access\CacheHardening::boot();
		Access\DropConfigAdmin::boot();

		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		Product\Admin::boot();
		Product\Access::boot();
		Release\Purchasability::boot();
		Catalog\Visibility::boot();
		Cart\Integrity::boot();
		Access\OrderAudit::boot();
		Access\EmailAccessGranted::boot();
		Access\EmailAccessReminder::boot();
		Access\ReminderService::boot();
		Access\AdminUi::boot();
		Access\RetentionService::boot();
		Order\Provenance::boot();
		Order\AdminOrderView::boot();
		Order\CustomerOrderView::boot();
		Order\EmailIntegration::boot();
		Marketing\SignupService::boot();
		Admin\LifecycleV2Admin::boot();
	}
}
