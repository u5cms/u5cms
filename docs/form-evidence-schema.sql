-- Apply once, manually, on the instance enabling archived-SAML form evidence.
-- Skip the ALTER if sender_evidence_json already exists as a nullable MEDIUMTEXT.
ALTER TABLE formdata ADD COLUMN sender_evidence_json MEDIUMTEXT NULL;

CREATE TABLE form_evidence_mail (
    evidence_id CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    attempt_id CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    role_name ENUM('admin','owner','receipt') NOT NULL,
    event_state ENUM('started','accepted','unconfirmed') NOT NULL,
    created_utc DATETIME NOT NULL,
    mac CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    PRIMARY KEY (attempt_id,event_state),
    KEY submission_events (evidence_id,role_name)
) ENGINE=InnoDB;
