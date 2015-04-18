<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
      $pdf_location = fs_jasper('plugins/jasper/reports/Leaf_Violet.jrxml');
      if($pdf_location)
      {
         $this->new_message('<a href="'.$pdf_location.'">PDF</a> generado correctamente.');
      }
      else
         $this->new_error_msg('Error al generar el PDF.');
   }
}
