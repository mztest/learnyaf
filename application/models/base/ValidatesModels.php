<?php namespace App\models\base;
/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/6/10
 * Time: 上午11:28
 */
use Illuminate\Container\Container;
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Factory;
use Symfony\Component\Translation\Translator;

trait ValidatesModels
{
    protected $validationFactory;

    private $_errors;

    public function validate()
    {

        $validator = $this->getValidationFactory()->make($this->toArray(), $this->rules());
        if ($validator->fails()) {
            $this->_errors = $validator->errors()->getMessages();
            return false;
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
        $this->validationFactory =  new Factory(\Yaf\Registry::get('Translator'), new Container());
        $this->validationFactory->setPresenceVerifier(new DatabasePresenceVerifier(\Yaf\Registry::get('CapsuleDatabaseManager')));
        return $this->validationFactory;

    }
}