<?php

/*
 * 設定は instance-config.php へ書き込んで下さい。
 * define値を含めて instance-config.php の定義が優先されます。
 */

// 調整用 エラーレベル設定
//error_reporting(E_ALL);
//error_reporting(E_ALL & ~E_NOTICE);

/******************
 * 設定値ここから *
 ******************/

// $config_ext[拡張子] = <val>;
//   <val> が文字列ならば代替アイコンへのパス
//   <val> が数値の 1 ならば静止画サムネイル生成処理(PHP-GD)
//   <val> が数値の 2 ならば動画サムネイル生成処理(ffmpeg)
//   <val> がそれ以外の値の場合は無条件で THUMB_FALSE
// // 2023-10-18: 動画か静止画かによるサムネイル生成処理分けはまだ実装していない
$config_ext['png'] = 1;
$config_ext['jpg'] = 1;
$config_ext['jpeg'] = 1;
$config_ext['gif'] = 1;
$config_ext['mpo'] = 1;

$config_ext['flv'] = 2;
$config_ext['wmv'] = 2;
$config_ext['asf'] = 2;
$config_ext['mp4'] = 2;
$config_ext['mpeg'] = 2;
$config_ext['mpg'] = 2;
$config_ext['avi'] = 2;
$config_ext['ts'] = 2;
$config_ext['m2ts'] = 2;
$config_ext['webm'] = 2;
$config_ext['mov'] = 2;
$config_ext['mkv'] = 2;

$config_ext['wav'] = 'icon_mov.png';
$config_ext['mp3'] = 'icon_mov.png';
$config_ext['swf'] = 'icon_mov.png';

$config_ext['pch'] = 'icon_arc.png';
$config_ext['spch'] = 'icon_arc.png';
$config_ext['txt'] = 'icon_txt.png';
$config_ext['pdf'] = 'icon_txt.png';
$config_ext['mhtm'] = 'icon_txt.png';
$config_ext['mht'] = 'icon_txt.png';

$config_ext['rar'] = 'icon_arc.png';
$config_ext['zip'] = 'icon_arc.png';
$config_ext['lzh'] = 'icon_arc.png';
$config_ext['7z'] = 'icon_arc.png';
$config_ext['gz'] = 'icon_arc.png';
$config_ext['bz2'] = 'icon_arc.png';

$THUMB_EXT = 'jpg';  // 生成するサムネイルの画像形式拡張子
$THUMB_SEARCH_EXT = ['webp', 'jpg'];  // サムネイル検索する拡張子

// 無視するファイル名
$ignore_file = '/^(THM_|LOG_|img_|tm_).+\.(png|jpe?g|gif)$|_thumb\.(png|jpe?g|gif)$/i';

// サムネイル生成に使う ffmpeg パス
// この値はエスケープやクオーションされずに使われます
$config_ffmpeg_path = 'ffmpeg';
// ロギングを有効にするかどうか
$config_log = false;
// ロギング有効時のログ出力先。空文字列('')ならば標準出力へ。
// **ファイルへの出力を放置するとログファイルが膨れ続けるので注意**
$config_log_path = 'log_oosamuneiru.txt';

// ファイルの昇順、逆順ソートが終わった後に以下の形式で呼び出される無名関数
// $config_on_files_sorted = function($new_to_old_files) {}
// default:false
$config_on_files_sorted = false;

if (file_exists('instance-config.php')) {
  require_once 'instance-config.php';
};

$backuped_error_level = error_reporting();

if ($backuped_error_level & E_WARNING) {
    error_reporting($backuped_error_level & ~E_WARNING);
};

define('IMG_DIR', 'img_src/');      // 相対パス。画像一覧ディレクトリ。 / で終了すること
define('THUMB_DIR', 'img_thumb/');  // 相対パス。サムネイル保存ディレクトリ。 / で終了すること
define('MAX_W', 200);           // 出力画像幅
define('MAX_H', 155);           // 出力画像高さ
define('PAGE_COLS', 4);         // 1行に表示する画像数
define('PAGE_DEF', 64);         // 1ページに表示する画像数

// $THUMB_SEARCH_EXT 設定に関わらずP-Ver仕様のサムネイル(gif,png,jpg)も検索されます

