<?php
namespace lo\plugins;

use lo\core\helpers\ArrayHelper;
use lo\plugins\interfaces\IShortcode;
use lo\plugins\shortcodes\Shortcode;
use Yii;
use yii\base\Event;
use yii\base\View;

/**
 * Class BaseShorcode
 * @package lo\plugins
 * @author Lukyanov Andrey <loveorigami@mail.ru>
 */
abstract class BaseShortcode extends BasePlugin implements IShortcode
{
    /**
     * Base Handler
     */
    const HANDLER_PARSE_SHORCODES = 'parseShortcodes';

    /**
     * @return array
     */
    final public static function events()
    {
        return [
            View::class => [
                View::EVENT_AFTER_RENDER => [self::HANDLER_PARSE_SHORCODES, static::$config]
            ],
        ];
    }

    /**
     * Parse shortcodes
     *    'callbacks' => [
     *      'code' => ['lo\plugins\plugins\code\Code', 'widget'],
     *      'anothershortcode'=>function($attrs, $content, $tag){
     *          .....
     *      },
     *  ]
     * @param Event $event
     */
    public static function parseShortcodes($event)
    {
        if (isset($event->output)) {
            $shortcodes = static::shortcodes();

            if ($shortcodes && is_array($shortcodes)) {
                foreach ($shortcodes as $tag => $callback) {

                    if (self::hasShortcode($tag)) {
                        continue;
                    }

                    if (is_callable($callback)) {
                        $parser = [
                            'callback' => $callback,
                            'config' => ArrayHelper::merge(
                                static::$config, $event->data
                            )
                        ];
                    } else {
                        continue;
                    }
                    /** add to collection */
                    self::addShortcode($tag, $parser);
                }
            }
            $event->output = self::doShortcode($event->output);
        }
    }

    /**
     * @param $content
     * @return string
     */
    public static function doShortcode($content)
    {
        $shortcode = self::getShortcodeObject();
        return $shortcode->process($content);
    }

    /**
     * @param $tag
     * @param $parser
     */
    public static function addShortcode($tag, $parser)
    {
        $shortcode = self::getShortcodeObject();
        $shortcode->addShortcode($tag, $parser);
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function hasShortcode($key)
    {
        $shortcode = self::getShortcodeObject();
        return $shortcode->hasShortcode($key);
    }

    /**
     * @return Shortcode
     */
    protected static function getShortcodeObject()
    {
        /** @var Shortcode $shortcode */
        $shortcode = Yii::$container->get(Shortcode::class);
        return $shortcode;
    }
}