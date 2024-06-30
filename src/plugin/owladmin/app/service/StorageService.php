<?php

namespace plugin\owladmin\app\service;

use Closure;
use Throwable;
use Webman\File;
use Webman\Http\UploadFile;
use Psr\Http\Message\StreamInterface;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToSetVisibility;
use Shopwwi\WebmanFilesystem\FilesystemFactory;

/**
 * 存储服务
 * StorageService
 * plugin\owladmin\app\service
 *
 * Author:sym
 * Date:2024/6/27 上午7:23
 * Company:极智科技
 */
class StorageService
{
    protected mixed $adapterType = '';
    protected string $path = 'storage';
    protected int $size = 1024 * 1024 * 10;
    protected array $extYes = [];      //允许上传文件类型
    protected array $extNo = [];       // 不允许上传文件类型
    protected array $config = [];

    protected array $imageYes = []; // 允许上传图片类型
    /**
     * @var Closure[]
     */
    protected static array $maker = [];

    /**
     * 构造方法
     *
     * @access public
     */
    public function __construct($config = null)
    {
        $this->config = $config != null ? $config : config('plugin.shopwwi.filesystem.app');
        $this->adapterType = $this->config['default'] ?? 'local';
        $this->size = $this->config['max_size'] ?? 1024 * 1024 * 10;
        $this->extYes = $this->config['ext_yes'] ?? [];
        $this->extNo = $this->config['ext_no'] ?? [];
        $this->imageYes = $this->config['image_yes'] ?? [];
        if (!empty(static::$maker)) {
            foreach (static::$maker as $maker) {
                \call_user_func($maker, $this);
            }
        }
    }

    /**
     * 注入配置文件
     *
     * @param $config
     *
     * @return $this
     */
    public function setConfig($config): static
    {
        $this->config = $config;
        return $this;
    }

    /**
     * 设置服务注入
     *
     * @access public
     *
     * @param Closure $maker
     *
     * @return void
     */
    public static function maker(Closure $maker): void
    {
        static::$maker[] = $maker;
    }

    /**
     * 存储路径
     *
     * @param string $name
     *
     * @return $this
     */
    public function adapter(string $name): static
    {
        $this->adapterType = $name;
        return $this;
    }

    /**
     * 存储路径
     *
     * @param string $name
     *
     * @return static
     */
    public function path(string $name): static
    {
        $this->path = $name;
        return $this;
    }

    /**
     * 允许上传文件类型
     *
     * @param array $ext
     *
     * @return $this
     */
    public function extYes(array $ext): static
    {
        $this->extYes = $ext;
        return $this;
    }

    /**
     * 不允许上传文件类型
     *
     * @param array $ext
     *
     * @return $this
     */
    public function extNo(array $ext): static
    {
        $this->extNo = $ext;
        return $this;
    }

    /**
     * 设置允许文件大小
     *
     * @param int $size
     *
     * @return $this
     */
    public function size(int $size): static
    {
        $this->size = $size;
        return $this;
    }

    /**
     * 上传文件
     *
     * @param      $file
     * @param bool $same
     *
     * @return mixed|void
     * @throws Throwable
     */
    public function upload($file, bool $same = true)
    {
        $this->verifyFile($file); // 验证附件

        $filesystem = FilesystemFactory::get($this->adapterType, $this->config);
        $storageKey = $this->hash($file->getPathname());
        $extName = strtolower($file->getUploadExtension());
        if ($same) {
            $storageKey = $this->hash($file->getPathname()) . '_' . uniqid();
        } else {
            if ($filesystem->fileExists(trim($this->path . '/' . $storageKey . '.' . $extName, '/'))) {
                $filesystem->delete(trim($this->path . '/' . $storageKey . '.' . $extName, '/'));
            }
        }
        $result = $this->putFileAs($this->path, $file, $storageKey . '.' . $extName);
        if ($result) {
            $info = [
                'adapter'     => $this->adapterType,
                'origin_name' => $file->getUploadName(),
                'file_name'   => $result,
                'storage_key' => $storageKey,
                'file_url'    => $this->url($result),
                'size'        => $file->getSize(),
                'mime_type'   => $file->getUploadMineType(),
                'extension'   => $extName,
            ];
            if (str_starts_with($file->getUploadMineType(), 'image')) {
                $size = \getimagesize($file);
                $info['file_height'] = $size[1] ?? 0;
                $info['file_width'] = $size[0] ?? 0;
            }
            return json_decode(json_encode($info));
        }

    }

