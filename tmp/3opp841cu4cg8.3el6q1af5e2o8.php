<div>

<div class="row">
    <div class="logo col-md-4"></div>
    <div class="col-md-4"></div>
    <div class="col-md-4">
    	<div class="footer-col-logo"><a href="#" id="shareBtn" class="no-modification" style="margin-top:20px;"><i class="fa fa-facebook-square fa-2x" aria-hidden="true"></i></a></div>
        <div class="footer-col-logo"><a href="<?= $appTwitter ?>" data-show-count="false" target="_blank" class="no-modification"><i class="fa fa-twitter-square fa-2x" aria-hidden="true"></i></a></div>
    </div>
</div>

    <div class="row">
        <div class="col" style="margin-top:50px;">
            <?php if ($new_user): ?>
                
                    <h2 style="color:#cde3f6;"><?= $user_loggedin_already_registered ?></h2>
                
                <?php else: ?>
                    <h2 style="color:#cde3f6;"><?= $user_loggedin_new_registered_header ?></h2>
                    <span style="color:#9eb9d0;"><?= $user_loggedin_new_registered_sub_header ?></span>
                
            <?php endif; ?>

            <div class="user-rank-position">

                <?php if ($waitrank == 0): ?>
                    
                        <?= $user_loggedin_new_registered_top_list ?>
                    
                    <?php else: ?>
                        <?php if ($waitrank == 1): ?>
                            
                                <?= $user_loggedin_new_registered_second_list_part_one ?><?= $waitrank ?><?= $user_loggedin_new_registered_second_list_part_two ?>
                            
                            <?php else: ?>
                                <?= $user_loggedin_new_registered_other_list_part_one ?><?= $waitrank ?><?= $user_loggedin_new_registered_other_list_part_two ?>
                            
                        <?php endif; ?>
                    
                <?php endif; ?>

            </div>
            <span style="color:#9eb9d0;"><?= $user_loggedin_details_line_one ?><br>
            <?= $user_loggedin_details_line_two ?><br>
            <?= $user_loggedin_details_line_three ?><br></span>

            <!--<div class="footer-col-logo">
                <a href="<?= $appInstagram ?>" target="_blank" class="no-modification"><i class="fa fa-instagram fa-2x" aria-hidden="true"></i></a>
            </div>-->

            <br>

            <span style="color:#9eb9d0;"><?= $user_loggedin_details_share_this_line ?></span>

            <pre style="background-color:#dfca9d;border:none;font-weight:bold;max-width:50%;
   min-width: 450px; padding:15px;margin:20px auto;border-radius:5px;
            webkit-box-shadow: 0px 3px 78px 8px rgba(0,0,0,0.38);
   			-moz-box-shadow: 0px 3px 78px 8px rgba(0,0,0,0.38);
    		box-shadow: 0px 3px 78px 8px rgba(0,0,0,0.38);"><a href="<?= $appURL ?><?= $uniqueurl ?>" style="color:#0e314d;"><?= $appURL ?><?= $uniqueurl ?></a></pre>


            <div class="row col-center">
                <!--<div class="col">
                    <a class="btn btn-md btn-outline-success" role="button" href="<?= $BASE ?>/"><?= $thank_you_btn_return_to_prelaunch ?></a>
                </div>-->
            </div>
        </div>
    </div>
</div>