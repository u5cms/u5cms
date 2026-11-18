"""Ephemeral values for disposable browser fixtures; never persist or print."""
import os,secrets,hmac,hashlib

def ensure_fixture_environment():
    for name in ['U5CMS_FIXTURE_PASSWORD','U5CMS_FIXTURE_BODY']:
        if name not in os.environ:os.environ[name]=secrets.token_urlsafe(32)
        if len(os.environ[name])<32:raise RuntimeError('Disposable fixture value is too short: '+name)

def fixture_password_for(scope):
    ensure_fixture_environment()
    return hmac.new(os.environ['U5CMS_FIXTURE_PASSWORD'].encode(),scope.encode(),hashlib.sha256).hexdigest()
