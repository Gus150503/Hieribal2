const ctx = document.getElementById('chartPedidos');

if(ctx){

new Chart(ctx,{

type:'bar',

data:{
labels:['Sep','Oct','Nov','Dic','Ene','Feb','Mar'],

datasets:[

{
label:'Pedidos',
data:[18,22,15,31,20,28,14],
backgroundColor:'rgba(45,158,95,0.2)',
borderColor:'#2d9e5f',
borderWidth:2
},

{
label:'Total',
data:[320,410,280,580,370,520,270],
type:'line',
borderColor:'#c8960c',
backgroundColor:'rgba(200,150,12,0.1)',
tension:0.4
}

]

},

options:{
responsive:true,
maintainAspectRatio:false,
plugins:{
legend:{display:false}
}
}

});

}