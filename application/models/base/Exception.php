<?php
/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/6/8
 * Time: 下午2:47
 */

namespace App\models\base;


/**
 * Exception represents a generic exception for all purposes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Exception extends \Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Exception';
    }
}