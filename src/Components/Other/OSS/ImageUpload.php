<?php
namespace Holy\Components\Other\OSS;

use Holy\Components\Primary\Str;
use OSS\Core\OssException;
use OSS\OssClient;

/**
 * 适用于bucket权限设置为公共读
 * Class ImageUpload
 * @package Holy\Service\OSS
 */
class ImageUpload
{
    const BUCKET_DIRECTORY = 'images';  //bucket中创建的文件夹

    /**
     * 允许上传的图片后缀
     * @var array
     */
    private static $suffix = array(
        'png'    => 'image/png',
        'jpg'    => 'image/jpeg',
        'jpeg'   => 'image/jpeg',
    );

    /**
     * 获取一个OSSClient实例
     * @return null|OssClient
     */
    public static function getClientInstance()
    {
        return XLOssClient::getOssClient();
    }

    /**
     * 上传图片到OSS
     * @param bool $addWaterMark
     * @return array
     */
    public static function uploadFile($addWaterMark = false)
    {
        $tmpFile = $_FILES['file']['tmp_name'];
        $client = self::getClientInstance();
        $pid = Str::uniqidStr();
        $file = explode('.', $_FILES['file']['name']);
        $options = [OssClient::OSS_HEADERS =>['Content-Type' => self::$suffix[end($file)]]];
        if ($addWaterMark) {
            $options[OssClient::OSS_PROCESS] = "image/watermark,text_6K6456OK5LiT55So_font_5b6u6L2v6ZuF6buR";
        }
        $imagePid = self::BUCKET_DIRECTORY.'/'.$pid;
        try {
            $result = $client->uploadFile('xlimages', $imagePid, $tmpFile, $options);
            if ($result['info']['http_code'] == 200) {
                $imgInfo = getimagesize($tmpFile);
                $data = [
                    'pid'    => $pid,
                    'imagePid' => $imagePid,
                    'url'    => $result['info']['url'],
                    'width'  => $imgInfo[0],
                    'height' => $imgInfo[1]
                ];
                return ['errcode' => 200, 'message' => '上传成功', 'data' => $data];
            }
            return ['errcode' => $result['info']['http_code'], 'message' => '上传失败'];
        } catch (OssException $e) {
            return null;
        }
    }

    /**
     * 简单的下载文件并添加水印
     * @param $imagePid
     * @param bool $addWaterMark
     */
    public static function downloadOSSFile($imagePid, $addWaterMark = false)
    {
        $downloadFile = "download.jpg";
        $client = self::getClientInstance();
        $options = [OssClient::OSS_FILE_DOWNLOAD => $downloadFile];
        if ($addWaterMark) {
            $options[OssClient::OSS_PROCESS] = "image/watermark,text_6K6456OK5LiT55So_font_5b6u6L2v6ZuF6buR";
        }
        try {
            $client->getObject(env('OSS_BUCKET'), $imagePid, $options);
        } catch (OssException $e) {}
    }

    /**
     * 判断OSS上该文件是否存在
     * @param $pid
     * @param $bucket
     * @return bool|null
     */
    public static function isObjectExists($pid, $bucket)
    {
        $client = self::getClientInstance();
        try {
            $exist = $client->doesObjectExist($bucket, $pid);
        } catch (OssException $e) {
            return null;
        }
        return $exist;
    }
}