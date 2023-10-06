<?php
/*
おまけのおまけ。 (sam.php 風)
バグがあればご報告をお願いします。

簡単な機能説明：
  このスクリプトにアクセスすると、htmlを出力します。
  基本的に sam.php の処理方法と同様です。
  sam.php のおまけ的なモノと考えてね。

対応拡張子
  GD版: .gif .jpg .jpeg .png

  正順と逆順のhtmlファイルも作っちゃいます。（手抜き実装…）

  ディレクトリを深く潜れるようになりました。
*/

// 調整用 エラーレベル設定
//error_reporting(E_ALL);
//error_reporting(E_ALL & ~E_NOTICE);

define('IMG_DIR', 'src_imgs/');		// 画像一覧ディレクトリ
define('THUMB_DIR', 'imgs/');	// サムネイル保存ディレクトリ
define('MAX_W', 200);			// 出力画像幅
define('MAX_H', 155);			// 出力画像高さ
define('PAGE_COLS', 4);			// 1行に表示する画像数
define('PAGE_DEF', 64);			// 1ページに表示する画像数

define('THUMB_FALSE', 'orz.png');	// サムネイル作成失敗時に表示する画像
define('THUMB_QUALITY', 75);	// サムネイルの品質(JPEG用) 0～100 まで指定可能  default:75
define('IMG_INWH', 0);			// 画像リンクに width height を含める  する:1 しない:0
define('PUT_DATE', 0);			// 更新時刻を入れる  する:1 しない:0

define('PHP_SELF', 'index.php');	// このスクリプト名
define('PHP_SELF2', 'sam_r.htm');	// 入り口ファイル名（逆順
define('PHP_EXT', 's_r.htm');		// 1ページ以降の拡張子（逆順
define('PHP_SELF2_R', 'sam.htm');	// 入り口ファイル名（正順
define('PHP_EXT_R', 's.htm');		// 1ページ以降の拡張子（正順
define('GOTO_P', 'sam_r.htm');		// 最初に表示するページ

define('IMG_REFER', 0);			// ツール避けに画像リンクをhtml経由にする  する:1 しない:0
define('IMG_REF_DIR', 'red/');		// 経由先html格納ディレクトリ

define('TITLE', 'サムネイル一覧');	// タイトル（<title>のみ
define('TITLE_T', '古い順');	// 正順表示
define('TITLE_R', '新しい順');	// 逆順表示

define('DIR_DEPTH', -1);	// 探索するディレクトリの深さを制限する  する:0以上 しない:-1
define('SORT_BY_DATE', 1);	// 更新日順にする  する:1 しない:0

$icons = array(
	// 拡張子小文字 => 代替画像  ※変更する場合は「検索する拡張子」も適宜変更すること
	'pch' => 'icon_arc.png',
	'spch' => 'icon_arc.png',
	'txt' => 'icon_txt.png',
	'pdf' => 'icon_txt.png',
	'mhtm' => 'icon_txt.png',
	'mht' => 'icon_txt.png',
	'swf' => 'icon_mov.png',
	'flv' => 'icon_mov.png',
	'wav' => 'icon_mov.png',
	'mp3' => 'icon_mov.png',
	'wmv' => 'icon_mov.png',
	'asf' => 'icon_mov.png',
	'mp4' => 'icon_mov.png',
	'mpeg' => 'icon_mov.png',
	'mpg' => 'icon_mov.png',
	'avi' => 'icon_mov.png',
	'ts' => 'icon_mov.png',
	'm2ts' => 'icon_mov.png',
	'rar' => 'icon_arc.png',
	'zip' => 'icon_arc.png',
	'lzh' => 'icon_arc.png',
	'7z' => 'icon_arc.png',
);

$file_ext = '/^(png|jpe?g|gif|pch|spch|txt|pdf|mhtm|mht|mpo|swf|flv|wav|mp3|wmv|asf|mp4|mpeg|mpg|avi|ts|m2ts|rar|zip|lzh|7z)$/i';	// 検索する拡張子
$ignore_file = '/^(THM_|LOG_|img_|tm_).+\.(png|jpe?g|gif)$|_thumb\.(png|jpe?g|gif)$/i';	// 無視するファイル名

