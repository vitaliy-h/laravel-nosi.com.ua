---
name: plan-task
description: >
  Research a task against the codebase, brainstorm the approach, ask the developer what is unclear,
  then write tasks/{issue|slug}/research.md + plan.md (in Russian) with a step-by-step plan.
  Use when the user says "plan task", "research task", "brainstorm task", "task plan", "new task #33",
  "нова задача", "зроби план по задачі", "розбери задачу", "новая задача", "сделай план по задаче",
  "разбери задачу", or invokes /plan-task.
---

Goal: turn a task description into a researched, checkable plan stored in `tasks/{issue|slug}/`.

**Never guess.** If unsure — ask the developer. Do not change application code at this stage.

Skill instructions are English; **the generated `research.md` and `plan.md` are written in Russian.**

---

## Step 1 — Input

Needed from the user: **issue number** (GitHub), **title** (optional), **task description**.

- No number → ask once. If there is none, use a short kebab-case slug (2-4 words) as the folder name.
- The title, if given, goes into the file headings.

## Step 2 — Codebase research

Read the docs first — the solution may already be described:

- `docs/README.md`, `docs/starter/architecture.md`, `docs/starter/modules.md`
- topic docs: `docs/api/`, `docs/filament/`, `docs/starter/{cart,routing,letters,sms,translations,images,uuid,logs}.md`
- `.claude/backend.md`, `.claude/frontend.md`, `.claude/testing.md`

Then find everything the task touches: module(s) under `app/Modules/`, models, migrations, Actions, DTOs, Web/Api controllers, Filament resources, routes, configs, seeders, existing tests.

For broad searches delegate to `Explore` or `cavecrew-investigator`. Record files with paths and line numbers.

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

## Step 4 — Files

Create `tasks/{issue|slug}/` with two files, **written in Russian**, short and to the point.

### `research.md`

```markdown
# {Номер} — {Название}

## Задача
{суть в 2-4 предложениях, как её сформулировал программист}

## Как сделано сейчас
{текущая реализация: поток данных, кто кого вызывает}

## Затронутые файлы
| Файл | Что там | Роль в задаче |
|---|---|---|
| `app/Modules/Shop/Models/Product.php:42` | ... | ... |

## Ответы программиста
- Вопрос → ответ

## Ограничения и риски
{опасные места}
```

### `plan.md`

```markdown
# План: {Номер} — {Название}

Статус: не начато | в работе | готово

## Подход
{2-5 предложений: архитектурное решение, применяемые паттерны и почему}

## Пункты

- [ ] 1. {Шаг} — файлы: `...`
- [ ] 2. ...
- [ ] N-2. Тесты на новый/изменённый функционал
- [ ] N-1. Проверить docs: нужно ли добавить/обновить (только по делу)
- [ ] N. (на выбор программиста) Прогнать тесты и сделать коммит

## Изменения плана
{дата — что и почему поменяли по ходу работы}
```

## Step 5 — Plan rules

- **Atomic steps**: the plan must be executable one step at a time or all at once. Each step names concrete files.
- **Track progress**: a finished step becomes `[x]`. If the approach changes mid-work, append to "Изменения плана" instead of silently rewriting history.
- **Project architecture**: Actions hold business logic (shared by Web and API), controllers stay thin, DTOs (`spatie/laravel-data`) for input, Resources for output, tests are PHPUnit 12 (not Pest). Unsure where something belongs → ask.
- **Design patterns** only where the task justifies them, never for their own sake. State the reason in "Подход".
- **Dangerous spots** marked inline in the step with `⚠️`:
  - editing existing migrations / dropping columns / data loss
  - renaming or changing signatures used by several modules
  - changes to cache, queues, permissions, routes (broken URLs, SEO)
  - anything that can behave differently on production vs local
- **Tests**: models without a factory get a factory + `HasFactory` first (see `.claude/testing.md`). Add new test files to git.
- **Docs step**: write only what is needed in `docs/`, no filler.

### Safety block (insert into every `plan.md` verbatim)

```markdown
## Безопасность выполнения

- Без разрешения программиста не ходить и не отправлять запросы на сторонние API и сервисы.
- С локалки не должны улетать email, SMS и другие уведомления — следить, какие джобы запускаются и какой код выполняется (проверять `MAIL_MAILER`, драйвер SMS, `QUEUE_CONNECTION`).
- Не запускать `migrate:fresh` / `migrate:refresh` без явного разрешения.
- Деструктивные команды и сидеры на реальных данных — только после подтверждения.
```

## Step 6 — Commit (last plan step)

Message format:

- with issue number: `#33: stop_cart able to turn off`
- without: `feature: admin dashboard with period filter and stats widgets`

English, lowercase, short. Commit only when the developer asks.

## Step 7 — Reply

Report: folder path, the approach in 2-3 lines, the list of steps, any open questions. Do not restate the files in full.
