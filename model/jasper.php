<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015-2016, Carlos García Gómez. All Rights Reserved. 
 */

/**
 * Description of jasper
 *
 * @author carlos
 */
class jasper
{
   private $bin;
   
   /**
    * Salida de comandos ejecutados.
    * @var type 
    */
   public $cmd_output;
   
   /**
    * Lista de errores encontrados.
    * @var type 
    */
   public $errors;
   
   public function __construct()
   {
      $this->cmd_output = array();
      $this->errors = array();
      
      $this->bin = 'jasperstarter';
      if( strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' )
      {
         $this->bin = "jasperstarter.exe";
      }
      else if( !is_executable(getcwd().'/plugins/jasper/jasperstarter/bin/'.$this->bin) )
      {
         if( @chmod(getcwd().'/plugins/jasper/jasperstarter/bin/'.$this->bin, 0755) )
         {
            /// nada
         }
         else
         {
            $this->errors[] = getcwd().'/plugins/jasper/jasperstarter/bin/'.$this->bin.' no tiene permisos de ejecución.';
         }
      }
   }
   
   /**
    * Genera el archivo .jasper a partir de un .jrxml
    * Lo genera en la misma carpeta del .jrxml
    * Devuelve FALSE en caso de fallo.
    * @param type $jrxml_location
    * @return boolean
    */
   public function compile($jrxml_location)
   {
      $retorno = FALSE;
      
      $cdir = getcwd();
      if( file_exists($cdir.'/'.$jrxml_location) )
      {
         if( !is_writable($cdir.'/'.$jrxml_location) )
         {
            $this->errors[] = 'No se puede escribir sobre el directorio de '.$cdir.'/'.$jrxml_location;
            return FALSE;
         }
         
         /// nos movemos al directorio del report
         if( chdir( dirname($cdir.'/'.$jrxml_location) ) )
         {
            /// generamos el .jasper
            $jasper_location = substr( basename($cdir.'/'.$jrxml_location), 0, -5).'jasper';
            $cmd = $cdir."/plugins/jasper/jasperstarter/bin/".$this->bin." cp ".basename($cdir.'/'.$jrxml_location);
            $salida = array($cmd);
            exec($cmd.' 2>&1', $salida);
            
            foreach($salida as $sal)
            {
               $this->cmd_output[] = $sal;
            }
            
            if( file_exists($jasper_location) )
            {
               $retorno = TRUE;
            }
            
            /// volvemos al directorio original
            chdir($cdir);
         }
      }
      else
         $this->errors[] = 'Archivo '.$cdir.'/'.$jrxml_location.' no encontrado.';
      
      return $retorno;
   }
   
   /**
    * Genera un PDF (o el formaro seleccionado) a partir de un archivo .jasper o .jrxml
    * Genera el archivo en el directorio tmp/jasper/
    * Devuelve la ruta del archivo generado o False en caso de fallo.
    * @param type $source_location
    * @param type $format
    * @param type $params
    * @return string
    */
   public function build($source_location, $format = 'pdf', $params = FALSE)
   {
      $retorno = FALSE;
      
      $cdir = getcwd();
      if( file_exists($cdir.'/'.$source_location) )
      {
         /// nos movemos al directorio del .jasper o .jrxml
         if( chdir( dirname($cdir.'/'.$source_location) ) )
         {
            $pdf_name = FALSE;
            if( substr($source_location, -6, 1) == '.' )
            {
               $pdf_name = substr( basename($cdir.'/'.$source_location), 0, -6).'_'.time();
            }
            else if( substr($source_location, -7, 1) == '.' )
            {
               $pdf_name = substr( basename($cdir.'/'.$source_location), 0, -7).'_'.time();
            }
            
            if($pdf_name)
            {
               $dbtype = strtolower(FS_DB_TYPE);
               if($dbtype == 'postgresql')
               {
                  $dbtype = 'postgres';
               }
               
               /// generamos el comando a ejecutar
               $cmd = $cdir."/plugins/jasper/jasperstarter/bin/".$this->bin." pr ".basename($source_location)." -t ".$dbtype." -u ".FS_DB_USER;
               if(FS_DB_PASS)
               {
                  $cmd .= " -p ".FS_DB_PASS;
               }
               $cmd .= " -o ".$pdf_name." -f ".$format." -H ".FS_DB_HOST." -n ".FS_DB_NAME;
               
               if($params)
               {
                  $cmd .= ' -P';
                  foreach($params as $key => $value)
                  {
                     $cmd .= ' '.$key.'='.$value;
                  }
               }
               
               $salida = array($cmd);
               exec($cmd.' 2>&1', $salida);
               
               foreach($salida as $sal)
               {
                  $this->cmd_output[] = $sal;
               }
               
               if( file_exists($pdf_name.'.'.$format) )
               {
                  if( !file_exists($cdir.'/tmp/'.FS_TMP_NAME.'jasper') )
                  {
                     mkdir($cdir.'/tmp/'.FS_TMP_NAME.'jasper');
                  }
                  
                  rename($pdf_name.'.'.$format, $cdir.'/tmp/'.FS_TMP_NAME.'jasper/'.$pdf_name.'.'.$format);
                  $retorno = 'tmp/'.FS_TMP_NAME.'jasper/'.$pdf_name.'.'.$format;
               }
            }
            else
            {
               $this->errors[] = 'No se ha detectado un archivo .jasper o .jrxml';
            }
            
            /// volvemos al directorio original
            chdir($cdir);
         }
      }
      else
         $this->errors[] = 'Archivo '.$cdir.'/'.$source_location.' no encontrado.';
      
      return $retorno;
   }
}
