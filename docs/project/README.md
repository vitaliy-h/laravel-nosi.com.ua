# Project

Notes that belong to **this** project rather than to the starter itself: what is planned,
what was audited, what is specific to the client's shop. Anything describing the starter's
own subsystems lives in [`docs/starter/`](../starter) instead.

---

## Contents

| Document | What it is | Status |
|---|---|---|
| [product_attributes.md](product_attributes.md) | Dynamic product attributes: manager-defined characteristics per category, and catalog filters built from them | Specification — not implemented |
| [product_items.md](product_items.md) | Product variants (`shop_product_items`): sizes/colours/bundles, and how carts, orders and queries have to change | Specification — not implemented |
| [docs_audit.md](docs_audit.md) | The running audit of documentation and architecture against the code (issue #50) | Working document |

---

## Conventions for this folder

- A document that describes work **not yet in the code** opens with a
  `**Статус: специфікація, не реалізовано.**` line, so nobody reads a plan as a
  description of current behaviour.
- When such a plan is implemented, move the substance into the matching
  `docs/starter/` page and leave the plan only if its reasoning is still worth keeping.
- Paths and class names here are checked against the code the same way as anywhere else —
  a stale path in a plan sends the next session to a file that does not exist.
