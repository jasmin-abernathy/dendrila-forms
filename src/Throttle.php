<?php
declare(strict_types=1);
require_once __DIR__.'/Storage.php';

/**
 * Limit creation and resume attempts by server-observed IP.
 * Never trust client-supplied forwarding headers; proxy operators need edge limits too.
 * Only keyed digests and short-lived counters are persisted.
 */
function df_throttle(string $action,int $max,int $window): void {
    $dir=df_storage().'/rate';
    if (!is_dir($dir) && !mkdir($dir,0700,true) && !is_dir($dir))
        throw new RuntimeException('Protection temporairement indisponible.');
    $keyFile=$dir.'/.secret';
    $secretHandle=fopen($keyFile,'c+');
    if (!$secretHandle) throw new RuntimeException('Protection temporairement indisponible.');
    chmod($keyFile,0600);
    try {
        if (!flock($secretHandle,LOCK_EX)) throw new RuntimeException('Protection temporairement indisponible.');
        rewind($secretHandle);
        $secret=trim((string)stream_get_contents($secretHandle));
        if (!preg_match('/\A[a-f0-9]{64}\z/D',$secret)) {
            $secret=bin2hex(random_bytes(32));
            ftruncate($secretHandle,0); rewind($secretHandle);
            if (fwrite($secretHandle,$secret)===false) throw new RuntimeException('Protection temporairement indisponible.');
            fflush($secretHandle);
        }
    } finally { flock($secretHandle,LOCK_UN); fclose($secretHandle); }
    $ip=(string)($_SERVER['REMOTE_ADDR']??'local');
    $digest=hash_hmac('sha256',$action."\0".$ip,$secret);
    $file=$dir.'/'.$digest.'.json';
    $handle=fopen($file,'c+');
    if (!$handle) throw new RuntimeException('Protection temporairement indisponible.');
    chmod($file,0600);
    try {
        if (!flock($handle,LOCK_EX)) throw new RuntimeException('Protection temporairement indisponible.');
        rewind($handle);
        $counter=json_decode((string)stream_get_contents($handle),true);
        $now=time();
        $since=(int)($counter['since']??0);
        $count=(int)($counter['count']??0);
        if ($since>$now || $since<=$now-$window) { $since=$now; $count=0; }
        if ($count >= $max) throw new RuntimeException('Trop de tentatives. Réessayez dans quelques minutes.');
        $data=json_encode(['since'=>$since,'count'=>$count+1],JSON_THROW_ON_ERROR);
        ftruncate($handle,0); rewind($handle);
        if (fwrite($handle,$data)===false) throw new RuntimeException('Protection temporairement indisponible.');
        fflush($handle);
    } finally { flock($handle,LOCK_UN); fclose($handle); }
}
