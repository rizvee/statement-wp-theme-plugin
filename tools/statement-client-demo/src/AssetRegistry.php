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
			// Campaign Hero Assets
			'monogram_hero_arch' => array(
				'file'    => 'statement-hero-slide-monogram-arch.jpg',
				'title'   => 'Statement Monogram Jacquard Jacket Architectural Campaign',
				'alt'     => 'Model wearing the Statement Monogram Jacquard Jacket standing under dramatic archway architecture',
				'caption' => '',
				'role'    => 'hero_slide',
			),
			'monogram_hero_golden' => array(
				'file'    => 'statement-hero-slide-monogram-golden.jpg',
				'title'   => 'Statement Monogram Jacquard Jacket Golden Hour Campaign',
				'alt'     => 'Model in Statement Monogram Jacquard Jacket bathed in warm golden light in architectural setting',
				'caption' => '',
				'role'    => 'hero_slide',
			),
			'hood_hero_arch' => array(
				'file'    => 'statement-hero-slide-hood-arch.jpg',
				'title'   => 'Statement Panelled Hood Jacket Cathedral Arch Campaign',
				'alt'     => 'Model wearing the white Statement Panelled Hood Jacket against dark stone cathedral architecture',
				'caption' => '',
				'role'    => 'hero_slide',
			),
			'monogram_mobile_video' => array(
				'file'    => 'statement-hero-mobile-monogram.mp4',
				'title'   => 'Statement Monogram Jacket Mobile Campaign Video',
				'alt'     => 'Portrait campaign video of model presenting the Statement Monogram Jacquard Jacket',
				'caption' => '',
				'role'    => 'hero_video',
			),
			'brand_logo' => array(
				'file'    => 'statement-logo.png',
				'title'   => 'Statement Brand Logo',
				'alt'     => 'Statement Brand Insignia Logo',
				'caption' => '',
				'role'    => 'brand_logo',
			),

			// Product 01: Monogram Jacquard Jacket
			'monogram_front' => array(
				'file'    => 'statement-monogram-jacket-model-front.webp',
				'title'   => 'Statement Monogram Jacquard Jacket — Model Front',
				'alt'     => 'Male model wearing the Statement Monogram Jacquard Jacket in studio standing pose',
				'caption' => '',
				'role'    => 'product_01_primary',
			),
			'monogram_product_front' => array(
				'file'    => 'statement-monogram-jacket-product-front.webp',
				'title'   => 'Statement Monogram Jacquard Jacket — Product Front Study',
				'alt'     => 'Studio front shot of the Statement Monogram Jacquard Jacket showing cut and pattern',
				'caption' => '',
				'role'    => 'product_01_gallery',
			),
			'monogram_side' => array(
				'file'    => 'statement-monogram-jacket-model-side.webp',
				'title'   => 'Statement Monogram Jacquard Jacket — Model Side Profile',
				'alt'     => 'Side three-quarter view of male model in Statement Monogram Jacquard Jacket',
				'caption' => '',
				'role'    => 'product_01_gallery',
			),
			'monogram_back' => array(
				'file'    => 'statement-monogram-jacket-model-back.webp',
				'title'   => 'Statement Monogram Jacquard Jacket — Model Rear',
				'alt'     => 'Rear view of male model wearing the Statement Monogram Jacquard Jacket',
				'caption' => '',
				'role'    => 'product_01_gallery',
			),
			'monogram_detail' => array(
				'file'    => 'statement-monogram-jacket-product-front-02.webp',
				'title'   => 'Statement Monogram Jacquard Jacket — Weave Detail',
				'alt'     => 'Detailed close-up of the custom woven jacquard monogram textile and structural stitching',
				'caption' => '',
				'role'    => 'product_01_gallery',
			),

			// Product 02: Panelled Hood Jacket (Corrected metadata — white hooded jacket)
			'hood_front' => array(
				'file'    => 'statement-panelled-hood-jacket-model-front.webp',
				'title'   => 'Statement Panelled Hood Jacket — Model Front',
				'alt'     => 'Model wearing the white Statement Panelled Hood Jacket with contrast sleeves in studio standing pose',
				'caption' => '',
				'role'    => 'product_02_primary',
			),
			'hood_product_front' => array(
				'file'    => 'statement-panelled-hood-jacket-product-front.webp',
				'title'   => 'Statement Panelled Hood Jacket — Product Front View',
				'alt'     => 'Studio front view of the white Statement Panelled Hood Jacket showing hood structure and sleeve panel construction',
				'caption' => '',
				'role'    => 'product_02_gallery',
			),
			'hood_side' => array(
				'file'    => 'statement-panelled-hood-jacket-model-side.webp',
				'title'   => 'Statement Panelled Hood Jacket — Model Side Profile',
				'alt'     => 'Side view of model wearing white Statement Panelled Hood Jacket with hood drape',
				'caption' => '',
				'role'    => 'product_02_gallery',
			),
			'hood_detail_front' => array(
				'file'    => 'statement-panelled-hood-jacket-product-front-02.webp',
				'title'   => 'Statement Panelled Hood Jacket — Front Angle View',
				'alt'     => 'Angled studio photograph of the white Statement Panelled Hood Jacket',
				'caption' => '',
				'role'    => 'product_02_gallery',
			),
			'hood_branding' => array(
				'file'    => 'statement-panelled-hood-jacket-branding-detail.webp',
				'title'   => 'Statement Panelled Hood Jacket — Insignia Embroidery Detail',
				'alt'     => 'Macro close-up of the Statement insignia embroidery and precision stitching on the Panelled Hood Jacket',
				'caption' => '',
				'role'    => 'product_02_gallery',
			),
			'hood_highres' => array(
				'file'    => 'statement-panelled-hood-jacket-product-front-04.webp',
				'title'   => 'Statement Panelled Hood Jacket — High-Resolution Front View',
				'alt'     => 'High-resolution full front view of the Statement Panelled Hood Jacket',
				'caption' => '',
				'role'    => 'product_02_gallery',
			),

			// Brand Heritage Assets
			'leather_patch' => array(
				'file'    => 'statement-brand-leather-patch.jpg',
				'title'   => "Statement Collector's Piece — Stitched Leather Label",
				'alt'     => "Stitched cream and black leather label inscribed Statement Collector's Piece Designed in Victoria",
				'caption' => '',
				'role'    => 'brand_object',
			),
			'dust_bag' => array(
				'file'    => 'statement-collector-dust-bag.jpg',
				'title'   => "Statement Canvas Dust Bag — Crafted, Not Mass Made",
				'alt'     => "Statement natural canvas garment dust bag printed with Crafted Not Mass Made and Collector's Piece mark",
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

			// Creative Genesis & Campaign Studio Assets
			'studio_duo_front' => array(
				'file'    => 'statement-black-nwhite-hoodie-n-jacket-product-front.webp',
				'title'   => 'Statement Drop 001 Creative Genesis & Studio Concept',
				'alt'     => 'Overhead studio composition of Statement garments presented on white surface',
				'caption' => '',
				'role'    => 'hero_slide',
			),
			'studio_seated_standing' => array(
				'file'    => 'statement-black-nwhite-hoodie-n-jacket-product-front-02.webp',
				'title'   => 'Statement Monogram Jacquard Jacket & Panelled Hood Duo Editorial',
				'alt'     => 'Studio portrait of two figures wearing the Monogram Jacquard Jacket and Panelled Hood',
				'caption' => '',
				'role'    => 'hero_slide',
			),
			'studio_tension_hold' => array(
				'file'    => 'statement-black-nwhite-hoodie-n-jacket-product-front-03.webp',
				'title'   => 'Statement The Object In Focus Relic Tension Study',
				'alt'     => 'Relic tension study featuring the Statement hooded piece held between two figures',
				'caption' => '',
				'role'    => 'hero_slide',
			),
		);
	}
}
