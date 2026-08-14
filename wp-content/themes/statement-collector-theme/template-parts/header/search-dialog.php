<?php

defined( 'ABSPATH' ) || exit;
?>
<dialog class="statement-dialog statement-search-dialog" id="statement-search-dialog" aria-labelledby="statement-search-title">
	<div class="statement-search-dialog__header">
		<h2 class="statement-search-dialog__title" id="statement-search-title"><?php esc_html_e( 'Search', 'statement-collector-theme' ); ?></h2>
		<button class="statement-dialog-close" type="button" data-dialog-close>
			<span aria-hidden="true">&times;</span>
			<span class="screen-reader-text"><?php esc_html_e( 'Close search', 'statement-collector-theme' ); ?></span>
		</button>
	</div>
	<form class="statement-search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="screen-reader-text" for="statement-search-field"><?php esc_html_e( 'Search for:', 'statement-collector-theme' ); ?></label>
		<input
			class="statement-search-form__field"
			id="statement-search-field"
			type="search"
			name="s"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			placeholder="<?php echo esc_attr_x( 'Search', 'search field placeholder', 'statement-collector-theme' ); ?>"
			data-dialog-focus
		>
		<button class="statement-search-form__submit" type="submit"><?php esc_html_e( 'Submit', 'statement-collector-theme' ); ?></button>
	</form>
</dialog>
