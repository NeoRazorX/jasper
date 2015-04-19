<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015, Carlos García Gómez. All Rights Reserved. 
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
         $this->errors[] = getcwd().'/plugins/jasper/jasperstarter/bin/'.$this->bin.' no tiene permisos de ejecución.';
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
    * Genera un PDF (o el formaro seleccionado) a partir de un archivo .jasper
    * Genera el archivo en el directorio tmp/jasper/
    * Devuelve la ruta del archivo generado o False en caso de fallo.
    * @param type $jasper_location
    * @param type $format
    * @param type $params
    * @return string
    */
   public function build($jasper_location, $format = 'pdf', $params = FALSE)
   {
      $retorno = FALSE;
      
      $cdir = getcwd();
      if( file_exists($cdir.'/'.$jasper_location) )
      {
         /// nos movemos al directorio del .jasper
         if( chdir( dirname($cdir.'/'.$jasper_location) ) )
         {
            $pdf_name = substr( basename($cdir.'/'.$jasper_location), 0, -7).'_'.time();
            
            $dbtype = strtolower(FS_DB_TYPE);
            if($dbtype == 'postgresql')
            {
               $dbtype = 'postgres';
            }
            
            /// generamos el PDF
            $cmd = $cdir."/plugins/jasper/jasperstarter/bin/".$this->bin." pr ".basename($jasper_location)." -t ".$dbtype.
                    " -u ".FS_DB_USER." -p ".FS_DB_PASS." -o ".$pdf_name." -f ".$format." -H ".FS_DB_HOST." -n ".FS_DB_NAME;
            
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
               if( !file_exists($cdir.'/tmp/jasper') )
               {
                  mkdir($cdir.'/tmp/jasper');
               }
               
               rename($pdf_name.'.'.$format, $cdir.'/tmp/jasper/'.$pdf_name.'.'.$format);
               $retorno = 'tmp/jasper/'.$pdf_name.'.'.$format;
            }
            
            /// volvemos al directorio original
            chdir($cdir);
         }
      }
      else
         $this->errors[] = 'Archivo '.$cdir.'/'.$jasper_location.' no encontrado.';
      
      return $retorno;
   }
}
