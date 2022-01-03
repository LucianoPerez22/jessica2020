<?php
namespace App\Zennovia\Common;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Form;
use Knp\Component\Pager\Paginator;


class FindEntitiesHelper
{
    /**
     * @var Request
     */
    private $request = null;

    /**
     * @var integer
     */
    private $limit = 50;

    /**
     * @var array
     */
    private $filters        = array();

    /**
     * @var array
     */
    private $extraFilters   = array();

    /**
     * @var Paginator
     */
    private $paginator      = null;

    /**
     * @var boolean
     */
    private $withPaginate   = true;

    /**
     * @var boolean
     */
    private $saveInSession  = true;

    /**
     * @var string
     */
    private $actionMethod   = 'GET';

    /**
     * @var string
     */
    private $filterMethod   = 'findAllWithFilterAndOrderQuery';

    /**
     * @var string
     */
    private $unfilterMethod = 'findAllQuery';

    /**
     * @var array
     */
    private $dataForm       =  array();

    public function __construct(RequestStack $requestStack, Paginator $paginator)
    {
        $this->paginator = $paginator;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getFilters()
    {
        return $this->filters;
    }

    // ejemplo extraFilter['user'] = $userLogged, etc
    public function setExtraFilters($extraFilters = array())
    {
        if(!empty($extraFilters)){
            $this->extraFilters = $extraFilters;
        }

        return $this;
    }

    public function setSaveInSession($saveInSession)
    {
        $this->saveInSession = $saveInSession;

        return $this;
    }

    public function getSaveInSession()
    {
        return $this->saveInSession;
    }

    private function mergeExtraFilters()
    {
        if(!empty($this->extraFilters)){
            $this->filters = array_merge($this->filters,$this->extraFilters);
        }

        return $this;
    }

    /**
     * Sets the value of withPaginate.
     *
     * @param mixed $withPaginate the with paginate
     *
     * @return self
     */
    public function setWithPaginate($withPaginate)
    {
        $this->withPaginate = $withPaginate;

        return $this;
    }

    /**
     * Gets the value of withPaginate.
     *
     * @return mixed
     */
    public function getWithPaginate()
    {
        return $this->withPaginate;
    }

    /**
     * Sets the value of paginator.
     *
     * @param mixed $paginator the paginator
     *
     * @return self
     */
    public function setPaginator($paginator)
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * Gets the value of paginator.
     *
     * @return mixed
     */
    public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * funcion que retorna true si no hay filtros
     * seleccionados, de lo contrario retorna false.
     */
    public function isEmptyFilters()
    {
        $currentFilters = array_diff($this->filters, array('filtered' => false));

        return empty($currentFilters);
    }

    /**
     * obtiene las entities en base al filtrado actual, pagina si es necesario y devuelve un array
     * con todos los datos necesarios para la visualización
     * @param $repository
     * @param $formFilter
     * @param array $order
     * @return array
     */
    public function getDataResultFiltered($repository, $formFilter, $order = array())
    {
        $this->filters = $this->processFilters($formFilter);

        $this->mergeExtraFilters();

        $query = $repository->{$this->filterMethod}($this->filters, $order);

        $this->getData($query);

        return $this->filters;
    }

    /**
     * obtiene las entities, pagina si es necesario y devuelve un array
     * con todos los datos necesarios para la visualización
     * @param $repository
     * @param array $order
     * @return array
     */
    public function getDataResult($repository, $order = array())
    {
        $query = $repository->{$this->unfilterMethod}($order);

        $this->getData($query);

        return $this->filters;
    }

    /**
     * getData
     * @param $query
     */
    private function getData($query)
    {
        if($this->getWithPaginate()){
            $this->filters['pager'] = $this->getPaginator();
            $this->filters['entities'] = $this->filters['pager']
                                              ->paginate($query, $this->request->get('page', 1), $this->getLimit());
            $this->filters['totalEntitiesCount'] = $this->filters['entities']->getTotalItemCount();
        }
        else{
            $this->filters['entities'] = $query->getResult();
            $this->filters['totalEntitiesCount'] = $this->filters['entities']->count();
        }

        $this->filters['paginate'] = $this->getWithPaginate();
        $this->filters['filtered'] = (isset($this->filters['filter']))? $this->filters['filter'] : false;
    }

    /**
     * @param Form $form
     * @return array:
     */
    public function processFilters(Form $form)
    {
        // si envia datos de filtrado o si hace un simple paginado
        if($this->request->getMethod() == $this->actionMethod  || $this->request->get('page')){
            if($this->request->get('filter', null) != null){
                $this->saveFormData($form);
            }
        }
        elseif($this->request->get('reset', null) != null || $this->request->get('filter', null) == null){
            $this->clearFilters($form);
        }
        else{ //intento cargar los valores por defecto si los hay
            $this->setAndGetDataDefault($form);
        }

        $data = $this->getFormData($form);

        $form->submit($data);

        // indicará si el formulario tiene un filtro aplicado
        $data['filter'] = false;

        foreach($data as $key => $value){
            if(!empty($value)){ $data['filter'] = true;}
        }

        return $data;
    }

    public function getLimit()
    {
        $this->setLimit();

        return $this->request->getSession()->get('limit');
    }

    public function setLimit($limit = null)
    {
        if(is_null($limit) ){
            $limit = ($this->request->get('limit', null)) ? $this->request->get('limit') : $this->limit;
        }

        $this->limit = $limit;

        $this->request->getSession()->set('limit', $limit);

        return $this;
    }

    private function saveFormData(Form $form)
    {
        if($this->getSaveInSession()){
            $this->request->getSession()->set($form->getName(), $this->request->get($form->getName(), array()));
        }
        else{
            $this->dataForm = $this->request->get($form->getName(), array());
        }
    }

    private function getFormData(Form $form)
    {
        if($this->getSaveInSession()){
            return $this->request->getSession()->get($form->getName(),array());
        }
        else{
            return $this->dataForm;
        }
    }

    private function clearFilters(Form $form)
    {
        $this->request->getSession()->remove($form->getName());
        $dataDefault = self::setAndGetDataDefault($form);

        return $dataDefault;
    }

    /**
     * setAndGetDataDefault
     * carga en session los valores por defecto si los hay
     *
     * @param Form $form
     * @return array
     */
    private function setAndGetDataDefault(Form $form)
    {
        $dataDefault = array();

        foreach ($form->all() as $nameChildren => $children) {
            $dataDefault[$nameChildren] = $children->getViewData();
        }

        $this->request->getSession()->set($form->getName(), $dataDefault);
        return $dataDefault;
    }

}
