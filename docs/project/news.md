# News

A news feed with block-based content. Code — the `News` module (`app/Modules/News/`) and the
admin resource `app/Filament/Resources/News/`.

---

## 1. News item

| Field | What it is |
|---|---|
| Title | required |
| Publication date | the feed is sorted by it |
| Show | visibility toggle on the site |
| Short text | teaser for the list |
| Image | main photo |
| Content | a set of blocks (see below) |
| SEO | slug, meta title, meta description; can be generated automatically |

### Content blocks

The manager builds the news text from blocks that can be collapsed, cloned and
reordered by dragging:

- **Text**
- **Image**
- **Gallery**
- **Video**
- **Teasers**

---

## 2. Pages

| URL | What it shows |
|---|---|
| `/{news}` | news list, newest first, 12 per page |
| `/{news}/{slug}` | a news item with meta title / description from its own SEO fields |

`{news}` — the slug of the page with the "News" module in the admin panel.

Only news items with "Show" enabled are visible on the site. The publication date does **not**
hide an item: an entry with a future date is visible immediately.

News items are included in `sitemap.xml`.

---

## 3. Admin panel

A list with image, title, slug, publication date and a visibility toggle right in the table;
sorted by publication date. Creating and editing happen in a modal.

---

## 4. Enabling / disabling

`config/modules.php` → `list` → `'news' => false` removes the routes, the admin resource and
news from the sitemap.

---

## 5. Not implemented yet

- Scheduled publication (showing by date).
- Categories / tags, search, related news.
- API.