    /**
     * 原文件覆盖上传
     *
     * @param      $file
     * @param      $fileName
     * @param bool $ext
     *
     * @return mixed|void
     * @throws FilesystemException
     * @throws Throwable
     */
    public function reUpload($file, $fileName, bool $ext = false)
    {
        $this->verifyFile($file); // 验证附件

        $filesystem = FilesystemFactory::get($this->adapterType, $this->config);
        $first = strrpos($fileName, '/');
        if ($first === false) {
            $path = $this->path;
            $keyAndExt = explode('.', substr($fileName, 0, strlen($fileName)));
        } else {
            $path = substr($fileName, 0, $first);
            $keyAndExt = explode('.', substr($fileName, $first + 1, strlen($fileName)));
        }
        $storageKey = $keyAndExt[0] ?? \hash_file('md5', $file->getPathname());
        $extName = strtolower($ext ? $keyAndExt[1] : $file->getUploadExtension());
        $fileName = $path . '/' . $storageKey . '.' . $extName;
        if ($filesystem->fileExists(trim($fileName, '/'))) {
            $filesystem->delete($fileName);
        }
        $result = $this->putFileAs($this->path, $file, $storageKey . '.' . $extName);
        if ($result) {
            $info = [
                'origin_name' => $file->getUploadName(),
                'file_name'   => $result,
                'storage_key' => $storageKey,
                'file_url'    => $this->url($result),
                'size'        => $file->getSize(),
                'mime_type'   => $file->getUploadMineType(),
                'extension'   => $extName,
            ];
            if (\substr($file->getUploadMineType(), 0, 5) == 'image') {
                $size = \getimagesize($file);
                $info['file_height'] = $size[1] ?? 0;
                $info['file_width'] = $size[0] ?? 0;
            }
            return \json_decode(\json_encode($info));
        }
    }

    /**
     * 批量上传文件
     *
     * @param      $files
     * @param int  $num
     * @param int  $size
     * @param bool $same
     *
     * @return mixed
     * @throws FilesystemException
     * @throws Throwable
     */
    public function uploads($files, int $num = 0, int $size = 0, bool $same = true)
    {
        $result = [];
        if ($num > 0 && count($files) > $num) {
            throw new \Exception('文件数量超过了' . $num);
        }
        if ($size > 0) {
            $allSize = 0;
            foreach ($files as $key => $file) {
                $allSize += $file->getSize();
            }
            if ($allSize > $size) {
                throw new \Exception('文件总大小超过了' . $size);
            }
        }
        foreach ($files as $key => $file) {
            $info = $this->upload($file, $same);
            array_push($result, $info);
        }
        return json_decode(json_encode($result));
    }

    /**
     * base64图片上传
     *
     * @param $baseImg
     *
     * @return mixed
     * @throws Throwable
     */
    public function base64Upload($baseImg): mixed
    {

        preg_match('/^(data:\s*image\/(\w+);base64,)/', $baseImg, $res);
        if (count($res) != 3) {
            throw new \Exception('格式错误');
        }
        $img = base64_decode(str_replace($res[1], '', $baseImg));
        $size = getimagesizefromstring($img);
        if (count($size) == 0) {
            throw new \Exception('图片格式不正确');
        }
        if (!empty($this->extYes) && !in_array($size['mime'], $this->extYes)) {
            throw new \Exception('不允许上传文件类型' . $size['mime']);
        }
        if (!empty($this->extNo) && in_array($size['mime'], $this->extNo)) {
            throw new \Exception('文件类型不被允许' . $size['mime']);
        }
        $extName = strtolower($res[2]);
        $storageKey = md5(uniqid());
        $fileName = $this->path . '/' . $storageKey . '.' . $extName;
        $base_img = str_replace($res[1], '', $baseImg);
        $base_img = str_replace('=', '', $base_img);
        $img_len = strlen($base_img);
        $file_size = intval($img_len - ($img_len / 8) * 2);

        if ($file_size > $this->size) {
            throw new \Exception("上传文件过大（当前大小 {$file_size}，需小于 {$this->size})");
        }

        $this->put(
            $path = trim($fileName, '/'), $img
        );

        $info = [
            'origin_name' => $fileName,
            'file_name'   => $fileName,
            'storage_key' => $storageKey,
            'file_url'    => $this->url($fileName),
            'size'        => $file_size,
            'mime_type'   => $size['mime'],
            'extension'   => $extName,
            'file_height' => $size[1] ?? 0,
            'file_width'  => $size[0] ?? 0,
        ];

        return \json_decode(\json_encode($info));
    }