$path = realpath('./').'/'.IMG_DIR;

/* ヘッダ */
function head(&$dat,$page){

  $dat .= '<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="Berry" content="no">
<meta name="Robots" content="noindex, nofollow">
<title>'.TITLE.'</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
a {
  word-break: break-all;
}
</style>
</head>
<body bgcolor="#FFFFEE">
<table width="100%">
<tr>';

  if (PUT_DATE && is_file(PHP_SELF2)) { // 更新日付表示
    $dat .= '<td align="left"><font size=-1>';
    if ($page==0) { $dat .= '最終更新：'.date('\'y m月 d日 H:i.').'<br>前回'; }
    else { $dat .= '最終'; }
    $dat .= '更新：'.date('\'y m月 d日 H:i.', filemtime(PHP_SELF2)).'</font>';
    $dat .= "</td>\n";
  }

  $dat .= '<td align="right">
[<a href="'.PHP_SELF.'">更新</a>] [<a href="./../">戻る</a>]
</td></tr></table>
<center>
<div class="autopagerize_page_element">
<p><b>サムネイル一覧</b></p>
';

}

/* 表示処理部分 */
function updatesam(){
  global $path,$file_ext,$ignore_file,$icons;

  // ディレクトリ一覧取得、ソート
  $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(IMG_DIR,
    FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO |
    FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS));
  $iterator->setMaxDepth(DIR_DEPTH);
  $key1 = $files = array();
  foreach ($iterator as $info) {
    if (preg_match($ignore_file, $info->getFilename())) {
      continue;
    }
    if (preg_match($file_ext, $info->getExtension())) {
      // ファイル名と拡張子を取得
      $tmpext = '.'.$info->getExtension();
      $tmpart = $info->getBasename($tmpext);
      // サムネイル作成
      $fixsub = ($iterator->getSubPath() != '') ? str_replace('/','_2F_',$iterator->getSubPath()).'_2F_' : 'root_2F_';
      if (!array_key_exists(strtolower($info->getExtension()), $icons)) {
        $tmpath = THUMB_DIR.$fixsub.$tmpart.$tmpext;
        if (!function_exists('imagegif') && !is_file($tmpath)) { $tmpath = THUMB_DIR.$fixsub.$tmpart.str_replace('gif','jpg',strtolower($tmpext)); }
        if (!is_file($tmpath)) { thumb($path,$tmpart,$tmpext,$iterator->getSubPath()); }
      }
      // リスト生成
      if (SORT_BY_DATE) {
        // http://www.php.net/manual/ja/class.splfileinfo.php
        $key1[] = $info->getMTime();
      } else {
        $key1[] = $info->getFilename();
      }
      $files[] = array('subpath' => $iterator->getSubPath(), 'name' => $tmpart, 'ext' => $tmpext, 'fixsub' => $fixsub);
    }
  }
  // チェック
  if (!$files) { error('ERROR!!<br>NO IMAGE DATA!'); }
  // ソート
  array_multisort($key1, SORT_ASC, SORT_REGULAR, $files);
  $filesA = array_reverse($files); // 逆順
  $filesB = &$files; // 正順
  // ページ作成.初期値設定
  $p = 0;
  $fcount = count($files);
  $tpage = ceil($fcount / PAGE_DEF);
  // ページ分の繰り返し
  for ($page = 0; $page < $tpage; ++$page) {
    $datA = $datB = '';
    $pagesA = $pagesB = '';
    $dispmsgA = $dispmsgB = '';
    $lineA = $lineB = 0;
    // 枚数分の繰り返し
    for ($i = 0; $i < PAGE_DEF; ++$i) {
      $counter = $i + 1;
      // 逆順表示
      $val = current($filesA);
      if ($val !== false) {
        next($filesA);
        $line = $lineA;
        ++$lineA;
        // ファイル名と拡張子を取得
        $fname = ($val['subpath'] != '') ? $val['subpath'].'/'.$val['name'].$val['ext'] : $val['name'].$val['ext'];
        $src = $path.$fname;
        $image = IMG_DIR.$fname;
        if (IMG_INWH) { // サムネイルの画像サイズ調整
          $size = getimagesize($src);
          // 画像表示縮小
          if ($size[0] > MAX_W || $size[1] > MAX_H) {
            $key_w = MAX_W / $size[0];
            $key_h = MAX_H / $size[1];
            ($key_w < $key_h) ? $keys = $key_w : $keys = $key_h;
            $out_w = ceil($size[0] * $keys);
            $out_h = ceil($size[1] * $keys);
          } else {
            $out_w = $size[0];
            $out_h = $size[1];
          }
        }
        // サムネイルがある時は、サムネイルへのリンク、無いときは失敗画像へ
        $is_icon = array_key_exists(strtolower(substr($val['ext'], 1)), $icons);
        if (!$is_icon) {
          $imagepath = THUMB_DIR.$val['fixsub'].$val['name'].$val['ext'];
          if (!function_exists('imagegif') && !is_file($imagepath)) { $imagepath = THUMB_DIR.$val['fixsub'].$val['name'].str_replace('gif','jpg',strtolower($val['ext'])); }
          if (is_file($imagepath)) { $piclink = str_replace('%2F','/',rawurlencode($imagepath)); } // URLエンコードを行う
          else { $piclink = THUMB_FALSE; $piclink = rawurlencode($imagepath); }
        }
        // 画像経由先htmlファイル作成
        if (IMG_REFER && is_file($src) && !is_file(IMG_REF_DIR.$val['fixsub'].$val['name'].'.htm')) {
          $fp = fopen(IMG_REF_DIR.$val['fixsub'].$val['name'].'.htm', 'w');
          flock($fp, 2);
          fputs($fp, '<meta http-equiv="refresh" content="0;URL=../'.str_replace('%2F','/',rawurlencode($image)).'">');
          fflush($fp);
          flock($fp, 3);
          fclose($fp);
        }
        // 画像テーブル
        if (IMG_REFER) {
          $dispmsgA .= '    <td align=center><a href="'.str_replace('%2F','/',rawurlencode(IMG_REF_DIR.$val['fixsub'].$val['name'])).".htm\" target=\"_blank\">\n";
        } else {
          $dispmsgA .= '    <td align=center><a href="'.str_replace('%2F','/',rawurlencode($image))."\" target=\"_blank\">\n";
        }
        if (!$is_icon) {
          if (IMG_INWH && is_file($imagepath)) {
            $dispmsgA .= '    <img src="'.$piclink.'" width="'.$out_w.'" height="'.$out_h.'" border="0"><br>'.$val['name'].$val['ext']."</a></td>\n";
          } else {
            $dispmsgA .= '    <img src="'.$piclink.'" border="0"><br>'.$val['name'].$val['ext']."</a></td>\n";
          }
        } else {
          $dispmsgA .= '    <img src="'.$icons[strtolower(substr($val['ext'], 1))].'" border="0"><br>'.$val['name'].$val['ext']."</a></td>\n";
        }
        if ((($counter % PAGE_COLS) == 0) && (($counter % PAGE_DEF) != 0) && ($line != ($fcount-1))) { $dispmsgA .= "  </tr><tr>\n"; }
      }
      // 正順表示
      $val = current($filesB);
      if ($val !== false) {
        next($filesB);
        $line = $lineB;
        ++$lineB;
        // ファイル名と拡張子を取得
        $fname = ($val['subpath'] != '') ? $val['subpath'].'/'.$val['name'].$val['ext'] : $val['name'].$val['ext'];
        $src = $path.$fname;
        $image = IMG_DIR.$fname;
        if (IMG_INWH) { // サムネイルの画像サイズ調整
          $size = getimagesize($src);
          // 画像表示縮小
          if ($size[0] > MAX_W || $size[1] > MAX_H) {
            $key_w = MAX_W / $size[0];
            $key_h = MAX_H / $size[1];
            ($key_w < $key_h) ? $keys = $key_w : $keys = $key_h;
            $out_w = ceil($size[0] * $keys);
            $out_h = ceil($size[1] * $keys);
          } else {
            $out_w = $size[0];
            $out_h = $size[1];
          }
        }
        // サムネイルがある時は、サムネイルへのリンク、無いときは失敗画像へ
        $is_icon = array_key_exists(strtolower(substr($val['ext'], 1)), $icons);
        if (!$is_icon) {
          $imagepath = THUMB_DIR.$val['fixsub'].$val['name'].$val['ext'];
          if (!function_exists('imagegif') && !is_file($imagepath)) { $imagepath = THUMB_DIR.$val['fixsub'].$val['name'].str_replace('gif','jpg',strtolower($val['ext'])); }
          if (is_file($imagepath)) { $piclink = str_replace('%2F','/',rawurlencode($imagepath)); } // URLエンコードを行う
          else { $piclink = THUMB_FALSE; $piclink = rawurlencode($imagepath); }
        }
        // 画像テーブル
        if (IMG_REFER) {
          $dispmsgB .= '    <td align=center><a href="'.str_replace('%2F','/',rawurlencode(IMG_REF_DIR.$val['fixsub'].$val['name'])).".htm\" target=\"_blank\">\n";
        } else {
          $dispmsgB .= '    <td align=center><a href="'.str_replace('%2F','/',rawurlencode($image))."\" target=\"_blank\">\n";
        }
        if (!$is_icon) {
          if (IMG_INWH && is_file($imagepath)) {
            $dispmsgB .= '    <img src="'.$piclink.'" width="'.$out_w.'" height="'.$out_h.'" border="0"><br>'.$val['name'].$val['ext']."</a></td>\n";
          } else {
            $dispmsgB .= '    <img src="'.$piclink.'" border="0"><br>'.$val['name'].$val['ext']."</a></td>\n";
          }
        } else {
          $dispmsgB .= '    <img src="'.$icons[strtolower(substr($val['ext'], 1))].'" border="0"><br>'.$val['name'].$val['ext']."</a></td>\n";
        }
        if ((($counter % PAGE_COLS) == 0) && (($counter % PAGE_DEF) != 0) && ($line != ($fcount-1))) { $dispmsgB .= "  </tr><tr>\n"; }
      }
      clearstatcache(); // ファイルのstatをクリア
      ++$p;
    }
    // 改ページ処理
    $prev = $page - 1;
    $next = $page + 1;
    if ($prev >= 0) {
      if ($prev == 0) {
        $pagesA .= '<a href="'.PHP_SELF2.'">&lt;&lt;前へ</a>　';
        $pagesB .= '<a href="'.PHP_SELF2_R.'">&lt;&lt;前へ</a>　';
      } else {
        $pagesA .= '<a href="'.$prev.PHP_EXT.'">&lt;&lt;前へ</a>　';
        $pagesB .= '<a href="'.$prev.PHP_EXT_R.'">&lt;&lt;前へ</a>　';
      }
    } else {
      $pagesA .= '&lt;&lt;前へ　';
      $pagesB .= '&lt;&lt;前へ　';
    }
    for ($i = 0; $i < $tpage ; ++$i) {
      if ($page == $i) {
        $pagesA .= '[<b>'.$i.'</b>] ';
        $pagesB .= '[<b>'.$i.'</b>] ';
        // 表示方法
        if ($page == 0) {
          $tmpA = '<p><a href="'.PHP_SELF2_R.'">'.TITLE_T.'</a>　'.TITLE_R.'</p>';
          $tmpB = '<p>'.TITLE_T.'　<a href="'.PHP_SELF2.'">'.TITLE_R.'</a></p>';
        } else {
          $tmpA = '<p><a href="'.$i.PHP_EXT_R.'">'.TITLE_T.'</a>　'.TITLE_R.'</p>';
          $tmpB = '<p>'.TITLE_T.'　<a href="'.$i.PHP_EXT.'">'.TITLE_R.'</a></p>';
        }
      } else {
        if ($i == 0) {
          $pagesA .= '[<a href="'.PHP_SELF2.'"><b>0</b></a>] ';
          $pagesB .= '[<a href="'.PHP_SELF2_R.'"><b>0</b></a>] ';
        }
        else {
          $pagesA .= '[<a href="'.$i.PHP_EXT.'"><b>'.$i.'</b></a>] ';
          $pagesB .= '[<a href="'.$i.PHP_EXT_R.'"><b>'.$i.'</b></a>] ';
        }
      }
    }
    if ($tpage > $next) {
      $pagesA .= '　<a rel="next" href="'.$next.PHP_EXT.'">次へ&gt;&gt;</a>';
      $pagesB .= '　<a rel="next" href="'.$next.PHP_EXT_R.'">次へ&gt;&gt;</a>';
    } else {
      $pagesA .= '　次へ&gt;&gt;';
      $pagesB .= '　次へ&gt;&gt;';
    }
    // ヘッダHTML
    head($datA,$page);
    head($datB,$page);
    // 総数表示
    $datA .= '<p>画像総数<b>'.$fcount."</b>枚</p>\n";
    $datB .= '<p>画像総数<b>'.$fcount."</b>枚</p>\n";
    // 表示方法
    $datA .= $tmpA;
    $datB .= $tmpB;
    // ページリンク
    $datA .= '<p>'.$pagesA."</p>\n";
    $datB .= '<p>'.$pagesB."</p>\n";
    // 画像テーブル
    $datA .= "<table border=\"0\" cellpadding=\"2\">\n".
             "  <tr>\n";
    $datB .= "<table border=\"0\" cellpadding=\"2\">\n".
             "  <tr>\n";
    $datA .= $dispmsgA;
    $datB .= $dispmsgB;
    $datA .= "  </tr>\n</table>\n";
    $datB .= "  </tr>\n</table>\n";
    // ページリンク
    $datA .= '<p>'.$pagesA."</p>\n";
    $datB .= '<p>'.$pagesB."</p>\n";
    // 表示方法
    $datA .= $tmpA;
    $datB .= $tmpB;
    // フッタHTML
    foot($datA);
    foot($datB);
    // 記事部分作成
    if ($page == 0) { $logfilename = PHP_SELF2; }
    else { $logfilename = $page.PHP_EXT; }
    $fp = fopen($logfilename, "w");
    set_file_buffer($fp, 0);
    rewind($fp);
    fputs($fp, $datA);
    fclose($fp);
    chmod($logfilename,0666);
    // 記事部分作成
    if ($page == 0) { $logfilename_reverse = PHP_SELF2_R; }
    else { $logfilename_reverse = $page.PHP_EXT_R; }
    $fp = fopen($logfilename_reverse, 'w');
    set_file_buffer($fp, 0);
    rewind($fp);
    fputs($fp, $datB);
    fclose($fp);
    chmod($logfilename_reverse,0666);
  }
}

