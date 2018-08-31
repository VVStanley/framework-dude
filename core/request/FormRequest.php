<?php

namespace core\request;

use core\exception\ErrorHandler;

class FormRequest extends Request
{
    const ANSWER_RU = 'resources/lang/ru/validation.php';
    const ANSWER_EN = 'resources/lang/en/validation.php';

    private $validation;
    private $message = array();
    private $answer = array();

    public function __construct()
    {
        parent::__construct();

        $this->validation = new Validation();

        $this->answer = include_once self::ANSWER_RU;
    }

    /** валидация массива пост
     *
     * @return array
     */
    public function checkPost()
    {
        $this->message['error'] = false;
        $this->message['errors'] = array();
        $this->message['redirect'] = false;
        $this->message['ok'] = $this->answer['ok'];

        //  проверяем пришел ли токен
        if (($this->post('_token')) == null
                || $this->session('_token') == null
                    || !hash_equals($this->post('_token'), $this->session('_token'))) {

            throw new ErrorHandler('Токен не соответствует либо не создан (_token)');
        }

        //  запускаем цикл по полям правил валидации
        foreach ($this->rules as $nameRules => $rule) {

            //  проверяем поле на заполнение по правилу required
            if (isset($rule['required']) && $rule['required']){

                if (empty($this->post($nameRules))){

                    $this->message['error'] = true;
                    $this->message['errors']['required' . $nameRules]
                            = sprintf($this->answer['required'], $rule['name']);
                    continue;
                }
            }

            //  проверяем телефон
            if (isset($rule['type']) && $rule['type'] == 'phone'){

                if (!$this->validation->checkPhone($this->post($nameRules))){

                    $this->message['error'] = true;
                    $this->message['errors']['phone'] = $this->answer['phone'];
                    continue;
                }
            }

            //  проверяем email
            if (isset($rule['type']) && $rule['type'] == 'email'){

                if (!$this->validation->checkEmail($this->post($nameRules))){

                    $this->message['error'] = true;
                    $this->message['errors']['email'] = $this->answer['email'];
                    continue;
                }
            }

            //  проверяем на длинну
            if (isset($rule['max']) && $rule['max']){

                if(strlen($this->post($nameRules)) > $rule['max']){

                    $this->message['error'] = true;
                    $this->message['errors']['max' . $nameRules]
                            = sprintf($this->answer['max'], $rule['name'], $rule['max']);
                    continue;
                }
            }

            //  проверяем на длинну
            if (isset($rule['min']) && $rule['min']){

                if(strlen($this->post($nameRules)) < $rule['min']){

                    $this->message['error'] = true;
                    $this->message['errors']['min' . $nameRules]
                            = sprintf($this->answer['min'], $rule['name'], $rule['min']);
                    continue;
                }
            }

            //  проверка на капчу
            if ($nameRules == 'captcha'){

                //  пришла ли капча в массиве пост
                if (!is_null($this->post('g-recaptcha-response'))) {

                    //  проверяем верна ли капча
                    if (!$this->validation->reCaptchaSuccess($this->post('g-recaptcha-response'))) {

                        $this->message['error'] = true;
                        $this->message['errors']['captcha'] = $this->answer['captcha'];
                        continue;
                    }
                    continue;
                }

                $this->message['error'] = true;
                $this->message['errors']['captcha'] = $this->answer['captchaNOT'];
                continue;
            }

            //  проверяем на подтверждение пароль
            if (isset($rule['type']) && $rule['type'] == 'password'){

                if (!is_null($this->post('password-confirm'))){

                    if ($this->post('password') != $this->post('password-confirm')){

                        $this->message['error'] = true;
                        $this->message['errors']['password-confirm'] = $this->answer['password-confirm'];
                        continue;
                    }
                }
            }

            //  проверка на наличие цыфр в пароле
            if (isset($rule['password-digits']) && $rule['password-digits']){

                if (!$this->validation->checkDigits($this->post('password'))){

                    $this->message['error'] = true;
                    $this->message['errors']['password-digits'] = $this->answer['password-digits'];
                    continue;
                }
            }

            //  проверка на наличие нижнего регистра букв в пароле
            if (isset($rule['password-lowercase']) && $rule['password-lowercase']){

                if (!$this->validation->checkLowercase($this->post('password'))){

                    $this->message['error'] = true;
                    $this->message['errors']['password-lowercase'] = $this->answer['password-lowercase'];
                    continue;
                }
            }

            //  проверка на наличие верхнего регистра букв в пароле
            if (isset($rule['password-uppercase']) && $rule['password-uppercase']){

                if (!$this->validation->checkUppercase($this->post('password'))){

                    $this->message['error'] = true;
                    $this->message['errors']['password-uppercase'] = $this->answer['password-uppercase'];
                    continue;
                }
            }

            //  поле состоит только из цифр
            if (isset($rule['numeric']) && $rule['numeric']){

                //  todo написать проверку на цифры
            }

        }

        return $this->message;
    }




    public function checkFiles()
    {
        $this->message['error'] = false;
        $this->message['errors'] = array();
        $this->message['redirect'] = false;
        $this->message['ok'] = $this->answer['ok'];

        //  проверяем пришел ли токен
        if (($this->post('_token')) == null
            || $this->session('_token') == null
            || !hash_equals($this->post('_token'), $this->session('_token'))) {

            throw new ErrorHandler('Токен не соответствует либо не создан (_token)');
        }

        //  запускаем цикл по полям правил валидации
        foreach ($this->rules as $nameRules => $rule) {

            //  проверяем поле на заполнение по правилу required
            if (isset($rule['required']) && $rule['required']) {

                if (empty($this->take('name')->files($nameRules))) {

                    $this->message['error'] = true;
                    $this->message['errors']['requiredFile' . $nameRules] = $this->answer['requiredFile'];
                    continue;
                }
            }

            //todo дописать проверку на разные типы файлов

        }

        return $this->message;
    }

}