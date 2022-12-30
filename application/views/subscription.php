<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Razorpay Subscription</title>	
</head>
<body>

<div id="container">

<div id="FormErr" style="width:100%;"></div><br>
<h2>PLAN : 799</h2>
<p>(Plan Created with the same Amount on Razorpay Dashboard)</p>
	<input type="text" id="full_name" placeholder="Type your Full name">
	<input type="text" id="pay_email" placeholder="Type your Email">
	<input type="text" id="pay_phone" placeholder="Type your Mobile Number">
	<textarea id="pay_address" >Type your Address</textarea>
	<button  class="btn-main mybtn1 cmn-btn" id="razor-subscription-pay-now">Subscribe</button>
</div>

<div class="subscribe-loader">Wait...</div>


<span id="render-subscription-pay-info"></span><!-- Required DIV-->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){ 
jQuery(document).on('click', '#razor-subscription-pay-now', function () {	
       jQuery('.subscribe-loader').show();
	   $full_name 	= $('#full_name').val();
	   $pay_email 	= $('#pay_email').val();
	   $pay_phone 	= $('#pay_phone').val();
	   $pay_address = $('#pay_address').val();
	   
      jQuery.ajax({
          url: '<?php echo base_url(); ?>home/initiateSubscriptions',
          type: 'post',
          data: {
			  full_name: $full_name, 
			  pay_email: $pay_email, 
			  pay_phone: $pay_phone, 
			  pay_address: $pay_address
		 },
          dataType: 'json',
          beforeSend: function () {
              //jQuery('#razor-subscription-pay-now').button('loading');
          },
          complete: function () {
              //jQuery('#razor-subscription-pay-now').button('reset');
          },
          success: function (json) {
              $('.text-danger').remove();
              jQuery('.subscribe-loader').hide();
              if (json['error']) {
                  for (i in json['error']) {
                      $('#FormErr').append('<small class="text-danger" style="float:left;">' + json['error'][i] + '</small>');
                  }
              } else {                  
                  jQuery.ajax({
                      url: '<?php echo base_url(); ?>home/createSubscription',
                      type: 'post',
                      data: {
						  full_name: $full_name, 
						  pay_email: $pay_email, 
						  pay_phone: $pay_phone, 
						  pay_address: $pay_address
					  },
                      dataType: 'html',                      
                      success: function (html) {                                                   
                       jQuery('span#render-subscription-pay-info').html(html);                       
                      },
                      error: function (xhr, ajaxOptions, thrownError) {
                          console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                      }
                  });
              }
          },
          error: function (xhr, ajaxOptions, thrownError) {              
              console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
          }
      });
  });
    });
</script>


<style>
#FormErr small{width:100%; display:inline-block;}
#FormErr small.text-danger{color:red;}
.subscribe-loader{background:#fff; position:fixed; top:0; bottom:0; left:0; right:0; display:none; text-align: center;}
</style>

</body>
</html>