/* フッタ */
function foot(&$dat){
  $dat .= '
</div>
<div class="autopagerize_insert_before"></div>
</center>
<div align=right><a href="http://php.s3.to" target="_top">レッツPHP!</a> + <a href="http://siokara.que.jp/" target="_top">siokara</a></div>
</body></html>';
}

/* エラー画面 */
function error($mes){
  head($dat,1);
  echo $dat;
  echo "<br><br><hr size=1><br><br>\n",
       "<center><font color=red size=5><b>",$mes,"<br><br><a href=",PHP_SELF2,">リロード</a></b></font></center>\n",
       "<br><br><hr size=1>\n";
  die('</body></html>');
}

/* サムネイル作成 */
function thumb($path,$tim,$ext,$fixsub=''){
  if ($fixsub != '') {
    $src = $path.$fixsub.'/'.$tim.$ext;
    $thumb_dir = THUMB_DIR.str_replace('/','_2F_',$fixsub).'_2F_';
  } else {
    $src = $path.$tim.$ext;	// ファイル名
    $thumb_dir = THUMB_DIR.'root_2F_';	// サムネイル保存ディレクトリ
  }
  $W = MAX_W;		// 出力画像幅
  $H = MAX_H;		// 出力画像高さ
  // 画像の幅と高さとタイプを取得
  $size = getimagesize($src);
  switch ($size[2]) {
    case 1 :
      if (function_exists('imagecreatefromgif')) {
        $im_in = @imagecreatefromgif($src);
      }
      else {
        // gif2png for Unix / gif2png.exe for Win : http://siokara.que.jp/
        if (!strncasecmp(PHP_OS,'WIN',3) && is_file(realpath('./gif2png.exe'))) {
          @exec(realpath('./gif2png.exe')." -z $src",$a);
        }
        elseif (is_executable(realpath('./gif2png'))) {
          @exec(realpath('./gif2png')." $src",$a);
        }
        $src_png = $path.$tim.str_replace('gif','png',strtolower($ext));
        if (is_file($src_png)) {
          $im_in = @imagecreatefrompng($src_png);
          unlink($src_png);
        }
      }
      break;
    case 2 :
      $im_in = @imagecreatefromjpeg($src);
      break;
    case 3 :
      if (function_exists('imagecreatefrompng')) {
        $im_in = @imagecreatefrompng($src);
      }
      break;
  }
  if (empty($im_in)) { return; }
  // リサイズ
  if ($size[0] > $W || $size[1] > $H) {
    $key_w = $W / $size[0];
    $key_h = $H / $size[1];
    ($key_w < $key_h) ? $keys = $key_w : $keys = $key_h;
    $out_w = ceil($size[0] * $keys);
    $out_h = ceil($size[1] * $keys);
  } else {
    $out_w = $size[0];
    $out_h = $size[1];
  }
  // 出力画像（サムネイル）のイメージを作成  元画像を縦横とも コピー
  $im_out = imagecreatetruecolor($out_w, $out_h);
  imagecopyresampled($im_out, $im_in, 0, 0, 0, 0, $out_w, $out_h, $size[0], $size[1]);

  // ここでエラーが出る方は下２行と置き換えてください。(GD2.0以下
  //$im_out = imagecreate($out_w, $out_h);
  //imagecopyresized($im_out, $im_in, 0, 0, 0, 0, $out_w, $out_h, $size[0], $size[1]);

  // サムネイル画像を保存
  switch ($size[2]) {
    case 1 : if (function_exists('imagegif')) { imagegif($im_out, $thumb_dir.$tim.$ext); }
             else { imagejpeg($im_out, $thumb_dir.$tim.str_replace('gif','jpg',strtolower($ext)), THUMB_QUALITY); }
             break;
    case 2 : imagejpeg($im_out, $thumb_dir.$tim.$ext, THUMB_QUALITY); break;
    case 3 : imagepng($im_out, $thumb_dir.$tim.$ext); break;
  }

  // 作成したイメージを破棄
  imagedestroy($im_in);
  imagedestroy($im_out);
}

