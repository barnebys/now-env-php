<?php

/**
 * Class NowEnv
 */
class NowEnv {

    private $baseDir;

    private $secrets = [];

    private $required = [];

    private $env = [];

    /**
     * NowEnv constructor.
     */
    public function __construct()
    {
        $this->setBaseDir();
        $this->loadSecrets();
        $this->loadRequired();
        $this->loadNowJSON();

        $this->applyEnv();
    }

    private function setBaseDir()
    {
        $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
        $baseDir = realpath(dirname(dirname($reflection->getFileName())) . '/../');

        $this->baseDir = $baseDir;
    }

    /**
     * Load secrets
     */
    private function loadSecrets()
    {
        $secretsPath = realpath( $this->baseDir . '/now-secrets.json');

        if ($secretsPath) {
            $this->secrets = json_decode(file_get_contents($secretsPath));
        }
    }

    /**
     * Load required
     */
    private function loadRequired()
    {
        $requiredPath = realpath($this->baseDir . '/now-required.json');

        if ($requiredPath) {
            $this->required = json_decode(file_get_contents($requiredPath));
        }

    }

    /**
     * Load Now JSON
     *
     * @return bool
     */
    private function loadNowJSON()
    {
        $nowPath = realpath($this->baseDir . '/now.json');

        if ($nowPath === false) {
            return false;
        }

        $nowFile = json_decode(file_get_contents($nowPath));

        if (isset($nowFile->env)) {
            $this->env = $nowFile->env;
        }
    }

    /**
     * Apply env variaböes
     *
     * @throws Exception
     */
    private function applyEnv()
    {
        foreach ($this->env as $key => $value) {
            if (getenv($key) === false) {
                $value = $this->getValue($key);
                putenv("$key=$value");
            }
        }
    }

    /**
     * Get value from required, now env or secrets
     *
     * @param $key
     * @return string|null
     * @throws Exception
     */
    private function getValue($key)
    {
        $value = '';

        if (isset($this->required->{$key})) {
            $value = $this->required->{$key};
        } elseif (isset($this->env->{$key})) {
            $value = $this->env->{$key};
        } else {
            throw new \Exception("The environment variable $key is required.");
        }

        if (substr($value, 0, 1) !== "@") {
            return $value;
        }

        if (isset($this->secrets->{$value})) {
            return $this->secrets->{$value};
        }

        return null;
    }

    public static function config()
    {
        return new self;
    }
}
