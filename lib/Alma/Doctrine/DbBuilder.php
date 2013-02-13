<?php
namespace Alma\Doctrine;

use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver,
    Doctrine\Common\Annotations\SimpleAnnotationReader,
    Doctrine\ODM\MongoDB\Configuration,
    Doctrine\ODM\MongoDB\DocumentManager,
    Doctrine\MongoDB\Connection,
    Doctrine\ORM\Tools\Setup,
    Doctrine\ORM\EntityManager,
    Zend_Config;

/**
 * @abstract
 * @author NMO <nico@multeegaming.com> 
 */
abstract class DbBuilder {

    public static function constructOdm(Zend_Config $config) {
        $odmConfig = new Configuration();

        // Could be really nice to be able to init from an array ....
        if(isset($config->options))
            foreach ($config->options->toArray() as $key => $value) 
                $odmConfig->{"set" . ucfirst($key)}($value);

        // To allow simple annotations
        $reader = new SimpleAnnotationReader();
        $reader->addNamespace($config->reader_namespace);

        // Static call to autoload annotation classes ...
        AnnotationDriver::registerAnnotationClasses();

        $driver = new AnnotationDriver($reader, $config->documents_path);
        $odmConfig->setMetadataDriverImpl($driver);

        $connectionOptions = (isset($config->connection->options)) ? $config->connection->options->toArray() : array();
        $connection = new Connection($config->connection->server, $connectionOptions);

        return DocumentManager::create($connection, $odmConfig);
    }

    public static function constructOrm(Zend_Config $config) {
        $ORMOptions = Setup::createAnnotationMetadataConfiguration($config->options->toArray(), true);
        return EntityManager::create($config->connection->toArray(), $ORMOptions);
    }

}
