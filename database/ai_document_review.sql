CREATE TABLE IF NOT EXISTS ai_review_rules (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    rule_code VARCHAR(80) NOT NULL,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(120) DEFAULT NULL,
    rule_text TEXT NOT NULL,
    expected_value TEXT DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by VARCHAR(120) DEFAULT NULL,
    updated_by VARCHAR(120) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ai_review_rule_code (rule_code),
    KEY idx_ai_review_rules_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ai_review_history (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_id INT NOT NULL,
    requested_by VARCHAR(120) DEFAULT NULL,
    model_name VARCHAR(120) DEFAULT NULL,
    document_count INT NOT NULL DEFAULT 0,
    rules_count INT NOT NULL DEFAULT 0,
    status VARCHAR(40) NOT NULL DEFAULT 'pending',
    summary TEXT DEFAULT NULL,
    result_json LONGTEXT DEFAULT NULL,
    raw_response LONGTEXT DEFAULT NULL,
    error_message TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ai_review_history_customer (customer_id),
    KEY idx_ai_review_history_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ai_review_document_history (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    review_history_id INT UNSIGNED NOT NULL,
    customer_id INT NOT NULL,
    document_key VARCHAR(64) NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    document_label VARCHAR(255) NOT NULL,
    service_type VARCHAR(40) DEFAULT NULL,
    service_id VARCHAR(100) DEFAULT NULL,
    column_name VARCHAR(100) DEFAULT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_hash VARCHAR(64) DEFAULT NULL,
    file_size_bytes BIGINT DEFAULT NULL,
    model_name VARCHAR(120) DEFAULT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'completed',
    summary TEXT DEFAULT NULL,
    analyzed_by VARCHAR(120) DEFAULT NULL,
    analyzed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ai_doc_history_customer_doc (customer_id, document_key),
    KEY idx_ai_doc_history_review (review_history_id),
    KEY idx_ai_doc_history_analyzed (analyzed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO ai_review_rules (rule_code, title, category, rule_text, expected_value, is_active)
VALUES
('AITF-CLIENT-001', 'Client identity consistency', 'Client Details', 'Client legal name, address, and contact details should be consistent across application, agreement, notification, audit reports, certificate, and feedback documents.', 'Expected values must come from customer_info and attached document evidence. If a field is absent or conflicting, mark Manual verification required.', 1),
('AITF-SCOPE-001', 'Scope consistency', 'Scope', 'Certification scope should remain consistent across application, contract review, audit planning, audit reports, CDC decision, and certificate documents unless a documented change is present.', 'Expected scope must come from customer_info.scope, certificate_print.certificate_scope, or direct document evidence. Do not invent scope.', 1),
('AITF-DATE-001', 'Chronological date order', 'Dates', 'Application, agreement, audit planning, stage audit, CDC decision, certificate issue, expiry, surveillance, and feedback dates must be chronologically reasonable for the certification workflow.', 'If dates are missing, contradictory, or out of sequence, report the issue. If expected date is not available, use Manual verification required.', 1),
('AITF-ISO-001', 'ISO standard consistency', 'ISO Standard', 'ISO standard names and versions should match across client record, application, agreement, audit documents, CDC decision, and certificate.', 'Expected ISO standard must come from the client record or document evidence. If multiple standards exist, check each separately.', 1),
('AITF-MANDAY-001', 'Manday justification', 'Mandays', 'Mandays and auditor allocation should be present and justified by scope, technical area, employee strength, sites, and applicable standard where these values are available.', 'Do not calculate unsupported mandays. If required inputs are absent, use Manual verification required.', 1)
ON DUPLICATE KEY UPDATE rule_code = VALUES(rule_code);
