<?php
/**
 * Template Name: Contact Statement
 * Description: Minimalist text-first contact and customer enquiries page for Statement Collector's Piece.
 */

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

get_header();

$contact_email = 'info@mystatement.store';
$facebook_url  = get_facebook_url();
?>
<main id="primary" class="statement-contact-page statement-container--narrow">
	<article class="statement-about-prose">
		<header class="statement-contact-header">
			<span class="statement-eyebrow"><?php esc_html_e( 'DIRECT ENQUIRIES', 'statement-collector-theme' ); ?></span>
			<h1 class="statement-contact-header__title"><?php esc_html_e( 'CONTACT', 'statement-collector-theme' ); ?></h1>
			<p class="statement-contact-header__lead">
				<?php esc_html_e( 'For orders, sizing guidance, press and general enquiries:', 'statement-collector-theme' ); ?>
			</p>
		</header>

		<div class="statement-contact-channels">
			<!-- Channel 1: Direct Email -->
			<div class="statement-contact-channel">
				<span class="statement-contact-channel__label"><?php esc_html_e( 'EMAIL', 'statement-collector-theme' ); ?></span>
				<a href="<?php echo esc_url( 'mailto:' . $contact_email ); ?>" class="statement-contact-channel__link">
					<?php echo esc_html( $contact_email ); ?>
				</a>
			</div>

			<!-- Channel 2: Instagram -->
			<div class="statement-contact-channel">
				<span class="statement-contact-channel__label"><?php esc_html_e( 'INSTAGRAM', 'statement-collector-theme' ); ?></span>
				<a href="https://instagram.com/statement.au" target="_blank" rel="noopener noreferrer" class="statement-contact-channel__link">
					@statement.au &rarr;
				</a>
			</div>

			<!-- Channel 3: Facebook (Configurable) -->
			<?php if ( ! empty( $facebook_url ) ) : ?>
				<div class="statement-contact-channel">
					<span class="statement-contact-channel__label"><?php esc_html_e( 'FACEBOOK', 'statement-collector-theme' ); ?></span>
					<a href="<?php echo esc_url( $facebook_url ); ?>" target="_blank" rel="noopener noreferrer" class="statement-contact-channel__link">
						<?php esc_html_e( 'Statement Official', 'statement-collector-theme' ); ?> &rarr;
					</a>
				</div>
			<?php endif; ?>
		</div>
	</article>
</main>
<?php
get_footer();
