<?php

/*
 * Broad compatibility catalogue for uploaded files.
 *
 * $extokTOTAL contains all supported file extensions and is used as the basis
 * for trusted back-end uploads. The public front-end list is derived from this
 * catalogue and removes formats that can contain active browser content,
 * executable code, installable software or security-sensitive profiles.
 *
 * Important:
 * A file extension is not a security boundary. Uploaded files must additionally
 * be checked by content, MIME detection, file signatures, size limits, storage
 * location, malware scanning and application-specific validation. Files must be
 * stored outside executable web paths, and download responses must use a safe
 * MIME fallback, Content-Disposition and X-Content-Type-Options: nosniff.
 *
 * Executable server-side script extensions such as php, phtml, asp, aspx, jsp,
 * cgi and similar formats are deliberately not included in $extokTOTAL.
 */

$extokTOTAL=array(

    // Plain text, documentation and markup
    'txt','text','log','nfo','readme','me',
    'md','markdown','mdown','mkd','mkdn','mdwn',
    'rst','rest',
    'adoc','asciidoc',
    'org',
    'tex','latex','ltx',
    'sty','cls','dtx','ins',
    'bib','bibtex','bbl',
    'texi','texinfo',
    'man','info',
    'rtf','rtfd',
    'wri',
    'wpd','wps',
    'abw','zabw',
    'lwp',
    '602',
    'pages',
    'odt','ott','odm','oth','fodt',
    'sxw','stw','sdw',
    'uot',

    // Microsoft Word and compatible word-processing formats
    'doc','docx','docm','docb',
    'dot','dotx','dotm',
    'wbk','asd',

    // Spreadsheet and tabular-data formats
    'csv','tsv','tab','psv','ssv',
    'dif','slk','sylk','prn',
    'xlr',
    'xls','xlsx','xlsm','xlsb',
    'xlt','xltx','xltm',
    'xla','xlam',
    'ods','ots','fods',
    'sxc','stc',
    'uos',
    'numbers',
    'gnumeric',

    // Presentation formats
    'ppt','pptx','pptm',
    'pps','ppsx','ppsm',
    'pot','potx','potm',
    'odp','otp','fodp',
    'sxi','sti',
    'uop',
    'key','keynote',
    'show','shw',

    // OpenDocument formulas, graphics and databases
    'odf','fodf','sxm',
    'odg','otg','fodg','sxd',
    'odc',
    'odi',
    'odb',

    // PDF, fixed-layout documents and form data
    'pdf',
    'xps','oxps',
    'djv','djvu',
    'fdf','xfdf','xdp',

    // E-books and electronic publications
    'epub',
    'mobi',
    'azw','azw3','azw4',
    'fb2','fb3',
    'prc',
    'lit',
    'ibooks','iba',
    'opf','ncx',
    'chm','hlp',

    // Comic-book containers
    'cbr','cbz','cb7','cbt','cba',

    // Desktop publishing, layout and design
    'indd','indt','idml','idms','inx',
    'qxp','qxd',
    'sla','scd',
    'pub',
    'pmd',
    'ai','ait',
    'eps','epsf','epsi',
    'ps',
    'psd','psb',
    'pspimage','psp',
    'xcf',
    'kra',
    'ora',
    'cpt',
    'afphoto','afdesign','afpub',
    'sketch',
    'fig',
    'xd',
    'clip',
    'sai','sai2',
    'mdp',
    'pdn',
    'cdr','cdt','cmx',
    'fh','fh3','fh4','fh5','fh6',
    'fh7','fh8','fh9','fh10','fh11',

    // Diagrams, mind maps and project-management formats
    'vsd','vsdx','vsdm',
    'vss','vssx','vssm',
    'vst','vstx','vstm',
    'vdx',
    'drawio','dio',
    'gliffy',
    'graffle','omnigraffle',
    'xmind',
    'mm',
    'mmap',
    'mindnode',
    'mpp','mpt','mpd',
    'xer',
    'gan',
    'gantt',
    'project',

    // E-mail, mailbox and message formats
    'eml','emlx',
    'mbox','mbx',
    'pst','ost',
    'msg','oft',
    'tnef','winmail',
    'pab',
    'olk',
    'olk14message',
    'olk15message',

    // Calendar, contact and directory-exchange formats
    'ics','ifb','vcs',
    'vcf','vcard',
    'contact',
    'wab',
    'ldif',

    // Generic structured-data formats
    'dat','data',
    'json','jsonl','ndjson',
    'jsonld','json5',
    'yaml','yml',
    'toml',
    'ini','cfg','conf','config','cnf',
    'properties',
    'plist',
    'strings',
    'cbor',
    'bson',
    'ubjson',
    'msgpack','mpack',
    'ion',
    'sdf',
    'avro',
    'parquet',
    'arrow',
    'feather',
    'orc',
    'protobuf','proto','pb',
    'thrift',
    'asn1','asn',
    'ber',
    'der',

    // XML and related schema, transformation and validation formats
    'xml',
    'xsd',
    'xsl','xslt',
    'xslfo','fo',
    'dtd',
    'ent',
    'rng','rnc',
    'sch',
    'cat',
    'wsdl',
    'soap',
    'xquery','xq','xql',
    'xpath',
    'xinclude','xinc',

    // SAML, federation, identity and access-management formats
    'saml',
    'saml2',
    'samlmetadata',
    'metadata',
    'idpmetadata',
    'spmetadata',
    'federationmetadata',
    'entitydescriptor',
    'entitiesdescriptor',
    'xacml',
    'wsfed',
    'wsfederation',
    'wstrust',
    'ws-trust',
    'openid',
    'oidc',
    'oauth',

    // XML signatures and XML encryption
    'xmlsig',
    'xmldsig',
    'dsig',
    'xenc',
    'xmlenc',

    // JOSE, JWT and related token formats
    'jwk',
    'jwks',
    'jwt',
    'jws',
    'jwe',
    'jose',
    'cose',

    // Verifiable Credentials and digital-identity documents
    'vc',
    'vp',
    'vcdm',
    'sdjwt',
    'sd-jwt',
    'sdjwtvc',
    'mdoc',
    'mdoc-cbor',
    'did',
    'didjson',

    // Certificates, certificate requests and trust material
    'cer','crt','cert',
    'pem',
    'csr','req',
    'crl',
    'ocsp',
    'tsr','tst',

    // PKCS and cryptographic container formats
    'p7b','p7c','p7m','p7s',
    'p8','p10','p12',
    'pfx',
    'pkcs7','pkcs8','pkcs10','pkcs12',

    // Keys, signatures and trust stores
    'key',
    'pub',
    'sshpub',
    'asc',
    'sig',
    'signature',
    'gpg',
    'pgp',
    'minisig',
    'cosign',
    'jks',
    'jceks',
    'keystore',
    'truststore',
    'bcfks',
    'keychain',
    'wallet',

    // Apple and mobile provisioning profiles
    'mobileconfig',
    'provisionprofile',

    // Database formats
    'accdb','accde','accdr','accdt',
    'mdb','mde',
    'db','db3','dbf',
    'pdb',
    'sqlite','sqlite3',
    'sqlite-shm','sqlite-wal',
    'db2',
    'fdb',
    'gdb',
    'ib',
    'frm','myd','myi',
    'ndf','mdf','ldf',
    'realm','realm.lock',
    'duckdb',

    // Database exports, backups and query data
    'sql',
    'bak','backup','bkp',
    'dump','dmp',

    // Statistical and analytical-data formats
    'hdf','hdf4','hdf5',
    'h5','he5',
    'nc','nc4','netcdf',
    'mat',
    'dta',
    'sav',
    'por',
    'zsav',
    'sps',
    'sas7bdat',
    'sas7bcat',
    'xpt',
    'arff',
    'rdata',
    'rds',
    'fst',

    // Common raster-image formats
    'bmp','dib','rle',
    'gif','gifv',
    'jpg','jpeg','jpe',
    'jif','jfif','jfi',
    'pjpeg','pjp',
    'png','apng',
    'mng','jng',
    'webp',
    'avif','avifs',
    'jxl',
    'ico',
    'cur',
    'ani',
    'icns',
    'wbmp',
    'qoi',
    'flif',
    'bpg',

    // HEIF, HEIC and ISO base-media image formats
    'heic','heics',
    'heif','heifs',
    'hif',
    'avci',
    'avcs',

    // JPEG XR and HD Photo formats
    'jxr',
    'hdp',
    'wdp',

    // TIFF and related formats
    'tif','tiff',
    'btf',
    'tf8',

    // JPEG 2000 and related formats
    'jp2',
    'j2k',
    'j2c',
    'jpc',
    'jpf',
    'jpx',
    'jpm',
    'mj2',

    // Stereoscopic and multi-picture image formats
    'mpo',
    'jps',
    'pns',

    // Legacy and professional raster-image formats
    'pct','pict','pic',
    'pcx','dcx',
    'pcd',
    'tga','targa',
    'vda','icb',
    'sgi',
    'rgb','rgba','bw',
    'ras','sun',
    'dds',
    'thm',
    'yuv','yuv10','yuv16',
    'uyvy','yuy2','nv12',

    // Portable bitmap and X11 image formats
    'pbm','pgm','ppm','pnm','pam','pfm',
    'xbm','xpm','xwd',

    // Amiga and Interchange File Format images
    'iff',
    'ilbm',
    'lbm',
    'ham',
    'ham8',

    // Brush, texture and pattern formats
    'pat',
    'gbr',
    'gih',
    'abr',

    // High-dynamic-range and cinema-image formats
    'hdr',
    'rgbe',
    'exr',
    'cin',
    'dpx',
    'pxr',

    // Camera RAW formats
    'raw',
    'dng',
    'cr2','cr3','crw',
    'nef','nrw',
    'arw','arw2',
    'srf','sr2',
    'raf',
    'orf',
    'rw2','rwl',
    'pef','ptx',
    '3fr','fff',
    'mef',
    'mos',
    'mrw',
    'erf',
    'kdc','dcr',
    'srw',
    'x3f',
    'iiq',
    'cap',
    'eip',
    'bay',
    'bmq',
    'cs1',
    'dc2',
    'dcs',
    'drf',
    'dri',
    'k25','kc2',
    'mdc',
    'rwz',
    'gpr',

    // Medical-image formats
    'dcm',
    'dicom',
    'ima',
    'nii',
    'nii.gz',
    'nrrd',
    'nhdr',
    'mha',
    'mhd',
    'minc',
    'mnc',

    // Microscopy and whole-slide image formats
    'ims',
    'czi',
    'lif',
    'nd2',
    'oib',
    'oif',
    'vsi',
    'svs',
    'ndpi',
    'mrxs',
    'scn',
    'bif',
    'qptiff',
    'ome.tif',
    'ome.tiff',
    'ome.xml',

    // Astronomy and scientific-image formats
    'fits',
    'fit',
    'fts',

    // Vector and metafile formats
    'svg',
    'svgz',
    'emf',
    'wmf',
    'emz',
    'wmz',
    'cgm',

    // Colour profiles and colour-table formats
    'icc',
    'icm',
    'iccmax',
    'acb',
    'aco',
    'ase',
    'act',
    'cxf',
    'cube',
    '3dl',
    'look',
    'lut',

    // Uncompressed and lossless audio formats
    'aif','aiff','aifc',
    'wav','wave',
    'bwf',
    'w64',
    'rf64',
    'au',
    'snd',
    'caf',
    'flac',
    'ape',
    'tak',
    'tta',
    'wv',
    'voc',

    // Common compressed audio formats
    'mp3',
    'mp2',
    'mpa',
    'mpc',
    'mpp',
    'aac',
    'adts',
    'm4a',
    'm4b',
    'm4p',
    'm4r',
    'wma',
    'ra',
    'ram',
    'rm',
    'ogg',
    'oga',
    'opus',
    'spx',
    'weba',

    // Surround, broadcast and high-resolution audio formats
    'ac3',
    'eac3',
    'dts',
    'dtshd',
    'dsf',
    'dff',

    // Mobile and speech audio formats
    'amr',
    'amrwb',
    'awb',
    'oma',
    'omg',
    'at3',
    'atrac',

    // Audiobook formats
    'aa',
    'aax',
    'aaxc',

    // Playlists
    'm3u',
    'm3u8',
    'pls',
    'xspf',

    // MIDI and karaoke formats
    'mid',
    'midi',
    'kar',
    'rmi',

    // Tracker and module music formats
    'xm',
    'mod',
    's3m',
    'it',
    'mtm',
    'umx',
    'mo3',
    '669',
    'far',
    'med',
    'okt',
    'stm',

    // Music notation and tablature formats
    'musicxml',
    'mxl',
    'mus',
    'musx',
    'sib',
    'sibx',
    'capx',
    'gp',
    'gp3','gp4','gp5','gp6','gp7',
    'gpx',
    'ptb',
    'tef',
    'ly',
    'abc',
    'mgu',

    // Digital-audio workstation and music-project formats
    'aup',
    'aup3',
    'als',
    'flp',
    'logic',
    'logicx',
    'band',
    'cpr',
    'sesx',
    'rpp',
    'reason',

    // Common video containers
    '3g2',
    '3gp',
    '3gp2',
    '3gpp',
    '3gpp2',
    'asf',
    'asx',
    'avi',
    'divx',
    'xvid',
    'flv',
    'f4v','f4p','f4a','f4b',
    'm4v',
    'mov',
    'qt',
    'mp4',
    'mpg',
    'mpeg',
    'mpe',
    'mkv',
    'mk3d',
    'mka',
    'mks',
    'webm',
    'ogv',
    'ogm',
    'rmvb',
    'rv',
    'wmv',

    // MPEG elementary and transport streams
    'm1v',
    'm2v',
    'mp2v',
    'mp4v',
    'mpv',
    'm2p',
    'mts',
    'm2ts',
    'm2t',
    'ts',
    'trp',

    // DVD and optical-disc video formats
    'vob',
    'ifo',
    'bup',
    'vcd',

    // Professional and broadcast video formats
    'mxf',
    'dv',
    'dif',
    'tod',
    'rec',
    'vdr',
    'wtv',
    'dvr-ms',
    'ismv',
    'isma',

    // Raw and codec-specific video streams
    'h264',
    'h265',
    'hevc',
    'avc',
    'av1',
    'vp8',
    'vp9',
    'ivf',
    'y4m',
    'mjpg',
    'mjpeg',

    // Professional digital-cinema and camera formats
    'r3d',
    'braw',
    'ari',
    'arx',
    'crm',
    'cine',

    // Legacy and specialised video formats
    'amv',
    'roq',
    'nsv',
    'swf',

    // Subtitle, caption and transcript formats
    'srt',
    'vtt',
    'webvtt',
    'ass',
    'ssa',
    'sub',
    'idx',
    'sbv',
    'ttml',
    'dfxp',
    'smi',
    'sami',
    'scc',
    'sup',
    'usf',
    'lrc',
    'stl',
    'itt',

    // Common 3D-model and scene formats
    '3dm',
    '3ds',
    'max',
    'obj',
    'ply',
    'fbx',
    'dae',
    'gltf',
    'glb',
    '3mf',
    'amf',
    'wrl',
    'vrml',
    'x3d',
    'x3db',
    'x3dv',
    'usd',
    'usda',
    'usdc',
    'usdz',
    'abc',
    'blend',
    'c4d',
    'lwo',
    'lws',
    'ma',
    'mb',

    // Game and animation model formats
    'md2',
    'md3',
    'md5',
    'md5mesh',
    'md5anim',
    'ms3d',
    'smd',
    'vta',
    'nif',
    'bvh',
    'ase',
    'ac',
    'u3d',
    'pov',
    'mesh',
    'off',
    'mqo',
    'pmx',
    'vmd',
    'vox',
    'voxels',

    // CAD and neutral product-model formats
    'dwg',
    'dxf',
    'dwf',
    'dwfx',
    'step',
    'stp',
    'stpz',
    'iges',
    'igs',
    'sat',
    'sab',
    'x_t',
    'x_b',
    'xmt_txt',
    'xmt_bin',
    'brep',

    // BIM and architectural formats
    'ifc',
    'ifczip',
    'ifcxml',
    'rvt',
    'rfa',
    'rft',
    'nwd',
    'nwc',
    'nwf',
    'pln',
    'pla',

    // Native CAD and mechanical-design formats
    'skp',
    'scad',
    'fcstd',
    'prt',
    'asm',
    'catpart',
    'catproduct',
    'cgr',
    'jt',
    'par',
    'psm',
    'ipt',
    'iam',
    'dgn',
    'sldprt',
    'sldasm',
    'slddrw',
    'eprt',
    'easm',
    'edrw',
    '3dxml',

    // Additive-manufacturing formats
    'stl',

    // Plotter and technical-drawing formats
    'plt',
    'hpgl',
    'hpg',
    'hp2',

    // Gerber and PCB manufacturing formats
    'ger',
    'gerber',
    'gbx',
    'gtl',
    'gbl',
    'gts',
    'gbs',
    'gto',
    'gbo',
    'gko',
    'gm1',
    'drl',
    'xln',

    // Electronic-design and PCB project formats
    'brd',
    'pcb',
    'schdoc',
    'pcbdoc',
    'kicad_pcb',
    'kicad_sch',
    'kicad_pro',
    'kicad_mod',
    'kicad_wks',
    'dsn',
    'ses',
    'fab',
    'bom',
    'pos',
    'pickplace',
    'centroid',
    'ncdrill',
    'ipc2581',

    // GPS and geographic exchange formats
    'gpx',
    'kml',
    'kmz',
    'geojson',
    'topojson',
    'gml',
    'wkt',
    'wkb',

    // GIS vector formats
    'shp',
    'shx',
    'prj',
    'cpg',
    'qix',
    'sbn',
    'sbx',
    'gpkg',
    'gdb',
    'mif',
    'osm',
    'pbf',
    's57',
    '000',
    'ntf',
    'mvt',

    // GIS raster and terrain formats
    'geotiff',
    'ecw',
    'sid',
    'asc',
    'grd',
    'dt0',
    'dt1',
    'dt2',
    'hgt',
    'adf',
    'dem',
    'bil',
    'bip',
    'bsq',
    'vrt',
    'aux',
    'aux.xml',
    'tfw',
    'jgw',
    'pgw',
    'wld',

    // Point-cloud and laser-scanning formats
    'las',
    'laz',
    'e57',
    'copc',
    'xyz',

    // Map packages and GIS project formats
    'mbtiles',
    'pmtiles',
    'tpk',
    'tpkx',
    'mpk',
    'mpkx',
    'lyr',
    'lyrx',
    'qgs',
    'qgz',
    'qlr',

    // Scientific and meteorological data
    'cdf',
    'grib',
    'grb',
    'grib2',
    'grb2',
    'edf',
    'bdf',
    'set',
    'fif',
    'fcs',
    'tdms',
    'lvm',
    'hsp',
    'spc',
    'dx',
    'jdx',
    'jcamp',
    'opj',
    'opju',
    'smr',
    'smrx',
    'abf',
    'axgx',
    'qdf',

    // Mass-spectrometry and laboratory-data formats
    'mzml',
    'mzxml',
    'mzdata',
    'wiff',
    'wiff2',

    // Bioinformatics sequence formats
    'fa',
    'fasta',
    'fna',
    'ffn',
    'faa',
    'frn',
    'fastq',
    'fq',

    // Bioinformatics alignment formats
    'sam',
    'bam',
    'cram',
    'aln',
    'clustal',
    'maf',

    // Genomic annotation and interval formats
    'bed',
    'bedgraph',
    'bigbed',
    'bb',
    'bigwig',
    'bw',
    'gff',
    'gff2',
    'gff3',
    'gtf',

    // Biological sequence and chromatogram formats
    'gb',
    'gbk',
    'genbank',
    'embl',
    'abi',
    'ab1',
    'scf',

    // Phylogenetic formats
    'phy',
    'phylip',
    'newick',
    'nwk',
    'nexus',
    'nex',
    'stockholm',
    'sto',

    // Genetic-variant and population-genetics formats
    'bcf',
    'ped',
    'map',
    'bim',
    'fam',
    'pgen',
    'pvar',
    'psam',
    '2bit',

    // Font formats
    'fnt',
    'fon',
    'otf',
    'ttf',
    'ttc',
    'otc',
    'woff',
    'woff2',
    'eot',
    'pfa',
    'pfb',
    'afm',
    'pfm',
    'bdf',
    'pcf',
    'snf',
    'suit',
    'dfont',
    'ufo',
    'ufoz',
    'glyphs',
    'glyphspackage',
    'sfd',
    'fea',

    // Common archive formats
    '7z',
    'zip',
    'zipx',
    'rar',
    'tar',
    'gz',
    'gzip',
    'bz',
    'bz2',
    'xz',
    'z',
    'zst',
    'zstd',
    'br',

    // Compound archive formats
    'tar.gz',
    'tar.bz2',
    'tar.xz',
    'tar.lz',
    'tar.lz4',
    'tar.zst',
    'tgz',
    'tbz',
    'tbz2',
    'txz',
    'tlz',
    'tzst',

    // Less-common compression and archive formats
    'lz',
    'lz4',
    'lzh',
    'lha',
    'lzma',
    'lzo',
    'lzip',
    'cab',
    'arj',
    'ace',
    'alz',
    'egg',
    'xar',
    'cpio',
    'ar',
    'sit',
    'sitx',
    'hqx',
    'sea',
    'pak',
    'zoo',
    'zpaq',

    // Software-package and extension containers
    'pkg',
    'deb',
    'rpm',
    'apk',
    'aab',
    'ipa',
    'msix',
    'appx',
    'appxbundle',
    'msixbundle',
    'nuget',
    'nupkg',
    'whl',
    'gem',
    'crate',
    'jar',
    'war',
    'ear',
    'sar',
    'nar',
    'xpi',
    'crx',
    'vsix',
    'oxt',

    // Optical-disc and disk-image formats
    'bin',
    'cue',
    'dmg',
    'iso',
    'img',
    'ima',
    'mds',
    'nrg',
    'ccd',
    'isz',
    'toast',
    'udf',
    'wim',
    'esd',
    'swm',
    'wimlib',

    // Virtual-disk and virtual-machine formats
    'vhd',
    'vhdx',
    'vmdk',
    'vdi',
    'qcow',
    'qcow2',
    'qed',
    'vpc',
    'hdd',
    'ova',
    'ovf',
    'vmx',
    'vmxf',
    'vbox',
    'vbox-prev',
    'pvm',
    'utm',
    'sparseimage',
    'sparsebundle',

    // Backup, temporary and partial-download files
    'old',
    'orig',
    'save',
    'tmp',
    'temp',
    'cache',
    'part',
    'partial',
    'download',
    'crdownload',

    // Generic binary, resource and container formats
    'binary',
    'blob',
    'file',
    'resource',
    'res',
    'assets',
    'bundle',
    'pack',
    'idx',
    'lib',

    // Game, emulator and ROM formats
    'dem',
    'gam',
    'game',
    'nes',
    'snes',
    'smc',
    'sfc',
    'gba',
    'gb',
    'gbc',
    'nds',
    'cia',
    'n64',
    'z64',
    'v64',
    'rom',
    'srm',
    'state',
    'mcr',
    'mc',
    'psv',
    'gcm',
    'wbfs',
    'wad',
    'rvz',
    'cso',
    'chd',
    'gdi',
    'nsp',
    'xci',
    'dol',
    'elf',
    'pk3',
    'pk4',
    'bsp',
    'vpk',

    // Tax, finance and accounting exchange formats
    'qif',
    'ofx',
    'ofc',
    'mt940',
    'sta',
    'camt',
    'pain',
    'pac',
    'bai',
    'bai2',

    // Genealogy formats
    'ged',
    'gedcom',

    // Note-taking and notebook formats
    'one',
    'onepkg',
    'onetoc2',
    'note',
    'enex',
    'nb',
    'ipynb',
    'rmd',
    'qmd',

    // Feed, outline and web-archive formats
    'opml',
    'rss',
    'atom',
    'atomsvc',
    'atomcat',
    'webarchive',
    'mhtml',
    'mht',

    // Additional legacy and specialised formats retained for compatibility
    'mus',
    'sib',
    'dot',
    'ico',
    'sdf',
    'sav',
    'mka',
    'pct',
    'thm'

);

