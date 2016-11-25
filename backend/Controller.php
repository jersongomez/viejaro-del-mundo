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
            'email' => strtoupper(trim($this->request["email"])),
            'accept_habeas_data' => isset($this->request["condiciones"]) ? TRUE : FALSE,
            'suscribe_newsletter' => FALSE,
            'applicaction_token' => MDM_TOKEN,
            'language' => strtoupper("ES"),
            'country' => strtoupper(trim($this->request["sigla"])),
            'ip' => $functions->getRealIP(),
            'agent' => $_SERVER['HTTP_USER_AGENT'],
        );

        $hwc = new HWInternalCrypt(PATH_PUB_MDM_KEY);
        $_d = $hwc->encriptar($habeasData);

        $respuestaWS = $functions->apiRestCall("PUT", RUTA_MDM, $_d);

        echo json_encode(array("codigo" => isset($respuestaWS->code) ? $respuestaWS->code : 500));
    }

}