    /**
     * 压缩上传图片
     *
     * @param      $file
     * @param null $processFunction
     * @param bool $same
     *
     * @return mixed|void
     * @throws FilesystemException
     * @throws Throwable
     */
    public function processUpload($file, $processFunction = null, bool $same = true)
    {

        $this->verifyFile($file); // 验证附件

        if (class_exists(\Intervention\Image\ImageManagerStatic::class) || class_exists(Intervention\Image\ImageManager::class)) {

        } else {
            throw new \Exception('图片处理器未安装');
        }
        if (class_exists(Intervention\Image\ImageManager::class)) {
            $image = \Intervention\Image\ImageManager::imagick()->read($file);
        } else {
            $image = \Intervention\Image\ImageManagerStatic::make($file);
        }

        if (is_callable($processFunction)) {
            $image = $processFunction($image);
        }

        $filesystem = FilesystemFactory::get($this->adapterType, $this->config);
        $storageKey = $this->hash($file->getPathname());
        $extName = strtolower($file->getUploadExtension());
        if ($same) {
            $storageKey = $this->hash($file->getPathname()) . '_' . uniqid();
        } else {
            if ($filesystem->fileExists(trim($this->path . '/' . $storageKey . '.' . $extName, '/'))) {
                $filesystem->delete(trim($this->path . '/' . $storageKey . '.' . $extName, '/'));
            }
        }
        $name = $storageKey . '.' . $extName;
        $result = $this->put($path = trim($this->path . '/' . $name, '/'), class_exists(Intervention\Image\ImageManager::class) ? $image->toPng() : $image->stream());

        if ($result) {
            $info = [
                'adapter'     => $this->adapterType,
                'origin_name' => $file->getUploadName(),
                'file_name'   => $path,
                'storage_key' => $storageKey,
                'file_url'    => $this->url($path),
                'size'        => $image->filesize(),
                'mime_type'   => $file->getUploadMineType(),
                'extension'   => $extName,
                'file_height' => $image->height(),
                'file_width'  => $image->width(),
            ];
            return \json_decode(\json_encode($info));
        }
    }

    /**
     * 文件验证
     *
     * @param $file
     *
     * @throws \Exception
     */
    protected function verifyFile($file): void
    {
        if (str_contains($file->getUploadMineType(), 'image')) {
            if (!empty($this->imageYes) && !in_array(ltrim($file->getUploadMineType(), 'image/'), $this->imageYes)) {
                throw new \Exception('不允许上传图片格式' . $file->getUploadMineType());
            }
        } else {
            if (!empty($this->extYes) && !in_array($file->getUploadMineType(), $this->extYes)) {
                throw new \Exception('不允许上传文件类型' . $file->getUploadMineType());
            }
            if (!empty($this->extNo) && in_array($file->getUploadMineType(), $this->extNo)) {
                throw new \Exception('文件类型不被允许' . $file->getUploadMineType());
            }
        }

        if ($file->getSize() > $this->size) {
            throw new \Exception("上传文件过大（当前大小 {$file->getSize()}，需小于 {$this->size})");
        }
    }

    /**
     * 获取url
     *
     * @param string $fileName
     *
     * @return string
     */
    public function url(string $fileName): string
    {
        $url = parse_url($fileName);
        if (isset($url['host'])) return $fileName;
        $domain = $this->config['storage'][$this->adapterType]['url'];
        if (empty($domain)) {
            $domain = '//' . \request()->host();
        }
        return $domain . '/' . $fileName;
    }

    /**
     * Determine if two files are the same by comparing their hashes.
     *
     * @param string $firstFile
     * @param string $secondFile
     *
     * @return bool
     */
    public function hasSameHash(string $firstFile, string $secondFile): bool
    {
        $hash = @md5_file($firstFile);

        return $hash && $hash === @md5_file($secondFile);
    }

    /**
     * Get the hash of the file at the given path.
     *
     * @param string $path
     * @param string $algorithm
     *
     * @return string
     */
    public function hash(string $path, string $algorithm = 'md5'): string
    {
        return hash_file($algorithm, $path);
    }

    /**
     *
     * @param       $path
     * @param       $file
     * @param array $options
     *
     * @return false|string
     * @throws Throwable
     */
    public function putFile($path, $file, array $options = []): bool|string
    {
        $file = is_string($file) ? new File($file) : $file;
        return $this->putFileAs($path, $file, $this->hash($file->getPathname()) . '.' . $file->getUploadExtension(), $options);
    }

    /**
     * @param       $path
     * @param       $file
     * @param       $name
     * @param array $options
     *
     * @return false|string
     * @throws Throwable
     */
    public function putFileAs($path, $file, $name, array $options = []): bool|string
    {
        $stream = fopen(is_string($file) ? $file : $file->getRealPath(), 'r');

        // Next, we will format the path of the file and store the file using a stream since
        // they provide better performance than alternatives. Once we write the file this
        // stream will get closed automatically by us so the developer doesn't have to.
        $result = $this->put(
            $path = trim($path . '/' . $name, '/'), $stream, $options
        );

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $result ? $path : false;
    }

