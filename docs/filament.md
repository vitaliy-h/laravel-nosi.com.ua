# Admin Modules Conventions (Filament v5)

This document defines the **default UI/UX conventions for all Filament admin modules** in this starter project.

The goal is to keep all admin resources **consistent, minimal, and predictable**.

---

# 🧭 General Principles

All Filament modules MUST follow these rules unless explicitly overridden.

---

# 🍞 Breadcrumbs

## ❌ Disabled globally

* Breadcrumbs are NOT used in admin panel modules
* Navigation relies on sidebar + page context only

---

# 🧾 Page Titles (H1)

## ❌ No H1 titles in pages

* Filament default page headers (H1) are disabled or hidden
* UI should rely on:

    * sidebar navigation
    * table headings
    * modal titles

---

# 📊 Table Layout (Default Pattern)

All CRUD modules MUST use a table-first approach.

---

## ➕ Create Button

* Always placed **above the table (top-right or top section)**
* Visible by default (not hidden in menus)

Example behavior:

```
[ + Create ]                         (Table)
```

---

## ✏️ Row Actions Layout

Each row in tables MUST follow this rule:

### Actions order:

* ✏️ Edit → LEFT side of actions group
* 🗑 Delete → RIGHT side of actions group
* 👁 View → NOT USED (disabled globally)

---

### ❌ View Action

* View pages are NOT used in admin modules
* All editing is done via modal or edit form

---

# 🪟 Editing Behavior (IMPORTANT)

## Modal-based editing (default)

All modules SHOULD use **modal editing instead of separate edit pages**.

---

## Edit Modal Requirements

When opening edit form:

* Opens in modal popup
* Contains full edit form
* Has two buttons:

### 💾 Buttons:

1. **Save**

    * saves changes
    * keeps modal open (optional per module)

2. **Save & Close**

    * saves changes
    * closes modal

---

## UX Flow

```
Table → Edit button → Modal opens → Save / Save & Close
```

---

# 🧩 CRUD Standard Pattern

Each module MUST follow this structure:

## 1. Table (index view)

* list records
* create button
* edit/delete actions

## 2. Modal form

* used for create & edit
* no separate pages

## 3. No View page

* intentionally removed

---

# ⚙️ Consistency Rules

All admin modules MUST:

* Use same button placement
* Use same action order
* Avoid additional UI complexity unless required
* Keep interaction inside tables + modals

---

# 🚫 Forbidden Patterns

The following MUST NOT be used by default:

* Breadcrumb navigation
* H1 page titles
* Separate edit pages
* View-only pages
* Actions placed inconsistently per module

---

# 📌 Optional Overrides

Some modules MAY override defaults if needed:

* complex relational editing
* nested resources
* large forms (non-modal allowed)

---

# 📎 Related Docs

* [General README](./README.md)
* [Translations Guide](./translations.md)
* [Filament Setup](./filament.md)
* [local Setup](./local_setup.md)

---

# 🚀 Summary

This convention ensures that all admin modules in Filament v5 are:

* consistent
* fast to use
* minimal in UI complexity
* optimized for CRUD workflows