define('THUMB_FALSE', 'orz.png');   // サムネイル作成失敗時に表示する画像
define('THUMB_QUALITY', 75);    // サムネイルの品質(JPEG用) 0～100 まで指定可能  default:75
define('IMG_INWH', 0);          // サムネイル<img>に width height を含める  する:1 しない:0
define('PUT_DATE', 0);          // 更新時刻を入れる  する:1 しない:0

define('HTML_ENTRANCE_O', 'sam_r.htm'); // 入り口ファイル名（逆順
define('HTML_ENTRANCE_R', 'sam.htm');   // 入り口ファイル名（正順
define('N_HTML_SUFFIX_O', 's_r.htm');       // 1ページ以降の拡張子（逆順
define('N_HTML_SUFFIX_R', 's.htm');     // 1ページ以降の拡張子（正順
define('GOTO_P', 'sam_r.htm');      // 最初に表示するページ
define('BACK_LINK', '../');  // 戻るリンク
define('TARGET_FRAME', '_blank');  // <a target=TARGET_FRAME>

define('TITLE', 'サムネイル一覧');  // タイトル（<title>のみ
define('TITLE_T', '古い順');    // 正順表示
define('TITLE_R', '新しい順');  // 逆順表示

define('DIR_DEPTH', -1);    // 探索するディレクトリの深さを制限する  する:0以上 しない:-1
define('SORT_BY_DATE', 1);  // 更新日順にする  する:1 しない:0

define('FORCE_P_VER_THUMB', false); // true/false index.php の旧バージョンのサムネシステムを使う

/******************
 * 設定値ここまで *
 ******************/

error_reporting($backuped_error_level);
unset($backuped_error_level);


/*
 * グローバル変数
 */
$src_rootdir = (function(){
  $x = realpath('.') . '/' . IMG_DIR;

  if (! str_ends_with($x, '/')) {
    return $x . '/';
  };
  return $x;
})();


// PHP 8.1 以降で true
$is_function_exists_imagecreatefromavif = function_exists('imagecreatefromavif');

// 時間計測用の入れもの
$performance = array();
$performance['listfile'] = 0;  # ファイルをリストするのにかかった時間
$performance['thumb'] = 0;     # サムネイルチェック・サムネイル生成にかかった時間
$performance['sort'] = 0;      # ファイルのソートにかかった時間
$performance['page'] = 0;      # ページ生成にかかった時間

function format_performance($performance) {
  $s = sprintf("処理(ファイル列挙[%01.2f], サムネ確認・生成[%01.2f], ファイルソート[%01.2f], HTML生成[%01.2f])"
               , $performance['listfile']
               , $performance['thumb']
               , $performance['sort']
               , $performance['page']);
  might_log($s);
  return '<div align=right>'
    . htmlentities($s)
    . '</div>';
};

function might_log(...$args) {
  global $config_log, $config_log_path;
  if (! $config_log) {
    return false;
  };

  $s = implode(' ', $args) . PHP_EOL;
  if ($config_log_path === '') {
    fwrite(STDERR, $s);
  }
  else {
    file_put_contents($config_log_path, $s, FILE_APPEND);
  };

  return true;
};


function is_video_ext($ext) {
  global $config_ext;
  return 2 === $config_ext[strtolower($ext)];
};


function might_shrink_size($width_height) {
  // $width_height: 配列: [source width, source height]
  // サムネイルサイズを計算して返す
  $in_w = $width_height[0];
  $in_h = $width_height[1];

  if ($in_w > MAX_W || $in_h > MAX_H) {
    $key_s = min(MAX_W / $in_w, MAX_H / $in_h);
    $out_w = ceil($in_w * $key_s);
    $out_h = ceil($in_h * $key_s);
  } else {
    $out_w = $in_w;
    $out_h = $in_h;
  };
  return [$out_w, $out_h];
};


/*
 * サムネパスとその状態をセットで扱うためだけのクラス
 */
class ThumbPath {
  public $path = '';
  private $type = 0;

  private const ICON = 0;
  private const EXIST = 1;
  private const NEED_GENERATE = 2;