    /**
     *
     * @param       $path
     * @param       $contents
     * @param array $options
     *
     * @return bool|string
     * @throws FilesystemException
     * @throws Throwable
     */
    public function put($path, $contents, array $options = []): bool|string
    {
        $options = is_string($options)
            ? ['visibility' => $options]
            : (array)$options;

        // If the given contents is actually a file or uploaded file instance than we will
        // automatically store the file using a stream. This provides a convenient path
        // for the developer to store streams without managing them manually in code.
        if ($contents instanceof \Symfony\Component\HttpFoundation\File\File ||
            $contents instanceof UploadFile) {
            return $this->putFile($path, $contents, $options);
        }

        try {
            if ($contents instanceof StreamInterface) {
                FilesystemFactory::get($this->adapterType, $this->config)->writeStream($path, $contents->detach(), $options);
                return true;
            }
            is_resource($contents)
                ? FilesystemFactory::get($this->adapterType, $this->config)->writeStream($path, $contents, $options)
                : FilesystemFactory::get($this->adapterType, $this->config)->write($path, $contents, $options);
        } catch (UnableToWriteFile|UnableToSetVisibility $e) {
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        return true;
    }

    public static function disk(string $name = ''): StorageService
    {
        // todo 数据库查询
        $config = settings()->get('storage');
        // var_dump($config);
        // die;
        $name = $config['engine'];
        $config = [
            'default'   => $config['engine'] ?? 'local',
            'max_size'  => $config['upload_size'] ?? 1024 * 1024 * 10, //单个文件大小10M
            'ext_yes'   => isset($config['file_type']) ? explode(',', $config['file_type']) : [], //允许上传文件类型 为空则为允许所有
            'ext_no'    => [], // 不允许上传文件类型 为空则不限制
            'image_yes' => isset($config['image_type']) ? explode(',', $config['image_type']) : [],
            'storage'   => [
                'local'  => [
                    'driver' => \Shopwwi\WebmanFilesystem\Adapter\LocalAdapterFactory::class,
                    'root'   => $config['local']['path'] ?? public_path(),
                    'url'    => $config['local']['domain'] ?? '//127.0.0.1:8787', // 静态文件访问域名
                ],
                'qiniu'  => [
                    'driver'    => \Shopwwi\WebmanFilesystem\Adapter\QiniuAdapterFactory::class,
                    'accessKey' => $config['qiniu']['access_key'] ?? '',
                    'secretKey' => $config['qiniu']['secret_key'] ?? '',
                    'bucket'    => $config['qiniu']['bucket'] ?? '',
                    'domain'    => $config['qiniu']['domain'] ?? '',
                    'url'       => $config['qiniu']['domain'] ?? '', // 静态文件访问域名
                ],
                'qcloud' => [
                    'driver'        => \Shopwwi\WebmanFilesystem\Adapter\CosAdapterFactory::class,
                    'region'        => $config['qcloud']['region'] ?? '',
                    'app_id'        => 'COS_APPID',
                    'secret_id'     => $config['qiniu']['access_key'] ?? '',
                    'secret_key'    => $config['qcloud']['secret_key'] ?? '',
                    // 可选，如果 bucket 为私有访问请打开此项
                    // 'signed_url' => false,
                    'bucket'        => $config['qcloud']['bucket'] ?? '',
                    'read_from_cdn' => false,
                    'url'           => $config['qcloud']['domain'] ?? '', // 静态文件访问域名
                    // 'timeout' => 60,
                    // 'connect_timeout' => 60,
                    // 'cdn' => '',
                    // 'scheme' => 'https',
                ],
                'aliyun' => [
                    'driver'       => \Shopwwi\WebmanFilesystem\Adapter\AliyunOssAdapterFactory::class,
                    'accessId'     => $config['qiniu']['access_key'] ?? '',
                    'accessSecret' => $config['qiniu']['secret_key'] ?? '',
                    'bucket'       => $config['qiniu']['bucket'] ?? '',
                    // 'endpoint'     => 'OSS_ENDPOINT',
                    'url'          => $config['qiniu']['domain'] ?? '', // 静态文件访问域名
                    // 'timeout' => 3600,
                    // 'connectTimeout' => 10,
                    // 'isCName' => false,
                    // 'token' => null,
                    // 'proxy' => null,
                ],
            ],
        ];
        $file = new static($config);
        return $file->adapter($name);
    }

    public function get(string $path): string
    {
        return FilesystemFactory::get($this->adapterType, $this->config)->read($path);
    }
}
