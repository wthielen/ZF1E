<?php

/**
 * An application plugin resource for connecting to a MongoDB server
 */
class ZFE_Resource_Mongo extends Zend_Application_Resource_ResourceAbstract
{
    private $connection;

    private $socket = false;
    private $host;
    private $port;
    private $username;
    private $password;
    private $database;

    private $mapping = array();

    /**
     * Initialize the plugin
     *
     * When no application options have been specified in the config.ini, then
     * it will use the default values from php.ini
     */
    public function init()
    {
        $o = $this->getOptions();

        if (empty($o['database']))
        {
            throw new Zend_Application_Resource_Exception('Please specify at least the Mongo database to use: resources.mongo.database');
        }

        if (isset($o['socket'])) $this->socket = $o['socket'];

        $this->host = isset($o['host']) ? $o['host'] : ini_get('mongo.default_host');
        $this->port = isset($o['port']) ? $o['port'] : ini_get('mongo.default_port');
        $this->username = isset($o['username']) && !empty($o['username']) ? $o['username'] : null;
        $this->password = isset($o['password']) && !empty($o['password']) ? $o['password'] : null;
        $this->authSource = isset($o['authSource']) && !empty($o['authSource']) ? $o['authSource'] : null;
        $this->database = $o['database'];

        if (isset($o['mapping']) && is_array($o['mapping'])) {
            $this->mapping = array_merge($this->mapping, $o['mapping']);
        }

        return $this;
    }

    /**
     * Creates the connection URI, the MongoClient instance, and returns
     * the MongoDB instance from it.
     *
     * The PECL Mongo PHP library already does persistent connections since 
     * version 1.2, but here it is just for saving the process of composing 
     * the URI every time.
     */
    public function getDatabase()
    {
        if (is_null($this->connection)) {
            $uri = "mongodb://";
            $options = array("connect" => TRUE);
            if ($this->username && $this->password) {
                $options["username"] = $this->username;
                $options["password"] = $this->password;
                $options["db"] = $this->database;
                if ($this->authSource) {
                    $options["authSource"] = $this->authSource;
                }
            }

            $uri .= $this->socket ? $this->socket : $this->host . ":" . $this->port;
            $this->connection = new MongoClient($uri, $options);
        }

        return $this->connection->{$this->database};
    }

    /**
     * Returns the class name for the given collection name.
     * If it is mentioned in the mapping configuration, it will use
     * the mapping's setting for not-so-obvious mappings. This can be
     * configured in the application's configuration file.
     */
    public function getClass($collectionName)
    {
        if (isset($this->mapping[$collectionName])) {
            $cls = $this->mapping[$collectionName];
        } else {
            $cls = ZFE_Environment::getResourcePrefix('model') . '_' . ucfirst($collectionName);
        }

        return $cls;
    }

    public function addMapping($collectionName, $className)
    {
        $this->mapping[$collectionName] = $className;
    }

    public function addMappings($mappings)
    {
        foreach($mappings as $coll => $cls) {
            $this->addMapping($coll, $cls);
        }
    }
}
