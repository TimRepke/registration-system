<?php

require_once 'abstract_signup_class.php';

class SignupMethods {

    private static $__instance = NULL;
    private $signup_methods = [];
    private $fallback_method = 'form';

    private $environment;

    public static function getInstance() {
        if(self::$__instance == NULL) self::$__instance = new SignupMethods();
        return self::$__instance;
    }

    protected function __construct() {
        $this->signup_methods = $this->loadSignupMethods();
        $this->environment = Environment::getEnv();
    }

    public function getSignupMethods() {
        return $this->signup_methods;
    }

    /**
     * @return array of assoc_arrays with name, desc and contribs
     */
    public function getSignupMethodsBaseInfo() {
        $tmp = [];
        foreach($this->signup_methods as $method) {
            array_push($tmp, [
                'id'           => $method['id'],
                'name'         => $method['class']::getName(),
                'description'  => $method['class']::getAltText(),
                'meta'         => $method['class']::getMetaInfo()
            ]);
        }
        return $tmp;
    }

    /**
     * @return bool returns true iff a method is set and exists
     */
    public function signupMethodExists() {
        try {
            $this->getActiveMethod();
            return true;
        } catch (Exception $e) {
            //echo $e;
            return false;
        }
    }

    public function getFallbackMethod() {
        $method = $this->getMethodObj($this->fallback_method);
        return new $method['class']();
    }

    /**
     * @return class (instantiated) of the active signup method
     * @throws ErrorException when $_GET['method'] is missing or not available in the list
     */
    public function getActiveMethod() {
        $method = $this->getActiveMethodObj();
        return new $method['class']();
    }

    /**
     * @return id of the class (and with that the folder)
     * @throws ErrorException when $_GET['method'] is missing or not available in the list
     */
    public function getActiveMethodId() {
        $method = $this->getActiveMethodObj();
        return $method['id'];
    }

    /**
     * @return array
     * @throws ErrorException when $_GET['method'] is missing or not available in the list
     */
    private function getActiveMethodObj() {
        if(!isset($_REQUEST['method'])) throw new ErrorException('No signup-method selected!');
        return $this->getMethodObj($_REQUEST['method']);
    }

    private function getMethodObj($mode) {
        if(Environment::getEnv()->formDataReceived()) $mode = $this->fallback_method;
        if(!isset($this->signup_methods[$mode])) throw new ErrorException('Signup-method does not exist!');
        return [ 'id' => $mode, 'class' => $this->signup_methods[$mode]['class']];
    }

    private function getMethodDirs() {
        return glob(__DIR__ . '/*' , GLOB_ONLYDIR);
    }

    private function loadSignupMethod($folder_name) {
        $tmp_method_folder = basename($folder_name);
        $tmp_file_name     = __DIR__ . '/' . $tmp_method_folder . '/index.php';

        try {
            if (file_exists($tmp_file_name)) {
                require_once $tmp_file_name;

                $tmp_class_name = ucfirst($tmp_method_folder . 'SignupMethod');
                if (class_exists($tmp_class_name)) {
                    return [
                        'id'        => $tmp_method_folder,
                        'class'     => $tmp_class_name,
                        'classFile' => $tmp_file_name
                    ];
                }
            }
        } catch (Exception $e) { /* do nothing */  }

        return false;
    }

    private function loadSignupMethods() {
        $tmp_method_dirs = $this->getMethodDirs();
        $tmp_methods     = [];

        foreach ($tmp_method_dirs as $method_dir) {
            $tmp_method = $this->loadSignupMethod($method_dir);
            if ($tmp_method) $tmp_methods[basename($method_dir)] =  $tmp_method;
        }

        return $tmp_methods;
    }
}
