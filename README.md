# AITF Management System

AITF Management System is a web-based management application developed for managing certification, audit, client, auditor, documentation, and related operational processes of AITF.

The application is primarily built using **PHP**, with HTML, CSS, and JavaScript for the frontend and MySQL/database components for data management.

## Features

### Customer & Client Management

* Add and manage customers
* Maintain customer information and records
* Manage customer-related documents
* Track certification-related information

### Application Management

* Audit application management
* Application review
* Application received processing
* Assessment planning
* Audit planning and scheduling

### Contract Review

* Contract review management
* Contract review forms and records
* Contract-related documentation

### Audit Management

The system provides modules for different stages of the audit and certification process, including:

* Stage I audit
* Stage II audit
* Audit notification and assignment
* Audit scheduling
* Surveillance Audit 1
* Surveillance Audit 2
* Audit analysis
* Auditor management

### Certification Management

* Certification records
* Certificate generation
* Certificate printing/PDF generation
* Certification-related documentation
* Certification status management

### Document & Letter Management

The system contains modules for generating and managing various operational documents, letters, forms, and notifications used during the certification process.

### Payment Management

* Payment records
* Payment-related customer information
* Payment processing/supporting records

### Auditor Management

* Auditor records
* Auditor assignment
* Auditor-related guidelines
* Audit allocation and management

### Notifications

The system includes modules for audit notifications, reminders, due notifications, suspension/withdrawal-related documents, and other operational communications.

## Technology Stack

| Technology | Usage                       |
| ---------- | --------------------------- |
| PHP        | Backend / Application Logic |
| MySQL      | Database                    |
| HTML       | Application Structure       |
| CSS        | Styling                     |
| JavaScript | Client-side functionality   |
| Apache     | Web Server / Hosting        |
| cPanel     | Deployment environment      |

## Project Structure

```text
AITF-Management/
│
├── config/                  # Application configuration
├── database/                # Database-related files
├── include/                 # Shared PHP components
├── css/                     # Stylesheets
├── js/                      # JavaScript files
├── images/                  # Application images
├── fonts/                   # Fonts used by the application
├── guidelines/              # Guidelines and supporting resources
├── notification/            # Notification-related resources
├── text/                    # Text/templates
├── website/                 # Website-related resources
│
├── ContractReview.php       # Contract review
├── AssessmentPlanning.php   # Assessment planning
├── AuditSchedule.php        # Audit scheduling
├── auditApplication.php     # Audit application
├── auditPlanning.php        # Audit planning
├── SurveillanceAudit1.php   # Surveillance audit 1
├── SurveillanceAudit2.php   # Surveillance audit 2
├── CertificatePrintPdf.php  # Certificate PDF generation
├── addCustomer.php          # Customer management
├── addAuditors.php          # Auditor management
├── addPayment.php           # Payment management
├── admin.php                # Administrative interface
└── ...
```

## Certification Workflow

The application supports the management of the certification lifecycle, including:

```text
Customer
   ↓
Application
   ↓
Application Review
   ↓
Contract Review
   ↓
Assessment Planning
   ↓
Audit Notification & Assignment
   ↓
Audit Scheduling
   ↓
Stage I / Stage II Audit
   ↓
Audit Analysis & Review
   ↓
Certification
   ↓
Surveillance Audits
```

The exact workflow and available steps can vary depending on the certification process and records maintained in the system.

## Requirements

A typical deployment requires:

* PHP-compatible web server
* Apache or equivalent server
* MySQL/MariaDB
* PHP extensions required by the application
* Web browser
* Database configured for the application

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/integrex-compliance/AITF-Management.git
```

### 2. Configure the database

Create the required MySQL database and import the database structure/data provided in the project's `database` directory.

Update the application's database configuration according to the deployment environment.

### 3. Configure the web server

Place the project inside the web server's document root or configure a virtual host pointing to the project directory.

### 4. Configure PHP

Make sure the required PHP extensions are enabled and that the server's PHP version is compatible with the application.

### 5. Run the application

Open the configured application URL in a web browser.

## Deployment

The project contains deployment-related configuration such as:

* `.htaccess`
* `.cpanel.yml`

This allows the application to be deployed in a typical Apache/cPanel hosting environment.

## Security

Before deploying the application to production:

* Keep database credentials outside publicly accessible files where possible.
* Do not commit passwords, API keys, or other secrets.
* Use HTTPS.
* Configure appropriate file and directory permissions.
* Validate and sanitize user input.
* Use secure authentication and authorization controls.
* Keep PHP and server dependencies updated.
* Restrict access to administrative functionality.

## Repository

**AITF Management**

[GitHub Repository](https://github.com/integrex-compliance/AITF-Management?utm_source=chatgpt.com)

## License

This project is proprietary software. Unless explicitly stated otherwise by the repository owner, the source code should not be redistributed, modified for external use, or commercially reused without permission from the owner.
