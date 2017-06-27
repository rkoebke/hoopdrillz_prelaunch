<?php if ($SESSION['haserror']): ?>
    
        <div id="errorwindow" class="modal alert-danger">
            <div class="modal-content"><?= $SESSION['error'] ?></div>
        </div>
    
<?php endif; ?>