<img src="src/icon.svg" alt="icon" width="100" height="100">

![](https://img.shields.io/github/release/jalendport/craft-updatechecker.svg?style=flat)
![](https://img.shields.io/packagist/dt/jalendport/craft-updatechecker.svg?style=flat)

# Update Checker plugin for Craft CMS 3

Automated update checker that notifies you of any pending updates.

## Support Open Source

This plugin is licensed under an MIT license, which means that it's completely free open source software, and you can use it for whatever and however you wish. If you find this plugin useful, please consider supporting my work by buying me a beer 🍻😊

[![Beerpay](https://beerpay.io/jalendport/craft-updatechecker/badge.svg?style=beer-square)](https://beerpay.io/lukeyouell/craft-updatechecker)

![Screenshot](resources/img/screenshot-slack.png)

## Requirements

This plugin requires Craft CMS 3.0.0 or later.

## Installation

To install the plugin, either install via the plugin store or follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require jalendport/craft-updatechecker

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Update Checker.

## Notification Methods

You can choose from a combination of the following notification methods:

### Email

An email will be sent to a list of comma-separated email addresses.

### Slack

Notifications will be sent to a Slack Channel of your choosing.

This requires the creation of a [Slack Webhook](https://api.slack.com/incoming-webhooks), which you can do [here](https://my.slack.com/services/new/incoming-webhook/).

## Endpoint

### Endpoint URL

You can find your endpoint url located in the plugin settings page, which will look like this:

```
https://www.yoursite.co.uk/actions/update-checker/check
```

### Securing Your Endpoint

You can secure your endpoint with an access key, which is set in the plugin settings.

After defining your access key you will need to include it in your requests in the form of the following header:

| Header Name | Header Value |
| ----------- | ------------ |
| `Access-Key` | `access-key-value` |

## Cron Job

Update Checker relies on the endpoint being hit on a regular basis, which will require a cron job. If you aren't familiar with setting up cron jobs I highly recommend using a service such as [SetCronJob](https://www.setcronjob.com).

HTTP requests headers:

| Header Name | Header Value | Required |
| ----------- | ------------ | -------- |
| `Accept` | `application/json` | Required |
| `Access-Key` | `access-key-value` | Optional |

Feel free to set the cron job to check for updates as often as you like.

## Overriding Plugin Settings

If you create a [config file](https://docs.craftcms.com/v3/configuration.html) in your `config` folder called `update-checker.php`, you can override the plugin’s settings in the Control Panel. Since that config file is fully [multi-environment](https://docs.craftcms.com/v3/configuration.html) aware, this is a handy way to have different settings across multiple environments.

Here’s what that config file might look like along with a list of all of the possible values you can override.

```php
<?php

return [
    'accessKey' => '6AIXCV7FoF5pvN4BiNE9xANtFUcy3CUr',
    'email' => true,
    'toEmail' => 'joe.bloggs@email.co.uk, jane.bloggs@email.co.uk',
    'slack' => true,
    'slackWebhook' => 'https://hooks.slack.com/services/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w',
];
```

## Update Checker Roadmap

Some things to do, and ideas for potential features:

- Ability to define which Slack channel to post updates to

Brought to you by [Luke Youell](https://github.com/lukeyouell)
