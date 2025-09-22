<?php
namespace controllers;

class Controller {

    protected $view;
    protected $model;
    protected $base_url;

    public function __construct($view, $model) {
        $this->view = $view;
        $this->model = $model;

        // Inicia a sessão se ainda não estiver iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Define a base URL dependendo do ambiente
        if ($_SERVER['HTTP_HOST'] == 'localhost') {
            $this->base_url = '/estoque_facil/';
        } else {
            $this->base_url = '/';
        }
    }

    public function index(){}
}
?>
