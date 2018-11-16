<?php

class ReporteDinamico extends Controller {

    private $rd;
    private $onlyHeaders = true;
    private $menuReportes;

    public function __construct($poroto) {
        parent::__construct($poroto);
        include($this->POROTO->ModelPath . '/reportedinamico.php');
        $this->rd = new ReporteDinamicoModel($this->POROTO);
    }

    function defentry() {
        if ($this->POROTO->Session->isLogged()) {
            $this->reportes();
        } else {
            include($this->POROTO->ViewPath . "/-login.php");
        }
    }

    public function reportes() {
        if (!$this->ses->tienePermiso('', 'Visualizar Informes')) {
            $this->ses->setMessage("Acceso denegado. Contactese con el administrador.", SessionMessageType::TransactionError);
            header("Location: /", TRUE, 302);
            exit();
        }

        $params = array();
        $params["funcion"] = "";
        $params["nombre"] = "";
        $params["menuReportes"] = $this->rd->getMenuReportes();
        $this->render("/reporte-dinamico.php", $params);
    }

    public function ejecutar($reporte = null) {
        if ($reporte != null) {
            if ($reporte == "consultaManual") {
                $this->consultaManual();
            }
            if (isset($_POST["datatable"]) && $_POST["datatable"]) {
                echo json_encode($this->convertDataReponse($this->rd->$reporte(false)));
                exit;
            }
            $params = array();
            $params["funcion"] = $reporte;
            $params["nombre"] = "";
            $params["menuReportes"] = $this->rd->getMenuReportes();
            foreach ($params["menuReportes"] as $clave => $valor) {
                if ($valor["funcion"] == $reporte) {
                    $params["nombre"] = $valor["nombre"];
                }
            }
            $params["encabezados"] = $this->rd->$reporte(true);
            $this->render("/reporte-dinamico.php", $params);
        } else {
            $this->reportes();
        }
    }

    public function consultaManual() {
        if (isset($_POST["datatable"]) && $_POST["datatable"]) {
            echo json_encode($this->convertDataReponse($this->rd->consultaManual(false, $_SESSION["query"])));
            unset($_SESSION["query"]);
            exit();
        }
        if (isset($_POST["query"]) && $_POST["query"] != "") {

            if (strpos(strtoupper($_POST["query"]), 'INSERT') !== false || strpos(strtoupper($_POST["query"]), 'UPDATE') !== false || strpos(strtoupper($_POST["query"]), 'DELETE') !== false) {
                $this->ses->setMessage("No esta permitido usar las palabras claves INSERT, UPDATE, DELETE.", SessionMessageType::TransactionError);
                $this->reportes();
                exit();
            }
            $_SESSION["query"] = $_POST["query"];
            $params = array();
            $params["funcion"] = "consultaManual";
            $params["nombre"] = "Consulta Manual";
            $params["menuReportes"] = $this->rd->getMenuReportes();
            try {
                $params["encabezados"] = $this->rd->consultaManual(true, $_SESSION["query"]);
                $this->render("/reporte-dinamico.php", $params);
            } catch (PDOException $e) {
                $this->ses->setMessage("Ocurrio un error al inscribir el area. <br/>Error: " . $e->getMessage(), SessionMessageType::TransactionError);
                $this->reportes();
                exit();
            }
        } else {
            $this->reportes();
            exit();
        }
    }

    public function convertDataReponse($params) {
        $i = 1;
        foreach ($params as $key => $value) {
            $temp = array();
            $tempKey;
            $temp["index"] = $i++;
            foreach ($value as $key => $value) {
                $temp[$key] = $value;
            }
            $response["data"][] = array_values($temp);
        }
        return $response;
    }

}
