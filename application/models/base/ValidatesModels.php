<?php namespace App\models\base;
/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/6/10
 * Time: 上午11:28
 */
use Illuminate\Container\Container;
use Illuminate\Validation\Factory;
use Symfony\Component\Translation\Translator;

trait ValidatesModels
{
    protected $validationFactory;

    public function validate()
    {

        $validator = $this->getValidationFactory()->make($this->toArray(), $this->rules());
        if ($validator->fails()) {
            return ($this->_errors = $validator->errors()->getMessages());
        }
        return true;
    }
    /**
     * Get a validation factory instance.
     *
     * @return \Illuminate\Contracts\Validation\Factory
     */
    protected function getValidationFactory()
    {
        if ($this->validationFactory) {
            return $this->validationFactory;
        }
        $translator = new Translator('en');
        $container = new Container();

        return $this->validationFactory =  new Factory($translator, $container);
    }
}