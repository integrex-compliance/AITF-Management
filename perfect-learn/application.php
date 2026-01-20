<!DOCTYPE html>
<html lang="en">
    <!-- Basic -->
    <head>
        <?php include './headSource.php'; ?>
    </head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>
    <!-- Start Banner -->

    <div class="section margin-top_80">
        <div class="container">
            <div class="row">
                <div class="col-md-12 layout_padding_2">
                    <div class="full">
                        <div class="heading_main text_align_center">
                            <h2><span>APPLICATION </span>FORM</h2>
                        </div>                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- end section -->

    <div class="section margin-top_50">
        <div class="container">
            <div class="row">
                <div class="col-md-12 layout_padding_2">
                    <div class="full">
                        <div class="full">
                            <p>Application form for ISO Certification</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section margin-top_50">
        <div class="container">
            <div class="row">
                <div class="col-md-6 layout_padding_2">
                    <p>Application Form</p>
                </div>
                <div class="col-md-6">
                    <a class="hvr-radial-out button-theme" style="float:right" href="text/appeal_form.DOC">Download</a>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="full float-right_img">
                        <embed src="text/audit_application_form.pdf" width="1200px" height="700px">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section margin-top_50">
        <div class="container">
            <div class="row">
                <div class="col-md-12 layout_padding_2">
                    <div class="full">
                        <h3><b>SUBMIT APPLICATION FORM</b></h3>
                        <p>Submit Application form for ISO 9001:2015 Certification</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8 layout_padding_2">
                    <form action="./upload.php" method="post" enctype="multipart/form-data">
                        <p><label for="name">Name:</label><input type="text" name="name" id="name" placeholder="Name" value="" style="float: right"></p><br/>
                        <p><label for="cno">Contact No:</label><input type="text" name="cno" id="cno" placeholder="Contact No" style="float: right"></p><br/>
                        <p><label for="eid">Email:</label><input type="text" name="eid" id="eid" placeholder="Email ID" style="float: right"></p><br/>
                        <p><label for="oname">Organisation name:</label><input type="text" name="oname" id="oname" placeholder="Organisation Name" style="float: right"></p><br/>
                        <p>
                            <label for="file">Select a file:</label> <input type="file" name="userfile" id="file" style="float: right"> <br />
                            <input name="action" type="hidden" value="Upload File" style="float: right"/>
                            <input type="submit" value="Upload File" style="float: right"/>
                        <p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include './footerSource.php'; ?>

</body>
</html>