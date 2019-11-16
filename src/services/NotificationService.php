<?php
/**
 * Update Checker plugin for Craft CMS 3.x
 *
 * Automated update checker that notifies you of any pending updates
 *
 * @link      https://github.com/jalendport
 * @copyright Copyright (c) 2018 Jalen Davenport
 */

namespace jalendport\updatechecker\services;

use jalendport\updatechecker\UpdateChecker;

use Craft;
use craft\base\Component;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use craft\mail\Message;
use craft\web\View;

use yii\base\InvalidConfigException;
use yii\helpers\Markdown;

/**
 * @author    Jalen Davenport
 * @package   UpdateChecker
 * @since     1.0.0
 */
class NotificationService extends Component
{
    // Static Methods
    // =========================================================================

    public static function handleNotifications($updates = null)
    {
        if ($updates) {
            // Get the plugin settings and make sure they validate before doing anything
            $settings = UpdateChecker::$plugin->getSettings();
            if (!$settings->validate()) {
                throw new InvalidConfigException('Update Checker settings don’t validate.');
            }

            // Prep values
            $system = Craft::$app->getInfo();
            $cpUrl = UrlHelper::cpUrl('utilities/updates');

            // Send emails if enabled
            if ($settings->email) {
                self::sendEmails($settings, $updates, $system, $cpUrl);
            }

            // Send slack notification if enabled
            if ($settings->slack) {
                self::sendSlackNotification($settings, $updates, $system, $cpUrl);
            }
        }
    }

    public static function sendEmails($settings, $updates, $system, $cpUrl)
    {
        // Prep values
        $system = Craft::$app->getInfo();
        $fromEmail = Craft::$app->systemSettings->getSetting('email', 'fromEmail');
        $fromName = Craft::$app->systemSettings->getSetting('email', 'fromName');
        $toEmails = is_string($settings->toEmail) ? StringHelper::split($settings->toEmail) : $settings->toEmail;
        $subject = '🔔 There are '.$updates['total'].' package updates available for '.$system->name;

        // Set template
        \Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $html = Craft::$app->view->renderTemplate(
            'update-checker/emails/updateNotification',
            [
              'system' => $system,
              'cpUrl' => $cpUrl,
              'updates' => $updates,
            ]
        );
        $html = Markdown::process($html, 'gfm');

        // Send the email
        $mailer = Craft::$app->getMailer();
        $message = (new Message())
            ->setFrom([$fromEmail => $fromName])
            ->setSubject($subject)
            ->setHtmlBody($html);

        foreach ($toEmails as $toEmail) {
            $message->setTo($toEmail);
            $mailer->send($message);
        }
    }

    public static function sendSlackNotification($settings, $updates, $system, $cpUrl)
    {
        $client = new \GuzzleHttp\Client([
            'http_errors' => false,
            'timeout' => 10,
        ]);

        $releases = [];
        $attachments = [];
        $cmsName = $updates['updates']['cms']['name'] ?? 'Undefined';
        $cmsReleases = $updates['updates']['cms']['releases'] ?? null;
        $plugins = $updates['updates']['plugins'] ?? null;

        // CMS updates
        if ($cmsReleases) {
            foreach ($cmsReleases as $release) {
                $icon = $release['critical'] ? '⚠️' : '▪️';
                $releases[] = "{$icon} {$release['version']}\n";
            }

            $cmsText = implode('', $releases);
        } else {
            $cmsText = "✅ Up-to-date!";
        }

        // CMS attachment
        $attachments[] = [
            'fallback' => "There are {$cmsName} updates pending.",
            'title' => $cmsName,
            'text' => $cmsText,
        ];

        // Plugin updates
        if ($plugins) {
            $releaseText = [];
            $pluginText = [];

            foreach ($plugins as $plugin) {
                if ($plugin['releases']) {
                    foreach ($plugin['releases'] as $release) {
                        $icon = $release['critical'] ? '⚠️' : '▪️';
                        $releaseText[] = "{$icon} {$release['version']}\n";
                    }
                } else {
                    $releaseText[] = "✅ Up-to-date!\n";
                }

                $pluginText[] = "*{$plugin['name']}*\n".implode('', $releaseText)."\n";

                $releaseText = [];
            }

            $pluginsText = implode('', $pluginText);

            // Plugin attachment
            $attachments[] = [
                'fallback' => "There are plugin updates pending.",
                'title' => 'Plugins',
                'text' => $pluginsText,
            ];
        }

        // View updates button
        $attachments[] = [
            'fallback' => "View updates at {$cpUrl}",
            'actions' => [
                [
                  'type' => 'button',
                  'text' => 'View updates',
                  'url' => $cpUrl,
                  'style' => 'primary',
                ],
            ],
        ];

        $client->request('POST', $settings->slackWebhook, [
            'body' => Json::encode([
                'username' => 'Update Checker',
                'icon_emoji' => ':bell:',
                'text' => "There are {$updates['total']} package updates available for {$system->name}",
                'attachments' => $attachments,
            ]),
        ]);
    }
}
