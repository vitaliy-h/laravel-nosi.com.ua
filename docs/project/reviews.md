# Reviews

Reviews of the shop as a whole — delivery, service, the order experience — not of a
single product. Products have no reviews and no rating. Code — the `Review` module
(`app/Modules/Review/`), the admin resource `app/Filament/Resources/Home/Reviews/` and the
dashboard widget `app/Filament/Widgets/Review/`.

---

## 1. On the site

The home page block «Відгуки покупців» shows:

- the shop rating — the average of published reviews and their number;
- a carousel of the 10 latest published reviews: stars, text, author name and city;
- a **«Залишити відгук»** button that opens a dialog with the form.

The block is shown even when nothing is published yet (without the rating and the
carousel), so the first customer can leave a review. All texts of the block and the form
are in the dictionary, group `home` (`reviews_*`, `review_*`).

## 2. Leaving a review

- Anyone can leave a review (a guest — with name and email, city optional): a 1–5 rating
  and text. A logged-in customer reviews under their account name and email.
- The form is sent without reloading the page (`POST /review`, route `review.store`);
  on success the dialog shows the thank-you message.
- Limit — 10 reviews per minute from one address.
- A new review is **hidden** until a manager publishes it in the admin panel; only
  published reviews are shown and count towards the rating.

## 3. Admin panel

**Home page → Reviews**: list with search by author and text, filters by «show», user
and rating; create (backdating allowed), edit, publish with the toggle in the list, delete.

**Dashboard**: the «Reviews» widget (average rating, new reviews in the period,
moderation queue, distribution by stars) and the «Reviews to moderate» card in «Needs
attention».

## 4. Enabling / disabling

`config/modules.php`, key `review`. Independent of the shop: with `shop` off the reviews
stay.
