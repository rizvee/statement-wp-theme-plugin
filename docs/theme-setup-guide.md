# Statement Collector Theme Setup Guide

A comprehensive guide for fresh installations in production environments.

---

## 1. Prerequisites

- **WordPress**: 6.0+
- **PHP**: 7.4+ (PHP 8.2+ recommended)
- **WooCommerce**: 8.0+ (Active)

---

## 2. Installation Sequence

1. **Install Statement Collector Core Plugin**:
   - Upload `dist/statement-collector-core-0.13.0-rc.12.zip` via **Plugins > Add New > Upload Plugin**.
   - Activate plugin.
2. **Install Statement Collector Theme**:
   - Upload `dist/statement-collector-theme-0.13.0-rc.9.zip` via **Appearance > Themes > Add New > Upload Theme**.
   - Activate theme.
3. **Run Statement Setup Screen**:
   - Navigate to **Appearance > Statement**.
   - Verify that all prerequisite indicators display **PASS**.
   - Click **Verify & Create Missing Standard Pages** to provision `/about/`, `/contact/`, `/drops/`, and `/archive/` templates.
4. **Configure Reading Settings**:
   - Navigate to **Settings > Reading**.
   - Select **A static page**.
   - Homepage: **Statement Home**.
   - Posts page: **Journal**.
5. **Configure Menus**:
   - In **Appearance > Menus**, assign the primary menu (`SHOP`, `DROPS`, `ARCHIVE`, `ABOUT`) to **Primary Navigation**.
6. **Configure Hero Slider**:
   - In **Appearance > Customize > Statement Design Settings > Homepage Hero Slider**, assign campaign imagery and headings to slides 1–4.
