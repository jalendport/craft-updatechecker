<img src="src/icon.svg" alt="icon" width="100" height="100">

# Update Checker plugin for Craft CMS 3

Automated update checker that notifies you of any pending updates

## Requirements

This plugin requires Craft CMS 3.0.0 or later.

## Installation

To install the plugin, either install via the plugin store or follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require lukeyouell/craft-updatechecker

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Update Checker.

## Update Checker Overview

This plugin will check for package updates at regular intervals and notify you of any pending updates.

## Configuring Update Checker

### Endpoint URL

You can find your endpoint url located in the plugin settings page, which will look like this:

```
https://www.yoursite.co.uk/actions/update-checker/check
```

### Cron Job

This plugin relies on the controller being hit on a regular basis, which will require a cron job. If you aren't familiar with setting up cron jobs I highly recommend using a service such as [SetCronJob](https://www.setcronjob.com).

Please note that all HTTP requests must contain the following headers:

| Header Name | Header Value |
| ----------- | ------------ |
| `Accept` | `application/json` |

Feel free to set the cron job to check for updates as often as you like.

## Overriding Plugin Settings

If you create a [config file](https://docs.craftcms.com/v3/configuration.html) in your `config` folder called `update-checker.php`, you can override the plugin’s settings in the Control Panel. Since that config file is fully [multi-environment](https://docs.craftcms.com/v3/configuration.html) aware, this is a handy way to have different settings across multiple environments.

Here’s what that config file might look like along with a list of all of the possible values you can override.

```php
<?php

return [
    'toEmail' => 'joe.bloggs@email.co.uk, jane.bloggs@email.co.uk',
];
```

## Update Checker Roadmap

Some things to do, and ideas for potential features:

- Additional notification methods

Brought to you by [Luke Youell](https://github.com/lukeyouell)
