<?php
echo "<center><h2>Story Generator</h2></center>";

function debug($str) {
    echo $str."<br>";
}

class Model { 
    public $context; 
    public $yqlQuery; 
    public $restQuery;
    public $xmlStory;

    public function __construct() { 
        $this->$context = '';
        $this->$yqlQuery = '';
        $this->$RestQuery = '';
        $this->$xmlStory = ''; 
    }

    public function setContext($str) {
        $this->$context = trim($str,"\t\n\r ");
    }

    public function getContext() {
        return $this->$context;
    }
} 

class Controller { 
    private $model; 

    public function __construct(Model $model) { 
        $this->model = $model; 
    }

    public function buildRestQuery() {
        #For url : select * from contentanalysis.analyze where url='http://www.cnn.com/2011/11/11/world/europe/greece-main/index.html';
        #For story : select * from contentanalysis.analyze where text="Italian sculptors and painters of the renaissance favored the Virgin Mary for inspiration"
        $head = substr($this->$model->$getContext(),0,4);
        $query = '';
        if( strcmp($head,"http")==0 )
        {
	        $query = "select * from contentanalysis.analyze where url=\"".$this->$model->$getContext()."\"";
        }
        else
        {
	        $query = "select * from contentanalysis.analyze where text=\"".$this->$model->$getContext()."\"";
        }
        debug("<b><u>Building Rest Query</u></b>"); 
        $restQuery = "http://query.yahooapis.com/v1/public/yql?q=".urlencode($query)."&diagnostics=true";
        debug($query);
        debug($restQuery);
        $model->$yqlQuery = $query;
        $model->$restQuery = $restQuery;
    }

    public function yqlLaunch($str) {
        $this->$model->$setContext($str);
        $this->buildRestQuery();
        debug("<b><u>Launching YQL</u></b>");
        $session = curl_init($this->$model->$restQuery);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        $xml = curl_exec($session);  #$xml now contains the result of the YQL Query...
        curl_close($session);
        debug($xml);
        $this->$model->$xmlStory = $xml;
    }
} 

class View { 
    private $model; 
    private $controller; 

    public function __construct(Controller $controller, Model $model) { 
        $this->controller = $controller; 
        $this->model = $model; 
    } 

    public function display() { 
        debug("<b><u>Dispalying data</u></b>");
   
        if( !empty($this->$model->$xmlStory) ) {
            $doc = new DOMDocument();
            $doc->loadXML($this->$model->$xmlStory);
            $catArr = $doc->getElementsByTagName('yctCategory');
            foreach( $catArr as $val ) {
                debug("<b>Category :</b> ".$val->nodeValue);
            }
            $entityArr = $doc->getElementsByTagName('entity');
            foreach( $entityArr as $val ) {
                $pos = strpos($val->nodeValue,"http");
                if($pos !== false) {
                    debug("<b>Entity :</b> ".substr($val->nodeValue,0,$pos)." <b>Link :</b> ".substr($val->nodeValue,$pos,strlen($val->nodeValue)-$pos));
                }
                else {
                    debug("<b>Entity :</b> ".$val->nodeValue);
                }
            }
        }
    } 

} 

echo 'Enter relevant story (or) specify a link over here ...
<form method="post" action="storyMVC.php">
    <textarea name="comments" cols="50" rows="5"></textarea><br>
    <input type="submit" value="Submit" />
</form>
';

$context="";
if(isset($_POST['comments'])) 
{
    $context = $_POST['comments'];
    $controller->yqlLaunch($context);
    unset($_POST['comments']);
}
else
{
    $model = new Model(); 
    $controller = new Controller($model); 
    $view = new View($controller, $model); 
}
echo $view->display();

#-->Some yql queries<--
#$yql_query_url='select * from html where url="http://finance.yahoo.com/q?s=yhoo" and xpath='//div[@id="yfi_headlines"]/div[2]/ul/li/a';
#$yql_restQuery_url='http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20html%20where%20url%3D%22http%3A%2F%2Ffinance.yahoo.com%2Fq%3Fs%3Dyhoo%22%20&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=cbfunc';
?>
