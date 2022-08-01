<?php
/**
 * Signature Html
 * @var $name
 * @var $sign_id
 * 
 */
?>

<!-- Signature -->

<!-- answer -->

<div class="signature-answers">
    
    <div id="signature-pad-<?php echo $sign_id;?>" class="signature-pad">
        <div class="signature-pad--body">
            <canvas></canvas>
           
        </div>
        <div class="signature-pad--footer">
            <div class="description">Sign above</div>

            <div class="signature-pad--actions">
                <div>
                    <button type="button" class="clear" data-action="clear">Clear</button>
                    
                    <button type="button" data-action="undo">Undo</button>

                </div>
               
            </div>
        </div>
        
    </div>
    <?php 
        if(!empty($dispVal))
        {
        ?>
        <img src="<?php echo $dispVal; ?>" class="signature-answer-preview">
         <?php 
        }
         ?>
</div>


<script type="text/javascript">
   
    var wrapper = document.getElementById("signature-pad-<?php echo $sign_id;?>");
        var clearButton = wrapper.querySelector("[data-action=clear]");

        var undoButton = wrapper.querySelector("[data-action=undo]");

        var canvas = wrapper.querySelector("canvas");
        var signaturePad = new SignaturePad(canvas, {
            // It's Necessary to use an opaque color when saving image as JPEG;
            // this option can be omitted if only saving as PNG or SVG
            backgroundColor: 'rgb(255, 255, 255)'
        });
        signaturePad.fromDataURL('<?php echo $dispVal; ?>');
        







        clearButton.addEventListener("click", function (event) {
            signaturePad.clear();
        });

        undoButton.addEventListener("click", function (event) {
            var data = signaturePad.toData();

            if (data) {
                data.pop(); // remove the last dot or line
                signaturePad.fromData(data);
            }
        });
        // Adjust canvas coordinate space taking into account pixel ratio,
        // to make it look crisp on mobile devices.
        // This also causes canvas to be cleared.
        function resizeCanvas() {
          // When zoomed out to less than 100%, for some very strange reason,
          // some browsers report devicePixelRatio as less than 1
          // and only part of the canvas is cleared then.
          var ratio =  Math.max(window.devicePixelRatio || 1, 1);

          // This part causes the canvas to be cleared
          canvas.width = canvas.offsetWidth * ratio;
          canvas.height = canvas.offsetHeight * ratio;
          canvas.getContext("2d").scale(ratio, ratio);

          // This library does not listen for canvas changes, so after the canvas is automatically
          // cleared by the browser, SignaturePad#isEmpty might still return false, even though the
          // canvas looks empty, because the internal data of this library wasn't cleared. To make sure
          // that the state of this library is consistent with visual state of the canvas, you
          // have to clear it manually.
          signaturePad.clear();
        }

        // On mobile devices it might make more sense to listen to orientation change,
        // rather than window resize events.
        window.onresize = resizeCanvas;
        resizeCanvas();
 $(document).ready(function(){
        
        $("#limesurvey").submit(function(){
            var todataUrl = signaturePad.toDataURL("image/jpeg");
            $("<input />").attr("type", "hidden")
          .attr("name", "<?php echo $name; ?>")
          .attr("value", todataUrl)
          .appendTo("#limesurvey");
           
            return true;
            
        });
    });
</script>

