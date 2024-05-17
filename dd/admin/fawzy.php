<!DOCTYPE html>
<html>
        <head>
                <meta charset="utf-8">
                <title>CKEditor</title>
                <link href="style/bootstrap.min.css" rel="stylesheet" />
                <link href="style/bootstrap.rtl.min.css" rel="stylesheet" />
                <link href="style/bootstrap-grid.min.css" rel="stylesheet" />
                <link href="style/bootstrap-reboot.min.css" rel="stylesheet" />
                <link href="style/bootstrap-utilities.min.css" rel="stylesheet" />
                <link href="style/select2.min.css" rel="stylesheet" />
                <script src="style/jquery-3.6.0.min.js"></script>
                <script src="style/select2.full.min.js"></script>
                <script src="style/ckeditor/ckeditor.js"></script>
                <style>
                        p{
                                border: 1px solid red;
                        }
                </style>
                <script>
                        $(document).ready(function() {
                $('.moad').select2();
                $('.text').select2();
                });
                </script>       
        </head>
        <body>
                <header>
                        <div class="row">
                                <div class='col-sm'><a href="fawzy.php" class='btn btn-primary'>Home</a></div>
                                <div class='col-sm'><a href="fawzy.php?table=Regulations_Articles&action=Add" class='btn btn-primary'>اضافة مواد</a></div>
                                <div class='col-sm'><a href="fawzy.php?table=Regulations_Articles&action=Update" class='btn btn-primary'>تعديل مواد</a></div>
                                <div class='col-sm'><a href="fawzy.php?table=Regulations_Topics&action=Add" class='btn btn-primary'>اضافة دلائل</a></div>
                                <div class='col-sm'><a href="fawzy.php?table=Regulations_Topics&action=Update" class='btn btn-primary'>تعديل دلائل</a></div>
                                <div class='col-sm'><a href="fawzy.php?table=Regulations&action=Add" class='btn btn-primary'>اضافة لائحة</a></div>
                                <div class='col-sm'><a href="fawzy.php?table=Regulations&action=Update" class='btn btn-primary'>تعديل لائحة</a></div>
                        </div>
                </header>
                <main>
