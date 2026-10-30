<?php
namespace U5cms\Oidc;

use RuntimeException;

/** Stable issuer/client/subject bindings for the explicitly selected verified-email profile. */
final class Bindings
{
    private $db;
    private $issuerKey;

    public function __construct($db, $issuer, $clientId)
    {
        $this->db = $db;
        $this->issuerKey = hash('sha256', $issuer."\0".$clientId);
    }

    public function install($sql)
    {
        if ($this->db->query($sql) === false) throw new RuntimeException('Cannot create the OIDC identity binding table.');
        $this->check();
    }

    public function check()
    {
        $columns = $this->db->query('SELECT issuer_key,subject_key,username,created,last_seen FROM u5oidc_bindings LIMIT 0');
        if ($columns === false) throw new RuntimeException('OIDC binding table is missing. Run php oidc/check.php --install-bindings.');
        $columns->free();
        $result = $this->db->query('SHOW INDEX FROM u5oidc_bindings');
        if ($result === false) throw new RuntimeException('Cannot check OIDC binding uniqueness.');
        $indexes = array();
        while ($row = $result->fetch_assoc()) {
            if ((int)$row['Non_unique'] === 0 && $row['Sub_part'] === null) $indexes[$row['Key_name']][(int)$row['Seq_in_index']] = $row['Column_name'];
        }
        $result->free();$subject=false;$username=false;
        foreach ($indexes as $columns) {
            ksort($columns);$columns=array_values($columns);
            if ($columns === array('issuer_key','subject_key')) $subject=true;
            if ($columns === array('issuer_key','username')) $username=true;
        }
        if (!$subject || !$username) throw new RuntimeException('OIDC binding table requires both complete unique identity indexes.');
    }

    private function lookup($subjectKey)
    {
        $query = $this->db->prepare('SELECT username FROM u5oidc_bindings WHERE issuer_key=? AND subject_key=?');
        if ($query === false) throw new RuntimeException('Cannot read the OIDC identity binding.');
        try {
            $query->bind_param('ss', $this->issuerKey, $subjectKey);
            if (!$query->execute()) throw new RuntimeException('Cannot read the OIDC identity binding.');
            $query->bind_result($username);
            return $query->fetch() ? $username : null;
        } finally { $query->close(); }
    }

    public function resolve($subject, $username)
    {
        if (!is_string($username) || $username === '' || strlen($username) > 255) throw new RuntimeException('Invalid CMS identity for OIDC binding.');
        $this->check();
        $subjectKey = hash('sha256', $subject);
        $existing = $this->lookup($subjectKey);
        if ($existing !== null && !hash_equals($existing, $username)) {
            throw new RuntimeException('OIDC email changed for an existing subject; administrative identity review is required.');
        }
        if ($existing === null) {
            // Both unique indexes arbitrate first-login races. Never replace an existing binding.
            $query = $this->db->prepare('INSERT IGNORE INTO u5oidc_bindings (issuer_key,subject_key,username,created,last_seen) VALUES (?,?,?,?,?)');
            if ($query === false) throw new RuntimeException('Cannot create the OIDC identity binding.');
            try {
                $now=time();$query->bind_param('sssii',$this->issuerKey,$subjectKey,$username,$now,$now);
                if (!$query->execute()) throw new RuntimeException('Cannot create the OIDC identity binding.');
            } finally { $query->close(); }
            $existing = $this->lookup($subjectKey);
            if ($existing === null || !hash_equals($existing, $username)) {
                throw new RuntimeException('OIDC email is already bound to another identity; administrative identity review is required.');
            }
        }
        $query=$this->db->prepare('UPDATE u5oidc_bindings SET last_seen=? WHERE issuer_key=? AND subject_key=?');
        if ($query === false) throw new RuntimeException('Cannot update the OIDC identity binding.');
        try {
            $now=time();$query->bind_param('iss',$now,$this->issuerKey,$subjectKey);
            if (!$query->execute()) throw new RuntimeException('Cannot update the OIDC identity binding.');
        } finally { $query->close(); }
        return $existing;
    }
}
