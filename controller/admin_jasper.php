<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if( !function_exists('fs_jasper') )
{
   require_once 'plugins/jasper/functions.php';
}

/**
 * Description of admin_jasper
 *
 * @author carlos
 */
class admin_jasper extends fs_controller
{
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Jasper', 'admin');
   }
   
   protected function private_core()
   {
      $pdf_location = fs_jasper('plugins/jasper/reports/facturascripts.jrxml');
      if($pdf_location)
      {
         $this->new_message('<a href="'.$pdf_location.'" target="_blank">PDF</a> generado correctamente.');
      }
      else
         $this->new_error_msg('Error al generar el PDF.');
   }
}
