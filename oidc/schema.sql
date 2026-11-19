-- Only needed for the explicitly selected verified_email identity profile.
-- Usernames use the same canonical bytes as the CMS; identifiers are SHA-256 digests.
CREATE TABLE IF NOT EXISTS u5oidc_bindings (
    issuer_key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    subject_key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    username VARBINARY(255) NOT NULL,
    created BIGINT UNSIGNED NOT NULL,
    last_seen BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (issuer_key,subject_key),
    UNIQUE KEY issuer_username (issuer_key,username)
) ENGINE=InnoDB;
