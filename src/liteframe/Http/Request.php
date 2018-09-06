<?php

namespace LiteFrame\Http;

use LiteFrame\Http\Request\Header;
use LiteFrame\Http\Request\UploadedFile;
use LiteFrame\Http\Routing\Route;
use LiteFrame\Storage\Server;
use LiteFrame\Storage\Session;
use LogicException;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use function config;

/**
 * Description of Request.
 *
 * @author Victor Anuebunwa
 */
final class Request {

    protected static $instance;
    protected $routeUrl;
    protected $route;
    protected $appURL;
    protected $baseDir;
    protected $ajax;
    protected $content;
    protected $request;
    protected $routeParams = [];

    private function __construct()
    {
        //Check route parameters
        $route = $this->getRoute();
        if ($route) {
            $this->routeParams = $route->getParameters();
        }

        $this->request = SymfonyRequest::createFromGlobals();
        $this->request->attributes->add($this->routeParams);
    }

    /**
     * Return singleton class instance.
     *
     * @return Request
     */
    public static function getInstance()
    {
        if (empty(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * 
     * @return SymfonyRequest
     */
    function getRequest() {
        return $this->request;
    }

    /**
     * Get request route.
     *
     * @return string
     */
    public function getRouteURL()
    {
        if (empty($this->routeUrl)) {
            $param = trim(Server::get('REQUEST_URI'), '/');

            // strip base directory from request url
            $base_dir = $this->getBaseDir();
            $param = substr($param, strlen($base_dir));

            // Strip query string (?a=b) from Request Url
            $path = trim(explode('?', $param)[0], '/');
            $this->routeUrl = strtolower($path);

            if (empty($this->routeUrl)) {
                $this->routeUrl = '/';
            }
        }

        return $this->routeUrl;
    }

    /**
     * Get application base directory.
     *
     * @return type
     */
    public function getBaseDir()
    {
        if (empty($this->baseDir)) {
            $this->baseDir = trim(str_replace('index.php', '', Server::get('SCRIPT_NAME')), '/');
        }

        return $this->baseDir;
    }

    /**
     * Get application URL.
     *
     * @return type
     */
    public function getAppURL()
    {
        if (empty($this->appURL)) {
            $hostname = $this->getHostname();
            $protocol = $this->getProtocol();
            if (empty($hostname) || empty($protocol)) {
                $this->appURL = config('app.url');
            } else {
                $this->appURL = $protocol . '://' . $hostname . '/' . $this->getBaseDir();
            }
        }

        return $this->appURL;
    }

    public function getProtocol()
    {
        return $this->request->getProtocolVersion();
//        return Server::getProtocol();
    }

    public function getHostname()
    {
        return $this->request->getHttpHost();
//        return Server::getHostname();
    }

    public function getMethod()
    {
        return $this->request->getMethod();
//        return Server::getMethod();
    }

    /**
     * Current Route object.
     *
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    public function setRoute(Route $route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get request parameters
     * @param string $key
     * @param mixed $default
     * @return array|string
     */
    public function input($key = null, $default = null)
    {
        return $this->request->get($key, $default);
    }

    /**
     * Check if uploaded file exists
     * @param string $name
     * @return boolean
     */
    public function hasFile($name)
    {
        return $this->request->files->has($name);
//        //See if it makes sense to use dot notation
//        if (isset($_FILES[$name])) {
//            $file = $_FILES[$name];
//            return isset($file['tmp_name']) && file_exists($file['tmp_name']) && is_uploaded_file($file['tmp_name']);
//        }
//        return false;
    }

    /**
     * Get uploaded file
     * @param string $name
     * @return UploadedFile Uploaded file
     */
    public function file($name)
    {
        return new UploadedFile($name);
    }

    /**
     * Get response object
     * @return Response
     */
    public function response()
    {
        return Response::getInstance();
    }

    /**
     * Get session
     * @return Session
     */
    public function session()
    {
        return Session::getInstance();
    }

    /**
     * Determine if the request is the result of an PJAX call.
     *
     * @return bool
     */
    public function pjax()
    {
        return Header::get('X-PJAX') == true;
    }

    public function ajax()
    {
        if (!isset($this->ajax)) {
            $rw = Header::get('HTTP_X_REQUESTED_WITH');
            $this->ajax = !empty($rw) && strtolower($rw) === 'xmlhttprequest';
        }

        return $this->ajax;
    }

    /**
     * Determine if the request is sending JSON.
     *
     * @return bool
     */
    public function isJson()
    {
        $contentType = Header::get('CONTENT_TYPE');

        return stripos($contentType, '/json') !== false ||
                stripos($contentType, '+json') !== false;
    }

    /**
     * Determine if the current request probably expects a JSON response.
     *
     * @return bool
     */
    public function expectsJson()
    {
        return ($this->ajax() && !$this->pjax()) || $this->wantsJson();
    }

    /**
     * Determine if the current request is asking for JSON in return.
     *
     * @return bool
     */
    public function wantsJson()
    {
        $acceptable = Header::get('Accept');

        return !empty($acceptable) && (stripos($acceptable, '/json') !== false ||
                stripos($acceptable, '+json') !== false);
    }

    /**
     * Returns the request body content.
     *
     * @param bool $asResource If true, a resource will be returned
     *
     * @return string|resource The request body content or a resource to read the body stream
     *
     * @throws LogicException
     *
     * @author Laravel
     */
    public function getContent($asResource = false)
    {
        $currentContentIsResource = is_resource($this->content);
        if (PHP_VERSION_ID < 50600 && false === $this->content) {
            throw new LogicException('getContent() can only be called once when using the resource return type and PHP below 5.6.');
        }

        if (true === $asResource) {
            if ($currentContentIsResource) {
                rewind($this->content);

                return $this->content;
            }

            // Content passed in parameter (test)
            if (is_string($this->content)) {
                $resource = fopen('php://temp', 'r+');
                fwrite($resource, $this->content);
                rewind($resource);

                return $resource;
            }

            $this->content = false;

            return fopen('php://input', 'rb');
        }

        if ($currentContentIsResource) {
            rewind($this->content);

            return stream_get_contents($this->content);
        }

        if (null === $this->content || false === $this->content) {
            $this->content = file_get_contents('php://input');
        }

        return $this->content;
    }

    public function __get($name)
    {
        return $this->input($name);
    }
}
