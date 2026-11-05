<?php
declare(strict_types=1);
namespace TalentHub;

final class Config {
    # final class significa que no se puede heredar
    private static ?array $values = null;
    public static function get(string $key):mixed {
        if (self :: $values === null) {
            $file = dirname(__DIR__). '/config/local.php';
            self::$values = is_file($file) ? require $file : [];
        }
        $enviroment = getenv(strtoupper($key));
         # getenv busca una variable de entorno y strtoupper lo convierte a mayusculas
         return ($enviroment !== false && $enviroment !== '') 
         ? $enviroment 
         : (self :: $values [$key] ?? null);
         # si getenv devuelve algo, lo devuelve, sino lo que esta en $values 
    }

    public static function required (string $key) : string {
        $value = self::get($key);
        if (!is_string($value) || $value === '' || str_contains($value , 'REEMPLAZAR') ||
         $value === 'tu-proyecto'){
            throw new ApiException('Configura'. $key . 'en config/local.php', 503);         
        }
        return $value;
    }
}

?>