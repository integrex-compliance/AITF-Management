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
        <p class="copyr"><script>document.write(new Date().getFullYear());</script> &copy; MSYC Connect Ltd. All Rights Reserved.</p>
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
    </script>      <script src="js/responsiveslides.min.js"></script>
      <script type="text/javascript">
        $(function () { $("#slides").responsiveSlides({ auto: true, pagination: false, nav: true, fade: 800 }); });
      </script>
  </body>


</html>