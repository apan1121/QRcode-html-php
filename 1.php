<?php 
include_once ('include/qrlib.php');

$base64 = "";
$data  = isset($_GET['qrdata'])?$_GET['qrdata']:"";

if(!empty($data)){
	$PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
	//html PNG location prefix
	$PNG_WEB_DIR = 'temp/';
	
	//ofcourse we need rights to create temp dir
	if (!file_exists($PNG_TEMP_DIR))
		mkdir($PNG_TEMP_DIR);
	
	
	$filename = $PNG_TEMP_DIR.'test.png';
	QRcode::png($data, $filename, "H", 8, 2);
	$type = pathinfo($filename, PATHINFO_EXTENSION);
	$data = file_get_contents($filename);
	$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
	
	unlink($filename);
}

?>
<?php if( !empty($base64) ){ ?>
	<img src='<?=$base64?>'>	
<?php }?>
<script src="http://code.jquery.com/jquery-2.1.1.min.js"></script>
<script src="binaryajax.js"></script>
<script src="canvasResize.js"></script>
<script src="exif.js"></script>

<script src="jquery.Jcrop.min.js"></script>
<link rel="stylesheet" href="jquery.Jcrop.min.css" type="text/css" />
<script src="llqrcode.js"></script>
<form>
<textarea name='qrdata'>
</textarea>
<input type='submit'>
</form>


<script>
var gCtx = null;
var gCanvas = null;

$(document).ready(function(){
	$("#a").change(function(){
		handleFiles(this.files);
	});

	$("#split").click(function(){
		updatePreview();
	});
	load();
});

function load()
{
	if(isCanvasSupported() && window.File && window.FileReader)
	{
		initCanvas(800, 600);
		qrcode.callback = read;
		//document.getElementById("mainbody").style.display="inline";
        //setwebcam();
	}
	else
	{
		document.getElementById("mainbody").style.display="inline";
		document.getElementById("mainbody").innerHTML='<p id="mp1">QR code scanner for HTML5 capable browsers</p><br>'+
        '<br><p id="mp2">sorry your browser is not supported</p><br><br>'+
        '<p id="mp1">try <a href="http://www.mozilla.com/firefox"><img src="firefox.png"/></a> or <a href="http://chrome.google.com"><img src="chrome_logo.gif"/></a> or <a href="http://www.opera.com"><img src="Opera-logo.png"/></a></p>';
	}
}

function handleFiles(f){
	var o=[];
	for(var i =0;i<f.length;i++){
		
		canvasResize(f[0], {
            width: 500,
            height: 500,
            crop: false,
            quality: 100,
            rotate: 0,
            callback: function(data, width, height) {
                console.log(data);
                $("#preview").attr({"src":data});
                setSplit();
            }
        });
			
	}
}

function handleFiles_1(f)
{
	var o=[];
	
	for(var i =0;i<f.length;i++)
	{
        var reader = new FileReader();
        reader.onload = (function(theFile) {
	        return function(e) {
	            gCtx.clearRect(0, 0, gCanvas.width, gCanvas.height);
				qrcode.decode(e.target.result);
	        };
        })(f[i]);
        reader.readAsDataURL(f[i]);	
    }
}

function initCanvas(w,h)
{
    gCanvas = document.getElementById("qr-canvas");
    gCanvas.style.width = w + "px";
    gCanvas.style.height = h + "px";
    gCanvas.width = w;
    gCanvas.height = h;
    gCtx = gCanvas.getContext("2d");
    gCtx.clearRect(0, 0, w, h);
}

function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function isCanvasSupported(){
	  var elem = document.createElement('canvas');
	  return !!(elem.getContext && elem.getContext('2d'));
}

function read(a)
{
    var html="<br>";
    if(a.indexOf("http://") === 0 || a.indexOf("https://") === 0)
        html+="<a target='_blank' href='"+a+"'>"+a+"</a><br>";
    html+="<b>"+htmlEntities(a)+"</b><br><br>";
    console.log(a);
    $(".info").html(a);
    //alert(a);
    //document.getElementById("result").innerHTML=html;
}



function setSplit(){
	$('#preview').Jcrop({
	      onChange: savePos,
	      onSelect: savePos,
	      aspectRatio: 1
	    },function(){
	      // Use the API to get the real image size
	      var bounds = this.getBounds();

	      // Store the API in the jcrop_api variable
	      jcrop_api = this;

	      // Move the preview into the jcrop container for css positioning
	      //console.log(jcrop_api.ui.holder);


	     
	    });
}
var saveposition = null;

function savePos(c){
	saveposition = c;
}

var canvas = "";
var context = "";
function updatePreview(c){

    var x = saveposition['x'];
    var y = saveposition['y'];
    var w = saveposition['w'];
    var h = saveposition['h'];

	
	 canvas = document.getElementById('qr-canvas');
     context = canvas.getContext('2d');
     context.clearRect(0, 0, canvas.width, canvas.height);
     var imageObj = new Image();
	
     imageObj.onload = function() {
         // draw cropped image
         var sourceX = x;
         var sourceY = y;
         var sourceWidth = w;
         var sourceHeight = h;
         var destWidth = w;
         var destHeight = h;
         var destX = 0;
         var destY = 0;
			
         context.drawImage(imageObj, sourceX, sourceY, sourceWidth, sourceHeight, destX, destY, destWidth, destHeight);
         setTimeout(function(){
        	 scan();
          },100);
       };
       imageObj.src = $("#preview").attr("src");
}

function scan(){
	var base64 = canvas.toDataURL();
	var file = canvasResize('dataURLtoBlob', base64) ;

	var reader = new FileReader();
    reader.onload = (function(theFile) {
        return function(e) {
            gCtx.clearRect(0, 0, gCanvas.width, gCanvas.height);
			qrcode.decode(e.target.result);
        };
    })(file);
    reader.readAsDataURL(file);	
}
</script>

<input type='file' name='a' id='a'><br>
<img id='preview' alt="[Jcrop Example]" ><br>
<input type='button' id='split' value='split'>
<div class='info'>no data</div>

<canvas id='qr-canvas'></canvas>

