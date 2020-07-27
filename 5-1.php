<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_5-1</title>
</head>
<body>
 <?php
        //フラグ初期化
        $err_flg = "n";
        $edi_flg = "n";
        
        //DB接続設定
        $dsn = 'データベース名';
        $user = 'ユーザー名';
        $password = 'パスワード';
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        $tb_name = "board";

    //----------------編集ボタン押下時の対象レコード表示処理--------------------
        $chk_list = !empty($_POST["edit_b"]) && isset($_POST["e_num"]) && $_POST["e_num"] != ""
                    && isset($_POST["pass_e"]) && $_POST["pass_e"] != "";
        
        if($chk_list){
            $h_value = $_POST["e_num"];
            $pass = $_POST["pass_e"];

            $result = $pdo->query("SHOW TABLES");
            $table = $result->fetchAll(PDO::FETCH_COLUMN);
            // テーブル存在確認
            if(in_array($tb_name,$table,true)){
                
                //入力されたidをSELECTで取得
                $sql = 'SELECT * FROM board WHERE id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $h_value, PDO::PARAM_INT);
                $stmt->execute();
                $count_id = $stmt->rowCount();            

                //idが一致するレコードを取得できた場合、パスワードを取得
                if($count_id == 1){
                    $sql = 'SELECT * FROM board WHERE id=:id and pass=:pass';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id', $h_value, PDO::PARAM_INT);
                    $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
                    $stmt->execute();
                    $count_pw = $stmt->rowCount();
                    
                    //id、パスワードが一致するレコードを取得できた場合
                    if($count_pw == 1){
                        $results = $stmt->fetchAll();                                   
                        foreach($results as $row){
                            $n_value = $row['name'];
                            $c_value = $row['comment'];
                            $pass_value = $row['pass'];
                        }
                        $edi_flg = "y";
                    }else{
                        echo "パスワードが一致しませんでした。";
                        $err_flg = "y";
                    }
                }else{
                    echo "入力した投稿番号は存在しません。";
                    $err_flg = "y";
                }
            }else{
                echo "データが存在しません。データを登録してください。";
                $err_flg = "y";
            }
        }elseif(!empty($_POST["edit_b"]) && isset($_POST["pass_e"]) && $_POST["pass_e"] != ""){
            echo "編集対象の番号を入力してください。";
            $err_flg = "y";
        }elseif(!empty($_POST["edit_b"]) && isset($_POST["e_num"]) && $_POST["e_num"] != ""){
            echo "パスワードを入力してください。";
            $err_flg = "y";
        }elseif(!empty($_POST["edit_b"])){
            echo "編集対象の番号とパスワードを入力してください。";
            $err_flg = "y";
        }
    ?>

    <form action="" method="post">
        <div class="add">
                 <input type="text" name="name" size="30" maxlength="30" placeholder="名前"
                        value="<?php if( !empty($n_value) ){ echo $n_value; } ?>">
            <div><input type="text" name="cmt" size="30" maxlength="50" placeholder="コメント"
                        value="<?php if( !empty($c_value) ){ echo $c_value; } ?>"></div>
                 <input type="password" name="pass_a" size="15" maxlength="10" autocomplete="new-password"
                        placeholder="パスワード" value="<?php if( !empty($pass_value) ){ echo $pass_value; } ?>">
                 <input type="submit" name="send_b">
            <div><input type="hidden" name="h_num" value="<?php if( !empty($h_value) ){ echo $h_value; } ?>"></div>
        </div>
                 
        <div class="delete" style=margin-top:10px;>
            <input type="number" name="del_n" min="1" max="10000"placeholder="削除番号">
            <div><input type="password" name="pass_d" size="15" maxlength="10" autocomplete="new-password"
                        placeholder="パスワード">
                 <input type="submit" name="del_b" value="削除"></div>
        </div>         
        <div class="edit" style=margin-top:10px;>
                 <input type="number" name="e_num" min="1" max="10000" placeholder="編集番号">
            <div><input type="password" name="pass_e" size="15" maxlength="10" 
                        autocomplete="new-password"placeholder="パスワード">
                 <input type="submit" name="edit_b" value="編集"></div>
        </div>
    </form>
    <?php
    //----------------------------登録処理--------------------------------------
        //---送信ボタン押下&空白チェック---
        $send_flg = "n";
        $chk_list = !empty($_POST["send_b"]) && $_POST["h_num"] == "" && isset($_POST["cmt"]) && 
                    $_POST["cmt"] != "" && isset($_POST["name"]) && $_POST["name"] != "" 
                    && isset($_POST["pass_a"]) && $_POST["pass_a"] != "";

        if($chk_list){
            //---変数定義---
            $name = $_POST["name"];
            $cmt = $_POST["cmt"];
            $pass = $_POST["pass_a"];
            $date = date("Y-m-d H:i:s");
            
            //DB内にテーブルがなければ作成する
            $sql="CREATE TABLE IF NOT EXISTS board"
            ."("
            ."id INT AUTO_INCREMENT PRIMARY KEY,"
            ."name char(32),"
            ."comment TEXT,"
            ."pass char(15),"
            ."date DATETIME"
            .");";
            $stmt = $pdo->query($sql); 
            
            //データ入力
            $sql = $pdo -> prepare("INSERT INTO board (name, comment, pass, date)
            VALUES (:name, :comment, :pass, :date)");
            $sql -> bindParam(':name', $name, PDO::PARAM_STR);
            $sql -> bindParam(':comment', $cmt, PDO::PARAM_STR);
            $sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
            $sql -> bindParam(':date', $date, PDO::PARAM_STR);
            $sql -> execute();
            
            //$pdo = null;
            $send_flg = "y";
        //---エラーチェック---
        }elseif(!empty($_POST["send_b"]) && !$_POST["h_num"]){
            echo "投稿する場合は、お名前、コメント、パスワードを入力してください";
            $err_flg = "y";
        }
    //-----------------------------削除処理-------------------------------------
        //---削除ボタン押下&空白チェック---
        $del_flg = "n";
        $chk_list = !empty($_POST["del_b"]) && isset($_POST["del_n"]) && $_POST["del_n"] != ""
                    && isset($_POST["pass_d"]) && $_POST["pass_d"] != "";
                    
        if($chk_list){
            $id = $_POST["del_n"];
            $pass = $_POST["pass_d"];
            
            $result = $pdo->query("SHOW TABLES");
            $table = $result->fetchAll(PDO::FETCH_COLUMN);
            // テーブル存在確認
            if(in_array($tb_name,$table,true)){            
                //入力されたidをSELECTで取得
                $sql = 'SELECT * FROM board WHERE id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $count_id = $stmt->rowCount();            
            
                //idが一致するレコードを取得できた場合、パスワードを取得
                if($count_id == 1){
                    $sql = 'SELECT * FROM board WHERE id=:id and pass=:pass';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
                    $stmt->execute();
                    $count_pw = $stmt->rowCount();    
                    
                    //id、パスワードが一致するレコードを取得できた場合
                    if($count_pw == 1){
                        //対象のレコードを削除
                        $sql = 'delete from board where id=:id and pass=:pass';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
                        $stmt->execute();
                        
                        $del_flg = "y";
                    }else{
                        echo "パスワードが一致しませんでした。";
                        $err_flg = "y"; 
                    } 
                }else{
                    echo "削除対象の投稿番号は存在しませんでした。";
                    $err_flg = "y"; 
                }
            }else{
                echo "データが存在しません。データを登録してください。";
                $err_flg = "y";
            }
        //---エラーチェック---
        }elseif(!empty($_POST["del_b"])){
                echo "削除する場合は、投稿番号、パスワードを入力してください";
                $err_flg = "y"; 
        }
    //-----------------------------編集内容更新処理-------------------------------------
        //---投稿番号が空白でないかつ名前、コメント、パスワードがある状態で送信された場合のみ処理実行---
        $chk_list = !empty($_POST["send_b"]) && isset($_POST["h_num"]) && isset($_POST["cmt"])
                    && $_POST["cmt"] != "" && isset($_POST["name"]) && $_POST["name"] != ""
                    && isset($_POST["pass_a"]) && $_POST["pass_a"] != "";
           
        if($chk_list){
            $h_value = $_POST["h_num"];
            $n_value = $_POST["name"];
            $c_value = $_POST["cmt"];
            $pass = $_POST["pass_a"];
            $date = date("Y-m-d H:i:s");
            
            //入力されたidをSELECTで取得
            $sql = 'SELECT * FROM board WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $h_value, PDO::PARAM_INT);
            $stmt->execute();
            $count_id = $stmt->rowCount();            
            
            //idが一致するレコードを取得できた場合
            if($count_id == 1){
                //内容を比較
                $sql = 'SELECT * FROM board WHERE id=:id and name=:name and comment=:comment and pass=:pass';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $h_value, PDO::PARAM_INT);
                $stmt->bindParam(':name', $n_value, PDO::PARAM_STR);
                $stmt->bindParam(':comment', $c_value, PDO::PARAM_STR);
                $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
                $stmt->execute();
                $count_rec = $stmt->rowCount();
                
                //名前とコメントとパスワードが全て変わらない時
                if($count_rec == 1){
                    echo "内容が変わっていません。"."<br>"."更新を中断しました。";
                    $err_flg = "y";
                }else{
                    //対象のレコードを更新
                    $sql = 'UPDATE board SET name=:name,comment=:comment,pass=:pass,date=:date WHERE id=:id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':name', $n_value, PDO::PARAM_STR);
                    $stmt->bindParam(':comment', $c_value, PDO::PARAM_STR);
                    $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
                    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
                    $stmt->bindParam(':id', $h_value, PDO::PARAM_INT);
                    $stmt->execute();
                    
                    $edi_flg = "c";
                }
            }
        }elseif(!empty($_POST["send_b"]) && isset($_POST["h_num"]) && $_POST["h_num"] != ""){
                echo "お名前、コメント、パスワードは必須です。";
                $err_flg = "y";
        }
        //-----------------------ファイル内容出力処理---------------------------//
        //---エラーフラグ「y」かつ送信or削除が正常終了、または編集フラグが「c」(完了)の場合---//
        if($err_flg == "n" && $send_flg == "y" || $del_flg == "y" || $edi_flg == "c"){
            //入力したデータレコードを抽出し、表示する
            $sql = 'SELECT * FROM board';
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();
            foreach ($results as $row){
                echo $row['id'].',';
                echo $row['name'].',';
                echo $row['comment'].',';
                echo $row['date'].'<br>';
                echo "<hr>";
            }
        }
    ?>
    
</body>
</html>