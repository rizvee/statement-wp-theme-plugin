<?php

namespace Statement\Collector\Theme;

use Statement\Collector\Core\Marketing\SignupService;

defined( 'ABSPATH' ) || exit;

$status     = isset( $_GET['signup_status'] ) ? sanitize_key( $_GET['signup_status'] ) : '';
$is_granted = isset( $_GET['access_granted'] );

$heading  = __( 'PRIVATE ACCESS', 'statement-collector-theme' );
$subtext  = __( 'Be first into the next release.', 'statement-collector-theme' );
$btn_text = __( 'JOIN ACCESS', 'statement-collector-theme' );
?>
<section class="statement-home-signup statement-container" aria-labelledby="statement-signup-title">
	<div class="statement-home-signup__inner">
		<h2 id="statement-signup-title" class="statement-home-signup__title"><?php echo esc_html( $heading ); ?></h2>
		<p class="statement-home-signup__subtext"><?php echo esc_html( $subtext ); ?></p>

		<?php if ( 'success' === $status ) : ?>
			<div class="statement-home-signup__notice statement-home-signup__notice--success" role="status">
				<?php esc_html_e( "YOU'RE ON THE LIST.", 'statement-collector-theme' ); ?>
			</div>
		<?php elseif ( $is_granted ) : ?>
			<div class="statement-home-signup__notice statement-home-signup__notice--success" role="status">
				<?php esc_html_e( 'ACCESS GRANTED.', 'statement-collector-theme' ); ?>
			</div>
		<?php else : ?>
			<?php if ( 'invalid_email' === $status ) : ?>
				<div class="statement-home-signup__notice statement-home-signup__notice--error" role="alert">
					<?php esc_html_e( 'Please enter a valid email address.', 'statement-collector-theme' ); ?>
				</div>
			<?php elseif ( 'rate_limited' === $status ) : ?>
				<div class="statement-home-signup__notice statement-home-signup__notice--error" role="alert">
					<?php esc_html_e( 'Too many attempts. Please try again shortly.', 'statement-collector-theme' ); ?>
				</div>
			<?php endif; ?>

			<form class="statement-home-signup__form" method="post" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="hidden" name="statement_signup_submit" value="1">
				<?php
				if ( class_exists( SignupService::class ) ) {
					wp_nonce_field( SignupService::NONCE_ACTION, 'statement_signup_nonce' );
				} else {
					wp_nonce_field( 'statement_signup_action', 'statement_signup_nonce' );
				}
				?>

				<div class="statement-home-signup__fields">
					<label for="statement-home-email" class="screen-reader-text">
						<?php esc_html_e( 'Email Address', 'statement-collector-theme' ); ?>
					</label>
					<input
						type="email"
						id="statement-home-email"
						name="statement_email"
						class="statement-home-signup__input"
						placeholder="<?php esc_attr_e( 'EMAIL ADDRESS', 'statement-collector-theme' ); ?>"
						required
						autocomplete="email"
					>
					<button type="submit" class="statement-home-signup__button">
						<?php echo esc_html( $btn_text ); ?>
					</button>
				</div>
				<p class="statement-home-signup__consent">
					<?php esc_html_e( 'By entering your email, you consent to receive release communications. Unsubscribe at any time.', 'statement-collector-theme' ); ?>
				</p>
			</form>
		<?php endif; ?>
	</div>
</section>
