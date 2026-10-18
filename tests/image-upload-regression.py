#!/usr/bin/env python3
"""Isolated multipart/browser regression checks; never connects to a CMS DB.

Requires PHP with GD, Python Pillow and Node with Playwright/Chromium.
Optional U5_TEST_PHP, U5_TEST_PHP_ARGS (JSON array), U5_TEST_CHROMIUM.
"""
import json
import os
from pathlib import Path
import shutil
import socket
import struct
import subprocess
import tarfile
import tempfile
import time
import urllib.request

from PIL import Image, ImageDraw, TiffImagePlugin

REPO = Path(__file__).resolve().parent.parent


def fixtures(directory):
    directory.mkdir()
    image = Image.new('RGB', (80, 50), 'white')
    draw = ImageDraw.Draw(image)
    draw.rectangle((0, 0, 39, 24), fill='red')
    draw.rectangle((40, 0, 79, 24), fill='lime')
    draw.rectangle((0, 25, 39, 49), fill='blue')
    for extension, kind in [('jpg', 'JPEG'), ('png', 'PNG'), ('gif', 'GIF'), ('webp', 'WEBP'), ('bmp', 'BMP'), ('avif', 'AVIF')]:
        image.save(directory / ('sample.' + extension), kind)
    transparent = image.convert('RGBA')
    transparent.putpixel((70, 40), (0, 0, 0, 0))
    transparent.save(directory / 'transparent.png')
    transparent.save(directory / 'transparent.tif')
    for compression in ['raw', 'tiff_lzw', 'tiff_adobe_deflate', 'packbits', 'jpeg']:
        image.save(directory / ('sample-' + compression + '.tif'), compression=compression)
    for orientation in range(1, 9):
        info = TiffImagePlugin.ImageFileDirectory_v2()
        info[274] = orientation
        image.save(directory / ('orientation-' + str(orientation) + '.tiff'), tiffinfo=info)
    image.save(directory / 'multiple.tiff', save_all=True, append_images=[Image.new('RGB', image.size, 'black')])
    # Small big-endian RGB TIFF, independent of Pillow's output byte order.
    tags = [(256, 4, 1, 80), (257, 4, 1, 50), (258, 3, 3, 134),
            (259, 3, 1, 1), (262, 3, 1, 2), (273, 4, 1, 140),
            (274, 3, 1, 1), (277, 3, 1, 3), (278, 4, 1, 50), (279, 4, 1, 12000)]
    data = b'MM\x00\x2a' + struct.pack('>I', 8) + struct.pack('>H', len(tags))
    for tag, kind, count, value in tags:
        data += struct.pack('>HHI', tag, kind, count)
        data += struct.pack('>H', value) + b'\x00\x00' if kind == 3 and count == 1 else struct.pack('>I', value)
    data += b'\x00' * 4 + struct.pack('>HHH', 8, 8, 8) + image.tobytes()
    (directory / 'big-endian.tif').write_bytes(data)
    huge = bytearray((directory / 'sample-raw.tif').read_bytes())
    offset = struct.unpack_from('<I', huge, 4)[0]
    for index in range(struct.unpack_from('<H', huge, offset)[0]):
        position = offset + 2 + index * 12
        tag, kind = struct.unpack_from('<HH', huge, position)
        if tag in (256, 257):
            struct.pack_into('<H' if kind == 3 else '<I', huge, position + 8, 50000)
    (directory / 'too-many-pixels.tif').write_bytes(huge)
    (directory / 'broken.heic').write_bytes(b'not an image')
    (directory / 'broken.tif').write_bytes(b'II\x2a\x00broken')
    (directory / 'sample.svg').write_text('<svg xmlns="http://www.w3.org/2000/svg" width="80" height="50"><rect width="80" height="50" fill="red"/></svg>')
    with tarfile.open(REPO / 'u5admin/image-formats/vendor/source/libheif-1.23.6.tar.gz') as archive:
        data = archive.extractfile('libheif-1.23.6/examples/example.heic').read()
    for name in ['example.heic', 'example.heif', 'example.HIF']:
        (directory / name).write_bytes(data)
    return image


