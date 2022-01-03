<?php

namespace App\Zennovia\Common;

use App\Zennovia\Common\EntityManagerHelper;
use App\Zennovia\Common\FormModelInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Clase que permite abstraer comportamientos a todos los handlers
 * que se asocian a un formulario.
 */
abstract class BaseFormHandler
{
    protected $classFormType = 'FormType';

    /**
     * @var AbstractType
     */
    protected $form;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var Object
     */
    protected $model;

    /**
     * @var EntityManagerHelper
     */
    protected $entityManagerHelper;

    /**
     * @var Object
     */
    protected $entity;

    public function __construct(FormFactoryInterface $formFactory, EntityManagerHelper $entityManagerHelper)
    {
        $this->formFactory = $formFactory;
        $this->entityManagerHelper = $entityManagerHelper;
    }

    /**
     * setEntity
     * Este metodo se usara cuando el model no sea un entidad.
     * Tiene que hacer referencia a la entidad que se quiere cambiar
     * de la base de datos.
     *
     * @param entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * getEntity
     * @return Object
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * setEntityManagerHelper
     * @param entityManagerHelper $entityManagerHelper
     * @return BaseFormHandler;
     */
    public function setEntityManagerHelper($entityManagerHelper)
    {
        $this->entityManagerHelper = $entityManagerHelper;

        return $this;
    }

    /**
     * getEntityManagerHelper
     * @return entityManagerHelper
     */
    public function getEntityManagerHelper()
    {
        return $this->entityManagerHelper;
    }

    /**
     * setFormFactory
     * @param FormFactoryInterface $formFactory
     */
    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * getFormFactory
     * @return FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * setForm, setea el formulario a utilizar
     * @param AbstractType $form
     */
    public function setForm($form)
    {
        $this->form = $form;
    }

    /**
     * getForm
     * @return AbstractType
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * setDataForm, setea los datos en el formulario
     * @param object $formModel
     */
    public function setDataForm($formModel)
    {
        $this->form->setData($formModel);
    }

    /**
     * getDataForm
     * @return mixed
     */
    public function getDataForm()
    {
        return $this->form->getData();
    }

    /**
     * setModel
     *
     * @param Object $model, objeto de la clase asociada al formulario
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * getEntity
     * @return Object
     */
    public function getModel()
    {
        return $this->model;
    }

    public function isSubmittedAndIsValidForm($request)
    {
        $this->form->handleRequest($request);

        return ($this->form->isSubmitted() && $this->form->isValid());
    }

    /**
     * validateForm, realiza el submit del formulario y
     * valida si la operación se realizó con éxito.
     *
     * @param  mixed $request, el request a manipular
     * @return boolean, exito de la operación
     */
    public function validateForm($request)
    {
        $this->form->handleRequest($request);

        return ($this->form->isValid());
    }

    /**
     * createForm, setea una instancia del formulario indicado,
     * seteándole los datos del objeto de dominio (FormModel)
     *
     * @param  Object $model , objeto del cual se cargan los datos en el form (Clase asociada en con el formulario)
     * @param array $options
     */
    public function createForm($model = null, array $options = array())
    {
        $this->form = $this->getFormFactory()->create($this->classFormType, $model, $options);

        //seteo el modelo
        $this->setModel($model);
    }

    /**
     * saveDataForm
     * Guarda los datos del formulario en la base de datos.
     *
     * Si el modelo es un formModel se tiene que setear la entidad a la cual pertenecen los cambios
     */
    protected function saveDataForm()
    {
        $data = $this->getModel();

        $class = FormModelInterface::class;
        if($data instanceof $class) {
            //el modelo es el encargado de popular la entidad con los cambios
            $data->saveData($this->getEntity());
            $data = $this->getEntity(); //Se cambia FormModel por Entity
        }

        /* persistir la entidad */
        return $this->getEntityManagerHelper()->doSave($data);
    }

    /**
     *  Generalmente esta funcion hace esto, pero puede redefinirse
     *  en la subclase si necesita procesar alguna otra información
     */
    public function processForm()
    {
        try{
            $this->preProcessForm();
            $this->saveDataForm();
            $this->postProcessForm();

            return true;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * funcion que sirve para modificar el modelo antes de ser guardado
     * ejem eliminar relaciones viejas, codificar alguna password, etc
     */
    public function preProcessForm()
    {
        $this->setModel($this->getDataForm());

        return true;
    }

    /**
     * funcion que se invocará luego de que se guarde el modelo
     * y por consiguiente la entidad.
     * util para enviar mails de confirmación, etc.
     */
    public function postProcessForm()
    {
        return true;
    }

    /**
     * processTransactionForm
     * Procesa el formulario dentro de una transacción
     * @return bool
     * @throws \Exception
     */
    public function processTransactionForm()
    {
        $em = $this->getEntityManagerHelper()->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            $success = $this->processForm();
            $em->getConnection()->commit();
            $this->getEntityManagerHelper()->flush();
            return $success;
        }
        catch (\Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }
    }

    /**
     * setClassFormType, setea la clase del formulario a manipular
     * @param string $classFormType, nombre completo de la clase del formulario Ej: App\Form\Type\NameFormType
     * @return $this
     */
    public function setClassFormType($classFormType)
    {
        $this->classFormType = $classFormType;

        return $this;
    }
}
