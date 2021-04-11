function Validar_Nuevo_Movimiento(formulario)
{
	var errores="";
	if(formulario.local_origen.value==formulario.local_destino.value)
	 errores+="\n El lugar de destino no debe ser igual al de origen";
	if(formulario.local_origen.value==-1)
	 errores+="\n Debes seleccionar el lugar de origen para poder realizar el movimiento";
	if(errores=="")
	 return true
	else
	{
		alert(errores);
		return false;
	}
}
function Validar_Nuevo_Material(formulario)
{
	var errores="";
	if(formulario.local.value==-1)
	 errores+="\n Debes seleccionar un local para el nuevo material";
	if(formulario.numero_serie.value=="" || formulario.numero_serie.value.length<5)
	 errores+="\n El N. de serie no es correcto";
	if(formulario.label.value=="" || formulario.label.value.length<5)
	 errores+="\n El nombre del nuevo material no es correcto";
	if(errores=="")
	 return true
	else
	{
		alert(errores);
		return false;
	}
}
function Validar_Buscar_Material(formulario)
{
	var errores="";
	var suma=0;
	if(formulario.label.value!="")
	 suma++;
	if(formulario.estado.value!="")
	 suma++;
	if(formulario.tipo_material.value!="")
	 suma++;
	if(formulario.numero_serie.value!="")
	 suma++;
	if(suma!=0)
	 return true
	else
	{
		alert("Debes escribir al menos un criterio");
		return false;
	}	
}