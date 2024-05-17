<?php
class fawzy{
  public static $dbname;
  public static $dbuser;
  public static $dbpass;
  public static $schema;
  private static $db;
  private static $Regulations_Articles;
  private static $Regulations_Topics;
  private static $Regulations_topic_article;
  function __construct($dbname,$dbuser,$dbpass,$schema=null){
    self::$schema=$schema;
    if($schema !== null){
      self::$Regulations_Articles='"'.$schema.'"."Regulations_Articles"';
      self::$Regulations_Topics='"'.$schema.'"."Regulations_Topics"';
      self::$Regulations_topic_article='"'.$schema.'"."Regulations_topic_article"';
    }else{
      self::$Regulations_Articles='"Regulations_Articles"';
      self::$Regulations_Topics='"Regulations_Topics"';
      self::$Regulations_topic_article='"Regulations_topic_article"';
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
}
$fawzy=new fawzy('jobs','amer','6330978','lotfy');
if(isset($_GET['export'])){
  $fawzy->exporttable($_GET['export']);
}
//$fawzy->viewtableslink();
//exporttable
?>