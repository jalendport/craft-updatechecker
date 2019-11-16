<?php
/**
 * Update Checker plugin for Craft CMS 3.x
 *
 * Automated update checker that notifies you of any pending updates
 *
 * @link      https://github.com/jalendport
 * @copyright Copyright (c) 2018 Jalen Davenport
 */

namespace jalendport\updatechecker;

use jalendport\updatechecker\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\events\PluginEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\services\Plugins;

use yii\base\Event;

/**
 * Class UpdateChecker
 *
 * @author    Jalen Davenport
 * @package   UpdateChecker
 * @since     1.0.0
 *
 */
class UpdateChecker extends Plugin
{
    // Static Properties
    // =========================================================================

    public static $plugin;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('settings/plugins/update-checker'))->send();
                }
            }
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        // Get and pre-validate the settings
        $settings = $this->getSettings();
        $settings->validate();

        // Get the settings that are being defined by the config file
        $overrides = Craft::$app->getConfig()->getConfigFromFile(strtolower($this->handle));

        return Craft::$app->view->renderTemplate(
            'update-checker/settings',
            [
                'settings' => $settings,
                'overrides' => array_keys($overrides)
            ]
        );
    }
}
