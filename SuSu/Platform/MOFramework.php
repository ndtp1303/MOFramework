<?php

namespace SuSu\Platform;

class MOFramework
{

    protected array $convert = [
        '#section'                                  => '<div class=":name-:time-:(run->key)">:content',
        '#endsection'                               => '</div>',
    ];

    protected array $errors = [
        '404'                                       => '',
        '500'                                       => '',
    ];

    protected string $key = '';

    protected string $beginSection = '#section';
    protected string $endSection = '#endsection';

    protected string $viewPath = '';
    protected string $defaultView = '/home';

    protected string $fileExtension = '.mo';

    public function __construct()
    {
        $this->key = $this->generateRandomString(64);
    }

    /**
     * @param string $viewPath
     */

    public function setViewPath(string $viewPath): void
    {
        $this->viewPath = $viewPath;
    }

    /**
     * @param string $extension
     */

    public function setFileExtension(string $extension): void
    {
        $this->fileExtension = $extension;
    }

    /**
     * @param string $viewPath
     */

    public function setDefaultView(string $viewPath): void
    {
        $this->defaultView = $viewPath;
    }

    /**
     * @param int $code
     * @param string $viewPath
     */

    public function setErrorPage(int $code, string $viewPath): void
    {
        $this->errors[$code] = (isset($this->viewPath) ? $this->viewPath : '') . $viewPath;
    }

    public function run(): void
    {
        $request_uri = $_SERVER['REQUEST_URI'];

        if (substr($request_uri, -1) == '/') $request_uri = substr($request_uri, 0, -1);

        if ($request_uri == '/run.php') {
            $this->redirect($this->defaultView);
            return;
        }

        $empty = true;
        foreach ($this->files() as $file) {
            if ($request_uri == $file['name']) {
                $empty = false;
                $this->build(
                    $this->convertToHTML($file['path'])
                );
                break;
            }
        }

        if ($empty) $this->build(
            $this->convertToHTML($this->errors['404'] . $this->fileExtension)
        );
    }

    /**
     * @param string $content
     */

    protected function build(string $content): void
    {
        echo $content;
    }

