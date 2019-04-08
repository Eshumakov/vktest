<?php
class Base_Model_User {
    private $data = [];
    public function __construct($data)
    {
        if (empty($data)) {
            return false;
        }
        $this->data = $data;
    }

    public function getId()
    {
        return $this->data['id'];
    }

    public function getLogin()
    {
        return $this->data['name'];
    }

    public function getPassword()
    {
        return $this->data['password'];
    }

}