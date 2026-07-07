### Architecture notes

* koten4ik: Remove Observers — they're magic that hurts code readability and are an anti-pattern at this point. Replace them with events that get dispatched from actions.

* koten4ik: Rename Actions to Commands.

* koten4ik: Data going from a command to a view should go through a ViewModel — it's fed an Eloquent model and outputs an array of prepared fields ready for rendering.

* koten4ik: Remove DTOs as well — in Laravel they're overkill, this is also usually used in enterprise-leaning projects.

* koten4ik: All models and controllers should extend a base Model and a base Controller.

### TODO

* Add endpoints for the Telegram bot and the corresponding libraries.

* Upload/update the price list from an Excel file.
 
* User roles in the admin panel. 

* Dynamic product attributes.

* Multilingual support on the website.

* Run migrations and other console commands from the web interface.

* Image rotation.

* Cron jobs.

* URL redirects.

* Canonical URLs.

* Structured data markup.

* Add language handling as the first URL slug to support future multilingual websites.
  `app/Modules/Core/Http/Controllers/ModuleRouteController.php`



