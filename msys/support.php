<?php include_once('header.php'); ?>
      <section class="subHeader" style="background-image: url(assets/_blockpic_1510929511.png)">
        <div class="container">
          <div class="flexDiv">
            <h2>Contact Us</h2><h5></h5>
          </div>
        </div>
      </section>
      
      <section class="container breathe" id="dlink-main">
        <div class="row">
          <div class="col-sm-5">
            <h3>Enquiry</h3>
            <h2 class="nomargin" style="font-weight: 100; margin-bottom: 30px">We will be happy to answer all your queries/enquiries</h2>
            <form id="queryForm" class="form-horizontal" method="POST" action="https://its.dlink.co.in/bin/sendquery.php">
              <div class="form-group">
                <div class="col-sm-12"><input type="text" name="name" class="form-control" id="name" value="" placeholder="*Your Name"></div>
              </div>
              <div class="form-group">
                <div class="col-sm-12"><input type="number" name="mobile" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" onkeydown="javascript: return ['Backspace','Delete','ArrowLeft','ArrowRight'].includes(event.code) ? true : !isNaN(Number(event.key)) && event.code!=='Space'" maxlength="10" class="form-control" id="mobile" value="" placeholder="*Mobile Number" /></div>
              </div>
              <div class="form-group">
                <div class="col-sm-12"><input type="email" name="email" class="form-control" id="email" value="" placeholder="*Email ID" /></div>
              </div>
              <div class="form-group">
                <div class="col-sm-12"><input type="text" name="city" class="form-control" id="city" value="" placeholder="Location" /></div>
              </div>
              <div class="form-group">
                <div class="col-sm-12"><input type="text" name="company" class="form-control" id="company" value="" placeholder="Company/Organization"></div>
              </div>
              <div class="form-group">
                <div class="col-sm-12"><textarea name="comments" class="form-control" id="comments" placeholder="Comments / Query"></textarea></div>
              </div>
              <div class="form-group">
                <div class="col-sm-9 form-inline">
                  <input type="text" name="verify" id="verify" class="form-control" placeholder="Type Code" /><img src="captcha.jpg" alt="" class="captchaImage" style="width: 60px; height: 30px; vertical-align: middle" />
                </div>
              </div>
              <p><button type="button" id="send" class="btn btn-lg btn-success dlink">Send Query</button></p>
            </form>
          </div>
          <div class="col-sm-7">
                        <p>&nbsp;</p>
            <img src="img/call-center.jpg" alt="" class="img-responsive" style="border-radius: 15px" />
          </div>
        </div>
    </section>

    
       <footer>
        <div class="bluBG breathe">
          <div class="container">
            <div class="row">
              <div class="col-sm-8">
                <span class="title-text">Get a robust <strong>cabling solution</strong> for your business</span>
              </div>
              <div class="col-sm-4">
                <a href="support.html" class="btn getInTouch">Get in touch</a>
              </div>
            </div>
          </div>
        </div>
        <p class="copyr"><script>document.write(new Date().getFullYear());</script> &copy; Msys India Ltd. All Rights Reserved.</p>
      </footer>

    </div>

    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/default.js"></script>
    <script type="text/javascript" src="js/jqueryForm.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/modernizr.js"></script>
    <script type="text/javascript">
      var url = window.location;

      /*if(url.href.indexOf("product.php") > -1) {
        var loc = window.location.href.replace("product.php","category.php").split("?pro");
        var newLoc = loc[0]+"?cat="+$("#ctgName").val()+"&ref="+$("#ctgID").val();
      } else {
        var newLoc = url.href;
      }*/

      /*$('.masterMenu a').filter(function() {
        return this.href == newLoc;
      }).closest(".masterMenu").addClass('active');*/

      console.log(".masterMenu"+$("#masterID").val());
      $(".masterMenu"+$("#masterID").val()).addClass("active");

      $(document).ready(new function() {
        $(".navbar-nav li").find("a").click(function(e) {
          e.preventDefault();
          window.location.href = $(this).attr("href");
        });

        if($(window).width() > 768) {
          $(".dropdown").hover(function() {
            $('.dropdown-menu', this).stop().fadeIn("fast");
          },function() {
            $('.dropdown-menu', this).stop().fadeOut("fast");
          });


          $(document).on("scroll", function(){
            if($(document).scrollTop() > 150){
              $(".navbar").addClass("fixedNav animated fadeInDown");
            } else {
              $(".navbar").removeClass("fixedNav animated fadeInDown");
            }
          });
        }
        
        $("a.primary").mouseenter(function() {
            $(".level2").fadeOut("fast");
            $($(this).attr("rel")).stop(true,true).slideDown("fast");
        });

        $(".level1").mouseleave(function() {
            $(".level2").fadeOut("fast");
        });

      });
    </script>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-17563549-5"></script>
    <script type="text/javascript">
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'UA-17563549-5');
    </script>      <script type="text/javascript">
        $(document).ready(function() {
            $("input[type='number']").keypress(function(e) {
                onlyNumerics(e);
            });
            
            $("#name, #company").keypress(function(e) {
                preventNumbers(e);
            });
            
          $("#send").click(function() {
            if(validateReq("#name") & validateReq("#email") & validateEmail("#email") & validateReq("#mobile",10) & validateReq("#company") & validateReq("#verify") & validateReq("#comments")) {
              $("#send").attr("disabled", "disabled");
              $("#send").text("Processing..");

              $("#queryForm").ajaxForm({success: function(response) {
                console.log(response);
                $("#send").removeAttr("disabled");
                $("#send").text("Send Query");

                  if(response=="OK") {
                      alert("Thank you for your message\nWe will get back to you soon!");
                      $("#queryForm")[0].reset();
                  } else {
                      alert(response);
                  }
              }}).submit();
            }
          });
        });
      </script>
  </body>

<!-- Mirrored from its.dlink.co.in/support.php by HTTrack Website Copier/3.x [XR&CO'2014], Sat, 04 May 2024 10:49:13 GMT -->
</html>