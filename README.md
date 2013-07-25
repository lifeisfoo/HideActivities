# HideActivities plugin for Vanilla Forums
Shows user activities (profile page) only to friends (from Friendships plugin).

Requires Vanilla >= 2.0.18.4 and Friendships plugin

## Setup
In order to get this working, you need to add this line at the beginning of your ```application/dashboard/views/activity/index.php```, just after ```<?php if (!defined('APPLICATION')) exit();```:

```php
$this->FireEvent('BeforeActivitiesList');      
```
Otherwise you can edit your theme's view (if it overrides this view).

## Sponsor
Thanks to [szarak](http://vanillaforums.org/profile/45649/szarak) for making this happen.

## Author and License
Alessandro Miliucci, GPL v3. Icon by [VisualPharm](http://www.visualpharm.com/)