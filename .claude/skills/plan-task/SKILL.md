---
name: plan-task
description: >
  Research a task against the codebase, brainstorm the approach, ask the developer what is unclear,
  then write the task file (research + step-by-step plan) in one of two storage modes —
  `docs/todo/{issue|slug}.md` (in git) or `tasks/{issue|slug}/` (local backlog, not in git);
  everything is written in the language the task was set in, and after the work is done
  the client-facing report is added in the same language.
  Use when the user says "plan task", "research task", "brainstorm task", "task plan", "new task #33",
  "нова задача", "зроби план по задачі", "розбери задачу", "новая задача", "сделай план по задаче",
  "разбери задачу", or invokes /plan-task.
---

Goal: turn a task description into a researched, checkable plan stored in one place — either a single
`docs/todo/{issue|slug}.md` file or a `tasks/{issue|slug}/` folder, depending on the chosen storage mode.

**Never guess.** If unsure — ask the developer. Do not change application code at this stage.

Skill instructions are English; **every generated file — research, plan, safety block and report — is written in
the language the task was set in** (the language of the task description as the developer wrote it:
Ukrainian → Ukrainian, Russian → Russian, English → English). The templates below are in English; translate their
headings, statuses and fixed text into that language. If the language is ambiguous — ask.

---

## Step 0 — Storage mode

