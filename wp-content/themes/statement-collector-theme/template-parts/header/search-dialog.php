<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;
?>
<dialog class="statement-dialog statement-search-dialog" id="statement-search-dialog" aria-labelledby="statement-search-title">
	<div class="statement-search-dialog__header">
		<h2 class="statement-search-dialog__title" id="statement-search-title"><?php esc_html_e( 'Catalogue Search', 'statement-collector-theme' ); ?></h2>
		<button class="statement-dialog-close" type="button" data-dialog-close aria-label="<?php esc_attr_e( 'Close search', 'statement-collector-theme' ); ?>">
			<?php render_statement_icon( 'close' ); ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Close search', 'statement-collector-theme' ); ?></span>
		</button>
	</div>
	<form class="statement-search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="screen-reader-text" for="statement-search-field"><?php esc_html_e( 'Search for:', 'statement-collector-theme' ); ?></label>
		<div class="statement-search-form__input-wrapper">
			<?php render_statement_icon( 'search', array( 'class' => 'statement-search-form__icon' ) ); ?>
			<input
				class="statement-search-form__field"
				id="statement-search-field"
				type="search"
				name="s"
				value="<?php echo esc_attr( get_search_query() ); ?>"
				placeholder="<?php echo esc_attr_x( 'Search pieces, releases, fabrics...', 'search field placeholder', 'statement-collector-theme' ); ?>"
				data-dialog-focus
			>
		</div>
		<button class="statement-search-form__submit" type="submit">
			<span><?php esc_html_e( 'Search', 'statement-collector-theme' ); ?></span>
			<?php render_statement_icon( 'arrow-right', array( 'class' => 'statement-search-form__submit-icon' ) ); ?>
		</button>
	</form>
</dialog>