def test_site(site):
    admin = site / 'u5admin'
    admin.mkdir(parents=True)
    (site / 'r').mkdir()
    (site / 'fileversions').mkdir()
    (site / 'upload').mkdir()
    (site / 'responses').mkdir()
    for name in ['sendfile.php', 'sendfile2.php', 'sendfilea.php', 'sendfilea2.php',
                 'extendedimage.inc.php', 'image-upload-limits.inc.php', 'image-upload.js',
                 'image-upload-worker.js', 'publishupload.inc.php', 't1.php', 't2.php',
                 'archivecheck.inc.php', 'shortcut.js']:
        shutil.copy2(REPO / 'u5admin' / name, admin / name)
    shutil.copytree(REPO / 'u5admin/image-formats', admin / 'image-formats')
    shutil.copy2(REPO / 'configallowedfileextensions.php', site / 'configallowedfileextensions.php')
    shutil.copy2(REPO / 'upload/spinner.gif', site / 'upload/spinner.gif')
    (admin / 'iniset.inc.php').write_text('<?php error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED);')
    (admin / 'backendcss.php').write_text('')
    (admin / 'blank.php').write_text('')
    (admin / 'connect.inc.php').write_text('''<?php
require_once(__DIR__.'/../config.php');
if ($_SERVER['REQUEST_METHOD']==='POST') ob_start(function($body) { file_put_contents(__DIR__.'/../responses/'.basename($_POST['name']).'.html',$body);return $body; });
function tnuoc($a) { return is_array($a)?count($a):0; }
function mysql_real_escape_string($s) { return addslashes($s); }
function mysql_query($s) { file_put_contents(__DIR__.'/../queries.log',$s."\\n",FILE_APPEND); return true; }
function mysql_fetch_array($a) { return array('deleted'=>'0','typ'=>'i'); }
function trxlog($s) { file_put_contents(__DIR__.'/../trx.log',$s."\\n",FILE_APPEND); }
''')
    (site / 'config.php').write_text('''<?php
$mymail='testmail';$host='localhost';$username='tester';$password='testpassword';$db='test';$sticksessiontoip='no';$archiveRqHIADRI='no';$cssRqHIADRI='no';$lan1na='de';$lan2na='fr';$lan3na='it';$lan4na='rm';$lan5na='en';
''')
    (site / 'testparent.php').write_text('''<!doctype html><script>
window.pending=0;window.upstart=function(){pending++};window.upend=function(){pending--};
window.files={location:{href:'blank.php'}};
</script><iframe id="upload" src="u5admin/<?php echo isset($_GET['album'])?'sendfilea.php':'sendfile.php'; ?>?name=<?php echo urlencode($_GET['name']); ?>&typ=<?php echo urlencode($_GET['typ']); ?>&l=_de" style="width:100%;height:300px"></iframe>''')


def main():
    with tempfile.TemporaryDirectory(prefix='u5-upload-test-') as temporary:
        work = Path(temporary)
        expected = fixtures(work / 'fixtures')
        site = work / 'site'
        test_site(site)
        with socket.socket() as listener:
            listener.bind(('127.0.0.1', 0))
            port = listener.getsockname()[1]
        php = [os.environ.get('U5_TEST_PHP', 'php')] + json.loads(os.environ.get('U5_TEST_PHP_ARGS', '[]'))
        for source in [*REPO.glob('u5admin/image-upload*.php'), REPO / 'u5admin/extendedimage.inc.php',
                       *[REPO / 'u5admin' / name for name in ['sendfile.php','sendfile2.php','sendfilea.php','sendfilea2.php','publishupload.inc.php','upload.php','uploada.php']]]:
            subprocess.run(php + ['-l', str(source)], check=True, stdout=subprocess.DEVNULL)
        for name in ['image-upload.js', 'image-upload-worker.js']:
            subprocess.run(['node', '--check', str(REPO / 'u5admin' / name)], check=True)
        with (work / 'server.log').open('w+') as log:
            server = subprocess.Popen(php + ['-d','upload_max_filesize=24M','-d','post_max_size=64M','-d','memory_limit=256M',
                                            '-S','127.0.0.1:'+str(port),'-t',str(site)], stdout=log, stderr=log)
            try:
                url = 'http://127.0.0.1:' + str(port)
                for _ in range(100):
                    try:
                        urllib.request.urlopen(url + '/u5admin/blank.php', timeout=1).close()
                        break
                    except OSError:
                        time.sleep(.05)
                else:
                    raise RuntimeError('PHP test server did not start')
                subprocess.run(['node', str(REPO / 'tests/image-upload-regression.cjs'), str(work), url], check=True)
            finally:
                server.terminate()
                server.wait(timeout=10)
            log.seek(0)
            errors = [line for line in log if 'Fatal error' in line or 'Uncaught' in line]
            assert not errors, errors
        rows = json.loads((work / 'results.json').read_text())
        transforms = {2:Image.Transpose.FLIP_LEFT_RIGHT, 3:Image.Transpose.ROTATE_180,
                      4:Image.Transpose.FLIP_TOP_BOTTOM, 5:Image.Transpose.TRANSPOSE,
                      6:Image.Transpose.ROTATE_270, 7:Image.Transpose.TRANSVERSE, 8:Image.Transpose.ROTATE_90}
        for row in rows:
            image = Image.open(row['path'])
            if row.get('heic'):
                assert image.size == (1280, 854), (row, image.size)
                continue
            reference = expected.transpose(transforms[row['orientation']]) if row.get('orientation') in transforms else expected
            assert image.size == reference.size, (row, image.size, reference.size)
            for x, y in [(10,10),(image.width-10,10),(10,image.height-10),(image.width-10,image.height-10)]:
                actual, target = image.convert('RGB').getpixel((x,y)), reference.getpixel((x,y))
                assert max(abs(a-b) for a,b in zip(actual,target)) < 40, (row, (x,y), actual, target)
        print('PASS: multipart/browser cases, original-byte checks, dimensions, TIFF orientations and image colors; '+str(len(rows))+' published images inspected.')


if __name__ == '__main__':
    main()