  // $path はプリセットアイコンを指している
  public function is_icon() {
    return $this->type == ThumbPath::ICON;
  }
  // $path は既に生成されたサムネイルを指している。プリセットアイコンでもない。
  public function is_exist() {
    return $this->type == ThumbPath::EXIST;
  }
  // $path はまだ生成されていないサムネイルを指している
  public function is_need_generate() {
    return $this->type == ThumbPath::NEED_GENERATE;
  }
  // ファクトリ関数
  public static function icon($path) {
    return new ThumbPath($path, ThumbPath::ICON);
  }
  public static function exist($path) {
    return new ThumbPath($path, ThumbPath::EXIST);
  }
  public static function need_generate($path) {
    return new ThumbPath($path, ThumbPath::NEED_GENERATE);
  }

  private function __construct($path, $type) {
    $this->path = $path;
    $this->type = $type;
  }
};


function slash_to_2f($s) {
  return str_replace('/', '_2F_', $s);
};


function array_ref($a, $key, $defval=null) {
  if (array_key_exists($key, $a)) {
    return $a[$key];
  };
  return $defval;
};

/*
 * 定義[pathcompo]: rootdir, subdir, basename, ext の 4 つよって表されるファイル
 *   パス
 *
 *   ここでは src が入力画像や動画。thumb が{既存あるいはこれから生成する}サムネ
 * イル。
 *
 *   この関数は、'pull the php code' 時点のバージョン(以降P-Version, P-Verと呼ぶ)
 * との互換性を保ち、不具合を緩和しつつ、拡張性を与えるために定義する。P-Verの
 * 時点では、コードの各所で集約せずに冗長にサムネイルパスを計算していた。
 *   以降 tesheke が仕様を決めた部分をTVersion, TVerと呼ぶ。
 *
 *   P-Verは gif, jpg, png の三種のみをサポートしていた。
 * ソースと同じ拡張子でサムネイルを生成していた。拡張子を除いたベースネームが同
 * じであるサムネファイルの名前の衝突を回避するための仕様だった。
 *   P-Verは rootdir 直下のファイルのサムネには root_2F_ というプレフィックスを付
 * けていた。この挙動は、root というフォルダがある場合には名前衝突を起こす可能性
 * がある( root/img.png と img.png が同じサムネ名になる)。
 *
 *   この関数は、P-VerとT-Verのサムネイル命名規則を加味して動作する。また、代替
 * サムネイルリスト(旧$icons, 現$config_ext)を加味する。
 *   サムネイルが既に存在している場合でもそうでない場合でもサムネイルパスを返す。
 * P-Verのサムネを検索し、次にTVerのサムネを検索し、存在する場合はそれを返す。
 * 存在しない場合は TVer 命名規則でサムネパスを返す。
 *
 * $src_rootdir: '/'で終わるパス。サムネイルの名前付けに使わないフォルダパス名。
 * $src_subdir: '/'で終わらないパス。サムネイルの名前付けに使うフォルダパス名。
 *              ソース画像が $src_rootdir 直下にある場合で '' を示す。
 * $src_basename: フォルダと拡張子を除いたファイル名
 * $src_ext: '.' から始まる拡張子。(もし 'a.tar.gz' の場合は '.gz' のみ)
 */
function prepare_thumb_path_by_src_pathcompo($src_rootdir, $src_subdir, $src_basename, $src_ext) {

  global $config_ext, $config_ffmpeg_path, $config_log, $config_log_path
    , $THUMB_EXT, $THUMB_SEARCH_EXT;

  $icon_path = array_ref($config_ext, strtolower(substr($src_ext, 1)));
  if (gettype($icon_path) === 'string') {
    return ThumbPath::icon($icon_path);
  };

  $thumbo = prepare_pver_thumb($src_rootdir, $src_subdir, $src_basename, $src_ext);

  if ($thumbo !== false && $thumbo->is_exist()) {
    return $thumbo;
  };

  if (FORCE_P_VER_THUMB) {
    return ThumbPath::icon(THUMB_FALSE);
  };
  unset($thumbo);

  /*
   * 以降は T-Ver(新バージョン)サムネイル命名規則。
   */
  $thumb_prefix = '';
  if ($src_subdir !== '') {
    $thumb_prefix = slash_to_2f($src_subdir . '/');
  };
  $thumb_prefix = THUMB_DIR . 't1_' . $thumb_prefix . $src_basename . $src_ext;

  foreach ($THUMB_SEARCH_EXT as $x) {
    // $x に来るのは 'webp' や 'jpg'
    $pa = $thumb_prefix . '.' . $x;
    if (file_exists($pa)) {
      return ThumbPath::exist($pa);
    };
  };

  $thumb_path = $thumb_prefix . '.' . $THUMB_EXT;
  $src_path = join_pathcompo($src_rootdir, $src_subdir, $src_basename, $src_ext);

  $thumb_obj = generate_thumb_with_gd($src_path, $thumb_prefix, $THUMB_EXT);
  if ($thumb_obj->is_exist()) {
    return $thumb_obj;
  };
  $thumb_obj = generate_thumb_with_ffmpeg($src_path, $thumb_prefix, $THUMB_EXT);
  return $thumb_obj;
};


