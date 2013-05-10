<?php
/**
 *
 * a php client of hadoop webhdfs
 *
 * @author zhiwensun
 * @date 2012-11-27
 **/

define('SERVICE_URL', 'http://10.10.6.99:50070');


/**
 * upload a file to hdfs
 *
 * example : var_dump(json_decode(hdfs_put('/tmp/xh', '/tmp/php_test', true)));
 *
 * @param string $localfile 本地文件
 * @param string $remotefile hdfs中的文件位置
 * @param boolean $overwrite 是否覆盖文件
 * @return string 返回的错误信息
 */
function hdfs_put($localfile, $remotefile, $overwrite = false) {

    $url = SERVICE_URL . "/webhdfs/v1$remotefile?op=CREATE&user.name=hadoop";
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_PUT, 1);
    curl_setopt($ch, CURLOPT_HEADER, true);

    $resp = curl_exec($ch);
    curl_close($ch);

    $pos = strpos($resp, 'Location:');
    $resp = strstr(substr($resp, $pos + 9), "\n", true);
    $datanode = trim($resp);

    if ($overwrite) {
        $datanode = str_replace('overwrite=false', 'overwrite=true', $datanode);
    }


    $f = fopen($localfile, 'rb');
    $file_size = filesize($localfile);

    $ch = curl_init($datanode);

    curl_setopt($ch, CURLOPT_PUT, 1);
    //curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_INFILESIZE, $file_size);
    curl_setopt($ch, CURLOPT_INFILE, $f);

    $resp = curl_exec($ch);
    curl_close($ch);

    fclose($f);

    return $resp;
}




/**
 * cat file in hdfs
 *
 * var_dump(hdfs_cat('/tmp/php_test'));
 *
 * @param string $remotefile
 * @return string
 */
function hdfs_cat($remotefile) {
    $url = SERVICE_URL . "/webhdfs/v1$remotefile?op=open&user.name=hadoop";

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $resp = curl_exec($ch);

    curl_close($ch);

    return $resp;
}




/**
 * get file from hdfs, write it to localfile
 *
 * var_dump(hdfs_get('/tmp/sync.log', '/tmp/dddd'));
 *
 * @param string $remotefile
 * @param string $localfile
 * @param int $per fetch size per request
 * @return boolean
 */
function hdfs_get($remotefile, $localfile, $per=5242880) {
    $offset = 0;

    @unlink($localfile);

    do {
        $url = SERVICE_URL . "/webhdfs/v1$remotefile?op=open&user.name=hadoop&offset={$offset}&length={$per}";

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $resp = curl_exec($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $offset += $per;
            file_put_contents($localfile, $resp, FILE_APPEND);
        }
    } while ($http_code == 200);

    return true;
}
function hdfs_get_link($remotefile, $localfile, $per=5242880) {
    $offset = 0;
    do {
        $url = SERVICE_URL . "/webhdfs/v1$remotefile?op=open&user.name=hadoop&offset={$offset}&length={$per}";
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $resp = curl_exec($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $offset += $per;
            file_put_contents($localfile, $resp, FILE_APPEND);
        }
    } while ($http_code == 200);

    return true;
}
/**
 * list dir on hdfs
 *
 * var_dump(hdfs_ls('/tmp'));
 *
 * @param string $remote_dir
 * @return json
 */
function hdfs_ls($remote_dir){
    $url = SERVICE_URL . "/webhdfs/v1$remote_dir?op=LISTSTATUS&user.name=hadoop";

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resp = curl_exec($ch);

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if($http_code == 200){
        return json_decode($resp, true);
    }else{
        return false;
    }
}

/**
 * download dir on hdfs and merge files into local_file
 *
 * var_dump(hdfs_merge('/user/corp/solr_slow_query/20121213', '/tmp/ssq.log'));
 *
 * @param string $remote_dir
 * @param string $local_file
 * @return bool
 */
function hdfs_merge($remote_dir, $local_file){
    echo "ls $remote_dir\n";
    $files = hdfs_ls($remote_dir);
    if($files === false) return false;

    file_put_contents($local_file, "");

    foreach($files['FileStatuses']['FileStatus'] as $f){
        $remote_dir .= '/'.$f['pathSuffix'];
        echo "downloading $remote_dir\n";
        hdfs_get_link($remote_dir, $local_file);
    }
    echo "merge files into $local_file\n";
    return true;
}
