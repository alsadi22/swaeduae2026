# SwaedUAE — stakeholder readiness snapshot

Short, non-binding view of how far the platform is toward the **1.0** bar in the roadmap (`Documents/PROJECT-ROADMAP.md` when checked out beside this repo). Percentages are directional estimates for planning conversations, not a formal audit.

**Full requirement traceability (four phases):** [`Documents/END-TO-END-FOUR-PHASE-CHECKLIST.md`](../../Documents/END-TO-END-FOUR-PHASE-CHECKLIST.md) — maps each launch criterion to code/tests/ops and lists **explicit deferrals** (e.g. block CMS **C9**, kiosk **F1**, admin 2FA **I5**, certificates **G1**). PHPUnit: `php artisan test --filter=EndToEndFourPhaseChecklist` from `app/`. **Legal / content sign-off:** [`Documents/MINIMUM-CONTENT-SIGNOFF.md`](../../Documents/MINIMUM-CONTENT-SIGNOFF.md).

| Area | Approx. done | Notes |
|------|----------------|--------|
| Public site and content (EN/AR, CMS-driven pages) | ~85% | Core pages, bilingual shell, programs hub, partners strip; polish and edge cases remain |
| Volunteer discovery and applications | ~80% | Listings, filters, saved opportunities, ICS, feeds; deeper engagement analytics optional |
| Organization portal and events | ~75% | CRUD, checkpoints, attendance flows; advanced reporting and ops tooling grow over time |
| Admin and moderation | ~70% | CMS, org approval, key tools; expand as policy and volume require |
| Auth and accounts | ~80% | Login, verification patterns, roles; hardening and UX refinements ongoing |
| Infrastructure and production | Per env | See **docs/POST-DEPLOY-SMOKE.md** and **Documents/PRODUCTION-ENV.md** |

**Deploy:** Server **git pull** then **./scripts/deploy-on-server.sh** (from this app root). **CI:** **.github/workflows/tests.yml** (Pint, tests, Vite build on Node 22).

**Last updated:** 2026-04-04 (regenerate or adjust when major roadmap phases close).
