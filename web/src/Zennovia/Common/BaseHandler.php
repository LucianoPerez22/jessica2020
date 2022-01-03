<?php

namespace App\Zennovia\Common;

/**
 * Clase que permite abstraer comportamientos a todos
 * los handlers NO asociados a un formulario.
 *
 * @author cgaleano
 */
abstract class BaseHandler
{

  /**
   * @var Request
   */
  protected $request;

  /**
   * @var EntityManagerHelper
   */
  protected $entityManagerHelper;

  /**
   * setEntityManagerHelper
   * @param entityManagerHelperInterface $entityManagerHelper
   */
  public function setEntityManagerHelper($entityManagerHelper)
  {
      $this->entityManagerHelper = $entityManagerHelper;
  }

  /**
   * getEntityManagerHelper
   * @return entityManagerHelper
   */
  public function getEntityManagerHelper()
  {
      return $this->entityManagerHelper;
  }



}
