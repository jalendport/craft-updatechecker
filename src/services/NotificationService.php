<?php
/**
 * Update Checker plugin for Craft CMS 3.x
 *
 * Automated update checker that notifies you of any pending updates
 *
 * @link      https://github.com/lukeyouell
 * @copyright Copyright (c) 2018 Luke Youell
 */

namespace lukeyouell\updatechecker\services;

use lukeyouell\updatechecker\UpdateChecker;

use Craft;
use craft\base\Component;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use craft\mail\Message;
use craft\web\View;

use yii\helpers\Markdown;

/**
 * @author    Luke Youell
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

        $client->request('POST', $settings->slackWebhook, [
            'body' => Json::encode([
                'username' => 'Update Checker',
                'icon_emoji' => ':bell:',
                'text' => 'There are '.$updates['total'].' package updates available for '.$system->name,
                'attachments' => [
                    [
                        'fallback' => 'View updates at '.$cpUrl,
                        'actions' => [
                            [
                              'type' => 'button',
                              'text' => 'View updates',
                              'url' => $cpUrl,
                              'style' => 'primary',
                            ],
                        ],
                    ],
                ],
            ]),
        ]);
    }
}
