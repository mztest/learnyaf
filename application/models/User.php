<?php
/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/5/27
 * Time: 上午10:46
 */
use App\models\base\ValidatesModels;
use App\models\web\IdentityInterface;
use Illuminate\Database\Eloquent\Model;

class UserModel extends Model implements IdentityInterface
{
    use ValidatesModels;

    protected $table = 'users';

    protected $guarded = ['id', 'password_hash', 'auth_key'];

    protected $hidden = ['password_hash', 'auth_key'];

    protected static function boot()
    {
        parent::boot();
        static::saving(function($user){
            return $user->validate($user->toArray(), $user->rules());
        });
    }

    public function rules()
    {
        return [
            'username' => 'required|string|between:6,30|unique:users',
            'age' => 'numeric',
            'phone' => 'numeric',
            'mobile' => 'numeric|length:11',
        ];
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yaf\Registry::get('Security')->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yaf\Registry::get('Security')->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yaf\Registry::get('Security')->generateRandomString();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->{$this->getKeyName()};
    }

    /**
     * @return mixed
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::find($id);
    }

    public static function findByUsername($username)
    {
        return static::where('username', $username)->first();
    }

}