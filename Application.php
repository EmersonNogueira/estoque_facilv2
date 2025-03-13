<?php

class Application {

    const DEFAULT = 'Item';

    public function run() {
        // Pega a URL, removendo barras no início e no final
        $url = isset($_GET['url']) ? explode('/', trim($_GET['url'], '/')) : [self::DEFAULT, 'index'];

        // Define o nome do controlador e método com base no tipo de requisição
  
            $controllerName = ucfirst($url[0]);
            $method = isset($url[1]) ? $url[1] : 'index';
            $param = isset($url[2]) ? $url[2] : null;
            $param2 = isset($url[3]) ? $url[3] : null;        
            $param3 = isset($url[4]) ? $url[4] : null;        

    
        $class = 'controllers\\' . $controllerName . 'Controller';
        $view = 'views\\' . $controllerName . 'View';
        $model = 'models\\' . $controllerName . 'Model';

        // Verifica se as classes de controlador, visualização e modelo existem
        if (class_exists($class) && class_exists($view) && class_exists($model)) {
            $controller = new $class(new $view, new $model);

            if (method_exists($controller, $method)) {
                // Chama o método do controlador com ou sem parâmetro
                $controller->$method($param,$param2,$param3);
            } else {
                die('Método não encontrado.');
            }
        } else {
            die('Controlador, visualização ou modelo não encontrados.');
        }
    }
}

?>