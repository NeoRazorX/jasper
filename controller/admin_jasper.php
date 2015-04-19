<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015, Carlos García Gómez. All Rights Reserved. 
 */

require_model('jasper.php');

/**
 * Description of admin_jasper
 *
 * @author carlos
 */
class admin_jasper extends fs_controller
{
   public $jasper;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Jasper', 'admin');
   }
   
   protected function private_core()
   {
      $this->jasper = new jasper();
      
      if( isset($_GET['test']) )
      {
         $this->jasper->compile('plugins/jasper/reports/facturascripts.jrxml');
         
         $file_location = $this->jasper->build('plugins/jasper/reports/facturascripts.jasper', $_GET['test']);
         if($file_location)
         {
            $this->new_message('<a href="'.$file_location.'" target="_blank">'.strtoupper($_GET['test']).'</a> generado correctamente.');
         }
         else
            $this->new_error_msg('Error al generar el archivo '.strtoupper($_GET['test']).'.');
         
         foreach($this->jasper->errors as $err)
         {
            $this->new_error_msg($err);
         }
      }
   }
}
