<?php

namespace plugin\owladmin\app\trait;
use plugin\owladmin\app\support\Cores\Asset;

trait AssetsTrait
{
    /**
     * @return Asset;
     */
    public static function asset(): Asset
    {
        return new Asset;
    }

    /**
     * 加载 js 文件
     *
     * @param $js
     *
     * @return Asset
     */
    public static function js($js = null): Asset
    {
        return static::asset()->js($js);
    }

    /**
     * 加载 css 文件
     *
     * @param $css
     *
     * @return Asset
     */
    public static function css($css = null): Asset
    {
        return static::asset()->css($css);
    }

    /**
     * 加载 js 脚本
     *
     * @param $scripts
     *
     * @return Asset
     */
    public static function scripts($scripts = null): Asset
    {
        return static::asset()->scripts($scripts);
    }

    /**
     * 加载样式表
     *
     * @param $styles
     *
     * @return Asset
     */
    public static function styles($styles = null): Asset
    {
        return static::asset()->styles($styles);
    }

    public static function getAssets(): array
    {
        return [
            'js'      => static::asset()->js(),
            'css'     => static::asset()->css(),
            'scripts' => static::asset()->scripts(),
            'styles'  => static::asset()->styles(),
        ];
    }

    /**
     * 在后面添加 Nav
     *
     * @param $appendNav
     *
     * @return Asset
     */
    public static function appendNav($appendNav = null): Asset
    {
        return static::asset()->appendNav($appendNav);
    }

    /**
     * 在前面添加 Nav
     *
     * @param $prependNav
     *
     * @return Asset
     */
    public static function prependNav($prependNav = null): Asset
    {
        return static::asset()->prependNav($prependNav);
    }

    public static function getNav(): array
    {
        return [
            'appendNav'  => static::asset()->appendNav(),
            'prependNav' => static::asset()->prependNav(),
        ];
    }
}
