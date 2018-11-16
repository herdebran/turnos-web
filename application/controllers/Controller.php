<?php

class Controller {

    protected $POROTO;
    protected $ses;
    protected $app;

    function __construct($poroto) {
        $this->POROTO = $poroto;
        $this->ses = $poroto->Session;
        include($this->POROTO->ModelPath . '/aplicacion.php');
        $this->app = new Aplicacion($poroto);

        $this->POROTO->pageHeader[] = array("label" => "Dashboard", "url" => "");
    }

    function defentry() {
        if ($this->POROTO->Session->isLogged()) {
            header("Location: /", TRUE, 302);
        } else {
            include($this->POROTO->ViewPath . "/-login.php");
        }
    }

    function render($vista, $params = array()) {
        $arrMenu = $this->app->getMenu();
        include($this->POROTO->ViewPath . "/-header.php");
        include($this->POROTO->ViewPath . $vista);
        include($this->POROTO->ViewPath . "/-footer.php");
    }
    
    function renderSinMenu($vista, $params) {
        $arrMenu = $this->app->getMenu();
        include($this->POROTO->ViewPath . "/-header.php");
        include($this->POROTO->ViewPath . $vista);
        include($this->POROTO->ViewPath . "/-footer.php");
    }

}