<?php
class fawzy{
        public static $schema;
        public static $table;
        public static $action;
        private static $db;
        function __construct($dbname,$dbuser,$dbpassword,$host=null,$port=null,$dbschema=null){
                if($host == '' || $host == null){$host='localhost';}
                if($port == '' || $port == null){$port=5432;}
                if($dbname == ''){exit('please add dbname');}
                if($dbuser == ''){exit('please add dbuser');}
                if($dbpassword == ''){exit('dbpassword');}
                if($dbschema != '' || $dbschema != null){self::$schema=$dbschema;}
                $conn_string = 'host='.$host.' port='.$port.' dbname='.$dbname.' user='.$dbuser.' password='.$dbpassword.'';
                self::$db = pg_connect($conn_string) or die('Could not connect: ' . pg_last_error());
        }
        public static function Regulations_Articles_Add_form ($id=null,$act=null){
                if($act=='update'){
                        $result =pg_query($dbconn4, 'SELECT * FROM "public"."topics";');
            if (!$result) {
                echo "An error occurred.\n";
                exit;
            };
            print '<form action="" method="POST">
                        <input type="text" name="topictext">
                        <br><select name="father"><option value="0"> - - - - - - </option>';
            while ($row = pg_fetch_assoc($result)) {
                print'<option value="'.$row['id'].'"> '.$row['text'].' </option>';
            }
            print '</select>
                        <br>
                        <button>ss</button>
                </form>';
                }
                print '
                  <form action="" method="POST">
                    <input type="text" name="number">
                        <textarea name="editor1"></textarea>
                        <script>
                                CKEDITOR.replace( \'editor1\' );
                        </script>
                        <button>ss</button>
               ';
        }
        private static function createform($inputs,$table=null){
                if(isset($_POST['id'])){
                        $inputs[]=['type'=>'hidden','name'=>'id','value'=>$_POST['id']];
                        $table='"'.self::$schema.'"."'.$table.'"';
                        $query='SELECT * FROM '.$table.' WHERE id='.$_POST['id'];
                        if ($result = pg_query(self::$db,$query)) {
                                while ($row = pg_fetch_assoc($result)) {
                                        foreach($inputs as $a=>$b){
                                                $inputs[$a]['value']=$row[$b['name']];
                                        }
                                }
                        }
                }
                $html='<form action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" method="post">';
                foreach($inputs as $input){
                        if($input['type'] == 'hidden'){
                                $html.='<input type="hidden" id="'.$input['name'].'" value="'.$input['value'].'" name="'.$input['name'].'">';
                        }else{
                                $html.='<div class="form-group row">';
                                $html.='<label for="text" class="col-sm-2 col-form-label">'.$input['label'].'</label>';
                                $html.='<div class="col-sm-10"><input type="'.$input['type'].'" class="form-control" id="'.$input['name'].'" value="'.$input['value'].'" name="'.$input['name'].'"></div>';
                                $html.='</div>';
                        }
                }
                $html.='<button type="submit" class="btn btn-primary">Submit</button>
                </form>';
                return $html;
        }
        public static function create_update_form($inputs,$table){
                $names=['id'];
                foreach($inputs as $input){
                        $names[]=$input['name'];
                }
                $namesStr=implode(",",$names);
                $table='"'.self::$schema.'"."'.$table.'"';
                $query='SELECT '.$namesStr .' FROM '.$table;
                $options='';
                if ($result = pg_query(self::$db,$query)) {
                        while ($row = pg_fetch_assoc($result)) {
                                $options.='<option value="'.$row['id'].'">'.$row['text'].'</option>';
                        }
                        $html='<form action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" method="POST">';
                        $html.='<div class="form-group">';
                        $html.='<label for="id">اختر للتعديل</label>';
                        $html.='<select name="id" class="form-control" data-init-function="bpFieldInitSelect2Element">';
                        $html.=$options;
                        $html.='</select>';
                        $html.='</div>';
                        $html.='<button type="submit" class="btn btn-primary">Submit</button>';
                        $html.='</form>';
                        return $html;
                }
        }
        public static function Regulations()
        {
                $inputs=[
                        ['type'=>'text','name'=>'text','label'=>'اسم اللائحة','value'=>'']
                ];
                if(self::$action == 'Update'){
                        if(isset($_POST['id'])){
                                if(count($_POST) > 1){
                                        $vals=[];$cols=[];
                                        foreach($inputs as $input){
                                                $cols[]='"'.$input['name'].'"';
                                                $vals[]="'". $_POST[$input['name']]."'";
                                        }
                                        
                                        $table='"'.self::$schema.'"."'.$_GET['table'].'"';
                                        $query='UPDATE '.$table.' SET ';
                                        if(count($cols) == 1){
                                                $query.=$cols[0]." = ".$vals[0];
                                        }
                                        $query.=' WHERE id='.$_POST['id'];
                                        //$query.='('.implode(',',$cols).') = () WHERE id='.$_POST['id'];
                                        if(pg_query(self::$db, $query )){return 'done';}
                                }
                                return self::createform($inputs,$_GET['table']);
                        }else{
                                return self::create_update_form($inputs,$_GET['table']);
                        }
                }elseif(self::$action == 'Add'){
                        if(isset($_POST['text'])){
                                $query1 = 'INSERT INTO "'.self::$schema.'"."Regulations" (text) VALUES (\''.$_POST['text'].'\')';
                                $result = pg_query(self::$db, $query1 );
                                return $result;
                        }else{return self::createform($inputs);}
                        
                }
        }
}
$fawzy=new fawzy('jobs','amer','6330978','localhost',null,'lotfy');
if(isset($_GET['table'])){
        $fawzy::$table=$_GET['table'];
        if(isset($_GET['action'])){$fawzy::$action=$_GET['action'];}
        if(method_exists($fawzy,$_GET['table'])){
                $result=call_user_func(array($fawzy,$_GET['table']));
                if(gettype($result) == 'object'){
                        $result="done";
                }
        }
}
?>
<div class="row border">
<div class="card">
  <div class="card-body">
        <?php if(isset($result)){print $result;}?>
  
