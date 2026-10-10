<?php
declare(strict_types=1);
/**
 * Cron: php scripts/purge.php
 * Deletes inactive answer records after DENDRILA_RETENTION_DAYS (default: 30).
 * This is deliberately not web accessible.
 */
if (PHP_SAPI!=='cli') { http_response_code(403); exit(1); }
require dirname(__DIR__).'/src/Storage.php';
$raw=getenv('DENDRILA_RETENTION_DAYS')?:'30';
if (!ctype_digit($raw) || (int)$raw<1 || (int)$raw>365)
    throw new RuntimeException('DENDRILA_RETENTION_DAYS doit être compris entre 1 et 365.');
$cutoff=time()-((int)$raw)*86400;
$dir=df_storage();
$deleted=0;
foreach (glob($dir.'/*.json')?:[] as $file) {
    $id=basename($file,'.json');
    if (!preg_match('/\A[a-f0-9]{32}\z/D',$id) || filemtime($file)>=$cutoff) continue;
    $handle=fopen($dir.'/'.$id.'.lock','c');
    if (!$handle) continue;
    try {
        if (!flock($handle,LOCK_EX|LOCK_NB)) continue;
        clearstatcache(true,$file);
        if (is_file($file) && filemtime($file)<$cutoff && unlink($file)) $deleted++;
    } finally { flock($handle,LOCK_UN); fclose($handle); }
}
$rateDir=$dir.'/rate';
foreach (glob($rateDir.'/*.json')?:[] as $file) {
    if (filemtime($file)>=time()-172800) continue;
    $handle=fopen($file,'c+');
    if (!$handle) continue;
    try {
        if (!flock($handle,LOCK_EX|LOCK_NB)) continue;
        clearstatcache(true,$file);
        if (is_file($file) && filemtime($file)<time()-172800) unlink($file);
    } finally { flock($handle,LOCK_UN); fclose($handle); }
}
echo "Dendrila Forms : {$deleted} dossier(s) supprimé(s) selon la durée d'inactivité.\n";