function generate_thumb_with_gd($src_path, $thumb_prefix, $thumb_ext_without_dot) {
  global $config_log, $config_log_path, $is_function_exists_imagecreatefromavif;

  $lower_src_path = strtolower($src_path);

  $type = exif_imagetype($src_path);

  if ($type === false) {
    return ThumbPath::icon(THUMB_FALSE);

    // 以下 ABC 順
  } elseif ($is_function_exists_imagecreatefromavif
            && str_ends_with($lower_src_path, '.avif')) {
    // PHP 8.3(2023-10)時点では exif_imagetype は avif に対応していない
    // 拡張子だけで判断する
    $image_obj = imagecreatefromavif($src_path);
  } elseif ($type === IMAGETYPE_BMP) {
    $image_obj = imagecreatefrombmp($src_path);
  } elseif ($type === IMAGETYPE_GIF) {
    $image_obj = imagecreatefromgif($src_path);
  } elseif ($type === IMAGETYPE_JPEG) {
    $image_obj = imagecreatefromjpeg($src_path);
  } elseif ($type === IMAGETYPE_PNG) {
    $image_obj = imagecreatefrompng($src_path);
  } elseif ($type === IMAGETYPE_WEBP) {
    $image_obj = imagecreatefromwebp($src_path);  // PHP 5.4.0
  }
  else {
    return ThumbPath::icon(THUMB_FALSE);
  };

  if ($image_obj === false) {
    // imagecreateを試みたのにも関わらず失敗した
    might_log('PHP-GD could not interpret image file:', $src_path);
    return ThumbPath::icon(THUMB_FALSE);
  };

  list($in_width, $in_height) = getimagesize($src_path);
  list($out_width, $out_height) = might_shrink_size([$in_width, $in_height]);

  $image_out_obj = imagecreatetruecolor($out_width, $out_height);
  imagecopyresampled($image_out_obj, $image_obj,
                     0, 0, 0, 0,
                     $out_width, $out_height, $in_width, $in_height);

  $thumb_path = $thumb_prefix . '.' . $thumb_ext_without_dot;

  if ($thumb_ext_without_dot === 'jpg'
      || $thumb_ext_without_dot === 'jpeg') {

    imagejpeg($image_out_obj, $thumb_path, THUMB_QUALITY);
    return ThumbPath::exist($thumb_path);
  }
  elseif ($thumb_ext_without_dot === 'webp') {

    imagewebp($image_out_obj, $thumb_path);
    return ThumbPath::exist($thumb_path);
  };
  // このコードは PHP 8 以降が前提だから imagedestroy しない。

  might_log('failed to output thumbnail file:', $thumb_path);

  return ThumbPath::icon(THUMB_FALSE);
};


