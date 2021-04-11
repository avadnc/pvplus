function DetallesTabla(idtabla)
{
	$.post('ajaxutilitario.php',{opcion:1,id:idtabla}, function(data){
		
			 var lugar_poner=document.getElementById('celda_detalles');	  
	  
	         lugar_poner.innerHTML=data;			
			
			 $("#celda_detalles").dialog({
			height: 'auto',
			width: 450,
			resizable:false,
			modal: true,
			buttons: {
				X: function() {
					$( this ).dialog( "close" );
				}}
				});
			 $("#celda_detalles").show("blind");			 				  
        });
}

function Validar_Tarifa_Cliente(formulario)
{
	var errores="";
	if(formulario.id_producto.value==-1)
	 errores+="\n Selecciona el producto.";
	if(formulario.producto.value==-1)
	 errores+="\n Selecciona una tabla de precios.";
	if(errores=="")
	 return true
	else
	{
		alert(errores);
		return false;
	}
}
function Ver_Detalles_Tarifa()
{	
	var tabla=document.getElementById('tabla_precio').value;
	if(tabla>0)
	 DetallesTabla(tabla);	
}
function Buscar_Tarifas_Producto()
{
  var producto=document.getElementById('id_producto').value;
   var lugar_poner=document.getElementById('celda_tarifas');
   var root=document.getElementById('dir_url').value;
  if(producto!=-1)
  {
 
  var ajax=nuevoAjax();	
  ajax.open("POST", root+"/preciovolumen/ajaxutilitario.php", true);
  ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  ajax.send("opcion=0&producto="+producto);
  
  ajax.onreadystatechange=function()
   {
   if (ajax.readyState==1){}
   if (ajax.readyState==4)
    {		
      var resultado=ajax.responseText;
	  if(resultado!=-1)
	  lugar_poner.innerHTML=resultado;
	  else
	  lugar_poner.innerHTML="<select name='producto' id='producto'><option value='-1'>.::No tiene tarifas::.</option></select>";  
	  //resultado=JSON.parse(resultado);
    }
   }
  }
  else
  lugar_poner.innerHTML="<select name='producto' id='producto'><option value='-1'>.::Seleccione::.</option></select>";	
}
function CalcularCantidadTarifa(idtabla,edit_cantidad,producto,socio)
{
	var cantidad=document.getElementById('calc'+edit_cantidad).value;	
  if(cantidad>0)
  {
	  $.post('ajaxutilitario.php',{opcion:2,tabla:idtabla,id_producto:producto,cant:cantidad,socid:socio}, function(data){
		  $('<div title="PVPLUS">'+data+'</div>').dialog({modal:true,resizable:false});
	   //alert(data);
	  
	  });	  
  }
  else
  alert('El valor '+cantidad+' no es correcto para realizar el calculo');
}

function CalcularCantidadProducto(idtabla,producto)
{
	var cantidad=document.getElementById('cantidad').value;
		
  if(cantidad>0)
  {
	  $.post('ajaxutilitario.php',{opcion:4,tabla:idtabla,id_producto:producto,cant:cantidad}, function(data){
	   alert(data);
	  
	  });	  
  }
  else
  alert('El valor '+cantidad+' no es correcto para realizar el calculo');	
}