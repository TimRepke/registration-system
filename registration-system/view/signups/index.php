<?php

require_once "abstract_signup_class.php";

class SignupMethods {

    private $signup_methods = [];

    function __construct() {
        $this->signup_methods = $this->loadSignupMethods();
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
                "name"         => $method["class"]::getName(),
                "description"  => $method["class"]::getAltText(),
                "contributors" => $method["class"]::getMetaInfo()
            ]);
        }
        return $tmp;
    }

    /**
     * @return class (instantiated) of the active signup method
     * @throws ErrorException when $_GET["method"] is missing or not available in the list
     */
    public function getActiveMethod() {
        if(!isset($_GET["method"])) throw new ErrorException("No signup-method selected!");
        $mode = $_GET["method"];
        if(!isset($this->signup_methods[$mode])) throw new ErrorException("Signup-method does not exist!");

        return new $this->signup_methods->$mode->class();
    }

    private function getMethodDirs() {
        return glob(__DIR__ . '/*' , GLOB_ONLYDIR);
    }

    private function loadSignupMethod($folder_name) {
        $tmp_file_name = __DIR__ . '/' . $folder_name . '/index.php';

        try {
            if (file_exists($tmp_file_name)) {
                require_once $tmp_file_name;

                $tmp_class_name = ucfirst($folder_name . 'SignupMethod');
                if (class_exists($tmp_class_name)) {
                    return [
                        'class' => $tmp_class_name,
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
            if ($tmp_method) $tmp_methods[$method_dir] =  $tmp_method;
        }

        return $tmp_methods;
    }
}