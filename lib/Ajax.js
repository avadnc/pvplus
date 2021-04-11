//----------------------------------funcion para trabajr con ajax-------------------------------------------------
 function nuevoAjax()
  { 
  var xmlhttp=false; 
  try 
   { 
    // Creacion del objeto AJAX para navegadores no IE
   xmlhttp=new ActiveXObject("Msxml2.XMLHTTP"); 
   }
  catch(e)
   { 
   try
    { 
    // Creacion del objet AJAX para IE 
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP"); 
   } 
   catch(E) { xmlhttp=false; }
  }
  if (!xmlhttp && typeof XMLHttpRequest!='undefined') { xmlhttp=new XMLHttpRequest(); } 
   return xmlhttp; 
}