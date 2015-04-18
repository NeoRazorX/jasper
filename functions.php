<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015, Carlos García Gómez. All Rights Reserved. 
 */

/**
 * Genera un PDF a partir de un archivo .jrxml usando JasperReports.
 * Si no existe el archivo .jasper lo compila.
 * Devuelve la ruta del PDF generado o FALSE en caso de fallo.
 * @param type $jrxml_location
 * @return string
 */
function fs_jasper($jrxml_location, $format = 'pdf', $params = FALSE)
{
   $salida = FALSE;
   
   $bin = "jasperstarter";
   if( strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' )
   {
      $bin = "jasperstarter.exe";
   }
   
   $cdir = getcwd();
   if( file_exists($cdir.'/'.$jrxml_location) )
   {
      /// nos movemos al directorio del report
      if( chdir( dirname($cdir.'/'.$jrxml_location) ) )
      {
         /// generamos el .jasper
         $jasper_location = substr( basename($cdir.'/'.$jrxml_location), 0, -5).'jasper';
         $cmd = $cdir."/plugins/jasper/jasperstarter/bin/".$bin." cp ".basename($cdir.'/'.$jrxml_location);
         exec($cmd);
         
         if( file_exists($jasper_location) )
         {
            $pdf_name = substr( basename($cdir.'/'.$jrxml_location), 0, -6).'_'.time();
            
            $dbtype = strtolower(FS_DB_TYPE);
            if($dbtype == 'postgresql')
            {
               $dbtype = 'postgres';
            }
            
            /// generamos el PDF
            $cmd = $cdir."/plugins/jasper/jasperstarter/bin/".$bin." pr ".$jasper_location." -t ".$dbtype.
                    " -u ".FS_DB_USER." -p ".FS_DB_PASS." -o ".$pdf_name." -f ".$format." -H ".FS_DB_HOST." -n ".FS_DB_NAME;
            
            if($params)
            {
               $cmd .= ' -P';
               foreach($params as $key => $value)
               {
                  $cmd .= ' '.$key.'='.$value;
               }
            }
            
            exec($cmd);
            
            if( file_exists($pdf_name.'.'.$format) )
            {
               if( !file_exists($cdir.'/tmp/jasper') )
               {
                  mkdir($cdir.'/tmp/jasper');
               }
               
               rename($pdf_name.'.'.$format, $cdir.'/tmp/jasper/'.$pdf_name.'.'.$format);
               $salida = 'tmp/jasper/'.$pdf_name.'.'.$format;
            }
            else
               echo $cmd;
         }
         
         /// volvemos al directorio original
         chdir($cdir);
      }
   }
   
   return $salida;
}