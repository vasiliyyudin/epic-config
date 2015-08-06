<?php
namespace Epic;

class Config implements \ArrayAccess{

    const TYPE_INI = 'ini';
    const TYPE_JSON = 'json';
    const TYPE_XML = 'xml';
    const TYPE_YAML = 'yaml';
    const TYPE_YML = 'yml';
    const TYPE_ENV = 'env';

    protected $parsers = [];
    public $config = [];

    public function __construct($paths = null)
    {
        if(!is_null($paths)){
            if(!is_array($paths)){
                $paths = [$paths];
            }

            foreach ($paths as $path) {
                $info = pathinfo($path);
                $type = isset($info['extension']) ? $info['extension'] : '';
                $content = file_get_contents($path);

                $this->parse($type, $content, $path);
            }
        }
    }

    public function setParser($type, callable $function)
    {
        $this->parsers[$type] = $function;
    }

    function addExtension($ext, $method) {
        $self = $this;
        $this->parsers[$ext] = function($type, $content) use ($self, $method) {
            $self->parse($method, $content, '');
        };
    }

    public function parse($type, $content, $path)
    {
        $data = [];
        if(array_key_exists($type, $this->parsers)){
            $data = $this->parsers[$type]($path, $content);
        } else {
            switch($type){
                case static::TYPE_INI:
                    $data = $this->parseIni($content);
                    break;
                case static::TYPE_JSON:
                    $data = $this->parseJson($content);
                    break;
                case static::TYPE_XML:
                    $data = $this->parseXml($content);
                    break;
                case static::TYPE_YAML:
                case static::TYPE_YML:
                    $data = $this->parseYml($content);
                    break;
                case static::TYPE_ENV:
                    $data = $this->parseEnv($path);
                    break;
            }
        }

        $this->config = array_replace_recursive($this->config, $data);
    }

    public function parseIni($content)
    {
        $data = parse_ini_string($content, true);
        return (!$data || !is_array($data)) ? [] : $data;
    }

    public function parseJson($content)
    {
        $data = json_decode($content, true);
        return $data;
    }

    public function parseXml($content)
    {
        $data = json_decode(json_encode($content), true);
        return $data;
    }

    public function parseEnv($path)
    {
        $loader = new \josegonzalez\Dotenv\Loader($path);
        return $loader->parse()->toArray();
    }

    public function parseYml($content)
    {
        $data = \Symfony\Component\Yaml\Yaml::parse($content);
        return $data;
    }

    public function get($key, $default = null)
    {
        return isset($this->config[$key]) ?  $this->config[$key] : $default;
    }
    public function set($key, $value)
    {
        $this->config[$key] = $value;
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
    public function offsetExists($offset)
    {
        return !is_null($this->get($offset));
    }
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }
    public function offsetUnset($offset)
    {
        $this->set($offset, null);
    }
}