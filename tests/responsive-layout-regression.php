<?php
require dirname(__DIR__).'/render/layout.inc.php';
$checks = 0;
function responsiveCheck($actual, $expected, $label) {
    global $checks;
    if ($actual !== $expected) throw new RuntimeException($label.': '.var_export($actual, true));
    $checks++;
}
responsiveCheck(u5RenderIsResponsive(), false, 'standalone render defaults to legacy');
foreach (array(
    '<body>' => false,
    '<body class="u5-responsive">' => false,
    '<body data-u5-layout="responsive">' => true,
    "<BODY id='body' DATA-U5-LAYOUT = 'responsive'>" => true,
    '<body data-u5-layout=responsive>' => true,
    '<body data-u5-layout="RESPONSIVE">' => false,
    '<body data-u5-layout="legacy" data-u5-layout="responsive">' => false,
    '<!-- <body data-u5-layout="responsive"> --><body>' => false,
    '<script>var example = \'<body data-u5-layout="responsive">\';</script><body>' => false,
    '<style>/* <body data-u5-layout="responsive"> */</style><body>' => false,
    '<body data-example-data-u5-layout="responsive">' => false,
    '<body title=" data-u5-layout=\'responsive\'">' => false,
    '<body title="a > b" data-u5-layout="responsive">' => true,
    '<body data-u5-layout="responsive-later">' => false,
    '<body data-u5-layout>' => false,
    '<body data-u5-layout="responsive"' => false,
) as $template => $expected) {
    responsiveCheck(u5RenderUseTemplate($template), $expected, $template);
    responsiveCheck(u5RenderIsResponsive(), $expected, 'request state');
}
responsiveCheck(u5RenderUseTemplate(null), false, 'missing template');
// A failed regular expression must not opt an old/custom template in.
$limit = ini_get('pcre.backtrack_limit');
ini_set('pcre.backtrack_limit', '100');
responsiveCheck(u5RenderUseTemplate('<!--'.str_repeat('x', 1000).'--><body data-u5-layout="responsive">'), false, 'regex budget fails closed');
ini_set('pcre.backtrack_limit', $limit);
u5RenderUseTemplate('<body>');
responsiveCheck(u5RenderMediaStyle(640, 480, 'video'), 'width:640px;height:480px', 'legacy bytes');
responsiveCheck(u5RenderFrameAttributes(640, 480), '', 'legacy frame');
u5RenderUseTemplate('<body data-u5-layout="responsive">');
responsiveCheck(u5RenderMediaStyle(640, 480, 'video'), 'width:min(100%,640px);height:auto;aspect-ratio:640/480', 'video ratio');
responsiveCheck(u5RenderMediaStyle(640, 35, 'audio'), 'width:min(100%,640px);height:35px', 'audio controls');
foreach (array(array(0, 480), array(640, 0), array(-1, 480), array('auto', 480)) as $size) {
    responsiveCheck(u5RenderFrameAttributes($size[0], $size[1]), '', 'invalid frame dimensions preserve legacy');
    responsiveCheck(u5RenderMediaStyle($size[0], $size[1], 'video'), 'width:'.$size[0].'px;height:'.$size[1].'px', 'invalid media dimensions preserve legacy');
}
echo 'Responsive layout boundary checks: '.$checks." passed.\n";
