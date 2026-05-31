# Claude Code Installation & Setup

## 1. Install Node.js

Download and install Node.js:

[Node.js](https://nodejs.org/?utm_source=chatgpt.com)

Check installation:

```bash
node -v
npm -v
```

---

## 2. Install Claude Code

Install Claude Code globally:

```bash
npm install -g @anthropic-ai/claude-code
```

---

## 3. Create Anthropic API Key

Open Anthropic Console:

[Anthropic Console](https://console.anthropic.com/?utm_source=chatgpt.com)

Steps:

1. Create account or login
2. Open API Keys
3. Create new API key
4. Copy the key

---

## 4. Configure API Key

### Linux / macOS

```bash
export ANTHROPIC_API_KEY=your_key
```

### Windows PowerShell

```powershell
setx ANTHROPIC_API_KEY "your_key"
```

Restart terminal after updating environment variables.

---

## 5. Start Claude Code

Run inside project directory:

```bash
npx @anthropic-ai/claude-code
```

---

## 6. Login Method

When Claude starts choose:

```text
2. Anthropic Console account · API usage billing
```

This mode uses Anthropic API billing.

---

## 7. First Project Analysis

Inside Claude Code run:

```text
Analyze this Laravel Filament project
```

Claude will:

* analyze project structure
* detect architecture
* inspect Filament Resources
* understand project conventions

---

## 8. AI Project Instructions

Project AI instructions are stored in:

```text
CLAUDE.md
.ai/
```

Claude automatically reads these files to understand:

* project architecture
* stack
* coding conventions
* generation rules
* Filament patterns

---

## 9. Recommended AI Structure

```text
.ai/
├── stack.md
├── architecture.md
├── conventions.md
├── filament.md
├── generators.md
├── prompts/
└── examples/
```

---

## 10. Recommended Workflow

1. Analyze project
2. Generate AI documentation
3. Create/update CLAUDE.md
4. Generate small modules first
5. Improve prompts and conventions over time

Recommended generation flow:

* migration
* seeder
* model
* Filament Resource
* form()
* table()
* filters
* actions
* bulk actions
