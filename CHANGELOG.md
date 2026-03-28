# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - 2026-03-28

### Added
- PopupCampaign entity with full CRUD admin panel under Marketing menu
- 4 trigger types: exit intent, time on page, scroll depth, cart abandonment
- 2 popup styles: centered modal with overlay, fixed bottom bar
- Full design control: background, text, and button colors via admin color pickers
- Discount code display with one-click copy (Clipboard API)
- Email capture with rate-limited API endpoint (3 req/IP/hour)
- `sylius_popup.email_captured` event dispatch for 3rd-party integrations
- Show-once logic via localStorage (per session / per 24h / per 7 days)
- Page targeting: all pages, product pages, cart, checkout
- Audience targeting: everyone, guests only, logged-in only
- Priority-based popup ordering when multiple campaigns match
- Stimulus controller (pure vanilla JS, no external libraries)
- Twig hooks for automatic shop layout injection
- Admin tabbed form (Content/Design/Targeting) matching Sylius conventions
- Sylius grid with filters, sorting, and enabled toggle
- Translations: English and French
- Unit tests for PopupRenderer and PopupSubscribeController
- GitHub Actions CI with PHPUnit and PHPStan (level 5)

[Unreleased]: https://github.com/abderrahimghazali/sylius-popup-plugin/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/abderrahimghazali/sylius-popup-plugin/releases/tag/v2.0.0
