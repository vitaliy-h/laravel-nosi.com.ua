# Inline editing in admin tables

A starter task (`app/Filament/`): works in the table of any resource.
Status as of 2026-10-05 — not started.

## What we want

In a resource table, a cell shows plain text. **Double-clicking** an editable cell
replaces the text with a field:

- text, number, date — an `input` (of the matching type);
- choosing one of several options (enum, `belongsTo`) — a `select`;
- choosing several (`belongsToMany`, an array in a json column) — a multiselect.

Save on `Enter` or blur (for a select — right after choosing); cancel on `Esc`: the
cell goes back to text without a request. After saving, the cell shows text with the
new value again (in the same format as before editing — badge, date, option label).
Show a validation error next to the field and keep the field open.

A cell that can't be edited does not react to a double-click. Editable cells get a
subtle hint (cursor / dashed underline on hover) so they can be told apart.

## Why the existing columns don't fit

Filament already has `TextInputColumn`, `SelectColumn`, `ToggleColumn` (we use them in
`LanguageResource`, `PageResource`: `rank`, `display`). They show the field
**all the time**: the table turns into a form, the text is hard to read, and formatting
(`badge()`, `formatStateUsing()`, `dateTime()`) is lost. None of them is a
multiselect. `ToggleColumn` stays as is — boolean fields don't need a double-click.


## Where to use it first

Enable it for editing `rank` in all sections, and for product prices in the
products tables of categories and brands.
In all sections — for the name, title and URL, if they are shown in the table.
