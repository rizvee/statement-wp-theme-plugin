<?php
/**
 * Template Name: Contact Statement
 * Description: Luxury typographic concierge interface for Statement Collector's Piece (Text Only).
 */

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

get_header();

$contact_email = 'info@mystatement.store';
$facebook_url  = get_facebook_url();
?>
<main id="primary" class="statement-contact-document statement-container--editorial">
	<!-- Concierge Header -->
	<header class="statement-contact-document__header">
		<div class="statement-contact-document__meta">
			<span class="statement-meta-code"><?php esc_html_e( 'STATEMENT / CLIENT CONCIERGE', 'statement-collector-theme' ); ?></span>
			<span class="statement-meta-code"><?php esc_html_e( 'DIRECT COMMUNICATIONS', 'statement-collector-theme' ); ?></span>
		</div>
		<h1 class="statement-contact-document__title"><?php esc_html_e( 'CONTACT', 'statement-collector-theme' ); ?></h1>
	</header>

	<div class="statement-drop-divider"></div>

	<!-- Primary Email Hero -->
	<section class="statement-contact-hero-block">
		<span class="statement-meta-code"><?php esc_html_e( 'PRIMARY CORRESPONDENCE', 'statement-collector-theme' ); ?></span>
		<div class="statement-contact-hero-address">
			<a href="<?php echo esc_url( 'mailto:' . $contact_email ); ?>" class="statement-contact-hero-email">
				<?php echo esc_html( $contact_email ); ?>
			</a>
		</div>
		<p class="statement-contact-hero-note">
			<?php esc_html_e( 'For product, order and general enquiries, contact us by email.', 'statement-collector-theme' ); ?>
		</p>
	</section>

	<div class="statement-drop-divider"></div>

	<!-- Concierge Details & Social Channels -->
	<div class="statement-contact-channels-grid">
		<div class="statement-contact-channel-card">
			<span class="statement-meta-code"><?php esc_html_e( 'DIGITAL CHANNELS', 'statement-collector-theme' ); ?></span>
			<ul class="statement-channel-list">
				<li class="statement-channel-item">
					<a href="https://instagram.com/statement.au" target="_blank" rel="noopener noreferrer" class="statement-channel-link">
						<span class="statement-channel-title">Instagram</span>
						<span class="statement-channel-handle">@statement.au &rarr;</span>
					</a>
				</li>
				<?php if ( ! empty( $facebook_url ) ) : ?>
					<li class="statement-channel-item">
						<a href="<?php echo esc_url( $facebook_url ); ?>" target="_blank" rel="noopener noreferrer" class="statement-channel-link">
							<span class="statement-channel-title">Facebook</span>
							<span class="statement-channel-handle"><?php esc_html_e( 'Statement Official', 'statement-collector-theme' ); ?> &rarr;</span>
						</a>
					</li>
				<?php endif; ?>
			</ul>
		</div>

		<div class="statement-contact-channel-card">
			<span class="statement-meta-code"><?php esc_html_e( 'DIRECT ENQUIRIES', 'statement-collector-theme' ); ?></span>
			<p class="statement-channel-prose">
				<?php esc_html_e( 'For product specifications, order updates, and general enquiries, please email info@mystatement.store. All communications are reviewed directly.', 'statement-collector-theme' ); ?>
			</p>
		</div>
	</div>

	<div class="statement-drop-divider"></div>

	<!-- Concluding Statement -->
	<footer class="statement-contact-document__footer">
		<p class="statement-contact-footer-note">
			<?php esc_html_e( 'Statement responds to all client communications directly.', 'statement-collector-theme' ); ?>
		</p>
	</footer>
</main>
<?php
get_footer();
