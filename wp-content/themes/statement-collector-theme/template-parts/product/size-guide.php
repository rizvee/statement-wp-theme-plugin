<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

?>
<div class="statement-size-guide-wrapper">
	<button type="button"
			class="statement-size-guide-trigger"
			id="statement-size-guide-open"
			aria-haspopup="dialog"
			aria-controls="statement-size-guide-dialog">
		<span><?php esc_html_e( 'SIZE GUIDE', 'statement-collector-theme' ); ?></span>
		<span aria-hidden="true" class="statement-size-guide-trigger__arrow">&nearr;</span>
	</button>

	<dialog id="statement-size-guide-dialog" class="statement-size-guide-dialog" aria-labelledby="statement-size-guide-title">
		<div class="statement-size-guide-dialog__inner">
			<header class="statement-size-guide-dialog__header">
				<h2 id="statement-size-guide-title" class="statement-size-guide-dialog__title"><?php esc_html_e( 'SIZE GUIDE', 'statement-collector-theme' ); ?></h2>
				<button type="button"
						class="statement-size-guide-dialog__close"
						id="statement-size-guide-close"
						aria-label="<?php esc_attr_e( 'Close size guide', 'statement-collector-theme' ); ?>">
					&times;
				</button>
			</header>

			<div class="statement-size-guide-dialog__body">
				<p class="statement-size-guide-dialog__subtitle"><?php esc_html_e( 'Garment measurements in centimeters (CM). Taken flat.', 'statement-collector-theme' ); ?></p>

				<table class="statement-size-guide-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'SIZE', 'statement-collector-theme' ); ?></th>
							<th scope="col"><?php esc_html_e( 'CHEST', 'statement-collector-theme' ); ?></th>
							<th scope="col"><?php esc_html_e( 'LENGTH', 'statement-collector-theme' ); ?></th>
							<th scope="col"><?php esc_html_e( 'SLEEVE', 'statement-collector-theme' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><strong>S</strong></td>
							<td>56 cm</td>
							<td>67 cm</td>
							<td>64 cm</td>
						</tr>
						<tr>
							<td><strong>M</strong></td>
							<td>59 cm</td>
							<td>69 cm</td>
							<td>65.5 cm</td>
						</tr>
						<tr>
							<td><strong>L</strong></td>
							<td>62 cm</td>
							<td>71 cm</td>
							<td>67 cm</td>
						</tr>
						<tr>
							<td><strong>XL</strong></td>
							<td>65 cm</td>
							<td>73 cm</td>
							<td>68.5 cm</td>
						</tr>
					</tbody>
				</table>

				<p class="statement-size-guide-dialog__note">
					<?php esc_html_e( 'Designed for a structured silhouette with a relaxed drape. If between sizes, select your standard size.', 'statement-collector-theme' ); ?>
				</p>
			</div>
		</div>
	</dialog>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	var openBtn = document.getElementById('statement-size-guide-open');
	var dialog  = document.getElementById('statement-size-guide-dialog');
	var closeBtn= document.getElementById('statement-size-guide-close');

	if (!openBtn || !dialog) return;

	openBtn.addEventListener('click', function() {
		if (typeof dialog.showModal === 'function') {
			dialog.showModal();
		} else {
			dialog.setAttribute('open', 'true');
		}
	});

	if (closeBtn) {
		closeBtn.addEventListener('click', function() {
			if (typeof dialog.close === 'function') {
				dialog.close();
			} else {
				dialog.removeAttribute('open');
			}
		});
	}

	dialog.addEventListener('click', function(e) {
		if (e.target === dialog) {
			if (typeof dialog.close === 'function') {
				dialog.close();
			} else {
				dialog.removeAttribute('open');
			}
		}
	});
});
</script>
