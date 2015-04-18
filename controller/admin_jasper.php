<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015, Carlos García Gómez. All Rights Reserved. 
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
      if( strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' AND !is_executable(getcwd().'/plugins/jasper/jasperstarter/bin/jasperstarter') )
      {
         $this->new_error_msg( getcwd().'/plugins/jasper/jasperstarter/bin/jasperstarter no tiene permisos de ejecución.' );
      }
      
      if( isset($_GET['test']) )
      {
         $file_location = fs_jasper('plugins/jasper/reports/facturascripts.jrxml', $_GET['test']);
         if($file_location)
         {
            $this->new_message('<a href="'.$file_location.'" target="_blank">'.strtoupper($_GET['test']).'</a> generado correctamente.');
         }
         else
            $this->new_error_msg('Error al generar el archivo '.strtoupper($_GET['test']).'.');
      }
   }
}
