<?php namespace App\helpers;
/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/6/11
 * Time: 下午3:28
 */
class HtmlHelper
{
    /**
     * @param $links
     * @return string|void
     */
    public static function breadcrumb(array $links)
    {
        if (empty($links)) {
            return;
        }
        $html = '<ol class="breadcrumb">';
        foreach($links as &$link) {
            if (!is_array($link)) {
                $link = ['label' => $link];
            }
            if (isset($link['url'])) {
                $html .= '<li><a href="'. $link['url']. '">'. $link['label'] .'</a></li>';
            } else {
                $html .= '<li class="active">'. $link['label'] .'</li>';
            }
        }
        $html .= '</ol>';
        return $html;
    }
}