/* 初期設定 */
function init(){
  $err='';
  if(!is_writable(realpath('./')))error('カレントディレクトリに書けません<br>');
  @mkdir(IMG_DIR,0777);@chmod(IMG_DIR,0777);
  if(!is_dir(realpath(IMG_DIR)))$err.=IMG_DIR.'がありません<br>';
  if(!is_writable(realpath(IMG_DIR)))$err.=IMG_DIR.'を書けません<br>';
  if(!is_readable(realpath(IMG_DIR)))$err.=IMG_DIR.'を読めません<br>';

  @mkdir(THUMB_DIR,0777);@chmod(THUMB_DIR,0777);
  if(!is_dir(realpath(THUMB_DIR)))$err.=THUMB_DIR.'がありません<br>';
  if(!is_writable(realpath(THUMB_DIR)))$err.=THUMB_DIR.'を書けません<br>';
  if(!is_readable(realpath(THUMB_DIR)))$err.=THUMB_DIR.'を読めません<br>';

  if(IMG_REFER){
    @mkdir(IMG_REF_DIR,0777);@chmod(IMG_REF_DIR,0777);
    if(!is_dir(realpath(IMG_REF_DIR)))$err.=IMG_REF_DIR.'がありません<br>';
    if(!is_writable(realpath(IMG_REF_DIR)))$err.=IMG_REF_DIR.'を書けません<br>';
    if(!is_readable(realpath(IMG_REF_DIR)))$err.=IMG_REF_DIR.'を読めません<br>';
  }
  if($err)error($err);
}

/*-----------Main-----------*/
init(); //←■■初期設定後は不要なので削除可■■
updatesam();
echo '<meta http-equiv="refresh" content="0;URL='.GOTO_P.'">';

?>
