/*
This CSS formats the navigation if you hav NOT A split navigation.

If you want a split navigation, you have to

1: In PIDVESA's S, htmltemplate, switch the #navTop on

  <div id="navigationtop">
<div id="navTop"><a name="navigation"></a>{{{navigation}}}</div>
  </div>

by removing <!-- and -->

2: Also in htmltemplate, change

<div id="navLeft"><a name="navigation"></a>{{{navigation}}}</div>

to

<div id="navLeftSubTop"><a name="navigation"></a>{{{navigation}}}</div>

*/

#navLeft {
}

#navLeft a {
display: block;
text-decoration: none;
color: #3F3F3F;
padding: 0.4em 0 0.2em 0;
}

#navLeft a:hover {
text-decoration: none;
color: black;
}

#navLeft a.activeItem {
color: #e63320;
font-weight: bolder;
}

/* list stylings */
#navLeft ul {
margin: 0;
padding: 0 0 0.3em 0;
}

#navLeft ul ul {
margin-left: 1.0em;
}

#navLeft li {
list-style-type: none;
margin: 0;
margin-left: 0;
border-bottom: 1px solid #ddd;
line-height: 1.2em;
padding: 0.5em 0.5em 0.5em 0;
}

#navLeft li li {
font-size: 0.82em;
border-bottom: none; padding:0;
}

#navLeft li.active {
color: #3F3F3F;
background-color: #f9f9f9;
}

#navLeft li.active>ul {
padding: 0.1em 0 0.3em 0;
}
 