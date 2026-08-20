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
				<h2 id="statement-size-guide-title" class="statement-size-guide-dialog__title"><?php esc_html_e( 'BODY SIZE GUIDE', 'statement-collector-theme' ); ?></h2>
				<button type="button"
						class="statement-size-guide-dialog__close"
						id="statement-size-guide-close"
						aria-label="<?php esc_attr_e( 'Close size guide', 'statement-collector-theme' ); ?>">
					&times;
				</button>
			</header>

			<div class="statement-size-guide-dialog__body">
				<p class="statement-size-guide-dialog__subtitle"><?php esc_html_e( 'Body measurements are provided as a general fit guide. Garment measurements may vary by piece. Measurements in centimeters (CM).', 'statement-collector-theme' ); ?></p>

				<table class="statement-size-guide-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'SIZE', 'statement-collector-theme' ); ?></th>
							<th scope="col"><?php esc_html_e( 'CHEST (CM)', 'statement-collector-theme' ); ?></th>
							<th scope="col"><?php esc_html_e( 'WAIST (CM)', 'statement-collector-theme' ); ?></th>
							<th scope="col"><?php esc_html_e( 'HEIGHT (CM)', 'statement-collector-theme' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><strong>S</strong></td>
							<td>88 &ndash; 96 cm</td>
							<td>74 &ndash; 82 cm</td>
							<td>165 &ndash; 175 cm</td>
						</tr>
						<tr>
							<td><strong>M</strong></td>
							<td>96 &ndash; 104 cm</td>
							<td>82 &ndash; 90 cm</td>
							<td>172 &ndash; 182 cm</td>
						</tr>
						<tr>
							<td><strong>L</strong></td>
							<td>104 &ndash; 112 cm</td>
							<td>90 &ndash; 98 cm</td>
							<td>178 &ndash; 188 cm</td>
						</tr>
						<tr>
							<td><strong>XL</strong></td>
							<td>112 &ndash; 120 cm</td>
							<td>98 &ndash; 106 cm</td>
							<td>184 &ndash; 194 cm</td>
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
