<?php namespace App\helpers;
/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/6/11
 * Time: 下午3:28
 */
use App\helpers\UrlHelper as Url;

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

    /**
     * @param array $items
     * @param array $options
     * @return string|void
     */
    public static function navbar(array $items, array $options=[])
    {
        if (empty($items)) {
            return;
        }
        if (isset($options['containerClass'])) {
            $html = '<ul class="'.$options['containerClass'].'">';
        } else {
            $html = '<ul class="nav navbar-nav">';
        }

        foreach($items as $item) {
            $isActive = Url::isItemActive($item);
            if (isset($item['items']) && is_array($item['items'])) {
                $html .= '<li class="dropdown">'
                    .'<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                aria-expanded="false">'.$item['label'].' <span class="caret"></span></a>';

                $html .= '<ul class="dropdown-menu" role="menu">';
                foreach($item['items'] as $subItem) {
                    if (is_string($subItem)) {
                        $html .= $subItem;
                    } else {
                        $html .= '<li><a href="'.Url::toRoute($subItem['url']).'">'.$subItem['label'].'</a></li>';
                    }
                }
                $html .= '</ul>';
            } else {
                $html .= '<li'.(($isActive) ? " class='active'":"").'><a href="'.Url::toRoute($item['url']).'">'
                    .$item['label']
                    .'</a>';
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }
}