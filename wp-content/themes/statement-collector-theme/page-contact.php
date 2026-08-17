<?php
/**
 * Template Name: Contact Statement
 * Description: Clean luxury contact and customer enquiries page for Statement Collector's Piece.
 */

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

get_header();

$admin_email = get_option( 'admin_email' );
if ( ! is_string( $admin_email ) || '' === $admin_email ) {
	$admin_email = 'concierge@mystatement.store';
}
$theme_uri = get_template_directory_uri();
?>
<main id="primary" class="statement-contact-page statement-container--wide">
	<header class="statement-contact-header">
		<span class="statement-eyebrow"><?php esc_html_e( 'DIRECT ENQUIRIES', 'statement-collector-theme' ); ?></span>
		<h1 class="statement-contact-header__title"><?php esc_html_e( 'CONTACT', 'statement-collector-theme' ); ?></h1>
		<p class="statement-contact-header__lead">
			<?php esc_html_e( 'For product information, sizing guidance, order enquiries, or private access requests.', 'statement-collector-theme' ); ?>
		</p>
	</header>

	<div class="statement-contact-grid">
		<div class="statement-contact-cards">
			<!-- Channel 1: Direct Email -->
			<div class="statement-contact-card">
				<span class="statement-eyebrow"><?php esc_html_e( 'ELECTRONIC MAIL', 'statement-collector-theme' ); ?></span>
				<h2 class="statement-contact-card__title"><?php esc_html_e( 'Client Services', 'statement-collector-theme' ); ?></h2>
				<p class="statement-contact-card__detail">
					<a href="<?php echo esc_url( 'mailto:' . $admin_email ); ?>" class="statement-contact-link">
						<?php echo esc_html( $admin_email ); ?>
					</a>
				</p>
				<p class="statement-contact-card__subtext">
					<?php esc_html_e( 'Responses within 24 business hours.', 'statement-collector-theme' ); ?>
				</p>
			</div>

			<!-- Channel 2: Social / Visual Archive -->
			<div class="statement-contact-card">
				<span class="statement-eyebrow"><?php esc_html_e( 'INSTAGRAM / ARCHIVE', 'statement-collector-theme' ); ?></span>
				<h2 class="statement-contact-card__title"><?php esc_html_e( '@statement.au', 'statement-collector-theme' ); ?></h2>
				<p class="statement-contact-card__detail">
					<a href="https://instagram.com/statement.au" target="_blank" rel="noopener noreferrer" class="statement-contact-link">
						instagram.com/statement.au &rarr;
					</a>
				</p>
				<p class="statement-contact-card__subtext">
					<?php esc_html_e( 'Campaign releases, material studies, and announcements.', 'statement-collector-theme' ); ?>
				</p>
			</div>

			<!-- Channel 3: Operating Hours & Provenance -->
			<div class="statement-contact-card">
				<span class="statement-eyebrow"><?php esc_html_e( 'STUDIO HOURS', 'statement-collector-theme' ); ?></span>
				<h2 class="statement-contact-card__title"><?php esc_html_e( 'Operating Times', 'statement-collector-theme' ); ?></h2>
				<p class="statement-contact-card__detail">
					<?php esc_html_e( 'Monday &ndash; Friday', 'statement-collector-theme' ); ?><br>
					<?php esc_html_e( '9:00 AM &ndash; 5:00 PM AEST', 'statement-collector-theme' ); ?>
				</p>
				<p class="statement-contact-card__subtext">
					<?php esc_html_e( 'Melbourne / Sydney, Australia', 'statement-collector-theme' ); ?>
				</p>
			</div>
		</div>

		<div class="statement-contact-visual">
			<div class="statement-contact-image-wrap">
				<img src="<?php echo esc_url( $theme_uri . '/assets/images/statement-crafted-not-mass-made-poster.jpg' ); ?>"
					 alt="<?php esc_attr_e( 'Statement Crafted Not Mass Made Brand Identity', 'statement-collector-theme' ); ?>"
					 class="statement-contact-image"
					 loading="lazy" />
			</div>
		</div>
	</div>
</main>
<?php
get_footer();
