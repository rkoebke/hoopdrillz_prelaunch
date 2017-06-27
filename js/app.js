//Created by Nu Am Chef Azi Project for HoopDrillz.

//copy to clipboard
(function() {

    'use strict';

    // click events
    document.body.addEventListener('click', copy, true);

    // event handler
    function copy(e) {

        // find target element
        var
            t = e.target,
            c = t.dataset.copytarget,
            inp = (c ? document.querySelector(c) : null);

        // is element selectable?
        if (inp && inp.select) {

            // select text
            inp.select();

            try {
                // copy text
                document.execCommand('copy');
                inp.blur();

                // copied animation
                t.classList.add('copied');
                setTimeout(function() { t.classList.remove('copied'); }, 1500);
            }
            catch (err) {
                alert('please press Ctrl/Cmd+C to copy');
            }

        }

    }

})();

//force enable button if rules are accepted
$('#disabledbtn').prop("disabled", true);
$('input:checkbox').click(function() {
    if ($(this).is(':checked')) {
        $('#disabledbtn').prop("disabled", false);
    } else {
        if ($('#iacceptdisclaimer').filter(':checked').length < 1){
            $('#disabledbtn').attr('disabled',true);}
    }
});

//popup modal like block
var modal = document.getElementById('errorwindow');
modal.style.display = "block";
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}