    protected function files(): array
    {
        $files = [];

        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->viewPath)
        ) as $file) {
            if ($file->isDir() || $file->getExtension() != str_replace(
                '.',
                '',
                $this->fileExtension
            )) continue;

            $files[] = [
                'path'          => $file,
                'name'          => explode('.', str_replace('\\', '/', explode($this->viewPath, $file)[1]))[0],
            ];
        }

        return $files;
    }

    /**
     * @param int $code
     */

    protected function sendResponse(int $code): array
    {
        if ($code !== NULL) {

            switch ($code) {
                case 100:
                    $text = 'Continue';
                    break;
                case 101:
                    $text = 'Switching Protocols';
                    break;
                case 200:
                    $text = 'OK';
                    break;
                case 201:
                    $text = 'Created';
                    break;
                case 202:
                    $text = 'Accepted';
                    break;
                case 203:
                    $text = 'Non-Authoritative Information';
                    break;
                case 204:
                    $text = 'No Content';
                    break;
                case 205:
                    $text = 'Reset Content';
                    break;
                case 206:
                    $text = 'Partial Content';
                    break;
                case 300:
                    $text = 'Multiple Choices';
                    break;
                case 301:
                    $text = 'Moved Permanently';
                    break;
                case 302:
                    $text = 'Moved Temporarily';
                    break;
                case 303:
                    $text = 'See Other';
                    break;
                case 304:
                    $text = 'Not Modified';
                    break;
                case 305:
                    $text = 'Use Proxy';
                    break;
                case 400:
                    $text = 'Bad Request';
                    break;
                case 401:
                    $text = 'Unauthorized';
                    break;
                case 402:
                    $text = 'Payment Required';
                    break;
                case 403:
                    $text = 'Forbidden';
                    break;
                case 404:
                    $text = 'Not Found';
                    break;
                case 405:
                    $text = 'Method Not Allowed';
                    break;
                case 406:
                    $text = 'Not Acceptable';
                    break;
                case 407:
                    $text = 'Proxy Authentication Required';
                    break;
                case 408:
                    $text = 'Request Time-out';
                    break;
                case 409:
                    $text = 'Conflict';
                    break;
                case 410:
                    $text = 'Gone';
                    break;
                case 411:
                    $text = 'Length Required';
                    break;
                case 412:
                    $text = 'Precondition Failed';
                    break;
                case 413:
                    $text = 'Request Entity Too Large';
                    break;
                case 414:
                    $text = 'Request-URI Too Large';
                    break;
                case 415:
                    $text = 'Unsupported Media Type';
                    break;
                case 500:
                    $text = 'Internal Server Error';
                    break;
                case 501:
                    $text = 'Not Implemented';
                    break;
                case 502:
                    $text = 'Bad Gateway';
                    break;
                case 503:
                    $text = 'Service Unavailable';
                    break;
                case 504:
                    $text = 'Gateway Time-out';
                    break;
                case 505:
                    $text = 'HTTP Version not supported';
                    break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
            }

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

            header($protocol . ' ' . $code . ' ' . $text);

            $GLOBALS['http_response_code'] = $code;
        } else {
            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
        }

        return [
            'code'          => $code,
            'text'          => $text,
        ];
    }

    /**
     * @param int $length
     */

    protected function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @param string $viewPath
     */

    protected function convertToHTML(string $viewPath): string
    {
        if ($this->file_exists($viewPath)) {
            $content = file_get_contents($viewPath);

            if (empty($content)) return $this->convertToHTML($this->errors['404'] . $this->fileExtension);

            $content = $this->replaceCode($content);

            if ($this->hasErrors($content)) {
                return $this->convertToHTML($this->errors['500'] . $this->fileExtension);
            } else {
                $content = '<!DOCTYPE html>
                <html lang="vi">
                <head>
                    <meta charset="UTF-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>' . $content['name'] . '</title>
                </head>
                <body>
                    ' . $content['content'] . '
                </body>
                </html>';
                return $content;
            }
        }

        return '';
    }

    /**
     * @param string $string
     */

    protected function removeUnicode(string $string): string
    {
        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D' => 'Đ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        ];

        foreach ($unicode as $nonUnicode => $uni) {
            $string = preg_replace("/($uni)/i", $nonUnicode, $string);
        }

        return str_replace(' ', '_', $string);
    }

    /**
     * @param string $content
     */

    protected function replaceCode(string $content): array
    {
        $nameSection = $this->getNameSection($content);
        $contentSection = $this->getContentSection($content, $nameSection);

        if ($nameSection == '' || $contentSection == '') {
            return [
                'errors' => [
                    'Internal Server Error',
                ]
            ];
        }

        return [
            'name'              => $nameSection,
            'content'           => str_replace([
                ':name',
                ':time',
                ':content',
                ':(run->key)',
            ], [
                $this->removeUnicode($nameSection),
                time(),
                $contentSection,
                $this->key,
            ], $this->convert[$this->beginSection]) . $this->convert[$this->endSection],
        ];
    }

    /**
     * @param string $content
     */

    protected function getNameSection(string $content): string
    {
        if (!isset(explode($this->beginSection, $content)[1])) return '';
        return trim(explode(PHP_EOL, explode($this->beginSection, $content)[1])[0]);
    }

    /**
     * @param string $content
     * @param string $name
     */

    protected function getContentSection(string $content, string $name): string
    {
        if (!isset(explode(
            $this->beginSection . ' ' . $name,
            trim($content)
        )[1])) return '';

        return (explode(
            $this->endSection,
            trim(
                explode(
                    $this->beginSection . ' ' . $name,
                    trim($content)
                )[1]
            )
        )[0]);
    }

    /**
     * @param string $uri
     */

    protected function redirect(string $uri)
    {
        ob_start();
        header('Location: ' . $uri);
        return;
    }

    /**
     * @param array $array
     */

    protected function hasErrors(array $array): bool
    {
        return !empty($array['errors']);
    }

    /**
     * @param string $viewPath
     */

    protected function file_exists(string $viewPath): bool
    {
        return file_exists($viewPath);
    }
}
