<?php
class CustomHttpException extends Exception {
    private $httpStatusCode;
    public function __construct($message,  $httpStatusCode = 500, $code = 0, Throwable $previous = null) {
        $this->httpStatusCode = $httpStatusCode;
        parent::__construct($message, $code,$previous);
    }

    public function getHttpStatusCode() {
        return $this->httpStatusCode;
    }
}

?>
