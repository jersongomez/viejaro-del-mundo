<?php

class Controller
{

    private $action;
    private $request;

    public function __construct()
    {
        $this->request = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $_GET;

        $this->action = (isset($this->request["action"]) && $this->request["action"] != "") ? $this->request["action"] : "index";
        call_user_func(array($this, $this->action));
    }

    private function index()
    {
        include 'index.html';
    }

    private function callMDM()
    {

        $functions = new Functions();

        $habeasData = array(
            'first_name' => trim($this->request["nombre"]),
            'last_name' => trim($this->request["apellido"]),
            'dni' => trim($this->request["documento"]),
            'phone' => trim($this->request["telefono"]),
            'mobile' => trim($this->request["mobile"]),
            'email' => strtoupper(trim($this->request["email"])),
            'accept_habeas_data' => isset($this->request["condiciones"]) ? TRUE : FALSE,
            'suscribe_newsletter' => FALSE,
            'applicaction_token' => MDM_TOKEN,
            'language' => strtoupper("ES"),
            'country' => strtoupper(trim($this->request["sigla"])),
            'ip' => $functions->getRealIP(),
            'agent' => $_SERVER['HTTP_USER_AGENT'],
            'campaign' => $this->request["campaign"],
            'html' => $this->request["form"],
            'area' => $this->request["ciudad"]
        );

        $hwc = new HWInternalCrypt(PATH_PUB_MDM_KEY);
        $_d = $hwc->encriptar($habeasData);

        $respuestaWS = $functions->apiRestCall("PUT", RUTA_MDM, $_d);

        error_log(var_export($respuestaWS, TRUE));

        echo json_encode(array("codigo" => isset($respuestaWS->code) ? $respuestaWS->code : 500));
    }

    /**
     * Obtengo las ciudades
     */
    public function getCities()
    {
        $strConnection = "host=" . NOMBRE_HOST . " dbname=" . NOMBRE_BD . " user=" . USUARIO . " password=" . PASSWORD;
        $connect = pg_connect($strConnection) or die("No fue posible conectarse a la base de datos");
        $result = pg_query_params($connect, "SELECT * FROM tblciu WHERE pai_cod=$1 AND tipdet_cod=$2", array(42, 1));
        $cityNames = array();

        while ($cities = pg_fetch_object($result)) {
            $cityName = ucfirst(strtolower(utf8_encode(preg_replace("/\s+?\(.*\)/", "", $cities->ciu_des))));
            $cityNames[] = $cityName;
        }

        try {
            $fileName = RUTA_JSON . FILE_CITIES;
            $open = fopen($fileName, "a+");
            fwrite($open, json_encode($cityNames));
            fclose($open);
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }

        pg_close($connect);
    }

}
