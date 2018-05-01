<?php
/**
 * Update Checker plugin for Craft CMS 3.x
 *
 * Automated update checker that notifies you of any pending updates
 *
 * @link      https://github.com/lukeyouell
 * @copyright Copyright (c) 2018 Luke Youell
 */

namespace lukeyouell\updatechecker\models;

use lukeyouell\updatechecker\UpdateChecker;

use Craft;
use craft\base\Model;

/**
 * @author    Luke Youell
 * @package   UpdateChecker
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $toEmail;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['toEmail'], 'required'],
            [['toEmail'], 'string'],
        ];
    }
}
