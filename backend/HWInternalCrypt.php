<?php

/**
 * La clase HWInternalCrypt se crea con el fin de poder pasar datos entre servidores sin que la informacion viaje desencriptada
 *
 */
class HWInternalCrypt {

    private $cMethod;
    private $rutaLlavePri;
    private $rutaLlavePub;

    /**
     * 
     * @param string $rPub Ruta a la llave Publica
     * @param string $rPrv Ruta a la llave Privada
     */
    public function __construct($rPub = "", $rPrv = "") {
        $this->cMethod = "AES-256-CBC";
        if (empty($rPrv)) {
            $this->rutaLlavePri = (defined('PATH_PRI_KEY')) ? PATH_PRI_KEY : '';
        } else {
            $this->rutaLlavePri = $rPrv;
        }
        if (empty($rPub)) {
            $this->rutaLlavePub = (defined('PATH_PUB_KEY')) ? PATH_PUB_KEY : '';
        } else {
            $this->rutaLlavePub = $rPub;
        }
    }

    /**
     * Genera la llave privada
     * @param type $priv_key
     * @return boolean
     */
    private function getPrivateKey(&$priv_key) {
        $fp = @fopen($this->rutaLlavePri, "r");
        if (!$fp) {
            error_log("No fue posible leer el archivo que contiene la llave privada {$this->rutaLlavePri} en " . __FILE__);
            return false;
        }
        $text = @fread($fp, 8192);
        @fclose($fp);
        $priv_key = openssl_get_privatekey($text);
        if ($priv_key === false) {
            error_log("No fue posible obtener la llave privada en " . __FILE__);
            return false;
        }
        return true;
    }

    /**
     * Genera la llave publica
     * @param type $pub_key
     * @return boolean
     */
    function getPublicKey(&$pub_key) {
        $fp = @fopen($this->rutaLlavePub, "r");
        if (!$fp) {
            error_log("No fue posible leer el archivo que contiene la llave publica {$this->rutaLlavePub} en " . __FILE__);
            return false;
        }
        $pub_key = fread($fp, 8192);
        fclose($fp);
        openssl_get_publickey($pub_key);
        if ($pub_key === false) {
            error_log("No fue posible obtener la llave publica en " . __FILE__);
            return false;
        }
        return true;
    }

    /**
     * Genera el vector de inicilizacion desde una llave aleatoria
     * @param type $llaveAleatoria
     * @return type
     */
    function generarIV($llaveAleatoria) {
        $vectorInicializacion = sprintf('%u', crc32($llaveAleatoria));
        while (strlen("$vectorInicializacion") < 16) {
            $vectorInicializacion .= $vectorInicializacion;
        }
        $vectorInicializacion = substr($vectorInicializacion, 0, 16);

//		echo "<font color='brown'>Llave numerica: $vectorInicializacion</font><br>";
        return $vectorInicializacion;
    }

    /**
     * Encripta un dato
     * @param Mixed $plaintext
     * @return boolean|Mixed
     */
    function encriptar($plaintext) {

        $llaveAleatoriaEncriptada = "";
        $llaveAleatoria = sha1(microtime(true));
        $vectorInicializacion = $this->generarIV($llaveAleatoria);

        $crypttext = "";
        $pub_key = "";
        if (!$this->getPublicKey($pub_key)) {
            error_log("Error al encriptar");
            return false;
        }

        $data = base64_encode(serialize($plaintext));

        openssl_public_encrypt($llaveAleatoria, $llaveAleatoriaEncriptada, $pub_key);

        if ($llaveAleatoriaEncriptada === false) {
            error_log("openssl_public_encrypt: No fue posible encriptar la llave");
            while ($msg = openssl_error_string()) {
                error_log("openssl_public_encrypt: $msg");
            }
            return false;
        }

        $datosEncriptados = openssl_encrypt($data, $this->cMethod, $llaveAleatoria, true, $vectorInicializacion);

        if ($datosEncriptados === false) {
            error_log("openssl_encrypt: No fue posible encriptar la variable: " . var_export($plaintext, true));
            while ($msg = openssl_error_string()) {
                error_log("openssl_encrypt: $msg");
            }
            return false;
        }

        $crypttext = array($datosEncriptados, $llaveAleatoriaEncriptada);
        $crypttext = serialize($crypttext);

        return $this->urlsafe_b64encode($crypttext);
    }

    /**
     * Desencripta un data
     * @param String $source
     * @return boolean|Mixed
     */
    function desencriptar($source, $utilizarLLavePublic = FALSE) {

        $llaveAleatoria = "";

        $partes = unserialize($this->urlsafe_b64decode($source));

        if ($partes === false) {
            error_log("desencriptar: No se recibieron datos validos para desencriptar: " . var_export($source, true));
            return false;
        }

        $llaveAleatoriaEncriptada = $partes[1];
        $crypttext = $partes[0];

        $priv_key = "";
        $pub_key = "";
        $plainText = "";

        if ($utilizarLLavePublic) {
            if (!$this->getPublicKey($pub_key)) {
                error_log("Error al desencriptar publica ");
                return false;
            }
            openssl_public_decrypt($llaveAleatoriaEncriptada, $llaveAleatoria, $pub_key);
        } else {
            if (!$this->getPrivateKey($priv_key)) {
                error_log("Error al desencriptar privada");
                return false;
            }
            openssl_private_decrypt($llaveAleatoriaEncriptada, $llaveAleatoria, $priv_key);
        }

        if ($llaveAleatoria === false) {
            error_log("openssl_private_decrypt: No fue posible desencriptar la llave");
            while ($msg = openssl_error_string()) {
                error_log("openssl_private_decrypt: $msg");
            }
            return false;
        }

        $vectorInicializacion = $this->generarIV($llaveAleatoria);

        $plainText = openssl_decrypt($crypttext, $this->cMethod, $llaveAleatoria, true, $vectorInicializacion);

        if ($plainText === false) {
            error_log("openssl_decrypt: Error al desencriptar");
            while ($msg = openssl_error_string()) {
                error_log("openssl_decrypt: $msg");
            }
            return false;
        }

        $plainText = unserialize(base64_decode($plainText));

        return $plainText;
    }

    /**
     * Permite convertir un base 64 a algo seguro para ser usado por url
     * @param type $string
     * @return type
     */
    function urlsafe_b64encode($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        return $data;
    }

    /**
     * Inverso de urlsafe_b64encode
     * @param type $string
     * @return type
     */
    function urlsafe_b64decode($string) {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

}

?>
