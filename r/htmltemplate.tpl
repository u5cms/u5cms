<!DOCTYPE html>
{{{meta}}}
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
<link href="favicon.ico" rel="shortcut icon" />
<link rel="stylesheet" href="r/cssbase.css?1694082138" type="text/css" />
<link rel="stylesheet" href="js/jquery.fancybox.min.css?1694082138" />
<script src="js/jquery.js"></script>
<script src="js/jquery.fancybox.min.js"></script>
<script src="js/u5_scripts.js" type="text/javascript"></script>
<script src="r/jsmobilespecific.css?1694082138"></script>
</head>
<body id="body"> 
<section class="skipLinks">{{{skiplinks}}}</section>
<main id="main">
  <header id="header" style="background:white;background:url([_logo_]);background-size:cover">
    <section id="metanavi"><div id="search"><a name="search"></a>{{{search}}}</div>
      <section id="languages">{{{languages}}}</section>
    </section>
      <section id="slogan">{{{slogan}}}</section>
  </header>
  <!-- <section id="navigationtop">
    <nav id="navTop"><a name="navigation"></a>{{{navigation}}}</nav> 
  </section> REMOVING HTML COMMENT TAG HERE = SWITCH SPLIT NAVIGATION ON. FURTHER: RENAME id="navLeft" TO id="navLeftSubTop" HERE IN THIS WINDOW! -->

<section id="outer">
<section id="navigation"><nav id="navLeft" style="float:left"><a name="navigation"></a>{{{navigation}}}</nav></section>
<article id="content"><a name="content"></a>{{{content}}}</article>
<aside id="news">{{{right}}}</aside>
<footer id="footer"><a name="contact"></a>{{{footer}}}</footer>
</section>  
</main>
<script>
function autoequalsone() {
if(location.href.indexOf('auto=1')>0)location.reload();
}
setTimeout("autoequalsone()",2222);
</script>
<script>
if(typeof u5mkmobile==="function" && !document.getElementById('nevermobile'))u5mkmobile();
</script>
</body>
</html> 