The mode is stored in `.claude/plan-task-mode.local.md` (not in git, one developer's choice).

- File exists → read it, work in that mode, do not ask again.
- File missing → ask the developer once (AskUserQuestion), describing both modes:

  **A. `docs/todo` (in git)** — one file `docs/todo/{issue|slug}.md` per task: research, plan and later the report.
  It is committed with the code, so the plan is visible in reviews, on other machines and in the task's history.
  What to do with the file when the task is finished is the developer's call — leave it with `Status: done`
  or delete it in the final commit.

  **B. `tasks/` (local, not in git)** — a folder `tasks/{issue|slug}/` with `research.md`, `plan.md` and later
  `report.md`. `tasks` is in `.gitignore`, so the files never reach the repository. Finished tasks stay on disk
  as a personal backlog — handy for quickly pulling back the context of an old task.

  Then write the answer to `.claude/plan-task-mode.local.md`:

  ```markdown
  # plan-task mode

  mode: docs-todo | tasks
  ```

  Make sure `.claude/plan-task-mode.local.md` is ignored by git (it is already covered by `.gitignore`;
  if not — add it).

- The developer can switch mode at any time ("switch plan-task to tasks mode") → rewrite the file.
  Existing task files are not migrated.

Throughout the rest of this skill, **task file** means:

| Mode | Research | Plan | Report |
|---|---|---|---|
| `docs-todo` | `docs/todo/{issue\|slug}.md`, first half | same file, after research | same file, appended at the end |
| `tasks` | `tasks/{issue\|slug}/research.md` | `tasks/{issue\|slug}/plan.md` | `tasks/{issue\|slug}/report.md` |

## Step 1 — Input

Needed from the user: **issue number** (GitHub), **title** (optional), **task description**.

- No number → ask once. If there is none, use a short kebab-case slug (2-4 words) as the file/folder name.
- The title, if given, goes into the file heading.

## Step 2 — Codebase research

Read the docs first — the solution may already be described:

- `docs/README.md`, `docs/starter/architecture.md`, `docs/starter/modules.md`
- `docs/project/` — **this project's own functionality** (`shop.md`, `news.md`, …): business logic that is
  specific to the project and is not part of the starter. Always check it — a task usually touches something
  already described there.
- topic docs: `docs/api/`, `docs/filament/`, `docs/starter/{cart,routing,letters,sms,translations,images,uuid,logs}.md`
- `.claude/backend.md`, `.claude/frontend.md`, `.claude/testing.md`

Then find everything the task touches: module(s) under `app/Modules/`, models, migrations, Actions, DTOs, ViewModels, Web/Api controllers, Filament resources, routes, configs, seeders, existing tests.

For broad searches delegate to `Explore`. Record files with paths and line numbers.

Detect the environment (it changes the commands in the plan): `compose.yaml` + `vendor/bin/sail` → Sail; otherwise ask (Docker / OpenServer / native PHP). Write commands for what actually exists on this machine.

## Step 3 — Clarifying questions

Ask in one batch (AskUserQuestion), only what actually changes the plan. Always consider:

1. **Migrations.** "Is the project already live, or still in starter-development mode?"
   - starter → edit existing migrations, do not create new ones
   - live → new migrations as usual (exception: a migration not yet committed may be edited)
   - Once the answer is "live", treat the project as live from then on (save to memory).
2. **New module?** If the task creates a module → ask whether it must be switchable off (`config/modules.php` + `ModuleGate`, see `docs/starter/modules.md`).
3. Architecture forks, UX, field names, permissions — anything with more than one reasonable option.

Do not ask what the code or docs already answer.

## Step 4 — Task file

Write the research part, then the plan part (one file or two, per the mode table in Step 0),
**in the task's language**, short and to the point. The report comes later, after the work is done (Step 6).

### Research part

```markdown
# {Number} — {Title}

Status: not started | in progress | done

## Task
{the essence in 2-4 sentences, as the developer phrased it}

## How it works now
{current implementation: data flow, who calls whom}

## Affected files
| File | What is there | Role in the task |
|---|---|---|
| `app/Modules/Shop/Models/Product.php:42` | ... | ... |

## Developer's answers
- Question → answer

## Constraints and risks
{dangerous spots}
```

(in `tasks` mode the same content goes to `research.md`; `Status` lives in `plan.md`)

### Plan part

```markdown
## What changes and how it will work
{plain words, up to 10 lines: which entities/tables appear, which code points are touched,
how the data flows after the change (step by step), what the user will see.
Written for a developer who did not research the task — they must grasp the mechanics before reading the steps.}

## Approach
{2-5 sentences: the architectural decision, patterns used and why}

## Plan

- [ ] 1. {Step} — files: `...`
- [ ] 2. ...
- [ ] N-3. Tests for the new/changed functionality
- [ ] N-2. Document the new functionality: `docs/project/` (project logic) and `docs/api/` (API endpoints), see Step 5
- [ ] N-1. Append the client report (see Step 6)
- [ ] N. (developer's choice) Run the tests and commit
```

In `tasks` mode the plan part is a standalone `plan.md` with its own `# План: {Number} — {Title}` heading and
`Status:` line.

```markdown
## Plan changes
{date — what changed during the work and why}
```

## Step 5 — Plan rules

- **Steps are self-contained blocks, not individual actions.** One step = one meaningful chunk of work that can be done, reviewed and left in a working state on its own (a layer, a feature slice, a subsystem). Inside a step, list the concrete files and what changes in them — as sub-bullets, not as separate steps.
  - Bad: "rename the model", "rename the factory", "update the imports in Product" — three steps for one idea.
  - Good: "Model layer: table + model + factory" with those files listed inside.
  - A typical plan is **5-12 steps**. More than ~15 means actions are being written as steps — merge them. One step per file is a smell.
  - Do not split a step just because it touches many files; split when the *purpose* changes.
- **Executable one step at a time or all at once.** Each step names concrete files.
- **Numbering**: steps are numbered with plain arabic numerals — `1.`, `2.`, `3.` … Never roman numerals, letters or unnumbered bullets.
- **"What changes and how it will work" comes first**, before "Approach". It describes the mechanics (what appears, what is touched, how the flow runs end to end), not the architectural justification — that stays in "Approach". No file lists there, they live in the steps.
- **Track progress**: a finished step becomes `[x]`. If the approach changes mid-work, append to "Plan changes" instead of silently rewriting history.
- **Project architecture**: Actions hold write logic (shared by Web and API), Queries (`app/Modules/{Module}/Queries/`) hold reads — there are no repositories, controllers stay thin, DTOs (`spatie/laravel-data`) for input, Resources for API output, ViewModels for anything a blade renders (`docs/starter/view-models.md`), tests are PHPUnit 13 (not Pest). Unsure where something belongs → ask.
- **Design patterns** only where the task justifies them, never for their own sake. State the reason in "Approach".
- **Dangerous spots** marked inline in the step with `⚠️`:
  - editing existing migrations / dropping columns / data loss
  - renaming or changing signatures used by several modules
  - changes to cache, queues, permissions, routes (broken URLs, SEO)
  - anything that can behave differently on production vs local
- **Tests**: models without a factory get a factory + `HasFactory` first (see `.claude/testing.md`). Add new test files to git.
- **Documentation step** (always in the plan, near the end):
  - project functionality → `docs/project/{module}.md`: new or changed business logic, entities, admin behaviour,
    what the client sees. New module/feature with no page yet → create one and link it from `docs/project/README.md`.
  - API → `docs/api/{module}.md`: every new or changed endpoint (route, method, auth, request fields, response shape).
  - starter subsystems (`docs/starter/`, `docs/filament/`) → only when the task actually changed the starter itself.
  - Name the concrete files in the step. No filler — document what changed, nothing else.

### Safety block (insert after the plan, translated into the task's language)

```markdown
## Execution safety

- Do not call or send requests to third-party APIs and services without the developer's permission.
- No email, SMS or other notifications may leave the local machine — watch which jobs run and what code executes (check `MAIL_MAILER`, the SMS driver, `QUEUE_CONNECTION`).
- Do not run `migrate:fresh` / `migrate:refresh` without explicit permission.
- Destructive commands and seeders on real data — only after confirmation.
```

## Step 6 — Task reply (report section)

Written **after the plan is executed**, not during planning. Set the status to done.

- `docs-todo` mode → appended as the last section of `docs/todo/{issue|slug}.md`.
  When the task is fully handed over, the developer decides whether the file stays with `Status: done`
  or is deleted in the final commit — ask them if they have not said.
- `tasks` mode → a separate `tasks/{issue|slug}/report.md`.

**Language: the same as the rest of the files** — the language of the original task description.

This text is for the client, not for a developer. Keep it plain:

- what was done, in the client's terms — features and behaviour, not class names, file paths or diffs
- what changed for the user visibly (new page, new button, URL change, admin section)
- how to check it, only if there is something to click: short numbered steps
- anything the client must know or decide (broken old links, needed re-import, actions on production)

Do not: list files, paste code, describe refactoring that changes nothing for the user, or explain internal architecture.

Length: 5-15 lines. If the task was purely internal (rename, refactor, tests) — say plainly that behaviour has not changed and name the one or two things worth a glance.

```markdown
## Report

{1-3 sentences: what was done}

### What changed
- ...

### How to check
1. ...
```

(translate the headings into the task's language; in `tasks` mode `## Report` becomes the `#` heading of `report.md`)

## Step 7 — Commit (last plan step)

Message format:

- with issue number: `#33: stop_cart able to turn off`
- without: `feature: admin dashboard with period filter and stats widgets`

English, lowercase, short. Commit only when the developer asks.
In `tasks` mode the task files are outside git — nothing to commit from `tasks/`.

## Step 8 — Reply to the developer

Report: the path(s) of the task file(s), the approach in 2-3 lines, the list of steps, any open questions. Do not restate the files in full.
