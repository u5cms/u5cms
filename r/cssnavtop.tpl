/*
This CSS formats the first (horizontal) level of a split navigation.

If you want a split navigation, you have to

1: In PIDVESA's S, htmltemplate, switch the 
#navTop on

  <div id="navigationtop">
    <div id="navTop"><a name="navigation"></a>{{{navigation}}}</div>
  </div>

by removing <!-- and -->

2: Also in htmltemplate, change

<div id="navLeft"><a name="navigation"></a>{{{navigation}}}</div>

to

<div id="navLeftSubTop"><a name="navigation"></a>{{{navigation}}}</div>

*/

#navTop {
display: block;
width: auto;
height:0px;
}

#navTop a {
text-decoration: none;
color: 
#3F3F3F;
display:inline-block;
margin-top:7px;
}

#navTop a:hover {
text-decoration: none;
color: black;
text-shadow: 1px 1px 
#ccc;
}

#navTop ul {
margin-left:-15px;
display: inline;
}

#navTop li {
display: inline;
margin-right: 11px;
background: 
#eee;
padding: 7px;
}

#navTop .active a {
color: red;
font-weight: bold;
}

#navTop li ul {
display: none;
}

#navTop li li {
display: none;
}

#navTop li li ul {
display: none;
}

#navTop li li li {
display: none;
}  