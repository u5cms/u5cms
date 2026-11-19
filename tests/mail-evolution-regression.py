#!/usr/bin/env python3
# Preserve the legacy construction comparison and exercise the new delivery boundary.
import pathlib,runpy
runpy.run_path(str(pathlib.Path(__file__).with_name('international-email-parity.py')),run_name='__main__')
