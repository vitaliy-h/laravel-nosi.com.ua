# Claude Code Installation & Setup

## 1. Buy Claude Pro

Open [claude.ai](https://claude.ai) → Settings → Upgrade to Pro ($20/month)

---

## 2. Install Node.js

Download and install Node.js:

[Node.js](https://nodejs.org/)

Check installation:

```bash
node -v
npm -v
```

---

## 3. Install Claude Code

Install Claude Code globally:

```bash
npm install -g @anthropic-ai/claude-code
```

---

## 4. Start Claude Code & Login

Run inside project directory:

```bash
npx @anthropic-ai/claude-code
```

When Claude starts, choose:

```text
1. Claude.ai subscription · claude.ai billing
```

Browser will open — login with your Claude.ai account.

---

## 5. Project AI Instructions Structure

Claude automatically reads these files on every session:

```
.claude/
├── skills/
│   ├── make-api/           # skill: generating API layer
│   ├── make-filament-resource/  # skill: generating Filament resources
│   └── make-model/         # skill: generating models
├── backend.md              # backend conventions (routing, actions, DTOs)
├── frontend.md             # frontend stack (Vite, Tailwind, Blade)
└── testing.md              # testing guide (PHPUnit, Sail, Filament tests)

CLAUDE.md                   # root project summary (read first)
```

### What each file covers

**`CLAUDE.md`** — project overview, stack, entry point for Claude

**`.claude/backend.md`** — routing conventions, API layer (Actions/Controllers/DTOs/Resources), code style (`pint`)

**`.claude/frontend.md`** — Vite 8, Tailwind CSS 4, Blade components, `t()` helper, asset references

**`.claude/testing.md`** — PHPUnit 12, Sail, Filament resource tests, factories, seeders, auth patterns

**`.claude/skills/`** — step-by-step generation guides for common tasks

---

## 6. First Project Analysis

Inside Claude Code run:

```text
Analyze this Laravel Filament project
```

Claude will:

* analyze project structure
* detect architecture
* inspect Filament Resources
* understand project conventions from `.claude/`

---

## 7. Recommended Workflow

1. Start Claude Code in project root
2. Claude reads `.claude/` automatically
3. Use skills for generation tasks:
    - `make-model` → migration + model + factory
    - `make-api` → DTO + Action + Controller + Resource
    - `make-filament-resource` → Filament Resource + tests

Recommended generation flow:

* migration
* seeder
* model + factory
* DTO
* Action
* Controller + Resource
* Filament Resource (`form()`, `table()`, filters, actions, bulk actions)
* PHPUnit tests (Feature + Unit)

---

## Switching from API Key to Claude.ai Subscription

If you previously used an API key, logout first:

```bash
npx @anthropic-ai/claude-code logout
```

Then start Claude Code again and choose option 1 (Claude.ai subscription).
