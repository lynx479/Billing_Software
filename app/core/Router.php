<?php
class Router {
    protected $controller = 'DashboardController';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        if (isset($url[0])) {
            // Handle both creditNotes, credit-notes, and credit_notes
            $formatted = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $url[0])));
            $controllerName = ucfirst($formatted) . 'Controller';

            if (file_exists('../app/controllers/' . $controllerName . '.php')) {
                $this->controller = $controllerName;
                unset($url[0]);
            } elseif (file_exists('../app/controllers/' . ucfirst($url[0]) . 'Controller.php')) {
                $this->controller = ucfirst($url[0]) . 'Controller';
                unset($url[0]);
            }
        }

        require_once '../app/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        if (isset($url[1])) {
            $methodClean = strtok($url[1], '?');
            if (method_exists($this->controller, $methodClean)) {
                $this->method = $methodClean;
                unset($url[1]);
            }
        }

        $this->params = $url ? array_values($url) : [];
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function parseUrl() {
        if (isset($_GET['url'])) {
            $cleanUrl = strtok($_GET['url'], '?');
            return explode('/', filter_var(rtrim($cleanUrl, '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}