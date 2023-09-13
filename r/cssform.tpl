fieldset {
background-color: #f0f0f0;
border: 1px solid #f0f0f0;
margin: 0 0 20px 0;
padding: 25px;
font-size:104%;
color:#555;
width:100%;
}

fieldset legend {
color: gray;
background:#f0f0f0;
font-size: 150%;
margin-left:-25px;
padding:3px 7px 7px 7px;
}

label {
display: inline;
padding-top: 14px;
margin-bottom: 0px;
cursor: pointer;
font-size: 0.9em;
cursor: pointer;
text-align: left;
margin-right: 8px;
display: block;/* change to display: inline-block if you want the labels on the left of the fields */
border-bottom: 0px dotted #ccc;
vertical-align: bottom;
}

input, select, textarea {
border: 1px solid #EFEFEF;
font-family: 'Open Sans', sans-serif;
}

input[type=submit], input[type=text], input[type=email], input[type=number], input[type=password], textarea, select {
width: 98%; 
font-size:110%; 
padding:3px; 
color:#555;
}

input[type=submit] {
background:lightgreen;
color:black;
width:auto;
padding:5px 17px 5px 17px;
}

input[type=checkbox], input[type=radio] {
height:20px;
width:20px;
}

input:hover, input:focus, input:active, select:hover, select:focus, select:active, textarea:hover, textarea:focus, textarea:active {
border: 1px solid #333333;
}

::placeholder { 
color: #ddd;
}

input.readonly, input.readonly:hover, input.readonly:focus, input.readonly:active {
background-color: #F2F2F2;
color: #333333;
border: 1px solid #999999;
}

label.readonly {
cursor: default;
}


 