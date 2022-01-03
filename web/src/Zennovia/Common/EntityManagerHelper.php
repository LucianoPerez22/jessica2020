<?php

namespace App\Zennovia\Common;

use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Clase que permite abstraer comportamientos a todas las entidades
 */
class EntityManagerHelper
{
    /**
     * @var ValidatorInterface
     */
    private $validator     = null;

    /**
     * @var ObjectManager
     */
    protected $entityManager = null;

    /**
     * @var array
     */
    protected $errors = array();

    public function __construct(ObjectManager $em, ValidatorInterface $validator)
    {
        $this->entityManager = $em;
        $this->validator = $validator;
    }

    /**
     * Setea una instancia del objeto ObjectManager
     * @param ObjectManager $entityManager
     */
    public function setEntityManager(ObjectManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * getEntityManager
     * @return ObjectManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Gets the value of errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Sets the value of errors.
     *
     * @param array $errors the errors
     * @return EntityManagerHelper
     */
    public function setErrors(Array $errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * Add error to array
     *
     * @param Mixed $error
     * @return EntityManagerHelper
     */
    public function addError($error)
    {
        $this->errors[] = $error;

        return $this;
    }

    /**
     * Setea una instancia del objeto Validator
     *
     * @param ValidatorInterface $validator
     * @return EntityManagerHelper
     */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * getValidator
     * @return ValidatorInterface
     */
    public function getValidator()
    {
        return $this->validator;
    }

    public function getRespository($name, $bundle = null)
    {
        if(!is_null($bundle)){
            $name = $bundle.':'.$name;
        }

        return $this->getEntityManager()->getRepository($name);
    }

    public function isValidEntity($entity)
    {
        $this->errors = $this->getValidator()->validate($entity);

        return (!count($this->errors) > 0);
    }

    public function validateAndSaveEntity($entity, $andFlush = true)
    {
        $isValidEntity = $this->isValidEntity($entity);

        if($isValidEntity){
            return $this->doSave($entity, $andFlush);
        }

        return $isValidEntity;
    }

    /**
     *  This is basic save function. Child entity can overwrite this.
     * @param object $entity
     * @param boolean $andFlush
     * @return bool
     * @throws \Exception
     */
    public function doSave($entity, $andFlush = true)
    {
        try{
            $this->getEntityManager()->persist($entity);
            if ($andFlush) {
                $this->getEntityManager()->flush();
            }

            return true;
        }
        catch(\Exception $e){
            $this->addError($e);
            throw $e;
        }
    }

    /**
     * doDelete, Elimina la entidad
     * @param  object $entity , entidad a eliminar
     * @param  boolean $andFlush , opción para decidir si realizar el flush
     * @return bool , exito de la operación
     * @throws \Exception
     */
    public function doDelete($entity, $andFlush = true)
    {
        try{
            $this->getEntityManager()->remove($entity);
            if ($andFlush) {
                $this->getEntityManager()->flush();
            }
            return true;
        }
        catch(\Exception $e){
            $this->addError($e);
            throw $e;
        }
    }

    /**
     * flush
     */
    public function flush()
    {
        $this->getEntityManager()->flush();
    }

    /**
     * clear
     * @param string|null $entityName if given, only entities of this type will get detached
     * @return void
     */
    public function clear($entityName = null)
    {
        $this->getEntityManager()->clear($entityName);
    }

    /**
     * flush and clear
     */
    public function flushAndClear()
    {
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
    }

}
