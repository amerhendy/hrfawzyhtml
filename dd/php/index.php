<?php
class fawzy{
  public static $dbname;
  public static $dbuser;
  public static $dbpass;
  public static $schema;
  private static $db;
  private static $Regulations;
  private static $Regulations_Articles;
  private static $Regulations_Topics;
  private static $Regulations_topic_article;
  function __construct($dbname,$dbuser,$dbpass,$schema=null){
    self::$schema=$schema;
    if($schema !== null){
      self::$Regulations_Articles='"'.$schema.'"."Regulations_Articles"';
      self::$Regulations_Topics='"'.$schema.'"."Regulations_Topics"';
      self::$Regulations_topic_article='"'.$schema.'"."Regulations_topic_article"';
      self::$Regulations='"'.$schema.'"."Regulations"';
    }else{
      self::$Regulations_Articles='"Regulations_Articles"';
      self::$Regulations_Topics='"Regulations_Topics"';
      self::$Regulations_topic_article='"Regulations_topic_article"';
      self::$Regulations='"Regulations"';
    }
    $conn_string = "host=localhost port=5432 dbname=".$dbname." user=".$dbuser." password=".$dbpass;
    self::$db = pg_connect($conn_string) or die('Could not connect: ' . pg_last_error());
  }
  public function viewtableslink(){
    $query="select table_name from information_schema.tables where table_schema not in ('information_schema', 'pg_catalog')";
    if ($result = pg_query(self::$db,$query)) {
      while ($row = pg_fetch_assoc($result)) {
        $ht='<a href="xml.php?export='.$row['table_name'].'">'.$row['table_name'].'</a><br>';
        print $ht;
      }
    }
  }
  private static function createquery($table){
    if($table == 'Regulations_Articles'){
      $order='number';
      $where='WHERE "Regulation_id"='.$_GET['Regulation_id'].' ';
      $query='SELECT * FROM '.self::$Regulations_Articles.' '.$where.'ORDER BY '.$order.' ASC;';
    }
    if($table == 'Regulations_Topics' OR $table=='Regulations_topic_article'){
      $order='number';
      $where='WHERE "Regulation_id"='.$_GET['Regulation_id'].' ';
      $query='SELECT * FROM '.self::$Regulations_Articles.' '.$where.'ORDER BY '.$order.' ASC;';
      $Regulations_Articles_IDS=[];
      if ($result = pg_query(self::$db,$query)) {
        while ($row = pg_fetch_assoc($result)) {
          array_push($Regulations_Articles_IDS,$row['id']);
        }
      }
      $Topic_ids=[];
      if(count($Regulations_Articles_IDS)){
        $query='SELECT * FROM '.self::$Regulations_topic_article.' where "Article_id" IN ('.implode(",",$Regulations_Articles_IDS).');';
        if($table=='Regulations_topic_article'){
         return $query;
        }        
        if ($result = pg_query(self::$db,$query)) {
          while ($row = pg_fetch_assoc($result)) {
            $Topic_ids[]=$row['Topic_id'];

          }
        }
      }
      if(isset($Topic_ids)){
        if(count($Topic_ids)){
          $Topic_ids=array_unique($Topic_ids);
          $query='SELECT * FROM '.self::$Regulations_Topics.' where "id" IN ('.implode(",",$Topic_ids).');';
        }
      }
    }
    return $query;
  }
  public static function exporttable($table=null){
    $booksArray=[];
    $query= self::createquery($table);
    if ($result = pg_query(self::$db,$query)) {
      while ($row = pg_fetch_assoc($result)) {
        array_push($booksArray, $row);
      }
    }
    if(count($booksArray)){
      if($_GET['export'] == 'Regulations_Articles'){
        self::Regulations_Articles($booksArray);
      }elseif($_GET['export'] == 'Regulations_Topics'){
        self::Regulations_Topics($booksArray);
        }elseif($_GET['export'] == 'Regulations_topic_article'){
          self::Regulations_topic_article($booksArray);
        }
    }
  }
  private static function Regulations_Articles($booksArray){
    foreach($booksArray as $a=>$b){
      unset($booksArray[$a]['created_at']);unset($booksArray[$a]['updated_at']);unset($booksArray[$a]['deleted_at']);
    }
    print json_encode($booksArray, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    foreach ($booksArray as $key => $value) {
      if(is_numeric($value['number'])){
        $value['number']=intval($value['number']);
      }
    }
    usort($booksArray, fn($a, $b) => $a['number'] <=> $b['number']);
    $table='Regulations_Articles';
    $filePath = $table.'.xml';
    $dom     = new DOMDocument('1.0', 'utf-8'); 
    $root      = $dom->createElement($table);
    for($i=0; $i<count($booksArray); $i++){
    $book = $dom->createElement($table);
      $Id        =  $booksArray[$i]['id'];
      $book->setAttribute('id', $Id);
      $number      =   $booksArray[$i]['number'];
      $name     = $dom->createElement('number', $number); 
      $book->appendChild($name);

      $text    =  htmlspecialchars($booksArray[$i]['text']);
      $author   = $dom->createElement('text', $text); 
      $book->appendChild($author); 
      if($booksArray[$i]['mp3'] !== null)
      {$mp3    =  htmlspecialchars($booksArray[$i]['mp3']);}else{$mp3    =  false;}
      $mp3author   = $dom->createElement('audio', $mp3); 
      $book->appendChild($mp3author); 
      $root->appendChild($book);
    }
    $dom->appendChild($root); 
    $dom->save($filePath); 
  }
  private static function Regulations_Topics($booksArray){
    foreach($booksArray as $a=>$b){
      unset($booksArray[$a]['created_at']);unset($booksArray[$a]['updated_at']);unset($booksArray[$a]['deleted_at']);
    }
    print json_encode($booksArray, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    $table='Regulations_Topics';
    $filePath = $table.'.xml';
    $dom     = new DOMDocument('1.0', 'utf-8'); 
    $root      = $dom->createElement($table);
    for($i=0; $i<count($booksArray); $i++){
    $book = $dom->createElement($table);
      $Id        =  $booksArray[$i]['id'];
      $book->setAttribute('id', $Id);
      
      $text    =  htmlspecialchars($booksArray[$i]['text']);
      $author   = $dom->createElement('text', $text); 
      $book->appendChild($author); 
      $father      =   $booksArray[$i]['father'];
      $name     = $dom->createElement('father', $father); 
      $book->appendChild($name);
      $root->appendChild($book);
    }
    $dom->appendChild($root); 
    $dom->save($filePath); 
  } 
  private static function Regulations_topic_article($booksArray){
    foreach($booksArray as $a=>$b){
      unset($booksArray[$a]['created_at']);unset($booksArray[$a]['updated_at']);unset($booksArray[$a]['deleted_at']);
    }
    print json_encode($booksArray, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    $table='Regulations_topic_article';
    $filePath = $table.'.xml';
    $dom     = new DOMDocument('1.0', 'utf-8'); 
    $root      = $dom->createElement($table);
    for($i=0; $i<count($booksArray); $i++){
    $book = $dom->createElement($table);
      $Id        =  $booksArray[$i]['id'];
      $book->setAttribute('id', $Id);
      $text_id      =   $booksArray[$i]['Topic_id'];
      $name     = $dom->createElement('Topic_id', $text_id); 
      $book->appendChild($name);
      $moad_id    =  $booksArray[$i]['Article_id']; 
      $author   = $dom->createElement('Article_id', $moad_id); 
      $book->appendChild($author); 
      $root->appendChild($book);
    }
    $dom->appendChild($root); 
    $dom->save($filePath);
  } 
  private static function queryall(){
    $query='
        SELECT
            "a"."id" AS "Regulation_id","a"."text" AS "Regulation_title",
            "b"."id" AS "Article_id","b"."number","b"."text","b"."mp3",
            "c"."Topic_id",
            "d"."text" AS "topic_text","d"."id"  AS "topic_id"
        FROM '.self::$Regulations.' "a" 
        JOIN '.self::$Regulations_Articles.' "b" ON "b"."Regulation_id"="a"."id" 
        JOIN '.self::$Regulations_topic_article.' "c" ON "c"."Article_id"="b"."id" 
        JOIN '.self::$Regulations_Topics.' "d" ON "d"."id"="c"."Topic_id"';
       $result = pg_query(self::$db,$query);
        /*while ($row = pg_fetch_assoc($result)) {
        }*/
        return $result;
  }
  public static function create_menu(){
    $result=self::queryall();
    $menu=[];
    print '<pre>';
    while ($row = pg_fetch_assoc($result)) {
        $menu[]=['id'=>$row['Regulation_id'],'title'=>$row['Regulation_title'],'child'=>[$row['topic_id'],$row['topic_text']]];
        //print_r($row);
    }
    print_r($menu);
  }
}
$fawzy=new fawzy('jobs','amer','6330978','lotfy');
$menu=$fawzy::create_menu();
print $menu;
$htmltemplate=' <html lang="ar-eg">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta charset="UTF-8">
  <meta name="referrer" content="origin"/>
  <meta name="referrer" content="origin-when-crossorigin"/>
  <meta name="referrer" content="origin-when-cross-origin"/>
  <meta name="language" content="Arabic">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="robots" content="index, follow">
  <meta name="theme-color" content    ="light">
  <link rel="icon" href="style/small.ico">
  <!-- Font Awesome -->
  <link href="style/style.css" rel="stylesheet" />

  <link href="style/bootstrap.min.css" rel="stylesheet" />
  <link href="style/bootstrap.rtl.min.css" rel="stylesheet" />
  <link href="style/bootstrap-grid.min.css" rel="stylesheet" />
  <link href="style/bootstrap-reboot.min.css" rel="stylesheet" />
  <link href="style/bootstrap-utilities.min.css" rel="stylesheet" />
  <link href="style/awesom/all.min.css" rel="stylesheet" />
<style>
  @media print{
    header,#main,.sidepanel,.myOverlay,audio,#loadtags{
      display:none !important;
    }
  }
</style>
</head>
<body class="bg-body-tertiary" style="position: relative;">
    <header class="flex-wrap justify-content-center"  id="main">
      <div class="row">
        <div class="col-sm btn btn-sm btn-primary" type="button" onclick="openNav()">الفهرس</div>
        <div class="col-sm btn btn-sm btn-primary" type="button" onclick="openSearch()">بحث</div>
        <div class="col-sm btn btn-sm btn-primary" type="button" onclick="openmoadSearch()">اختر مادة للعرض</div>
        <div class="col-sm btn btn-sm btn-primary closebtn" id="closebtn" type="button" onclick="closeNav()">اغلاق</div>
      </div>
    </header>
    <main>
  <div id="mySidepanel" class="sidepanel" style="padding-top: 130px;">
    <div>
      <ul class="list-group">
      <li role="link" class="list-group-item" style="" onclick="seemada([\'frontpage\'],\'single]\');">الرئيسية</li>
      <li class="list-group-item" style="  margin:0px;padding-top:0px;padding-bottom:0px"></li>
      </ul>
  </div>
  </div>
    <div id="myOverlay" class="overlay">
      <div class="overlay-content">
        <div id="searchbyword" style=""><input type="text" placeholder="بحث ...." class="form-control" id="myInput" onkeyup="filterFunction()"><div>
          <ul class="list-group"  id="searchresult"></ul>
        </div></div>
        <div id="searchbymoad" style=""><div id="searchselectdiv"></div><button class="btn btn-primary px-4 rounded-pill" type="button" onclick="seemadabysearch()">عرض</button></div>
      </div>
    </div>
  <div class="container-fluid my-5 border" id="frontpage" style="">
    <div class="row text-center p-5">
      <div class="col-sm"><h1 class="text-body-emphasis">لائحة نظام العاملين</h1></div>
      <div class="col-sm"><img src="style/hcww.gif" width="200"></div>
      <div class="col-sm"><h3>الشركة القابضة<br> لمياه الشرب والصرف الصحى </h3></div>
      <div class="col-sm">
        <em>
          حقوق التأليف والطباعة والنسخ للشركة القابضة لمياه الشرب والصرف الصحى
          <br>
          حقوق البرمجة والتصميم لشركة مياه الشرب والصرف الصحى بشمال وجنوب سيناء
        </em>
      </div>
    </div>
  </div>
  <div class="row container-fluid" id="madashows" style="padding-top: 100px;" >
  </div>
</main>
<script type="text/javascript"src="style/jquery-3.6.0.min.js"></script>
<script type="text/javascript"src="style/bootstrap.min.js"></script>
<script type="text/javascript"src="style/bootstrap.bundle.min.js"></script>
</body>
</html> 
';
//print $htmltemplate;
?>