function generate_thumb_with_ffmpeg($src_path, $thumb_prefix, $thumb_ext_without_dot) {
  global $config_ffmpeg_path, $config_log, $config_log_path;

  $thumb_path = $thumb_prefix . '.' . $thumb_ext_without_dot;

  $ifile = escapeshellarg($src_path);
  $ofile = escapeshellarg($thumb_path);

  // アス比維持で拡縮
  $vf = 'scale=ceil(min(' . MAX_W . '/iw, ' . MAX_H . '/ih) * iw):'
      .       'ceil(min(' . MAX_W . '/iw, ' . MAX_H . '/ih) * ih)';
  $vf = str_replace(',', '\,', $vf);
  $vf = escapeshellarg($vf);

  $verbose = '-v quiet';
  if ($config_log) {
    $verbose = '';
  };

  $cmd = $config_ffmpeg_path . ' -y -i ' . $ifile . ' ' . $verbose . ' -an -frames:v 1 '
       . ' -vf ' . $vf . ' ' . $ofile . ' 2>&1';
  $ffmpeg_stdout = [];
  $ffmpeg_retcode = 0;

  exec($cmd, $ffmpeg_stdout, $ffmpeg_retcode);

  if ($config_log) {
    might_log(implode(PHP_EOL, $ffmpeg_stdout) . PHP_EOL
              . 'return code: ' . $ffmpeg_retcode . PHP_EOL);
  };

  if (! file_exists($thumb_path)) {
    return ThumbPath::icon(THUMB_FALSE);
  };
  if (0 === filesize($thumb_path)) {
    unlink($thumb_path);
    return ThumbPath::icon(THUMB_FALSE);
  };

  return ThumbPath::exist($thumb_path);
};


/* 旧バージョンサムネシステム */
function prepare_pver_thumb($src_rootdir, $src_subdir, $src_basename, $src_ext) {

  if ($src_subdir == '') {
    $thumb_prefix = 'root_2F_';
  } else {
    $thumb_prefix = slash_to_2f($src_subdir . '/');
  };
  $thumb_prefix = THUMB_DIR . $thumb_prefix . $src_basename;

  foreach (['jpg', 'png', 'gif'] as $x) {
    $pa = $thumb_prefix . '.' . $x;
    if (file_exists($pa)) {
      return ThumbPath::exist($pa);
    };
  };

  $src_path = join_pathcompo($src_rootdir, $src_subdir, $src_basename, $src_ext);

  $thumb_path = false;
  list($_, $_, $type) = getimagesize($src_path);
  if ($type === IMAGETYPE_GIF) {
    $thumb_path = $thumb_prefix . '.gif';
  }
  elseif ($type === IMAGETYPE_JPEG) {
    $thumb_path = $thumb_prefix . '.jpg';
  }
  elseif ($type === IMAGETYPE_PNG) {
    $thumb_path = $thumb_prefix . '.png';
  };

  if ($thumb_path === false) {
    return false;
  };

  if (FORCE_P_VER_THUMB) {
    $src_path = join_pathcompo($src_rootdir, $src_subdir, $src_basename, $src_ext);
    if (generate_pver_thumb($src_path, $thumb_path)) {
      return ThumbPath::exist($thumb_path);
    };
  };

  return ThumbPath::need_generate($thumb_path);
};

/* pathcompo形式を path文字列形式へ。 */
function join_pathcompo($src_rootdir, $src_subdir, $src_basename, $src_ext) {
  if ($src_subdir == '') {
    return $src_rootdir . $src_basename . $src_ext;
  };
  return $src_rootdir . $src_subdir . '/' . $src_basename . $src_ext;
};


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
.video {
  border: 1px solid gray;
  border-radius: 10px;
}
</style>
</head>
<body bgcolor="#FFFFEE">
<table width="100%">
<tr>';

  if (PUT_DATE && is_file(HTML_ENTRANCE_O)) { // 更新日付表示
    $dat .= '<td align="left"><font size=-1>';
    if ($page==0) { $dat .= '最終更新：'.date('\'y m月 d日 H:i.').'<br>前回'; }
    else { $dat .= '最終'; }
    $dat .= '更新：'.date('\'y m月 d日 H:i.', filemtime(HTML_ENTRANCE_O)).'</font>';
    $dat .= "</td>\n";
  }

  $dat .= '<td align="right">
[<a href="'. basename(__FILE__) .'">更新</a>] [<a href="' . BACK_LINK . '">戻る</a>]
</td></tr></table>
<center>
<div class="autopagerize_page_element">
<p><b>サムネイル一覧</b></p>
';

}


/*
 * ファイル一覧を作る。
 * 各サムネイルも作る。
 * 各アイコンとの関連付けも行う。
 */
