(function(){
    // alert('加载');
})();

const tanchu = '外部JS 弹出提示';

var page = {
    data:'',
    init:function(){
       var vm = new Vue({
           el:"#box",
           data () {
               return {
                   
               }
           },
           components:{
               'myHeader':{
                   template:"#head"
               }
           }
       })
    }
}

$(document).ready(function () {

    page.init();

    $('.bu').click(function (e) { 
        alert(tanchu);
    });
});

function changepic(e){
    // alert('change');
    var f = e.files[0];
    var fr = new FileReader();
    fr.readAsDataURL(f);
    fr.onload = function(){
        $('#show').attr('src',fr.result)
    }
}
