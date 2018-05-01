<?php
/**
 * Update Checker plugin for Craft CMS 3.x
 *
 * Automated update checker that notifies you of any pending updates
 *
 * @link      https://github.com/lukeyouell
 * @copyright Copyright (c) 2018 Luke Youell
 */

namespace lukeyouell\updatechecker\controllers;

use lukeyouell\updatechecker\UpdateChecker;
use lukeyouell\updatechecker\services\MailService;

use Craft;
use craft\models\Update;
use craft\web\Controller;

/**
 * @author    Luke Youell
 * @package   UpdateChecker
 * @since     1.0.0
 */
class CheckController extends Controller
{
    // Protected Properties
    // =========================================================================

    protected $allowAnonymous = ['index'];

    // Public Methods
    // =========================================================================

    public function actionIndex()
    {
        $this->requireAcceptsJson();

        $forceRefresh = true;
        $includeDetails = true;
        $allowUpdates = true;

        // Get updates (force refresh)
        $updates = Craft::$app->getUpdates()->getUpdates($forceRefresh);

        $res = [
            'total' => $updates->getTotal(),
            'critical' => $updates->getHasCritical(),
            'allowUpdates' => $allowUpdates,
        ];

        if ($includeDetails) {
            $res['updates'] = [
                'cms' => $this->_transformUpdate($allowUpdates, $updates->cms, 'craft', 'Craft CMS'),
                'plugins' => [],
            ];

            $pluginsService = Craft::$app->getPlugins();

            foreach ($updates->plugins as $handle => $update) {
                if (($plugin = $pluginsService->getPlugin($handle)) !== null) {
                    /** @var Plugin $plugin */
                    $res['updates']['plugins'][] = $this->_transformUpdate($allowUpdates, $update, $handle, $plugin->name);
                }
            }
        }

        // If there are updates send notification
        if ($res['total'] > 0) {
          MailService::sendUpdateNotification($res);
        }

        return $this->asJson($res);
    }

    // Private Methods
    // =========================================================================

    private function _transformUpdate(bool $allowUpdates, Update $update, string $handle, string $name): array
    {
        $arr = $update->toArray();
        $arr['handle'] = $handle;
        $arr['name'] = $name;
        $arr['latestVersion'] = $update->getLatest()->version ?? null;

        if ($update->status === Update::STATUS_EXPIRED) {
            $arr['statusText'] = Craft::t('app', '<strong>Your license has expired!</strong> Renew your {name} license for another year of amazing updates.', [
                'name' => $name
            ]);
            $arr['ctaText'] = Craft::t('app', 'Renew for {price}', [
                'price' => Craft::$app->getFormatter()->asCurrency($update->renewalPrice, $update->renewalCurrency)
            ]);
            $arr['ctaUrl'] = UrlHelper::url($update->renewalUrl);
        } else {
            if ($update->status === Update::STATUS_BREAKPOINT) {
                $arr['statusText'] = Craft::t('app', '<strong>You’ve reached a breakpoint!</strong> More updates will become available after you install {update}.</p>', [
                    'update' => $name.' '.($update->getLatest()->version ?? '')
                ]);
            }

            if ($allowUpdates) {
                $arr['ctaText'] = Craft::t('app', 'Update');
            }
        }

        return $arr;
    }
}