function prepare_files() {
  global $src_rootdir, $config_ext, $ignore_file, $performance;

  $iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(
      IMG_DIR,
      FilesystemIterator::KEY_AS_PATHNAME
        | FilesystemIterator::CURRENT_AS_FILEINFO
        | FilesystemIterator::SKIP_DOTS
        | FilesystemIterator::UNIX_PATHS));
  $iterator->setMaxDepth(DIR_DEPTH);
  $key1 = array();
  $files = array();
  $performance['thumb'] = 0;

  foreach ($iterator as $info) {
    if (preg_match($ignore_file, $info->getFilename())) {
      continue;
    };
    if (! array_key_exists(strtolower($info->getExtension()), $config_ext)) {
      continue;
    };

    // ファイル名と拡張子を取得
    $tmpext = '.' . $info->getExtension();
    $tmpart = $info->getBasename($tmpext);
    $tmsubdir = $iterator->getSubPath();

    $time_thumb_start = microtime(true);

    $thumb = prepare_thumb_path_by_src_pathcompo(
      IMG_DIR, $tmsubdir, $tmpart, $tmpext);

    $performance['thumb'] += microtime(true) - $time_thumb_start;

    $rel_srcpath = join_pathcompo(
      IMG_DIR, $tmsubdir, $tmpart, $tmpext);

    // リスト生成
    if (SORT_BY_DATE) {
      // http://www.php.net/manual/ja/class.splfileinfo.php
      $key1[] = $info->getMTime();
    } else {
      $key1[] = $info->getFilename();
    };

    if ($iterator->getSubPath() != '') {
      $fixsub = slash_to_2f($iterator->getSubPath() . '/');
    } else {
      $fixsub = 'root_2F_';
    };

    $files[] = array(
      'name_with_ext' => $info->getBasename(),
      'rel_thumbpath' => $thumb->path,
      'rel_srcpath' => $rel_srcpath,
      'is_video' => is_video_ext($info->getExtension())
    );
  };

  // ソート
  array_multisort($key1, SORT_ASC, SORT_REGULAR, $files);

  return $files;
};


function build_dispmsg(&$files, &$file_index, $files_length, $page_local_index) {
  /*
   * $page_local_index: 序数
   */
  global $src_rootdir;

  $val = current($files);

  if ($val === false) {
    return '';
  };

  next($files);
  $line = $file_index;
  ++$file_index;

  $attr_width_height = '';
  if (IMG_INWH && file_exists($val['rel_thumbpath'])) {
    // tesheke: サムネへの縮小とこの部分で計算が二重になってしまっている
    $size = getimagesize($val['rel_thumbpath']);

    // 画像表示縮小
    list($out_w, $out_h) = might_shrink_size($size);
    $attr_width_height = 'width="' . $out_w . '" height="' . $out_h . '"';
  };

  $imglink = str_replace('%2F','/',rawurlencode($val['rel_srcpath']));
  $thumblink = str_replace('%2F', '/', rawurlencode($val['rel_thumbpath']));

  $attr_target = '';
  if (TARGET_FRAME !== '') {
    $attr_target = 'target="' . TARGET_FRAME . '"';
  };

  $dispmsg = '';
  // 画像テーブル
  $is_video = $val['is_video'];
  $dispmsg .= '    <td align=center '
           .           ($is_video ? 'class="video"' : '')
           . '><a href="' . $imglink . '"'
           .  ' ' . $attr_target . '>' . PHP_EOL
           .  '    <img src="' . $thumblink . '" '
           .  $attr_width_height
           .  ' border="0"><br>'
           .  ($is_video ? htmlentities('[動画]') : '')
           .  htmlentities($val['name_with_ext']) . "</a></td>" . PHP_EOL;

  if ( (($page_local_index % PAGE_COLS) == 0)
       && (($page_local_index % PAGE_DEF) != 0)
       && ($line != ($files_length-1))) {
    $dispmsg .= "  </tr><tr>" . PHP_EOL;
  };

  return $dispmsg;
};