/*
 * Normalise all entries and remove duplicates while preserving the order of
 * the first occurrence. Compound extensions such as tar.gz, nii.gz and
 * ome.tiff remain intact.
 */
$extokTOTAL=array_values(
    array_unique(
        array_filter(
            array_map(
                'strtolower',
                $extokTOTAL
            ),
            'strlen'
        )
    )
);

/*
 * Formats blocked for public front-end uploads.
 *
 * This denylist is intentionally defensive and also contains extensions that
 * are not currently present in $extokTOTAL. This prevents them from becoming
 * public-upload formats accidentally if they are added to the catalogue later.
 */
$extokFRONTendBLOCKED=array(

    // Server-side scripts and executable web handlers
    'php','php3','php4','php5','php7','php8','phtml','phar','inc',
    'asp','aspx','asa','ashx','asmx','axd',
    'jsp','jspx','jspf','jsw','jsv',
    'cgi','fcgi','pl','pm','py','pyc','pyo','rb',
    'sh','bash','zsh','ksh',

    // Web-server and runtime configuration files
    'htaccess','htpasswd','user.ini',

    // Active browser content and client-side code
    'htm','html','xht','xhtml','shtm','shtml','hta',
    'js','jse','mjs','cjs','jsx','tsx','css','wasm',
    'svg','svgz',
    'swf','dcr',
    'mht','mhtml','webarchive',

    // XML transformations and browser-renderable XML content
    'xml','xsl','xslt','xslfo','fo',
    'rss','atom','atomsvc','atomcat','opml',

    // Legacy help, shell-command and MIME-collision formats
    'chm','hlp','scf','stm','war',

    // Native executables, scripts and operating-system shortcuts
    'exe','com','scr','pif','cpl','dll','sys','drv','ocx',
    'bat','cmd','ps1','psm1','psd1','vbs','vbe','wsf','wsh',
    'reg','inf','ins','isp','url','website','lnk',
    'application','appref-ms','library-ms','search-ms','settingcontent-ms',
    'desktop','elf','dol','sea',

    // Installable applications, packages, extensions and code containers
    'msi','msp','mst','appx','appxbundle','msix','msixbundle',
    'apk','aab','ipa','pkg','deb','rpm',
    'jar','ear','sar','nar','class',
    'xpi','crx','vsix','oxt',
    'nuget','nupkg','whl','gem','crate',

    // Security-sensitive device and provisioning profiles
    'mobileconfig','provisionprofile'

);

$extokFRONTendBLOCKED=array_values(
    array_unique(
        array_filter(
            array_map(
                'strtolower',
                $extokFRONTendBLOCKED
            ),
            'strlen'
        )
    )
);

$extokFRONTend=array_values(
    array_diff(
        $extokTOTAL,
        $extokFRONTendBLOCKED
    )
);

$extokBACKend=$extokTOTAL;

unset($extokFRONTendBLOCKED);

?>