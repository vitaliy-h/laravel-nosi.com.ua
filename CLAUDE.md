# CLAUDE.md

Guidance for Claude Code working in this Laravel + Filament repository.

## Role-specific guides

- Backend: `@.claude/backend.md`
- Frontend: `@.claude/frontend.md`
- Testing: `@.claude/testing.md`

## Project documentation

Read the relevant page before changing a subsystem — these describe how this project
actually works, not generic Laravel:

- `docs/starter/` — architecture and subsystems: `architecture.md`, `modules.md`,
  `routing.md`, `actions.md`, `view-models.md`, `cart.md`, `images.md`, `logs.md`,
  `letters.md`, `sms.md`, `sitemap.md`, `translations.md`, `oauth.md`, `uuid.md`,
  `commands.md`, `testing.md`
- `docs/api/` — the HTTP API per module (`auth`, `profile`, `shop`, `translation`)
- `docs/filament/` — admin panel: resource generation, dashboard, nested sets, translations,
  inline editing in tables
- `docs/project/` — this project's functionality (`shop.md`, `news.md`); later — the project description
- `docs/todo/` — specifications and plans for work not yet done, one file per task
- `docs/README.md` — server requirements, install and local setup (Sail / WSL2 / OpenServer)

When code and a doc disagree, the code wins — fix the doc in the same change.

## Base Rules

- Always add newly created test files to git
- Tests are plain PHPUnit 13 classes — the stack and conventions live in
  `@.claude/testing.md`; follow that file rather than inventing a setup
