<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015-2016, Carlos García Gómez. All Rights Reserved. 
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
         $this->report1();
      }
      else if( isset($_GET['test2']) )
      {
         $this->report2();
      }
      else if( isset($_POST['jrxml']) )
      {
         $this->nuevo_report();
      }
      
      foreach($this->jasper->errors as $err)
      {
         $this->new_error_msg($err);
      }
   }
   
   private function report1()
   {
      if( $this->jasper->compile('plugins/jasper/report1/facturascripts.jrxml') )
      {
         $file_location = $this->jasper->build('plugins/jasper/report1/facturascripts.jasper', $_GET['test']);
         if($file_location)
         {
            $this->new_message('<a href="'.$file_location.'" target="_blank">'.strtoupper($_GET['test']).'</a> generado correctamente.');
         }
         else
            $this->new_error_msg('Error al generar el archivo '.strtoupper($_GET['test']).'.');
      }
   }
   
   private function report2()
   {
      if( $this->jasper->compile('plugins/jasper/report2/articulos.jrxml') )
      {
         $file_location = $this->jasper->build('plugins/jasper/report2/articulos.jasper', $_GET['test2']);
         if($file_location)
         {
            $this->new_message('<a href="'.$file_location.'" target="_blank">'.strtoupper($_GET['test2']).'</a> generado correctamente.');
         }
         else
            $this->new_error_msg('Error al generar el archivo '.strtoupper($_GET['test2']).'.');
      }
   }
   
   private function nuevo_report()
   {
      if( !file_exists('tmp/'.FS_TMP_NAME.'jasper') )
      {
         mkdir('tmp/'.FS_TMP_NAME.'jasper');
      }
      
      if( is_uploaded_file($_FILES['fimagen']['tmp_name']) )
      {
         copy($_FILES['fimagen']['tmp_name'], 'tmp/'.FS_TMP_NAME.'jasper/'.$_FILES['fimagen']['name']);
      }
      
      if( is_uploaded_file($_FILES['fjrxml']['tmp_name']) )
      {
         /// lo movemos al temporal
         if( copy($_FILES['fjrxml']['tmp_name'], 'tmp/'.FS_TMP_NAME.'jasper/test.jrxml') )
         {
            if( $this->jasper->compile('tmp/'.FS_TMP_NAME.'jasper/test.jrxml') )
            {
               $file_location = $this->jasper->build('tmp/'.FS_TMP_NAME.'jasper/test.jrxml');
               if($file_location)
               {
                  $this->new_message('<a href="'.$file_location.'" target="_blank">PDF</a> generado correctamente.');
               }
               else
                  $this->new_error_msg('Error al generar el archivo PDF.');
            }
         }
         else
         {
            $this->new_error_msg('Error al copiar el archivo.');
         }
      }
      else
      {
         $this->new_error_msg('Archivo no encontrado.');
      }
   }
}
