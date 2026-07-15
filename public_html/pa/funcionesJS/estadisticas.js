var imagenes=[];    
$(document).ready(function(){

	cargaInicial();
});

function cargaInicial(){
	$.post("negocio/estadisticas.php",{id:1},function(r){
		$("#datos").html(r);
		$.post("negocio/estadisticas.php",{id:2},function(r){
			var datos = jQuery.parseJSON(r);
			console.log(datos);
			var cantidades = [];
			var nombres = [];
            $.each(datos, function (i, d) {
            	cantidades.push(d.cantidad);
            	nombres.push(d.nombre_subcontrato+" "+d.nombre_contrato);

        	});
			grafico(cantidades,nombres);
			//setTimeout(function(){ copiar(); }, 1000);
			
		});

	});
}
function grafico(cantidades,nombres){
	var ctx = document.getElementById("barra").getContext('2d');
	var myChart = new Chart(ctx, {
	    type: 'bar',
	    data: {
	        labels: nombres,
	        datasets: [{
	            label: 'Ultimos 90 días.',
	            backgroundColor:"rgba(54,162,235,0.6)",
	            data: cantidades
		        }]
	    },
	    options: {
	        scales: {
	            yAxes: [{
	                ticks: {
	                    beginAtZero:true
	                }
	            }]
	        }
	    }
	});
}
function getRandomColor() {
    var letters = '0123456789ABCDEF'.split('');
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

function Arm_pdf(){
     	      	var doc = new jsPDF("1","pt");
     	      	doc.text(35, 25, 'Ultimos 90 días.')
                doc.addImage(imagenes[0].toDataURL('image/jpeg'), 'jpeg', 5, 30, 590, 300);
                doc.text(35, 355, 'Resumen anual documentos.');
                doc.addImage(imagenes[1].toDataURL("image/jpeg"), 'jpeg', 5, 360, 590, 300);
                var pdf =btoa(doc.output());
                $.post("negocio/estadisticas.php",{id:4,pdf:pdf},function(){

                });
                //doc.save('ejemplo.pdf');

}

function imagen_pdf(){

				$( "#grafico" ).attr("style","background:#FFFFFF;");
                html2canvas($("#grafico"), {
                    onrendered: function (canvas) {
                    	imagenes[0]=canvas;
                    }
                });
				$( "#registros" ).attr("style","background:#FFFFFF;");
                html2canvas($("#registros"), {
                    onrendered: function (canvas) {
                    	imagenes[1]=canvas;
                    }
                });
}
function copiar(){
  setTimeout(function(){ imagen_pdf(); }, 1000);

  setTimeout(function(){ Arm_pdf(); }, 4000);

  setTimeout(function(){ enviarMail(); }, 6000);
}

function enviarMail(){
	$.post("negocio/estadisticas.php",{id:3},function(){});
}