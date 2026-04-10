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
                            <h2><span>CONTACT </span>US</h2>
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
                <div class="col-md-8 layout_padding_2">
                    <p>Feel free to talk with our online representative at any time you please using our Live Chat
                        system on our website or one of the below instant messaging programs.</p>
                    <p>Please be patient we will respond you within 24 Hours.</p>
                    <p><b>Phone General Inquiries:</b></p>
                    <div class="address-info">
                        <h2>Address Info</h2>
                        <ul>
                            <li>
                                <strong>AITF Management Certification Limited</strong><br />
                                Devi Indrayani Wing B 26,<br />
                                Gat No. 382+3+4/1, Near IT Park,<br />
                                Talwade, Pune - 411 062. Maharashtra, India.<br />

                                Mobile : +91 9881691012<br />
                                E-mail: <a href="mailto:aitfindia@gmail.com">aitfindia@gmail.com</a><br />
                                Website: <a href="index.html">www.aitf.co.in</a>
                            </li>
                            <li>
                                <br />
                                <strong>Business Hours</strong>
                                <p>Friday - Wednesday 9.30am to 6.30pm</p>
                                <p>Thursday 9.30am to 2.30pm</p>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <h2>Contact Form</h2>

                    <form action="" method="post">

                        <fieldset>

                            <?php if (!isset($errorC) || $errorC == true) { ?>

                                <label for="name" class="blocklabel">Your Name*</label>
                                <p class="<?php if (isset($the_nameclass))
                                    echo $the_nameclass; ?>"><input name="yourname"
                                        class="input_bg" type="text" id="name"
                                        value='<?php if (isset($the_name))
                                            echo $the_name ?>' /></p>


                                    <label for="email" class="blocklabel">E-Mail*</label>
                                    <p class="<?php if (isset($the_emailclass))
                                            echo $the_emailclass; ?>"><input name="email"
                                        class="input_bg" type="text" id="email"
                                        value='<?php if (isset($the_email))
                                            echo $the_email ?>' /></p>


                                    <label for="website" class="blocklabel">Website</label>
                                    <p><input name="website" class="input_bg" type="text" id="website"
                                            value="<?php if (isset($the_website))
                                            echo $the_website ?>" /></p>


                                    <label for="message" class="blocklabel">Your Message*</label>
                                    <p class="<?php if (isset($the_messageclass))
                                            echo $the_messageclass; ?>"><textarea
                                        name="message" class="textarea_bg" id="message" cols="20"
                                        rows="7"><?php if (isset($the_message))
                                            echo $the_message ?></textarea></p>
                                    <input type="hidden" id="myemail" name="myemail"
                                        value="<?php echo $email_adress_reciever; ?>" />
                                <input type="hidden" id="myblogname" name="myblogname"
                                    value="<?php echo $name_of_your_site; ?>" />
                                <div class="clearfix"></div>
                                <input name="Send" type="submit" value="SUBMIT" class="button medium align" id="send" />
                            <?php } else { ?>
                                <div class="success">
                                    <div class="message-box-wrap">
                                        Your message has been <strong>Received Successfully!</strong> Thank you!</div>
                                </div>
                            <?php } ?>
                        </fieldset>
                    </form>
                </div>

            </div>

        </div>
    </div>

    <div class="section margin-top_50">
        <div class="container">
            <div class="row">
                <div class="col-md-12 layout_padding_2">
                    <div class="full">
                        <p>Find the Address</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 layout_padding_2">
                    <div class="full">
                        <iframe class="google-map" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
                            width="100%" height="300px"
                            src="https://maps.google.co.in/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=Devi+Indrayani,+Talwade,+Pimpri+Chinchwad,+Maharashtra&amp;aq=0&amp;oq=deviindrayani+&amp;sll=18.815427,76.775144&amp;sspn=11.960868,14.128418&amp;ie=UTF8&amp;hq=Devi+Indrayani,&amp;hnear=Talwade,+Pimpri+Chinchwad,+Pune,+Maharashtra&amp;ll=18.686254,73.784213&amp;spn=0.048643,0.006295&amp;t=m&amp;output=embed"></iframe><br /><small><a
                                href="https://maps.google.co.in/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q=Devi+Indrayani,+Talwade,+Pimpri+Chinchwad,+Maharashtra&amp;aq=0&amp;oq=deviindrayani+&amp;sll=18.815427,76.775144&amp;sspn=11.960868,14.128418&amp;ie=UTF8&amp;hq=Devi+Indrayani,&amp;hnear=Talwade,+Pimpri+Chinchwad,+Pune,+Maharashtra&amp;ll=18.686254,73.784213&amp;spn=0.048643,0.006295&amp;t=m"
                                style="color:#0000FF;text-align:left" target="blank">View Larger Map</a></small>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include './footerSource.php'; ?>

</body>

</html>