/* 表示処理部分 */
function updatesam(){
  global $src_rootdir, $ignore_file, $config_on_files_sorted, $performance;

  $time_prepare = microtime(true);
  $files = prepare_files();
  $performance['listfile'] = microtime(true) - $time_prepare - $performance['thumb'];

  if (0 == count($files)) {
    error('ERROR!!<br>NO IMAGE DATA!');
  };

  $time_sort_start = microtime(true);

  $filesA = array_reverse($files); // 逆順
  $filesB = &$files; // 正順

  if ($config_on_files_sorted) {
    $config_on_files_sorted($filesA);
  };

  $performance['sort'] = microtime(true) - $time_sort_start;

  $time_page_start = microtime(true);

  // ページ作成.初期値設定
  $files_length = count($files);
  $pages_length = ceil($files_length / PAGE_DEF);
  // ページ分の繰り返し
  for ($page = 0; $page < $pages_length; ++$page) {
    $page_is_loop_last = $page == ($pages_length - 1);
    $datA = '';
    $datB = '';
    $pagesA = '';
    $pagesB = '';
    $dispmsgA = '';
    $dispmsgB = '';
    $lineA = 0;
    $lineB = 0;
    // 枚数分の繰り返し
    for ($i = 0; $i < PAGE_DEF; ++$i) {
      // 逆順表示
      $dispmsgA .= build_dispmsg($filesA, $lineA, $files_length, $i + 1);
      // 正順表示
      $dispmsgB .= build_dispmsg($filesB, $lineB, $files_length, $i + 1);
      clearstatcache(); // ファイルのstatをクリア
    };

    // 改ページ処理
    $prev = $page - 1;
    $next = $page + 1;
    if ($prev >= 0) {
      if ($prev == 0) {
        $pagesA .= '<a href="'.HTML_ENTRANCE_O.'">&lt;&lt;前へ</a>　';
        $pagesB .= '<a href="'.HTML_ENTRANCE_R.'">&lt;&lt;前へ</a>　';
      } else {
        $pagesA .= '<a href="'.$prev.N_HTML_SUFFIX_O.'">&lt;&lt;前へ</a>　';
        $pagesB .= '<a href="'.$prev.N_HTML_SUFFIX_R.'">&lt;&lt;前へ</a>　';
      }
    } else {
      $pagesA .= '&lt;&lt;前へ　';
      $pagesB .= '&lt;&lt;前へ　';
    };

    for ($i = 0; $i < $pages_length ; ++$i) {
      if ($page == $i) {
        $pagesA .= '[<b>'.$i.'</b>] ';
        $pagesB .= '[<b>'.$i.'</b>] ';
        // 表示方法
        if ($page == 0) {
          $tmpA = '<p><a href="'.HTML_ENTRANCE_R.'">'.TITLE_T.'</a>　'.TITLE_R.'</p>';
          $tmpB = '<p>'.TITLE_T.'　<a href="'.HTML_ENTRANCE_O.'">'.TITLE_R.'</a></p>';
        } else {
          $tmpA = '<p><a href="'.$i.N_HTML_SUFFIX_R.'">'.TITLE_T.'</a>　'.TITLE_R.'</p>';
          $tmpB = '<p>'.TITLE_T.'　<a href="'.$i.N_HTML_SUFFIX_O.'">'.TITLE_R.'</a></p>';
        };
      } else {
        if ($i == 0) {
          $pagesA .= '[<a href="'.HTML_ENTRANCE_O.'"><b>0</b></a>] ';
          $pagesB .= '[<a href="'.HTML_ENTRANCE_R.'"><b>0</b></a>] ';
        }
        else {
          $pagesA .= '[<a href="'.$i.N_HTML_SUFFIX_O.'"><b>'.$i.'</b></a>] ';
          $pagesB .= '[<a href="'.$i.N_HTML_SUFFIX_R.'"><b>'.$i.'</b></a>] ';
        };
      };
    };

    if ($pages_length > $next) {
      $pagesA .= '　<a rel="next" href="'.$next.N_HTML_SUFFIX_O.'">次へ&gt;&gt;</a>';
      $pagesB .= '　<a rel="next" href="'.$next.N_HTML_SUFFIX_R.'">次へ&gt;&gt;</a>';
    } else {
      $pagesA .= '　次へ&gt;&gt;';
      $pagesB .= '　次へ&gt;&gt;';
    };
    // ヘッダHTML
    head($datA,$page);
    head($datB,$page);
    // 総数表示
    $datA .= '<p>画像総数<b>'.$files_length."</b>枚</p>\n";
    $datB .= '<p>画像総数<b>'.$files_length."</b>枚</p>\n";
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
    // 統計締め
    $stats = '';
    if ($page_is_loop_last) {
      $performance['page'] = microtime(true) - $time_page_start;
      $stats = format_performance($performance);
      echo $stats;
    };
    // フッタHTML
    foot($datA, $stats);
    foot($datB, $stats);
    // 記事部分作成
    if ($page == 0) {
      $logfilename = HTML_ENTRANCE_O;
    }
    else {
      $logfilename = $page . N_HTML_SUFFIX_O;
    };
    $fp = fopen($logfilename, "w");
    set_file_buffer($fp, 0);
    rewind($fp);
    fputs($fp, $datA);
    fclose($fp);
    chmod($logfilename, 0666);
    // 記事部分作成
    if ($page == 0) {
      $logfilename_reverse = HTML_ENTRANCE_R;
    }
    else {
      $logfilename_reverse = $page . N_HTML_SUFFIX_R;
    };
    $fp = fopen($logfilename_reverse, 'w');
    set_file_buffer($fp, 0);
    rewind($fp);
    fputs($fp, $datB);
    fclose($fp);
    chmod($logfilename_reverse,0666);
  };
};


