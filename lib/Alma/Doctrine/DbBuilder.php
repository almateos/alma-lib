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
        $ODMConfig = new Configuration();
        if(isset($config->options)) {
            foreach ( $config->options->toArray() as $key => $value) {
                $ODMConfig->{"set" . ucfirst($key)}($value);
            }
        }

        $reader = new SimpleAnnotationReader();
        $reader->addNamespace($config->reader_namespace);
        AnnotationDriver::registerAnnotationClasses();

        $driver = new AnnotationDriver($reader, $config->documents_path);
        if(isset($config->driver)) {
            foreach ($config->driver->toArray() as $key => $value) { $driver->{"set" . ucfirst($key)}($value); }
        }
        $ODMConfig->setMetadataDriverImpl($driver);

        if($config->connection->options) {
            if($config->connection->options->bool) {
                foreach ($config->connection->options->bool as $key => $option) { $config->connection->options->$key = (bool) $option; }
                unset($config->connection->options->bool);
            }
        }
        $connectionOptions = ($config->connection->options) ? $config->connection->options->toArray() : array();
        $connection = new Connection($config->connection->server, $connectionOptions);

        return DocumentManager::create($connection, $ODMConfig);
    }

    public static function constructOrm(Zend_Config $config) {
        $ORMOptions = Setup::createAnnotationMetadataConfiguration($config->options->toArray(), true);
        return EntityManager::create($config->connection->toArray(), $ORMOptions);
    }

}
