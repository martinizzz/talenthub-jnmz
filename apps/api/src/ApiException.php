<?php
declare(strict_types = 1);
namespace TalentHub;

final class ApiException extends \RuntimeException {
    public int $httpStatus;
    # la \ sirve para indicar una clase que esta en el espacio de nombres raiz
    # si se omitiera , PHP buscaria 'RuntimeException' dentro del namespace 'TalentHub' -> 
    # 'TalentHub\RuntimeException'
    # pero no existe , por lo que marcaria un error
    
    public function __construct (string $message, public readonly int 
    $httpStatusCode = 400) {
        parent :: __construct($message);
    }
}

?>