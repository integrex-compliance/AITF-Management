<!DOCTYPE html>
<html lang="en">

<head>
    <?php include './headSource.php'; ?>
    <style>
        :root {
            --ink: #f5f5f5;
            --brand: #ffffff;
            --muted: #c0c0c0;
            --soft: #0a0a0a;
            --card: #0f0f0f;
        }

        body {
            background: #000000;
            color: var(--ink);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .training-hero {
            padding: 120px 0 60px;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.96), rgba(10, 10, 10, 0.92));
            position: relative;
            overflow: hidden;
        }

        .training-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02));
            pointer-events: none;
        }

        .training-hero h1 {
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #ffffff;
        }

        .training-hero p {
            max-width: 720px;
            color: var(--muted);
            font-size: 18px;
        }

        .section-nav {
            position: sticky;
            top: 80px;
            z-index: 900;
            padding: 12px 0 20px;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
            margin-top: 20px;
            border: 1px solid #1a1a1a;
        }

        .section-nav .nav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
        }

        .section-nav a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 14px;
            border: 1.5px solid #1f1f1f;
            border-radius: 12px;
            background: #0f0f0f;
            color: #f1f1f1;
            font-weight: 700;
            letter-spacing: 0.2px;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.35);
        }

        .section-nav a:hover {
            background: #111111;
            border-color: #ffffff;
            color: #ffffff;
            transform: translateY(-2px);
        }

        .training-section-block {
            padding: 80px 0;
            background: transparent;
        }

        .section-alt {
            background: #050505;
        }

        .training-section-block .section-heading {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .training-section-block h2 {
            font-weight: 800;
            color: #ffffff;
            margin: 0;
        }

        .section-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #ffffff;
            color: #000000;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            font-size: 12px;
        }

        .section-desc {
            max-width: 820px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 28px;
        }

        .iso-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        .iso-card {
            background: var(--card);
            border: 1.5px solid #1a1a1a;
            border-radius: 12px;
            padding: 16px 14px;
            text-align: center;
            font-weight: 800;
            color: #ffffff;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.45);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            cursor: pointer;
        }

        .iso-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.55);
            border-color: #ffffff;
        }

        .iso-card span {
            display: block;
            font-size: 15px;
            line-height: 1.4;
        }

        .detail-panel {
            margin-top: 24px;
            padding: 20px;
            border-radius: 14px;
            background: #0d0d0d;
            border: 1px solid #1a1a1a;
            box-shadow: 0 10px 26px rgba(0, 0, 0, 0.45);
        }

        .detail-title {
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 10px;
        }

        .detail-body {
            color: #cbd5e1;
            margin: 0;
            line-height: 1.6;
            white-space: pre-line;
        }

        .scroll-target {
            scroll-margin-top: 120px;
        }

        @media (max-width: 767px) {
            .training-hero {
                padding: 90px 0 40px;
            }

            .section-nav {
                top: 72px;
            }
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>

    <section class="training-hero">
        <div class="container">
            <h1>Training Programs</h1>
            <p>Pick a track and dive into globally recognized standards. Every section below mirrors the items you saw in the menu, now with quick descriptions so you can choose the right course with confidence.</p>

            <div class="section-nav">
                <div class="nav-grid">
                    <a href="#quality-management">Quality Management</a>
                    <a href="#safety-environment">Safety &amp; Environment</a>
                    <a href="#information-security">Information Security</a>
                    <a href="#lead-auditor">Lead Auditor Training</a>
                </div>
            </div>
        </div>
    </section>

    <section id="quality-management" class="training-section-block scroll-target">
        <div class="container">
            <div class="section-heading">
                <div class="section-kicker">Section</div>
                <h2>Quality Management</h2>
            </div>
            <p class="section-desc">Build the systems that keep products and services consistent, compliant, and trusted. These courses guide you through quality frameworks used across industries.</p>
            <div class="iso-grid">
                <div class="iso-card" data-group="quality" data-key="QMS ISO 9001:2015"><span>QMS ISO 9001:2015</span></div>
                <div class="iso-card" data-group="quality" data-key="MD QMS ISO 13485:2016"><span>MD QMS ISO 13485:2016</span></div>
                <div class="iso-card" data-group="quality" data-key="FSMS ISO 22000:2018"><span>FSMS ISO 22000:2018</span></div>
                <div class="iso-card" data-group="quality" data-key="HACCP ISO 22000:2018"><span>HACCP ISO 22000:2018</span></div>
            </div>
            <div class="detail-panel" id="detail-quality">
                <div class="detail-title">Select a training to see details</div>
                <p class="detail-body">Click any card above to view what that training covers.</p>
            </div>
        </div>
    </section>

    <section id="safety-environment" class="training-section-block scroll-target section-alt">
        <div class="container">
            <div class="section-heading">
                <div class="section-kicker">Section</div>
                <h2>Safety &amp; Environment</h2>
            </div>
            <p class="section-desc">Strengthen workplace safety and environmental stewardship. Learn how to implement controls, reduce risk, and keep operations resilient.</p>
            <div class="iso-grid">
                <div class="iso-card" data-group="safety" data-key="EMS ISO 14001:2015"><span>EMS ISO 14001:2015</span></div>
                <div class="iso-card" data-group="safety" data-key="OHSMS ISO 45001:2018"><span>OHSMS ISO 45001:2018</span></div>
                <div class="iso-card" data-group="safety" data-key="BCMS ISO 22301:2012"><span>BCMS ISO 22301:2012</span></div>
                <div class="iso-card" data-group="safety" data-key="ENMS ISO 50001:2018"><span>ENMS ISO 50001:2018</span></div>
            </div>
            <div class="detail-panel" id="detail-safety">
                <div class="detail-title">Select a training to see details</div>
                <p class="detail-body">Click any card above to view what that training covers.</p>
            </div>
        </div>
    </section>

    <section id="information-security" class="training-section-block scroll-target">
        <div class="container">
            <div class="section-heading">
                <div class="section-kicker">Section</div>
                <h2>Information Security</h2>
            </div>
            <p class="section-desc">Protect data, services, and infrastructure with proven security management practices. Get ready to lead audits and governance activities.</p>
            <div class="iso-grid">
                <div class="iso-card" data-group="infosec" data-key="ISMS ISO 27001:2013"><span>ISMS ISO 27001:2013</span></div>
                <div class="iso-card" data-group="infosec" data-key="ITSM ISO 20000-1:2018"><span>ITSM ISO 20000-1:2018</span></div>
            </div>
            <div class="detail-panel" id="detail-infosec">
                <div class="detail-title">Select a training to see details</div>
                <p class="detail-body">Click any card above to view what that training covers.</p>
            </div>
        </div>
    </section>

    <section id="lead-auditor" class="training-section-block scroll-target section-alt">
        <div class="container">
            <div class="section-heading">
                <div class="section-kicker">Section</div>
                <h2>Lead Auditor Training</h2>
            </div>
            <p class="section-desc">Elevate into audit leadership. Learn how to plan, execute, and report audits across multiple standards to drive continual improvement.</p>
            <div class="iso-grid">
                <div class="iso-card" data-group="lead" data-key="Lead Auditor Training"><span>Lead Auditor Training</span></div>
                <div class="iso-card" data-group="lead" data-key="QMS ISO 9001:2015 LEAD"><span>QMS ISO 9001:2015 LEAD</span></div>
                <div class="iso-card" data-group="lead" data-key="EMS ISO 14001:2015 LEAD"><span>EMS ISO 14001:2015 LEAD</span></div>
                <div class="iso-card" data-group="lead" data-key="OHSMS ISO 45001:2018 LEAD"><span>OHSMS ISO 45001:2018 LEAD</span></div>
                <div class="iso-card" data-group="lead" data-key="ISMS ISO 27001:2022 LEAD"><span>ISMS ISO 27001:2022 LEAD</span></div>
                <div class="iso-card" data-group="lead" data-key="INTEGRATED SYSTEMS"><span>INTEGRATED SYSTEMS</span></div>
            </div>
            <div class="detail-panel" id="detail-lead">
                <div class="detail-title">Select a training to see details</div>
                <p class="detail-body">Click any card above to view what that training covers.</p>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const detailContent = {
                quality: {
                    specifics: {
                        'QMS ISO 9001:2015': 'Standard focus: context, leadership, risk-based thinking, documented info, and process performance for consistent quality.',
                        'MD QMS ISO 13485:2016': 'Standard focus: medical device lifecycle controls, traceability, sterility/cleanliness, risk management, and regulatory dossiers.',
                        'FSMS ISO 22000:2018': 'Standard focus: food safety PRPs, hazard analysis, traceability, validation/verification, and supplier control.',
                        'HACCP ISO 22000:2018': 'Standard focus: defining CCPs, monitoring plans, corrective actions, verification, and recall readiness.'
                    },
                    generic: [
                        'Set quality objectives aligned to business risk and customer needs.',
                        'Map process interactions to reduce variation and defects.',
                        'Control documented information, versioning, and records retention.',
                        'Plan and run internal audits with solid sampling strategies.',
                        'Use nonconformity and CAPA workflows to drive correction and prevention.',
                        'Apply risk-based thinking to prioritize improvements.',
                        'Define KPIs and dashboards for process performance tracking.',
                        'Manage supplier quality, incoming inspection, and approvals.',
                        'Prepare and lead effective management reviews with evidence.',
                        'Measure customer satisfaction and complaint handling outcomes.',
                        'Coach teams on the process approach and ownership.',
                        'Build a continual improvement roadmap using PDCA.',
                        'Collect objective evidence for audits without slowing operations.',
                        'Clarify roles, responsibilities, and authority in the QMS.',
                        'Use readiness checklists to close gaps before certification.'
                    ]
                },
                safety: {
                    specifics: {
                        'EMS ISO 14001:2015': 'Standard focus: aspect/impact registers, compliance obligations, operational controls, and emergency preparedness.',
                        'OHSMS ISO 45001:2018': 'Standard focus: OH&S leadership, worker participation, hazard identification, and risk reduction.',
                        'BCMS ISO 22301:2012': 'Standard focus: business impact analysis, recovery strategies, exercising, and crisis communications.',
                        'ENMS ISO 50001:2018': 'Standard focus: energy baselines, metering plans, operational controls, and ROI for efficiency projects.'
                    },
                    generic: [
                        'Identify environmental and safety risks with structured assessments.',
                        'Set objectives and targets that reflect legal and stakeholder needs.',
                        'Design operational controls and work instructions for risk reduction.',
                        'Embed emergency preparedness and response testing routines.',
                        'Track leading and lagging indicators to monitor performance.',
                        'Strengthen worker participation and consultation mechanisms.',
                        'Integrate contractor and supplier controls into site requirements.',
                        'Investigate incidents with root cause analysis and follow-up.',
                        'Maintain compliance calendars and evidence for regulators.',
                        'Plan and execute internal audits with risk-based sampling.',
                        'Drive continual improvement through corrective actions.',
                        'Align documentation and training with real shop-floor practice.',
                        'Benchmark resilience and recovery capabilities across sites.',
                        'Report performance to leadership with clear visuals and trends.',
                        'Prepare for certification audits with gap reviews and drills.'
                    ]
                },
                infosec: {
                    specifics: {
                        'ISMS ISO 27001:2013': 'Standard focus: ISMS scope, risk treatment plans, Annex A control selection, and Statement of Applicability.',
                        'ITSM ISO 20000-1:2018': 'Standard focus: IT service lifecycle, SLAs/OLAs, incident and change governance, and continual service improvement.'
                    },
                    generic: [
                        'Define governance roles for information security and services.',
                        'Perform risk assessments and prioritize mitigations.',
                        'Select and tailor controls to match business context and threats.',
                        'Document policies, procedures, and standards users can follow.',
                        'Implement access control, logging, and monitoring disciplines.',
                        'Handle incidents with triage, containment, and lessons learned.',
                        'Protect infrastructure with network, endpoint, and cloud controls.',
                        'Safeguard data with classification, encryption, and retention rules.',
                        'Ensure supplier and third-party security through due diligence.',
                        'Plan and execute internal audits of controls and service processes.',
                        'Measure effectiveness with KPIs and management review inputs.',
                        'Integrate business continuity and disaster recovery expectations.',
                        'Train staff and build awareness to reduce human error risk.',
                        'Maintain evidence for certification and customer assurances.',
                        'Drive continual improvement through corrective actions and metrics.'
                    ]
                },
                lead: {
                    specifics: {
                        'Lead Auditor Training': 'Standard focus: audit program management, sampling, interviews, and strong reporting skills.',
                        'QMS ISO 9001:2015 LEAD': 'Standard focus: process-based QMS audits, effectiveness checks, and improvement findings.',
                        'EMS ISO 14001:2015 LEAD': 'Standard focus: environmental compliance, operational controls, and performance evidence.',
                        'OHSMS ISO 45001:2018 LEAD': 'Standard focus: worker participation, risk controls, incident follow-up, and safety culture.',
                        'ISMS ISO 27001:2022 LEAD': 'Standard focus: Annex A control testing, evidence quality, and risk-traceable findings.',
                        'INTEGRATED SYSTEMS': 'Standard focus: auditing multiple ISO standards together with unified sampling and reporting.'
                    },
                    generic: [
                        'Plan audit programs, scope, and criteria with risk priority.',
                        'Build checklists that trace requirements to evidence types.',
                        'Use effective interviewing and note-taking techniques.',
                        'Sample processes, records, and observations with rigor.',
                        'Write clear nonconformities with requirement/evidence/gap.',
                        'Facilitate opening and closing meetings professionally.',
                        'Manage time on-site and keep the audit on schedule.',
                        'Handle conflicts and objections while staying objective.',
                        'Evaluate corrective action plans for adequacy and timelines.',
                        'Report findings with impact, risk, and priority levels.',
                        'Lead audit teams, assign roles, and review working papers.',
                        'Maintain independence, ethics, and confidentiality.',
                        'Coach auditees on evidence readiness and best practices.',
                        'Prepare for certification and surveillance audit expectations.',
                        'Track follow-up and verify effectiveness of corrections.'
                    ]
                }
            };

            function updateDetailPanel(group, key) {
                const panel = document.getElementById(`detail-${group}`);
                if (!panel) return;
                const titleEl = panel.querySelector('.detail-title');
                const bodyEl = panel.querySelector('.detail-body');
                const content = detailContent[group];
                if (!content) return;
                const specific = content.specifics?.[key] || 'This course covers core requirements and audit expectations.';
                const genericLines = content.generic || [];
                const lines = [specific, ...genericLines];
                titleEl.textContent = key;
                bodyEl.textContent = lines.join('\n');
            }

            document.querySelectorAll('.iso-card').forEach(card => {
                card.addEventListener('click', () => {
                    const group = card.dataset.group;
                    const key = card.dataset.key;
                    updateDetailPanel(group, key);
                });
            });
        });
    </script>

    <?php include './footer.php'; ?>
</body>

</html>
