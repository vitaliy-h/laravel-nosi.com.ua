# Claude Code Installation & Setup

## 1. Buy Claude Pro

Open [claude.ai](https://claude.ai) → Settings → Upgrade to Pro ($20/month)

If you previously used an API key, logout first:

```bash
npx @anthropic-ai/claude-code logout
```

---

## 2. Choose How to Run Claude Code

There are two ways to work with Claude Code. Pick one:

---

### Option A — Windows Desktop App (recommended)

Download and install the Claude Code desktop app for Windows:

[Claude Code for Windows](https://claude.ai/download)

After installation:

1. Open the app
2. Sign in with your Claude.ai account
3. Open your project folder via **File → Open Folder**

No Node.js or terminal setup required.

---

### Option B — Console (CLI)

#### Install Node.js

Download and install Node.js:

[Node.js](https://nodejs.org/)

Check installation:

```bash
node -v
npm -v
```

#### Install Claude Code

Install Claude Code globally:

```bash
npm install -g @anthropic-ai/claude-code
```

#### Start Claude Code & Login

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

## 3. Project AI Instructions Structure

Claude automatically reads these files on every session:

```
.claude/
├── skills/
│   ├── make-api/                # skill: generating the API layer
│   ├── make-filament-resource/  # skill: generating Filament resources
│   ├── make-model/              # skill: generating models + migrations
│   └── plan-task/               # skill: researching a task and writing its plan
├── backend.md              # backend conventions (routing, layers, code style)
├── frontend.md             # frontend stack (Bootstrap 5 theme, Blade)
└── testing.md              # testing guide (PHPUnit, Sail, Filament tests)

CLAUDE.md                   # entry point (read first)
```

### What each file covers

**`CLAUDE.md`** — the entry point: links to the three role guides, a map of `docs/`, and
the few project-wide rules. It is short by design — the substance lives in the files it
points at.

**`.claude/backend.md`** — routing conventions, the read layer (`Queries/`), API layer
(Actions/Controllers/DTOs/Resources), ViewModels, Action return values, code style (`pint`)

**`.claude/frontend.md`** — the frontend stack (Bootstrap 5 demo theme; Vite + Tailwind are
configured but not wired into the layout), Blade structure rules, `t()` helper

**`.claude/testing.md`** — PHPUnit 13, Sail, Filament resource tests, factories, seeders,
module skipping, auth patterns

**`.claude/skills/`** — step-by-step generation guides for common tasks

---

## 4. First Project Analysis

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

## 5. Recommended Workflow

1. Start Claude Code in project root
2. Claude reads `.claude/` automatically
3. Use skills for generation tasks:
    - `make-model` → migration + model (+ module scaffolding when it's a new module)
    - `make-api` → FormRequest + Query/Action + Controller + Resource + route
    - `make-filament-resource` → Filament resource, navigation item and translations
      (it does not write tests — see `.claude/testing.md` for those)
    - `plan-task` → research + plan in `docs/todo/{id}.md` before a larger change

Recommended generation flow:

* migration
* seeder
* model + factory
* DTO
* Action
* Controller + Resource
* Filament Resource (`form()`, `table()`, filters, actions, bulk actions)
* PHPUnit tests (Feature + Unit)
