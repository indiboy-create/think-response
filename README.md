## ThinkPHP-PanGu - 盘古ThinkPHP开源索引!
面向 ThinkPHP 的开源工程索引：组件、框架、服务与发布工具，按能力组织，持续整理。
仓库地址：**<https://github.com/indiboy-create/ThinkPHP-PanGu/>**
在线站点：**<https://indiboy-create.github.io/ThinkPHP-PanGu/>**

# think-response

为 ThinkPHP 8 API 项目提供规范统一的响应数据结构。

## 特性

- 固定响应字段：`status`、`code`、`message`、`data`、`error`
- 成功、失败、异常响应格式统一
- 业务码自动映射 HTTP 状态码，支持严格模式与固定 200/500 模式
- 支持模型、集合、数组、分页数据
- 支持字段隐藏、字段别名（例如 `message` 改为 `msg`、分页内层 `data.data` 改为 `data.list`）
- 支持业务码多语言消息
- 内置统一异常处理，可自动接管 ThinkPHP 默认异常处理器
- 兼容 ThinkPHP 8 的 Facade 与服务自动发现

## 环境

- PHP >= 8.0
- ThinkPHP 8

## 安装

```bash
composer require youvision/think-response
```

发布配置文件（可选）：

```bash
php think vendor:publish
```

## 快速开始

```php
use Youvision\ThinkResponse\Facade\Response;

class UserController
{
    public function index()
    {
        return Response::success(User::select());
    }

    public function read(int $id)
    {
        return Response::success(User::find($id));
    }

    public function save()
    {
        return Response::created(['id' => 1]);
    }
}
```

成功响应：

```json
{
  "status": "success",
  "code": 200,
  "message": "操作成功",
  "data": {
    "id": 1,
    "nickname": "admin"
  },
  "error": {}
}
```

失败响应：

```php
return Response::fail('账户或密码错误', 401);
```

```json
{
  "status": "error",
  "code": 401,
  "message": "账户或密码错误",
  "data": {},
  "error": {}
}
```

## 分页

```php
return Response::success(User::paginate(10));
```

```json
{
  "status": "success",
  "code": 200,
  "message": "操作成功",
  "data": {
    "data": [
      {"id": 1, "nickname": "admin"}
    ],
    "meta": {
      "pagination": {
        "count": 1,
        "per_page": 10,
        "current_page": 1,
        "last_page": 3,
        "total": 21,
        "has_more": true
      }
    }
  },
  "error": {}
}
```

## 常用方法

```php
Response::success($data, $message, $code);
Response::ok($message);
Response::localize(200101);
Response::created($data, $message, $location);
Response::accepted($data, $message, $location);
Response::noContent($message);

Response::fail($message, $code, $errors);
Response::errorBadRequest($message);
Response::errorUnauthorized($message);
Response::errorForbidden($message);
Response::errorNotFound($message);
Response::errorMethodNotAllowed($message);
Response::errorInternal($message);
```

辅助函数：

```php
json_success($data, $message, $code);
json_fail($message, $code, $errors);
```

## 字段配置

在 `config/response.php` 中调整：

```php
'fields' => [
    'message'   => ['show' => true, 'alias' => 'msg'],
    'debug'     => ['show' => false],
    'data.data' => ['show' => true, 'alias' => 'data.list'],
],
```

响应会变为：

```json
{
  "status": "success",
  "code": 200,
  "msg": "操作成功",
  "data": {
    "list": []
  },
  "error": {}
}
```

`show` 为 `false` 时该字段不会输出；`alias` 支持点路径。修改顶层字段别名时，嵌套字段的点路径需要同步修改。

## 业务码与消息

业务码采用「HTTP 状态码前三位 + 三位业务编号」的形式：

```php
const SERVICE_REGISTER_SUCCESS = 200101;
const SERVICE_LOGIN_ERROR      = 500102;
```

`200101` 的 HTTP 状态码为 `200`，`status` 为 `success`；`500102` 的 HTTP 状态码为 `500`，`status` 为 `fail`。

消息读取优先级：

1. 方法中显式传入的 `message`
2. 语言包中的消息
3. `config/response.php` 的 `messages`
4. 内置的 HTTP 状态码中文描述

语言包 `app/lang/zh-cn.php` 或 `app/lang/zh-cn.response.php`：

```php
return [
    'response_200101' => '注册成功',
    'response_500102' => '登录失败',
];
```

如果 `config/lang.php` 中开启了 `allow_group`，也可以使用分组写法：

```php
return [
    'response' => [
        '200101' => '注册成功',
        '500102' => '登录失败',
    ],
];
```

```php
return Response::localize(200101);
```

## 异常处理

包内置的异常处理器会在以下情况返回统一 JSON：

- 请求头 `Accept` 包含 `json`
- AJAX 请求
- 请求体为 JSON

如需所有请求都返回 JSON，将配置项 `exception.always` 设为 `true`。

### 自动接管

当应用没有自定义 `think\exception\Handle` 绑定时，服务提供者会自动接管默认异常处理器，无需额外操作。

### 已有自定义异常处理器

如果 `app/provider.php` 中绑定了自己的异常处理器，可将其改为继承包的处理器：

```php
namespace app;

use Youvision\ThinkResponse\Exception\Handle as ResponseHandle;

class ExceptionHandle extends ResponseHandle
{
}
```

也可以在 `config/response.php` 中设置：

```php
'exception' => [
    'override' => true,
],
```

强制替换自定义异常处理器（应用自身的异常处理逻辑会失效）。

### 异常映射

```php
'exception' => [
    'map' => [
        \think\exception\ValidateException::class => [
            'code'    => 422,
            'message' => '验证失败',
            'expose'  => true,
        ],
        \app\exception\BusinessException::class => [
            'code'    => 400001,
            'message' => '业务处理失败',
            'expose'  => false,
        ],
    ],
],
```

表单验证异常响应：

```json
{
  "status": "error",
  "code": 422,
  "message": "验证失败",
  "data": {},
  "error": {
    "email": [
      "邮箱不能为空"
    ]
  }
}
```

## 失败响应 HTTP 状态码

```php
// 严格模式：业务码前三位作为 HTTP 状态码
'error_code' => false,

// 所有失败响应返回 HTTP 200
'error_code' => 200,

// 所有失败响应返回 HTTP 500
'error_code' => 500,
```

## 许可

Apache-2.0