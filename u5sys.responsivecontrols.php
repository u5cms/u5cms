<?php
// Progressive enhancement: navigation stays visible if JavaScript is unavailable.
if (u5RenderIsResponsive()) {
    echo '<button type="button" id="u5-navigation-toggle" aria-controls="navigation" aria-expanded="true" hidden>'.ehtml(def('Navigation','Navigation','Navigazione','Navigaziun','Navigation')).'</button>';
}
