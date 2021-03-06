<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-100945954-1', 'auto');
  ga('send', 'pageview');

</script>

<div class="row">
    <div class="logo col-md-4"></div>
    <div class="col-md-4"></div>
    <div class="col-md-4">
    	<a href="https://hoopdrillz.typeform.com/to/LpezVz" class="btn2 btn2-sm btn2-outline-primary" target="_blank"><?= $landing_btn_about_text ?></a>
        <a href="<?= $BASE ?><?= $url_coach ?>" class="btn2 btn2-sm btn2-outline-primary"><?= $landing_btn_coach_text ?></a>
    </div>
</div>

<div id="backbox">
	<?php echo $this->render('error.html',NULL,get_defined_vars(),0); ?>

		<div class="row">
    		<div class="col">
    			<div style="font-size:48px; color:#cde3f6; margin:100px auto -10px auto; font-weight:bold; width:80%;">BASKETBALL TRAINING ON DEMAND</div>
        	<div class="landing-page-text" style="padding:0px 0px 20px 0px;margin:0 auto;margin-top:10px;width:80%; align:left;font-size:23px; color:#9eb9d0; line-height: 30px; justify-content: center;"><?= $landing_text ?></div>
            <div style="font-size:18px; color:#fff; margin:50px auto auto auto; width:80%;">GET EARLY ACCESS</div>
            </div>
    	</div>
</div>

<form action="<?= $url_new_user ?>" method="post">
    <div class="form-group row">
        <div class="col">
            <input type="text" id="email" name="email" class="landing-page-email-input"
                   placeholder="<?= $landing_input_email_placeholder ?>">
            <button type="submit" class="btn btn-outline-primary"><?= $landing_btn_join_text ?></button>
        </div>
    </div>
</form>

<div style="margin:auto; width:100%; color:#65829f; position:relative; bottom: 0; height: 40px; margin-top: 15px; font-size:12px;">Copyright &copy;2017 - HoopDrillz</div>