/* フッタ */
function foot(&$dat, $stats=''){
  $dat .= '
</div>
<div class="autopagerize_insert_before"></div>
</center>
' . $stats . '
<div align=right><a href="http://php.s3.to" target="_top">レッツPHP!</a> + <a href="http://siokara.que.jp/" target="_top">siokara</a></div>
</body></html>';
}


/* エラー画面 */
function error($mes){
  head($dat,1);
  echo $dat;
  echo "<br><br><hr size=1><br><br>\n",
       "<center><font color=red size=5><b>",$mes,"<br><br><a href=",HTML_ENTRANCE_O,">リロード</a></b></font></center>\n",
       "<br><br><hr size=1>\n";
  die('</body></html>');
}


/*
 * P-Ver (旧バージョン)命名規則のサムネイルを作成する。
 */
function generate_pver_thumb($src_path, $thumb_path) {
  // 画像の幅と高さとタイプを取得
  list($in_w, $in_h, $in_type) = getimagesize($src_path);
  // リサイズ
  list($out_w, $out_h) = might_shrink_size([$in_w, $in_h]);

  switch ($in_type) {
  case IMAGETYPE_GIF: // 1
    $im_in = @imagecreatefromgif($src_path);
    break;
  case IMAGETYPE_JPEG: // 2
    $im_in = @imagecreatefromjpeg($src_path);
    break;
  case IMAGETYPE_PNG: // 3
    $im_in = @imagecreatefrompng($src_path);
    break;
  default:
    return false;
  };

  if (! $im_in) {
    return false;
  };

  // 出力画像（サムネイル）のイメージを作成  元画像を縦横とも コピー
  $im_out = imagecreatetruecolor($out_w, $out_h);
  imagecopyresampled($im_out, $im_in, 0, 0, 0, 0, $out_w, $out_h, $in_w, $in_h);

  // tesheke: 画像タイプとサムネファイル名の決定が分離していて気持ちが悪いが、
  //   これは互換性のため。

  // サムネイル画像を保存
  switch ($in_type) {
  case IMAGETYPE_GIF:
    imagegif($im_out, $thumb_path);
    break;
  case IMAGETYPE_JPEG:
    imagejpeg($im_out, $thumb_path, THUMB_QUALITY);
    break;
  case IMAGETYPE_PNG:
    imagepng($im_out, $thumb_path);
    break;
  };

  // 作成したイメージを破棄(PHP 8 以降で imagedestroy は何もしない)
  imagedestroy($im_in);
  imagedestroy($im_out);
  return true;
};


/* P-Ver命名規則のサムネイルを作成 */
// function thumb($src_rootdir, $src_subdir, $src_basename, $src_ext) {
//
//   $thumb_path_obj = prepare_thumb_path_by_src_pathcompo(
//     $src_rootdir, $src_subdir, $src_basename, $src_ext);
//
//   if (! $thumb_path_obj->is_need_generate()) {
//     return;
//   };
//
//   $thumb_path = $thumb_path_obj->path;
//   unset($thumb_path_obj);
//
//   $src = join_pathcompo(
//     $src_rootdir, $src_subdir, $src_basename, $src_ext);
//
// }


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

  if($err)error($err);
}

/*-----------Main-----------*/
init(); //←■■初期設定後は不要なので削除可■■
updatesam();
echo '<meta http-equiv="refresh" content="0;URL='.GOTO_P.'">';

?>
