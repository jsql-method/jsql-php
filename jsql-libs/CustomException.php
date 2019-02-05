<?php
// na razie nie obsługiwane moze nie bedzie potrzebne
class CustomException extends PDOException {

    public function __construct($message=null, $code=null) {
        $this->message = $message;
        $this->code = $code;
    }

}