  </div>
</div>
        
</div>
<?php
/*
if(empty($_GET)){return'';}
if(isset($_GET['action'])){
        if($_GET['action'] == 'Regulations_Articles_Add'){Regulations_Articles_Add_form(null,'add');}
        if($_GET['action'] == 'Regulations_Articles_Update'){
                if(isset($_GET['id'])){
                        Regulations_Articles_Add_form($_GET['id'],'update');
                }else{
                        Regulations_Articles_Add_form();
                }
                
        }
        if($_GET['action'] == 'Regulations_Topics_Add'){}
        if($_GET['action'] == 'Regulations_Topics_Update'){}
        if($_GET['action'] == 'Regulations_Add'){}
}
function Regulations_Articles_Update_form(){}

if(isset($_POST['number'])){
  $symbol=$_POST['number'];
  $text=$_POST['editor1'];
  if($symbol == null){print "error"; exit();}
  elseif($text == null){print "error"; exit();}
  else{insertStock($dbconn4,$symbol, $text);}
}elseif(isset($_GET['id'])){
        $query = 'SELECT * FROM "lotfy"."Regulations_Articles" where "id"='.$_GET['id'];
        $result = pg_query($dbconn4,$query) or die('Error message: ' . pg_last_error());
        while ($row = pg_fetch_row($result)) {
                var_dump($row);
            }
            pg_free_result($result);
            pg_close($dbconn4);
        print($_GET['id']);
}
elseif(isset($_GET['action'])){
if($_GET['action'] == 'addtopic') {
    if(isset($_POST['topictext'])) {
        insertTopic($dbconn4, $_POST);
    }
    $result =pg_query($dbconn4, 'SELECT * FROM "public"."topics";');
    if (!$result) {
        echo "An error occurred.\n";
        exit;
    };
    print '<form action="" method="POST">
                <input type="text" name="topictext">
                <br><select name="father"><option value="0"> - - - - - - </option>';
    while ($row = pg_fetch_assoc($result)) {
        print'<option value="'.$row['id'].'"> '.$row['text'].' </option>';
    }
    print '</select>
                <br>
                <button>ss</button>
        </form>';
        }elseif($_GET['action'] == 'addmp3'){
                if(isset($_POST['url'])){
                        insertmp3($dbconn4,$_POST);
                }
                $result2 =pg_query($dbconn4,'SELECT * FROM "public"."moad";');
                if (!$result2) {echo "An error occurred.\n";exit;};
                print '<form action="" method="post">url:<input type="text" name="url"><input type=button onclick="isValidUrl()" value="check validation"><br><select name="moad">';
                while ($row = pg_fetch_assoc($result2)) {
                        print'<option value="'.$row['id'].'"> '.$row['number'].' </option>';
                       }
                print '</select><button>add</button></form><script>function isValidUrl() {string=$(\'input[name=url]\').val();try {new URL(string);alert("صحيح");}catch (err) {alert("خطأ");}}</script>';

        }elseif($_GET['action'] == 'addtopictomodo'){
                if(isset($_POST['text'])){
                        if($_POST['text'] == ''){print 'empty text'; exit();}
                        if(empty($_POST['moad'])){print 'empty moad'; exit();}
                        insertMoadTopic($dbconn4,$_POST);
                }
                $result =pg_query($dbconn4,'SELECT * FROM "public"."topics";');
                if (!$result) {echo "An error occurred.\n";exit;};
                $result2 =pg_query($dbconn4,'SELECT * FROM "public"."moad";');
                if (!$result2) {echo "An error occurred.\n";exit;};
                print '<form action="" method="POST">
                الموضوعات:<select class="text" name="text">';
                while ($row = pg_fetch_assoc($result)) {
                 print'<option value="'.$row['id'].'"> '.$row['text'].' </option>';
                }
                print '
                </select><br>المواد:
                <select class="moad" name="moad[]" multiple="multiple">';
                while ($row = pg_fetch_assoc($result2)) {
                 print'<option value="'.$row['id'].'"> '.$row['number'].' </option>';
                }
                print '</select>
                <button>go</button>
                </form>';
        }
}
else{
print '
          <form action="" method="POST">
            <input type="text" name="number">
                <textarea name="editor1"></textarea>
                <script>
                        CKEDITOR.replace( \'editor1\' );
                </script>
                <button>ss</button>
       ';
}

 function insertStock($dbconn4,$symbol, $company) {
  // prepare statement for insert
  $query = 'INSERT INTO "moad" ("number", "text") VALUES (\''.$symbol.'\',	\''.$company.'\');';
  
  $result = pg_query($dbconn4,$query);
}
function insertTopic($dbconn4,$symbol) {
        foreach($symbol as $a=>$b){
                if($b == ''){print 'empty'.$a;exit();}
        }
        $query = 'INSERT INTO "topics" ("text","father") VALUES (\''.$symbol['topictext'].'\',	\''.$symbol['father'].'\');';
        $result = pg_query($dbconn4,$query);
        print '<script>document.write(\'<a href="\' + document.referrer + \'">Go Back</a>\');</script>';
      }
      function insertMoadTopic($dbconn4,$post) {
        foreach($_POST['moad'] as $a=>$b){
                $query = 'INSERT INTO "moad_text" ("text_id","moad_id") VALUES (\''.$post['text'].'\',	\''.$b.'\');';
                $result = pg_query($dbconn4,$query);
                print '<script>document.write(\'<a href="\' + document.referrer + \'">Go Back</a>\');</script>';
        }
      }
      function insertmp3($dbconn4,$post) {
        $mp3s=[
                'https://www.dropbox.com/s/z9ckoofbiwcx7p6/1.mp3?dl=0',
                'https://www.dropbox.com/s/z693ia7unwn1gux/2.mp3?dl=0',
                'https://www.dropbox.com/s/xz1udiotcg3vls1/3.mp3?dl=0',
                'https://www.dropbox.com/s/gzfmloj9in0mi7l/4.mp3?dl=0',
                'https://www.dropbox.com/s/5734twro0o2nm51/5.mp3?dl=0',
                'https://www.dropbox.com/s/ar1j0zfepusal7u/6.mp3?dl=0',
                'https://www.dropbox.com/s/e8m9uicjkgzpmp9/7.mp3?dl=0',
                'https://www.dropbox.com/s/0p6k92bhgq482le/8.mp3?dl=0',
                'https://www.dropbox.com/s/lowy1p0l447u700/9.mp3?dl=0',
                'https://www.dropbox.com/s/wfl4zjzddjxkmah/10.mp3?dl=0',
                'https://www.dropbox.com/s/ww3nz0cpno5vvvb/11.mp3?dl=0',
                'https://www.dropbox.com/s/vtac2r51bgev09m/12.mp3?dl=0',
                'https://www.dropbox.com/s/so6o5jr9ma3i1uf/13.mp3?dl=0',
                'https://www.dropbox.com/s/42pj9s7dmnm3d6f/14.mp3?dl=0',
                'https://www.dropbox.com/s/9dvh9esle2s6dmg/15.mp3?dl=0',
                'https://www.dropbox.com/s/fx7k5igbdnudl1o/16.mp3?dl=0',
                'https://www.dropbox.com/s/vo2z27v6ilnfwut/17.mp3?dl=0',
                'https://www.dropbox.com/s/i8bvw2vmm8bpp9s/18.mp3?dl=0',
                'https://www.dropbox.com/s/0ial670b87dqp84/19.mp3?dl=0',
                'https://www.dropbox.com/s/50bs23p1d691y90/20.mp3?dl=0',
                'https://www.dropbox.com/s/mg3lv6i1c5hesm3/21.mp3?dl=0',
                'https://www.dropbox.com/s/djbqtp030n93cwb/22.mp3?dl=0',
                'https://www.dropbox.com/s/nali9sr6tp9a63r/23.mp3?dl=0',
                'https://www.dropbox.com/s/p9d6f0te9gpui9n/24.mp3?dl=0',
                'https://www.dropbox.com/s/phdnvqqkbjbw194/25.mp3?dl=0',
                'https://www.dropbox.com/s/b02t03dcsdj0hbj/26.mp3?dl=0',
                'https://www.dropbox.com/s/aw0ekpwg77epebi/27.mp3?dl=0',
                'https://www.dropbox.com/s/pjx4bfw9b6xmta3/28.mp3?dl=0',
                'https://www.dropbox.com/s/fnalgvogavo0mjp/29.mp3?dl=0',
                'https://www.dropbox.com/s/xouoz96ynr4chts/30.mp3?dl=0',
                'https://www.dropbox.com/s/etzaft6prgks6qe/31.mp3?dl=0',
                'https://www.dropbox.com/s/1ey705dld62s7ci/32.mp3?dl=0',
                'https://www.dropbox.com/s/80r06lvsdan803s/33.mp3?dl=0',
                'https://www.dropbox.com/s/44d5cv0qr293mvx/34.mp3?dl=0',
                'https://www.dropbox.com/s/bstcbmqzs2ojzo8/35.mp3?dl=0',
                'https://www.dropbox.com/s/k41441ono8qcmsv/36.mp3?dl=0',
                'https://www.dropbox.com/s/19ygj174ao5pe1m/37.mp3?dl=0',
                'https://www.dropbox.com/s/lqc9nlh865wg3ag/38.mp3?dl=0',
                'https://www.dropbox.com/s/2k4jjhwy7pbm8bx/39.mp3?dl=0',
                'https://www.dropbox.com/s/pzn2orapggkauu0/40.mp3?dl=0',
                'https://www.dropbox.com/s/o19rfrbr37aqpgo/41.mp3?dl=0',
                'https://www.dropbox.com/s/ey5g2d8y0yeido4/42.mp3?dl=0',
                'https://www.dropbox.com/s/xu9419gg4uhttos/43.mp3?dl=0',
                'https://www.dropbox.com/s/l54sp7m0pdtcu40/44.mp3?dl=0',
                'https://www.dropbox.com/s/sytletm7ybv7ulf/45.mp3?dl=0',
                'https://www.dropbox.com/s/erxdx9nl1p8yma6/46.mp3?dl=0',
                'https://www.dropbox.com/s/emlqs2mf3inavl3/47.mp3?dl=0',
                'https://www.dropbox.com/s/ce4ye9iviyg16lk/48.mp3?dl=0',
                'https://www.dropbox.com/s/iudqtvoe4nwy7r9/49.mp3?dl=0',
                'https://www.dropbox.com/s/mnzbvlymwxffp27/50.mp3?dl=0',
                'https://www.dropbox.com/s/1k20nft5ct8fbp0/51.mp3?dl=0',
                'https://www.dropbox.com/s/397vchyzkhg9x83/52.mp3?dl=0',
                'https://www.dropbox.com/s/hhpp98cfi8wak1k/53.mp3?dl=0',
                'https://www.dropbox.com/s/fqyujohjxro39w8/54.mp3?dl=0',
                'https://www.dropbox.com/s/2c3c53erivumqbr/55.mp3?dl=0',
                'https://www.dropbox.com/s/2ax50ixmmrk49na/56.mp3?dl=0',
                'https://www.dropbox.com/s/5aox6k9kkz0b36q/57.mp3?dl=0',
                'https://www.dropbox.com/s/nnptxv21o87js0c/58.mp3?dl=0',
                'https://www.dropbox.com/s/8v6m42e3464z87x/59.mp3?dl=0',
                'https://www.dropbox.com/s/99i4f7tvnpkj274/60.mp3?dl=0',
                'https://www.dropbox.com/s/ptfhoarlhczi75w/61.mp3?dl=0',
                'https://www.dropbox.com/s/czw1v1iljxo43h8/62.mp3?dl=0',
                'https://www.dropbox.com/s/9z49xam0uix3mwj/63.mp3?dl=0',
                'https://www.dropbox.com/s/44nywwt0vx1a2r6/64.mp3?dl=0',
                'https://www.dropbox.com/s/ydjov1aettlrge6/65.mp3?dl=0',
                'https://www.dropbox.com/s/4syis0biww9bhlg/66.mp3?dl=0',
                'https://www.dropbox.com/s/cx0e2cde9mrgzvm/67.mp3?dl=0',
                'https://www.dropbox.com/s/nwb8oay3pzwiwb0/68.mp3?dl=0',
                'https://www.dropbox.com/s/mb3t66h06toyo4o/69.mp3?dl=0',
                'https://www.dropbox.com/s/jlq95b69rjbxtkf/70.mp3?dl=0',
                'https://www.dropbox.com/s/a66a87dacnprwy9/71.mp3?dl=0',
                'https://www.dropbox.com/s/9yk4jo6wrp1a9m0/72.mp3?dl=0',
                'https://www.dropbox.com/s/u4wsp0r36ajv9yi/73.mp3?dl=0',
                'https://www.dropbox.com/s/3xowyt1ni0trpev/74.mp3?dl=0',
                'https://www.dropbox.com/s/kucskhnrr2z6usr/75.mp3?dl=0',
                'https://www.dropbox.com/s/99kauijcfgx08bx/76.mp3?dl=0',
                'https://www.dropbox.com/s/qcqedk3uj0ge96o/77.mp3?dl=0',
                'https://www.dropbox.com/s/i4cf25bmdnlhqsg/78.mp3?dl=0',
                'https://www.dropbox.com/s/7o4ntgkyo4u2ign/79.mp3?dl=0',
                'https://www.dropbox.com/s/41voxbn9odw2nzd/80.mp3?dl=0',
                'https://www.dropbox.com/s/zwbcpvyf9ivunp6/81.mp3?dl=0',
                'https://www.dropbox.com/s/znkn0gf931ugwi7/82.mp3?dl=0',
                'https://www.dropbox.com/s/8m2e90lckoo8f79/83.mp3?dl=0',
                'https://www.dropbox.com/s/m8hv9l93abelt6r/84.mp3?dl=0',
                'https://www.dropbox.com/s/q0ju6mtf3f0kq4y/85.mp3?dl=0',
                'https://www.dropbox.com/s/o1oqewfuiwuofaq/86.mp3?dl=0',
                'https://www.dropbox.com/s/65u08uh08b2d4pa/87.mp3?dl=0',
                'https://www.dropbox.com/s/60c4hbv6docbvmq/88.mp3?dl=0',
                'https://www.dropbox.com/s/ikbunu3thdcrq7q/89.mp3?dl=0',
                'https://www.dropbox.com/s/pv3c9uj9bys8qte/90.mp3?dl=0',
                'https://www.dropbox.com/s/nrjyk937v51xgoz/91.mp3?dl=0',
                'https://www.dropbox.com/s/8lamjrl6d031wz3/92.mp3?dl=0',
                'https://www.dropbox.com/s/azbs65qwsitzyz7/93.mp3?dl=0',
                'https://www.dropbox.com/s/x5lk1czvz7j5pdh/94.mp3?dl=0',
                'https://www.dropbox.com/s/l2t5equzqvb5klm/95.mp3?dl=0',
                'https://www.dropbox.com/s/met0njruq1fae2h/96.mp3?dl=0',
                'https://www.dropbox.com/s/2c97y3v8cq6l4nw/97.mp3?dl=0',
                'https://www.dropbox.com/s/o2xzgg4o7mx0tr2/98.mp3?dl=0',
                'https://www.dropbox.com/s/zloro1mnbkwso6e/99.mp3?dl=0',
                'https://www.dropbox.com/s/s6fle87rcdtkaqd/100.mp3?dl=0',
                'https://www.dropbox.com/s/uo16axrwmu79c9x/101.mp3?dl=0',
                'https://www.dropbox.com/s/ddwo48azvi74l11/102.mp3?dl=0',
                'https://www.dropbox.com/s/4ligjuf1equki8u/103.mp3?dl=0',
                'https://www.dropbox.com/s/5eg8z4j1j7q872b/104.mp3?dl=0',
                'https://www.dropbox.com/s/ap04k8gg1gslk09/105.mp3?dl=0',
                'https://www.dropbox.com/s/1m0xhn9942arepy/106.mp3?dl=0',
                'https://www.dropbox.com/s/h6j4gvj5awielhd/107.mp3?dl=0',
                'https://www.dropbox.com/s/p7qym2ukhq10bwz/108.mp3?dl=0',
                'https://www.dropbox.com/s/t95ryx4napcvhfg/109.mp3?dl=0',
                'https://www.dropbox.com/s/c36xzzw6qgnb9c3/110.mp3?dl=0',
                'https://www.dropbox.com/s/i3t58qne3zhop1w/111.mp3?dl=0',
                'https://www.dropbox.com/s/iexwyvfna0px8im/112.mp3?dl=0',
                'https://www.dropbox.com/s/tsfxw8mx3tjkpa9/113.mp3?dl=0',
        ];
        print '<pre>';
        $mp3=[];
        $numbers=[];
        foreach($mp3s as $v){
                $v1=explode('/',$v)[5];
                $v2=explode('.',$v1)[0];
                $v=str_replace('www','dl',$v);
                $mp3[]=['number'=>$v2,'link'=>$v];
                $query = 'UPDATE "moad" SET mp3 =\''.$v.'\' WHERE number=\''.$v2.'\';';
                pg_query($dbconn4,$query);
        }
        print_r($mp3);
        exit();
        $result =pg_query($dbconn4, 'SELECT * FROM "public"."moad";');
        if (!$result) {
            echo "An error occurred.\n";
            exit;
        };
        while ($row = pg_fetch_assoc($result)) {
                $id=$row['id'];
                echo "M".$id.'M<br>';
                $row['text']= str_replace( "<p>", "", $row['text']);
                $row['text']= str_replace( "<p dir=\"RTL\">", "", $row['text']);
                $row['text']= str_replace( '<p dir="RTL" style="margin-right:2px; text-align:justify">', "", $row['text']);
                $row['text']= str_replace( "</p>", "<br>", $row['text']);
                $row['text']= str_replace( "&lt;br&gt;", "", $row['text']);
                $row['text']= str_replace( "&lt;li&gt;", "<li>", $row['text']);
                $row['text']= str_replace( "&lt;/li&gt;", "</li>", $row['text']);
                $row['text']= str_replace( "&lt;ul&gt;", "<ul>", $row['text']);
                $row['text']= str_replace( "&lt;/ul&gt;", "</ul>", $row['text']);
                $row['text']= str_replace( "&lt;ol&gt;", "<ol>", $row['text']);
                $row['text']= str_replace( "&lt;/ol&gt;", "</ol>", $row['text']);
                $row['text']= str_replace( "&nbsp;", "", $row['text']);
                print $row['text'].'<hr>';
                
                $query = 'UPDATE "moad" SET text =\''.$row['text'].'\' WHERE id=\''.$id.'\';';
                pg_query($dbconn4,$query);
            }
                
                //$result = pg_query($dbconn4,$query);
                print '<script>document.write(\'<a href="\' + document.referrer + \'">Go Back</a>\');</script>';
      }
      */
?>

</main>
<script>
if (top !== self) top.location.replace(self.location.href);
$(document).ready(function() {
    var forms = document.querySelectorAll('form');
    var inputs = document.querySelectorAll('input');
    var selects = document.querySelectorAll('select');
    if ((forms.length !== 0) || (inputs.length !== 0) || (selects.length !== 0)) {
        initializeFieldsWithJavascript('form');
    }
});
function initializeFieldsWithJavascript(container) {
    var selector;
    if (container instanceof jQuery) {
        selector = container;
    } else {
        selector = $(container);
    }
    selector.find("[data-init-function]").each(function() {
        var element = $(this);
        var functionName = element.data('init-function');

        if (typeof window[functionName] === "function") {
            window[functionName](element);
        }
    });
}
        function bpFieldInitSelect2Element(element) {
                set_select2_element($(element));
                
        }
        function set_select2_element(element) {
    element.select2({
        dor: 'rtl',
        dropdownAutoWidth: true,
        theme: "bootstrap"
    }).on('select2:unselect', function(e) {
        if ($(this).attr('multiple') && $(this).val().length == 0) {
            alert
            $(this).val(null).trigger('change');
        }
    });
}
</script>
 </body>
</html>
<style>
        ol,ul,li{
        }
</style>

<b><u>يستحق العامل الإجازات الآتية:</u></b>
        <ol>
                <li>
                        <u>اجازة عارضة بأجر كامل</u>
                        <br>
                        إجازة عارضه بأجر كامل لمدة سبعة أيام فى السنة وذلك لسبب طارئ يتعذر معه طلب الحصول على أية إجازة أخرى ولا تحسب ضمن الإجازة السنوية المقررة بشرط ألا تزيد عن يومين متتاليين فى المرة الواحدة وتقدم إلى جهة رئاسته فى يوم عودته مباشرة
                </li>
                <li>
                        <u>إجازة اعتيادية بأجر كامل</u>
                        <ul>
                                <li>
                                        إجازة اعتيادية سنوية بأجر كامل لا يدخل فى حسابها أيام العطلات وأيام المناسبات الرسمية <u>وذلك على الوجه التالي</u>:
                                        <ol>
                                                <li>خمسة عشر يوماً فى السنة الأولى وذلك بعد مضى ستة أشهر من تاريخ استلام العمل</li>
                                                <li>واحد وعشرون يوماً لمن أمضى سنة كاملة على الأقل</li>
                                                <li>ثلاثون يوماً لمن أمضى مدة عشر سنوات في الخدمة</li>
                                                <li>خمسة وأربعون يوماً لمن بلغ سن الخمسين</li>
                                        </ol>
                                </li>
                                <li>تحدد مواعيد الإجازة الاعتيادية حسب مقتضيات العمل وظروفه ولا يجوز تقصيرها أو تأجيلها أو قطعها إلا لأسباب قوية تقتضيها مصلحة العمل ويوافق عليها الرئيس الأعلى</li>
                                <li>تضع الإدارة في بداية العام خطة لحصول العاملين على كامل اجازاتهم الاعتيادية السنوية</li>
                                <li>فى جميع الأحوال يجب ان يحصل العامل على إجازة سنوية لا تقل مدتها عن ثلثي اجازاته السنوية منها أسبوع عمل متصل ولا يجوز للشركة ترحيلها الا لأسباب تتعلق بمصلحة العمل، وفى حدود الثلث على الأكثر ولمدة لا تزيد على ثلاث سنوات</li>
                                <li>إذا لم يتقدم العامل بطلب الحصول على اجازاته على النحو المشار   اليه سقط حقه فيها وفى اقتضاء مقابل عنها</li>
                                <li>اما إذا تقدم بطلب للحصول عليها ورفضته السلطة المختصة استحق مقابل نقدي عنها يصرف بعد مرور ثلاث سنوات على انتهاء العام المستحق عنه الاجازة على أساس اجر الاشتراك في التأمينات الاجتماعية</li>
                                <li>
                                        فى جميع الأحوال، لا يجوز الحصول على المقابل النقدي عن رصيد الاجازات الذي تكون للعامل قبل العمل بهذا التعديل <u>الا بتوافر الشروط الآتية:</u>
                                        <ol>
                                                <li>ان يتقدم العامل بطلب للحصول على هذه الاجازات</li>
                                                <li>ان تقرر   السلطة المختصة رفض الطلب لأسباب تتعلق بمصلحة العمل</li>
                                                <li>حال عدم تقدم العامل بهذا الطلب يسقط حقه نهائيا في تقاضى مقابل نقدى عن هذا الرصيد</li>
                                        </ol>
                                </li>
                        </ul>
                </li>
        </ol>