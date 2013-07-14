<?php

echo '<head>
<link rel="stylesheet" type="text/css" href="mystyle.css">
</head>';

echo "<h1>Story Generator</h1>";

#Some php_functions

function buildRestQuery($story)
{
    #For url : select * from contentanalysis.analyze where url='http://www.cnn.com/2011/11/11/world/europe/greece-main/index.html';
    #For story : select * from contentanalysis.analyze where text="Italian sculptors and painters of the renaissance favored the Virgin Mary for inspiration"
    debug("<b><u>Building Rest Query</u></b>"); 
    $query = "";
    $trimmedStory = trim($story,"\t\n\r ");
    $head = substr($trimmedStory,0,4);
    if( strcmp($head,"http")==0 )
    {
	    $query = "select * from contentanalysis.analyze where url=\"".$story."\"";
    }
    else
    {
	    $query = "select * from contentanalysis.analyze where text=\"".$story."\"";
    }
    $restQuery = "http://query.yahooapis.com/v1/public/yql?q=".urlencode($query)."&diagnostics=true";
    debug($query);
    debug($restQuery);
    return $restQuery;
}

function yqlLaunch($restQuery)
{
    debug("<b><u>Launching YQL</u></b>");
    $session = curl_init($restQuery);
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    #curl_setopt($session, CURLOPT_URL, $restQuery);
    #curl_setopt($session, CURLOPT_RANGE,"1-10240");
    $xml = curl_exec($session);  #$xml now contains the result of the YQL Query...
    curl_close($session);
    debug($xml);
    return $xml;
}

function displayData($xml)
{
    debug("<b><u>Dispalying data</u></b>");
   
    $doc = new DOMDocument();
    $doc->loadXML($xml);
    $catArr = $doc->getElementsByTagName('yctCategory');
    if( $catArr->length > 0 )
    {
        echo "<div id='allcategories'> <h2>Categories</h2>";
        foreach( $catArr as $val )
        {
            echo "<div id='category'>".$val->nodeValue."</div>";
        }
        echo "</div>";
    } 
    echo "<div id='footer'></div>";
    $entityArr = $doc->getElementsByTagName('entity');
    if( $entityArr->length > 0 )
    {
        echo "<div id='allentities'> <center><h2>Stories</h2></center>";
        foreach( $entityArr as $val )
        {
            $pos = strpos($val->nodeValue,"http");
            if($pos !== false)
            {
                echo "<div id='entity'>";
                echo "<h3>".substr($val->nodeValue,0,$pos)."</h3>";
                #echo "<b>Link :</b> ".substr($val->nodeValue,$pos,strlen($val->nodeValue)-$pos);
                echo "<iframe src=\"".substr($val->nodeValue,$pos,strlen($val->nodeValue)-$pos)."\"> </iframe>";
                echo "<br><a href=\"".substr($val->nodeValue,$pos,strlen($val->nodeValue)-$pos)."\"> url </a>";
                echo "</div>";
            }
        }
        foreach( $entityArr as $val )
        {
            $pos = strpos($val->nodeValue,"http");
            if($pos === false)
            {
                echo "<div id='entity'>";
                echo $val->nodeValue;
                echo "</div>";
            }
        }
        echo "</div>";
    }
    echo "<div id='footer'></div>";

    #$textArr   = $doc->getElementsByTagName('text');
    #$wikiArr   = $doc->getElementsByTagName('wiki_url');

    #var_dump(json_decode(json_encode($xml)));

    #$arr = json_decode(json_encode($xml),true,10);
    #print_r($arr);
    #foreach($arr as $key => $value)
    #{ 
    #    echo "<p>$key | $value </p>";
    #}
}

function debug($str)
{
    #echo $str."<br>";
}

echo '<div id="bg"> &nbsp;&nbsp;&nbsp;&nbsp; <b>Enter relevant story (or) specify a link over here</b> ...<br>
<form id="tarea" method="post" action="story.php">
    <textarea name="comments" cols="50" rows="5"></textarea><br>
    <input type="submit" value="Submit" />
</form>
';

$story="";
if(isset($_POST['comments'])) 
{
    $story = $_POST['comments'];
    unset($_POST['comments']);
}

if( !empty($story) )
{
    echo displayData(yqlLaunch(buildRestQuery($story)));
}

echo "</div>";  #bg div close

#-->Some yql queries<--
#$yql_query_url='select * from html where url="http://finance.yahoo.com/q?s=yhoo" and xpath='//div[@id="yfi_headlines"]/div[2]/ul/li/a';
#$yql_restQuery_url='http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20html%20where%20url%3D%22http%3A%2F%2Ffinance.yahoo.com%2Fq%3Fs%3Dyhoo%22%20&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=cbfunc';
?>
