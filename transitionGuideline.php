<!DOCTYPE html>
<html lang="en">

<head>
    <?php include './headSource.php'; ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            color: #2d3748;
            line-height: 1.7;
            background-color: #f8fafc;
        }

        /* Header Section */
        .guideline-header {
            background: linear-gradient(135deg, #1a4b8c 0%, #0d2b4e 100%);
            color: white;
            padding: 180px 0 70px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .guideline-header:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgcGF0dGVyblRyYW5zZm9ybT0icm90YXRlKDQ1KSI+PHJlY3Qgd2lkdGg9IjUwJSIgaGVpZ2h0PSI1MCUiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wMikiLz48L3BhdHRlcm4+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjcGF0dGVybikiLz48L3N2Zz4=');
            opacity: 0.3;
        }

        .guideline-header h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            display: inline-block;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            position: relative;
            display: inline-block;
        }

        .guideline-header h1:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: #e74c3c;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
        }

        .guideline-subtitle {
            font-size: 1.5rem;
            margin-bottom: 30px;
            color: #ecf0f1;
        }

        .document-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 50px;
        }

        .document-title {
            color: #2c3e50;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f1f1;
        }

        .transition-doc-header {
            background: #f8fafc;
            border: 1px solid #e9ecef;
            border-left: 5px solid #3498db;
            border-radius: 10px;
            padding: 28px 24px;
            margin-bottom: 30px;
        }

        .document-actions {
            margin: 30px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-download {
            background: #3498db;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-block;
        }

        .btn-download:hover {
            background: #2980b9;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .pdf-buttons-container {
            margin: 30px 0;
            text-align: center;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .btn-view-pdf,
        .btn-download-pdf {
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-top: 10px;
        }

        .btn-view-pdf {
            background: #3498db;
        }

        .btn-download-pdf {
            background: #2ecc71;
        }

        .btn-view-pdf:hover,
        .btn-download-pdf:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            color: white;
            text-decoration: none;
        }

        .btn-view-pdf:hover {
            background: #2980b9;
        }

        .btn-download-pdf:hover {
            background: #27ae60;
        }

        .btn-view-pdf i,
        .btn-download-pdf i {
            margin-right: 8px;
        }

        .document-viewer {
            width: 100%;
            height: 800px;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .guideline-header {
                padding: 145px 0 45px;
            }

            .guideline-header h1 {
                font-size: 2.2rem;
            }

            .guideline-subtitle {
                font-size: 1.2rem;
            }

            .document-container {
                padding: 20px 15px;
            }
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>

    <!-- Header Section -->
    <section class="guideline-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1>TRANSITION GUIDELINE</h1>
                    <p class="guideline-subtitle">AITF Transition Guideline for ISO 9001:2015</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Document Actions Section -->
    <section class="section pt-0">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="document-container">
                        <div class="document-actions mb-5">
                            <div class="text-center">
                                <h3 class="mb-4">Access the Complete Transition Guidelines</h3>
                                <div class="pdf-buttons-container">
                                    <a href="perfect-learn/text/transition_guideline.pdf" target="_blank"
                                        class="btn-view-pdf">
                                        <i class="fa fa-eye"></i> View Document
                                    </a>
                                    <a href="perfect-learn/text/transition_guideline.pdf" download
                                        class="btn-download-pdf">
                                        <i class="fa fa-download"></i> Download PDF
                                    </a>
                                </div>
                                <p class="mt-3 text-muted">Get the complete transition guideline document for ISO
                                    9001:2015</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <?php include './footerSource.php'; ?>

</body>

</html>
