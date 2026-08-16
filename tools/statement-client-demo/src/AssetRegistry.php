<?php

namespace Statement\ClientDemo;

defined( 'ABSPATH' ) || exit;

final class AssetRegistry {

	/**
	 * Canonical dictionary of all assets to import.
	 *
	 * @return array<string, array{file: string, title: string, alt: string, caption: string, role: string}>
	 */
	public static function get_assets(): array {
		return array(
			'monogram_front' => array(
				'file'    => 'statement-monogram-jacket-front.jpg',
				'title'   => 'Statement Monogram Jacquard Jacket — Model Front',
				'alt'     => 'Male model wearing the Statement Monogram Jacquard Jacket in studio standing pose',
				'caption' => '',
				'role'    => 'product_01_primary',
			),
			'monogram_back' => array(
				'file'    => 'statement-monogram-jacket-back.jpg',
				'title'   => 'Statement Monogram Jacquard Jacket — Model Rear',
				'alt'     => 'Rear view of male model wearing the Statement Monogram Jacquard Jacket',
				'caption' => '',
				'role'    => 'product_01_gallery',
			),
			'monogram_concrete' => array(
				'file'    => 'statement-monogram-jacket-flatlay-concrete.jpg',
				'title'   => 'Statement Monogram Jacquard Jacket — Concrete Flat Lay',
				'alt'     => 'Top-down flat lay of the Statement Monogram Jacquard Jacket on textured stone',
				'caption' => '',
				'role'    => 'product_01_gallery',
			),
			'monogram_collar' => array(
				'file'    => 'statement-monogram-jacket-collar-detail.jpg',
				'title'   => 'Statement Monogram Jacquard Jacket — Collar & Label',
				'alt'     => 'Close-up of the Statement Monogram Jacquard Jacket woven collar label and jacquard weave',
				'caption' => '',
				'role'    => 'product_01_gallery',
			),
			'monogram_slate' => array(
				'file'    => 'statement-monogram-jacket-flatlay-slate.jpg',
				'title'   => 'Statement Monogram Jacquard Jacket — Slate Flat Lay',
				'alt'     => 'Statement Monogram Jacquard Jacket placed on dark slate platform',
				'caption' => '',
				'role'    => 'product_01_gallery',
			),
			'hood_front' => array(
				'file'    => 'statement-panelled-hood-jacket-front.jpg',
				'title'   => 'Statement Panelled Hood Jacket — Model Front',
				'alt'     => 'Model wearing the white Statement Panelled Hood Jacket with patterned sleeves',
				'caption' => '',
				'role'    => 'product_02_primary',
			),
			'hood_back' => array(
				'file'    => 'statement-panelled-hood-jacket-back.jpg',
				'title'   => 'Statement Panelled Hood Jacket — Rear Architecture',
				'alt'     => 'Rear view of model wearing white hooded jacket with patterned sleeves against cathedral stone',
				'caption' => '',
				'role'    => 'product_02_gallery',
			),
			'hood_cathedral' => array(
				'file'    => 'statement-panelled-hood-jacket-cathedral-front.jpg',
				'title'   => 'Statement Panelled Hood Jacket — Full Body Front',
				'alt'     => 'Full body front view of model in white panelled hooded jacket and matching trousers',
				'caption' => '',
				'role'    => 'product_02_gallery',
			),
			'hood_embroidery' => array(
				'file'    => 'statement-panelled-hood-jacket-embroidery-detail.jpg',
				'title'   => 'Statement Panelled Hood Jacket — Chest Insignia Macro',
				'alt'     => 'Close-up macro of gold and navy embroidered Statement geometric insignia on white hoodie',
				'caption' => '',
				'role'    => 'product_02_gallery',
			),
			'hood_night' => array(
				'file'    => 'statement-panelled-hood-jacket-night-34.jpg',
				'title'   => 'Statement Panelled Hood Jacket — Editorial 3/4',
				'alt'     => 'Three-quarter editorial night shot of model in Statement panelled hooded jacket',
				'caption' => '',
				'role'    => 'product_02_gallery',
			),
			'leather_patch' => array(
				'file'    => 'statement-brand-leather-patch.jpg',
				'title'   => "Statement Collector's Piece — Stitched Leather Label",
				'alt'     => "Stitched cream and black leather label inscribed Statement Collector's Piece Designed in Victoria",
				'caption' => '',
				'role'    => 'brand_object',
			),
			'leather_badge' => array(
				'file'    => 'statement-brand-leather-badge.jpg',
				'title'   => "Statement Collector's Piece — Square Emblem Seal",
				'alt'     => "Embossed square brand seal with Statement geometric emblem and Collector's Piece typography",
				'caption' => '',
				'role'    => 'brand_object',
			),
			'insignia_vector' => array(
				'file'    => 'statement-brand-insignia-vector.jpg',
				'title'   => 'Statement Brand Insignia — Vector Mark',
				'alt'     => 'Statement geometric emblem and Collector Piece wordmark in ink navy on white',
				'caption' => '',
				'role'    => 'brand_mark',
			),
			'insignia_gold' => array(
				'file'    => 'statement-brand-insignia-gold.jpg',
				'title'   => 'Statement Brand Insignia — Gold & Navy Graphic',
				'alt'     => 'Gold and navy Statement geometric emblem with vertical Collector Piece typography',
				'caption' => '',
				'role'    => 'brand_mark',
			),
			'wordmark' => array(
				'file'    => 'statement-brand-wordmark.jpg',
				'title'   => 'Statement Wordmark Logo with Diamond',
				'alt'     => 'Horizontal Statement Collector Piece embossed wordmark logo with diamond accent',
				'caption' => '',
				'role'    => 'brand_mark',
			),
			'dust_bag' => array(
				'file'    => 'statement-collector-dust-bag.jpg',
				'title'   => "Statement Canvas Dust Bag — Crafted, Not Mass Made",
				'alt'     => "Statement natural canvas garment dust bag printed with Crafted Not Mass Made and Collector's Piece mark",
				'caption' => '',
				'role'    => 'brand_object',
			),
			'patch_palm' => array(
				'file'    => 'statement-collector-patch-palm.jpg',
				'title'   => 'Statement Hexagonal Insignia Patch',
				'alt'     => 'Tactile black and bronze rubber hexagonal Statement insignia patch in hand',
				'caption' => '',
				'role'    => 'brand_object',
			),
			'poster' => array(
				'file'    => 'statement-crafted-not-mass-made-poster.jpg',
				'title'   => 'Statement Brand Manifesto Poster',
				'alt'     => 'Minimalist typography poster with Crafted Not Mass Made and Statement brand insignia',
				'caption' => '',
				'role'    => 'brand_manifesto',
			),
		);
	}
}
